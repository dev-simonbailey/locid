<?php
date_default_timezone_set('Europe/London');
require("../data/global.php");
$db = new SQLite3('../'.$database);
$tableName = "deliveries";
$docid = htmlspecialchars($_POST['updateDocID']);
$id = htmlspecialchars($_POST['jobID']);
$redirectTo = htmlspecialchars($_POST['redirect']);
$sql = "UPDATE `".$tableName."` 
SET 
    `docid` = '".$docid."'
WHERE `id` = ".$id;
$ret = $db->exec($sql);
if(!$ret){
    echo $db->lastErrorMsg();
    echo ", Driver Update failed\n";
} else {
    if($redirectTo == "adminDashboard"){
        header('Location: ../dashboard.php');
    } else {
        header('Location: ../driver.php');
    } 
}