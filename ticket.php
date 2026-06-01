<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require 'db.php';

$booking_ids = $_SESSION['ticket_booking_ids'] ?? [];
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
    unset($_SESSION['ticket_booking_ids']); // Clear session to prevent reuse
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Ticket - Movie Ticket Booking System</title>
    <link rel="stylesheet" href="assets/styles.css">
    <style>
        .ticket { 
            border: 2px dashed #1a2238; 
            padding: 2rem; 
            margin-top: 2rem;
            background-color: #fdfdfd;
        }
        .ticket-header { text-align: center; margin-bottom: 2rem; }
        .ticket-header h3 { margin-bottom: 0.5rem; }
        .ticket-actions { text-align: center; margin-top: 2rem; }
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
    <h2>Payment Successful!</h2>
    <p>Here is your ticket. You can download it or find it later in your booking history.</p>
    
    <?php if (!empty($bookings)): ?>
        <div class="ticket" id="ticket-to-print">
            <div class="ticket-header">
                <h3><?= htmlspecialchars($bookings[0]['title']) ?></h3>
                <p>E-Ticket / Booking Confirmation</p>
            </div>
            <p><b>Date:</b> <?= htmlspecialchars($bookings[0]['date']) ?></p>
            <p><b>Time:</b> <?= htmlspecialchars($bookings[0]['time']) ?></p>
            <p><b>Screen:</b> <?= htmlspecialchars($bookings[0]['screen_number']) ?></p>
            <hr>
            <p><b>Seats:</b> <span style="font-weight:bold; font-size: 1.2em;"><?= implode(', ', array_column($bookings, 'seat_number')) ?></span></p>
            <p><b>Price per Ticket:</b> Rs. <?= number_format($bookings[0]['ticket_price'], 2) ?></p>
            <p><b>Total Tickets:</b> <?= count($bookings) ?></p>
            <p><b>Total Paid:</b> Rs. <?= number_format($total_amount,2) ?></p>
            <hr>
            <p style="text-align:center; font-size: 0.9em;">Thank you for booking with Movie Ticket Booking System!</p>
        </div>
        <div class="ticket-actions">
            <a href="download_ticket.php?booking_ids=<?= urlencode(json_encode($booking_ids)) ?>" class="btn">Download Ticket</a>
            <a href="index.php" class="btn btn-secondary">Back to Home</a>
        </div>
        
    <?php else: ?>
        <div class="msg error">Could not find your ticket details. Please check your <a href="booking_history.php">booking history</a>.</div>
    <?php endif; ?>
</div>

<script>
function downloadTicket() {
    window.print();
}
</script>

<footer>
    © 2025 Movie Ticket Booking System<br>
    Developed by: Riwaj Dahal
</footer>
</body>
</html> 