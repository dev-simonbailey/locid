<?php
$db = new SQLite3('scratchpad.db');

$tableName = "drivers";

// SQL statement to create company table
$sql ="CREATE TABLE ".$tableName."(
    id INTEGER  PRIMARY KEY AUTOINCREMENT,
    depot       TEXT    NOT NULL,
    firstName   TEXT    NOT NULL,
    lastName    TEXT    NOT NULL,
    routeId     TEXT
);";

$ret = $db->exec($sql);
    if(!$ret){
        echo $db->lastErrorMsg();
        echo "Table creation (".$tableName.") failed\n";
    } else {
    echo "Table (".$tableName.") created successfully\n";
    }