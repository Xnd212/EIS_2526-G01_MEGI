<?php
session_start();
require_once __DIR__ . "/db.php";

header("Content-Type: application/json");

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "error" => "Invalid request"]);
    exit;
}

$rawName = trim($_POST['tagName'] ?? '');

if ($rawName === '') {
    echo json_encode(["success" => false, "error" => "Tag name is required"]);
    exit;
}

// NORMALIZAÇÃO
$name = strtolower($rawName);
$name = preg_replace('/\s+/', ' ', $name);
$name = trim($name);
$name = ucfirst($name);

// Verificar duplicados
$sqlCheck = "SELECT tag_id FROM tags WHERE LOWER(name) = LOWER(?) LIMIT 1";
$stmt = $conn->prepare($sqlCheck);
$stmt->bind_param("s", $name);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        "success" => true,
        "existing" => true,
        "tag_id" => $row['tag_id'],
        "name" => $name
    ]);
    exit;
}

// Criar nova tag
$sqlInsert = "INSERT INTO tags (name) VALUES (?)";
$stmt2 = $conn->prepare($sqlInsert);
$stmt2->bind_param("s", $name);
$stmt2->execute();
$newId = $stmt2->insert_id;

echo json_encode([
    "success" => true,
    "existing" => false,
    "tag_id" => $newId,
    "name" => $name
]);

exit;
?>
