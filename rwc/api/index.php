<?php
// Disable error display for production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// API request router
header('Content-Type: application/json');

// Set up error handling
$log_file = __DIR__ . '/../logs/api_redirects.log';

try {
    // Log API requests
    $request_uri = $_SERVER['REQUEST_URI'];
    $request_method = $_SERVER['REQUEST_METHOD'];
    $query_string = $_SERVER['QUERY_STRING'] ?? '';
    $remote_addr = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    // Log the request
    $log_entry = date('Y-m-d H:i:s') . " | $remote_addr | $request_method | $request_uri | $query_string | $user_agent\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);

    // Extract the endpoint from the request URI
    $path = parse_url($request_uri, PHP_URL_PATH);
    $endpoint = basename($path);

    // If the request is directly to api/ or api/index.php, redirect to land_surface_stations.php
    if ($endpoint === 'api' || $endpoint === 'index.php') {
        // Pass all query parameters to the land_surface_stations.php endpoint
        include 'land_surface_stations.php';
        exit;
    }

    // Otherwise handle specific endpoints
    switch ($endpoint) {
        case 'land_surface':
        case 'land_surface_stations':
            include 'land_surface_stations.php';
            break;
        
        case 'upper_air':
        case 'upper_air_stations':
            include 'upper_air_stations.php';
            break;
        
        // Add other API endpoints as needed
        
        default:
            // Return an error for unknown endpoints
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'API endpoint not found',
                'endpoint' => $endpoint
            ]);
            break;
    }
} catch (Exception $e) {
    // Log the error
    $error_message = date('Y-m-d H:i:s') . " - Uncaught Exception\n";
    $error_message .= "Message: " . $e->getMessage() . "\n";
    $error_message .= "File: " . $e->getFile() . "\n";
    $error_message .= "Line: " . $e->getLine() . "\n";
    file_put_contents($log_file, $error_message, FILE_APPEND);

    // Return a clean error response
    echo json_encode([
        'error' => true,
        'message' => 'An unexpected error occurred. Please try again later.'
    ]);
}
?> 