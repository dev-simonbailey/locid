<?php
$db = new SQLite3('scratchpad.db');

$tableName = "statuses";

// SQL statement to create company table
$sql ="CREATE TABLE ".$tableName."(
    id INTEGER  PRIMARY KEY AUTOINCREMENT,
    status       TEXT    NOT NULL
);";

$ret = $db->exec($sql);
    if(!$ret){
        echo $db->lastErrorMsg();
        echo "Table creation (".$tableName.") failed\n";
    } else {
    echo "Table (".$tableName.") created successfully\n";
    }