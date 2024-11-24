<?php
date_default_timezone_set('Europe/London');
require("../data/global.php");
$db = new SQLite3('../'.$database);
$tableName = "deliveries";

// Function to export data to CSV
function exportToCSV($db, $tableName, $depot, $startDate = null, $endDate = null) {
    // Define the directory and ensure it exists
    $directory = '../exports/csv';
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    // Get the current date and time
    $currentDateTime = date('Y-m-d_H-i-s');

    // Define the CSV file name
    if ($startDate && $endDate) {
        $fileName = $directory . '/' . $depot . '_deliveries_export_' . $startDate . '_to_' . $endDate . '_' . $currentDateTime . '.csv';
    } else {
        $fileName = $directory . '/' . $depot . '_deliveries_export_' . $currentDateTime . '.csv';
    }

    // Open the file for writing
    $file = fopen($fileName, 'w');

    // Write the header row
    $header = ['Depot', 'Type', 'Created By', 'Company', 'Location', 'Doc ID', 'Added', 'Driver', 'Assigned', 'Status', 'Completed', 'Sign Name', 'Signature', 'Note'];
    fputcsv($file, $header);

    // Prepare the SQL query
    $query = "SELECT depot, type, createdby, company, location, docid, added, driver, assigned, status, completed, sign_name, signature, note FROM $tableName WHERE depot = :depot";
    if ($startDate && $endDate) {
        $query .= " AND added BETWEEN :startDate AND :endDate";
    }

    // Prepare the statement
    $stmt = $db->prepare($query);
    $stmt->bindValue(':depot', $depot, SQLITE3_TEXT);
    if ($startDate && $endDate) {
        $stmt->bindValue(':startDate', $startDate, SQLITE3_TEXT);
        $stmt->bindValue(':endDate', $endDate, SQLITE3_TEXT);
    }

    // Execute the query and fetch the results
    $result = $stmt->execute();

    // Write the data rows
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['signature'] = 'https://locid.co.uk/signatures/' . $row['signature'];
        fputcsv($file, $row);
    }

    // Close the file
    fclose($file);

    // Return the file name
    return $fileName;
}

// Check if depot is specified
$depot = isset($_GET['depot']) ? $_GET['depot'] : null;
if (!$depot) {
    echo "No Depot Specified";
    exit();
}

// Check if dates are specified
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Export the data to CSV
$fileName = exportToCSV($db, $tableName, $depot, $startDate, $endDate);

echo "CSV file created: <a href='$fileName' download>Download</a>";


// export-jobs-to-csv.php?depot=Northwich&start_date=2024-10-01&end_date=2024-31-10
?>