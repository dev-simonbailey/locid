<?php
session_start();
date_default_timezone_set('Europe/London');
require("data/global.php");
$db = new SQLite3($database);
$todaysDate = date("Y-m-d");
switch (htmlspecialchars($_POST['f'])) {
    case 'Log-Out':
        session_unset();
        header('Location: index.php');
        break;
    case 'Log-In':
        $username = htmlspecialchars($_POST['username']);
        $password = htmlspecialchars($_POST['password']);
        $loginSQL = "SELECT depot FROM account WHERE username='".$username."' AND password='".$password."'";
        $loginRet = $db->query($loginSQL);
        while( $loginRow = $loginRet->fetchArray( SQLITE3_ASSOC ) ) {
            $_SESSION['depot'] = $loginRow['depot'];
            $_SESSION['user'] = $username;
        }
    default:
        # code...
        break;
}
/* Get the data needed from the database */
/* Get the deliveries data */
$mainSQL = "SELECT * FROM deliveries 
            WHERE 
                (added >= '".$todaysDate."' OR status != 'Completed') 
            AND
                (depot = '".$_SESSION['depot']."' AND depot !='') 
            ORDER BY status ASC, added ASC";
$mainRet = $db->query($mainSQL);
$countSQL = "SELECT COUNT(*) as count FROM deliveries 
            WHERE 
                (added >= '".$todaysDate."' OR status != 'Completed') 
            AND
                (depot = '".$_SESSION['depot']."' AND depot !='')
            AND
                (status != 'Return to Base')
            ORDER BY status ASC, added ASC";
$jobCount = $db->querySingle($countSQL);
/* Get the drivers data */
$driversSQL = "SELECT * FROM drivers WHERE depot = '".$_SESSION['depot']."' ORDER BY lastname ASC";
$driversRet = $db->query($driversSQL);
/* Get the status data */
$statusSQL = "SELECT * FROM statuses";
$statusRet = $db->query($statusSQL);
/* Get the depot data */
$depotSQL = "SELECT depot FROM depots";
$depotRet = $db->query($depotSQL);

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
    <title>LOCID | DASHBOARD</title>
    <script src="https://kit.fontawesome.com/c63864ee50.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family:arial;
        }
        .sticky {
            position: -webkit-sticky; /* Safari */
            position: sticky;
            top: 0;
        }
        .ready {
            background-color:burlywood;
        }
        .returntobase {
            background-color:black;
            color:white;
        }
        .completed {
            background-color:#C0C0C0;
            color:#808080;
        }
        .outfordelivery {
            background-color: yellow;
        }
        .tobepicked {
            background-color:darkorange;
        }
        .awaitingparts {
            background-color: coral;
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
            background-color: blue;
            color:white;
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
    </style>
</head>
<body>
<!-- START OPTIONS MODAL -->
<div id="optionsModal" class="modal">
    <!-- CONTENT -->
    <div class="modal-content">
        <span id='optionsClose' class="modalClose">&times;</span>
        <p id='optionsModalText'></p>
        <!-- TAB LINKS -->
        <div class="tab">
            <button class="tablinks" onclick="openOptionsTab(event, 'cancelJobTab')" id="defaultOpen">Cancel Job</button>
            <button class="tablinks" onclick="openOptionsTab(event, 'assignJobTab')">Assign Driver</button>
            <button class="tablinks" onclick="openOptionsTab(event, 'updateJobTab')">Update Status</button>
        </div>
        <!-- TAB CONTENT -->
        <div id="cancelJobTab" class="tabcontent">
            <h2>Cancel Job</h2>
            <p style='text-align:center' id='cancelJob'></p>
        </div>
        <div id="assignJobTab" class="tabcontent">
            <h2>Assign Driver</h2>
            <form name='assignDriver' action='actions/update_assigned.php' method='POST'>
                <input type='hidden' name='redirect' value='adminDashboard'>
                <p><input type='hidden' id='assignJob' name='jobID' /></p>
                <p>
                    <select name='driver' class='formBoxes'>
                        <?php
                            while( $row1 = $driversRet->fetchArray( SQLITE3_ASSOC ) ) {
                                echo "<option value='".$row1['firstName']." ".$row1['lastName']."'>".$row1['firstName']." ".$row1['lastName']."</option>";
                            }
                        ?>
                        <option value='None'>None</option>
                    </select>    
                <p><input class='actionJob' type='submit' value='Assign Driver' /></p>
            </form>
        </div>
        <div id="updateJobTab" class="tabcontent">
            <h3>Update Status</h3>
            <form name='updateStatus' action='actions/update_status.php' method='POST'>
                <input type='hidden' name='redirect' value='adminDashboard'>
                <p><input type='hidden' id='updateJob' name='jobID' /></p>
                <p>
                    <select name='status' class='formBoxes'>
                        <?php
                            while( $row1 = $statusRet->fetchArray( SQLITE3_ASSOC ) ) {
                                if($row1['onaddscreen'] == "TRUE"){
                                    echo "<option value='".$row1['status']."'>".$row1['status']."</option>";
                                }
                            }
                        ?>
                    </select>    
                <p><input class='actionJob' type='submit' value='Update Status' /></p>
            </form>
        </div>
    </div>
</div>
<!-- END OPTION MODAL -->
<!-- START ADD JOB MODAL -->
<div id="addJobModal" class="modal">
    <div class="modal-content" style='height:675px'>
        <span id='addJobClose' class="modalClose">&times;</span>
        <div class='main-box'>
        <center>
        <form name='add-new-delivery' action='actions/add_delivery.php' method='POST'>
            <input type='hidden' name='redirect' value='adminDashboard'>
            <input type='hidden' name='driver' value=''>
            <input type='hidden' name='add-new-createdby' value='<?php echo $_SESSION['user'];?>'>

            <table width='80%'>
                <tr><th><h2>Add New Job</h2></th></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Depot</td></tr>
                <tr>
                    <td>
                        <select class='textboxes' name='add-new-depot'>
                        <?php 
                            while( $depotRow = $depotRet->fetchArray( SQLITE3_ASSOC ) ) {
                                if( $depotRow['depot'] == $_SESSION['depot']){
                                    echo "<option value='".$depotRow['depot']."' selected>".$depotRow['depot']."</option>";
                                } else {
                                    echo "<option value='".$depotRow['depot']."'>".$depotRow['depot']."</option>";
                                }
                            }
                        ?>
                        </select>
                    </td>
                </tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Job Type</td></tr>
                <tr><td>
                    <select class='textboxes' name='add-new-type'>
                        <option value='Delivery'>Delivery</option>
                        <option value='Collection'>Collection</option>
                        <option value='ICT'>Meet</option>
                    </select>
                </td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Name</td></tr>
                <tr><td><input class='textboxes' name='add-new-company' type='text' required></td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Location</td></tr>
                <tr><td><input class='textboxes' name='add-new-location' type='text' required/></td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Document ID</td></tr>
                <tr><td><input class='textboxes' name='add-new-docid' type='text'/></td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Status</td></tr>
                <tr><td>
                        <select class='textboxes' name='add-new-status'>\
                            <?php
                                while( $statusRow1 = $statusRet->fetchArray( SQLITE3_ASSOC ) ) {
                                    if($statusRow1['onaddscreen'] == "TRUE"){
                                        if($statusRow1['status'] == "To Be Picked"){
                                            echo "<option value='".$statusRow1['status']."' selected>".$statusRow1['status']."</option>";
                                        } else {
                                            echo "<option value='".$statusRow1['status']."'>".$statusRow1['status']."</option>";
                                        }  
                                    }
                                }
                            ?>
                        </select>
                    </td>
                 </tr>
                 <tr><td>&nbsp;</td></tr>
                <tr><td> Note</td></tr>
                <tr><td><textarea class='textboxes' name='add-new-note' type='text'></textarea></td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td><input class='addJobButton' type='submit' value='Add Job'></td></tr>
            </table>
        </form>
    </center>
    </div>
    </div>
</div>
<!-- END ADD JOB MODAL -->
<!-- START SETTINGS MODAL -->
<div id="settingsModal" class="modal">
    <div class="modal-content">
        <span id='settingsClose' class="modalClose">&times;</span>
        <div class='main-box'>
        <center>
        <form name='update-settings' action='update_settings.php' method='POST'>
            <table width='80%'>
                <tr><th><h2>Update Settings</h2></th></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Depots</td></tr>
                <tr>
                    <td>
                        <select class='textboxes' name='depot'>
                        <?php 
                            while( $depotRow = $depotRet->fetchArray( SQLITE3_ASSOC ) ) {
                                if( $depotRow['depot'] == $_SESSION['depot']){
                                    echo "<option value='".$depotRow['depot']."' selected>".$depotRow['depot']."</option>";
                                } else {
                                    echo "<option value='".$depotRow['depot']."'>".$depotRow['depot']."</option>";
                                }
                            }
                        ?>
                            <option value='Woodwards'>Woodwards</option>
                        </select>
                    </td>
                </tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Job Type</td></tr>
                <tr><td>
                    <select class='textboxes' name='type'>
                        <option value='Delivery'>Delivery</option>
                        <option value='Collection'>Collection</option>
                        <option value='ICT'>Meet</option>
                    </select>
                </td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Customer/Supplier</td></tr>
                <tr><td><input class='textboxes' name='company' type='text'></td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Location</td></tr>
                <tr><td><input class='textboxes' name='location' type='text'/></td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Document ID</td></tr>
                <tr><td><input class='textboxes' name='docid' type='text'/></td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Status</td></tr>
                <tr><td>
                        <select class='textboxes' name='status'>
                        <?php
                                while( $statusRow1 = $statusRet->fetchArray( SQLITE3_ASSOC ) ) {
                                    echo "<option value='".$statusRow1['status']."'>".$statusRow1['status']."</option>";     
                                }
                            ?>
                        </select>
                    </td>
                 </tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td><input class='addJobButton' type='submit' value='Update Settings'></td></tr>
            </table>
        </form>
    </center>
    </div>
    </div>
</div>
<!-- END SETTINGS MODAL -->
<!-- START ACCOUNT MODAL -->
<div id="accountModal" class="modal">
    <div class="modal-content">
        <span id='accountClose' class="modalClose">&times;</span>
        <?php 
            if($_SESSION['depot'] == ""){
                echo "<div class='main-box' style='height:250px;'>";
            } else {
                echo "<div class='main-box' style='height:150px;'>";
            }
            ?>
        <center>
            <table width='80%'> 
                <tr><th><h2>Account</h2></th></tr>
                <?php
                    if($_SESSION['depot'] == ""){
                        echo "<form name='login' action='dashboard.php' method='POST'>";
                        echo "<input type='hidden' name='f' value='Log-In'/>";
                        echo "<tr><td align='center'>Username:<br /><input type='text' name='username'/></td></tr>";
                        echo "<tr><td align='center'>Password:<br /><input type='text' name='password'/></td></tr>";
                        echo "<tr><td align='center'>&nbsp;</td></tr>";
                        echo "<tr><td align='center'><input type='submit' value='Login' class='actionJob'/></td></tr>";
                        echo "</form>";
                    } else {
                        echo "<form name='login' action='dashboard.php' method='POST'>";
                        echo "<input type='hidden' name='f' value='Log-Out'/>";
                        echo "<tr><td align='center'><input type='submit' value='Logout' class='actionJob'/></td></tr>";
                        echo "</form>";
                    }
                   ?>
            </table>
    </center>
    </div>
    </div>
</div>
<!-- END ACCOUNT MODAL -->
<!-- START MAIN TABLE -->
<div class='sticky'>
<table id='header' cellspacing='0'>
        <tr>
            <th colspan='4' style='text-align:center'>
                <button class='settingsButton' onclick='javascript:showAddJobModal()' title='Add Job'><i class="fa-solid fa-plus"></i></button>
                <a href='dashboard.php'><button class='settingsButton' title='Refresh Data'><i class="fa-solid fa-arrows-rotate"></i></button></a>
                <a href='report.php'><button class='settingsButton' title='View Completed Reports'><i class="fa-solid fa-chart-bar"></i></button></a>
                <button class='settingsButton' onclick='javascript:showAccountModal()' title='Login / Log Out'><i class="fa-solid fa-user"></i></button>
            </th>
            <th colspan='6' style='text-align:center'>
            <?php
                    if($_SESSION['user'] != ""){
                        echo "<h2>North West Trucks | Dashboard (".$_SESSION['user'].")</h2>";
                    } else {
                        echo "<h2>North West Trucks | Dashboard (LOGGED OUT)</h2>";
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
    <table id='header' cellspacing='0'>
        <?php
        while( $mainRow = $mainRet->fetchArray( SQLITE3_ASSOC ) ) {
            if($mainRow['status'] == "Return to Base") {
                $date1 = new DateTime($mainRow['added']);
                $date2 = new DateTime($todaysDate);
                $returnDate = date_format(date_create($mainRow['assigned']),"H:i:s d/m/Y");
                if($date1 >= $date2) {
                    echo "<tr class='returntobase'>";
                    echo "<td colspan='12'>".$mainRow['driver']." is returning to base (".$returnDate.")</td>";
                    echo "</tr>";
                }
            }
        }
        ?>
    </table>
</div>
    <table id='deliveries' cellspacing='0'>
        <tr>
            <th>ID</th>
            <th>Depot</th>
            <th>Created By</th>
            <th>Type</th>
            <th>Name</th>
            <th>Location</th>
            <th>Doc ID</th>
            <th>Added</th>
            <th>Driver</th>
            <th>Assigned</th>
            <th>Status</th>
            <th>Completed</th>
            <th>Note</th>
        </tr>
        <?php
            while( $mainRow = $mainRet->fetchArray( SQLITE3_ASSOC ) ) {
                $addedDateTime = date_format(date_create($mainRow['added']),"H:i:s d/m/Y");
                if($mainRow['assigned'] != ''){
                    $assignedDateTime = date_format(date_create($mainRow['assigned']),"H:i:s d/m/Y");
                } else {
                    $assignedDateTime = '';
                }
                if($mainRow['completed'] != ''){
                    $completedDateTime = date_format(date_create($mainRow['completed']),"H:i:s d/m/Y");
                } else {
                    $completedDateTime = '';
                }
 //               if($mainRow['status'] == "Return to Base") {
 //                   $date1 = new DateTime($mainRow['added']);
 //                   $date2 = new DateTime($todaysDate);
 //                   if($date1 >= $date2) {
 //                       echo "<tr class='returntobase'>";
 //                       echo "<td colspan='12'>".$mainRow['driver']." is returning to base (".$mainRow['assigned'].")</td>";
 //                       echo "</tr>";
 //                   }
 //               } else {
                    switch ($mainRow['status']){
                        case "On Van":
                            echo "<tr class='outfordelivery' onclick='javascript:showOptionsModal(".$mainRow['id'].");' title='Click for options'>";
                            break;
                        case "Completed":
                            //echo "<tr class='completed' onclick='javascript:showOptionsModal(".$mainRow['id'].");' title='Click for options'>";
                            echo "<tr class='completed'>";
                            break;
                        case "Ready":
                        case "Manual":
                            echo "<tr class='ready' onclick='javascript:showOptionsModal(".$mainRow['id'].");' title='Click for options'>";
                            break;
                        case "To Be Picked":
                            echo "<tr class='tobepicked' onclick='javascript:showOptionsModal(".$mainRow['id'].");' title='Click for options'>";
                            break;
                        case "To Be Collected":
                            echo "<tr class='ready' onclick='javascript:showOptionsModal(".$mainRow['id'].");' title='Click for options'>";
                            break;
                        case "Awaiting Parts":
                            echo "<tr class='awaitingparts' onclick='javascript:showOptionsModal(".$mainRow['id'].");' title='Click for options'>";
                            break;
                        case "Return to Base":
                            break;
                        default:
                            break;
                        echo "<tr>";
                    }
                if($mainRow['status'] != "Return to Base"){
                    echo "<td>".$mainRow['id']."</td>";
                    echo "<td>".$mainRow['depot']."</td>";
                    echo "<td>".$mainRow['createdby']."</td>";
                    echo "<td>".$mainRow['type']."</td>";
                    echo "<td>".$mainRow['company']."</td>";
                    echo "<td>".$mainRow['location']."</td>";
                    echo "<td>".$mainRow['docid']."</td>";
                    echo "<td>".$addedDateTime."</td>";
                    echo "<td>".$mainRow['driver']."</td>";
                    echo "<td>".$assignedDateTime."</td>";
                    echo "<td>".$mainRow['status']."</td>";
                    echo "<td>".$completedDateTime."</td>";
                    echo "<td>".$mainRow['note']."</td>";
                    echo "</tr>";
                }

 //               }

            }
        ?>
    </table>
<!-- END MAIN TABLE -->
<script>
    const d = new Date();
    var YY = d.getFullYear();
    var MM = d.getMonth()+1;
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
    //TODO Remember to switch this back on!!!!!
    <?php
        if($_SESSION['depot'] != ""){
            echo "var autoRefresh = true;";
            echo "var refreshPeriod = 5 * 60 * 1000;";
        } else{
            echo "var autoRefresh = false;";
        }
    ?>
    if(autoRefresh){
        setInterval(pageRefresh, refreshPeriod);
        console.log("Auto Refresh Active");
    } else {
        console.log("Auto Refresh Deactivated");
    }
    function pageRefresh (){
        if(autoRefresh){
            location.replace("dashboard.php");
        }
    }
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
    function showOptionsModal(id) {
        optionsModalText.innerText = "You are actioning job number "+id;
        cancelJob.innerHTML ="<button class='actionJob' onclick='javascript:showCancel("+id+");'>Cancel Job "+id+"</button>";
        assignJob.value = id;
        updateJob.value = id;
        optionsModal.style.display = "block";
        autoRefresh = false;
        console.log("Auto Refresh Paused");
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

    // ADD JOB MODAL
    var addJobModal = document.getElementById("addJobModal");
    var AddJobModalText = document.getElementById("addJobModalText");
    var addJobClose = document.getElementById("addJobClose");
    function showAddJobModal() {
        addJobModal.style.display = "block";
        autoRefresh = false;
        console.log("Auto Refresh Paused");
    }     
    addJobClose.onclick = function() {
        addJobModal.style.display = "none";
        autoRefresh = true;
        console.log("Auto Refresh Active");
    }

    // SETTINGS MODAL - NOT IN USE AT PRESENT
    var settingsModal = document.getElementById("settingsModal");
    var settingsClose = document.getElementById("settingsClose");
    function showSettingsModal() {
        settingsModal.style.display = "block";
        autoRefresh = false;
        console.log("Auto Refresh Paused");
    }     
    settingsClose.onclick = function() {
        settingsModal.style.display = "none";
        autoRefresh = true;
        console.log("Auto Refresh Active");
    }

    // ACCOUNT MODAL
    var accountModal = document.getElementById("accountModal");
    var accountClose = document.getElementById("accountClose");
    function showAccountModal() {
        accountModal.style.display = "block";
        autoRefresh = false;
        console.log("Auto Refresh Paused");
    }     
    accountClose.onclick = function() {
        accountModal.style.display = "none";
        autoRefresh = true;
        console.log("Auto Refresh Active");
    }
    window.scrollTo(0,document.body.scrollHeight);
</script>
</body>
</html>