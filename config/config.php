<?php
// Configuration file for D&D Battle Manager

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// File paths
define('ROOT_PATH', dirname(__DIR__));
define('DB_FILE', ROOT_PATH . '/data.sqlite');
define('JSON_FILE', ROOT_PATH . '/data.json'); // Only used for export/import

// Error handling
function setError($message)
{
    $_SESSION['error'] = $message;
}

function getError()
{
    $error = $_SESSION['error'] ?? null;
    unset($_SESSION['error']);
    return $error;
}

// Success messages
function setSuccess($message)
{
    $_SESSION['success'] = $message;
}

function getSuccess()
{
    $success = $_SESSION['success'] ?? null;
    unset($_SESSION['success']);
    return $success;
}
