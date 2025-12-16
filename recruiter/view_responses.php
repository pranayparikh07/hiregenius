<?php
session_start();
require_once '../db.php';

// Ensure that interview_code and candidate_email are set
if (isset($_POST['interview_code']) && isset($_POST['candidate_email'])) {
    $candidate_email = $_POST['candidate_email'];
    $interview_code = $_POST['interview_code'];

    // Fetch the candidate's responses to the interview questions
    $responses_query = "
        SELECT question_text, answer
        FROM answers
        WHERE candidate_email = ? AND interview_code = ?
    ";
    $stmt = $conn->prepare($responses_query);
    $stmt->bind_param("ss", $candidate_email, $interview_code);
    $stmt->execute();
    $responses_result = $stmt->get_result();
} else {
    echo "No interview or candidate selected.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Candidate Responses</title>
</head>
<body>
    <h1>Candidate Responses</h1>

    <table border="1">
        <thead>
            <tr>
                <th>Question</th>
                <th>Answer</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($response = $responses_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($response['question_text']); ?></td>
                    <td><?= htmlspecialchars($response['answer']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
