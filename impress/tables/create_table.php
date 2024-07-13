<?php
$db = new SQLite3('scratchpad.db');

$tableName = "stock";

// SQL statement to create company table
$sql ="CREATE TABLE ".$tableName."(
    id INTEGER  PRIMARY KEY AUTOINCREMENT,
    customer    TEXT    NOT NULL,
    location    TEXT    NOT NULL,
    partnumber  TEXT    NOT NULL,
    description TEXT    NOT NULL,
    stockqty    TEXT    NOT NULL
);";

$ret = $db->exec($sql);
    if(!$ret){
        echo $db->lastErrorMsg();
        echo "Table creation (".$tableName.") failed\n";
    } else {
    echo "Table (".$tableName.") created successfully\n";
    }

    /*
    login table -> requires add/edit/delete UI
    -> The login table will be set up from super user and the customer will have no access to the UI
    id
    username
    password
    customer -> id from customer table
    location -> id from location table

    stock table -> requires add/edit/delete UI
    -> The stock table will be set up from super user and the customer will have no access to the UI
    id
    customer -> id from customer table
    location -> id from location table
    part-number
    description
    impress-qty
    stock-qty
    date-added
    date-last-booked
    date-last-ordered
    date-last-replenished

    customer table -> requires add/edit/delete UI
    -> The customer table will be set up from super user and the customer will have no access to the UI
    id
    account
    customer-name

    location table -> requires add/edit/delete UI
    -> The location table will be set up from super user and the customer will have no access to the UI
    id
    customer -> id from customer table
    location

    vehicle table -> requires add/edit/delete UI -> the customer will need acccess to this.
    id
    reg-no
    make
    model
    vin-number
    notes

    impress table -> requires add/edit/delete UI
    -> The impress table will be accessible by the users and will need to have the ability for them to enter a part number, qty, job ref and reg no.
    -> it will also need to allow them to enter a part number/job ref/reg no and allow them to edit or delete the transaction
    -> there should be two drop downs that bring back all the job references and reg no is the system, with the ability to add new ones.
    customer -> id from customer table
    date-used
    part-number
    qty
    job-ref
    reg-no
    ordered
    date-ordered

    It would be good to have a UI that will bring back all parts referenced by job no/reg no. There should also be the ability to search for job no/reg no by part number.

    There needs to be a cron job that runs everyday at 22:00 that sends the details of the parts booked out during that day to NWT (email is fine, in the initial instance)

    There needs to be a UI that will allow the stock controller to update the replenished parts qty's
    */

    