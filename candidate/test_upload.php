<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video'])) {
    $video = $_FILES['video'];

    // Debug $_FILES to ensure the video is received
    echo '<pre>';
    print_r($video);
    echo '</pre>';

    $uploadDir = '../videos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $uploadDir . basename($video['name']);

    if (move_uploaded_file($video['tmp_name'], $filePath)) {
        echo "File uploaded successfully: $filePath";
    } else {
        echo "Failed to upload video.";
    }
}
?>

<!DOCTYPE html>
<html>
<body>
    <form action="test_upload.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="video">
        <button type="submit">Upload</button>
    </form>
</body>
</html>
