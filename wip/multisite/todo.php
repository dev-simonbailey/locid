<?php
/*

main login
    -> user enters client id
        -> fail     ->  return to main login
        -> success  ->  get client db name and add to session variable
                    ->  add client id to session variable
            -> show login for the user
                -> call the client db with the username and password
                    -> check username and password
                        ->  get depot the user is associated with and add to session variable
                        ->  get the role of the user that has logged in.
                            -> if role is driver then add driver name to session variable
                                -> pass the user to the driver dashboard
                            -> if role is admin then pass the user to the admin dashboard

locid.db
    -> client id

client db change driver table to users
    -> username - username of the user
    -> password - username of the user
    -> role - role of the user (admin/driver)
    -> depot - depot the user is associated with

file structure
index.php - the page the user enters their client id
    -> login.php - the page the user enters their username and password
    -> dashboard.php - admin dashboard
    -> driver.php - driver dashboard
    -> myroute.php - my route child page of driver.php
    -> favicons
    -> site.webmanifest
    -> actions (DIR)
    -> data (DIR)
    -> images (DIR)
        -> client_id (DIR)
    -> signature (DIR)
        -> client_id (DIR)
*/