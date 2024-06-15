<?php
date_default_timezone_set('Europe/London');
$db = new SQLite3('data/northwesttrucks.db');
$tableName = "deliveries";
$id = 14;
$completed = date("Y-m-d H:i:s");
$status = "Completed";
$sql = "UPDATE `".$tableName."` 
SET 
    `status` = '".$status."',
    `completed` = '".$completed."'
WHERE `id` = ".$id;
$ret = $db->exec($sql);
if(!$ret){
    echo $db->lastErrorMsg();
    echo ", Completed Upate failed\n";
} else {
    echo "Completed Update successfull\n";
}