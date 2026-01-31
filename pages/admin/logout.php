<?php
// logout.php - Production-ready with BASE_URL

session_start();

// Include config if BASE_URL is defined there
require_once '../../includes/db.php'; // ← if you have BASE_URL here

// Clear session data
$_SESSION = array();

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect to login
if (defined('BASE_URL')) {
    header("Location: " . BASE_URL . "/pages/login.php");
} else {
    header("Location: /pages/login.php");
}
exit;