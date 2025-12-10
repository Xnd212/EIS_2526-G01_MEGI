<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['type'])) {
    header("Location: editprofile.php");
    exit();
}

$downloadType = $_GET['type'];
$userId = (int)$_SESSION['user_id'];

require_once __DIR__ . '/db.php';

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="tralle_export_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

if ($downloadType === 'items' || $downloadType === 'both') {
    // Export Items
    fputcsv($output, ['=== ITEMS ===']);
    fputcsv($output, ['Name', 'Price', 'Type', 'Importance', 'Acquisition Date', 'Acquisition Place', 'Description', 'Collection']);
    
    $sqlItems = "
        SELECT 
            i.name,
            i.price,
            t.name AS type_name,
            i.importance,
            i.acc_date,
            i.acc_place,
            i.description,
            c.name AS collection_name
        FROM item i
        LEFT JOIN type t ON i.type_id = t.type_id
        LEFT JOIN contains cont ON i.item_id = cont.item_id
        LEFT JOIN collection c ON cont.collection_id = c.collection_id
        WHERE c.user_id = ?
        ORDER BY c.name, i.name
    ";
    
    $stmt = $conn->prepare($sqlItems);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['name'] ?? '',
            $row['price'] ?? '',
            $row['type_name'] ?? '',
            $row['importance'] ?? '',
            $row['acc_date'] ?? '',
            $row['acc_place'] ?? '',
            $row['description'] ?? '',
            $row['collection_name'] ?? ''
        ]);
    }
    $stmt->close();
    
    if ($downloadType === 'both') {
        fputcsv($output, []); // Empty line separator
    }
}

if ($downloadType === 'collections' || $downloadType === 'both') {
    // Export Collections
    fputcsv($output, ['=== COLLECTIONS ===']);
    fputcsv($output, ['Name', 'Theme', 'Starting Date', 'Description', 'Number of Items']);
    
    $sqlCollections = "
        SELECT 
            c.name,
            c.Theme,
            c.starting_date,
            c.description,
            COUNT(cont.item_id) AS item_count
        FROM collection c
        LEFT JOIN contains cont ON c.collection_id = cont.collection_id
        WHERE c.user_id = ?
        GROUP BY c.collection_id
        ORDER BY c.name
    ";
    
    $stmt = $conn->prepare($sqlCollections);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['name'] ?? '',
            $row['Theme'] ?? '',
            $row['starting_date'] ?? '',
            $row['description'] ?? '',
            $row['item_count'] ?? '0'
        ]);
    }
    $stmt->close();
}

fclose($output);
exit();
