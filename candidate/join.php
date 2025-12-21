<?php
/**
 * HireGenius - Join Interview (Candidate Entry)
 */
require_once '../includes/init.php';

$error = '';

if (isPost()) {
    $interviewCode = trim(post('interview_code', ''));
    $name = trim(post('name', ''));
    $email = trim(post('email', ''));
    
    if (!verifyCsrfToken(post('csrf_token', ''))) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($interviewCode) || empty($name) || empty($email)) {
        $error = 'Please fill in all fields.';
    } elseif (!isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Validate interview code
        $stmt = db()->prepare("SELECT * FROM interviews WHERE interview_code = ? AND status = 'active'");
        $stmt->bind_param("s", $interviewCode);
        $stmt->execute();
        $interview = $stmt->get_result()->fetch_assoc();
        
        if (!$interview) {
            $error = 'Invalid interview code or interview is not active.';
        } elseif (strtotime($interview['start_datetime']) > time()) {
            $error = 'This interview has not started yet. Please try again later.';
        } elseif (strtotime($interview['end_datetime']) < time()) {
            $error = 'This interview has ended.';
        } else {
            // Check or create candidate
            $stmt = db()->prepare("SELECT id FROM candidates WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $candidateId = $row['id'];
                // Update name if different
                $stmt = db()->prepare("UPDATE candidates SET name = ? WHERE id = ?");
                $stmt->bind_param("si", $name, $candidateId);
                $stmt->execute();
            } else {
                // Create new candidate
                $stmt = db()->prepare("INSERT INTO candidates (name, email) VALUES (?, ?)");
                $stmt->bind_param("ss", $name, $email);
                $stmt->execute();
                $candidateId = $stmt->insert_id;
            }
            
            // Check if already attempted this interview
            $stmt = db()->prepare("SELECT * FROM interview_candidates WHERE interview_id = ? AND candidate_id = ?");
            $stmt->bind_param("ii", $interview['id'], $candidateId);
            $stmt->execute();
            $existingAttempt = $stmt->get_result()->fetch_assoc();
            
            if ($existingAttempt && $existingAttempt['status'] === 'completed') {
                $error = 'You have already completed this interview.';
            } else {
                // Create or update interview candidate record
                if (!$existingAttempt) {
                    $stmt = db()->prepare("INSERT INTO interview_candidates (interview_id, candidate_id, status) VALUES (?, ?, 'invited')");
                    $stmt->bind_param("ii", $interview['id'], $candidateId);
                    $stmt->execute();
                    $interviewCandidateId = $stmt->insert_id;
                } else {
                    $interviewCandidateId = $existingAttempt['id'];
                }
                
                // Store in session
                $_SESSION['interview_id'] = $interview['id'];
                $_SESSION['interview_candidate_id'] = $interviewCandidateId;
                $_SESSION['candidate_id'] = $candidateId;
                $_SESSION['candidate_name'] = $name;
                $_SESSION['candidate_email'] = $email;
                $_SESSION['interview_title'] = $interview['title'];
                $_SESSION['time_per_question'] = $interview['time_per_question'];
                
                redirect('interview.php');
            }
        }
    }
}

$pageTitle = 'Join Interview - HireGenius';
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
                <h1>Join Interview</h1>
                <p>Enter your details to start the interview</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label for="interview_code">Interview Code *</label>
                    <input type="text" id="interview_code" name="interview_code" required 
                           value="<?= e(post('interview_code', '')) ?>" 
                           placeholder="Enter 6-digit code"
                           maxlength="6" pattern="[0-9]{6}">
                </div>
                
                <div class="form-group">
                    <label for="name">Your Full Name *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?= e(post('name', '')) ?>" 
                           placeholder="John Doe">
                </div>
                
                <div class="form-group">
                    <label for="email">Your Email *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?= e(post('email', '')) ?>" 
                           placeholder="you@example.com">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Start Interview</button>
            </form>
            
            <div class="auth-footer">
                <a href="../public/index.php">&larr; Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
