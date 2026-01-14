<?php
require 'config.php';
require 'auth.php';

if ($_SESSION['role'] !== 'admin') {
    die('Unauthorized access.');
}

// Handle role change
if (isset($_POST['update_role'])) {
    $uid = (int)$_POST['user_id'];
    $role = trim($_POST['role']);

    if ($uid !== $_SESSION['user_id'] && in_array($role, ['admin', 'moderator', 'viewer'])) {
        $stmt = $conn->prepare('UPDATE users SET role=? WHERE id=?');
        $stmt->bind_param('si', $role, $uid);
        $stmt->execute();
        $stmt->close();
    }
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $uid = (int)$_POST['user_id'];

    if ($uid !== $_SESSION['user_id']) {
        $stmt = $conn->prepare('DELETE FROM users WHERE id=?');
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $stmt->close();
    }
}

// Get all users
$res = $conn->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - ResearchHub</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-brand">ResearchHub</div>
        <div class="navbar-nav">
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="index1.php">📄 Papers</a>
            <a href="manage_users.php">👥 Manage Users</a>
        </div>
        <div class="navbar-right">
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?></div>
                <a href="logout.php" class="btn btn-sm btn-secondary">Logout</a>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="container">
        <div class="content">
            <a href="dashboard.php" class="btn btn-secondary btn-sm" style="margin-bottom: 1.5rem;">← Back</a>

            <h1>👥 Manage Users</h1>
            <p style="font-size: 1rem; color: #6b7280; margin-bottom: 2rem;">Total users: <strong><?php echo $res->num_rows; ?></strong></p>

            <!-- USERS TABLE -->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $res->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                    <?php if ($user['id'] === $_SESSION['user_id']): ?>
                                        <span style="background: #2563eb; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; margin-left: 0.5rem;">(you)</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="role" onchange="this.form.submit();" style="padding: 6px 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                                <option value="viewer" <?php echo $user['role'] === 'viewer' ? 'selected' : ''; ?>>👁️ Viewer</option>
                                                <option value="moderator" <?php echo $user['role'] === 'moderator' ? 'selected' : ''; ?>>✏️ Moderator</option>
                                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>🔐 Admin</option>
                                            </select>
                                            <input type="hidden" name="update_role" value="1">
                                        </form>
                                    <?php else: ?>
                                        <span class="tag primary"><?php echo ucfirst($user['role']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="delete_user" value="1">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?');">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>
