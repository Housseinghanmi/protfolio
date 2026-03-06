<?php
/**
 * Visitor Tracker Backend
 * Handles storing visitor data with email notifications and security
 */

header('Content-Type: application/json');

// Load configuration
require_once __DIR__ . '/config.php';

// Define data storage paths
$dataDir = DATA_DIR;
$visitorsFile = $dataDir . '/visitors.json';
$logFile = $dataDir . '/visitors.log';
$securityLog = $dataDir . '/security.log';

// Create data directory if it doesn't exist
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

// Security checks
$clientIP = getClientIP();

// Check rate limiting
if (RATE_LIMIT_REQUESTS > 0 && !checkRateLimit($clientIP, $dataDir)) {
    logSecurityEvent('RATE_LIMIT_EXCEEDED', $clientIP, $securityLog);
    http_response_code(429);
    echo json_encode(['status' => 'error', 'message' => 'Rate limited']);
    exit;
}

// Check for bots
if (BLOCK_BOTS && isBot($_SERVER['HTTP_USER_AGENT'] ?? '')) {
    logSecurityEvent('BOT_DETECTED', $clientIP, $securityLog);
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
    exit;
}

// Get the visitor data from request
$input = file_get_contents('php://input');
$visitorData = json_decode($input, true);

if (!$visitorData) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data']);
    exit;
}

// Add server-side data
$visitorData['ipAddress'] = $clientIP;
$visitorData['serverId'] = gethostname();
$visitorData['recordedAt'] = date('Y-m-d H:i:s');

// Store in JSON file
storeVisitorData($visitorData, $visitorsFile);

// Write to log file
writeLogEntry($visitorData, $logFile);

// Send email notification if enabled
if (SEND_EMAIL_NOTIFICATIONS) {
    sendVisitorNotificationEmail($visitorData);
}

// Log request if enabled
if (LOG_ALL_REQUESTS) {
    logSecurityEvent('VISITOR_TRACKED', $clientIP, $securityLog);
}

// Response
http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'Visitor tracked']);

/**
 * Get client IP address
 */
function getClientIP() {
    $ip = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
}

/**
 * Check rate limiting
 */
function checkRateLimit($ip, $dataDir) {
    $rateFile = $dataDir . '/rate_limit.json';
    $now = time();
    $limit = 0;
    
    if (file_exists($rateFile)) {
        $data = json_decode(file_get_contents($rateFile), true) ?? [];
        // Count requests in last minute
        $limit = 0;
        foreach ($data as $timestamp => $ips) {
            if ($now - intval($timestamp) < 60) {
                if (isset($ips[$ip])) {
                    $limit += $ips[$ip];
                }
            }
        }
    }
    
    return $limit < RATE_LIMIT_REQUESTS;
}

/**
 * Check if user agent is a bot
 */
function isBot($userAgent) {
    $bots = ['bot', 'crawler', 'spider', 'curl', 'wget', 'python', 'java(?!script)'];
    foreach ($bots as $bot) {
        if (preg_match('/' . $bot . '/i', $userAgent)) {
            return true;
        }
    }
    return false;
}

/**
 * Log security events
 */
function logSecurityEvent($event, $ip, $filePath) {
    $entry = sprintf(
        "[%s] Event: %s | IP: %s\n",
        date('Y-m-d H:i:s'),
        $event,
        $ip
    );
    file_put_contents($filePath, $entry, FILE_APPEND | LOCK_EX);
}

/**
 * Send email notification to admin
 */
function sendVisitorNotificationEmail($visitorData) {
    $adminEmail = ADMIN_EMAIL;
    
    if (!$adminEmail || $adminEmail === 'your-email@gmail.com') {
        return; // Not configured
    }
    
    // Build email content
    $location = 'N/A';
    if (isset($visitorData['latitude'], $visitorData['longitude'])) {
        $location = sprintf(
            "%.4f°N, %.4f°E",
            $visitorData['latitude'],
            $visitorData['longitude']
        );
    }
    
    $browser = getBrowserName($visitorData['userAgent'] ?? '');
    
    $emailBody = sprintf(
        "New Visitor Alert!\n\n" .
        "Time: %s\n" .
        "Page: %s\n" .
        "IP Address: %s\n" .
        "Location: %s\n" .
        "Browser: %s\n" .
        "Language: %s\n" .
        "Timezone: %s\n" .
        "Referrer: %s\n" .
        "Session: %s\n",
        $visitorData['recordedAt'],
        $visitorData['page'] ?? 'unknown',
        $visitorData['ipAddress'],
        $location,
        $browser,
        $visitorData['language'] ?? 'unknown',
        $visitorData['timezone'] ?? 'unknown',
        $visitorData['referrer'] ?? 'direct',
        substr($visitorData['sessionId'] ?? '', 0, 12) . '...'
    );
    
    $headers = sprintf(
        "From: %s <%s>\r\n" .
        "Content-Type: text/plain; charset=UTF-8\r\n",
        EMAIL_FROM_NAME,
        EMAIL_FROM
    );
    
    $subject = EMAIL_SUBJECT_PREFIX . ' ' . ($visitorData['pageTitle'] ?? 'Portfolio');
    
    // Send email
    @mail($adminEmail, $subject, $emailBody, $headers);
}

/**
 * Get browser name from user agent
 */
function getBrowserName($userAgent) {
    if (stripos($userAgent, 'firefox') !== false) return 'Firefox';
    if (stripos($userAgent, 'edge') !== false) return 'Edge';
    if (stripos($userAgent, 'chrome') !== false) return 'Chrome';
    if (stripos($userAgent, 'safari') !== false) return 'Safari';
    if (stripos($userAgent, 'opera') !== false) return 'Opera';
    return 'Unknown';
}

/**
 * Store visitor data to JSON file
 */
function storeVisitorData($data, $filePath) {
    $visitors = [];
    
    // Load existing data
    if (file_exists($filePath)) {
        $json = file_get_contents($filePath);
        $visitors = json_decode($json, true) ?? [];
    }
    
    // Add new data
    $visitors[] = $data;
    
    // Keep only last 10000 records to prevent file from getting too large
    if (count($visitors) > 10000) {
        $visitors = array_slice($visitors, -10000);
    }
    
    // Save to file
    file_put_contents($filePath, json_encode($visitors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

/**
 * Write entry to log file
 */
function writeLogEntry($data, $filePath) {
    $logEntry = sprintf(
        "[%s] IP: %s | Page: %s | Location: %.4f,%.4f | Session: %s\n",
        $data['recordedAt'],
        $data['ipAddress'],
        $data['page'],
        $data['latitude'] ?? 0,
        $data['longitude'] ?? 0,
        $data['sessionId']
    );
    
    file_put_contents($filePath, $logEntry, FILE_APPEND | LOCK_EX);
}
?>
