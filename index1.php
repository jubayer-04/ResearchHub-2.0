<?php
require 'config.php';
require 'auth.php';

// Get search parameters from form
$search_tag = isset($_GET['search_tag']) ? trim($_GET['search_tag']) : '';
$search_year = isset($_GET['search_year']) ? trim($_GET['search_year']) : '';

// Base SQL query
$sql = 'SELECT * FROM papers WHERE 1=1';
$params = [];
$types = '';

// --- NEW LOGIC: ROLE-BASED ACCESS CONTROL ---
// If the user is NOT an admin, restrict the query to only their own papers.
if ($_SESSION['role'] !== 'admin') {
    $sql .= " AND created_by = ?";
    $params[] = $_SESSION['user_id'];
    $types .= 'i';
}
// --------------------------------------------

// Add search filter for tag/title/author if provided
if (!empty($search_tag)) {
    $sql .= " AND (tags LIKE ? OR title LIKE ? OR authors LIKE ?)";
    $params = array_merge($params, ["%$search_tag%", "%$search_tag%", "%$search_tag%"]);
    $types .= 'sss';
}

// Add search filter for year if provided
if (!empty($search_year)) {
    $sql .= " AND year = ?";
    $params[] = (int)$search_year;
    $types .= 'i';
}

$sql .= ' ORDER BY created_at DESC';

$stmt = $conn->prepare($sql);

if ($types) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Get all available years for dropdown
// Note: We might want to restrict this filter based on role too, 
// but usually it's fine to see all available years in the filter dropdown.
$year_sql = 'SELECT DISTINCT year FROM papers WHERE year IS NOT NULL ORDER BY year DESC';
$year_stmt = $conn->prepare($year_sql);
$year_stmt->execute();
$year_result = $year_stmt->get_result();
$available_years = [];
while ($year_row = $year_result->fetch_assoc()) {
    if (!empty($year_row['year'])) {
        $available_years[] = $year_row['year'];
    }
}
$year_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ResearchHub</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .search-container {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 1rem;
            align-items: end;
            margin-bottom: 2rem;
        }
        
        .search-input-group {
            display: flex;
            flex-direction: column;
        }
        
        .search-input-group label {
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .search-input-group input,
        .search-input-group select {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .search-input-group input:focus,
        .search-input-group select:focus {
            outline: none;
            border-color: #2d7ab8;
            box-shadow: 0 0 0 2px rgba(45, 122, 184, 0.1);
        }
        
        .search-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .search-buttons button {
            padding: 0.75rem 1.5rem;
            background-color: #2d7ab8;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .search-buttons button:hover {
            background-color: #225a99;
        }
        
        .clear-search {
            padding: 0.75rem 1rem;
            background-color: #999;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .clear-search:hover {
            background-color: #777;
        }
        
        .search-result-info {
            margin-bottom: 1rem;
            padding: 1rem;
            background-color: #e8f4f8;
            border-radius: 4px;
            color: #333;
            border-left: 4px solid #2d7ab8;
        }
        
        .search-filters {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .filter-tag {
            display: inline-block;
            background-color: #2d7ab8;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            margin-right: 0.5rem;
            margin-bottom: 0.25rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand"> ResearchHub</div>
        <div class="navbar-nav">
            <a href="dashboard.php">📊 Dashboard</a>
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

    <div class="container">
        <div class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1>📚 Research Papers</h1>
                <a href="paper_form.php" class="btn btn-primary btn-lg">+ Add New Paper</a>
            </div>

            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-body">
                    <form method="GET" action="index1.php" class="search-container">
                        <div class="search-input-group">
                            <label for="search_tag">🔍 Search by Tag/Title/Author</label>
                            <input 
                                type="text" 
                                id="search_tag"
                                name="search_tag" 
                                placeholder="e.g., Machine Learning, AI, Author Name..."
                                value="<?php echo htmlspecialchars($search_tag); ?>"
                            >
                        </div>
                        
                        <div class="search-input-group">
                            <label for="search_year">📅 Filter by Year</label>
                            <select id="search_year" name="search_year">
                                <option value="">All Years</option>
                                <?php foreach ($available_years as $year): ?>
                                    <option value="<?php echo $year; ?>" <?php echo $search_year == $year ? 'selected' : ''; ?>>
                                        <?php echo $year; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="search-buttons">
                            <button type="submit">🔍 Search</button>
                            <?php if (!empty($search_tag) || !empty($search_year)): ?>
                                <a href="index1.php" class="clear-search">✕ Clear</a>
                            <?php endif; ?>
                        </div>
                    </form>
                    
                    <?php if (!empty($search_tag) || !empty($search_year)): ?>
                        <div class="search-result-info">
                            <div style="margin-bottom: 0.5rem;">
                                <strong>Active Filters:</strong>
                            </div>
                            <div class="search-filters">
                                <?php if (!empty($search_tag)): ?>
                                    <span class="filter-tag">Tag: <?php echo htmlspecialchars($search_tag); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($search_year)): ?>
                                    <span class="filter-tag">Year: <?php echo htmlspecialchars($search_year); ?></span>
                                <?php endif; ?>
                            </div>
                            <div style="margin-top: 0.5rem; font-size: 0.95rem;">
                                Found <strong><?php echo $result->num_rows; ?></strong> paper<?php echo $result->num_rows !== 1 ? 's' : ''; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($result->num_rows === 0): ?>
                <div class="card">
                    <div class="card-body" style="text-align: center;">
                        <p style="color: #999; font-size: 1.1rem;">
                            <?php echo (!empty($search_tag) || !empty($search_year)) ? '❌ No papers found matching your filters.' : '📭 No papers yet.'; ?>
                        </p>
                        <?php if (empty($search_tag) && empty($search_year)): ?>
                            <a href="paper_form.php" class="btn btn-primary">+ Create Your First Paper</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div style="display: grid; gap: 1rem;">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="card">
                            <div class="card-header">
                                <h3 style="margin: 0;">
                                    <a href="view_paper.php?id=<?php echo $row['id']; ?>" style="color: #2d7ab8; text-decoration: none;">
                                        <?php echo htmlspecialchars($row['title']); ?>
                                    </a>
                                </h3>
                                <p style="color: #666; margin: 0.5rem 0 0 0; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($row['authors']); ?> 
                                    <?php if ($row['year']): ?>
                                        <a href="index1.php?search_year=<?php echo $row['year']; ?>" style="color: #2d7ab8; text-decoration: none; margin-left: 0.5rem;">
                                            📅 (<?php echo $row['year']; ?>)
                                        </a>
                                    <?php else: ?>
                                        (N/A)
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="card-body">
                                <?php if ($row['remark']): ?>
                                    <p style="margin-bottom: 1rem;">
                                        <strong>Abstract:</strong> <?php echo htmlspecialchars(substr($row['remark'], 0, 150)); ?>...
                                    </p>
                                <?php endif; ?>

                                <?php if ($row['tags']): ?>
                                    <p style="margin-bottom: 1rem;">
                                        <strong>Tags:</strong>
                                        <?php foreach (explode(',', $row['tags']) as $tag): ?>
                                            <a href="index1.php?search_tag=<?php echo urlencode(trim($tag)); ?>" class="tag primary" style="text-decoration: none; cursor: pointer;">
                                                <?php echo htmlspecialchars(trim($tag)); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </p>
                                <?php endif; ?>

                                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                                    <a href="view_paper.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">📖 View</a>
                                    <a href="paper_form.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary">✏️ Edit</a>
                                    <a href="delete_paper.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this paper?');">🗑️ Delete</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>

<?php include 'footer.php'; ?>