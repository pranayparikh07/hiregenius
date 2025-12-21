<?php
/**
 * HireGenius - Admin Login
 */
require_once '../includes/init.php';

// Redirect if already logged in
if (isLoggedIn('admin')) {
    redirect('dashboard.php');
}

$error = '';

if (isPost()) {
    $email = trim(post('email', ''));
    $password = post('password', '');
    
    // Validate CSRF
    if (!verifyCsrfToken(post('csrf_token', ''))) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Check credentials
        $stmt = db()->prepare("SELECT id, name, password FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($admin = $result->fetch_assoc()) {
            if (verifyPassword($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                setFlash('success', 'Welcome back, ' . $admin['name'] . '!');
                redirect('dashboard.php');
            }
        }
        
        $error = 'Invalid email or password.';
    }
}

$pageTitle = 'Admin Login - HireGenius';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card admin-auth">
            <div class="auth-logo">
                <a href="../public/index.php">
                    <i class="fas fa-bolt"></i>
                    <span>Hire<span class="accent">Genius</span></span>
                </a>
            </div>
            
            <div class="auth-header">
                <div class="auth-icon admin">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h1>Admin Access</h1>
                <p>Sign in to the administration panel</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= e($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input type="email" id="email" name="email" required 
                           value="<?= e(post('email', '')) ?>" 
                           placeholder="admin@hiregenius.com">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" required 
                               placeholder="Enter your password">
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye" id="eye-icon"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
            
            <div class="auth-footer">
                <a href="../public/index.php">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
