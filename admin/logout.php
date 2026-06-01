<?php
session_start();
unset($_SESSION['admin_logged_in']);
header('Location: login.php');
exit;
?>
<footer>
    © 2025 Movie Ticket Booking System<br>
    Developed by: Riwaj Dahal
</footer> 