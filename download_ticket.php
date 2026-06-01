<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require 'db.php';

$booking_ids_json = $_GET['booking_ids'] ?? '[]';
$booking_ids = json_decode($booking_ids_json, true);

if (empty($booking_ids)) {
    die('No booking specified.');
}

$ids_placeholder = implode(',', array_fill(0, count($booking_ids), '?'));
$query = "SELECT b.id as booking_id, b.seat_number, m.title, m.ticket_price, s.date, s.time, s.screen_number, u.phone
          FROM bookings b
          JOIN showtimes s ON b.showtime_id = s.id
          JOIN movies m ON s.movie_id = m.id
          JOIN users u ON b.user_id = u.id
          WHERE b.id IN ($ids_placeholder) AND b.user_id = ?";
$stmt = $conn->prepare($query);
$types = str_repeat('i', count($booking_ids)) . 'i';
$params = array_merge($booking_ids, [$_SESSION['user_id']]);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);

if (empty($bookings)) {
    die('Booking not found or access denied.');
}

// Consolidate booking info
$first_booking = $bookings[0];
$seat_numbers = implode(', ', array_column($bookings, 'seat_number'));
$total_amount = count($bookings) * $first_booking['ticket_price'];
$movie_title = $first_booking['title'];
$filename = str_replace(' ', '_', $movie_title) . '_ticket.html';

// Generate the HTML ticket content
$html_ticket = file_get_contents('ticket_template.html');

// Create QR code data with all booking details
$qr_data = "MOVIE TICKET BOOKING SYSTEM\n\n";
$qr_data .= "Movie: " . $first_booking['title'] . "\n";
$qr_data .= "Date: " . $first_booking['date'] . "\n";
$qr_data .= "Time: " . $first_booking['time'] . "\n";
$qr_data .= "Screen: " . $first_booking['screen_number'] . "\n";
$qr_data .= "Seats: " . $seat_numbers . "\n";
$qr_data .= "Price per ticket: Rs. " . number_format($first_booking['ticket_price'], 2) . "\n";
$qr_data .= "Total amount: Rs. " . number_format($total_amount, 2) . "\n";
$qr_data .= "Phone: " . $first_booking['phone'] . "\n";
$qr_data .= "Booking ID: " . implode(',', $booking_ids) . "\n\n";
$qr_data .= "Thank you for booking with Movie Ticket Booking System!";

$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qr_data);

$html_ticket = str_replace(
    ['{{movie_title}}', '{{date}}', '{{time}}', '{{screen}}', '{{seats}}', '{{total_price}}', '{{qr_url}}', '{{per_ticket_price}}'],
    [
        htmlspecialchars($movie_title),
        htmlspecialchars($first_booking['date']),
        htmlspecialchars($first_booking['time']),
        htmlspecialchars($first_booking['screen_number']),
        htmlspecialchars($seat_numbers),
        'Rs. ' . number_format($total_amount, 2),
        $qr_url,
        'Rs. ' . number_format($first_booking['ticket_price'], 2)
    ],
    $html_ticket
);

// Output as downloadable HTML file
header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($html_ticket));
echo $html_ticket;
exit; 