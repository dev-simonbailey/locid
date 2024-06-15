<?php
/* DONE */
date_default_timezone_set('Europe/London');
require("../data/global.php");
$db = new SQLite3('../'.$database);
$tableName = "deliveries";
$updateStatusStatus = htmlspecialchars($_POST['status']);
$updateStatusId = htmlspecialchars($_POST['jobID']);
$redirectTo = htmlspecialchars($_POST['redirect']);
$updateSQL = "UPDATE `".$tableName."` 
        SET 
        `status` = '".$updateStatusStatus."',
        `assigned` = ''
        WHERE `id` = ".$updateStatusId;
$updateStatusRet = $db->exec($updateSQL);
if(!$updateStatusRet){
    echo $db->lastErrorMsg();
    echo ", Status Upate failed\n";
} else {
    if($redirectTo == "adminDashboard"){
        header('Location: ../dashboard.php');
    } else {
        header('Location: ../driver.php');
    } 
}