<?php
session_start();
require("data/global.php");
$driver = $_SESSION['driver'];
$directCompleteID = $_GET['id'];
$db = new SQLite3($database);
$todaysDate = date("Y-m-d");
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    switch (htmlspecialchars($_POST['f'])) {
        case 'Log-Out':
            session_unset();
            header('Location: index.php');
            break;
        case 'Log-In':
            $username = trim(htmlspecialchars($_POST['username']));
            $password = trim(htmlspecialchars($_POST['password']));
            $loginSQL = "SELECT depot FROM account WHERE username='".$username."' AND password='".$password."'";
            $loginRet = $db->query($loginSQL);
            while( $loginRow = $loginRet->fetchArray( SQLITE3_ASSOC ) ) {
                $_SESSION['depot'] = $loginRow['depot'];
            }
        default:
            # code...
            break;
    }
}
/* Get the data needed from the database */
/* Get the deliveries data  - This needs to be altered so that completed drop off the my route screen*/
$mainSQL = "SELECT * FROM deliveries WHERE (added > '".$todaysDate."' OR status != 'Completed') AND (depot = '".$_SESSION['depot']."' AND depot !='') AND (driver = '".$driver."') AND (status != 'Completed') ORDER BY added ASC";
$mainRet = $db->query($mainSQL);
/* Get the drivers data */
$driversSQL = "SELECT * FROM drivers WHERE depot = '".$_SESSION['depot']."'";
$driversRet = $db->query($driversSQL);
/* Get the status data */
$statusSQL = "SELECT * FROM statuses";
$statusRet = $db->query($statusSQL);
/* Get the depot data */
$depotSQL = "SELECT depot FROM depots";
$depotRet = $db->query($depotSQL);
$activeJobCount = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.9">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <title>LOCID | VAN</title>
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
        tbody tr:nth-child(odd) {
            background-color:#9fd0fc ;
            color: #000;
        }
        tbody tr:nth-child(even) {
            background-color:#ddd;
            color: #000;
        }
        .bluebar {
            background-color: #00529C;
            color:white;
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
            width:300px;
            height:64px;
            background-color: #00529C;
            color:white;
            border-radius: 5px;
            border:none;
            font-size: 24px;
            cursor: pointer;
        }
        .settingsButton {
            width:100%;
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
            background-color: #04AA6D;
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
        canvas {
            border: 1px solid #000;
            border-radius: 5px;
        }
        #signature-pad {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<!-- START OPTIONS MODAL -->
<div id="optionsModal" class="modal">
    <div class="modal-content">
        <span id='optionsClose' class="modalClose">&times;</span>
        <p id='optionsModalText'></p>
        <div class="tab">
            <button class="tablinks" onclick="openOptionsTab(event, 'completeJobTab')"id="defaultOpen">Complete Job</button>
        </div>
        <div id="completeJobTab" class="tabcontent">
            <form id="signature-form" action="signature.php" method="post">
                <input type='hidden' name='redirect' value='adminDashboard'>
                <p><input type='hidden' id='assignJob' name='jobID' /></p>
                <center>
                    <table style='background-color:white;border:none'>
                        <tr style='border:none'>
                            <td style='background-color:white;border:none'>
                                Name:
                            </td>  
                        </tr>
                        <tr style='border:none'>
                            <td style='background-color:white;border:none'>
                                <input type='text' name='signName' style='width:300px;height:48px;font-size:32px;'/>
                            </td>
                        </tr>
                        <tr style='border:none'>
                            <td style='background-color:white;border:none'>
                                Signature:
                            </td>
                        </tr>
                        <tr style='border:none'>
                            <td style='background-color:white;border:none'>
                                <div id="signature-pad">
                                    <canvas id="canvas" width="300" height="150"></canvas>
                                </div>
                            </td>
                        </tr>
                        <tr style='border:none'>
                            <td style='background-color:white;border:none'>
                                <input type="hidden" name="signature" id="signature-input">
                            </td>
                        </tr>
                    </table>
                </center>
                <p><input class='actionJob' type='submit' value='Complete job' /></p>
                <p><button class='actionJob' id="clear">Clear</button></p>
            </form>
        </div>
    </div>
</div>
<!-- END OPTION MODAL -->
<!-- START MAIN TABLE -->
<div class='sticky'>
    <table id='deliveries' cellspacing='0'>
        <tr>
            <th colspan='6' style='text-align:center'>
                    <p style='font-size: 24px'>
                    <?php
                        echo $_SESSION['driver'] . " - Van";
                    ?>
                    </p>
            </th>
        </tr>
        <tr>
            <th colspan='6' style='background-color: white;color: black'>
                <a href='driver.php'><button class='settingsButton'><i class="fa-solid fa-house"></i></button></a>
                <form name='returnTobase' action='actions/return_to_base.php' method='POST'>
                <input type='hidden' name='redirect' value='driverDashboard'>
                <input type='hidden' name='rtb-depot' value='<?php echo $_SESSION['depot'];?>'>
                <input type='hidden' name='rtb-driver' value='<?php echo $_SESSION['driver'];?>'>
                <p><button class='settingsButton' type='submit' value='Update Status' ><i class="fa-solid fa-plane-arrival"></i></button></p>
            </form>
                <a href='myroute.php'><button class='settingsButton'><i class="fa-solid fa-arrows-rotate"></i></button></a>
            </th>
        </tr>
        <tr>
            <th colspan='6' style='background-color: white;color: black;text-align:right'>
                    <i class="fa-solid fa-arrows-rotate"></i> <span id="updated-at"><?php echo $refreshDate;?></span>
            </th>
        </tr>
    </table>
</div>
    <table id='deliveries' cellspacing='0'>
        <tr>
            <th>MY JOBS (<span id='activejobs'></span>)</th>
        </tr>
        <?php
            while( $mainRow = $mainRet->fetchArray( SQLITE3_ASSOC ) ) {
                $addedDateTime = date_format(date_create($mainRow['added']),"H:i:s d/m/Y");
                if($mainRow['assigned'] != ''){
                    $assignedDateTime = date_format(date_create($mainRow['assigned']),"H:i:s d/m/Y");
                } else {
                    $assignedDateTime = '';
                }
                if($mainRow['status'] != "Return to Base"){
                    echo "<tr onclick='javascript:showOptionsModal(".$mainRow['id'].");' title='Click for options'>";
                    echo "<td style='min-height:72px'>";
                    echo "<div class='bluebar' style='width:95%;padding:10px;'><strong>".$mainRow['company']."</strong> in <strong>" .$mainRow['location']."</strong></div>";
                    echo "<div>Job ID: <strong>".strtoupper($mainRow['id'])."</strong></div>";
                    echo "<div>Created By: <strong>".strtoupper($mainRow['createdby'])."</strong></div>";
                    echo "<div>Job Type: <strong>".strtoupper($mainRow['type'])."</strong></div>";
                    echo "<div>Added at: <strong>".$addedDateTime."</strong></div>";
                    echo "<div>Document No.: <strong>".$mainRow['docid']."</strong></div>";
                    echo "<div>Assigned to: <strong>".$mainRow['driver']."</strong></div>";
                    echo "<div>Assigned at: <strong>".$assignedDateTime."</strong></div>";
                    echo "<div>Status: <strong>".$mainRow['status']."</strong></div>";
                    echo "<div><strong>Note:</strong><br />".$mainRow['note']."<hr /></div>";
                    echo "</td>";
                    echo "</tr>";
                    $activeJobCount++;
                }
            }
        ?>
    </table>
<!-- END MAIN TABLE -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const canvas = document.getElementById('canvas');
        const signaturePad = new SignaturePad(canvas);
        const clearButton = document.getElementById('clear');
        const form = document.getElementById('signature-form');
        const signatureInput = document.getElementById('signature-input');

        form.addEventListener('submit', (event) => {
            if (signaturePad.isEmpty()) {
                alert("A signature is required.");
                event.preventDefault();
            } else {
                const dataUrl = signaturePad.toDataURL();
                signatureInput.value = dataUrl;
            }
        });

        clearButton.addEventListener('click', () => {
            signaturePad.clear();
        });
    });
</script>
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
            location.replace("myroute.php");
        }
    }
    <?php
        if($_SESSION['depot'] != ""){
            echo "document.getElementById('updated-at').innerHTML = thisUpdate;";
        } else {
            echo "document.getElementById('updated-at').innerHTML = 'Logged Out';";
        }
    ?>
    // OPTIONS MODAL
    var optionsModal = document.getElementById("optionsModal");
    var optionsModalText = document.getElementById("optionsModalText");
    var cancelJob = document.getElementById('cancelJob');
    var assignJob = document.getElementById('assignJob');
    var updateJob = document.getElementById('updateJob');
    var optionsSpan = document.getElementById("optionsClose");
    function showOptionsModal(id) {
        optionsModalText.innerText = "You are completing job number "+id;
        assignJob.value = id;
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

    <?php
    if($directCompleteID != ""){
        echo "showOptionsModal(".$directCompleteID.");";
    }
    ?>
</script>
<script>
var newCount = <?php echo $activeJobCount;?>;
var currentCount = getCookie(activejobs);

if(newCount > currentCount){
    document.getElementById('activejobs').innerText = newCount
    beep();
    setCookie(activejobs,newCount);
} else {
    document.getElementById('activejobs').innerText = newCount
    setCookie(activejobs,newCount);
}


function setCookie(cname, cvalue) {
  document.cookie = cname + "=" + cvalue + ";path=/";
}

function getCookie(cname) {
  let name = cname + "=";
  let ca = document.cookie.split(';');
  for(let i = 0; i < ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function beep() {
    var snd = new Audio("data:audio/wav;base64,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qJrx4KOQjahZxWbcZgztj2c49nKmkId44S71j0c8eV9yDK6uPRzx5X18eDvjvQ6yKo9ZSS6l//8elePK/Lf//IInrOF/FvDoADYAGBMGb7FtErm5MXMlmPAJQVgWta7Zx2go+8xJ0UiCb8LHHdftWyLJE0QIAIsI+UbXu67dZMjmgDGCGl1H+vpF4NSDckSIkk7Vd+sxEhBQMRU8j/12UIRhzSaUdQ+rQU5kGeFxm+hb1oh6pWWmv3uvmReDl0UnvtapVaIzo1jZbf/pD6ElLqSX+rUmOQNpJFa/r+sa4e/pBlAABoAAAAA3CUgShLdGIxsY7AUABPRrgCABdDuQ5GC7DqPQCgbbJUAoRSUj+NIEig0YfyWUho1VBBBA//uQZB4ABZx5zfMakeAAAAmwAAAAF5F3P0w9GtAAACfAAAAAwLhMDmAYWMgVEG1U0FIGCBgXBXAtfMH10000EEEEEECUBYln03TTTdNBDZopopYvrTTdNa325mImNg3TTPV9q3pmY0xoO6bv3r00y+IDGid/9aaaZTGMuj9mpu9Mpio1dXrr5HERTZSmqU36A3CumzN/9Robv/Xx4v9ijkSRSNLQhAWumap82WRSBUqXStV/YcS+XVLnSS+WLDroqArFkMEsAS+eWmrUzrO0oEmE40RlMZ5+ODIkAyKAGUwZ3mVKmcamcJnMW26MRPgUw6j+LkhyHGVGYjSUUKNpuJUQoOIAyDvEyG8S5yfK6dhZc0Tx1KI/gviKL6qvvFs1+bWtaz58uUNnryq6kt5RzOCkPWlVqVX2a/EEBUdU1KrXLf40GoiiFXK///qpoiDXrOgqDR38JB0bw7SoL+ZB9o1RCkQjQ2CBYZKd/+VJxZRRZlqSkKiws0WFxUyCwsKiMy7hUVFhIaCrNQsKkTIsLivwKKigsj8XYlwt/WKi2N4d//uQRCSAAjURNIHpMZBGYiaQPSYyAAABLAAAAAAAACWAAAAApUF/Mg+0aohSIRobBAsMlO//Kk4soosy1JSFRYWaLC4qZBYWFRGZdwqKiwkNBVmoWFSJkWFxX4FFRQWR+LsS4W/rFRb/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////VEFHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAU291bmRib3kuZGUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMjAwNGh0dHA6Ly93d3cuc291bmRib3kuZGUAAAAAAAAAACU=");  
    snd.play();
}
</script>
</body>
</html>