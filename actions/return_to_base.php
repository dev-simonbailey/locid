<?php
/* DONE */
date_default_timezone_set('Europe/London');
require("../data/global.php");
$db = new SQLite3('../'.$database);
$tableName = "deliveries";
$rtbDepot = htmlspecialchars($_POST['rtb-depot']);
$rtbDriver = htmlspecialchars($_POST['rtb-driver']);
$rtbAdded = date('Y-m-d H:i:s');

$delRtbSQL = "DELETE FROM deliveries WHERE driver='".$rtbDriver."' AND status='Return to Base'";

$deleteRTB = $db->exec($delRtbSQL);

if(!$deleteRTB){
    echo $db->lastErrorMsg();
    echo ", RTB DELETE failed\n";
}

$addNewSQL = "INSERT INTO deliveries (
                                    'depot',
                                    'type',
                                    'company',
                                    'location',
                                    'docid',
                                    'added',
                                    'driver',
                                    'assigned',
                                    'status',
                                    'completed'
                                    )
                            VALUES (
                                    '".$rtbDepot."',
                                    '',
                                    '',
                                    '',
                                    '',
                                    '".$rtbAdded."',
                                    '".$rtbDriver."',
                                    '".$rtbAdded."',
                                    'Return to Base',
                                    ''
                                    );";
$addNewRet = $db->exec($addNewSQL);
if(!$addNewRet){
    echo $db->lastErrorMsg();
    echo ", Add Delivery failed\n";
} else {
    if($redirectTo == "adminDashboard"){
        header('Location: ../dashboard.php');
    } else {
        header('Location: ../driver.php');
    } 
}
?>