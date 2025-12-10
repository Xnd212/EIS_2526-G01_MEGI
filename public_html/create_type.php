<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'error'   => 'Not authenticated.'
    ]);
    exit;
}

require_once __DIR__ . "/db.php";

$typeName = trim($_POST['typeName'] ?? "");

if ($typeName === "") {
    echo json_encode([
        'success' => false,
        'error'   => 'Empty type name.'
    ]);
    exit;
}

// Ver se já existe
$stmt = $conn->prepare("SELECT type_id, name FROM type WHERE name = ? LIMIT 1");
$stmt->bind_param("s", $typeName);
$stmt->execute();
$res = $stmt->get_result();
$existing = $res->fetch_assoc();
$stmt->close();

if ($existing) {
    echo json_encode([
        'success'  => true,
        'existing' => true,
        'type_id'  => (int)$existing['type_id'],
        'name'     => $existing['name'],
    ]);
    exit;
}

// Criar novo
$stmtIns = $conn->prepare("INSERT INTO type (name) VALUES (?)");
$stmtIns->bind_param("s", $typeName);

if ($stmtIns->execute()) {
    $newId = $stmtIns->insert_id;
    $stmtIns->close();

    echo json_encode([
        'success'  => true,
        'existing' => false,
        'type_id'  => (int)$newId,
        'name'     => $typeName,
    ]);
    exit;
} else {
    $err = $stmtIns->error;
    $stmtIns->close();
    echo json_encode([
        'success' => false,
        'error'   => 'DB error: ' . $err
    ]);
    exit;
}
