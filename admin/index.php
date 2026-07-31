<?php

/**
 * Elpis Counselling Centre - Admin Login
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            header('Location: ' . SITE_URL . '/admin/dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #3F5195;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-container {
            background: #fff;
            border-radius: 20px;
            padding: 3rem;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }

        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo img {
            height: 60px;
            margin-bottom: 1rem;
        }

        .login-logo h1 {
            font-size: 1.3rem;
            color: #3F5195;
        }

        .login-logo p {
            color: #999;
            font-size: 0.85rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 600;
            font-size: 0.9rem;
            color: #3F5195;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #D7DDD9;
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #4FA08A;
        }

        .btn {
            width: 100%;
            padding: 0.8rem;
            background: #4FA08A;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: #3F5195;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 0.8rem 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            text-align: center;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: #999;
            font-size: 0.85rem;
            text-decoration: none;
        }

        .back-link:hover {
            color: #4FA08A;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-logo">
            <img src="<?php echo SITE_URL; ?>/images/logo.jpeg" alt="Logo">
            <h1>Elpis Counselling Centre</h1>
            <p>Admin Panel Login</p>
        </div>

        <?php if ($error): ?>
            <div class="error"><?php echo h($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required
                    autocomplete="current-password">
            </div>
            <button type="submit" class="btn">Sign In</button>
        </form>

        <a href="<?php echo SITE_URL; ?>/index.php" class="back-link">&larr; Back to Website</a>
    </div>
</body>

</html>