<?php
/**
 * HireGenius - View Interview Responses
 */
require_once '../includes/init.php';

requireAuth('recruiter', 'login.php');

$recruiterId = $_SESSION['recruiter_id'];
$interviewId = (int) get('id');

if (!$interviewId) {
    setFlash('error', 'Interview not found.');
    redirect('interviews.php');
}

// Fetch interview
$stmt = db()->prepare("SELECT * FROM interviews WHERE id = ? AND recruiter_id = ?");
$stmt->bind_param("ii", $interviewId, $recruiterId);
$stmt->execute();
$interview = $stmt->get_result()->fetch_assoc();

if (!$interview) {
    setFlash('error', 'Interview not found.');
    redirect('interviews.php');
}

// Fetch questions
$stmt = db()->prepare("SELECT * FROM questions WHERE interview_id = ? ORDER BY question_order");
$stmt->bind_param("i", $interviewId);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch candidates with responses
$stmt = db()->prepare("
    SELECT 
        c.id as candidate_id,
        c.name as candidate_name,
        c.email as candidate_email,
        ic.id as interview_candidate_id,
        ic.status,
        ic.started_at,
        ic.completed_at
    FROM interview_candidates ic
    JOIN candidates c ON ic.candidate_id = c.id
    WHERE ic.interview_id = ?
    ORDER BY ic.created_at DESC
");
$stmt->bind_param("i", $interviewId);
$stmt->execute();
$candidates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get selected candidate's answers
$selectedCandidateId = (int) get('candidate');
$candidateAnswers = [];

if ($selectedCandidateId) {
    $stmt = db()->prepare("
        SELECT a.*, q.question_text, q.question_order
        FROM answers a
        JOIN questions q ON a.question_id = q.id
        WHERE a.interview_candidate_id = ?
        ORDER BY q.question_order
    ");
    $stmt->bind_param("i", $selectedCandidateId);
    $stmt->execute();
    $candidateAnswers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$pageTitle = 'View Responses - ' . $interview['title'] . ' - HireGenius';
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
        <?= displayFlash() ?>
        
        <header class="dashboard-header">
            <div>
                <h1><?= e($interview['title']) ?></h1>
                <p class="interview-code">Interview Code: <code><?= e($interview['interview_code']) ?></code></p>
            </div>
            <a href="interviews.php" class="btn btn-outline">&larr; Back to Interviews</a>
        </header>

        <div class="responses-layout">
            <aside class="candidates-sidebar">
                <h3>Candidates (<?= count($candidates) ?>)</h3>
                
                <?php if (empty($candidates)): ?>
                    <p class="empty-state">No candidates yet</p>
                <?php else: ?>
                    <ul class="candidate-list">
                        <?php foreach ($candidates as $candidate): ?>
                            <li class="<?= $selectedCandidateId === (int)$candidate['interview_candidate_id'] ? 'active' : '' ?>">
                                <a href="?id=<?= $interviewId ?>&candidate=<?= $candidate['interview_candidate_id'] ?>">
                                    <span class="candidate-name"><?= e($candidate['candidate_name']) ?></span>
                                    <span class="candidate-email"><?= e($candidate['candidate_email']) ?></span>
                                    <span class="status-badge status-<?= $candidate['status'] ?>">
                                        <?= ucfirst($candidate['status']) ?>
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </aside>

            <section class="responses-content">
                <?php if ($selectedCandidateId && !empty($candidateAnswers)): ?>
                    <h2>Responses</h2>
                    
                    <div class="answers-list">
                        <?php foreach ($candidateAnswers as $answer): ?>
                            <div class="answer-card">
                                <div class="answer-question">
                                    <span class="question-number">Q<?= $answer['question_order'] ?></span>
                                    <?= e($answer['question_text']) ?>
                                </div>
                                <div class="answer-text">
                                    <?= nl2br(e($answer['answer_text'] ?? 'No answer provided')) ?>
                                </div>
                                <?php if ($answer['time_taken']): ?>
                                    <div class="answer-meta">
                                        <small>Time taken: <?= floor($answer['time_taken'] / 60) ?>m <?= $answer['time_taken'] % 60 ?>s</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                <?php elseif ($selectedCandidateId): ?>
                    <div class="empty-state">
                        <p>No responses recorded for this candidate yet.</p>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <h2>Interview Questions (<?= count($questions) ?>)</h2>
                        <ol class="questions-list">
                            <?php foreach ($questions as $question): ?>
                                <li><?= e($question['question_text']) ?></li>
                            <?php endforeach; ?>
                        </ol>
                        <p>Select a candidate from the sidebar to view their responses.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>
