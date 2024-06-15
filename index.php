<?php
//session_start();
//session_unset();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <title>LOCID | LOGIN</title>
    <style>
        .header {
            margin: auto;
            text-align: center;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 32px;
        }
        .subheader {
            margin: auto;
            text-align: center;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 16px;
            margin-top: 20px;
        }
        .login-chooser {
            border: 2px solid #000000;
            border-radius: 10px;
            width: 300px;
            height: 270px;
            margin: auto;
            margin-top: 1%;
        }
        .options {
            width:90%;
            height:100px;
            background-color: white;
            margin: auto;
            margin-top: 20px;
            text-align: center;
            font-family: Arial, Helvetica, sans-serif;
        }
        .actionJob {
            width:200px;
            height:64px;
            background-color: #00529C;
            color:white;
            border-radius: 5px;
            border:none;
            font-size: 24px;
            cursor: pointer;
            margin-top: 13%;
        }
    </style>
</head>
<body>
    <div class='header'>LOCID</div>
    <div class='subheader'>Please login into your required area</div>
    <div class='login-chooser'>
        <div class='options' name='admin'>
            <a href='dashboard.php'><button class='actionJob' title='Login as Admin'>Admin</button></a>
        </div>
        <div class='options' name='driver'>
            <a href='driver.php'><button class='actionJob' title='Login as Driver'>Driver</button></a>
        </div>
    </div>
</body>
</html>