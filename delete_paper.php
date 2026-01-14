<?php
require 'config.php';
require 'auth.php';

// BLOCK VIEWERS from deleting papers
if ($_SESSION['role'] === 'viewer') {
    http_response_code(403);
    echo '<div class="alert alert-error" style="margin: 2rem; padding: 1.5rem;"><div class="alert-icon">✕</div><div><h3>Access Denied</h3><p>Viewers are not allowed to delete papers. Contact an administrator.</p></div></div>';
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    die('Invalid paper ID.');
}

$stmt = $conn->prepare('DELETE FROM papers WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

header('Location: index1.php');