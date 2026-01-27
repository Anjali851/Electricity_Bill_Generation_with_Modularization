<?php
// src/inc/db.php
// Centralized DB connection and helper

$config = require __DIR__ . '/../../config.php';

$mysqli = new mysqli(
    $config['db_host'],
    $config['db_user'],
    $config['db_pass'],
    $config['db_name']
);

if ($mysqli->connect_errno) {
    // Fail fast with a generic message (log details to file in production)
    error_log("DB connect error: " . $mysqli->connect_error);
    die("Database connection failed.");
}

$mysqli->set_charset('utf8mb4');

function db() {
    global $mysqli;
    return $mysqli;
}
?>