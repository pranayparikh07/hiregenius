<?php
session_start();
require_once '../db.php';

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
    <title>Start Interview</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h1>Interview: <?php echo htmlspecialchars($interview['name']); ?></h1>
    <p>Candidate: <?php echo htmlspecialchars($_SESSION['candidate_name']); ?> (<?php echo htmlspecialchars($_SESSION['candidate_email']); ?>)</p>

    <div class="container">
        <!-- Interview Video (side-by-side with Camera) -->
        <div id="video-container" class="video-container">
            <video id="video-preview" autoplay muted width="320" height="240"></video>
        </div>

        <!-- Camera Recording (Live Feed) -->
        <div id="camera-container" class="camera-container">
            <video id="live-feed" autoplay muted width="320" height="240"></video>
        </div>
    </div>

    <div id="question-container">
        <h2>Question <span id="question-number"></span>:</h2>
        <p id="question-text"></p>
        <textarea id="answer" placeholder="Type your answer here..." rows="4" cols="50"></textarea>
        <p id="timer">Time Left: <span id="time-left"></span> seconds</p>
        <button id="next-button">Next Question</button>
    </div>

    <p id="redirect-timer" style="display: none; font-weight: bold; color: red;"></p>

    <script>
        const questions = <?php echo json_encode($questions); ?>;
        const interviewCode = '<?php echo $interview_code; ?>';
        const candidateEmail = '<?php echo $_SESSION['candidate_email']; ?>';
        const candidateName = '<?php echo $_SESSION['candidate_name']; ?>';

        let currentQuestionIndex = 0;
        let recordedChunks = [];
        let mediaRecorder;
        let timer;
        let timeLeft = 180; // 3 minutes per question

        function displayQuestion() {
            if (currentQuestionIndex >= questions.length) {
                stopRecording();
                document.getElementById('question-container').style.display = 'none';
                document.getElementById('video-container').style.display = 'none';
                document.getElementById('camera-container').style.display = 'none';
                document.getElementById('redirect-timer').style.display = 'block';
                return;
            }

            document.getElementById('question-number').textContent = currentQuestionIndex + 1;
            document.getElementById('question-text').textContent = questions[currentQuestionIndex]['question_text'];
            document.getElementById('answer').value = '';
            timeLeft = 180;
            startTimer();
        }

        function startTimer() {
            clearInterval(timer);
            timer = setInterval(() => {
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    nextQuestion();
                } else {
                    document.getElementById('time-left').textContent = timeLeft;
                    timeLeft--;
                }
            }, 1000);
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
            fetch('save_answer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `interview_code=${interviewCode}&question=${encodeURIComponent(questions[currentQuestionIndex]['question_text'])}&answer=${encodeURIComponent(answer)}&candidate_name=${encodeURIComponent(candidateName)}&candidate_email=${encodeURIComponent(candidateEmail)}`
            }).then(response => response.text())
              .then(data => console.log("Answer saved:", data))
              .catch(error => console.error("Error saving answer:", error));
        }

        function startRecording() {
            navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                .then(stream => {
                    // Display the live camera feed
                    document.getElementById('live-feed').srcObject = stream;

                    mediaRecorder = new MediaRecorder(stream);
                    mediaRecorder.ondataavailable = event => recordedChunks.push(event.data);
                    mediaRecorder.start();
                })
                .catch(error => {
                    console.error("Camera access error:", error);
                    alert("Please allow access to camera and microphone.");
                });
        }

        function stopRecording() {
            mediaRecorder.stop();
            mediaRecorder.onstop = () => {
                const blob = new Blob(recordedChunks, { type: 'video/webm' });
                uploadVideo(blob);

                // Notify the candidate and delay redirection
                let timeLeft = 3;
                const timerElement = document.getElementById('redirect-timer');
                timerElement.textContent = `Redirecting in ${timeLeft} seconds...`;
                const countdown = setInterval(() => {
                    timeLeft--;
                    timerElement.textContent = `Redirecting in ${timeLeft} seconds...`;
                    if (timeLeft <= 0) {
                        clearInterval(countdown);
                        window.location.href = 'thank_you.php';
                    }
                }, 3000); // Countdown every second
            };
        }

        function uploadVideo(blob) {
            const formData = new FormData();
            formData.append('video', blob, `interview_video.webm`);
            formData.append('interview_code', interviewCode);
            formData.append('candidate_email', candidateEmail);
            formData.append('candidate_name', candidateName);

            fetch('upload_video.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => console.log("Video upload response:", data))
            .catch(error => console.error("Error uploading video:", error));
        }

        document.getElementById('next-button').addEventListener('click', nextQuestion);

        displayQuestion();
        startRecording();
    </script>
</body>
</html>
