<?php
// calendar_popup.php

// 1. SESSION CHECK (Safe version)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. CHECK LOGIN
if (!isset($_SESSION['user_id'])) {
    return; 
}

$currentUserId = (int) $_SESSION['user_id'];

// 3. DATABASE CHECK
if (!isset($conn) || $conn->connect_error) {
    echo "<!-- Calendar: DB connection failed -->";
    return;
}

echo "<!-- Calendar: Starting to load for user $currentUserId -->";

// =================== FETCH UPCOMING EVENTS ===================
$sql = "
    SELECT 
        e.event_id,
        e.name,
        e.date,
        DATEDIFF(e.date, CURDATE()) AS days_left
    FROM event e
    WHERE e.user_id = ?
      AND e.date >= CURDATE()
    ORDER BY e.date ASC
    LIMIT 10
";

$stmt = $conn->prepare($sql);
$upcomingEvents = [];

if ($stmt) {
    $stmt->bind_param("i", $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $upcomingEvents[] = [
            'id' => $row['event_id'],
            'name' => htmlspecialchars($row['name']),
            'date' => $row['date'],
            'days_left' => (int) $row['days_left']
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
                    <a href='eventpage.php?id=<?= $event['id'] ?>' class='event-link'>
                        <strong><?= $event['name'] ?></strong>
                    </a>
                    <span class='days-countdown'>
                        <?php if ($event['days_left'] === 0): ?>
                            Today!
                        <?php elseif ($event['days_left'] === 1): ?>
                            Tomorrow
                        <?php else: ?>
                            <?= $event['days_left'] ?> days left
                        <?php endif; ?>
                    </span>
                    <span class='event-date'><?= date('M d, Y', strtotime($event['date'])) ?></span>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
    <div style="text-align: center; padding: 8px 0;">
        <a href="upcomingevents.php" class="see-more-link">+ See all events</a>
    </div>
</div>

<script>
// Calendar Popup Toggle
document.addEventListener('DOMContentLoaded', function() {
    const calendarBtn = document.getElementById('calendar-btn');
    const calendarPopup = document.getElementById('calendar-popup');

    if (calendarBtn && calendarPopup) {
        calendarBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            calendarPopup.classList.toggle('show');
            
            // Close notification popup if open
            const notificationPopup = document.getElementById('notification-popup');
            if (notificationPopup && notificationPopup.classList.contains('show')) {
                notificationPopup.classList.remove('show');
            }
        });

        // Close popup when clicking outside
        document.addEventListener('click', function(e) {
            if (!calendarPopup.contains(e.target) && e.target !== calendarBtn) {
                calendarPopup.classList.remove('show');
            }
        });
    }
});
</script>