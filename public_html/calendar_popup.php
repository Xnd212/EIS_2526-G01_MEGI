<?php
// calendar_popup.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    return;
}
$currentUserId = (int) $_SESSION['user_id'];
if (!isset($conn) || $conn->connect_error) {
    echo "<!-- Calendar: DB connection failed -->";
    return;
}

// =================== FETCH UPCOMING EVENTS ===================
$sql = "
    SELECT DISTINCT
        e.event_id,
        e.name,
        e.date,
        DATEDIFF(e.date, CURDATE()) AS days_left
    FROM event e
    LEFT JOIN attends a 
        ON a.event_id = e.event_id 
        AND a.user_id = ?
    WHERE 
        (e.user_id = ? OR a.user_id IS NOT NULL)
        AND e.date >= CURDATE()
    ORDER BY e.date ASC
    LIMIT 3
";
$stmt = $conn->prepare($sql);
$upcomingEvents = [];
if ($stmt) {
    $stmt->bind_param("ii", $currentUserId, $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $upcomingEvents[] = [
            'event_id'  => (int)$row['event_id'],
            'name'      => htmlspecialchars($row['name'] ?? ''),
            'date'      => $row['date'],
            'days_left' => (int)$row['days_left']
        ];
    }
    $stmt->close();
}


// =================== FETCH UNRATED PAST EVENTS ===================
$sqlUnrated = "
    SELECT DISTINCT
        e.event_id,
        e.name,
        e.date
    FROM event e
    JOIN attends a ON a.event_id = e.event_id
    WHERE a.user_id = ?
      AND e.date < CURDATE()
      AND NOT EXISTS (
          SELECT 1
          FROM rating r
          WHERE r.event_id = e.event_id
            AND r.user_id = ?
      )
      AND EXISTS (
          SELECT 1
          FROM attends a_other
          WHERE a_other.event_id = e.event_id
            AND a_other.user_id <> ?
      )
    ORDER BY e.date DESC
    LIMIT 3
";

$stmtUnrated = $conn->prepare($sqlUnrated);
$unratedEvents = [];
if ($stmtUnrated) {
    $stmtUnrated->bind_param("iii", $currentUserId, $currentUserId, $currentUserId);
    $stmtUnrated->execute();
    $resultUnrated = $stmtUnrated->get_result();
    while ($row = $resultUnrated->fetch_assoc()) {
        $unratedEvents[] = [
            'event_id' => (int)$row['event_id'],
            'name'     => htmlspecialchars($row['name'] ?? ''),
            'date'     => $row['date']
        ];
    }
    $stmtUnrated->close();
}
?>

<button class="icon-btn" aria-label="Calendar" id="calendar-btn">📅</button>
<div class="calendar-popup" id="calendar-popup">
    <div class="popup-header">
        <h3>Calendar <span>📅</span></h3>
    </div>

    <!-- UNRATED PAST EVENTS WARNING -->
    <?php if (!empty($unratedEvents)): ?>
        <div class="unrated-warning">
            <div class="warning-header">
                <span class="warning-icon">⚠️</span>
                <strong>Events to Rate</strong>
            </div>
            <p class="warning-text">
                You attended <?= count($unratedEvents) ?> event<?= count($unratedEvents) > 1 ? 's' : '' ?> 
                that <?= count($unratedEvents) > 1 ? 'haven\'t' : 'hasn\'t' ?> been rated yet.
            </p>
            <ul class="unrated-list">
                <?php foreach ($unratedEvents as $event): ?>
                    <li>
                        <a href="eventpage.php?id=<?= $event['event_id'] ?>" class="event-link">
                            <strong><?= $event['name'] ?></strong>
                        </a>
                        <span class="event-date">
                            <?= date('M d, Y', strtotime($event['date'])) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <hr class="popup-divider">
    <?php endif; ?>

    <!-- UPCOMING EVENTS -->
    <div class="upcoming-section">
        <h4 class="section-subtitle">Upcoming Events</h4>
        <ul class="calendar-list">
            <?php if (empty($upcomingEvents)): ?>
                <li>No upcoming events.</li>
            <?php else: ?>
                <?php foreach ($upcomingEvents as $event): ?>
                    <li>
                        <a href="eventpage.php?id=<?= $event['event_id'] ?>" class="event-link">
                            <strong><?= $event['name'] ?></strong>
                        </a>
                        <span class="days-countdown">
                            <?php if ($event['days_left'] === 0): ?>
                                Today!
                            <?php elseif ($event['days_left'] === 1): ?>
                                Tomorrow
                            <?php else: ?>
                                <?= $event['days_left'] ?> days left
                            <?php endif; ?>
                        </span>
                        <span class="event-date">
                            <?= date('M d, Y', strtotime($event['date'])) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <div style="text-align: center; padding: 8px 0;">
        <a href="upcomingevents.php" class="see-more-link">+ See all events</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarBtn = document.getElementById('calendar-btn');
    const calendarPopup = document.getElementById('calendar-popup');
    if (calendarBtn && calendarPopup) {
        calendarBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            calendarPopup.classList.toggle('show');
            const notificationPopup = document.getElementById('notification-popup');
            if (notificationPopup && notificationPopup.classList.contains('show')) {
                notificationPopup.classList.remove('show');
            }
        });
        document.addEventListener('click', function(e) {
            if (!calendarPopup.contains(e.target) && e.target !== calendarBtn) {
                calendarPopup.classList.remove('show');
            }
        });
    }
});
</script>