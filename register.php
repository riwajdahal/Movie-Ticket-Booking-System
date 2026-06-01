<?php
session_start();
require 'db.php';
$error = $_SESSION['register_error'] ?? '';
$success = $_SESSION['register_success'] ?? '';
unset($_SESSION['register_error'], $_SESSION['register_success']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (!$name || !$email || !$phone || !$password || !$confirm) {
        $_SESSION['register_error'] = 'All fields are required!';
    } elseif (!preg_match('/^[a-zA-Z ]+$/', $name)) {
        $_SESSION['register_error'] = 'Name must contain only letters and spaces!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@gmail\.com$/', $email)) {
        $_SESSION['register_error'] = 'Email must be a valid Gmail address (ending with @gmail.com)!';
    } elseif (!preg_match('/^(98|97)[0-9]{8}$/', $phone)) {
        $_SESSION['register_error'] = 'Phone number must start with 98 or 97 and be 10 digits!';
    } elseif ($password !== $confirm) {
        $_SESSION['register_error'] = 'Passwords do not match!';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? OR phone = ?');
        $stmt->bind_param('ss', $email, $phone);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $_SESSION['register_error'] = 'This email or phone number is already used!';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssss', $name, $email, $phone, $hash);
            if ($stmt->execute()) {
                $_SESSION['register_success'] = 'Registration successful! Please login.';
                header('Location: register.php');
                exit;
            } else {
                $_SESSION['register_error'] = 'Registration failed!';
            }
        }
    }
    header('Location: register.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration - Movie Ticket Booking System</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<header>
    <h1>Movie Ticket Booking System</h1>
    <nav>
        <a href="index.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='index.php') echo ' active'; ?>">Home</a>
        <a href="login.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='login.php') echo ' active'; ?>">Login</a>
        <a href="register.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='register.php') echo ' active'; ?>">Register</a>
        <a href="about.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='about.php') echo ' active'; ?>">About Us</a>
        <a href="contact.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='contact.php') echo ' active'; ?>">Contact Us</a>
    </nav>
</header>
<div class="container container--auth">
    <h2>User Register</h2>
    <?php if ($error): ?><div class="msg error"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="msg success"><?= $success ?></div><?php endif; ?>
    <form method="post" class="form-animated" id="register-form" novalidate>
        <input type="text" name="name" placeholder="Full Name" required pattern="[a-zA-Z ]+">
        <input type="email" name="email" id="email" placeholder="Email" required>
        <input type="text" name="phone" id="phone" placeholder="Phone Number" required pattern="^(98|97)[0-9]{8}$">
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button class="btn" type="submit">Register</button>
    </form>
</div>
<script>
document.getElementById('register-form').addEventListener('submit', function(e) {
    let valid = true;
    // Email validation
    const email = document.getElementById('email');
    if (!/^.+@gmail\.com$/.test(email.value)) {
        email.setCustomValidity('Only Gmail addresses are allowed.');
        email.reportValidity();
        valid = false;
    } else {
        email.setCustomValidity('');
    }
    // Phone validation
    const phone = document.getElementById('phone');
    if (!/^(98|97)[0-9]{8}$/.test(phone.value)) {
        phone.setCustomValidity('Phone number must start with 98 or 97 and be 10 digits.');
        phone.reportValidity();
        valid = false;
    } else {
        phone.setCustomValidity('');
    }
    if (!valid) e.preventDefault();
});
</script>
<footer>
    © 2025 Movie Ticket Booking System<br>
    Developed by: Riwaj Dahal
</footer>
</body>
</html> 