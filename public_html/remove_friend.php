<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$currentUserId = (int) $_SESSION['user_id'];
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

// Apagar follow só nesta direção (currentUser → friend)
$deleteSql = "
    DELETE FROM friends
    WHERE user_id = ? AND friend_id = ?
";
$stmt = $conn->prepare($deleteSql);
$stmt->bind_param("ii", $currentUserId, $friendId);
$stmt->execute();
$stmt->close();

$conn->close();

// Voltar à página do perfil
header("Location: friendpage.php?user_id=" . $friendId);
exit();
