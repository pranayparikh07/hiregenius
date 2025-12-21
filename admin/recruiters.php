<?php
/**
 * HireGenius - Admin Recruiters Management
 */
require_once '../includes/init.php';

requireAuth('admin', 'login.php');

// Handle actions
if (isPost()) {
    if (!verifyCsrfToken(post('csrf_token', ''))) {
        setFlash('error', 'Invalid request.');
        redirect('recruiters.php');
    }
    
    $recruiterId = (int) post('recruiter_id');
    $action = post('action');
    
    if ($recruiterId && in_array($action, ['approve', 'reject', 'suspend', 'activate'])) {
        switch ($action) {
            case 'approve':
                $stmt = db()->prepare("UPDATE recruiters SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
                $stmt->bind_param("ii", $_SESSION['admin_id'], $recruiterId);
                $stmt->execute();
                setFlash('success', 'Recruiter approved successfully.');
                break;
                
            case 'reject':
                $stmt = db()->prepare("UPDATE recruiters SET status = 'rejected' WHERE id = ?");
                $stmt->bind_param("i", $recruiterId);
                $stmt->execute();
                setFlash('success', 'Recruiter rejected.');
                break;
                
            case 'suspend':
                $stmt = db()->prepare("UPDATE recruiters SET status = 'suspended' WHERE id = ?");
                $stmt->bind_param("i", $recruiterId);
                $stmt->execute();
                setFlash('success', 'Recruiter suspended.');
                break;
                
            case 'activate':
                $stmt = db()->prepare("UPDATE recruiters SET status = 'approved' WHERE id = ?");
                $stmt->bind_param("i", $recruiterId);
                $stmt->execute();
                setFlash('success', 'Recruiter activated.');
                break;
        }
    }
    
    redirect('recruiters.php' . (get('status') ? '?status=' . get('status') : ''));
}

// Filter by status
$status = get('status', '');
$where = '';
$params = [];
$types = '';

if ($status && in_array($status, ['pending', 'approved', 'rejected', 'suspended'])) {
    $where = "WHERE status = ?";
    $params[] = $status;
    $types = 's';
}

// Fetch recruiters
$sql = "SELECT r.*, a.name as approved_by_name 
        FROM recruiters r 
        LEFT JOIN admins a ON r.approved_by = a.id 
        $where 
        ORDER BY r.created_at DESC";

if ($types) {
    $stmt = db()->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $recruiters = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $recruiters = db()->query($sql)->fetch_all(MYSQLI_ASSOC);
}

$pageTitle = 'Manage Recruiters - HireGenius';
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
            <a href="dashboard.php">Dashboard</a>
            <a href="recruiters.php" class="active">Recruiters</a>
            <a href="settings.php">Settings</a>
            <a href="logout.php" class="nav-logout">Logout</a>
        </div>
    </nav>

    <main class="dashboard-container">
        <?= displayFlash() ?>
        
        <header class="dashboard-header">
            <h1>Manage Recruiters</h1>
        </header>

        <section class="filter-bar">
            <a href="recruiters.php" class="btn <?= !$status ? 'btn-primary' : 'btn-outline' ?> btn-sm">All</a>
            <a href="recruiters.php?status=pending" class="btn <?= $status === 'pending' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Pending</a>
            <a href="recruiters.php?status=approved" class="btn <?= $status === 'approved' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Approved</a>
            <a href="recruiters.php?status=suspended" class="btn <?= $status === 'suspended' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Suspended</a>
            <a href="recruiters.php?status=rejected" class="btn <?= $status === 'rejected' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Rejected</a>
        </section>

        <section class="dashboard-section">
            <?php if (empty($recruiters)): ?>
                <p class="empty-state">No recruiters found</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recruiters as $recruiter): ?>
                            <tr>
                                <td><?= e($recruiter['name']) ?></td>
                                <td><?= e($recruiter['email']) ?></td>
                                <td><?= e($recruiter['company_name']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $recruiter['status'] ?>">
                                        <?= ucfirst($recruiter['status']) ?>
                                    </span>
                                </td>
                                <td><?= formatDateTime($recruiter['created_at']) ?></td>
                                <td class="actions">
                                    <form method="POST" class="inline-form">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="recruiter_id" value="<?= $recruiter['id'] ?>">
                                        
                                        <?php if ($recruiter['status'] === 'pending'): ?>
                                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                            <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                        <?php elseif ($recruiter['status'] === 'approved'): ?>
                                            <button type="submit" name="action" value="suspend" class="btn btn-warning btn-sm">Suspend</button>
                                        <?php elseif ($recruiter['status'] === 'suspended'): ?>
                                            <button type="submit" name="action" value="activate" class="btn btn-success btn-sm">Activate</button>
                                        <?php endif; ?>
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
