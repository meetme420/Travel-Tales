<?php
/**
 * logout.php — Destroys the session and returns JSON success.
 * The frontend redirects to login.html after calling this.
 */
session_start();
session_unset();
session_destroy();

header('Content-Type: application/json');
echo json_encode(['success' => true]);
