<?php
/**
 * HireGenius - Interview Page (Text-based answers only, no video)
 */
require_once '../includes/init.php';

// Check session
if (!isset($_SESSION['interview_candidate_id']) || !isset($_SESSION['interview_id'])) {
    redirect('join.php');
}

$interviewId = $_SESSION['interview_id'];
$interviewCandidateId = $_SESSION['interview_candidate_id'];
$candidateName = $_SESSION['candidate_name'];
$interviewTitle = $_SESSION['interview_title'];
$timePerQuestion = $_SESSION['time_per_question'] ?? 180;

// Fetch questions
$stmt = db()->prepare("SELECT id, question_text, question_order FROM questions WHERE interview_id = ? ORDER BY question_order");
$stmt->bind_param("i", $interviewId);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($questions)) {
    setFlash('error', 'No questions found for this interview.');
    redirect('join.php');
}

// Update status to started
$stmt = db()->prepare("UPDATE interview_candidates SET status = 'started', started_at = NOW() WHERE id = ? AND status = 'invited'");
$stmt->bind_param("i", $interviewCandidateId);
$stmt->execute();

$pageTitle = $interviewTitle . ' - HireGenius';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="interview-body">
    <nav class="navbar interview-navbar">
        <div class="nav-brand">
            <span>Hire<span class="accent">Genius</span></span>
        </div>
        <div class="nav-info">
            <span class="candidate-info">👤 <?= e($candidateName) ?></span>
        </div>
    </nav>

    <main class="interview-container">
        <header class="interview-header">
            <h1><?= e($interviewTitle) ?></h1>
            <p>Answer each question within the time limit. Your answers are automatically saved.</p>
        </header>

        <div class="interview-progress">
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill" style="width: 0%"></div>
            </div>
            <span class="progress-text">Question <span id="current-question">1</span> of <?= count($questions) ?></span>
        </div>

        <div class="question-card" id="question-card">
            <div class="question-header">
                <span class="question-number" id="question-number">Question 1</span>
                <div class="timer" id="timer">
                    <span class="timer-icon">⏱️</span>
                    <span id="time-display"><?= floor($timePerQuestion / 60) ?>:<?= str_pad($timePerQuestion % 60, 2, '0', STR_PAD_LEFT) ?></span>
                </div>
            </div>
            
            <div class="question-text" id="question-text"></div>
            
            <div class="answer-section">
                <label for="answer">Your Answer:</label>
                <textarea id="answer" name="answer" rows="8" 
                          placeholder="Type your answer here..."></textarea>
                <small class="char-count"><span id="char-count">0</span> characters</small>
            </div>
            
            <div class="question-actions">
                <button type="button" id="next-btn" class="btn btn-primary">
                    Next Question →
                </button>
            </div>
        </div>

        <div class="interview-complete" id="complete-screen" style="display: none;">
            <div class="complete-icon">✅</div>
            <h2>Interview Complete!</h2>
            <p>Thank you for completing the interview. Your responses have been submitted.</p>
            <p>You will be redirected shortly...</p>
        </div>
    </main>

    <script>
        const questions = <?= json_encode($questions) ?>;
        const interviewCandidateId = <?= $interviewCandidateId ?>;
        const timePerQuestion = <?= $timePerQuestion ?>;
        
        let currentIndex = 0;
        let timeLeft = timePerQuestion;
        let timer = null;
        let startTime = null;

        function updateDisplay() {
            const question = questions[currentIndex];
            document.getElementById('question-number').textContent = `Question ${currentIndex + 1}`;
            document.getElementById('current-question').textContent = currentIndex + 1;
            document.getElementById('question-text').textContent = question.question_text;
            document.getElementById('answer').value = '';
            document.getElementById('char-count').textContent = '0';
            
            const progress = ((currentIndex) / questions.length) * 100;
            document.getElementById('progress-fill').style.width = progress + '%';
            
            document.getElementById('next-btn').textContent = 
                currentIndex === questions.length - 1 ? 'Submit Interview' : 'Next Question →';
            
            timeLeft = timePerQuestion;
            startTime = Date.now();
            updateTimer();
            startTimer();
        }

        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('time-display').textContent = 
                `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            const timerEl = document.getElementById('timer');
            timerEl.classList.toggle('warning', timeLeft <= 30);
            timerEl.classList.toggle('danger', timeLeft <= 10);
        }

        function startTimer() {
            clearInterval(timer);
            timer = setInterval(() => {
                timeLeft--;
                updateTimer();
                
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    nextQuestion(true);
                }
            }, 1000);
        }

        function saveAnswer(callback) {
            const answer = document.getElementById('answer').value.trim();
            const question = questions[currentIndex];
            const timeTaken = Math.floor((Date.now() - startTime) / 1000);
            
            fetch('save-answer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    interview_candidate_id: interviewCandidateId,
                    question_id: question.id,
                    answer_text: answer,
                    time_taken: timeTaken
                })
            })
            .then(response => response.json())
            .then(data => {
                if (callback) callback(data);
            })
            .catch(error => {
                console.error('Error saving answer:', error);
                if (callback) callback({status: 'error'});
            });
        }

        function nextQuestion(timedOut = false) {
            clearInterval(timer);
            
            const answer = document.getElementById('answer').value.trim();
            if (!answer && !timedOut) {
                alert('Please provide an answer before proceeding.');
                startTimer();
                return;
            }
            
            saveAnswer((data) => {
                currentIndex++;
                
                if (currentIndex >= questions.length) {
                    completeInterview();
                } else {
                    updateDisplay();
                }
            });
        }

        function completeInterview() {
            document.getElementById('question-card').style.display = 'none';
            document.getElementById('complete-screen').style.display = 'block';
            document.getElementById('progress-fill').style.width = '100%';
            
            fetch('complete-interview.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    interview_candidate_id: interviewCandidateId
                })
            });
            
            setTimeout(() => {
                window.location.href = 'thank-you.php';
            }, 3000);
        }

        document.getElementById('answer').addEventListener('input', function() {
            document.getElementById('char-count').textContent = this.value.length;
        });

        document.getElementById('next-btn').addEventListener('click', () => nextQuestion(false));

        window.addEventListener('beforeunload', (e) => {
            if (currentIndex < questions.length) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        updateDisplay();
    </script>
</body>
</html>
