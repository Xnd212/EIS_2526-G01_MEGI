<?php
// delete_event.php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Check Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// 2. Get Event ID
$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$event_id) {
    header("Location: upcomingevents.php");
    exit();
}

// 3. Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "sie";
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 4. VERIFY PERMISSION (Crucial!)
// Check if the logged-in user is actually the creator of this event
$checkSql = "SELECT user_id FROM event WHERE event_id = ?";
$stmt = $conn->prepare($checkSql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Event not found.");
}

$row = $result->fetch_assoc();
if ($row['user_id'] !== $user_id) {
    die("You do not have permission to delete this event.");
}
$stmt->close();

// 5. DELETE DATA
// We must delete dependencies first to avoid Foreign Key errors 
// (unless your DB has ON DELETE CASCADE set up).

// A. Delete Ratings for this event
$delRatings = $conn->prepare("DELETE FROM rating WHERE event_id = ?");
$delRatings->bind_param("i", $event_id);
$delRatings->execute();
$delRatings->close();

// B. Delete Attendance records for this event
$delAttends = $conn->prepare("DELETE FROM attends WHERE event_id = ?");
$delAttends->bind_param("i", $event_id);
$delAttends->execute();
$delAttends->close();

// C. Delete the Event itself
$delEvent = $conn->prepare("DELETE FROM event WHERE event_id = ?");
$delEvent->bind_param("i", $event_id);

if ($delEvent->execute()) {
    // Success -> Redirect to homepage or upcoming events
    header("Location: upcomingevents.php?msg=deleted");
} else {
    echo "Error deleting event: " . $conn->error;
}
$delEvent->close();
$conn->close();
