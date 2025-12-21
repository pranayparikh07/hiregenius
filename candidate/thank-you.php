<?php
/**
 * HireGenius - Thank You Page
 */
require_once '../includes/init.php';

// Clear candidate session
$candidateName = $_SESSION['candidate_name'] ?? 'Candidate';

unset($_SESSION['interview_id']);
unset($_SESSION['interview_candidate_id']);
unset($_SESSION['candidate_id']);
unset($_SESSION['candidate_name']);
unset($_SESSION['candidate_email']);
unset($_SESSION['interview_title']);
unset($_SESSION['time_per_question']);

$pageTitle = 'Thank You - HireGenius';
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
    <div class="thank-you-container">
        <div class="thank-you-card">
            <div class="thank-you-icon">🎉</div>
            <h1>Thank You, <?= e($candidateName) ?>!</h1>
            <p class="thank-you-message">
                Your interview has been successfully submitted. The recruiting team will review your responses and get back to you.
            </p>
            
            <div class="thank-you-note">
                <h3>What happens next?</h3>
                <ul>
                    <li>The recruiter will review your answers</li>
                    <li>You may be contacted for further steps</li>
                    <li>Check your email for updates</li>
                </ul>
            </div>
            
            <div class="thank-you-actions">
                <a href="../public/index.php" class="btn btn-primary">Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
