<?php
require 'config.php';
require 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ResearchHub</title>
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
            <h1>👋 Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
            <p style="font-size: 1.1rem; color: #6b7280; margin-bottom: 2rem;">
                Role: <strong><?php echo ucfirst($_SESSION['role']); ?></strong>
            </p>

            <!-- STATS GRID -->
            <?php
            $userPapers = $conn->query("SELECT COUNT(*) FROM papers WHERE created_by = " . $_SESSION['user_id'])->fetch_row()[0];
           // $userAnnotations = $conn->query("SELECT COUNT(*) FROM annotations WHERE user_id = " . $_SESSION['user_id'])->fetch_row()[0];
            $totalPapers = $conn->query("SELECT COUNT(*) FROM papers")->fetch_row()[0];
            ?>
            
            <div class="dashboard-grid">
                <div class="stat-card">
                    <h4>Your Papers</h4>
                    <div class="stat-number"><?php echo $userPapers; ?></div>
                </div>
                
                <div class="stat-card">
                    <h4>Total Papers</h4>
                    <div class="stat-number"><?php echo $totalPapers; ?></div>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 3rem;">
                <div class="card">
                    <h3>📖 Browse Papers</h3>
                    <p>View all research papers and their annotations.</p>
                    <a href="index1.php" class="btn btn-primary btn-block" style="margin-top: 1rem;">Go to Papers</a>
                </div>
                <div class="card">
                    <h3>📝 Add New Paper</h3>
                    <p>Upload and create a new research paper.</p>
                    <a href="paper_form.php" class="btn btn-primary btn-block" style="margin-top: 1rem;">Add Paper</a>
                </div>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <div class="card">
                    <h3>👥 Manage Users</h3>
                    <p>View and manage system users and roles.</p>
                    <a href="manage_users.php" class="btn btn-primary btn-block" style="margin-top: 1rem;">Manage Users</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>