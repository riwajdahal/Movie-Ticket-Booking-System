<?php
require 'db.php';
$id = intval($_GET['id'] ?? 0);
$movie = null;
$showtimes = [];
if ($id) {
    $stmt = $conn->prepare('SELECT * FROM movies WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();
    $stmt = $conn->prepare('SELECT * FROM showtimes WHERE movie_id = ? ORDER BY date, time');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $showtimes = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $movie ? htmlspecialchars($movie['title']) : 'Movie Details' ?> - Movie Ticket Booking System</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<header>
    <h1>Movie Ticket Booking System</h1>
    <nav>
        <a href="index.php">Home</a>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    </nav>
</header>
<div class="container">
<?php if ($movie): ?>
    <div class="movie-details-flex">
        <img class="movie-details-poster" src="assets/images/<?= htmlspecialchars($movie['image_path']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
        <div class="movie-details-info">
            <h2><?= htmlspecialchars($movie['title']) ?></h2>
            <p><?= nl2br(htmlspecialchars($movie['description'])) ?></p>
            <p><b>Duration:</b> <?= $movie['duration_minutes'] ?></p>
            <p><b>Price:</b> Rs. <?= number_format($movie['ticket_price'],2) ?></p>
            <h3>Showtimes</h3>
            <?php if ($showtimes->num_rows): ?>
                <ul class="showtimes-list">
                <?php while($show = $showtimes->fetch_assoc()): ?>
                    <li class="showtime-item">
                        <span><?= htmlspecialchars($show['date']) ?> at <?= htmlspecialchars($show['time']) ?> (Screen <?= htmlspecialchars($show['screen_number']) ?>)</span>
                        <a class="btn" href="book_ticket.php?showtime_id=<?= $show['id'] ?>">Book</a>
                    </li>
                <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No showtimes available.</p>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <p>Movie not found.</p>
<?php endif; ?>
</div>
<footer>
    © 2025 Movie Ticket Booking System<br>
    Developed by: Riwaj Dahal
</footer>
</body>
</html> 