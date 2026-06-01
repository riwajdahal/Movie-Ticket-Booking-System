<?php
$page_title = 'View Bookings';
require '../db.php';
require 'admin_header.php';

if (isset($_POST['update_status']) && isset($_POST['booking_time']) && isset($_POST['status'])) {
    $stmt = $conn->prepare('UPDATE bookings SET status=? WHERE booking_time=?');
    $stmt->bind_param('ss', $_POST['status'], $_POST['booking_time']);
    $stmt->execute();
}

$result = $conn->query('SELECT 
    b.user_id, b.showtime_id,
    u.name AS user_name, u.phone AS user_phone,
    m.title AS movie_title, m.ticket_price,
    s.date, s.time, s.screen_number,
    MIN(b.booking_time) AS booking_time,
    GROUP_CONCAT(b.seat_number ORDER BY b.seat_number) AS seats,
    b.status, b.payment_method,
    COUNT(b.id) AS num_seats
FROM bookings b 
JOIN users u ON b.user_id = u.id 
JOIN showtimes s ON b.showtime_id = s.id 
JOIN movies m ON s.movie_id = m.id 
GROUP BY b.user_id, b.showtime_id, b.status, b.payment_method,
         u.name, u.phone, m.title, m.ticket_price, s.date, s.time, s.screen_number
ORDER BY booking_time DESC');

$stats = ['Total' => 0, 'Confirmed' => 0, 'Pending' => 0, 'Cancelled' => 0];
$stats_result = $conn->query('SELECT status, COUNT(*) AS count FROM bookings GROUP BY status');
while ($s = $stats_result->fetch_assoc()) {
    $stats[$s['status']] = (int) $s['count'];
    $stats['Total'] += (int) $s['count'];
}
?>
<div class="admin-page view-bookings-page">
    <div class="vb-layout">
        <aside class="vb-sidebar">
            <section class="vb-panel">
                <h3 class="vb-panel-title">Stats</h3>
                <ul class="vb-stats">
                    <li><span>Total</span><strong><?= $stats['Total'] ?></strong></li>
                    <li class="st-confirmed"><span>Confirmed</span><strong><?= $stats['Confirmed'] ?></strong></li>
                    <li class="st-pending"><span>Pending</span><strong><?= $stats['Pending'] ?></strong></li>
                    <li class="st-cancelled"><span>Cancelled</span><strong><?= $stats['Cancelled'] ?></strong></li>
                </ul>
            </section>
            <section class="vb-panel">
                <h3 class="vb-panel-title">Filter</h3>
                <div class="vb-filters">
                    <button type="button" class="vb-filter active" data-status="all">All</button>
                    <button type="button" class="vb-filter" data-status="Confirmed">Confirmed</button>
                    <button type="button" class="vb-filter" data-status="Pending">Pending</button>
                    <button type="button" class="vb-filter" data-status="Cancelled">Cancelled</button>
                </div>
            </section>
        </aside>
        <main class="vb-main">
            <input type="search" id="booking-search" class="vb-search" placeholder="Search name or phone…" autocomplete="off">
            <div class="vb-list" id="booking-card-list">
            <?php while ($row = $result->fetch_assoc()):
                $sc = 'status-pending';
                if ($row['status'] === 'Confirmed') $sc = 'status-confirmed';
                elseif ($row['status'] === 'Cancelled') $sc = 'status-cancelled';
                $total = $row['ticket_price'] * $row['num_seats'];
            ?>
                <article class="vb-item" data-name="<?= htmlspecialchars(strtolower($row['user_name'])) ?>" data-phone="<?= htmlspecialchars($row['user_phone']) ?>" data-status="<?= htmlspecialchars($row['status']) ?>">
                    <div class="vb-item-head">
                        <div class="vb-user">
                            <span class="vb-user-name"><?= htmlspecialchars($row['user_name']) ?></span>
                            <span class="vb-user-phone"><?= htmlspecialchars($row['user_phone']) ?></span>
                        </div>
                        <form method="post" class="vb-status-form">
                            <div class="vb-status-picker">
                                <input type="hidden" name="status" value="<?= htmlspecialchars($row['status']) ?>">
                                <button type="button" class="vb-status-toggle <?= $sc ?>" aria-expanded="false" aria-haspopup="listbox">
                                    <?= htmlspecialchars($row['status']) ?>
                                </button>
                                <ul class="vb-status-menu" role="listbox" hidden>
                                    <li role="option" data-value="Pending" class="status-pending<?= $row['status'] === 'Pending' ? ' is-current' : '' ?>">Pending</li>
                                    <li role="option" data-value="Confirmed" class="status-confirmed<?= $row['status'] === 'Confirmed' ? ' is-current' : '' ?>">Confirmed</li>
                                    <li role="option" data-value="Cancelled" class="status-cancelled<?= $row['status'] === 'Cancelled' ? ' is-current' : '' ?>">Cancelled</li>
                                </ul>
                            </div>
                            <input type="hidden" name="booking_time" value="<?= htmlspecialchars($row['booking_time']) ?>">
                            <button type="submit" class="vb-btn-update" name="update_status" value="1">Update</button>
                        </form>
                    </div>
                    <p class="vb-details">
                        <span class="vb-chip"><em>Movie</em><?= htmlspecialchars($row['movie_title']) ?></span>
                        <span class="vb-chip"><em>Date</em><?= htmlspecialchars($row['date']) ?></span>
                        <span class="vb-chip"><em>Time</em><?= htmlspecialchars($row['time']) ?></span>
                        <span class="vb-chip"><em>Screen</em><?= htmlspecialchars($row['screen_number']) ?></span>
                        <span class="vb-chip"><em>Seats</em><?= htmlspecialchars($row['seats']) ?></span>
                        <span class="vb-chip"><em>Total</em>Rs.<?= number_format($total, 2) ?></span>
                        <span class="vb-chip"><em>Pay</em><?= htmlspecialchars($row['payment_method'] ?? '—') ?></span>
                        <span class="vb-chip vb-chip--time"><em>Booked</em><?= htmlspecialchars($row['booking_time']) ?></span>
                    </p>
                </article>
            <?php endwhile; ?>
            </div>
        </main>
    </div>
</div>
<script>
(function () {
    var searchInput = document.getElementById('booking-search');
    var cards = Array.from(document.querySelectorAll('.vb-item'));

    function statusClass(value) {
        if (value === 'Confirmed') return 'status-confirmed';
        if (value === 'Cancelled') return 'status-cancelled';
        return 'status-pending';
    }

    function closeAllMenus() {
        document.querySelectorAll('.vb-status-menu').forEach(function (m) { m.hidden = true; });
        document.querySelectorAll('.vb-status-toggle').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
    }

    document.querySelectorAll('.vb-status-picker').forEach(function (picker) {
        var toggle = picker.querySelector('.vb-status-toggle');
        var menu = picker.querySelector('.vb-status-menu');
        var hidden = picker.querySelector('input[name="status"]');

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = !menu.hidden;
            closeAllMenus();
            if (!open) {
                menu.hidden = false;
                toggle.setAttribute('aria-expanded', 'true');
            }
        });

        menu.querySelectorAll('[role="option"]').forEach(function (opt) {
            opt.addEventListener('click', function (e) {
                e.stopPropagation();
                var value = this.getAttribute('data-value');
                hidden.value = value;
                toggle.textContent = value;
                toggle.classList.remove('status-confirmed', 'status-pending', 'status-cancelled');
                toggle.classList.add(statusClass(value));
                menu.querySelectorAll('[role="option"]').forEach(function (o) { o.classList.remove('is-current'); });
                this.classList.add('is-current');
                closeAllMenus();
            });
        });
    });

    document.addEventListener('click', closeAllMenus);

    searchInput.addEventListener('input', function () {
        var q = this.value.trim().toLowerCase();
        cards.forEach(function (card) {
            var show = card.getAttribute('data-name').includes(q) || card.getAttribute('data-phone').includes(q);
            card.style.display = show ? '' : 'none';
        });
    });

    document.querySelectorAll('.vb-filter').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.vb-filter').forEach(function (b) { b.classList.remove('active'); });
            this.classList.add('active');
            var status = this.getAttribute('data-status');
            cards.forEach(function (card) {
                card.style.display = (status === 'all' || card.getAttribute('data-status') === status) ? '' : 'none';
            });
            searchInput.value = '';
        });
    });
})();
</script>
<?php require 'admin_footer.php'; ?>
