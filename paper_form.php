<?php
require 'config.php';
require 'auth.php';

// BLOCK VIEWERS from creating or editing papers
if ($_SESSION['role'] === 'viewer') {
    http_response_code(403);
    die('<div class="alert alert-error" style="margin: 2rem; padding: 1.5rem;"><div class="alert-icon">✕</div><div><h3>Access Denied</h3><p>Viewers are not allowed to create or edit papers. Contact an administrator to upgrade your role.</p></div></div>');
}

$id = (int)($_GET['id'] ?? 0);
$is_edit = $id > 0;

$paper = [
    'title' => '',
    'link' => '',
    'year' => '',
    'authors' => '',
    'methodology' => '',
    'limitations' => '',
    'remark' => '',
    'tags' => ''
];

if ($is_edit) {
    $stmt = $conn->prepare('SELECT * FROM papers WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $paper = $res->fetch_assoc();
    $stmt->close();

    if (!$paper) {
        die('Paper not found.');
    }
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $year = (int)($_POST['year'] ?? 0);
    $authors = trim($_POST['authors'] ?? '');
    $methodology = trim($_POST['methodology'] ?? '');
    $limitations = trim($_POST['limitations'] ?? '');
    $remark = trim($_POST['remark'] ?? '');
    $tags = trim($_POST['tags'] ?? '');

    if ($title === '' || $authors === '') {
        $errors[] = 'Title and authors are required.';
    }

    $file_path = $paper['file_path'] ?? null;

    if (!empty($_FILES['file']['name'])) {
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            $errors[] = 'Only PDF files are allowed.';
        } else {
            $newName = time() . '_' . basename($_FILES['file']['name']);
            $target = 'uploads/' . $newName;

            if (!is_dir('uploads')) {
                mkdir('uploads', 0755, true);
            }

            if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
                $file_path = $newName;
            } else {
                $errors[] = 'File upload failed.';
            }
        }
    }

    if (empty($errors)) {
        if ($is_edit) {
            $stmt = $conn->prepare('UPDATE papers SET title=?, link=?, file_path=?, year=?, authors=?, methodology=?, limitations=?, remark=?, tags=? WHERE id=?');
            $stmt->bind_param('sssisssssi', $title, $link, $file_path, $year, $authors, $methodology, $limitations, $remark, $tags, $id);
            $stmt->execute();
            $stmt->close();
            $success = '✓ Paper updated successfully!';
        } else {
            $stmt = $conn->prepare('INSERT INTO papers (title, link, file_path, year, authors, methodology, limitations, remark, tags, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $uid = $_SESSION['user_id'];
            $stmt->bind_param('sssisssssi', $title, $link, $file_path, $year, $authors, $methodology, $limitations, $remark, $tags, $uid);
            $stmt->execute();
            $id = $stmt->insert_id;
            $stmt->close();
            $is_edit = true;
            $success = '✓ Paper created successfully!';
        }

        // Reload paper data
        $stmt = $conn->prepare('SELECT * FROM papers WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $paper = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit Paper' : 'Add New Paper'; ?> - ResearchHub</title>
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

            <h1><?php echo $is_edit ? '✏️ Edit Paper' : '📝 Add New Paper'; ?></h1>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <div class="alert-icon">✓</div>
                    <div>
                        <h4><?php echo $success; ?></h4>
                        <p><a href="index1.php">View all papers</a> or <a href="view_paper.php?id=<?php echo $id; ?>">view this paper</a></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error">
                    <div class="alert-icon">✕</div>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endforeach; ?>

            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    <!-- Title -->
                    <div class="form-group">
                        <label for="title">Paper Title <span>*</span></label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($paper['title']); ?>" placeholder="Enter the paper title..." required>
                    </div>

                    <!-- Authors -->
                    <div class="form-group">
                        <label for="authors">Authors <span>*</span></label>
                        <input type="text" id="authors" name="authors" value="<?php echo htmlspecialchars($paper['authors']); ?>" placeholder="e.g., Nazia Sultana Marjan, Anisa Azad Oishy, Romana Akhter Runa, Jubayer Hossain, Md. Ismail Hossain Sifat, Al Nahian Habib" required>
                    </div>

                    <!-- Year -->
                    <div class="form-group">
                        <label for="year">Year</label>
                        <input type="number" id="year" name="year" value="<?php echo $paper['year']; ?>" placeholder="2024" min="1900" max="<?php echo date('Y') + 5; ?>">
                    </div>

                    <!-- Link -->
                    <div class="form-group">
                        <label for="link">Paper Link (URL)</label>
                        <input type="url" id="link" name="link" value="<?php echo htmlspecialchars($paper['link']); ?>" placeholder="https://example.com/paper">
                    </div>

                    <!-- PDF File -->
                    <div class="form-group">
                        <label for="file">PDF File <?php echo !$is_edit ? '<span>*</span>' : ''; ?></label>
                        <input type="file" id="file" name="file" accept=".pdf" <?php echo !$is_edit ? 'required' : ''; ?>>
                        <?php if ($paper['file_path'] ?? false): ?>
                            <small style="display: block; margin-top: 0.5rem;">Current file: <a href="uploads/<?php echo htmlspecialchars($paper['file_path']); ?>">View PDF</a></small>
                        <?php endif; ?>
                    </div>

                    <!-- Methodology -->
                    <div class="form-group">
                        <label for="methodology">Methodology</label>
                        <textarea id="methodology" name="methodology" placeholder="Describe the research methodology..."><?php echo htmlspecialchars($paper['methodology']); ?></textarea>
                    </div>

                    <!-- Limitations -->
                    <div class="form-group">
                        <label for="limitations">Limitations</label>
                        <textarea id="limitations" name="limitations" placeholder="Describe the study limitations..."><?php echo htmlspecialchars($paper['limitations']); ?></textarea>
                    </div>

                    <!-- Remark / Abstract -->
                    <div class="form-group">
                        <label for="remark">Abstract / Remark</label>
                        <textarea id="remark" name="remark" placeholder="Enter the paper abstract or key remarks..."><?php echo htmlspecialchars($paper['remark']); ?></textarea>
                    </div>

                    <!-- Tags -->
                    <div class="form-group">
                        <label for="tags">Tags (comma-separated)</label>
                        <input type="text" id="tags" name="tags" value="<?php echo htmlspecialchars($paper['tags']); ?>" placeholder="AI, Healthcare, Machine Learning">
                    </div>

                    <!-- Buttons -->
                    <div class="card-footer" style="margin: 2rem -1rem -1rem; border: none;">
                        <a href="index1.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <?php echo $is_edit ? '✓ Update Paper' : '+ Create Paper'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>