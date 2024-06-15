<?php
session_start();
require("data/global.php");
$db = new SQLite3($database);
$todaysDate = date("Y-m-d");
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    switch (htmlspecialchars($_POST['f'])) {
        case 'Log-Out':
            session_unset();
            header('Location: index.php');
            exit();
        case 'Log-In':
            $username = trim(htmlspecialchars($_POST['username']));
            $password = trim(htmlspecialchars($_POST['password']));
            $loginSQL = "SELECT depot,firstName, lastname FROM drivers WHERE username='".$username."' AND password='".$password."'";
            $loginRet = $db->query($loginSQL);
            while( $loginRow = $loginRet->fetchArray( SQLITE3_ASSOC ) ) {
                $_SESSION['depot'] = $loginRow['depot'];
                $_SESSION['driver'] = $loginRow['firstName'] . " " . $loginRow['lastName'];
            }
        default:
            # code...
            break;
    }
}
/* Get the data needed from the database */
/* Get the deliveries data */
$mainSQL = "SELECT * FROM deliveries WHERE (added > '".$todaysDate."' OR status != 'Completed') AND (depot = '".$_SESSION['depot']."' AND depot !='') ORDER BY added ASC";
$mainRet = $db->query($mainSQL);
/* Get the drivers data */
$driversSQL = "SELECT * FROM drivers WHERE depot = '".$_SESSION['depot']."' ORDER BY lastname ASC";
$driversRet = $db->query($driversSQL);
/* Get the status data */
$statusSQL = "SELECT * FROM statuses";
$statusRet = $db->query($statusSQL);
/* Get the depot data */
$depotSQL = "SELECT depot FROM depots";
$depotRet = $db->query($depotSQL);
/* Get the driver rtb status */
$getRtbStatusSQL = "SELECT * FROM deliveries WHERE driver='".$_SESSION['driver']."' AND status='Return to Base'";
$getRtbStatus = $db->query($getRtbStatusSQL);
$activeJobCount = 0;
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
    <title>LOCID | DRIVER DASHBOARD</title>
    <script src="https://kit.fontawesome.com/c63864ee50.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family:arial;
            margin: 0 !important;
            padding: 0 !important;
        }
        .sticky {
            position: -webkit-sticky; /* Safari */
            position: sticky;
            top: 0;
        }
        .bluebar {
            background-color: #00529C;
            color:white;
        }
        .inpick {
            border-left: 12px solid red;
            padding-left:15px;
        }
        .ready {
            border-left: 12px solid orange;
            padding-left:15px;
        }
        .onvan {
            border-left: 12px solid green;
            padding-left:15px;
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
            width:100%;
            border-radius: 2px;
            table-layout: fixed;

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
            overflow-wrap: break-word;
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
        .actionJobDisabled {
            width:200px;
            height:32px;
            background-color: grey;
            color:white;
            border-radius: 5px;
            border:none;
            font-size: 24px;
            cursor: pointer;
        }
        .settingsButton {
            width:48%;
            height:64px;
            background-color: #00529C;
            color:white;
            border-radius: 5px;
            border:1px solid white;
            font-size: 24px;
            cursor: pointer;
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
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 10px;
            width: 90%;
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
        <p id='completeButton' style='text-align:center'><a href='myroute.php?id=thisID'><button class='actionJob'>Complete Job</button></a></p>
        <!-- TAB LINKS -->
        <div class="tab">
            <!--<button class="tablinks" onclick="openOptionsTab(event, 'cancelJobTab')" id="defaultOpen">Cancel Job</button>-->
            <button class="tablinks" onclick="openOptionsTab(event, 'assignJobTab')" id="defaultOpen">Assign Driver</button>
            <button class="tablinks" onclick="openOptionsTab(event, 'updateJobTab')">Update Status</button>
            <button class="tablinks" onclick="openOptionsTab(event, 'cancelJobTab')">Update Doc ID</button>
        </div>
        <div id="assignJobTab" class="tabcontent">
            <h3>Assign Driver</h3>
            <form name='assignDriver' action='actions/update_assigned.php' method='POST'>
            <input type='hidden' name='redirect' value='driverDashboard'>
                <p><input type='hidden' id='assignJob' name='jobID' /></p>
                <p>
                    <select name='driver' class='formBoxes' style='width:200px;height:32px;font-size:24px;'>
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
                <input type='hidden' name='redirect' value='driverDashboard'>
                <p><input type='hidden' id='updateJob' name='jobID' /></p>
                <p>
                    <select name='status' class='formBoxes' style='width:200px;height:32px;font-size:24px;'>
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
        <div id="cancelJobTab" class="tabcontent">
            <h3>Update Doc ID</h3>
            <form name='updateStatus' action='actions/update_delivery.php' method='POST'>
            <input type='hidden' name='redirect' value='driverDashboard'>
                <p><input type='hidden' id='updateDocId' name='jobID' /></p>
                <p>Document ID</p>
                <p><input type='text' name='updateDocID'  style='width:200px;height:32px;font-size:24px;'/></p>
                <p><input class='actionJob' type='submit' value='Update Doc ID' /></p>
            </form>
        </div>
    </div>
</div>
<!-- END OPTION MODAL -->
<!-- START ADD JOB MODAL -->
<div id="addJobModal" class="modal">
    <div class="modal-content" style='height:100%'>
        <span id='addJobClose' class="modalClose">&times;</span>
        <div class='main-box'>
        <center>
        <form name='add-new-delivery' action='actions/add_delivery.php' method='POST'>
            <input type='hidden' name='redirect' value='driverDashboard'>
            <input type='hidden' name='add-new-createdby' value='<?php echo $_SESSION['driver'];?>'>
            <input type='hidden' name='driver' value='<?php echo $_SESSION['driver'];?>'>
            <table width='80%'>
                <tr><th><h2>Add New Job</h2></th></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Depot</td></tr>
                <tr>
                    <td>
                        <select class='textboxes' name='add-new-depot' style='width:100%;height:32px;font-size:24px;'>
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
                    <select class='textboxes' name='add-new-type' style='width:100%;height:32px;font-size:24px;'>
                        <option value='Delivery'>Delivery</option>
                        <option value='Collection'>Collection</option>
                        <option value='ICT'>Meet</option>
                    </select>
                </td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Name</td></tr>
                <tr><td><input class='textboxes' style='width:100%;height:32px;font-size:24px;' name='add-new-company' type='text' required></td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Location</td></tr>
                <tr><td><input class='textboxes' style='width:100%;height:32px;font-size:24px;' name='add-new-location' type='text' required/></td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Document ID</td></tr>
                <tr><td><input class='textboxes' style='width:100%;height:32px;font-size:24px;' name='add-new-docid' type='text'/></td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Status</td></tr>
                <tr><td>
                        <select class='textboxes' name='add-new-status' style='width:100%;height:32px;font-size:24px;'>
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
                <tr><td><textarea class='textboxes' name='add-new-note' type='text' style='width:100%;height:64px;font-size:16px;'></textarea></td></tr>
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
                <tr><td>Name</td></tr>
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
                        echo "<form name='login' action='driver.php' method='POST'>";
                        echo "<input type='hidden' name='f' value='Log-In'/>";
                        echo "<tr><td align='center'>Username:<br /><input type='text' name='username'/></td></tr>";
                        echo "<tr><td align='center'>Password:<br /><input type='text' name='password'/></td></tr>";
                        echo "<tr><td align='center'>&nbsp;</td></tr>";
                        echo "<tr><td align='center'><input type='submit' value='Login' class='actionJob'/></td></tr>";
                        echo "</form>";
                    } else {
                        echo "<form name='login' action='driver.php' method='POST'>";
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
            <th colspan='1' style='text-align:center'>
                    <p style='font-size: 24px'>
                    <?php
                        echo $_SESSION['driver'] . " - Home";
                    ?>
                    </p>
            </th>
        </tr>
        <tr>
            <th colspan='1' style='background-color: white;color: black;text-align:center'>
                <button class='settingsButton' onclick='javascript:showAddJobModal()' title='Add New Job'><i class="fa-solid fa-plus"></i></button>
                <button class='settingsButton' onclick='javascript:showAccountModal()' title='Login / Logout'><i class="fa-solid fa-user"></i></button>
                <a href='driver.php'><button class='settingsButton' title='Refresh Data'><i class="fa-solid fa-arrows-rotate"></i></button></a>
                <a href='myroute.php'><button class='settingsButton' title='View My Route'><i class="fa-solid fa-truck-front"></i></button></a>
            </th>
        </tr>
        <tr>
            <th>
                    <?php
                        while( $rtbRow = $getRtbStatus->fetchArray( SQLITE3_ASSOC ) ) {
                            if($rtbRow['status'] == "Return to Base") {
                                echo "Returning to base at ".$rtbRow['assigned'];
                            }
                        }
                    ?>
            </th>
        </tr>
        <tr>
            <th colspan='1' style='background-color: white;color: black;text-align:right'>
            <i class="fa-solid fa-arrows-rotate"></i> <span id="updated-at"></span>
            </th>
        </tr>
    </table>
</div>
    <table id='deliveries' cellspacing='0'>
        <tr>
            <th>Active Jobs (<span id='activejobs'></span>)</th>
        </tr>
        <?php
            while( $mainRow = $mainRet->fetchArray( SQLITE3_ASSOC ) ) {
                $addedDateTime = date_format(date_create($mainRow['added']),"H:i:s d/m/Y");
                if($mainRow['assigned'] != ''){
                    $assignedDateTime = date_format(date_create($mainRow['assigned']),"H:i:s d/m/Y");
                } else {
                    $assignedDateTime = '';
                }
                switch ($mainRow['status']){
                    case "On Van":
                        if($_SESSION['driver'] == $mainRow['driver']){
                            echo "<tr onclick='javascript:showOptionsModal(".$mainRow['id'].",1);' title='Click for options'>";
                        } else {
                            echo "<tr onclick='javascript:showOptionsModal(".$mainRow['id'].",0);' title='Click for options'>";
                        }
                        echo "<td style='min-height:72px'>";
                        echo "<div class='onvan'>";
                        echo "<div class='bluebar' style='width:90%;padding:10px;'><strong>".$mainRow['company']."</strong> in <strong>" .$mainRow['location']."</strong></div>";
                        echo "<div>Job ID: <strong>".strtoupper($mainRow['id'])."</strong></div>";
                        echo "<div>Created By: <strong>".strtoupper($mainRow['createdby'])."</strong></div>";
                        echo "<div>Job Type: <strong>".strtoupper($mainRow['type'])."</strong></div>";
                        echo "<div>Added at: <strong>".$addedDateTime."</strong></div>";
                        echo "<div>Document No.: <strong>".$mainRow['docid']."</strong></div>";
                        echo "<div>Assigned to: <strong>".$mainRow['driver']."</strong></div>";
                        echo "<div>Assigned at: <strong>".$assignedDateTime."</strong></div>";
                        echo "<div>Status: <strong>".$mainRow['status']."</strong></div>";
                        echo "<div><strong>Note:</strong><br />".$mainRow['note']."<hr /></div>";
                        echo "<div>";
                        echo "</td>";
                        echo "</tr>";
                        $activeJobCount++;
                        break;
                    case "Ready":
                    case "Manual":
                    case "To Be Collected":
                        echo "<tr onclick='javascript:showOptionsModal(".$mainRow['id'].",0);' title='Click for options'>";
                        echo "<td style='min-height:72px'>";
                        echo "<div class='ready'>";
                        echo "<div class='bluebar' style='width:90%;padding:10px;'><strong>".$mainRow['company']."</strong> in <strong>" .$mainRow['location']."</strong></div>";
                        echo "<div>Job ID: <strong>".strtoupper($mainRow['id'])."</strong></div>";
                        echo "<div>Created By: <strong>".strtoupper($mainRow['createdby'])."</strong></div>";
                        echo "<div>Job Type: <strong>".strtoupper($mainRow['type'])."</strong></div>";
                        echo "<div>Added at: <strong>".$addedDateTime."</strong></div>";
                        echo "<div>Document No.: <strong>".$mainRow['docid']."</strong></div>";
                        echo "<div>Assigned to: <strong></strong></div>";
                        echo "<div>Assigned at: <strong></strong></div>";
                        echo "<div>Status: <strong>".$mainRow['status']."</strong></div>";
                        echo "<div><strong>Note:</strong><br />".$mainRow['note']."<hr /></div>";
                        echo "</div>";
                        echo "</td>";
                        echo "</tr>";
                        $activeJobCount++;
                        break;
                    case "To Be Picked":
                    case "Awaiting Parts":
                        echo "<tr onclick='javascript:showOptionsModal(".$mainRow['id'].",0);' title='Click for options'>";
                        echo "<td style='min-height:72px'>";
                        echo "<div class='inpick'>";
                        echo "<div class='bluebar' style='width:90%;padding:10px;'><strong>".$mainRow['company']."</strong> in <strong>" .$mainRow['location']."</strong></div>";
                        echo "<div>Job ID: <strong>".strtoupper($mainRow['id'])."</strong></div>";
                        echo "<div>Created By: <strong>".strtoupper($mainRow['createdby'])."</strong></div>";
                        echo "<div>Job Type: <strong>".strtoupper($mainRow['type'])."</strong></div>";
                        echo "<div>Added at: <strong>".$addedDateTime."</strong></div>";
                        echo "<div>Document No.: <strong>".$mainRow['docid']."</strong></div>";
                        echo "<div>Assigned to: <strong></strong></div>";
                        echo "<div>Assigned at: <strong></strong></div>";
                        echo "<div>Status: <strong>".$mainRow['status']."</strong></div>";
                        echo "<div><strong>Note:</strong><br />".$mainRow['note']."<hr /></div>";
                        echo "</div>";
                        echo "</td>";
                        echo "</tr>";
                        $activeJobCount++;
                        break;
                    default:
                        break;
                }
            }
        ?>
    </table>
<!-- END MAIN TABLE -->
<script>
    <?php
    echo "const activeJobs = ".$activeJobCount.";";
    ?>
    document.getElementById('activejobs').innerText = activeJobs;
    

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
            location.replace("driver.php");
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
    var updateDocId = document.getElementById('updateDocId');
    var optionsSpan = document.getElementById("optionsClose");
    var completeJob = document.getElementById("completeButton");;
    
    function showOptionsModal(id,showButton) {
        optionsModalText.innerText = "You are actioning job number "+id;
        //cancelJob.innerHTML ="<button class='actionJob' onclick='javascript:showCancel("+id+");'>Cancel Job "+id+"</button>";
        assignJob.value = id;
        updateJob.value = id;
        updateDocId.value = id;
        console.log(showButton);
        if(showButton == 1){
            completeJob.innerHTML = "<a href='myroute.php?id="+id+"'><button class='actionJob'>Complete Job</button></a>";
        } else {
            completeJob.innerHTML = "<button class='actionJobDisabled'>Complete Job</button>";
        }
        
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
</script>
</body>
</html>