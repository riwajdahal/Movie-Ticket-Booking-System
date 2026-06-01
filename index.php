<?php
session_start();
require 'db.php';
$result = $conn->query('SELECT * FROM movies ORDER BY created_at DESC');
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? '';
$user_email = '';
$user_phone = '';
if ($is_logged_in) {
    $stmt = $conn->prepare('SELECT email, phone FROM users WHERE id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $user_email = $row['email'] ?? '';
    $user_phone = $row['phone'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Ticket Booking System</title>
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
    <div class="homepage-info">
        <h2>Welcome to Movie Ticket Booking System</h2>
        <p>Book your favorite movies online. Enjoy a seamless experience with easy seat selection and instant booking.</p>
        <ul>
            <li>✔ Browse all movies and showtimes</li>
            <li>✔ Visual seat selection and instant booking</li>
            <li>✔ View your booking history</li>
            <li>✔ Fully responsive and easy to use</li>
        </ul>
    </div>
    <?php if ($is_logged_in): ?>
    <div class="user-info-card">
        <h3>Welcome, <?= htmlspecialchars($user_name) ?>!</h3>
        <p><b>Email:</b> <?= htmlspecialchars($user_email) ?></p>
        <p><b>Phone:</b> <?= htmlspecialchars($user_phone) ?></p>
    </div>
    <?php endif; ?>
    <h2>Now Showing</h2>
    <div class="movie-list">
    <?php while($movie = $result->fetch_assoc()): ?>
        <div class="movie-card">
            <img src="assets/images/<?= htmlspecialchars($movie['image_path']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
            <div class="info">
                <h3><?= htmlspecialchars($movie['title']) ?></h3>
                <a href="movie_details.php?id=<?= $movie['id'] ?>" class="btn no-underline">View Details</a>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
</div>
<footer>
    © 2025 Movie Ticket Booking System<br>
    Developed by: Riwaj Dahal
</footer>
</body>
</html> 