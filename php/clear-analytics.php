<?php
/**
 * Clear Analytics Data
 * Clears all visitor tracking data with enhanced security
 */

header('Content-Type: application/json');

// Load configuration
require_once __DIR__ . '/config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$providedKey = $_POST['key'] ?? '';
$secretKey = CLEAR_DATA_SECRET_KEY;

// Check for basic authentication (from dashboard)
if (empty($providedKey)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Missing authentication key']);
    exit;
}

// Verify the secret key
if ($providedKey !== $secretKey || $secretKey === 'your_secret_delete_key_change_me_xyz789') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - Invalid key']);
    exit;
}

// Require confirmation token
$expectedToken = hash('sha256', $secretKey . date('Y-m-d'));
$providedToken = $_POST['token'] ?? '';

if ($providedToken !== $expectedToken) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid confirmation token']);
    exit;
}

$dataDir = DATA_DIR;
$visitorsFile = $dataDir . '/visitors.json';
$logFile = $dataDir . '/visitors.log';
$deletionLog = $dataDir . '/deletion_log.txt';

// Log the deletion attempt
$logEntry = sprintf(
    "[%s] IP: %s | Status: CLEARED ALL DATA\n",
    date('Y-m-d H:i:s'),
    $_SERVER['REMOTE_ADDR']
);
@file_put_contents($deletionLog, $logEntry, FILE_APPEND);

$success = false;

// Delete visitor data
if (file_exists($visitorsFile)) {
    $success = @unlink($visitorsFile);
}

// Delete log file
if (file_exists($logFile)) {
    @unlink($logFile);
}

if ($success && !file_exists($visitorsFile)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'All visitor data has been cleared',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to clear data']);
}
?>

