<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 1. Autenticação
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$currentUserId = (int) $_SESSION['user_id'];

// 2. Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// 3. ID do item
$item_id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
if (!$item_id || $item_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID']);
    exit();
}

// 4. BD
require_once __DIR__ . "/db.php";

try {
    $conn->set_charset('utf8mb4');

    // Inicia transação
    if (!$conn->begin_transaction()) {
        throw new Exception('Could not start transaction: ' . $conn->error);
    }

    // 4.1 Verificar se o item pertence ao user atual
    $sqlCheck = "
        SELECT i.item_id, c.user_id
        FROM item i
        INNER JOIN `contains` con ON i.item_id = con.item_id
        INNER JOIN collection c   ON con.collection_id = c.collection_id
        WHERE i.item_id = ?
        LIMIT 1
    ";

    $stmtCheck = $conn->prepare($sqlCheck);
    if (!$stmtCheck) {
        throw new Exception('Prepare failed (check): ' . $conn->error);
    }

    $stmtCheck->bind_param("i", $item_id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $itemData    = $resultCheck->fetch_assoc();
    $stmtCheck->close();

    if (!$itemData) {
        throw new Exception('Item not found');
    }

    if ((int)$itemData['user_id'] !== $currentUserId) {
        throw new Exception('Unauthorized: You do not own this item');
    }

    // 4.2 Apagar relações na contains
    $sqlDeleteContains = "DELETE FROM `contains` WHERE item_id = ?";
    $stmtDeleteContains = $conn->prepare($sqlDeleteContains);
    if (!$stmtDeleteContains) {
        throw new Exception('Prepare failed (delete contains): ' . $conn->error);
    }
    $stmtDeleteContains->bind_param("i", $item_id);
    $stmtDeleteContains->execute();
    $stmtDeleteContains->close();

    // 4.3 Apagar o item
    $sqlDeleteItem = "DELETE FROM item WHERE item_id = ?";
    $stmtDeleteItem = $conn->prepare($sqlDeleteItem);
    if (!$stmtDeleteItem) {
        throw new Exception('Prepare failed (delete item): ' . $conn->error);
    }
    $stmtDeleteItem->bind_param("i", $item_id);
    $stmtDeleteItem->execute();
    $stmtDeleteItem->close();

    // Commit
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);

} catch (Exception $e) {
    // Rollback
    if ($conn->errno === 0) {
        // ainda assim tenta
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
