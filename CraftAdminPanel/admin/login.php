<?php
require_once 'config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// Handle login form submission
if ($_POST) {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        setMessage('Please enter both username and password.', 'error');
    } else {
        $database = new Database();
        $conn = $database->getConnection();
        
        try {
            $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['login_time'] = time();
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                
                header('Location: dashboard.php');
                exit();
            } else {
                setMessage('Invalid username or password.', 'error');
            }
        } catch (PDOException $e) {
            setMessage('Database error occurred.', 'error');
        }
    }
}

$message = getMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1><i class="fas fa-palette"></i> Tribal Arts</h1>
                <p>Admin Panel</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type'] == 'error' ? 'error' : 'success'; ?>">
                    <i class="fas fa-<?php echo $message['type'] == 'error' ? 'exclamation-circle' : 'check-circle'; ?>"></i>
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label" for="username">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?php echo $_POST['username'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div style="margin-top: 2rem; text-align: center; color: #6c757d; font-size: 0.9rem;">
                <p><strong>Demo Credentials:</strong></p>
                <p>Username: <code>admin</code> | Password: <code>admin123</code></p>
            </div>
        </div>
    </div>
</body>
</html>