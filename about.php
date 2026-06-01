<?php
session_start();
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Movie Ticket Booking System</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<header>
    <h1>Movie Ticket Booking System</h1>
    <nav>
        <a href="index.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='index.php') echo ' active'; ?>">Home</a>
        <?php if (!$is_logged_in): ?>
            <a href="login.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='login.php') echo ' active'; ?>">Login</a>
            <a href="register.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='register.php') echo ' active'; ?>">Register</a>
            <a href="about.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='about.php') echo ' active'; ?>">About Us</a>
            <a href="contact.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='contact.php') echo ' active'; ?>">Contact Us</a>
        <?php else: ?>
            <a href="booking_history.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='booking_history.php') echo ' active'; ?>">My Bookings</a>
            <a href="logout.php" class="nav-btn logout">Logout</a>
        <?php endif; ?>
    </nav>
</header>
<div class="container">
    <section class="info-section">
        <h2>About Us</h2>
        <p>The Movie Ticket Booking System is an online platform that helps you book cinema tickets from anywhere. You can see what movies are playing, check showtimes, choose your seats, and confirm your booking in one place.</p>
        <p>We built this system to save your time and make planning a movie visit easier. After you register, you can view your booking history and access your tickets whenever you need them.</p>
        <h3>Main features</h3>
        <ul>
            <li>Browse movies and available showtimes</li>
            <li>Select seats using the online seat map</li>
            <li>Book tickets and complete payment online</li>
            <li>View and manage your past bookings</li>
        </ul>
    </section>
</div>
<footer>
    © 2025 Movie Ticket Booking System<br>
    Developed by: Riwaj Dahal
</footer>
</body>
</html> 