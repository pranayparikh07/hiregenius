<?php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video'])) {
    $interview_code = $_POST['interview_code'];
    $candidate_email = $_POST['candidate_email'];
    $candidate_name = $_POST['candidate_name'];

    // Define the upload directory
    $uploadDir = '../uploads/videos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
    }

    // Generate a unique file name to avoid conflicts
    $fileName = uniqid('video_') . '.webm';
    $filePath = $uploadDir . $fileName;

    // Move the uploaded file to the uploads/videos folder
    if (move_uploaded_file($_FILES['video']['tmp_name'], $filePath)) {
        // Save the file information to the database
        $stmt = $conn->prepare("INSERT INTO video_uploads (interview_code, candidate_email, candidate_name, video_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $interview_code, $candidate_email, $candidate_name, $filePath);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'file' => $filePath]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save video to database']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to upload video']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
