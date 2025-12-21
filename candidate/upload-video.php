<?php
/**
 * HireGenius - Video Upload Handler
 * Handles video file uploads from interview recordings
 */
require_once '../includes/init.php';

header('Content-Type: application/json');

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Validate session
if (!isset($_SESSION['interview_candidate_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired']);
    exit;
}

// Get POST data
$interviewCandidateId = $_POST['interview_candidate_id'] ?? null;
$questionId = $_POST['question_id'] ?? null;
$answerText = $_POST['answer_text'] ?? '';
$timeTaken = $_POST['time_taken'] ?? 0;

// Validate required fields
if (!$interviewCandidateId || !$questionId) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// Validate session matches
if ($interviewCandidateId != $_SESSION['interview_candidate_id']) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid session']);
    exit;
}

// Check if video file was uploaded
if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
    ];
    
    $errorCode = $_FILES['video']['error'] ?? UPLOAD_ERR_NO_FILE;
    $errorMessage = $errorMessages[$errorCode] ?? 'Unknown upload error';
    
    echo json_encode(['status' => 'error', 'message' => $errorMessage]);
    exit;
}

// Create upload directory if it doesn't exist
$uploadDir = '../uploads/videos/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$timestamp = time();
$randomStr = bin2hex(random_bytes(8));
$filename = "video_{$interviewCandidateId}_{$questionId}_{$timestamp}_{$randomStr}.webm";
$filepath = $uploadDir . $filename;

// Move uploaded file
if (!move_uploaded_file($_FILES['video']['tmp_name'], $filepath)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save video file']);
    exit;
}

// Get video file size
$fileSize = filesize($filepath);
$videoPath = 'uploads/videos/' . $filename;

// Check if answer already exists for this question
$stmt = db()->prepare("SELECT id FROM answers WHERE interview_candidate_id = ? AND question_id = ?");
$stmt->bind_param("ii", $interviewCandidateId, $questionId);
$stmt->execute();
$existingAnswer = $stmt->get_result()->fetch_assoc();

if ($existingAnswer) {
    // Update existing answer
    $stmt = db()->prepare("UPDATE answers SET answer_text = ?, video_path = ?, time_taken = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssii", $answerText, $videoPath, $timeTaken, $existingAnswer['id']);
} else {
    // Insert new answer
    $stmt = db()->prepare("INSERT INTO answers (interview_candidate_id, question_id, answer_text, video_path, time_taken, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iissi", $interviewCandidateId, $questionId, $answerText, $videoPath, $timeTaken);
}

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Video uploaded successfully',
        'data' => [
            'filename' => $filename,
            'size' => $fileSize,
            'path' => $videoPath
        ]
    ]);
} else {
    // Delete uploaded file if database insert fails
    unlink($filepath);
    echo json_encode(['status' => 'error', 'message' => 'Failed to save answer to database']);
}
