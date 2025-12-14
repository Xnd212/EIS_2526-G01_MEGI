<?php
session_start();
require_once __DIR__ . "/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$currentUserId = (int)$_SESSION['user_id'];
$action = $_GET['action'] ?? '';

// ==========================================
// ACTION: CHECK (Count exclusive items and event attendance)
// ==========================================
if ($action === 'check') {
    $col_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$col_id) { echo json_encode(['error' => 'Invalid ID']); exit; }

    // Logic 1: Find items in this collection that DO NOT exist in any other collection
    $sql = "
        SELECT COUNT(*) as exclusive_count
        FROM (
            SELECT item_id
            FROM contains
            WHERE item_id IN (
                SELECT item_id FROM contains WHERE collection_id = ?
            )
            GROUP BY item_id
            HAVING COUNT(collection_id) = 1
        ) as exclusive_items
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $col_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Logic 2: Check if this collection is attending any future events
    $eventSql = "
        SELECT COUNT(*) as future_event_count 
        FROM attends a
        JOIN event e ON a.event_id = e.event_id
        WHERE a.collection_id = ? AND e.date > NOW()
    ";
    $stmtEvent = $conn->prepare($eventSql);
    $stmtEvent->bind_param("i", $col_id);
    $stmtEvent->execute();
    $eventResult = $stmtEvent->get_result();
    $eventRow = $eventResult->fetch_assoc();

    echo json_encode([
        'exclusive_count' => (int)$row['exclusive_count'],
        'future_event_count' => (int)$eventRow['future_event_count']
    ]);
    exit();
}

// ==========================================
// ACTION: DELETE (Perform deletion)
// ==========================================
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $col_id = filter_input(INPUT_POST, 'collection_id', FILTER_VALIDATE_INT);
    
    // 1. Verify Ownership
    $checkOwner = $conn->prepare("SELECT user_id FROM collection WHERE collection_id = ?");
    $checkOwner->bind_param("i", $col_id);
    $checkOwner->execute();
    $resOwner = $checkOwner->get_result()->fetch_assoc();

    if (!$resOwner || $resOwner['user_id'] != $currentUserId) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    // 2. Check if collection is attending any future events
    $checkFutureEvents = $conn->prepare("
        SELECT COUNT(*) as future_count 
        FROM attends a
        JOIN event e ON a.event_id = e.event_id
        WHERE a.collection_id = ? AND e.date > NOW()
    ");
    $checkFutureEvents->bind_param("i", $col_id);
    $checkFutureEvents->execute();
    $resFuture = $checkFutureEvents->get_result()->fetch_assoc();

    if ($resFuture['future_count'] > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Cannot delete collection: it is attending ' . $resFuture['future_count'] . ' future event(s). Please edit the event(s) to remove this collection before deletion.'
        ]);
        exit();
    }

    // Start Transaction
    $conn->begin_transaction();

    try {
        // STEP 1: IDENTIFY ITEMS TO DELETE
        // We need to store the IDs in PHP first, because we are about to delete the links in 'contains'
        $findExclusiveSql = "
            SELECT item_id 
            FROM contains 
            WHERE item_id IN (SELECT item_id FROM contains WHERE collection_id = ?)
            GROUP BY item_id 
            HAVING COUNT(collection_id) = 1
        ";
        $stmtFind = $conn->prepare($findExclusiveSql);
        $stmtFind->bind_param("i", $col_id);
        $stmtFind->execute();
        $resFind = $stmtFind->get_result();

        $itemsToDelete = [];
        while($row = $resFind->fetch_assoc()) {
            $itemsToDelete[] = $row['item_id'];
        }

        // STEP 2: DELETE LINKS IN 'contains'
        // We delete the links FIRST. This satisfies the Foreign Key constraint.
        // The items still exist in the 'item' table, but they are no longer linked to this collection.
        $stmtDelLinks = $conn->prepare("DELETE FROM contains WHERE collection_id = ?");
        $stmtDelLinks->bind_param("i", $col_id);
        $stmtDelLinks->execute();

        // STEP 3: DELETE THE EXCLUSIVE ITEMS
        // Now that the links are gone, we can safely delete the items from the parent table.
        if (!empty($itemsToDelete)) {
            // Create a string like "1, 5, 12" for the SQL query
            $idsList = implode(',', array_map('intval', $itemsToDelete));
            $conn->query("DELETE FROM item WHERE item_id IN ($idsList)");
        }

        // STEP 4: CLEANUP OTHER TABLES
        // Delete Tags
        $stmtDelTags = $conn->prepare("DELETE FROM collection_tags WHERE collection_id = ?");
        $stmtDelTags->bind_param("i", $col_id);
        $stmtDelTags->execute();

        // Delete Event Attendance
        $stmtDelAttends = $conn->prepare("DELETE FROM attends WHERE collection_id = ?");
        $stmtDelAttends->bind_param("i", $col_id);
        $stmtDelAttends->execute();

        // STEP 5: DELETE THE COLLECTION ITSELF
        $stmtDelCol = $conn->prepare("DELETE FROM collection WHERE collection_id = ?");
        $stmtDelCol->bind_param("i", $col_id);
        $stmtDelCol->execute();

        $conn->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}
