<?php
/**
 * HireGenius - Admin Logout
 */
require_once '../includes/init.php';

unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
session_destroy();

redirect('login.php');
