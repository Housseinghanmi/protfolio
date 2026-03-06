<?php
/**
 * Get Analytics Data
 * Retrieves and processes visitor analytics with API key authentication
 */

header('Content-Type: application/json');

// Load configuration
require_once __DIR__ . '/config.php';

// Check API key
$apiKey = isset($_GET['key']) ? $_GET['key'] : (isset($_POST['key']) ? $_POST['key'] : '');

if (empty($apiKey) || $apiKey !== ANALYTICS_API_KEY) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized - Invalid API key']);
    exit;
}

$dataDir = DATA_DIR;
$visitorsFile = $dataDir . '/visitors.json';

// Load visitor data
$visitors = [];
if (file_exists($visitorsFile)) {
    $json = file_get_contents($visitorsFile);
    $visitors = json_decode($json, true) ?? [];
}

// Calculate statistics
$stats = calculateStats($visitors);
$pageStats = calculatePageStats($visitors);

// Prepare response
$response = [
    'status' => 'success',
    'stats' => $stats,
    'visitors' => array_reverse($visitors), // Most recent first
    'pageStats' => $pageStats,
    'lastUpdate' => date('Y-m-d H:i:s')
];

echo json_encode($response);

/**
 * Calculate statistics from visitor data
 */
function calculateStats($visitors) {
    $totalVisitors = count($visitors);
    
    // Today's visits
    $today = date('Y-m-d');
    $todayVisits = 0;
    $uniqueSessions = [];
    $locationsWithData = 0;

    foreach ($visitors as $visitor) {
        if (isset($visitor['timestamp'])) {
            $visitDate = substr($visitor['timestamp'], 0, 10);
            if ($visitDate === $today) {
                $todayVisits++;
            }
        }
        
        if (isset($visitor['sessionId'])) {
            $uniqueSessions[$visitor['sessionId']] = true;
        }
        
        if (isset($visitor['latitude'], $visitor['longitude']) && $visitor['latitude'] > 0) {
            $locationsWithData++;
        }
    }

    return [
        'totalVisitors' => $totalVisitors,
        'todayVisits' => $todayVisits,
        'uniqueLocations' => count(array_unique(
            array_filter(
                array_map(fn($v) => isset($v['latitude'], $v['longitude']) ? 
                    round($v['latitude'], 2) . ',' . round($v['longitude'], 2) : null, 
                    $visitors
                )
            )
        )),
        'activeSessions' => count($uniqueSessions),
        'locationsWithData' => $locationsWithData
    ];
}

/**
 * Calculate page statistics
 */
function calculatePageStats($visitors) {
    $pageStats = [];

    foreach ($visitors as $visitor) {
        if (isset($visitor['page'])) {
            $page = $visitor['page'];
            if (!isset($pageStats[$page])) {
                $pageStats[$page] = [
                    'count' => 0,
                    'lastVisited' => $visitor['timestamp'] ?? date('Y-m-d H:i:s')
                ];
            }
            $pageStats[$page]['count']++;
            $pageStats[$page]['lastVisited'] = $visitor['timestamp'] ?? date('Y-m-d H:i:s');
        }
    }

    return $pageStats;
}
?>
