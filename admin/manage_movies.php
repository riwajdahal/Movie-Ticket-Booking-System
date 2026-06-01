<?php
$page_title = 'Manage Movies';
require '../db.php';
require 'admin_header.php';

$error = '';
$success = '';
$upload_dir = '../assets/images/';

// Handle success message from redirect
if (isset($_GET['success'])) {
    $success = 'Movie added successfully!';
}

// Add movie
if (isset($_POST['add_movie'])) {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $duration = trim($_POST['duration_minutes']);
    $price = floatval($_POST['ticket_price']);
    $img = '';
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $img = basename($_FILES['image_file']['name']);
        move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_dir . $img);
    }
    if ($title && $duration && $price && $img) {
        // Check for existing movie
        $check_stmt = $conn->prepare('SELECT id FROM movies WHERE title = ?');
        $check_stmt->bind_param('s', $title);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows > 0) {
            $error = 'A movie with this title already exists!';
        } else {
            $stmt = $conn->prepare('INSERT INTO movies (title, description, duration_minutes, image_path, ticket_price) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssd', $title, $desc, $duration, $img, $price);
            if ($stmt->execute()) {
                // Redirect to refresh the page and show the new movie
                header('Location: manage_movies.php?success=1');
                exit;
            } else {
                $error = 'Error adding movie: ' . $stmt->error;
            }
        }
    } else {
        $error = 'All fields required and poster image must be uploaded!';
    }
}
// Delete movie
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($conn->query('DELETE FROM movies WHERE id='.$id)) {
        $success = 'Movie deleted successfully!';
    } else {
        $error = 'Error deleting movie: ' . $conn->error;
    }
}
// Edit movie
if (isset($_POST['edit_movie']) && isset($_POST['edit_id'])) {
    $id = intval($_POST['edit_id']);
    $title = trim($_POST['title_' . $id]);
    $desc = trim($_POST['description_' . $id]);
    $duration = trim($_POST['duration_minutes_' . $id]);
    $price = floatval($_POST['ticket_price_' . $id]);
    $img = $_POST['current_image_' . $id];
    if (isset($_FILES['image_file_' . $id]) && $_FILES['image_file_' . $id]['error'] === UPLOAD_ERR_OK) {
        $img = basename($_FILES['image_file_' . $id]['name']);
        move_uploaded_file($_FILES['image_file_' . $id]['tmp_name'], $upload_dir . $img);
    }
    if ($title && $duration && $price && $img) {
        $stmt = $conn->prepare('UPDATE movies SET title=?, description=?, duration_minutes=?, image_path=?, ticket_price=? WHERE id=?');
        $stmt->bind_param('ssssdi', $title, $desc, $duration, $img, $price, $id);
        if ($stmt->execute()) {
            $success = 'Movie updated successfully!';
            echo "<script>
                if(confirm('Movie updated successfully!')) {
                    window.location.href = 'manage_movies.php';
                }
            </script>";
        }
    } else {
        $error = 'All fields required!';
    }
}
$movies = $conn->query('SELECT * FROM movies ORDER BY created_at DESC');
?>
<div class="admin-page">
    <h2>Add New Movie</h2>
    <?php if ($error && !isset($_POST['edit_movie'])): ?>
        <div class="msg error"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success && !isset($_POST['edit_movie'])): ?>
        <div class="msg success"><?= $success ?></div>
    <?php endif; ?>
    <form method="post" class="admin-form admin-form--movies" enctype="multipart/form-data">
        <div class="admin-form-grid">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Duration (minutes)</label>
                <input type="text" name="duration_minutes" required>
            </div>
            <div class="form-group">
                <label>Ticket Price (Rs.)</label>
                <input type="number" step="0.01" name="ticket_price" required>
            </div>
            <div class="form-group form-group--file">
                <label>Poster Image</label>
                <div class="admin-file-picker">
                    <input type="file" id="add_movie_poster" class="admin-file-input" name="image_file" accept="image/*" required>
                    <label for="add_movie_poster" class="admin-file-btn">Choose image</label>
                    <span class="admin-file-name" id="add_movie_poster_name">No file selected</span>
                </div>
            </div>
            <div class="form-group form-group--wide">
                <label>Description</label>
                <textarea name="description" rows="3"></textarea>
            </div>
            <div class="form-group form-submit-row">
                <button class="btn" name="add_movie" type="submit">Add Movie</button>
            </div>
        </div>
    </form>
    <div id="movies-list-container">
        <h2>All Movies</h2>
        <form method="post" enctype="multipart/form-data" id="edit-movie-form">
        <div class="table-wrap">
        <table class="admin-table movies-table" id="movies-table">
            <thead>
            <tr>
                <th class="col-poster">Poster</th>
                <th class="col-title">Title</th>
                <th class="col-desc">Description</th>
                <th class="col-duration">Duration</th>
                <th class="col-price">Price</th>
                <th class="col-actions">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php while($m = $movies->fetch_assoc()): ?>
            <tr class="movie-row" data-movie-id="<?= $m['id'] ?>">
                <td class="poster-cell col-poster">
                    <img src="../assets/images/<?= htmlspecialchars($m['image_path']) ?>" alt="" class="view-cell">
                    <div class="admin-file-picker edit-cell" style="display:none;">
                        <input type="file" id="file_<?= $m['id'] ?>" class="admin-file-input" name="image_file_<?= $m['id'] ?>" accept="image/*" onchange="document.getElementById('filename_<?= $m['id'] ?>').textContent = this.files[0] ? this.files[0].name : 'Keep current'">
                        <label for="file_<?= $m['id'] ?>" class="admin-file-btn">Choose image</label>
                        <span class="admin-file-name" id="filename_<?= $m['id'] ?>">Keep current</span>
                        <input type="hidden" name="current_image_<?= $m['id'] ?>" value="<?= htmlspecialchars($m['image_path']) ?>">
                    </div>
                </td>
                <td>
                    <span class="view-cell"><?= htmlspecialchars($m['title']) ?></span>
                    <input type="text" name="title_<?= $m['id'] ?>" value="<?= htmlspecialchars($m['title']) ?>" class="edit-cell" style="display:none;">
                </td>
                <td class="col-desc">
                    <span class="view-cell desc-view"><?= htmlspecialchars($m['description']) ?></span>
                    <textarea name="description_<?= $m['id'] ?>" class="edit-cell" style="display:none;" rows="2"><?= htmlspecialchars($m['description']) ?></textarea>
                </td>
                <td>
                    <span class="view-cell"><?= htmlspecialchars($m['duration_minutes']) ?></span>
                    <input type="text" name="duration_minutes_<?= $m['id'] ?>" value="<?= htmlspecialchars($m['duration_minutes']) ?>" class="edit-cell" style="display:none;">
                </td>
                <td>
                    <span class="view-cell">Rs. <?= number_format($m['ticket_price'],2) ?></span>
                    <input type="number" step="0.01" name="ticket_price_<?= $m['id'] ?>" value="<?= $m['ticket_price'] ?>" class="edit-cell" style="display:none;">
                </td>
                <td class="actions-cell col-actions">
                    <div class="action-btn-row view-cell">
                        <button class="btn edit-btn" type="button">Edit</button>
                        <a class="btn btn-danger" href="?delete=<?= $m['id'] ?>" onclick="return confirm('Delete this movie?')">Delete</a>
                    </div>
                    <div class="action-btn-row edit-cell" style="display:none;">
                        <button class="btn btn-save save-btn" type="button">Save</button>
                        <button class="btn btn-cancel cancel-edit" type="button">Cancel</button>
                    </div>
                    <input type="hidden" name="id_<?= $m['id'] ?>" value="<?= $m['id'] ?>">
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
        <input type="hidden" name="edit_movie" value="1">
        </form>
    </div>
</div>
<script>
function setRowViewMode(row, isEdit) {
    if (!row) return;
    row.classList.toggle('is-editing', isEdit);
    row.querySelectorAll('.view-cell').forEach(cell => {
        cell.style.display = isEdit ? 'none' : '';
    });
    row.querySelectorAll('.edit-cell').forEach(cell => {
        cell.style.display = isEdit ? '' : 'none';
    });
}

const table = document.getElementById('movies-table');
if (table) {
    table.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit-btn')) {
            document.querySelectorAll('.movie-row').forEach(row => setRowViewMode(row, false));
            setRowViewMode(e.target.closest('.movie-row'), true);
        }
        if (e.target.classList.contains('cancel-edit')) {
            setRowViewMode(e.target.closest('.movie-row'), false);
        }
        if (e.target.classList.contains('save-btn')) {
            const tr = e.target.closest('.movie-row');
            // Set a hidden input to indicate which movie is being edited
            const idInput = tr.querySelector('input[type="hidden"][name^="id_"]');
            if (idInput) {
                const form = document.getElementById('edit-movie-form');
                // Add a hidden input to indicate which movie to update
                let editIdInput = form.querySelector('input[name="edit_id"]');
                if (!editIdInput) {
                    editIdInput = document.createElement('input');
                    editIdInput.type = 'hidden';
                    editIdInput.name = 'edit_id';
                    form.appendChild(editIdInput);
                }
                editIdInput.value = idInput.value;
                form.submit();
            }
        }
    });
}
window.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.movie-row').forEach(row => setRowViewMode(row, false));
    var posterInput = document.getElementById('add_movie_poster');
    var posterName = document.getElementById('add_movie_poster_name');
    if (posterInput && posterName) {
        posterInput.addEventListener('change', function() {
            posterName.textContent = this.files[0] ? this.files[0].name : 'No file selected';
        });
    }
});
</script>
<?php require 'admin_footer.php'; ?> 