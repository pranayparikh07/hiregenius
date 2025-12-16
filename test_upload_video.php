<?php
require_once '../db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video'])) {
    $uploadDir = '../uploads/videos/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
    }

    $filePath = $uploadDir . basename($_FILES['video']['name']);

    // Try uploading the file
    if (move_uploaded_file($_FILES['video']['tmp_name'], $filePath)) {
        // Log the file path for debugging
        error_log("File uploaded successfully: $filePath");

        // Save the file path in the database
        $stmt = $conn->prepare("INSERT INTO test_video_uploads (video_path) VALUES (?)");
        $stmt->bind_param("s", $filePath);

        if ($stmt->execute()) {
            // Return success response in JSON format
            echo json_encode(['status' => 'success', 'message' => 'Video uploaded and saved successfully!', 'file_path' => $filePath]);
        } else {
            // Database save failed, return error message
            echo json_encode(['status' => 'error', 'message' => 'Failed to save video to database']);
        }
    } else {
        // Upload failed, return error message
        echo json_encode(['status' => 'error', 'message' => 'Failed to upload video to server']);
    }
} else {
    // Invalid request
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
