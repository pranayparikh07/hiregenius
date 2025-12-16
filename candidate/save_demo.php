<?php
session_start();
require_once '../db.php';

// Check if candidate session exists, redirect if not
if (!isset($_SESSION['candidate_email'])) {
    header("Location: interview.php");
    exit;
}

// Fetch associated questions directly (skipping interview validation)
$questions = [
    ["question_text" => "Tell us about yourself."],
    ["question_text" => "Why do you want this job?"],
    ["question_text" => "What are your strengths and weaknesses?"],
    ["question_text" =>"Where do you see yourself in 5 years?"],
    ["question_text" =>"What do you know about our company?"],
    ["question_text" =>"Describe a challenging situation and how you handled it."],
["question_text" =>"Why should we hire you?"],
["question_text" =>"What are your salary expectations?"],
["question_text" =>"Do you have any questions for us?"]
]; // Example static questions, replace with dynamic query if needed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Interview</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Larger video size styling */
        .camera-container {
            text-align: center;
            margin: 20px auto;
        }
        video {
            border: 2px solid #000;
            border-radius: 10px;
            width: 640px;
            height: 480px;
        }
        #question-container {
            text-align: center;
            margin-top: 20px;
        }
        textarea {
            width: 80%;
            font-size: 16px;
            padding: 10px;
            margin-top: 10px;
        }
        button {
            margin-top: 10px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    

    <!-- Camera Live Feed -->
    <div id="camera-container" class="camera-container">
        <video id="live-feed" autoplay muted></video>
    </div>

    <!-- Question and Answer Section -->
    <div id="question-container">
        <h2>Question <span id="question-number"></span>:</h2>
        <p id="question-text"></p>
        <textarea id="answer" placeholder="Type your answer here..." rows="4"></textarea>
        <button id="next-button">Next Question</button>
    </div>

    <script>
        const questions = <?php echo json_encode($questions); ?>;
        const candidateEmail = '<?php echo $_SESSION['candidate_email']; ?>';

        let currentQuestionIndex = 0;

        // Display the current question
        function displayQuestion() {
            if (currentQuestionIndex >= questions.length) {
                document.getElementById('question-container').innerHTML = `<p>Interview complete. Thank you!</p>`;
                return;
            }

            document.getElementById('question-number').textContent = currentQuestionIndex + 1;
            document.getElementById('question-text').textContent = questions[currentQuestionIndex]['question_text'];
            document.getElementById('answer').value = '';
        }

        // Move to the next question
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

        // Save answer to the database
        function saveAnswer(answer) {
            fetch('save_demo_answer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `question=${encodeURIComponent(questions[currentQuestionIndex]['question_text'])}&answer=${encodeURIComponent(answer)}&candidate_email=${encodeURIComponent(candidateEmail)}`
            }).then(response => response.text())
              .then(data => console.log("Answer saved:", data))
              .catch(error => console.error("Error saving answer:", error));
        }

        // Start the camera
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
