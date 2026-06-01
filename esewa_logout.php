<?php
session_start();
unset($_SESSION['esewa_user_id']);
header('Location: esewa_payment.php');
exit;
?> 