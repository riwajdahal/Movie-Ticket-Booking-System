<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require 'db.php';

$booking_ids = $_SESSION['last_booking_ids'] ?? [];
unset($_SESSION['payment_attempt_booking_ids']);

$bookings = [];
$total_amount = 0;

if (!empty($booking_ids)) {
    $ids = implode(',', array_map('intval', $booking_ids));
    $query = "SELECT b.*, m.title, m.ticket_price, s.date, s.time, s.screen_number 
              FROM bookings b 
              JOIN showtimes s ON b.showtime_id = s.id 
              JOIN movies m ON s.movie_id = m.id 
              WHERE b.id IN ($ids) AND b.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
        $total_amount += $row['ticket_price'];
    }
} else {
    header('Location: index.php'); // No booking to process
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eSewa Payment</title>
    <link rel="stylesheet" href="assets/styles.css">
    <style>
        .esewa-header { display: flex; align-items: center; justify-content: space-between; }
        .esewa-header img { height: 40px; }
        .esewa-details h4, .esewa-form h4 { font-size: 1rem; color: var(--primary); margin-top: 0; margin-bottom: 1.1rem; }
        .details-row { display: flex; justify-content: space-between; margin-bottom: 0.75rem; font-size: 0.9rem; }
        .details-row .value { font-weight: 700; color: var(--text); }
        .total-row { border-top: 1px solid var(--card-border); padding-top: 1rem; margin-top: 1rem; font-size: 1.1rem; }
        .error-list { list-style-type: none; padding: 0; margin: 0; font-size: 0.9rem; }
        .esewa-login-signup { margin-bottom: 1.5rem; }
        .esewa-form-panel { background: #f9fafb; padding: 1rem; border-radius: var(--radius); border: 2px solid var(--card-border); transition: border-color 0.3s, box-shadow 0.3s; }
        .esewa-form-panel:hover { border-color: rgba(249, 115, 22, 0.4); box-shadow: 0 0 12px var(--orange-glow); }
    </style>
</head>
<body class="payment-page">
<header>
    <h1>Movie Ticket Booking System</h1>
    <nav>
        <a href="index.php" class="nav-btn">Home</a>
        <a href="about.php" class="nav-btn">About Us</a>
    </nav>
</header>
<div class="esewa-container">
    <div class="esewa-header">
        <img src="assets/images/esewa.jpeg" alt="eSewa">
    </div>
    <div class="esewa-body">
        <div class="esewa-details">
            <h4>Payment Details</h4>
            <div class="details-row">
                <span class="label">Movie:</span>
                <span class="value"><?= htmlspecialchars($bookings[0]['title']) ?></span>
            </div>
            <div class="details-row">
                <span class="label">Date & Time:</span>
                <span class="value"><?= htmlspecialchars($bookings[0]['date']) ?> at <?= htmlspecialchars($bookings[0]['time']) ?></span>
            </div>
            <div class="details-row">
                <span class="label">Seats:</span>
                <span class="value"><?= implode(', ', array_column($bookings, 'seat_number')) ?></span>
            </div>
            <div class="details-row">
                <span class="label">Per Ticket Price:</span>
                <span class="value">NPR <?= number_format($bookings[0]['ticket_price'], 2) ?></span>
            </div>
            <div class="details-row total-row">
                <span class="label">Total Amount:</span>
                <span class="value">NPR <?= number_format($total_amount, 2) ?></span>
            </div>
        </div>
        <div class="esewa-form">
            <div class="esewa-login-signup">
                <?php if (empty($_SESSION['esewa_user_id'])): ?>
                    <div class="tab-btns">
                        <button type="button" class="tab-btn active" onclick="showTab('login')">Login</button>
                        <button type="button" class="tab-btn" onclick="showTab('signup')">Signup</button>
                    </div>
                    <div id="login-tab" class="esewa-form-panel">
                        <form method="post" action="esewa_login.php" class="form-animated" style="max-width:none;box-shadow:none;border:none;background:transparent;padding:0;">
                            <div class="form-group">
                                <label for="esewa_id_login">eSewa ID</label>
                                <input type="text" id="esewa_id_login" name="esewa_id" required>
                            </div>
                            <div class="form-group">
                                <label for="esewa_mpin_login">MPIN</label>
                                <input type="password" id="esewa_mpin_login" name="esewa_mpin" required>
                            </div>
                            <button type="submit" class="btn btn-esewa">Login</button>
                        </form>
                        <?php if (!empty($_SESSION['esewa_login_error'])): ?>
                            <div class="msg error"><?= $_SESSION['esewa_login_error']; unset($_SESSION['esewa_login_error']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div id="signup-tab" class="esewa-form-panel" style="display:none;">
                        <form method="post" action="esewa_signup.php" class="form-animated" style="max-width:none;box-shadow:none;border:none;background:transparent;padding:0;">
                            <div class="form-group">
                                <label for="esewa_id_signup">eSewa ID</label>
                                <input type="text" id="esewa_id_signup" name="esewa_id" required>
                            </div>
                            <div class="form-group">
                                <label for="esewa_mpin_signup">MPIN</label>
                                <input type="password" id="esewa_mpin_signup" name="esewa_mpin" required>
                            </div>
                            <button type="submit" class="btn btn-esewa">Signup</button>
                        </form>
                        <?php if (!empty($_SESSION['esewa_signup_error'])): ?>
                            <div class="msg error"><?= $_SESSION['esewa_signup_error']; unset($_SESSION['esewa_signup_error']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($_SESSION['esewa_signup_success'])): ?>
                            <div class="msg success"><?= $_SESSION['esewa_signup_success']; unset($_SESSION['esewa_signup_success']); ?></div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="msg success">Logged in as eSewa ID: <b><?= htmlspecialchars($_SESSION['esewa_user_id']) ?></b> <a href="esewa_logout.php" style="color:#60bb46; margin-left:10px;">(Logout)</a></div>
                    <?php if (!empty($_SESSION['esewa_login_success'])): ?>
                        <div class="msg success" style="margin-top:10px;"><?= $_SESSION['esewa_login_success']; unset($_SESSION['esewa_login_success']); ?></div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php if (!empty($_SESSION['esewa_user_id'])): ?>
            <h4>Pay with eSewa</h4>
            <?php if(isset($_SESSION['payment_errors'])): ?>
            <div class="msg error" style="text-align:left; font-size:14px;">
                <ul class="error-list">
                <?php 
                    foreach($_SESSION['payment_errors'] as $error) { echo '<li>' . htmlspecialchars($error) . '</li>'; }
                    unset($_SESSION['payment_errors']);
                ?>
                </ul>
            </div>
            <?php endif; ?>
            <form id="esewa-pay-form" class="form-animated esewa-form-panel" action="process_payment.php" method="post" style="max-width:none;">
                <div class="form-group">
                    <label for="amount">Amount</label>
                    <input type="number" step="0.01" id="amount" name="amount" value="<?= $total_amount ?>" readonly required>
                </div>
                <input type="hidden" name="esewa_payment" value="1">
                <input type="hidden" name="total_amount_expected" value="<?= $total_amount ?>">
                <input type="hidden" name="booking_ids_json" value='<?= json_encode($booking_ids) ?>'>
                <button type="submit" class="btn btn-esewa" id="pay-btn">Pay</button>
            </form>
            <div id="esewa-success-animation" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(255,255,255,0.95); z-index:9999; align-items:center; justify-content:center; flex-direction:column;">
                <div style="background:#60bb46; border-radius:50%; width:120px; height:120px; display:flex; align-items:center; justify-content:center; margin-bottom:24px;">
                    <svg width="70" height="70" viewBox="0 0 70 70"><circle cx="35" cy="35" r="33" fill="none" stroke="#fff" stroke-width="4"/><polyline points="20,38 32,50 50,25" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div style="font-size:2rem; color:#60bb46; font-weight:bold;">Payment Successful</div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
function showTab(tab) {
    document.getElementById('login-tab').style.display = (tab === 'login') ? '' : 'none';
    document.getElementById('signup-tab').style.display = (tab === 'signup') ? '' : 'none';
    var btns = document.querySelectorAll('.tab-btn');
    btns.forEach(function(btn){ btn.classList.remove('active'); });
    if(tab === 'login') btns[0].classList.add('active');
    else btns[1].classList.add('active');
}

// Payment animation logic
const payForm = document.getElementById('esewa-pay-form');
if (payForm) {
    payForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const anim = document.getElementById('esewa-success-animation');
        anim.style.display = 'flex';
        setTimeout(() => {
            payForm.submit();
        }, 2000); // 2 seconds animation
    });
}
</script>
<footer>
    © 2025 Movie Ticket Booking System<br>
    Developed by: Riwaj Dahal
</footer>
</body>
</html> 