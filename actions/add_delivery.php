<?php
/* DONE */
date_default_timezone_set('Europe/London');
require("../data/global.php");
$db = new SQLite3('../'.$database);
$tableName = "deliveries";
$addNewDepot = htmlspecialchars($_POST['add-new-depot']);
$addNewCreatedBy = strtoupper(htmlspecialchars($_POST['add-new-createdby']));
$addNewCompany = strtoupper(htmlspecialchars($_POST['add-new-company']));
$addNewLocation = strtoupper(htmlspecialchars($_POST['add-new-location']));
$addNewDocid = htmlspecialchars($_POST['add-new-docid']);
$addNewType = htmlspecialchars($_POST['add-new-type']);
$addNewNote = htmlspecialchars($_POST['add-new-note']);
$redirectTo = htmlspecialchars($_POST['redirect']);
$addNewAdded = date('Y-m-d H:i:s');
$addNewDriver = htmlspecialchars($_POST['driver']);
$addNewAssigned = "";
if($driver != ""){
    $addNewAssigned = $addNewAdded;
}
if($addNewType == "Collection" || $addNewType =="ICT") {
    $addNewStatus = "To Be Collected";
} else {
    $addNewStatus = htmlspecialchars($_POST['add-new-status']); //OPTION - Ready/Awaiting Parts/Manual/To Be Picked
}

$addNewSQL = "INSERT INTO deliveries (
                                    'depot',
                                    'type',
                                    'createdby',
                                    'company',
                                    'location',
                                    'docid',
                                    'added',
                                    'driver',
                                    'assigned',
                                    'status',
                                    'completed',
                                    'sign_name',
                                    'signature',
                                    'note'
                                    )
                            VALUES (
                                    '".$addNewDepot."',
                                    '".$addNewType."',
                                    '".$addNewCreatedBy."',
                                    '".$addNewCompany."',
                                    '".$addNewLocation."',
                                    '".$addNewDocid."',
                                    '".$addNewAdded."',
                                    '".$addNewDriver."',
                                    '".$addNewAssigned."',
                                    '".$addNewStatus."',
                                    '',
                                    '',
                                    '',
                                    '".$addNewNote."'
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