<?php
session_start();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Movie Ticket Booking System</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
<header>
    <h1>Movie Ticket Booking System</h1>
    <p class="admin-page-title">ADMIN LOGIN</p>
</header>
<div class="container container--auth">
    <h2>Sign in to Admin Panel</h2>
    <?php if ($error): ?><div class="msg error"><?= $error ?></div><?php endif; ?>
    <form method="post" class="form-animated">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button class="btn" type="submit">Login</button>
    </form>
</div>
<footer>
    © 2025 Movie Ticket Booking System<br>
    Developed by: Riwaj Dahal
</footer>
</body>
</html> 