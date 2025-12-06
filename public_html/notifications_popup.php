<?php
// notifications_popup.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$currentUserId = (int) $_SESSION['user_id'];

// =================== LIGAÇÃO À BD ===================
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "sie";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}

// =================== BUSCAR NOTIFICAÇÕES ===================
// 1) collections criadas por friends
// 2) events criados por friends
// 3) items adicionados a coleções de friends
// 4) friends que vão a eventos (tabela attends)

$sql = "
    SELECT 
        'collection_created' AS type,
        u.username           AS actor_name,
        c.name               AS target_name,
        c.collection_id      AS sort_key
    FROM collection c
    INNER JOIN user u    ON u.user_id = c.user_id
    INNER JOIN friends f ON f.friend_id = c.user_id
    WHERE f.user_id = ?

    UNION ALL

    SELECT 
        'event_created'      AS type,
        u.username           AS actor_name,
        e.name               AS target_name,
        e.event_id           AS sort_key
    FROM event e
    INNER JOIN user u    ON u.user_id = e.user_id
    INNER JOIN friends f ON f.friend_id = e.user_id
    WHERE f.user_id = ?

    UNION ALL

    SELECT
        'item_added'         AS type,
        u.username           AS actor_name,
        c.name               AS target_name,
        i.item_id            AS sort_key
    FROM contains ct
    INNER JOIN collection c ON ct.collection_id = c.collection_id
    INNER JOIN user u       ON c.user_id = u.user_id        -- dono da coleção (friend)
    INNER JOIN friends f    ON f.friend_id = c.user_id      -- só coleções de friends
    INNER JOIN item i       ON ct.item_id = i.item_id
    WHERE f.user_id = ?

    ORDER BY sort_key DESC
    LIMIT 10
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $currentUserId, $currentUserId, $currentUserId);

$stmt->execute();
$result = $stmt->get_result();

$notifications = [];

while ($row = $result->fetch_assoc()) {
    $text = '';

switch ($row['type']) {
    case 'collection_created':
        $text = "<strong>{$row['actor_name']}</strong> created a new collection: {$row['target_name']}.";
        break;

    case 'event_created':
        $text = "<strong>{$row['actor_name']}</strong> created a new event: {$row['target_name']}.";
        break;

    case 'item_added':
        $text = "<strong>{$row['actor_name']}</strong> added a new item to the {$row['target_name']} collection.";
        break;
}


    if ($text !== '') {
        $notifications[] = $text;
    }
}

$stmt->close();
$conn->close();
?>

<!-- =================== HTML DO POPUP =================== -->
<button class="icon-btn" aria-label="Notificações" id="notification-btn">🔔</button>

<div class="notification-popup" id="notification-popup">
    <div class="popup-header">
        <h3>Notifications <span>🔔</span></h3>
    </div>

    <ul class="notification-list">
        <?php if (empty($notifications)): ?>
            <li>No notifications yet.</li>
        <?php else: ?>
            <?php foreach ($notifications as $n): ?>
                <li><?= $n ?></li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>

    <a href="#" class="see-more-link">+ See more</a>
</div>
