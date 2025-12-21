<?php
/**
 * HireGenius - List Interviews
 */
require_once '../includes/init.php';

requireAuth('recruiter', 'login.php');

$recruiterId = $_SESSION['recruiter_id'];

// Handle status update
if (isPost() && post('action') === 'update_status') {
    if (verifyCsrfToken(post('csrf_token', ''))) {
        $interviewId = (int) post('interview_id');
        $newStatus = post('new_status');
        
        if (in_array($newStatus, ['active', 'completed', 'cancelled'])) {
            $stmt = db()->prepare("UPDATE interviews SET status = ? WHERE id = ? AND recruiter_id = ?");
            $stmt->bind_param("sii", $newStatus, $interviewId, $recruiterId);
            $stmt->execute();
            setFlash('success', 'Interview status updated.');
        }
    }
    redirect('interviews.php');
}

// Filter
$status = get('status', '');
$search = get('search', '');

$where = "WHERE i.recruiter_id = ?";
$params = [$recruiterId];
$types = "i";

if ($status && in_array($status, ['active', 'completed', 'cancelled', 'draft'])) {
    $where .= " AND i.status = ?";
    $params[] = $status;
    $types .= "s";
}

if ($search) {
    $where .= " AND (i.title LIKE ? OR i.interview_code LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

// Fetch interviews
$sql = "
    SELECT i.*, 
           (SELECT COUNT(*) FROM interview_candidates ic WHERE ic.interview_id = i.id) as candidate_count,
           (SELECT COUNT(*) FROM interview_candidates ic WHERE ic.interview_id = i.id AND ic.status = 'completed') as completed_count,
           (SELECT COUNT(*) FROM questions q WHERE q.interview_id = i.id) as question_count
    FROM interviews i 
    $where 
    ORDER BY i.created_at DESC
";

$stmt = db()->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$interviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Interviews - HireGenius';
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
            <a href="dashboard.php">Dashboard</a>
            <a href="interviews.php" class="active">Interviews</a>
            <a href="logout.php" class="nav-logout">Logout</a>
        </div>
    </nav>

    <main class="dashboard-container">
        <?= displayFlash() ?>
        
        <header class="dashboard-header">
            <h1>My Interviews</h1>
            <a href="create-interview.php" class="btn btn-primary">+ Create Interview</a>
        </header>

        <section class="filter-bar">
            <div class="filter-buttons">
                <a href="interviews.php" class="btn <?= !$status ? 'btn-primary' : 'btn-outline' ?> btn-sm">All</a>
                <a href="interviews.php?status=active" class="btn <?= $status === 'active' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Active</a>
                <a href="interviews.php?status=completed" class="btn <?= $status === 'completed' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Completed</a>
                <a href="interviews.php?status=cancelled" class="btn <?= $status === 'cancelled' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Cancelled</a>
            </div>
            
            <form method="GET" class="search-form">
                <?php if ($status): ?>
                    <input type="hidden" name="status" value="<?= e($status) ?>">
                <?php endif; ?>
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by title or code...">
                <button type="submit" class="btn btn-outline btn-sm">Search</button>
            </form>
        </section>

        <section class="dashboard-section">
            <?php if (empty($interviews)): ?>
                <div class="empty-state">
                    <p>No interviews found.</p>
                    <a href="create-interview.php" class="btn btn-primary">Create Your First Interview</a>
                </div>
            <?php else: ?>
                <div class="interview-cards">
                    <?php foreach ($interviews as $interview): ?>
                        <div class="interview-card">
                            <div class="interview-card-header">
                                <h3><?= e($interview['title']) ?></h3>
                                <span class="status-badge status-<?= $interview['status'] ?>">
                                    <?= ucfirst($interview['status']) ?>
                                </span>
                            </div>
                            
                            <div class="interview-card-body">
                                <div class="interview-code">
                                    <span class="label">Code:</span>
                                    <code><?= e($interview['interview_code']) ?></code>
                                    <button class="copy-btn" onclick="copyCode('<?= e($interview['interview_code']) ?>')" title="Copy code">📋</button>
                                </div>
                                
                                <div class="interview-stats">
                                    <div class="stat">
                                        <span class="stat-value"><?= $interview['question_count'] ?></span>
                                        <span class="stat-label">Questions</span>
                                    </div>
                                    <div class="stat">
                                        <span class="stat-value"><?= $interview['completed_count'] ?>/<?= $interview['candidate_count'] ?></span>
                                        <span class="stat-label">Responses</span>
                                    </div>
                                </div>
                                
                                <div class="interview-dates">
                                    <small>
                                        <?= formatDateTime($interview['start_datetime'], 'M j, Y g:i A') ?> - 
                                        <?= formatDateTime($interview['end_datetime'], 'M j, Y g:i A') ?>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="interview-card-actions">
                                <a href="view-responses.php?id=<?= $interview['id'] ?>" class="btn btn-outline btn-sm">View Responses</a>
                                
                                <?php if ($interview['status'] === 'active'): ?>
                                    <form method="POST" class="inline-form">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="interview_id" value="<?= $interview['id'] ?>">
                                        <input type="hidden" name="new_status" value="completed">
                                        <button type="submit" class="btn btn-success btn-sm">Mark Complete</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script>
        function copyCode(code) {
            navigator.clipboard.writeText(code).then(() => {
                alert('Interview code copied: ' + code);
            });
        }
    </script>
</body>
</html>
