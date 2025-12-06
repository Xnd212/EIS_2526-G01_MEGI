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
require_once __DIR__ . "/db.php";

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
