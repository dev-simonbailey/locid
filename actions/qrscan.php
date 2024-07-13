<?php
session_start();
date_default_timezone_set('Europe/London');
require("../data/global.php");
$db = new SQLite3('../'.$database);
$tableName = "deliveries";
$completed = date("Y-m-d H:i:s");
$status = "Completed";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sign_name  = $_POST['scanName'];
    $job_id     = $_POST['scanJobId'];

    // Save to the db
    $sql = "UPDATE `".$tableName."` SET `status` = '".$status."',`completed` = '".$completed."',`sign_name` = '".$sign_name."',`signature` = 'qrsignature.png' WHERE `id` IN(".$job_id.")";

    $ret = $db->exec($sql);
    if(!$ret){
        echo $db->lastErrorMsg();
    } else {
        header('Location: ../myroute.php');
    }
}