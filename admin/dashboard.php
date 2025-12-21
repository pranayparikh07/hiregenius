<?php
/**
 * HireGenius - Admin Dashboard
 */
require_once '../includes/init.php';

requireAuth('admin', 'login.php');

// Get statistics
$stats = [];

// Total recruiters
$result = db()->query("SELECT COUNT(*) as count FROM recruiters");
$stats['total_recruiters'] = $result->fetch_assoc()['count'];

// Pending recruiters
$result = db()->query("SELECT COUNT(*) as count FROM recruiters WHERE status = 'pending'");
$stats['pending_recruiters'] = $result->fetch_assoc()['count'];

// Total interviews
$result = db()->query("SELECT COUNT(*) as count FROM interviews");
$stats['total_interviews'] = $result->fetch_assoc()['count'];

// Total candidates
$result = db()->query("SELECT COUNT(*) as count FROM candidates");
$stats['total_candidates'] = $result->fetch_assoc()['count'];

// Recent pending recruiters
$pendingRecruiters = db()->query("
    SELECT id, name, email, company_name, created_at 
    FROM recruiters 
    WHERE status = 'pending' 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Admin Dashboard - HireGenius';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="../public/index.php">Hire<span class="accent">Genius</span></a>
        </div>
        <div class="nav-menu">
            <span class="nav-user">Welcome, <?= e($_SESSION['admin_name']) ?></span>
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="recruiters.php">Recruiters</a>
            <a href="settings.php">Settings</a>
            <a href="logout.php" class="nav-logout">Logout</a>
        </div>
    </nav>

    <main class="dashboard-container">
        <?= displayFlash() ?>
        
        <header class="dashboard-header">
            <h1>Admin Dashboard</h1>
        </header>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_recruiters'] ?></div>
                <div class="stat-label">Total Recruiters</div>
            </div>
            <div class="stat-card pending">
                <div class="stat-number"><?= $stats['pending_recruiters'] ?></div>
                <div class="stat-label">Pending Approval</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_interviews'] ?></div>
                <div class="stat-label">Total Interviews</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_candidates'] ?></div>
                <div class="stat-label">Total Candidates</div>
            </div>
        </section>

        <section class="dashboard-section">
            <div class="section-header">
                <h2>Pending Recruiter Approvals</h2>
                <a href="recruiters.php" class="btn btn-outline btn-sm">View All</a>
            </div>
            
            <?php if (empty($pendingRecruiters)): ?>
                <p class="empty-state">No pending approvals</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Company</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingRecruiters as $recruiter): ?>
                            <tr>
                                <td><?= e($recruiter['name']) ?></td>
                                <td><?= e($recruiter['email']) ?></td>
                                <td><?= e($recruiter['company_name']) ?></td>
                                <td><?= formatDateTime($recruiter['created_at']) ?></td>
                                <td class="actions">
                                    <form method="POST" action="recruiters.php" class="inline-form">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="recruiter_id" value="<?= $recruiter['id'] ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
