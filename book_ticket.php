<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require 'db.php';
$user_id = $_SESSION['user_id'];
$showtime_id = intval($_GET['showtime_id'] ?? 0);
$error = '';
$success = '';
$movie = null;
$showtime = null;
$booked_seats = [];
if ($showtime_id) {
    $stmt = $conn->prepare('SELECT s.*, m.title, m.image_path, m.ticket_price, m.duration_minutes FROM showtimes s JOIN movies m ON s.movie_id = m.id WHERE s.id = ?');
    $stmt->bind_param('i', $showtime_id);
    $stmt->execute();
    $showtime = $stmt->get_result()->fetch_assoc();
    if ($showtime) {
        $movie = $showtime;
        $stmt = $conn->prepare('SELECT seat_number FROM bookings WHERE showtime_id = ?');
        $stmt->bind_param('i', $showtime_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $booked_seats[] = $row['seat_number'];
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $showtime) {
    $selected_seats = explode(',', $_POST['selected_seats'] ?? '');
    if (empty($selected_seats) || $selected_seats[0] === '') {
        $error = 'Please select at least one seat!';
    } else {
        $booking_ids = [];
        $error_occurred = false;
        
        foreach ($selected_seats as $seat) {
            if (in_array($seat, $booked_seats)) {
                $error = 'Seat ' . $seat . ' is already booked!';
                $error_occurred = true;
                break;
            }
        }
        
        if (!$error_occurred) {
            foreach ($selected_seats as $seat) {
                $stmt = $conn->prepare('INSERT INTO bookings (user_id, showtime_id, seat_number) VALUES (?, ?, ?)');
                $stmt->bind_param('iis', $user_id, $showtime_id, $seat);
                if ($stmt->execute()) {
                    $booking_ids[] = $conn->insert_id;
                } else {
                    $error = 'Booking failed!';
                    $error_occurred = true;
                    break;
                }
            }
            
            if (!$error_occurred && !empty($booking_ids)) {
                $_SESSION['last_booking_ids'] = $booking_ids;
                header('Location: confirm_booking.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Ticket - <?= htmlspecialchars($movie['title']) ?></title>
    <link rel="stylesheet" href="assets/styles.css">
    <script src="assets/scripts.js" defer></script>
    <style>
        .seat-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
            min-height: 30px;
        }
        .seat-tag {
            background-color: #4b5563;
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            font-size: 14px;
            border: 1px solid rgba(249, 115, 22, 0.5);
            animation: seatTagIn 0.35s cubic-bezier(0.34, 1.4, 0.64, 1) both;
        }
        @keyframes seatTagIn {
            from { opacity: 0; transform: scale(0.6) translateY(8px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .seat-tag.removing {
            animation: seatTagOut 0.25s ease forwards;
        }
        @keyframes seatTagOut {
            to { opacity: 0; transform: scale(0.6); }
        }
        .remove-seat-btn {
            margin-left: 8px;
            cursor: pointer;
            font-weight: bold;
            color: #ffca28;
            background: none;
            border: none;
            font-size: 20px;
            line-height: 1;
            padding: 0 0 2px 0;
            transition: transform 0.15s, color 0.15s;
        }
        .remove-seat-btn:hover {
            transform: scale(1.2);
            color: #fff;
        }
        #book-btn:not(:disabled) {
            animation: bookBtnReady 0.5s ease;
        }
        @keyframes bookBtnReady {
            0% { transform: scale(1); }
            50% { transform: scale(1.03); box-shadow: 0 0 16px var(--orange-glow); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
<header>
    <h1>Movie Ticket Booking System</h1>
    <nav>
        <a href="index.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='index.php') echo ' active'; ?>">Home</a>
        <a href="booking_history.php" class="nav-btn<?php if(basename($_SERVER['PHP_SELF'])=='booking_history.php') echo ' active'; ?>">My Bookings</a>
        <a href="logout.php" class="nav-btn logout">Logout</a>
    </nav>
</header>
<div class="container booking-page">
<?php if ($showtime): ?>
    <h2>Book Ticket for <?= htmlspecialchars($movie['title']) ?></h2>
    <p><b>Date:</b> <?= htmlspecialchars($showtime['date']) ?> | <b>Time:</b> <?= htmlspecialchars($showtime['time']) ?> | <b>Screen:</b> <?= htmlspecialchars($showtime['screen_number']) ?></p>
    <p><b>Price per ticket:</b> Rs. <?= number_format($movie['ticket_price'],2) ?></p>
    <form method="post" class="form-animated booking-form">
        <p class="screen-label">Select your seats</p>
        <div class="seat-map" id="seat-map">
        <?php
        $rows = 5; $cols = 10;
        for ($r = 1; $r <= $rows; $r++) {
            for ($c = 1; $c <= $cols; $c++) {
                $seat = chr(64+$r).$c;
                $booked = in_array($seat, $booked_seats);
                echo '<div class="seat'.($booked?' booked':'').'" data-seat="'.$seat.'" onclick="toggleSeat(this)">'.$seat.'</div>';
            }
        }
        ?>
        </div>
        <div id="selected-seats-info" class="selected-seats-info" aria-live="polite">
            <p>Selected Seats:</p>
            <div id="selected-seats-list" class="seat-tags"><span>None</span></div>
            <p>Total Amount: Rs. <span id="total-amount">0.00</span></p>
        </div>
        <input type="hidden" name="selected_seats" id="selected-seats-input">
        <button class="btn" type="submit" id="book-btn" disabled>Book Selected Seats</button>
    </form>
    <?php if ($error): ?><div class="msg error"><?= $error ?></div><?php endif; ?>
<?php else: ?>
    <p>Showtime not found.</p>
<?php endif; ?>
</div>
<script>
let selectedSeats = new Set();
const ticketPrice = <?= $movie ? $movie['ticket_price'] : 0 ?>;

function playSeatAnimation(seatElement, className) {
    seatElement.classList.remove('seat-pop-in', 'seat-pop-out');
    void seatElement.offsetWidth;
    seatElement.classList.add(className);
    seatElement.addEventListener('animationend', function onEnd() {
        seatElement.classList.remove(className);
        seatElement.removeEventListener('animationend', onEnd);
    }, { once: true });
}

function toggleSeat(seatElement) {
    if (seatElement.classList.contains('booked')) return;

    const seatNumber = seatElement.dataset.seat;
    if (selectedSeats.has(seatNumber)) {
        selectedSeats.delete(seatNumber);
        seatElement.classList.remove('selected');
        playSeatAnimation(seatElement, 'seat-pop-out');
    } else {
        selectedSeats.add(seatNumber);
        seatElement.classList.add('selected');
        playSeatAnimation(seatElement, 'seat-pop-in');
    }

    updateSelectedSeatsInfo();
}

function updateSelectedSeatsInfo() {
    const selectedSeatsList = document.getElementById('selected-seats-list');
    const totalAmountSpan = document.getElementById('total-amount');
    const bookBtn = document.getElementById('book-btn');
    const selectedSeatsInput = document.getElementById('selected-seats-input');
    const infoPanel = document.getElementById('selected-seats-info');
    const prevCount = selectedSeatsList.querySelectorAll('.seat-tag').length;

    selectedSeatsList.innerHTML = '';

    if (selectedSeats.size === 0) {
        selectedSeatsList.innerHTML = '<span>None</span>';
        totalAmountSpan.textContent = '0.00';
        bookBtn.disabled = true;
        infoPanel.classList.remove('has-seats');
    } else {
        infoPanel.classList.add('has-seats');
        const seatsArray = Array.from(selectedSeats).sort();
        seatsArray.forEach(function (seatNumber, index) {
            const seatTag = document.createElement('div');
            seatTag.className = 'seat-tag';
            seatTag.style.animationDelay = (index * 0.05) + 's';
            seatTag.appendChild(document.createTextNode(seatNumber));

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-seat-btn';
            removeBtn.setAttribute('aria-label', 'Remove seat ' + seatNumber);
            removeBtn.innerHTML = '&times;';
            removeBtn.onclick = function () {
                const seatElementInMap = document.querySelector('.seat[data-seat="' + seatNumber + '"]');
                if (seatElementInMap) toggleSeat(seatElementInMap);
            };

            seatTag.appendChild(removeBtn);
            selectedSeatsList.appendChild(seatTag);
        });

        totalAmountSpan.textContent = (ticketPrice * selectedSeats.size).toFixed(2);
        if (selectedSeats.size !== prevCount) {
            totalAmountSpan.classList.remove('amount-bump');
            void totalAmountSpan.offsetWidth;
            totalAmountSpan.classList.add('amount-bump');
        }
        bookBtn.disabled = false;
    }

    selectedSeatsInput.value = Array.from(selectedSeats).join(',');
}
</script>
<footer>
    © 2025 Movie Ticket Booking System<br>
    Developed by: Riwaj Dahal
</footer>
</body>
</html> 