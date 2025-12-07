<?php
// notifications_popup.php

// 1. SESSION CHECK (Safe version)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. CHECK LOGIN
if (!isset($_SESSION['user_id'])) {
    // If used inside an include, we usually don't redirect, just stop output
    return; 
}

$currentUserId = (int) $_SESSION['user_id'];

// 3. DATABASE CHECK
// We assume $conn exists from the parent page. 
// If it's closed or missing, we stop to avoid a crash.
if (!isset($conn) || $conn->connect_error) {
    echo "";
    return;
}

// =================== BUSCAR NOTIFICAÇÕES ===================
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
    INNER JOIN user u       ON c.user_id = u.user_id 
    INNER JOIN friends f    ON f.friend_id = c.user_id
    INNER JOIN item i       ON ct.item_id = i.item_id
    WHERE f.user_id = ?

    ORDER BY sort_key DESC
    LIMIT 10
";

$stmt = $conn->prepare($sql);
if ($stmt) {
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
}
// NOTE: DO NOT CLOSE $conn HERE. The parent page needs it!
?>

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