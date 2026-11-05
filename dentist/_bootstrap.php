<?php
require_once __DIR__ . '/../config/security.php';
start_secure_session();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'dentist') {
    header('Location: ../auth/login.php');
    exit;
}

$dentist_id = (int) $_SESSION['user_id'];