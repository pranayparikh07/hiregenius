<?php
/**
 * HireGenius - Create Interview
 */
require_once '../includes/init.php';

requireAuth('recruiter', 'login.php');

$recruiterId = $_SESSION['recruiter_id'];
$error = '';

// Default questions
$defaultQuestions = config('default_questions') ?? [
    "Tell me about yourself.",
    "Why do you want this job?",
    "What are your greatest strengths?",
    "What are your weaknesses and how do you manage them?",
    "Where do you see yourself in 5 years?",
    "What do you know about our company?",
    "Describe a challenging situation and how you handled it.",
    "Why should we hire you?",
    "Do you have any questions for us?"
];

if (isPost()) {
    $title = trim(post('title', ''));
    $description = trim(post('description', ''));
    $startDatetime = post('start_datetime', '');
    $endDatetime = post('end_datetime', '');
    $questionType = post('question_type', 'default');
    $timePerQuestion = (int) post('time_per_question', 180);
    $customQuestions = post('custom_questions', []);
    
    if (!verifyCsrfToken(post('csrf_token', ''))) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($title) || empty($startDatetime) || empty($endDatetime)) {
        $error = 'Please fill in all required fields.';
    } elseif (strtotime($endDatetime) <= strtotime($startDatetime)) {
        $error = 'End date must be after start date.';
    } else {
        // Generate unique interview code
        do {
            $interviewCode = generateInterviewCode(6);
            $stmt = db()->prepare("SELECT id FROM interviews WHERE interview_code = ?");
            $stmt->bind_param("s", $interviewCode);
            $stmt->execute();
        } while ($stmt->get_result()->num_rows > 0);
        
        // Insert interview
        $stmt = db()->prepare("
            INSERT INTO interviews (recruiter_id, title, description, interview_code, question_type, time_per_question, start_datetime, end_datetime, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        $stmt->bind_param("issssiss", $recruiterId, $title, $description, $interviewCode, $questionType, $timePerQuestion, $startDatetime, $endDatetime);
        
        if ($stmt->execute()) {
            $interviewId = $stmt->insert_id;
            
            // Insert questions
            $questions = ($questionType === 'custom' && !empty($customQuestions)) 
                ? array_filter($customQuestions, fn($q) => !empty(trim($q)))
                : $defaultQuestions;
            
            $order = 1;
            foreach ($questions as $question) {
                $stmt = db()->prepare("INSERT INTO questions (interview_id, question_text, question_order) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $interviewId, $question, $order);
                $stmt->execute();
                $order++;
            }
            
            setFlash('success', "Interview created successfully! Share this code with candidates: $interviewCode");
            redirect('interviews.php');
        } else {
            $error = 'An error occurred. Please try again.';
        }
    }
}

$pageTitle = 'Create Interview - HireGenius';
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
            <span class="nav-user">Welcome, <?= e($_SESSION['recruiter_name']) ?></span>
            <a href="dashboard.php">Dashboard</a>
            <a href="interviews.php">Interviews</a>
            <a href="logout.php" class="nav-logout">Logout</a>
        </div>
    </nav>

    <main class="dashboard-container">
        <header class="dashboard-header">
            <h1>Create New Interview</h1>
            <a href="dashboard.php" class="btn btn-outline">&larr; Back to Dashboard</a>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="interview-form">
            <?= csrfField() ?>
            
            <div class="form-section">
                <h3>Interview Details</h3>
                
                <div class="form-group">
                    <label for="title">Interview Title *</label>
                    <input type="text" id="title" name="title" required 
                           value="<?= e(post('title', '')) ?>" 
                           placeholder="e.g., Senior Developer Position">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" 
                              placeholder="Brief description of the interview"><?= e(post('description', '')) ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_datetime">Start Date & Time *</label>
                        <input type="datetime-local" id="start_datetime" name="start_datetime" required 
                               value="<?= e(post('start_datetime', '')) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="end_datetime">End Date & Time *</label>
                        <input type="datetime-local" id="end_datetime" name="end_datetime" required 
                               value="<?= e(post('end_datetime', '')) ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="time_per_question">Time per Question (seconds)</label>
                    <input type="number" id="time_per_question" name="time_per_question" 
                           min="30" max="600" value="<?= e(post('time_per_question', '180')) ?>">
                    <small>Default: 180 seconds (3 minutes)</small>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Interview Questions</h3>
                
                <div class="form-group">
                    <label>Question Type</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="question_type" value="default" 
                                   <?= post('question_type', 'default') === 'default' ? 'checked' : '' ?>>
                            Use Default Questions
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="question_type" value="custom" 
                                   <?= post('question_type') === 'custom' ? 'checked' : '' ?>>
                            Custom Questions
                        </label>
                    </div>
                </div>
                
                <div id="default-questions" class="questions-preview">
                    <h4>Default Questions:</h4>
                    <ol>
                        <?php foreach ($defaultQuestions as $q): ?>
                            <li><?= e($q) ?></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
                
                <div id="custom-questions" class="custom-questions-section" style="display: none;">
                    <h4>Enter Your Questions:</h4>
                    <div id="questions-container">
                        <?php for ($i = 1; $i <= 9; $i++): ?>
                            <div class="form-group">
                                <label>Question <?= $i ?></label>
                                <textarea name="custom_questions[]" rows="2" 
                                          placeholder="Enter question <?= $i ?>"></textarea>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <button type="button" id="add-question" class="btn btn-outline btn-sm">+ Add Question</button>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Interview</button>
                <a href="dashboard.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </main>

    <script>
        // Toggle question sections
        document.querySelectorAll('input[name="question_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('default-questions').style.display = 
                    this.value === 'default' ? 'block' : 'none';
                document.getElementById('custom-questions').style.display = 
                    this.value === 'custom' ? 'block' : 'none';
            });
        });
        
        // Add more questions
        let questionCount = 9;
        document.getElementById('add-question').addEventListener('click', function() {
            questionCount++;
            const container = document.getElementById('questions-container');
            const div = document.createElement('div');
            div.className = 'form-group';
            div.innerHTML = `
                <label>Question ${questionCount}</label>
                <textarea name="custom_questions[]" rows="2" placeholder="Enter question ${questionCount}"></textarea>
            `;
            container.appendChild(div);
        });
    </script>
</body>
</html>
