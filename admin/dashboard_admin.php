<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recruiter_email = $_POST['recruiter_email'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE recruiters SET status = 'approved' WHERE email = ?");
        $stmt->bind_param("s", $recruiter_email);
        if ($stmt->execute()) {
            $message = "Recruiter approved successfully!";
        } else {
            $message = "Error approving recruiter.";
        }
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("DELETE FROM recruiters WHERE email = ?");
        $stmt->bind_param("s", $recruiter_email);
        if ($stmt->execute()) {
            $message = "Recruiter rejected successfully!";
        } else {
            $message = "Error rejecting recruiter.";
        }
    }
}

$recruiters = $conn->query("SELECT * FROM recruiters WHERE status = 'pending'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Admin Dashboard</title>
</head>
<body>
    <div class="dashboard-container">
        <h1>Admin Dashboard</h1>
        <?php if (!empty($message)) echo "<p class='success'>$message</p>"; ?>
        
        <h2>Pending Recruiters</h2>
        <table>
            <tr>
                <th>Email</th>
                <th>Action</th>
            </tr>
            <?php while ($recruiter = $recruiters->fetch_assoc()): ?>
                <tr>
                    <td><?= $recruiter['email'] ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="recruiter_email" value="<?= $recruiter['email'] ?>">
                            <button type="submit" name="action" value="approve">Approve</button>
                            <button type="submit" name="action" value="reject">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
