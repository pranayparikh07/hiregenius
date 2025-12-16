<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['recruiter_id'])) {
    header("Location: login_recruiter.php");
    exit;
}

$recruiter_id = $_SESSION['recruiter_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $interview_name = $_POST['interview_name'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $question_type = $_POST['question_type'];
    $custom_questions = $_POST['custom_questions'] ?? [];
    $interview_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT); // Unique 6-digit code

    // Insert interview details into the database with dual columns
    $stmt = $conn->prepare("INSERT INTO interviews (recruiter_id, name, interview_name, start_time, end_time, code, interview_code) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $recruiter_id, $interview_name, $interview_name, $start_time, $end_time, $interview_code, $interview_code);

    if ($stmt->execute()) {
        $interview_id = $stmt->insert_id;

        // Handle questions based on type
        if ($question_type === 'default') {
            $default_questions = [
                "Tell me about yourself.",
                "Why do you want this job?",
                "What are your strengths and weaknesses?",
                "Where do you see yourself in 5 years?",
                "What do you know about our company?",
                "Describe a challenging situation and how you handled it.",
                "Why should we hire you?",
                "What are your salary expectations?",
                "Do you have any questions for us?"
            ];

            foreach ($default_questions as $question) {
                $stmt_question = $conn->prepare("INSERT INTO questions (interview_id, question_text) VALUES (?, ?)");
                $stmt_question->bind_param("is", $interview_id, $question);
                $stmt_question->execute();
            }
        } elseif ($question_type === 'custom') {
            foreach ($custom_questions as $question) {
                if (!empty(trim($question))) {
                    $stmt_question = $conn->prepare("INSERT INTO questions (interview_id, question_text) VALUES (?, ?)");
                    $stmt_question->bind_param("is", $interview_id, $question);
                    $stmt_question->execute();
                }
            }
        }

        $message = "Interview created successfully! Share the code: $interview_code with the candidate.";
    } else {
        $message = "Error creating interview.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Create Interview</title>
</head>
<body>
    <div class="form-container">
        <h1>Create Interview</h1>
        <?php if (!empty($message)) echo "<p class='success'>$message</p>"; ?>
        <form method="POST">
            <input type="text" name="interview_name" placeholder="Interview Name" required>
            <input type="datetime-local" name="start_time" required class="datetime-input">
            <input type="datetime-local" name="end_time" required class="datetime-input">

            <label>Select Questions:</label>
            <input type="radio" name="question_type" value="default" required> Use Default Questions
            <input type="radio" name="question_type" value="custom" required> Write Custom Questions

            <div id="custom-questions" style="display: none;">
                <textarea name="custom_questions[]" placeholder="Enter Question 1"></textarea>
                <textarea name="custom_questions[]" placeholder="Enter Question 2"></textarea>
                <textarea name="custom_questions[]" placeholder="Enter Question 3"></textarea>
                <textarea name="custom_questions[]" placeholder="Enter Question 4"></textarea>
                <textarea name="custom_questions[]" placeholder="Enter Question 5"></textarea>
                <textarea name="custom_questions[]" placeholder="Enter Question 6"></textarea>
                <textarea name="custom_questions[]" placeholder="Enter Question 7"></textarea>
                <textarea name="custom_questions[]" placeholder="Enter Question 8"></textarea>
                <textarea name="custom_questions[]" placeholder="Enter Question 9"></textarea>
            </div>

            <button type="submit">Create Interview</button>
        </form>
        <a href="dashboard_recruiter.php">Back to Dashboard</a>
    </div>

    <script>
        // Show/Hide custom questions section based on selection
        document.querySelectorAll('input[name="question_type"]').forEach(input => {
            input.addEventListener('change', () => {
                const customQuestionsDiv = document.getElementById('custom-questions');
                customQuestionsDiv.style.display = input.value === 'custom' ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>
