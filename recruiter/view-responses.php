<?php
/**
 * HireGenius - View Interview Responses with Video Playback
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
        ic.completed_at,
        (SELECT COUNT(*) FROM answers WHERE interview_candidate_id = ic.id AND video_path IS NOT NULL) as video_count,
        (SELECT COUNT(*) FROM answers WHERE interview_candidate_id = ic.id) as answer_count
    FROM interview_candidates ic
    JOIN candidates c ON ic.candidate_id = c.id
    WHERE ic.interview_id = ?
    ORDER BY ic.completed_at DESC, ic.created_at DESC
");
$stmt->bind_param("i", $interviewId);
$stmt->execute();
$candidates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get selected candidate's answers
$selectedCandidateId = (int) get('candidate');
$candidateAnswers = [];
$selectedCandidate = null;

if ($selectedCandidateId) {
    // Get candidate info
    foreach ($candidates as $c) {
        if ((int)$c['interview_candidate_id'] === $selectedCandidateId) {
            $selectedCandidate = $c;
            break;
        }
    }
    
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="../public/index.php">Hire<span class="accent">Genius</span></a>
        </div>
        <div class="nav-menu">
            <span class="nav-user"><i class="fas fa-user-tie"></i> <?= e($_SESSION['recruiter_name']) ?></span>
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="interviews.php"><i class="fas fa-clipboard-list"></i> Interviews</a>
            <a href="logout.php" class="nav-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <main class="dashboard-container">
        <?= displayFlash() ?>
        
        <header class="dashboard-header responses-header">
            <div class="header-info">
                <h1><i class="fas fa-play-circle"></i> <?= e($interview['title']) ?></h1>
                <div class="interview-meta">
                    <span class="meta-item">
                        <i class="fas fa-key"></i> Code: <code><?= e($interview['interview_code']) ?></code>
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-users"></i> <?= count($candidates) ?> Candidates
                    </span>
                    <span class="meta-item">
                        <i class="fas fa-question-circle"></i> <?= count($questions) ?> Questions
                    </span>
                </div>
            </div>
            <a href="interviews.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Interviews
            </a>
        </header>

        <div class="responses-layout">
            <aside class="candidates-sidebar">
                <div class="sidebar-header">
                    <h3><i class="fas fa-users"></i> Candidates</h3>
                    <span class="candidate-count"><?= count($candidates) ?></span>
                </div>
                
                <?php if (empty($candidates)): ?>
                    <div class="empty-sidebar">
                        <i class="fas fa-user-plus"></i>
                        <p>No candidates yet</p>
                        <small>Share the interview code to get started</small>
                    </div>
                <?php else: ?>
                    <ul class="candidate-list">
                        <?php foreach ($candidates as $candidate): ?>
                            <li class="candidate-item <?= $selectedCandidateId === (int)$candidate['interview_candidate_id'] ? 'active' : '' ?>">
                                <a href="?id=<?= $interviewId ?>&candidate=<?= $candidate['interview_candidate_id'] ?>">
                                    <div class="candidate-avatar">
                                        <?= strtoupper(substr($candidate['candidate_name'], 0, 1)) ?>
                                    </div>
                                    <div class="candidate-info">
                                        <span class="candidate-name"><?= e($candidate['candidate_name']) ?></span>
                                        <span class="candidate-email"><?= e($candidate['candidate_email']) ?></span>
                                        <div class="candidate-stats">
                                            <?php if ($candidate['video_count'] > 0): ?>
                                                <span class="stat-badge video">
                                                    <i class="fas fa-video"></i> <?= $candidate['video_count'] ?>
                                                </span>
                                            <?php endif; ?>
                                            <span class="stat-badge answers">
                                                <i class="fas fa-comment-dots"></i> <?= $candidate['answer_count'] ?>/<?= count($questions) ?>
                                            </span>
                                        </div>
                                    </div>
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
                <?php if ($selectedCandidateId && $selectedCandidate): ?>
                    <div class="candidate-header">
                        <div class="candidate-profile">
                            <div class="profile-avatar large">
                                <?= strtoupper(substr($selectedCandidate['candidate_name'], 0, 1)) ?>
                            </div>
                            <div class="profile-info">
                                <h2><?= e($selectedCandidate['candidate_name']) ?></h2>
                                <p><i class="fas fa-envelope"></i> <?= e($selectedCandidate['candidate_email']) ?></p>
                                <?php if ($selectedCandidate['completed_at']): ?>
                                    <p><i class="fas fa-check-circle"></i> Completed: <?= formatDateTime($selectedCandidate['completed_at']) ?></p>
                                <?php elseif ($selectedCandidate['started_at']): ?>
                                    <p><i class="fas fa-clock"></i> Started: <?= formatDateTime($selectedCandidate['started_at']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="response-stats">
                            <div class="stat-card">
                                <i class="fas fa-video"></i>
                                <span class="stat-value"><?= $selectedCandidate['video_count'] ?></span>
                                <span class="stat-label">Videos</span>
                            </div>
                            <div class="stat-card">
                                <i class="fas fa-comment-dots"></i>
                                <span class="stat-value"><?= $selectedCandidate['answer_count'] ?></span>
                                <span class="stat-label">Answers</span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($candidateAnswers)): ?>
                        <div class="answers-list">
                            <?php foreach ($candidateAnswers as $index => $answer): ?>
                                <div class="answer-card" id="answer-<?= $answer['question_order'] ?>">
                                    <div class="answer-header">
                                        <div class="question-badge">
                                            <span class="question-number">Q<?= $answer['question_order'] ?></span>
                                        </div>
                                        <div class="answer-meta">
                                            <?php if ($answer['time_taken']): ?>
                                                <span class="meta-item">
                                                    <i class="fas fa-stopwatch"></i>
                                                    <?= floor($answer['time_taken'] / 60) ?>m <?= $answer['time_taken'] % 60 ?>s
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($answer['video_path']): ?>
                                                <span class="meta-item video-badge">
                                                    <i class="fas fa-video"></i> Video Recorded
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="question-text">
                                        <?= e($answer['question_text']) ?>
                                    </div>
                                    
                                    <?php if ($answer['video_path']): ?>
                                        <div class="video-response">
                                            <div class="video-player-container">
                                                <video 
                                                    id="video-<?= $answer['id'] ?>" 
                                                    class="response-video" 
                                                    controls 
                                                    preload="metadata"
                                                    poster=""
                                                >
                                                    <source src="../<?= e($answer['video_path']) ?>" type="video/webm">
                                                    Your browser does not support the video tag.
                                                </video>
                                                <div class="video-controls-overlay">
                                                    <button class="play-btn" onclick="toggleVideo('video-<?= $answer['id'] ?>')">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="video-actions">
                                                <a href="../<?= e($answer['video_path']) ?>" download class="btn btn-sm btn-outline">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                                <button class="btn btn-sm btn-outline" onclick="toggleFullscreen('video-<?= $answer['id'] ?>')">
                                                    <i class="fas fa-expand"></i> Fullscreen
                                                </button>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($answer['answer_text']): ?>
                                        <div class="text-response">
                                            <h4><i class="fas fa-comment-alt"></i> Text Notes:</h4>
                                            <p><?= nl2br(e($answer['answer_text'])) ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!$answer['video_path'] && !$answer['answer_text']): ?>
                                        <div class="no-response">
                                            <i class="fas fa-minus-circle"></i>
                                            <p>No response recorded</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Quick Navigation -->
                        <div class="quick-nav">
                            <h4>Quick Navigation</h4>
                            <div class="nav-buttons">
                                <?php foreach ($candidateAnswers as $answer): ?>
                                    <a href="#answer-<?= $answer['question_order'] ?>" class="nav-btn <?= $answer['video_path'] ? 'has-video' : '' ?>">
                                        Q<?= $answer['question_order'] ?>
                                        <?php if ($answer['video_path']): ?>
                                            <i class="fas fa-video"></i>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-responses">
                            <i class="fas fa-hourglass-half"></i>
                            <h3>Waiting for responses</h3>
                            <p>This candidate hasn't submitted any answers yet.</p>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="no-selection">
                        <div class="no-selection-content">
                            <i class="fas fa-hand-pointer"></i>
                            <h2>Select a Candidate</h2>
                            <p>Choose a candidate from the sidebar to view their video responses.</p>
                            
                            <?php if (!empty($questions)): ?>
                                <div class="questions-preview">
                                    <h3><i class="fas fa-list-ol"></i> Interview Questions (<?= count($questions) ?>)</h3>
                                    <ol class="questions-list">
                                        <?php foreach ($questions as $question): ?>
                                            <li><?= e($question['question_text']) ?></li>
                                        <?php endforeach; ?>
                                    </ol>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <!-- Video Modal for Fullscreen -->
    <div class="video-modal" id="video-modal" onclick="closeVideoModal()">
        <div class="video-modal-content" onclick="event.stopPropagation()">
            <button class="close-modal" onclick="closeVideoModal()">
                <i class="fas fa-times"></i>
            </button>
            <video id="modal-video" controls></video>
        </div>
    </div>

    <script>
        function toggleVideo(videoId) {
            const video = document.getElementById(videoId);
            if (video.paused) {
                video.play();
            } else {
                video.pause();
            }
        }

        function toggleFullscreen(videoId) {
            const video = document.getElementById(videoId);
            const modal = document.getElementById('video-modal');
            const modalVideo = document.getElementById('modal-video');
            
            modalVideo.src = video.querySelector('source').src;
            modal.classList.add('active');
            modalVideo.play();
        }

        function closeVideoModal() {
            const modal = document.getElementById('video-modal');
            const modalVideo = document.getElementById('modal-video');
            
            modalVideo.pause();
            modal.classList.remove('active');
        }

        // Update play button on video state change
        document.querySelectorAll('.response-video').forEach(video => {
            const container = video.closest('.video-player-container');
            const overlay = container.querySelector('.video-controls-overlay');
            
            video.addEventListener('play', () => {
                overlay.style.opacity = '0';
            });
            
            video.addEventListener('pause', () => {
                overlay.style.opacity = '1';
            });
            
            video.addEventListener('ended', () => {
                overlay.style.opacity = '1';
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeVideoModal();
            }
        });

        // Smooth scroll for quick nav
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(btn.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>
