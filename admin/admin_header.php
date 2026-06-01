<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Admin') ?> - Movie Ticket Booking System</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/admin-pages.css">
    <style>
        .admin-page-title { margin: -0.35rem 0 0.9rem; font-size: 1rem; font-weight: 500; color: var(--text-muted, #94a3b8); letter-spacing: 0.06em; text-transform: uppercase; }
        .admin-form { margin-bottom: 2rem; }
        .admin-table th, .admin-table td { vertical-align: middle; }
        .admin-table img { width: 60px; border-radius: 6px; }
        .admin-actions { display: flex; gap: 0.5rem; }
    </style>
</head>
<body>
<header class="admin-header">
    <h1>Movie Ticket Booking System</h1>
    <?php if (!empty($page_title)): ?><p class="admin-page-title"><?= htmlspecialchars($page_title) ?></p><?php endif; ?>
    <nav class="admin-nav" aria-label="Admin navigation">
        <a href="dashboard.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='dashboard.php') echo ' active'; ?>">Dashboard</a>
        <a href="manage_movies.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='manage_movies.php') echo ' active'; ?>">Manage Movies</a>
        <a href="manage_showtimes.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='manage_showtimes.php') echo ' active'; ?>">Manage Showtimes</a>
        <a href="view_bookings.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='view_bookings.php') echo ' active'; ?>">View Bookings</a>
        <a href="view_users_data.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='view_users_data.php') echo ' active'; ?>">View Users Data</a>
        <a href="logout.php" class="nav-btn logout">Logout</a>
    </nav>
</header>
<div class="container admin-shell"> 