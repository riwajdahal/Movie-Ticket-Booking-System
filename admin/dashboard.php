<?php
$page_title = 'Admin Dashboard';
require '../db.php';
require 'admin_header.php';

$movies = $conn->query('SELECT COUNT(*) as total FROM movies')->fetch_assoc()['total'];
$bookings = $conn->query('SELECT COUNT(*) as total FROM bookings')->fetch_assoc()['total'];
$revenue = $conn->query('SELECT SUM(m.ticket_price) as revenue FROM bookings b JOIN showtimes s ON b.showtime_id = s.id JOIN movies m ON s.movie_id = m.id')->fetch_assoc()['revenue'] ?? 0;
$users = $conn->query('SELECT COUNT(*) as total FROM users')->fetch_assoc()['total'];
?>

<h2>Summary</h2>
<div class="dashboard-summary">
    <div class="dashboard-card">
        <div class="dashboard-card-title">Total Registered Users</div>
        <div class="dashboard-card-value"><?= $users ?></div>
    </div>
    <div class="dashboard-card">
        <div class="dashboard-card-title">Total Movies</div>
        <div class="dashboard-card-value"><?= $movies ?></div>
    </div>
    <div class="dashboard-card">
        <div class="dashboard-card-title">Total Bookings</div>
        <div class="dashboard-card-value"><?= $bookings ?></div>
    </div>
    <div class="dashboard-card">
        <div class="dashboard-card-title">Total Revenue</div>
        <div class="dashboard-card-value">Rs. <?= number_format($revenue,2) ?></div>
    </div>
</div>

<?php require 'admin_footer.php'; ?>