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
        u.user_id            AS actor_id,
        u.username           AS actor_name,
        c.collection_id      AS target_id,
        c.name               AS target_name,
        NULL                 AS item_id
    FROM collection c
    INNER JOIN user u    ON u.user_id = c.user_id
    INNER JOIN friends f ON f.friend_id = c.user_id
    WHERE f.user_id = ?

    UNION ALL

    SELECT 
        'event_created'      AS type,
        u.user_id            AS actor_id,
        u.username           AS actor_name,
        e.event_id           AS target_id,
        e.name               AS target_name,
        NULL                 AS item_id
    FROM event e
    INNER JOIN user u    ON u.user_id = e.user_id
    INNER JOIN friends f ON f.friend_id = e.user_id
    WHERE f.user_id = ?

    UNION ALL

    SELECT
        'item_added'         AS type,
        u.user_id            AS actor_id,
        u.username           AS actor_name,
        c.collection_id      AS target_id,
        c.name               AS target_name,
        i.item_id            AS item_id
    FROM contains ct
    INNER JOIN collection c ON ct.collection_id = c.collection_id
    INNER JOIN user u       ON c.user_id = u.user_id
    INNER JOIN friends f    ON f.friend_id = c.user_id
    INNER JOIN item i       ON ct.item_id = i.item_id
    WHERE f.user_id = ?

    ORDER BY actor_id DESC
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

        $actorLink = "<a href='friendpage.php?user_id={$row['actor_id']}' class='notif-link'>{$row['actor_name']}</a>";

        switch ($row['type']) {

            case 'collection_created':
                $collectionLink = "<a href='collectionpage.php?id={$row['target_id']}' class='notif-link'>{$row['target_name']}</a>";
                $text = "$actorLink created a new collection: $collectionLink.";
                break;

            case 'event_created':
                $eventLink = "<a href='eventpage.php?id={$row['target_id']}' class='notif-link'>{$row['target_name']}</a>";
                $text = "$actorLink created a new event: $eventLink.";
                break;

            case 'item_added':
                $collectionLink = "<a href='collectionpage.php?id={$row['target_id']}' class='notif-link'>{$row['target_name']}</a>";
                $itemLink = "<a href='itempage.php?id={$row['item_id']}' class='notif-link'>item</a>";
                $text = "$actorLink added a new $itemLink to the $collectionLink collection.";
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