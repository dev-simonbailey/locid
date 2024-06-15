<?php
session_start();
/* DONE */
date_default_timezone_set('Europe/London');
$db = new SQLite3('data/locid.db');
$user = htmlspecialchars($_POST['user']);
$pass = htmlspecialchars($_POST['pass']);
$clientSQL = "SELECT client FROM account WHERE `username` = '".$user."' AND `password` = '".$pass."'";
$clientRET = $db->query($clientSQL);

if ($clientRET) {
    while( $mainRow = $clientRET->fetchArray( SQLITE3_ASSOC ) ) {
        $_SESSION['client'] = $mainRow['client'];
        //echo $mainRow['client'];
    }
}

echo $_SESSION['client'];

$client_db = new SQLite3('data/'.$_SESSION['client']."db");
$cSQL = "SELECT client FROM test";
$cRET = $db->query($cSQL);
if ($cRET) {
    while( $cRow = $cRET->fetchArray( SQLITE3_ASSOC ) ) {
        echo $cRow['client'];
    }
}

exit();


$tableName = "deliveries";
$updateStatusStatus = htmlspecialchars($_POST['status']);
$updateStatusId = htmlspecialchars($_POST['jobID']);
$redirectTo = htmlspecialchars($_POST['redirect']);
$updateSQL = "UPDATE `".$tableName."` 
        SET 
        `status` = '".$updateStatusStatus."'
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