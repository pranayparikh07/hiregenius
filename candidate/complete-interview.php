<?php
/**
 * HireGenius - Complete Interview API
 */
require_once '../includes/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$interviewCandidateId = (int) ($input['interview_candidate_id'] ?? 0);

if (!$interviewCandidateId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing interview candidate ID']);
    exit;
}

// Update interview candidate status to completed
$stmt = db()->prepare("UPDATE interview_candidates SET status = 'completed', completed_at = NOW() WHERE id = ?");
$stmt->bind_param("i", $interviewCandidateId);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to complete interview']);
}
