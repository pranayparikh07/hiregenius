<?php
session_start();
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if recruiter exists and is approved
    $stmt = $conn->prepare("SELECT * FROM recruiters WHERE email = ? AND approved = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $recruiter = $result->fetch_assoc();
        if (password_verify($password, $recruiter['password'])) {
            $_SESSION['recruiter_id'] = $recruiter['id'];
            header("Location: dashboard_recruiter.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Account not found or not approved yet.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Recruiter Login</title>
</head>
<body>
    <div class="form-container">
        <h1>Recruiter Login</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
