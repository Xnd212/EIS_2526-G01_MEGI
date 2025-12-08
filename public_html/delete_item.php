<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$currentUserId = (int) $_SESSION['user_id'];

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get item_id from POST
$item_id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
if (!$item_id || $item_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
    exit();
}

// Connect to database
require_once __DIR__ . "/db.php";

try {
    // Start transaction
    $conn->begin_transaction();

    // Verify that the item belongs to the current user
    $sqlCheck = "
        SELECT i.item_id, c.user_id 
        FROM item i
        INNER JOIN contains con ON i.item_id = con.item_id
        INNER JOIN collection c ON con.collection_id = c.collection_id
        WHERE i.item_id = ?
        LIMIT 1
    ";
    
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("i", $item_id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $itemData = $resultCheck->fetch_assoc();

    if (!$itemData) {
        throw new Exception('Item not found');
    }

    if ((int)$itemData['user_id'] !== $currentUserId) {
        throw new Exception('Unauthorized: You do not own this item');
    }

    // Delete from contains table (relationship between item and collection)
    $sqlDeleteContains = "DELETE FROM contains WHERE item_id = ?";
    $stmtDeleteContains = $conn->prepare($sqlDeleteContains);
    $stmtDeleteContains->bind_param("i", $item_id);
    $stmtDeleteContains->execute();

    // Delete the item itself
    $sqlDeleteItem = "DELETE FROM item WHERE item_id = ?";
    $stmtDeleteItem = $conn->prepare($sqlDeleteItem);
    $stmtDeleteItem->bind_param("i", $item_id);
    $stmtDeleteItem->execute();

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>