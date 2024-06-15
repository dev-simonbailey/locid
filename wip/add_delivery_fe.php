<?php
$db = new SQLite3('scratchpad.db');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Delivery</title>
    <style>
        body {
            font-family:arial;
        }
        .main-box{
            width:300px;
            height:600px;
            margin: auto;
            border: #000 1px solid;
            border-radius: 10px;
            text-align:center;
        }
        .button {
            width:100%;
            height:24px;
        }
        .textboxes{
            width:100%;
            height:24px;
        }
    </style>
</head>
<body>
    <div class='main-box'>
        <center>
        <form name='add-new-delivery' action='add_delivery.php' method='POST'>
            <table width='50%'>
                <tr><th>Add new Delivery</th></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Depot</td></tr>
                <tr>
                    <td>
                        <select class='textboxes' name='depot'>
                            <option value='NWT Ellesmere Port'>NWT Ellesmere Port</option>
                            <option value='NWT Huyton'>NWT Huyton</option>
                            <option value='NWT Northwich'>NWT Northwich</option>
                            <option value='NWT Warrington'>NWT Warrington</option>
                            <option value='Woodwards'>Woodwards</option>
                        </select>
                    </td>
                </tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Job Type</td></tr>
                <tr><td>
                    <select class='textboxes' name='type'>
                        <option value='Delivery'>Delivery</option>
                        <option value='Collection'>Collection</option>
                        <option value='ICT'>Meet</option>
                    </select>
                </td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Customer</td></tr>
                <tr><td><input class='textboxes' name='company' type='text'></td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Location</td></tr>
                <tr><td><input class='textboxes' name='location' type='text'/></td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Document ID</td></tr>
                <tr><td><input class='textboxes' name='docid' type='text'/></td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>Status</td></tr>
                <tr><td>
                        <select class='textboxes' name='status'>
                                <option value='To Be Picked' selected>To Be Picked</option>
                                <option value='Awaiting Parts'>Awaiting Parts</option>
                                <option value='Manual'>Manual</option>
                        </select>
                    </td>
                 </tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td><input class='button' type='submit' value='Add Job'></td></tr>
            </table>
        </form>
        <table width='50%'>
            <tr><td>&nbsp;</td></tr>
            <tr><td><a href='dashboard.php'><button class='button'>Return to Dashboard</button></a></td></tr>
        </table>
    </center>
    </div>
</body>
</html>