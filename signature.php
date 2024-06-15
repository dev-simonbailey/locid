<?php
session_start();
date_default_timezone_set('Europe/London');
require("data/global.php");
$db = new SQLite3($database);
$tableName = "deliveries";
$completed = date("Y-m-d H:i:s");
$status = "Completed";
$db_updated = false;
$image_saved = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the base64-encoded signature
    $signature = $_POST['signature'];
    $sign_name = $_POST['signName'];
    $job_id    = $_POST['jobID'];

    // Remove the data URL prefix (if present)
    $signature = str_replace('data:image/png;base64,', '', $signature);
    $signature = str_replace(' ', '+', $signature);

    // Decode the base64-encoded string
    $decoded_signature = base64_decode($signature);

    $image_name = 'signature' .time() . '.png';

    // Define the path to save the image
    $file_path = 'signatures/'.$image_name;

    // Save to the db
    $sql = "UPDATE `".$tableName."` 
            SET 
                `status` = '".$status."',
                `completed` = '".$completed."',
                `sign_name` = '".$sign_name."',
                `signature` = '".$image_name."'
            WHERE `id` = ".$job_id;

    $ret = $db->exec($sql);
    if(!$ret){
        //echo $db->lastErrorMsg();
        $db_updated = false;
    } else {
        $db_updated = true;
        //echo "db_updated = true<br />";
    }
    // Save the decoded image to the file path
    if (file_put_contents($file_path, $decoded_signature)) {
        $image_saved = true;
        //echo "image_saved = true<br />";
    } else {
        $image_saved = false;
    }
    if($db_updated && $image_saved){
        header('Location: myroute.php');
    }
} else {
    //echo "Invalid request method.";
}
?>
