<?php
/**
 * HireGenius - Admin Settings
 */
require_once '../includes/init.php';

requireAuth('admin', 'login.php');

// Handle settings update
if (isPost()) {
    if (!verifyCsrfToken(post('csrf_token', ''))) {
        setFlash('error', 'Invalid request.');
        redirect('settings.php');
    }
    
    $settings = [
        'site_name' => post('site_name'),
        'site_tagline' => post('site_tagline'),
        'default_time_per_question' => (int) post('default_time_per_question'),
        'max_questions_per_interview' => (int) post('max_questions_per_interview'),
        'allow_recruiter_signup' => post('allow_recruiter_signup') ? 'true' : 'false',
        'require_admin_approval' => post('require_admin_approval') ? 'true' : 'false',
    ];
    
    foreach ($settings as $key => $value) {
        $stmt = db()->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->bind_param("ss", $value, $key);
        $stmt->execute();
    }
    
    setFlash('success', 'Settings updated successfully.');
    redirect('settings.php');
}

// Get current settings
$settingsResult = db()->query("SELECT setting_key, setting_value, setting_type, description FROM settings");
$settings = [];
while ($row = $settingsResult->fetch_assoc()) {
    $settings[$row['setting_key']] = $row;
}

$pageTitle = 'Settings - HireGenius';
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
    <nav class="navbar">
        <div class="nav-brand">
            <a href="../public/index.php">Hire<span class="accent">Genius</span></a>
        </div>
        <div class="nav-menu">
            <span class="nav-user">Welcome, <?= e($_SESSION['admin_name']) ?></span>
            <a href="dashboard.php">Dashboard</a>
            <a href="recruiters.php">Recruiters</a>
            <a href="settings.php" class="active">Settings</a>
            <a href="logout.php" class="nav-logout">Logout</a>
        </div>
    </nav>

    <main class="dashboard-container">
        <?= displayFlash() ?>
        
        <header class="dashboard-header">
            <h1>System Settings</h1>
        </header>

        <section class="settings-section">
            <form method="POST" class="settings-form">
                <?= csrfField() ?>
                
                <div class="form-section">
                    <h3>General Settings</h3>
                    
                    <div class="form-group">
                        <label for="site_name">Site Name</label>
                        <input type="text" id="site_name" name="site_name" 
                               value="<?= e($settings['site_name']['setting_value'] ?? 'HireGenius') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="site_tagline">Tagline</label>
                        <input type="text" id="site_tagline" name="site_tagline" 
                               value="<?= e($settings['site_tagline']['setting_value'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Interview Settings</h3>
                    
                    <div class="form-group">
                        <label for="default_time_per_question">Default Time per Question (seconds)</label>
                        <input type="number" id="default_time_per_question" name="default_time_per_question" 
                               min="30" max="600"
                               value="<?= e($settings['default_time_per_question']['setting_value'] ?? 180) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="max_questions_per_interview">Max Questions per Interview</label>
                        <input type="number" id="max_questions_per_interview" name="max_questions_per_interview" 
                               min="1" max="50"
                               value="<?= e($settings['max_questions_per_interview']['setting_value'] ?? 20) ?>">
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Registration Settings</h3>
                    
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" name="allow_recruiter_signup" 
                                   <?= ($settings['allow_recruiter_signup']['setting_value'] ?? 'true') === 'true' ? 'checked' : '' ?>>
                            Allow Recruiter Sign Up
                        </label>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" name="require_admin_approval" 
                                   <?= ($settings['require_admin_approval']['setting_value'] ?? 'true') === 'true' ? 'checked' : '' ?>>
                            Require Admin Approval for Recruiters
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
