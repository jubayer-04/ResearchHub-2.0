<?php
require 'config.php';
require 'auth.php';

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    http_response_code(404);
    die('Paper not found.');
}

// Get paper details
$stmt = $conn->prepare('SELECT p.*, u.name AS creator_name FROM papers p LEFT JOIN users u ON p.created_by = u.id WHERE p.id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$paper = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$paper) {
    die('Paper not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($paper['title']); ?> - ResearchHub</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-brand">ResearchHub</div>
        <div class="navbar-nav">
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="index1.php">📄 Papers</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="manage_users.php">👥 Manage Users</a>
            <?php endif; ?>
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
            <a href="index1.php" class="btn btn-secondary btn-sm" style="margin-bottom: 1.5rem;">← Back to Papers</a>

            <!-- PAPER DETAILS -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h2><?php echo htmlspecialchars($paper['title']); ?></h2>
                    <p style="color: #6b7280; margin: 0.5rem 0 0 0;">
                        <?php echo htmlspecialchars($paper['authors']); ?> (<?php echo $paper['year'] ?: 'N/A'; ?>)
                    </p>
                </div>
                <div class="card-body">
                    <?php if ($paper['remark']): ?>
                        <h4>Abstract</h4>
                        <p><?php echo htmlspecialchars($paper['remark']); ?></p>
                    <?php endif; ?>

                    <?php if ($paper['methodology']): ?>
                        <h4>Methodology</h4>
                        <p><?php echo htmlspecialchars($paper['methodology']); ?></p>
                    <?php endif; ?>

                    <?php if ($paper['limitations']): ?>
                        <h4>Limitations</h4>
                        <p><?php echo htmlspecialchars($paper['limitations']); ?></p>
                    <?php endif; ?>

                    <?php if ($paper['link']): ?>
                        <p>
                            <strong>Paper Link:</strong> 
                            <a href="<?php echo htmlspecialchars($paper['link']); ?>" target="_blank">View Online</a>
                        </p>
                    <?php endif; ?>

                    <?php if ($paper['file_path']): ?>
                        <p>
                            <strong>PDF File:</strong> 
                            <a href="uploads/<?php echo htmlspecialchars($paper['file_path']); ?>" target="_blank">📥 Download PDF</a>
                        </p>
                    <?php endif; ?>

                    <?php if ($paper['tags']): ?>
                        <p style="margin-top: 1rem;">
                            <strong>Tags:</strong>
                            <?php foreach (explode(',', $paper['tags']) as $tag): ?>
                                <span class="tag primary"><?php echo trim($tag); ?></span>
                            <?php endforeach; ?>
                        </p>
                    <?php endif; ?>

                    <p style="margin-top: 1rem; font-size: 0.9rem; color: #9ca3af;">
                        Created by: <?php echo htmlspecialchars($paper['creator_name'] ?? 'Unknown'); ?>
                    </p>
                </div>
            </div>

        </div>
    </div>

</body>
</html>

<?php include 'footer.php'; ?>
