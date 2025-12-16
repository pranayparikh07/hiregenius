<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['recruiter_id'])) {
    header("Location: login_recruiter.php");
    exit();
}

$recruiter_id = $_SESSION['recruiter_id'];

// Fetch ongoing and past interviews
$stmt = $conn->prepare("
    SELECT id, name, start_time, end_time, interview_code, code FROM interviews
    WHERE recruiter_id = ?
");
$stmt->bind_param("i", $recruiter_id);
$stmt->execute();
$result = $stmt->get_result();
$interviews = $result->fetch_all(MYSQLI_ASSOC);
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
    <div class="dashboard-container">
        <h1>Recruiter Dashboard</h1>
       
        <button onclick="location.href='create_interview.php'">Create New Interview</button>
        <button onclick="location.href='view_results.php'">Results</button>

        <h2>Past and Ongoing Interviews</h2>
        <table border=2px>
            <thead>
                <tr>
                    <th>Interview Name</th>
                    <th>Interview Code</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php foreach ($interviews as $interview): ?>
                    <tr>
                        <td><?= htmlspecialchars($interview['name']) ?></td>
                        <td><?= htmlspecialchars($interview['interview_code']) ?></td>
                        <td><?= htmlspecialchars($interview['start_time']) ?></td>
                        <td><?= htmlspecialchars($interview['end_time']) ?></td>
                        
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
