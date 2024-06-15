<?php
$db = new SQLite3('scratchpad.db');

$tableName = "deliveries";

$sql = "DROP TABLE IF EXISTS " . $tableName;
$ret = $db->exec($sql);
    if(!$ret){
        echo $db->lastErrorMsg();
        echo "Drop Table Operation unsuccessul\n";
    } else {
    echo "Table (".$tableName.") Dropped successfully\n";
}
