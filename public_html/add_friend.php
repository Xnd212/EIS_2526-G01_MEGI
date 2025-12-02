<?php
session_start();

// Tem de estar logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$currentUserId = (int) $_SESSION['user_id'];

// ID do amigo a adicionar (vem no GET)
$friendId = filter_input(INPUT_GET, 'friend_id', FILTER_VALIDATE_INT);

if (!$friendId || $friendId === $currentUserId) {
    header("Location: homepage.php");
    exit();
}

// -------- LIGAÇÃO À BD --------
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "sie";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}

// Verificar se já existe follow *nesta direção* (currentUser → friend)
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

// Voltar à página do perfil que estás a seguir
header("Location: friendpage.php?user_id=" . $friendId);
exit();
