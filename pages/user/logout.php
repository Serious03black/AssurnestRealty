<?php

session_start();

require_once '../../includes/db.php'; 

$_SESSION = array();

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

if (defined('BASE_URL')) {
    header("Location: " . BASE_URL . "/pages/login.php");
} else {
    header("Location: /pages/login.php");
}
exit;