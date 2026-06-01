<?php
session_start();
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
unset($_SESSION['esewa_user_id']);
header('Location: index.php');
exit;
?>
<footer>
    © 2025 Movie Ticket Booking System<br>
    Developed by: Riwaj Dahal
</footer> 