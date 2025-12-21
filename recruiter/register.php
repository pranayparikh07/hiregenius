<?php
/**
 * HireGenius - Recruiter Registration
 */
require_once '../includes/init.php';

if (isLoggedIn('recruiter')) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if (isPost()) {
    $name = trim(post('name', ''));
    $email = trim(post('email', ''));
    $company = trim(post('company_name', ''));
    $phone = trim(post('phone', ''));
    $password = post('password', '');
    $confirmPassword = post('confirm_password', '');
    
    if (!verifyCsrfToken(post('csrf_token', ''))) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($name) || empty($email) || empty($company) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email exists
        $stmt = db()->prepare("SELECT id FROM recruiters WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'This email is already registered.';
        } else {
            // Insert new recruiter
            $hashedPassword = hashPassword($password);
            $stmt = db()->prepare("INSERT INTO recruiters (name, email, password, company_name, phone, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("sssss", $name, $email, $hashedPassword, $company, $phone);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! Your account is pending admin approval.';
            } else {
                $error = 'An error occurred. Please try again.';
            }
        }
    }
}

$pageTitle = 'Recruiter Sign Up - HireGenius';
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
                <h1>Recruiter Sign Up</h1>
                <p>Create your account to start hiring</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php else: ?>
            
            <form method="POST" class="auth-form">
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?= e(post('name', '')) ?>" 
                           placeholder="John Doe">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?= e(post('email', '')) ?>" 
                           placeholder="you@company.com">
                </div>
                
                <div class="form-group">
                    <label for="company_name">Company Name *</label>
                    <input type="text" id="company_name" name="company_name" required 
                           value="<?= e(post('company_name', '')) ?>" 
                           placeholder="Your Company Inc.">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?= e(post('phone', '')) ?>" 
                           placeholder="+1 234 567 8900">
                </div>
                
                <div class="form-group">
                    <label for="password">Password * (min 8 characters)</label>
                    <input type="password" id="password" name="password" required 
                           minlength="8" placeholder="Create a strong password">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           minlength="8" placeholder="Confirm your password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>
            
            <?php endif; ?>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
                <a href="../public/index.php">&larr; Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
