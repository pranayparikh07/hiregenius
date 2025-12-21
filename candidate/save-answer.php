<?php
/**
 * HireGenius - Save Answer API
 */
require_once '../includes/init.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$interviewCandidateId = (int) ($input['interview_candidate_id'] ?? 0);
$questionId = (int) ($input['question_id'] ?? 0);
$answerText = trim($input['answer_text'] ?? '');
$timeTaken = (int) ($input['time_taken'] ?? 0);

if (!$interviewCandidateId || !$questionId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// Check if answer already exists (update) or insert new
$stmt = db()->prepare("SELECT id FROM answers WHERE interview_candidate_id = ? AND question_id = ?");
$stmt->bind_param("ii", $interviewCandidateId, $questionId);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if ($existing) {
    // Update existing answer
    $stmt = db()->prepare("UPDATE answers SET answer_text = ?, time_taken = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("sii", $answerText, $timeTaken, $existing['id']);
} else {
    // Insert new answer
    $stmt = db()->prepare("INSERT INTO answers (interview_candidate_id, question_id, answer_text, time_taken) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $interviewCandidateId, $questionId, $answerText, $timeTaken);
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save answer']);
}
