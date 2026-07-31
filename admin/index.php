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
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? LIMIT 1");
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
        $error = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #3F5195;
        }

        .login-box {
            background: #fff;
            border-radius: 15px;
            padding: 3rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }

        .login-box h1 {
            text-align: center;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .login-box .subtitle {
            text-align: center;
            color: #999;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .login-logo img {
            height: 60px;
            width: auto;
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <div class="login-logo">
            <img src="<?php echo SITE_URL; ?>/images/logo.jpeg" alt="Logo">
        </div>
        <h1>Admin Login</h1>
        <p class="subtitle"><?php echo SITE_NAME; ?></p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo h($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Sign In</button>
        </form>

        <p style="text-align:center;margin-top:1.5rem;font-size:0.85rem;color:#999;">
            <a href="<?php echo SITE_URL; ?>/index.php">&larr; Back to Website</a>
        </p>
    </div>
</body>

</html>