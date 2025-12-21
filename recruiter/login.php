<?php
/**
 * HireGenius - Recruiter Login
 */
require_once '../includes/init.php';

if (isLoggedIn('recruiter')) {
    redirect('dashboard.php');
}

$error = '';

if (isPost()) {
    $email = trim(post('email', ''));
    $password = post('password', '');
    
    if (!verifyCsrfToken(post('csrf_token', ''))) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = db()->prepare("SELECT id, name, password, status FROM recruiters WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($recruiter = $result->fetch_assoc()) {
            if ($recruiter['status'] === 'pending') {
                $error = 'Your account is pending approval. Please wait for admin verification.';
            } elseif ($recruiter['status'] === 'rejected') {
                $error = 'Your account has been rejected. Please contact support.';
            } elseif ($recruiter['status'] === 'suspended') {
                $error = 'Your account has been suspended. Please contact support.';
            } elseif (verifyPassword($password, $recruiter['password'])) {
                $_SESSION['recruiter_id'] = $recruiter['id'];
                $_SESSION['recruiter_name'] = $recruiter['name'];
                setFlash('success', 'Welcome back, ' . $recruiter['name'] . '!');
                redirect('dashboard.php');
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Recruiter Login - HireGenius';
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
                <h1>Recruiter Login</h1>
                <p>Access your hiring dashboard</p>
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
                           placeholder="you@company.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Sign up here</a></p>
                <a href="../public/index.php">&larr; Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
