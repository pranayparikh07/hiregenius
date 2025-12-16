<?php
session_start();
require_once '../db.php';

$interview_name = "";
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $interview_code = $_POST['interview_code'];
    $candidate_email = $_POST['candidate_email'];
    $candidate_name = $_POST['candidate_name'];

    // Fetch interview name based on the provided code (interview_code)
    $stmt = $conn->prepare("SELECT name FROM interviews WHERE interview_code = ?");
    $stmt->bind_param("s", $interview_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $interview = $result->fetch_assoc();

    if ($interview) {
        $interview_name = $interview['name'];

        // Check if the candidate already exists
        $check_stmt = $conn->prepare("SELECT * FROM candidates WHERE email = ?");
        $check_stmt->bind_param("s", $candidate_email);
        $check_stmt->execute();
        $existing_candidate = $check_stmt->get_result()->fetch_assoc();

        if (!$existing_candidate) {
            // Insert candidate details into the candidates table if not exists
            $insert_stmt = $conn->prepare("INSERT INTO candidates (email, name) VALUES (?, ?)");
            $insert_stmt->bind_param("ss", $candidate_email, $candidate_name);
            $insert_stmt->execute();
        }

        // Insert the candidate's name and email into the answers table along with the interview code
        $insert_answer_stmt = $conn->prepare("INSERT INTO answers (interview_code, candidate_name, candidate_email) VALUES (?, ?, ?)");
        $insert_answer_stmt->bind_param("sss", $interview_code, $candidate_name, $candidate_email);
        $insert_answer_stmt->execute();

        // Record the candidate's interview details in the session
        $_SESSION['interview_code'] = $interview_code;
        $_SESSION['candidate_email'] = $candidate_email;
        $_SESSION['candidate_name'] = $candidate_name;

        // Redirect to the interview page
        header("Location: start_interview.php");
        exit;
    } else {
        $message = "Invalid interview code. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Give Interview</title>
</head>
<body>
    <h1>Give Interview</h1>
    <?php if (!empty($message)) echo "<p class='error'>$message</p>"; ?>
    <form method="POST">
        <input type="text" name="interview_code" placeholder="Enter Interview Code" required 
               value="<?php echo isset($_POST['interview_code']) ? $_POST['interview_code'] : ''; ?>"><br>
        <input type="email" name="candidate_email" placeholder="Enter Your Email" required><br>
        <input type="text" name="candidate_name" placeholder="Enter Your Name" required><br>
        
        <!-- Display the interview name if fetched -->
        <?php if (!empty($interview_name)) : ?>
            <p><strong>Interview Name:</strong> <?php echo htmlspecialchars($interview_name); ?></p>
        <?php endif; ?>
        
        <button type="submit">Start Interview</button>
    </form>
</body>
</html>
