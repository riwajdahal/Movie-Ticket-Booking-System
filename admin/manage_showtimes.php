<?php
$page_title = 'Manage Showtimes';
require '../db.php';
require 'admin_header.php';

$error = '';
$success = '';

$movies_result = $conn->query('SELECT DISTINCT title, id FROM movies ORDER BY title');
$movies = [];
while ($row = $movies_result->fetch_assoc()) {
    $movies[] = $row;
}

if (isset($_POST['add_showtime'])) {
    $movie_id = intval($_POST['movie_id']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $screen = trim($_POST['screen_number']);
    if ($movie_id && $date && $time && $screen) {
        $check_stmt = $conn->prepare('SELECT id FROM showtimes WHERE movie_id = ? AND date = ? AND time = ? AND screen_number = ?');
        $check_stmt->bind_param('isss', $movie_id, $date, $time, $screen);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = 'This showtime already exists!';
        } else {
            $stmt = $conn->prepare('INSERT INTO showtimes (movie_id, date, time, screen_number) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('isss', $movie_id, $date, $time, $screen);
            $stmt->execute();
            $success = 'Showtime added!';
        }
    } else {
        $error = 'All fields required!';
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query('DELETE FROM showtimes WHERE id=' . $id);
    $success = 'Showtime deleted!';
}

if (isset($_POST['edit_showtime']) && isset($_POST['edit_id'])) {
    $id = intval($_POST['edit_id']);
    $movie_id = intval($_POST['movie_id_' . $id] ?? 0);
    $date = $_POST['date_' . $id] ?? '';
    $time = $_POST['time_' . $id] ?? '';
    $screen = trim($_POST['screen_number_' . $id] ?? '');
    if ($movie_id && $date && $time && $screen) {
        $stmt = $conn->prepare('UPDATE showtimes SET movie_id=?, date=?, time=?, screen_number=? WHERE id=?');
        $stmt->bind_param('isssi', $movie_id, $date, $time, $screen, $id);
        if ($stmt->execute()) {
            $success = 'Showtime updated!';
        } else {
            $error = 'Could not update showtime.';
        }
    } else {
        $error = 'All fields required!';
    }
}

$showtimes = $conn->query('SELECT s.*, m.title FROM showtimes s JOIN movies m ON s.movie_id = m.id ORDER BY s.date, s.time');
?>
<div class="admin-page">
    <h2>Add New Showtime</h2>
    <?php if ($error && !isset($_POST['edit_showtime'])): ?><div class="msg error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success && !isset($_POST['edit_showtime'])): ?><div class="msg success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if (isset($_POST['edit_showtime']) && $error): ?><div class="msg error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if (isset($_POST['edit_showtime']) && $success): ?><div class="msg success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <form method="post" class="admin-form admin-form--showtime">
        <div class="admin-form-grid">
            <div class="form-group">
                <label>Movie</label>
                <select name="movie_id" required>
                    <option value="">Select Movie</option>
                    <?php foreach ($movies as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" required>
            </div>
            <div class="form-group">
                <label>Time</label>
                <input type="time" name="time" required>
            </div>
            <div class="form-group">
                <label>Screen Number</label>
                <input type="text" name="screen_number" required>
            </div>
            <div class="form-group form-submit-row">
                <button class="btn" name="add_showtime" type="submit">Add Showtime</button>
            </div>
        </div>
    </form>

    <h2>All Showtimes</h2>
    <form method="post" id="edit-showtime-form">
        <div class="table-wrap">
        <table class="admin-table showtimes-table" id="showtimes-table">
            <thead>
            <tr>
                <th class="col-movie">Movie</th>
                <th class="col-date">Date</th>
                <th class="col-time">Time</th>
                <th class="col-screen">Screen</th>
                <th class="col-actions">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($s = $showtimes->fetch_assoc()):
                $time_value = strlen($s['time']) >= 5 ? substr($s['time'], 0, 5) : $s['time'];
            ?>
            <tr class="showtime-row" data-showtime-id="<?= $s['id'] ?>">
                <td class="col-movie">
                    <span class="view-cell"><?= htmlspecialchars($s['title']) ?></span>
                    <select name="movie_id_<?= $s['id'] ?>" class="edit-cell" style="display:none;" required>
                        <?php foreach ($movies as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= $m['id'] == $s['movie_id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td class="col-date">
                    <span class="view-cell"><?= htmlspecialchars($s['date']) ?></span>
                    <input type="date" name="date_<?= $s['id'] ?>" value="<?= htmlspecialchars($s['date']) ?>" class="edit-cell" style="display:none;" required>
                </td>
                <td class="col-time">
                    <span class="view-cell"><?= htmlspecialchars(date('h:i A', strtotime($s['time']))) ?></span>
                    <input type="time" name="time_<?= $s['id'] ?>" value="<?= htmlspecialchars($time_value) ?>" class="edit-cell" style="display:none;" required>
                </td>
                <td class="col-screen">
                    <span class="view-cell"><?= htmlspecialchars($s['screen_number']) ?></span>
                    <input type="text" name="screen_number_<?= $s['id'] ?>" value="<?= htmlspecialchars($s['screen_number']) ?>" class="edit-cell" style="display:none;" required>
                </td>
                <td class="actions-cell col-actions">
                    <div class="action-btn-row view-cell">
                        <button type="button" class="btn edit-btn">Edit</button>
                        <a class="btn btn-danger" href="?delete=<?= $s['id'] ?>" onclick="return confirm('Delete this showtime?')">Delete</a>
                    </div>
                    <div class="action-btn-row edit-cell" style="display:none;">
                        <button type="button" class="btn btn-save save-btn">Save</button>
                        <button type="button" class="btn btn-cancel cancel-edit">Cancel</button>
                    </div>
                    <input type="hidden" name="id_<?= $s['id'] ?>" value="<?= $s['id'] ?>">
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
        <input type="hidden" name="edit_showtime" value="1">
    </form>
</div>
<script>
function setShowtimeRowMode(row, isEdit) {
    if (!row) return;
    row.classList.toggle('is-editing', isEdit);
    row.querySelectorAll('.view-cell').forEach(function(cell) {
        cell.style.display = isEdit ? 'none' : '';
    });
    row.querySelectorAll('.edit-cell').forEach(function(cell) {
        cell.style.display = isEdit ? '' : 'none';
    });
}

var showtimesTable = document.getElementById('showtimes-table');
if (showtimesTable) {
    showtimesTable.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit-btn')) {
            document.querySelectorAll('.showtime-row').forEach(function(row) { setShowtimeRowMode(row, false); });
            setShowtimeRowMode(e.target.closest('.showtime-row'), true);
        }
        if (e.target.classList.contains('cancel-edit')) {
            setShowtimeRowMode(e.target.closest('.showtime-row'), false);
        }
        if (e.target.classList.contains('save-btn')) {
            var row = e.target.closest('.showtime-row');
            var idInput = row.querySelector('input[type="hidden"][name^="id_"]');
            var form = document.getElementById('edit-showtime-form');
            var editIdInput = form.querySelector('input[name="edit_id"]');
            if (!editIdInput) {
                editIdInput = document.createElement('input');
                editIdInput.type = 'hidden';
                editIdInput.name = 'edit_id';
                form.appendChild(editIdInput);
            }
            editIdInput.value = idInput.value;
            form.submit();
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.showtime-row').forEach(function(row) { setShowtimeRowMode(row, false); });
});
</script>
<?php require 'admin_footer.php'; ?>
