<?php
/* DONE */
require("../data/global.php");
$db = new SQLite3('../'.$database);
$tableName = "deliveries";
$id = htmlspecialchars($_GET['id']);;
$sql = "DELETE FROM `".$tableName."` WHERE `id` = ".$id;
$ret = $db->exec($sql);
if(!$ret){
    echo $db->lastErrorMsg();
    echo ", Driver Upate failed\n";
} else {
    header('Location: ../dashboard.php');
}
?>