<?php
$db = new SQLite3('scratchpad.db');

$tableName = "deliveries";

// SQL statement to create company table
$sql ="CREATE TABLE ".$tableName."(
    id INTEGER  PRIMARY KEY AUTOINCREMENT,
    depot       TEXT    NOT NULL,
    type        TEXT    NOT NULL,
    company     TEXT    NOT NULL,
    location    TEXT    NOT NULL,
    docid       TEXT,
    added       TEXT,
    driver      TEXT,
    assigned    TEXT,
    status      TEXT,
    completed   TEXT
);";

$ret = $db->exec($sql);
    if(!$ret){
        echo $db->lastErrorMsg();
        echo "Table creation (".$tableName.") failed\n";
    } else {
    echo "Table (".$tableName.") created successfully\n";
    }