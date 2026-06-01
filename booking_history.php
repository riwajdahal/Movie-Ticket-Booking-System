<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require 'db.php';
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT 
    b.*, m.title, m.ticket_price, s.date, s.time, s.screen_number
FROM bookings b 
JOIN showtimes s ON b.showtime_id = s.id 
JOIN movies m ON s.movie_id = m.id 
WHERE b.user_id = ? 
ORDER BY b.booking_time DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$grouped = [];
while ($row = $result->fetch_assoc()) {
    $key = $row['booking_time'];
    if (!isset($grouped[$key])) {
        $grouped[$key] = [
            'title' => $row['title'],
            'date' => $row['date'],
            'time' => $row['time'],
            'screen_number' => $row['screen_number'],
            'ticket_price' => $row['ticket_price'],
            'status' => $row['status'] ?? 'Pending',
            'seats' => [],
            'booking_ids' => [],
            'total_price' => 0,
            'booking_time' => $row['booking_time']
        ];
    }
    $grouped[$key]['seats'][] = $row['seat_number'];
    $grouped[$key]['booking_ids'][] = $row['id'];
    $grouped[$key]['total_price'] += $row['ticket_price'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Booking History - Movie Ticket Booking System</title>
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
    <h2>My Booking History</h2>
    <?php if ($grouped): ?>
    <table class="table-animated">
        <tr><th>Movie</th><th>Date</th><th>Time</th><th>Screen</th><th>Seats</th><th>Price</th><th>Total Price</th><th>Status</th><th>Booked At</th><th>Action</th></tr>
        <?php foreach ($grouped as $g): ?>
        <tr>
            <td><?= htmlspecialchars($g['title']) ?></td>
            <td><?= htmlspecialchars($g['date']) ?></td>
            <td><?= htmlspecialchars($g['time']) ?></td>
            <td><?= htmlspecialchars($g['screen_number']) ?></td>
            <td><?= htmlspecialchars(implode(',', $g['seats'])) ?></td>
            <td>Rs. <?= number_format($g['ticket_price'],2) ?></td>
            <td>Rs. <?= number_format($g['total_price'],2) ?></td>
            <td><?= htmlspecialchars($g['status']) ?></td>
            <td><?= htmlspecialchars($g['booking_time']) ?></td>
            <td>
                <?php if ($g['status'] === 'Confirmed'): ?>
                    <a href="download_ticket.php?booking_ids=<?= urlencode(json_encode($g['booking_ids'])) ?>" class="btn btn-small" download>Download</a>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
    <p>No bookings found.</p>
    <?php endif; ?>
</div>
<footer>
    © 2025 Movie Ticket Booking System<br>
    Developed by: Riwaj Dahal
</footer>
</body>
</html> 