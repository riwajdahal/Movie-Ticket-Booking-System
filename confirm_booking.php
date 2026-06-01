<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require 'db.php';
$booking_ids = $_SESSION['last_booking_ids'] ?? [];
$bookings = [];
$total_amount = 0;

if (!empty($booking_ids)) {
    $ids = implode(',', array_map('intval', $booking_ids));
    $query = "SELECT b.*, m.title, m.ticket_price, s.date, s.time, s.screen_number 
              FROM bookings b 
              JOIN showtimes s ON b.showtime_id = s.id 
              JOIN movies m ON s.movie_id = m.id 
              WHERE b.id IN ($ids) AND b.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
        $total_amount += $row['ticket_price'];
    }
    // We will unset 'last_booking_ids' after successful payment
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Booking - Movie Ticket Booking System</title>
    <link rel="stylesheet" href="assets/styles.css">
    <style>
        .payment-options {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
    </style>
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
    <h2>Confirm Your Booking</h2>
    <?php if (!empty($bookings)): ?>
        <div class="confirmation-box">
            <h3>Booking Summary</h3>
            <p><b>Movie:</b> <?= htmlspecialchars($bookings[0]['title']) ?></p>
            <p><b>Date:</b> <?= htmlspecialchars($bookings[0]['date']) ?> | <b>Time:</b> <?= htmlspecialchars($bookings[0]['time']) ?> | <b>Screen:</b> <?= htmlspecialchars($bookings[0]['screen_number']) ?></p>
            <p><b>Seats:</b> <?= implode(', ', array_column($bookings, 'seat_number')) ?></p>
            <p><b>Total Amount:</b> Rs. <?= number_format($total_amount,2) ?></p>
        </div>
        
        <div class="payment-options">
            <form action="esewa_payment.php" method="post" style="display:inline;">
                <input type="hidden" name="booking_ids_json" value='<?= json_encode($booking_ids) ?>'>
                <button type="submit" class="btn">Pay with eSewa</button>
            </form>
            <form action="counter_confirmation.php" method="post" style="display:inline;">
                 <input type="hidden" name="booking_ids_json" value='<?= json_encode($booking_ids) ?>'>
                <button type="submit" class="btn btn-secondary">Pay at Counter</button>
            </form>
        </div>

    <?php else: ?>
        <div class="msg error">No pending booking found to confirm.</div>
    <?php endif; ?>
</div>
<footer>
    © 2025 Movie Ticket Booking System<br>
    Developed by: Riwaj Dahal
</footer>
</body>
</html> 