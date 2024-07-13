<?php
session_start();
require("data/global.php");
$db = new SQLite3($database);
switch (htmlspecialchars($_POST['f'])) {
    case 'byDate':
        $startD = htmlspecialchars($_POST['startDay']);
        $startM = htmlspecialchars($_POST['startMonth']);
        $startY = htmlspecialchars($_POST['startYear']);
        $endD = htmlspecialchars($_POST['endDay']);
        $endM = htmlspecialchars($_POST['endMonth']);
        $endY = htmlspecialchars($_POST['endYear']);
        $startDate = new DateTime ($startY."-".$startM."-".$startD);
        $endDate = new DateTime($endY."-".$endM."-".$endD);
        if($startDate < $endDate){
            $sD = $startDate->format('Y-m-d');
            $eD = $endDate->format('Y-m-d');
        } else {
            $sD = $startDate->format('Y-m-d');
            $endDate = $startDate;
            $eD = $endDate->format('Y-m-d');
        }
        $mainSQL = "SELECT * FROM deliveries WHERE (completed BETWEEN '".$sD." 00:00:00' AND '".$eD." 23:59:59') AND (depot = '".$_SESSION['depot']."' AND depot !='') ORDER BY completed DESC";
        $countSQL = "SELECT COUNT(*) FROM deliveries WHERE (completed BETWEEN '".$sD." 00:00:00' AND '".$eD." 23:59:59') AND (depot = '".$_SESSION['depot']."' AND depot !='') ORDER BY completed DESC";
        break;
    case 'byCustomer':
        $customer = htmlspecialchars($_POST['customer']);
        if($customer == "NONE"){
            $mainSQL = "SELECT * FROM deliveries WHERE (depot = '".$_SESSION['depot']."' AND depot !='') AND status = 'Completed' ORDER BY added DESC";
            $countSQL = "SELECT COUNT(*) FROM deliveries WHERE (depot = '".$_SESSION['depot']."' AND depot !='') AND status = 'Completed' ORDER BY added DESC";
        } else {
            $mainSQL = "SELECT * FROM deliveries WHERE (company = '".$customer."') AND status = 'Completed' ORDER BY added DESC";
            $countSQL = "SELECT COUNT(*) FROM deliveries WHERE (company = '".$customer."') AND status = 'Completed' ORDER BY added DESC";
        }
        break;
    case 'byDriver':
        $driver = htmlspecialchars($_POST['driver']);
        if($driver == "NONE"){
            $mainSQL = "SELECT * FROM deliveries WHERE (depot = '".$_SESSION['depot']."' AND depot !='') AND status = 'Completed' ORDER BY added DESC";
            $countSQL = "SELECT COUNT(*) FROM deliveries WHERE (depot = '".$_SESSION['depot']."' AND depot !='') AND status = 'Completed' ORDER BY added DESC";
        } else {
            $mainSQL = "SELECT * FROM deliveries WHERE (driver = '".$driver."') AND status = 'Completed' ORDER BY added DESC";
            $countSQL = "SELECT COUNT(*) as count FROM deliveries WHERE (driver = '".$driver."') AND status = 'Completed' ORDER BY added DESC";
        }
        break;
    default:
        $mainSQL = "SELECT * FROM deliveries WHERE (depot = '".$_SESSION['depot']."' AND depot !='') AND status = 'Completed' ORDER BY added DESC";
        $countSQL = "SELECT COUNT(*) FROM deliveries WHERE (depot = '".$_SESSION['depot']."' AND depot !='') AND status = 'Completed' ORDER BY added DESC";
        break;
}

$jobCount = $db->querySingle($countSQL);
$mainRet = $db->query($mainSQL);
/* Get the data needed from the database */
/* Get the drivers data */
$driversSQL = "SELECT * FROM drivers WHERE depot = '".$_SESSION['depot']."'";
$driversRet = $db->query($driversSQL);
$customerSQL = "SELECT DISTINCT company FROM deliveries ORDER BY company ASC";
$customerRet = $db->query($customerSQL);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <title>LOCID | REPORTS</title>
    <script src="https://kit.fontawesome.com/c63864ee50.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family:arial;
            background-color:#ddd;
        }
        .sticky {
            position: -webkit-sticky; /* Safari */
            position: sticky;
            top: 0;
        }
        #header {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width:100%;
            border-radius: 2px;
        }
        #header th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #00529C;
            color: white;
        }
        #header tr:hover {
            background-color: blue;
            color:white;
            cursor: pointer;
        }
        #header td, #header th {
            border: 1px solid #FFF;
            padding: 8px;
        }
        #deliveries {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }
        #deliveries{
            border-radius: 2px;
            width:100%;
        }
        #deliveries th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #00529C;
            color: white;
        }
        #deliveries tr:hover {
            background-color: white;
            color:black;
            cursor: pointer;
        }
        #deliveries td, #deliveries th {
            border: 1px solid #FFF;
            padding: 8px;
        }
        .actionJob {
            width:200px;
            height:32px;
            background-color: #00529C;
            color:white;
            border-radius: 5px;
            border:none;
            font-size: 24px;
            cursor: pointer;
        }
        .settingsButton {
            width:15%;
            height:48px;
            background-color: #00529C;
            color:white;
            border-radius: 5px;
            border:1px solid white;
            font-size: 24px;
            cursor: pointer;
            margin:15px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 10px;
            width: 400px;
        }
        .modalClose {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .modalClose:hover,
        .modalClose:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .tab {
            overflow: hidden;
            border: 1px solid #ccc;
            background-color: #f1f1f1;
        }
        .tab button {
            background-color: inherit;
            float: left;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 14px 16px;
            transition: 0.3s;
        }
        .tab button:hover {
            background-color: #ddd;
        }
        .tab button.active {
            background-color: #ccc;
        }
        .tabcontent {
            display: none;
            padding: 6px 12px;
            border: 1px solid #ccc;
            border-top: none;
            text-align: center;
        }
        .formBoxes{
            width:200px;
            height:32px;
        }
        .smallDateBoxes{
            width:50px;
            height:32px;
        }
        .bigDateBoxes{
            width:75px;
            height:32px;
        }
        .main-box{
            width:100%;
            height:600px;
            margin: auto;
            /* border: #000 1px solid; */
            border-radius: 10px;
            text-align:center;
        }
        .addJobButton {
            width:100%;
            height:32px;
            background-color: #00529C;
            color:white;
            border-radius: 5px;
            border:none;
            font-size: 24px;
            cursor: pointer;
        }
        .textboxes{
            width:100%;
            height:24px;
        }
        tbody tr:nth-child(odd) {
            background-color:#9fd0fc ;
            color: #000;
        }
    </style>
</head>
<body>
<!-- START OPTIONS MODAL -->
<div id="optionsModal" class="modal">
    <!-- CONTENT -->
    <div class="modal-content">
        <span id='optionsClose' class="modalClose">&times;</span>
        <h2>Filter by</h2>
        <!-- TAB LINKS -->
        <div class="tab">
            <button class="tablinks" onclick="openOptionsTab(event, 'byDateTab')" id="defaultOpen">Date</button>
            <button class="tablinks" onclick="openOptionsTab(event, 'byCustomerTab')">Customer/Supplier</button>
            <button class="tablinks" onclick="openOptionsTab(event, 'byDriverTab')">Driver</button>
        </div>
        <!-- TAB CONTENT -->
        <div id="byDateTab" class="tabcontent">
            <h3>Filter By Date</h3>
            <form name='by-date' action='report.php' method='POST'>
                <input type='hidden' name='f' value='byDate'>
                <p>Start Date (Day/Month/Year)</p>
                <p>
                    <select class='smallDateBoxes' name='startDay'>
                        <?php
                            for ($i=1; $i < 32; $i++) { 
                                if($i < 10){
                                    echo "<option value='0".$i."'/>0".$i."</option>";
                                 } else {
                                    echo "<option value='".$i."'/>".$i."</option>";
                                }
                            }
                        ?>
                    </select> /
                    <select class='smallDateBoxes' name='startMonth'>
                        <?php
                            for ($i=1; $i < 13; $i++) { 
                                if($i < 10){
                                    echo "<option value='0".$i."'/>0".$i."</option>";
                                } else {
                                    echo "<option value='".$i."'/>".$i."</option>";
                                }
                            }
                        ?>
                    </select> /
                    <select class='bigDateBoxes' name='startYear'>
                        <?php
                            $thisYear = date("Y");
                            $backTo = $thisYear - 20;
                            for ($i=$thisYear; $i > $backTo; $i--) { 
                                echo "<option value='".$i."'/>".$i."</option>";
                            }
                        ?>
                    </select>
                </p>
                <p>End Date (Day/Month/Year)</p>
                <p>
                    <select class='smallDateBoxes' name='endDay'>
                        <?php
                            for ($i=1; $i < 32; $i++) {
                                if($i < 10){
                                    echo "<option value='0".$i."'/>0".$i."</option>";
                                } else {
                                    echo "<option value='".$i."'/>".$i."</option>";
                                }
                            }
                        ?>
                    </select> /
                    <select class='smallDateBoxes' name='endMonth'>
                        <?php
                            for ($i=1; $i < 13; $i++) { 
                                if($i < 10){
                                    echo "<option value='0".$i."'/>0".$i."</option>";
                                } else {
                                    echo "<option value='".$i."'/>".$i."</option>";
                                }
                            }
                        ?>
                    </select> /
                    <select class='bigDateBoxes' name='endYear'>
                        <?php
                            $thisYear = date("Y");
                            $backTo = $thisYear - 20;
                            for ($i=$thisYear; $i > $backTo; $i--) { 
                                echo "<option value='".$i."'/>".$i."</option>";
                            }
                        ?>
                    </select>
                </p>
                <p><input class='actionJob' type='submit' value='Filter' /></p>
            </form>
        </div>
        <div id="byCustomerTab" class="tabcontent">
            <h3>Filter by Customer / Supplier</h3>
            <form name='by-customer' action='report.php' method='POST'>
                <input type='hidden' name='f' value='byCustomer'>
                <p>
                    <select name='customer' class='formBoxes'>
                        <?php
                            while( $row1 = $customerRet->fetchArray( SQLITE3_ASSOC ) ) {
                                if($row1['company'] != ""){
                                    echo "<option value='".$row1['company']."'>".$row1['company']."</option>";
                                }
                            }
                        ?>
                        <option value='NONE'>Remove Filter</option>
                    </select>    
                <p><input class='actionJob' type='submit' value='Filter' /></p>
            </form>
        </div>
        <div id="byDriverTab" class="tabcontent">
            <h3>Filter by Driver</h3>
            <form name='assignDriver' action='report.php' method='POST'>
                <input type='hidden' name='f' value='byDriver'>
                <p>
                    <select name='driver' class='formBoxes'>
                        <?php
                            while( $row1 = $driversRet->fetchArray( SQLITE3_ASSOC ) ) {
                                echo "<option value='".$row1['firstName']." ".$row1['lastName']."'>".$row1['firstName']." ".$row1['lastName']."</option>";
                            }
                        ?>
                        <option value='NONE'>Remove Filter</option>
                    </select>    
                <p><input class='actionJob' type='submit' value='Filter' /></p>
            </form>
        </div>
    </div>
</div>
<!-- END OPTION MODAL -->
<!-- START MAIN TABLE -->
<div class='sticky'>
    <table id='header' cellspacing='0'>
        <tr>
            <th colspan='4' style='text-align:center'>
                <a href='dashboard.php'><button class='settingsButton' title='Return to Dashboard'><i class="fa-solid fa-house"></i></button></a>
                <a href='report.php'><button class='settingsButton' title='Reset All Filters'><i class="fa-solid fa-arrows-rotate"></i></button></a>
                <button class='settingsButton' onclick='javascript:showOptionsModal()' title='Filter Results'><i class="fa-solid fa-filter"></i></button>
            </th>
            <th colspan='6' style='text-align:center'>
            <?php
                    if($_SESSION['user'] != ""){
                        echo "<h2>North West Trucks | Report (".$_SESSION['user'].")</h2>";
                    } else {
                        echo "<h2>North West Trucks | Report (LOGGED OUT)</h2>";
                    }
                ?>
            </th>
            <th colspan='2' style='text-align:center'>
                Jobs Total: <button style='height:32px;width:32px;border-radius:50%;border:1px solid white'><strong><?php echo $jobCount; ?></strong></button>
                &nbsp;&nbsp;&nbsp;
                Last Updated at: <span id="updated-at"></span>
            </th>
        </tr>
    </table>
</div>
<table id='deliveries' cellspacing='0'>
    <tr>
        <th>ID</th>
        <th>Depot</th>
        <th>Type</th>
        <th>Company</th>
        <th>Location</th>
        <th>Doc ID</th>
        <th>Added</th>
        <th>Driver</th>
        <th>Assigned</th>
        <th>Status</th>
        <th>Completed</th>
        <th>Signed By</th>
        <th>Signature</th>
    </tr>
    <?php
        while( $mainRow = $mainRet->fetchArray( SQLITE3_ASSOC ) ) {
            if($mainRow['status'] != "Return to Base"){
                echo "<tr>";
                echo "<td>".$mainRow['id']."</td>";
                echo "<td>".$mainRow['depot']."</td>";
                echo "<td>".$mainRow['type']."</td>";
                echo "<td>".$mainRow['company']."</td>";
                echo "<td>".$mainRow['location']."</td>";
                echo "<td>".$mainRow['docid']."</td>";
                echo "<td>".$mainRow['added']."</td>";
                echo "<td>".$mainRow['driver']."</td>";
                echo "<td>".$mainRow['assigned']."</td>";
                echo "<td>".$mainRow['status']."</td>";
                echo "<td>".$mainRow['completed']."</td>";
                echo "<td>".$mainRow['sign_name']."</td>";
                if($mainRow['signature'] != ""){
                    if($mainRow['signature'] == "qrsignature.png"){
                        echo "<td align='center'><img src='signatures/".$mainRow['signature']."' style='width:43px;height:50px;'/></td>";
                    } else {
                        echo "<td><img src='signatures/".$mainRow['signature']."' style='width:100px;height:50px;'/></td>";
                    }
                } else {
                    echo "<td>No Signature</td>";
                }
                echo "</tr>";
            }
        }
    ?>
</table>
<!-- END MAIN TABLE -->
<script>
    const d = new Date();
    var YY = d.getFullYear();
    var MM = d.getMonth();
    if(MM < 10){
        MM = "0" + MM;
    }
    var DD = d.getDate();
    if(DD < 10){
        DD = "0" + DD;
    }
    var HH = d.getHours();
    if(HH < 10){
        HH = "0" + HH;
    }

    var mm = d.getMinutes();
    if(mm < 10){
        mm = "0" + mm;
    }
    const thisUpdate = HH + ":" + mm + " - " + DD + "/" + MM + "/" + YY;
    <?php
        if($_SESSION['depot'] != ""){
            echo "document.getElementById('updated-at').innerHTML = thisUpdate;";
        } else {
            echo "document.getElementById('updated-at').innerHTML = 'Logged Out';";
        }
    ?>
    function showCancel(id){
        let text = "Do you want to cancel the delivery with the ID " + id + "?";
        if(confirm(text) == true){
            let url = "actions/cancel_delivery.php?id="+id;
            location.href = url;
        }
    }
    // OPTIONS MODAL
    var optionsModal = document.getElementById("optionsModal");
    var optionsModalText = document.getElementById("optionsModalText");
    var cancelJob = document.getElementById('cancelJob');
    var assignJob = document.getElementById('assignJob');
    var updateJob = document.getElementById('updateJob');
    var optionsSpan = document.getElementById("optionsClose");
    function showOptionsModal() {
        optionsModal.style.display = "block";
    }     
    optionsSpan.onclick = function() {
        optionsModal.style.display = "none";
        autoRefresh = true;
        console.log("Auto Refresh Active");
    }
    function openOptionsTab(evt, optionName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(optionName).style.display = "block";
        evt.currentTarget.className += " active"; 
    }
    document.getElementById("defaultOpen").click();
</script>
</body>
</html>