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

// Active interviews
$result = db()->query("SELECT COUNT(*) as count FROM interviews WHERE status = 'active'");
$stats['active_interviews'] = $result->fetch_assoc()['count'];

// Recent pending recruiters
$pendingRecruiters = db()->query("
    SELECT id, name, email, company_name, created_at 
    FROM recruiters 
    WHERE status = 'pending' 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Recent interviews
$recentInterviews = db()->query("
    SELECT i.id, i.title, i.status, i.created_at, r.company_name,
           (SELECT COUNT(*) FROM interview_candidates ic WHERE ic.interview_id = i.id) as candidate_count
    FROM interviews i
    JOIN recruiters r ON i.recruiter_id = r.id
    ORDER BY i.created_at DESC
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="../public/index.php">
                <i class="fas fa-bolt"></i>
                Hire<span class="accent">Genius</span>
            </a>
        </div>
        <div class="nav-menu">
            <span class="nav-user"><i class="fas fa-shield-alt"></i> <?= e($_SESSION['admin_name']) ?></span>
            <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="recruiters.php"><i class="fas fa-users"></i> Recruiters</a>
            <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
            <a href="logout.php" class="nav-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <main class="dashboard-container">
        <?= displayFlash() ?>
        
        <header class="dashboard-header">
            <div>
                <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
                <p class="text-muted">System overview and management</p>
            </div>
            <div class="header-actions">
                <a href="recruiters.php?filter=pending" class="btn btn-warning btn-sm">
                    <i class="fas fa-clock"></i> Pending (<?= $stats['pending_recruiters'] ?>)
                </a>
            </div>
        </header>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['total_recruiters'] ?></div>
                    <div class="stat-label">Total Recruiters</div>
                </div>
            </div>
            <div class="stat-card pending">
                <div class="stat-icon warning">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['pending_recruiters'] ?></div>
                    <div class="stat-label">Pending Approval</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-video"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['total_interviews'] ?></div>
                    <div class="stat-label">Total Interviews</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $stats['total_candidates'] ?></div>
                    <div class="stat-label">Total Candidates</div>
                </div>
            </div>
        </section>

        <div class="dashboard-grid">
            <section class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-user-clock"></i> Pending Recruiter Approvals</h2>
                    <a href="recruiters.php" class="btn btn-outline btn-sm">
                        <i class="fas fa-eye"></i> View All
                    </a>
                </div>
                
                <?php if (empty($pendingRecruiters)): ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <p>No pending approvals</p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-user"></i> Name</th>
                                <th><i class="fas fa-envelope"></i> Email</th>
                                <th><i class="fas fa-building"></i> Company</th>
                                <th><i class="fas fa-calendar"></i> Registered</th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
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
                                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

            <section class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-video"></i> Recent Interviews</h2>
                </div>
                
                <?php if (empty($recentInterviews)): ?>
                    <div class="empty-state">
                        <i class="fas fa-video-slash"></i>
                        <p>No interviews yet</p>
                    </div>
                <?php else: ?>
                    <div class="interview-list">
                        <?php foreach ($recentInterviews as $interview): ?>
                            <div class="interview-item">
                                <div class="interview-info">
                                    <h4><?= e($interview['title']) ?></h4>
                                    <p><i class="fas fa-building"></i> <?= e($interview['company_name']) ?></p>
                                </div>
                                <div class="interview-meta">
                                    <span class="status-badge status-<?= $interview['status'] ?>">
                                        <?= ucfirst($interview['status']) ?>
                                    </span>
                                    <span class="candidate-count">
                                        <i class="fas fa-users"></i> <?= $interview['candidate_count'] ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>
