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
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Admin Login</h1>
                <p>Access the administration panel</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           value="<?= e(post('email', '')) ?>" 
                           placeholder="admin@example.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            
            <div class="auth-footer">
                <a href="../public/index.php">&larr; Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
