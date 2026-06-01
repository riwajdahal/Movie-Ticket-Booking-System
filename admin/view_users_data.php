<?php
$page_title = 'View Users Data';
require '../db.php';
require 'admin_header.php';
$users = $conn->query('SELECT id, name, email, phone, created_at FROM users ORDER BY created_at ASC');
?>
    <h2>All Registered Users</h2>
    <table class="admin-table table-animated">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Registration Date</th>
        </tr>
        <?php while($u = $users->fetch_assoc()): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['phone']) ?></td>
            <td><?= htmlspecialchars($u['created_at']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
<?php require 'admin_footer.php'; ?> 