<?php
session_start();
require("data/global.php");
$db = new SQLite3($database);
$todaysDate = date("Y-m-d");

function sanitizeInput($input) {
    return htmlspecialchars($input);
}

switch (sanitizeInput($_POST['f'] ?? '')) {
    case 'Log-Out':
        session_unset();
        header('Location: index.php');
        break;
    case 'Log-In':
        $username = sanitizeInput($_POST['username']);
        $password = sanitizeInput($_POST['password']);
        $loginSQL = "SELECT depot FROM account WHERE username='".$username."' AND password='".$password."'";
        $loginRet = $db->query($loginSQL);
        while( $loginRow = $loginRet->fetchArray( SQLITE3_ASSOC ) ) {
            $_SESSION['depot'] = $loginRow['depot'];
        }
    default:
        break;
}

$mainSQL = "SELECT * FROM deliveries WHERE (added > '".$todaysDate."' OR status != 'Completed') AND (depot = '".$_SESSION['depot']."' AND depot !='') ORDER BY added ASC";
$mainRet = $db->query($mainSQL);

$driversSQL = "SELECT * FROM drivers WHERE depot = '".$_SESSION['depot']."'";
$driversRet = $db->query($driversSQL);

$statusSQL = "SELECT * FROM statuses";
$statusRet = $db->query($statusSQL);

$depotSQL = "SELECT depot FROM depots";
$depotRet = $db->query($depotSQL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Manager - Dashboard</title>
    <script src="https://kit.fontawesome.com/c63864ee50.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
        }
        .ready {
            background-color: burlywood;
        }
        .completed {
            background-color: aquamarine;
        }
        .outfordelivery {
            background-color: yellow;
        }
        .tobepicked {
            background-color: darkorange;
        }
        .awaitingparts {
            background-color: coral;
        }
        #deliveries {
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
            width: 100%;
            border-radius: 2px;
        }
        #deliveries th, #deliveries td {
            border: 1px solid #FFF;
            padding: 8px;
            text-align: left;
        }
        #deliveries th {
            background-color: #04AA6D;
            color: white;
        }
        #deliveries tr:hover {
            background-color: blue;
            color: white;
            cursor: pointer;
        }
        .actionJob, .settingsButton, .addJobButton {
            width: 100%;
            height: 32px;
            background-color: #04AA6D;
            color: white;
            border-radius: 5px;
            border: none;
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
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
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
        .formBoxes, .textboxes {
            width: 100%;
            height: 32px;
        }
        .main-box {
            width: 100%;
            margin: auto;
            border-radius: 10px;
            text-align: center;
        }
        @media only screen and (max-width: 600px) {
            #deliveries th, #deliveries td {
                padding: 12px;
                font-size: 14px;
            }
            .actionJob, .settingsButton, .addJobButton {
                font-size: 18px;
            }
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
            <button class="tablinks" onclick="openOptionsTab(event, 'assignJobTab')"id="defaultOpen">Assign Driver</button>
            <button class="tablinks" onclick="openOptionsTab(event, 'updateJobTab')">Update Status</button>
        </div>
        <div id="assignJobTab" class="tabcontent">
            <h2>Assign Driver</h2>
            <form name='assignDriver' action='actions/update_assigned.php' method='POST'>
                <input type='hidden' name='redirect' value='adminDashboard'>
                <input type='hidden' id='assignJob' name='jobID' />
                <select name='driver' class='formBoxes'>
                    <?php while($row1 = $driversRet->fetchArray(SQLITE3_ASSOC)): ?>
                        <option value="<?= $row1['firstName'] . " " . $row1['lastName'] ?>"><?= $row1['firstName'] . " " . $row1['lastName'] ?></option>
                    <?php endwhile; ?>
                    <option value='None'>None</option>
                </select>    
                <p><input class='actionJob' type='submit' value='Assign Driver' /></p>
            </form>
        </div>
        <div id="updateJobTab" class="tabcontent">
            <h3>Update Status</h3>
            <form name='updateStatus' action='actions/update_status.php' method='POST'>
                <input type='hidden' name='redirect' value='adminDashboard'>
                <input type='hidden' id='updateJob' name='jobID' />
                <select name='status' class='formBoxes'>
                    <?php while($row1 = $statusRet->fetchArray(SQLITE3_ASSOC)): ?>
                        <option value="<?= $row1['status'] ?>"><?= $row1['status'] ?></option>
                    <?php endwhile; ?>
                </select>    
                <p><input class='actionJob' type='submit' value='Update Status' /></p>
            </form>
        </div>
    </div>
</div>
<!-- END OPTION MODAL -->

<!-- START ADD JOB MODAL -->
<div id="addJobModal" class="modal">
    <div class="modal-content">
        <span id='addJobClose' class="modalClose">&times;</span>
        <div class='main-box'>
        <center>
        <form name='add-new-delivery' action='actions/add_delivery.php' method='POST'>
            <input type='hidden' name='redirect' value='adminDashboard'>
            <table width='80%'>
                <tr><th><h2>Add new Delivery</h2></th></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Depot</td></tr>
                <tr>
                    <td>
                        <select class='textboxes' name='add-new-depot'>
                        <?php while($depotRow = $depotRet->fetchArray(SQLITE3_ASSOC)): ?>
                            <option value="<?= $depotRow['depot'] ?>" <?= $depotRow['depot'] == $_SESSION['depot'] ? 'selected' : '' ?>><?= $depotRow['depot'] ?></option>
                        <?php endwhile; ?>
                        </select>
                    </td>
                </tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Job Type</td></tr>
                <tr><td>
                    <select class='textboxes' name='add-new-type'>
                        <option value='New Delivery'>New Delivery</option>
                        <option value='Return'>Return</option>
                        <option value='Failed Collection'>Failed Collection</option>
                        <option value='Failed Delivery'>Failed Delivery</option>
                    </select>
                </td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Reference</td></tr>
                <tr><td><input class='textboxes' name='add-new-reference' placeholder='Reference' required /></td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Client</td></tr>
                <tr><td><input class='textboxes' name='add-new-client' placeholder='Client' required /></td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Address</td></tr>
                <tr><td><input class='textboxes' name='add-new-address' placeholder='Address' required /></td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Postcode</td></tr>
                <tr><td><input class='textboxes' name='add-new-postcode' placeholder='Postcode' required /></td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Added By</td></tr>
                <tr><td><input class='textboxes' name='add-new-addedby' value="<?= $_SESSION['user'] ?>" readonly /></td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td><input class='addJobButton' type='submit' value='Add Job' /></td></tr>
            </table>
        </form>
        </center>
        </div>
    </div>
</div>
<!-- END ADD JOB MODAL -->

<div id="logout">
    <form method='POST' action='adminDashboard.php'>
        <input class='settingsButton' type='submit' name='f' value='Log-Out' />
    </form>
</div>
<div id="container">
    <table id="deliveries">
        <tr>
            <th>Options</th>
            <th>Reference</th>
            <th>Client</th>
            <th>Address</th>
            <th>Postcode</th>
            <th>Depot</th>
            <th>Status</th>
            <th>Type</th>
            <th>Added</th>
            <th>Added By</th>
            <th>Driver</th>
        </tr>
        <?php while($row1 = $mainRet->fetchArray(SQLITE3_ASSOC)): ?>
            <?php 
                $statusClass = strtolower(str_replace(" ", "", $row1['status']));
            ?>
            <tr class="<?= $statusClass ?>">
                <td><button class='optionsButton' onclick="showOptionsModal('<?= $row1['ID'] ?>')">Options</button></td>
                <td><?= $row1['reference'] ?></td>
                <td><?= $row1['client'] ?></td>
                <td><?= $row1['address'] ?></td>
                <td><?= $row1['postcode'] ?></td>
                <td><?= $row1['depot'] ?></td>
                <td><?= $row1['status'] ?></td>
                <td><?= $row1['type'] ?></td>
                <td><?= $row1['added'] ?></td>
                <td><?= $row1['addedBy'] ?></td>
                <td><?= $row1['driver'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
<p><button class='addJobButton' onclick='showAddJobModal()'>Add Delivery</button></p>

<script>
    var optionsModal = document.getElementById("optionsModal");
    var optionsClose = document.getElementById("optionsClose");
    optionsClose.onclick = function() {
        optionsModal.style.display = "none";
    }
    window.onclick = function(event) {
        if (event.target == optionsModal) {
            optionsModal.style.display = "none";
        }
    }
    function showOptionsModal(ID) {
        optionsModal.style.display = "block";
        document.getElementById("optionsModalText").innerHTML = "Options for job: " + ID;
        document.getElementById("assignJob").value = ID;
        document.getElementById("updateJob").value = ID;
        document.getElementById("defaultOpen").click();
    }

    var addJobModal = document.getElementById("addJobModal");
    var addJobClose = document.getElementById("addJobClose");
    addJobClose.onclick = function() {
        addJobModal.style.display = "none";
    }
    window.onclick = function(event) {
        if (event.target == addJobModal) {
            addJobModal.style.display = "none";
        }
    }
    function showAddJobModal() {
        addJobModal.style.display = "block";
    }

    function openOptionsTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
    }

    document.getElementById("defaultOpen").click();
</script>
</body>
</html>
