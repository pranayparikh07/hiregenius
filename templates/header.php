<?php
/**
 * HireGenius - Header Template
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'HireGenius') ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="<?= baseUrl() ?>">Hire<span class="accent">Genius</span></a>
        </div>
        <div class="nav-menu">
            <?php if (isLoggedIn('recruiter')): ?>
                <a href="<?= baseUrl('recruiter/dashboard.php') ?>">Dashboard</a>
                <a href="<?= baseUrl('recruiter/interviews.php') ?>">Interviews</a>
                <a href="<?= baseUrl('recruiter/logout.php') ?>">Logout</a>
            <?php elseif (isLoggedIn('admin')): ?>
                <a href="<?= baseUrl('admin/dashboard.php') ?>">Dashboard</a>
                <a href="<?= baseUrl('admin/recruiters.php') ?>">Recruiters</a>
                <a href="<?= baseUrl('admin/logout.php') ?>">Logout</a>
            <?php endif; ?>
        </div>
    </nav>
    <main class="main-content">
        <?= displayFlash() ?>
