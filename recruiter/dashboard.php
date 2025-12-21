<?php
/**
 * HireGenius - Recruiter Dashboard
 */
require_once '../includes/init.php';

requireAuth('recruiter', 'login.php');

$recruiterId = $_SESSION['recruiter_id'];

// Get statistics
$stats = [];

// Active interviews
$stmt = db()->prepare("SELECT COUNT(*) as count FROM interviews WHERE recruiter_id = ? AND status = 'active'");
$stmt->bind_param("i", $recruiterId);
$stmt->execute();
$stats['active_interviews'] = $stmt->get_result()->fetch_assoc()['count'];

// Total interviews
$stmt = db()->prepare("SELECT COUNT(*) as count FROM interviews WHERE recruiter_id = ?");
$stmt->bind_param("i", $recruiterId);
$stmt->execute();
$stats['total_interviews'] = $stmt->get_result()->fetch_assoc()['count'];

// Total candidates
$stmt = db()->prepare("
    SELECT COUNT(DISTINCT ic.candidate_id) as count 
    FROM interview_candidates ic 
    JOIN interviews i ON ic.interview_id = i.id 
    WHERE i.recruiter_id = ?
");
$stmt->bind_param("i", $recruiterId);
$stmt->execute();
$stats['total_candidates'] = $stmt->get_result()->fetch_assoc()['count'];

// Completed interviews
$stmt = db()->prepare("
    SELECT COUNT(*) as count 
    FROM interview_candidates ic 
    JOIN interviews i ON ic.interview_id = i.id 
    WHERE i.recruiter_id = ? AND ic.status = 'completed'
");
$stmt->bind_param("i", $recruiterId);
$stmt->execute();
$stats['completed_responses'] = $stmt->get_result()->fetch_assoc()['count'];

// Recent interviews
$stmt = db()->prepare("
    SELECT i.*, 
           (SELECT COUNT(*) FROM interview_candidates ic WHERE ic.interview_id = i.id) as candidate_count,
           (SELECT COUNT(*) FROM interview_candidates ic WHERE ic.interview_id = i.id AND ic.status = 'completed') as completed_count
    FROM interviews i 
    WHERE i.recruiter_id = ? 
    ORDER BY i.created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $recruiterId);
$stmt->execute();
$recentInterviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Dashboard - HireGenius';
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
            <span class="nav-user">Welcome, <?= e($_SESSION['recruiter_name']) ?></span>
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="interviews.php">Interviews</a>
            <a href="logout.php" class="nav-logout">Logout</a>
        </div>
    </nav>

    <main class="dashboard-container">
        <?= displayFlash() ?>
        
        <header class="dashboard-header">
            <h1>Recruiter Dashboard</h1>
            <a href="create-interview.php" class="btn btn-primary">+ Create Interview</a>
        </header>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['active_interviews'] ?></div>
                <div class="stat-label">Active Interviews</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_interviews'] ?></div>
                <div class="stat-label">Total Interviews</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_candidates'] ?></div>
                <div class="stat-label">Total Candidates</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['completed_responses'] ?></div>
                <div class="stat-label">Completed Responses</div>
            </div>
        </section>

        <section class="dashboard-section">
            <div class="section-header">
                <h2>Recent Interviews</h2>
                <a href="interviews.php" class="btn btn-outline btn-sm">View All</a>
            </div>
            
            <?php if (empty($recentInterviews)): ?>
                <div class="empty-state">
                    <p>No interviews created yet.</p>
                    <a href="create-interview.php" class="btn btn-primary">Create Your First Interview</a>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Code</th>
                            <th>Status</th>
                            <th>Candidates</th>
                            <th>Date Range</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentInterviews as $interview): ?>
                            <tr>
                                <td><?= e($interview['title']) ?></td>
                                <td><code><?= e($interview['interview_code']) ?></code></td>
                                <td>
                                    <span class="status-badge status-<?= $interview['status'] ?>">
                                        <?= ucfirst($interview['status']) ?>
                                    </span>
                                </td>
                                <td><?= $interview['completed_count'] ?> / <?= $interview['candidate_count'] ?></td>
                                <td>
                                    <?= formatDateTime($interview['start_datetime'], 'M j') ?> - 
                                    <?= formatDateTime($interview['end_datetime'], 'M j, Y') ?>
                                </td>
                                <td class="actions">
                                    <a href="view-responses.php?id=<?= $interview['id'] ?>" class="btn btn-outline btn-sm">View</a>
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
