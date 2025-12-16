<?php
session_start();
require_once '../db.php';

// Check if recruiter is logged in
if (!isset($_SESSION['recruiter_id'])) {
    header("Location: ../recruiter_login.php");
    exit;
}

// Fetch recruiter's ID
$recruiter_id = $_SESSION['recruiter_id'];

// Function to fetch interviews created by the recruiter
function getRecruiterInterviews($conn, $recruiter_id) {
    $stmt = $conn->prepare("SELECT * FROM interviews WHERE recruiter_id = ?");
    $stmt->bind_param("i", $recruiter_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to fetch candidates for a specific interview
function getInterviewCandidates($conn, $interview_code) {
    $stmt = $conn->prepare("SELECT DISTINCT candidate_name, candidate_email FROM answers WHERE interview_code = ?");
    $stmt->bind_param("s", $interview_code);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to fetch candidate's responses
function getCandidateResults($conn, $interview_code, $candidate_email) {
    $stmt = $conn->prepare("
        SELECT answers.candidate_name, answers.candidate_email, answers.question_text, answers.answer
        FROM answers
        WHERE answers.interview_code = ? AND answers.candidate_email = ?
    ");
    $stmt->bind_param("ss", $interview_code, $candidate_email);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Function to fetch candidate's video
function getCandidateVideo($conn, $interview_code, $candidate_email) {
    $stmt = $conn->prepare("SELECT video_path FROM video_uploads WHERE interview_code = ? AND candidate_email = ?");
    $stmt->bind_param("ss", $interview_code, $candidate_email);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Fetch data for the current page
$interviews = getRecruiterInterviews($conn, $recruiter_id);
$selected_interview_code = isset($_GET['interview_code']) ? $_GET['interview_code'] : null;
$selected_candidate_email = isset($_GET['candidate_email']) ? $_GET['candidate_email'] : null;
$candidate_results = [];
$video_path = '';

if ($selected_interview_code) {
    $candidates = getInterviewCandidates($conn, $selected_interview_code);
    if ($selected_candidate_email) {
        $candidate_results = getCandidateResults($conn, $selected_interview_code, $selected_candidate_email);
        $video = getCandidateVideo($conn, $selected_interview_code, $selected_candidate_email);
        $video_path = $video ? $video['video_path'] : '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>View Results</title>
</head>
<body>
    <div class="container">
        <h1>View Results</h1>

        <!-- Interview Selection -->
        <div>
            <h2>Your Interviews</h2>
            <form method="GET" action="">
                <label for="interview_code">Select an Interview:</label>
                <select name="interview_code" id="interview_code" onchange="this.form.submit()">
                    <option value="">-- Select an Interview --</option>
                    <?php while ($interview = $interviews->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($interview['interview_code']); ?>" 
                            <?php echo $selected_interview_code == $interview['interview_code'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($interview['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>

        <!-- Candidate Selection -->
        <?php if ($selected_interview_code && isset($candidates) && $candidates->num_rows > 0): ?>
            <h2>Select Candidate</h2>
            <form method="GET" action="">
                <input type="hidden" name="interview_code" value="<?php echo htmlspecialchars($selected_interview_code); ?>">
                <label for="candidate_email">Select a Candidate:</label>
                <select name="candidate_email" id="candidate_email" onchange="this.form.submit()">
                    <option value="">-- Select a Candidate --</option>
                    <?php while ($candidate = $candidates->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($candidate['candidate_email']); ?>" 
                            <?php echo $selected_candidate_email == $candidate['candidate_email'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($candidate['candidate_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        <?php endif; ?>

        <!-- Candidate Results -->
        <?php if ($selected_candidate_email && !empty($candidate_results)): ?>
            <h2>Results for Candidate: <?php echo htmlspecialchars($selected_candidate_email); ?></h2>
            <table border="1" cellpadding="10">
                <thead>
                    <tr>
                        <th>Candidate Name</th>
                        <th>Email</th>
                        <th>Question</th>
                        <th>Answer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($candidate_results as $result): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($result['candidate_name']); ?></td>
                            <td><?php echo htmlspecialchars($result['candidate_email']); ?></td>
                            <td><?php echo htmlspecialchars($result['question_text']); ?></td>
                            <td><?php echo htmlspecialchars($result['answer']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Video -->
            <?php if ($video_path): ?>
                <h3>Interview Video</h3>
                <video width="640" height="480" controls>
                    <source src="../videos/<?php echo htmlspecialchars($video_path); ?>" type="video/webm">
                    Your browser does not support the video tag.
                </video>
            <?php else: ?>
                <p>No video recorded for this candidate.</p>
            <?php endif; ?>
        <?php elseif ($selected_candidate_email): ?>
            <p>No results found for this candidate.</p>
        <?php endif; ?>
    </div>
</body>
</html>
