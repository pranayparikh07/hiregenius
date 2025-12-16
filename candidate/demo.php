<?php
session_start();
require_once '../db.php';

// Check session variables
if (!isset($_SESSION['interview_code'], $_SESSION['candidate_email'], $_SESSION['candidate_name'])) {
    header("Location: interview.php");
    exit;
}

$interview_code = $_SESSION['interview_code'];

// Fetch interview details
$stmt = $conn->prepare("SELECT * FROM interviews WHERE interview_code = ?");
$stmt->bind_param("s", $interview_code);
$stmt->execute();
$interview = $stmt->get_result()->fetch_assoc();

if (!$interview) {
    echo "Invalid interview code.";
    exit;
}

// Fetch associated questions
$question_stmt = $conn->prepare("SELECT * FROM questions WHERE interview_id = ?");
$question_stmt->bind_param("i", $interview['id']);
$question_stmt->execute();
$questions = $question_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Interview</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    
    <div id="camera-container" class="camera-container">
        <!-- Camera Live Feed -->
        <video id="live-feed" autoplay muted width="500" height="500"></video>
    </div>

    <div id="question-container" style="margin-left: 50px;">
        <h2>Question <span id="question-number"></span>:</h2>
        <p id="question-text"></p>
        <textarea id="answer" placeholder="Type your answer here..." rows="4" cols="50"></textarea>
        <button id="next-button" style="margin-left: 180px;">Next Question</button>
    </div>

    <script>
        const questions = <?php echo json_encode($questions); ?>;
        const interviewCode = '<?php echo $interview_code; ?>';
        const candidateEmail = '<?php echo $_SESSION['candidate_email']; ?>';
        const candidateName = '<?php echo $_SESSION['candidate_name']; ?>';

        let currentQuestionIndex = 0;

        function displayQuestion() {
            if (currentQuestionIndex >= questions.length) {
                document.getElementById('question-container').innerHTML = `<p>Interview complete. Thank you!</p>`;
                return;
            }

            document.getElementById('question-number').textContent = currentQuestionIndex + 1;
            document.getElementById('question-text').textContent = questions[currentQuestionIndex]['question_text'];
            document.getElementById('answer').value = '';
        }

        function nextQuestion() {
            const answer = document.getElementById('answer').value;
            if (!answer.trim()) {
                alert("Please provide an answer.");
                return;
            }

            saveAnswer(answer);
            currentQuestionIndex++;
            displayQuestion();
        }

        function saveAnswer(answer) {
            fetch('save_demo_answer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `interview_code=${interviewCode}&question=${encodeURIComponent(questions[currentQuestionIndex]['question_text'])}&answer=${encodeURIComponent(answer)}&candidate_name=${encodeURIComponent(candidateName)}&candidate_email=${encodeURIComponent(candidateEmail)}`
            }).then(response => response.text())
              .then(data => console.log("Answer saved:", data))
              .catch(error => console.error("Error saving answer:", error));
        }

        function startCamera() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    // Display the live camera feed
                    document.getElementById('live-feed').srcObject = stream;
                })
                .catch(error => {
                    console.error("Camera access error:", error);
                    alert("Please allow access to the camera.");
                });
        }

        document.getElementById('next-button').addEventListener('click', nextQuestion);

        displayQuestion();
        startCamera();
    </script>
</body>
</html>
