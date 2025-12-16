<?php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $interview_code = $_POST['interview_code'];
    $answer = $_POST['answer'];
    $question_text = $_POST['question'];
    $candidate_name = $_POST['candidate_name'];
    $candidate_email = $_POST['candidate_email'];

    // Validate the inputs
    if (empty($interview_code) || empty($answer) || empty($question_text) || empty($candidate_name) || empty($candidate_email)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    // Prepare the query to save the answer with candidate details
    $stmt = $conn->prepare("
        INSERT INTO answers (interview_code, question_text, answer, candidate_name, candidate_email) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssss", $interview_code, $question_text, $answer, $candidate_name, $candidate_email);

    // Execute the query and return a response
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save answer.']);
    }

    $stmt->close();
    $conn->close();
}
?>
