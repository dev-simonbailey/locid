<?php
$db = new SQLite3('scratchpad.db');

$tableName = "deliveries";
$action = "show-all"; //show-all/by-company/by-driver
$status = ""; // Ready/In Transit/Completed

switch ($action) {
    case "by-company":
        $company = "Abbey Logistics";
        if($status != "") {
            $sql = "SELECT * FROM deliveries 
                    WHERE `company` = '".$company."' 
                    AND `status` = '".$status."' 
                    ORDER BY added ASC";
        } else {
            $sql = "SELECT * FROM deliveries 
                    WHERE `company` = '".$company."' 
                    ORDER BY added ASC";
        }
        break;
    case "by-driver":
        $driver = "Simon Barnet";
        if($status != "") {
            $sql = "SELECT * FROM deliveries 
                    WHERE `driver` = '".$driver."' 
                    AND `status` = '".$status."' 
                    ORDER BY added ASC";
        } else {
            $sql = "SELECT * FROM deliveries 
                WHERE `driver` = '".$driver."' 
                ORDER BY added ASC";
        }
        break;
    default:
        if($status != ""){
            $sql = "SELECT * FROM deliveries 
                    WHERE type = 'Delivery' 
                    AND `status` = '".$status."'
                    ORDER BY added ASC";
        } else {
            $sql = "SELECT * FROM deliveries WHERE type = 'Delivery' ORDER BY added ASC";
  }
        }
        

echo $sql.PHP_EOL;

$ret = $db->query($sql);
if(!$ret){
    echo $db->lastErrorMsg();
    echo ", Query failed\n";
} else {

    
    while( $row1 = $ret->fetchArray( SQLITE3_ASSOC ) ) {
    
        echo 
        $row1['depot']."|"
        .$row1['type']."|"
        .$row1['company']."|"
        .$row1['docid']."|"
        .$row1['added']."|"
        .$row1['driver']."|"
        .$row1['assigned']."|"
        .$row1['status']."|"
        .$row1['completed'].PHP_EOL;
    
    }


    echo "Query successfull\n";
}