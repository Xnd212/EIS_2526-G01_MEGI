<?php
session_start();

/* =========================
   1) CHECK LOGIN
   ========================= */

if (!isset($_SESSION['user_id'])) {
    // Se alguém tentar aceder direto sem login
    header("Location: login.php");
    exit();
}

$currentUserId = (int) $_SESSION['user_id'];
if ($currentUserId <= 0) {
    header("Location: login.php");
    exit();
}

/* =========================
   2) LER ID DO AMIGO
   ========================= */

$friendId = filter_input(INPUT_GET, 'friend_id', FILTER_VALIDATE_INT);

// Não pode ser vazio nem o próprio user
if (!$friendId || $friendId === $currentUserId) {
    header("Location: homepage.php");
    exit();
}

/* =========================
   3) LIGAÇÃO À BD
   ========================= */

$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "sie";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}

/* =========================
   4) VALIDAR QUE AMBOS OS USERS EXISTEM
   ========================= */

$sqlCheckUsers = "
    SELECT user_id 
    FROM user 
    WHERE user_id IN (?, ?)
";

$stmtUsers = $conn->prepare($sqlCheckUsers);
$stmtUsers->bind_param("ii", $currentUserId, $friendId);
$stmtUsers->execute();
$resUsers = $stmtUsers->get_result();

if ($resUsers->num_rows < 2) {
    // Um dos IDs não existe → evita violar FK
    $stmtUsers->close();
    $conn->close();
    header("Location: homepage.php");
    exit();
}
$stmtUsers->close();

/* =========================
   5) VER SE JÁ É AMIGO
   ========================= */

$checkSql = "
    SELECT 1 
    FROM friends
    WHERE user_id = ? AND friend_id = ?
    LIMIT 1
";
$stmt = $conn->prepare($checkSql);
$stmt->bind_param("ii", $currentUserId, $friendId);
$stmt->execute();
$result = $stmt->get_result();

/* =========================
   6) SE AINDA NÃO FOR, INSERE
   ========================= */

if ($result->num_rows === 0) {
    $insertSql = "
        INSERT INTO friends (user_id, friend_id)
        VALUES (?, ?)
    ";
    $insert = $conn->prepare($insertSql);
    $insert->bind_param("ii", $currentUserId, $friendId);
    $insert->execute();
    $insert->close();
}

$stmt->close();
$conn->close();

/* =========================
   7) VOLTAR À PÁGINA DO AMIGO
   ========================= */

header("Location: friendpage.php?user_id=" . $friendId);
exit();
