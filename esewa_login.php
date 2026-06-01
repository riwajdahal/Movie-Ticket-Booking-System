<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $esewa_id = trim($_POST['esewa_id'] ?? '');
    $esewa_mpin = $_POST['esewa_mpin'] ?? '';

    if (!$esewa_id || !$esewa_mpin) {
        $_SESSION['esewa_login_error'] = 'All fields are required!';
        header('Location: esewa_payment.php');
        exit;
    }

    $stmt = $conn->prepare('SELECT esewa_mpin FROM esewa_users WHERE esewa_id = ?');
    $stmt->bind_param('s', $esewa_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        $_SESSION['esewa_login_error'] = 'eSewa ID not found!';
        header('Location: esewa_payment.php');
        exit;
    }
    $stmt->bind_result($hashed_mpin);
    $stmt->fetch();

    if (!password_verify($esewa_mpin, $hashed_mpin)) {
        $_SESSION['esewa_login_error'] = 'Incorrect MPIN!';
        header('Location: esewa_payment.php');
        exit;
    }

    $_SESSION['esewa_user_id'] = $esewa_id;
    $_SESSION['esewa_login_success'] = 'Login successful!';
    header('Location: esewa_payment.php');
    exit;
}
?>