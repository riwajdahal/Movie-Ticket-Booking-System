<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $esewa_id = trim($_POST['esewa_id'] ?? '');
    $esewa_mpin = $_POST['esewa_mpin'] ?? '';

    if (!$esewa_id || !$esewa_mpin) {
        $_SESSION['esewa_signup_error'] = 'All fields are required!';
        header('Location: esewa_payment.php');
        exit;
    }

    $stmt = $conn->prepare('SELECT id FROM esewa_users WHERE esewa_id = ?');
    $stmt->bind_param('s', $esewa_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $_SESSION['esewa_signup_error'] = 'eSewa ID already exists!';
        header('Location: esewa_payment.php');
        exit;
    }

    $hashed_mpin = password_hash($esewa_mpin, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO esewa_users (esewa_id, esewa_mpin) VALUES (?, ?)');
    $stmt->bind_param('ss', $esewa_id, $hashed_mpin);
    if ($stmt->execute()) {
        $_SESSION['esewa_user_id'] = $esewa_id;
        $_SESSION['esewa_signup_success'] = 'Signup successful! You are now logged in to eSewa.';
    } else {
        $_SESSION['esewa_signup_error'] = 'Signup failed!';
    }
    header('Location: esewa_payment.php');
    exit;
}
?> 