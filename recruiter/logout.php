<?php
/**
 * HireGenius - Recruiter Logout
 */
require_once '../includes/init.php';

unset($_SESSION['recruiter_id']);
unset($_SESSION['recruiter_name']);
session_destroy();

redirect('login.php');
