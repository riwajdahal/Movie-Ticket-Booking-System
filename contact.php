<?php
session_start();
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Movie Ticket Booking System</title>
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
        <h2>Contact Us</h2>
        <p>Have questions or need help? Reach out to us:</p>
        <ul>
            <li><b>Phone:</b> +977 9862182138</li>
            <li><b>Email:</b> riwajdahal111@gmail.com</li>
            <li><b>Address:</b> Kathmandu, Nepal</li>
        </ul>
    </section>
</div>
<footer>
    © 2025 Movie Ticket Booking System<br>
    Developed by: Riwaj Dahal
</footer>
</body>
</html> 