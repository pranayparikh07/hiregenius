<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['recruiter_id'])) {
    header("Location: login_recruiter.php");
    exit;
}

$recruiter_id = $_SESSION['recruiter_id'];
$interviews = [];

$stmt = $conn->prepare("SELECT * FROM interviews WHERE recruiter_id = ?");
$stmt->bind_param("i", $recruiter_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $interviews[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Recruiter Dashboard</title>
</head>
<body>
    <h1>Dashboard</h1>

    <div id="interviews">
        <?php foreach ($interviews as $interview): ?>
            <h2>Interview: <?php echo htmlspecialchars($interview['name']); ?></h2>
            <div class="video-section">
                <?php
                    $stmt = $conn->prepare("SELECT * FROM interview_videos WHERE interview_code = ?");
                    $stmt->bind_param("s", $interview['interview_code']);
                    $stmt->execute();
                    $videos = $stmt->get_result();
                    
                    while ($video = $videos->fetch_assoc()) {
                        echo '<video width="320" height="240" controls><source src="'.$video['video_path'].'" type="video/webm"></video>';
                    }
                ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
