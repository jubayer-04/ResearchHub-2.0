<?php
require 'config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    $confirmPass = trim($_POST['confirm_password'] ?? '');

    if (!$name || !$email || !$pass) {
        $errors[] = 'All fields are required.';
    } elseif ($pass !== $confirmPass) {
        $errors[] = 'Passwords do not match.';
    } elseif (strlen($pass) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    } else {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        // FORCE VIEWER ROLE - No one can register as admin/moderator
        $role = 'viewer';

        $stmt = $conn->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $email, $hash, $role);

        if ($stmt->execute()) {
            header('Location: index.php?registered=1');
            exit;
        } else {
            $errors[] = 'Email already exists or database error.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Research Paper Tool</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }
        .register-container {
            width: 100%;
            max-width: 450px;
        }
        .register-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 3rem;
        }
        .register-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .register-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .register-header p {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1>📚 ResearchHub</h1>
                <p>Create a new account</p>
            </div>

            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error">
                    <div class="alert-icon">✕</div>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endforeach; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Jubayer Hossain" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="jubayer.cse@gmail.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
                </div>

                <div class="alert alert-info" style="margin-bottom: 1.5rem;">
                    <div class="alert-icon">ℹ️</div>
                    <div><strong>Note:</strong> New accounts are created as "Viewer". To get access, contact the admin.</div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-bottom: 1rem;">
                    Create Account
                </button>
            </form>

            <p style="text-align: center; color: #6b7280;">
                Already have an account? <a href="index.php">Sign in</a>
            </p>
        </div>
    </div>
</body>
</html>