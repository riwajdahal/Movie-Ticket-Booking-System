<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_ids_json'])) {
    $booking_ids = json_decode($_POST['booking_ids_json'] ?? '[]', true);
    
    if (!empty($booking_ids)) {
        // Mark bookings as "Pay at Counter" - no email sent for counter payments
        $ids_placeholder = implode(',', array_fill(0, count($booking_ids), '?'));
        $update_stmt = $conn->prepare("UPDATE bookings SET status='Pending', payment_method='Counter' WHERE id IN ($ids_placeholder) AND user_id = ?");
        $types = str_repeat('i', count($booking_ids)) . 'i';
        $params = array_merge($booking_ids, [$_SESSION['user_id']]);
        $update_stmt->bind_param($types, ...$params);
        $update_stmt->execute();
        
        $_SESSION['last_booking_ids'] = $booking_ids;
    }
}

$booking_ids = $_SESSION['last_booking_ids'] ?? [];
$booking_details = null;

if (!empty($booking_ids)) {
    $ids = implode(',', array_map('intval', $booking_ids));
    $query = "SELECT b.*, m.title, s.date, s.time, s.screen_number 
              FROM bookings b 
              JOIN showtimes s ON b.showtime_id = s.id 
              JOIN movies m ON s.movie_id = m.id 
              WHERE b.id IN ($ids) AND b.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $booking_details = $result->fetch_assoc();
        $booking_details['seats'] = implode(', ', array_column($result->fetch_all(MYSQLI_ASSOC), 'seat_number'));
    }
    unset($_SESSION['last_booking_ids']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmation - Movie Ticket Booking System</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<header>
    <h1>Movie Ticket Booking System</h1>
    <nav>
        <a href="index.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='index.php') echo ' active'; ?>">Home</a>
        <a href="booking_history.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='booking_history.php') echo ' active'; ?>">My Bookings</a>
        <a href="logout.php" class="nav-btn logout">Logout</a>
    </nav>
</header>
<div class="container">
    <h2>Booking Received!</h2>
    <?php if ($booking_details): ?>
        <div class="confirmation-box">
            <h3>Your booking for <?= htmlspecialchars($booking_details['title']) ?> is waiting for payment.</h3>
            <p>Please show this confirmation at the counter to purchase your tickets.</p>
            <p><b>Seats:</b> <?= htmlspecialchars($booking_details['seats']) ?></p>
            <p>Your booking will be held until 30 minutes before the show starts.</p>
            <div class="msg info">Your booking status is: PENDING</div>
        </div>
    <?php else: ?>
        <div class="msg error">Could not retrieve booking details.</div>
    <?php endif; ?>
</div>
<footer>
    © 2025 Movie Ticket Booking System<br>
    Developed by: Riwaj Dahal
</footer>
</body>
</html> 