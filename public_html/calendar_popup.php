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
// Só eventos:
//  - criados pelo user (e.user_id = user)
//  - em que o user deu sign up (attends.user_id = user)
//  - data futura (>= hoje)
$sql = "
    SELECT 
        e.event_id,
        e.name,
        e.date,
        DATEDIFF(e.date, CURDATE()) AS days_left
    FROM event e
    INNER JOIN attends a
        ON a.event_id = e.event_id
       AND a.user_id = ?
    WHERE 
        e.user_id = ?
        AND e.date >= CURDATE()
    ORDER BY e.date ASC
    LIMIT 10
";

$stmt = $conn->prepare($sql);
$upcomingEvents = [];

if ($stmt) {
    // mesmo user nas duas condições (criador + sign up)
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
?>

<button class="icon-btn" aria-label="Calendar" id="calendar-btn">📅</button>

<div class="calendar-popup" id="calendar-popup">
    <div class="popup-header">
        <h3>Upcoming Events <span>📅</span></h3>
    </div>

    <ul class="calendar-list">
        <?php if (empty($upcomingEvents)): ?>
            <li>No upcoming events.</li>
        <?php else: ?>
            <?php foreach ($upcomingEvents as $event): ?>
                <li>
                    <!-- usa sempre event_id -->
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
