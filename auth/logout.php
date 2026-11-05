<?php
// auth/logout.php
require_once __DIR__ . '/../config/security.php';
start_secure_session();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !valid_csrf_token($_POST['csrf_token'] ?? null)) {
	http_response_code(405);
	exit('Invalid logout request.');
}

if (isset($_SESSION['user_id'])) {
	audit_log($pdo, (int) $_SESSION['user_id'], $_SESSION['name'] ?? null, 'logout', 'authentication');
}

session_unset();
session_destroy();
header('Location: login.php');
exit;
?>