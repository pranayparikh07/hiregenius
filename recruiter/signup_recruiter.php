<?php
session_start();
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $company_name = $_POST['company_name'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM recruiters WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "Error: Email is already registered.";
    } else {
        // Insert recruiter details (not approved yet)
        $stmt = $conn->prepare("INSERT INTO recruiters (email, password, company_name) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $password, $company_name);
        if ($stmt->execute()) {
            echo "Your account has been created! Wait for admin approval.";
        } else {
            echo "Error creating account. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Recruiter Signup</title>
</head>
<body>
    <div class="form-container">
        <h1>Recruiter Signup</h1>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="company_name" placeholder="Company Name" required>
            <button type="submit">Sign Up</button>
        </form>
        <a href="login_recruiter.php">Already have an account? Login here.</a>
    </div>
</body>
</html>
