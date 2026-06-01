<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['esewa_payment'])) {
    $booking_ids = json_decode($_POST['booking_ids_json'] ?? '[]', true);
    $amount_paid = floatval($_POST['amount'] ?? 0);
    $total_amount_expected = floatval($_POST['total_amount_expected'] ?? -1);

    $errors = [];
    // Check if user is logged in as eSewa
    if (empty($_SESSION['esewa_user_id'])) {
        $errors[] = 'You must be logged in to eSewa.';
    }
    if (abs($amount_paid - $total_amount_expected) > 0.01) {
        $errors[] = 'The payment amount does not match the required total.';
    }

    if (!empty($errors)) {
        $_SESSION['payment_errors'] = $errors;
        $_SESSION['last_booking_ids'] = $booking_ids;
        header('Location: esewa_payment.php');
        exit;
    }
    // --- End Validation ---

    if (!empty($booking_ids)) {
        $ids_placeholder = implode(',', array_fill(0, count($booking_ids), '?'));
        $update_stmt = $conn->prepare("UPDATE bookings SET status='Confirmed', payment_method='eSewa' WHERE id IN ($ids_placeholder) AND user_id = ?");
        $types = str_repeat('i', count($booking_ids)) . 'i';
        $params = array_merge($booking_ids, [$_SESSION['user_id']]);
        $update_stmt->bind_param($types, ...$params);

        if ($update_stmt->execute()) {
            $_SESSION['ticket_booking_ids'] = $booking_ids;
            unset($_SESSION['last_booking_ids']);
            
            header('Location: ticket.php');
            exit;
        } else {
            $_SESSION['payment_errors'] = ['A database error occurred. Please try again.'];
            $_SESSION['last_booking_ids'] = $booking_ids;
            header('Location: esewa_payment.php');
            exit;
        }
    }
}

// Redirect back if accessed without valid conditions
header('Location: index.php');
exit;
?> 