<?php
/* DONE */
date_default_timezone_set('Europe/London');
require("../data/global.php");
$db = new SQLite3('../'.$database);
$tableName = "deliveries";
$assignDriverDriver = htmlspecialchars($_POST['driver']);
$assignDriverId = htmlspecialchars($_POST['jobID']);
$redirectTo = htmlspecialchars($_POST['redirect']);
$date = date("Y-m-d H:i:s");
$assignDriverStatus = "On Van";
if($assignDriverDriver == "None"){
    $assign = false;
} else {
    $assign = true;
}
if($assign == true){
    $assignDriverSQL = "UPDATE `".$tableName."` 
    SET 
        `driver` = '".$assignDriverDriver."', 
        `assigned` = '".$date."',
        `status` = '".$assignDriverStatus."'
    WHERE `id` = ".$assignDriverId;
    $delRtbSQL = "DELETE FROM deliveries WHERE driver='".$assignDriverDriver."' AND status='Return to Base'";
    $deleteRTB = $db->exec($delRtbSQL);
} else {
    $assignDriverSQL = "UPDATE `".$tableName."` 
    SET 
        `driver` = '', 
        `assigned` = '',
        `status` = 'Ready'
    WHERE `id` = ".$assignDriverId;
}
$assignDriverRet = $db->exec($assignDriverSQL);
if(!$assignDriverRet){
    echo $db->lastErrorMsg();
    echo ", Driver Update failed\n";
} else {
    if($redirectTo == "adminDashboard"){
        header('Location: ../dashboard.php');
    } else {
        header('Location: ../driver.php');
    } 
}