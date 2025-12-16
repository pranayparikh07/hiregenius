<?php
session_start();
require_once '../db.php';

// Ensure the session has an interview code, meaning the candidate has completed the interview
if (!isset($_SESSION['interview_code'])) {
    header("Location: give_interview.php");
    exit;
}

$interview_code = $_SESSION['interview_code'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Thank You</title>
</head>
<body>
    <div class="thank-you-message">
        <h1>Thank You for Completing the Interview!</h1>
        <p>Your responses have been submitted successfully. Please note that completing this interview does not guarantee that you will get the job. The final decision depends entirely on the recruiter’s evaluation.</p>

        <a href="../index.php">
            <button type="button">Back to Dashboard</button>
        </a>
    </div>
</body>
</html>
