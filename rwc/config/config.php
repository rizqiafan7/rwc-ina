<?php
// config/config.php
define('SITE_TITLE', 'Indonesian Operational System Monitoring');
define('SUPPORT_PHONE', '192');

function detectSiteUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
    
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    
    // Jika kita menggunakan PHP built-in server
    if (php_sapi_name() === 'cli-server') {
        return $protocol . '://' . $host;
    }
    
    // Dapatkan path dasar dari SCRIPT_NAME
    $scriptPath = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
    $baseDir = dirname($scriptPath);
    
    // Normalisasi path - hapus multiple slashes dan trailing slash
    $baseDir = rtrim(preg_replace('#/+#', '/', $baseDir), '/');
    
    // Jika kita berada di root, baseDir akan '/'
    if ($baseDir === '' || $baseDir === '.') {
        $baseDir = '';
    }
    
    // Kembalikan URL dasar
    return $protocol . '://' . $host . $baseDir;
}

define('SITE_URL', detectSiteUrl());
define('BASE_PATH', realpath(dirname(__DIR__)));

$isDev = true; // Force development mode to show errors

if ($isDev) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

date_default_timezone_set('Asia/Jakarta');

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

function asset($path = '') {
    if (php_sapi_name() === 'cli-server') {
        return '/assets/' . ltrim($path, '/');
    }
    
    // Base URL dari konfigurasi
    $baseUrl = rtrim(SITE_URL, '/');
    
    // Dapatkan root domain tanpa subdirektori
    $urlParts = parse_url($baseUrl);
    $rootUrl = $urlParts['scheme'] . '://' . $urlParts['host'];
    if (isset($urlParts['port'])) {
        $rootUrl .= ':' . $urlParts['port'];
    }
    
    // Bersihkan path asset
    $assetPath = ltrim($path, '/');
    
    // Gabungkan root URL dengan path asset
    return $rootUrl . '/assets/' . $assetPath;
}

function url($path = '') {
    // Base URL dari konfigurasi
    $baseUrl = rtrim(SITE_URL, '/');
    
    // Handle empty path
    if (empty($path)) {
        return $baseUrl . '/index.php';
    }
    
    // Clean the path and check if it starts with a slash
    $startsWithSlash = substr($path, 0, 1) === '/';
    $cleanPath = ltrim($path, '/');
    
    // Cek apakah path sudah mengandung domain/baseUrl
    if (strpos($cleanPath, 'http://') === 0 || strpos($cleanPath, 'https://') === 0) {
        return $cleanPath;
    }
    
    // Jika path dimulai dengan slash, gunakan path absolut dari root domain
    if ($startsWithSlash) {
        // Dapatkan root domain tanpa subdirektori
        $urlParts = parse_url($baseUrl);
        $rootUrl = $urlParts['scheme'] . '://' . $urlParts['host'];
        if (isset($urlParts['port'])) {
            $rootUrl .= ':' . $urlParts['port'];
        }
        
        // Handle paths that don't have .php extension
        if (strpos($cleanPath, '.') === false && substr($cleanPath, -1) !== '/') {
            // Check if it's a directory that might contain index.php
            if (is_dir(BASE_PATH . '/' . $cleanPath)) {
                $cleanPath .= '/index.php';
            } else {
                $cleanPath .= '.php';
            }
        }
        
        return $rootUrl . '/' . $cleanPath;
    }
    
    // Untuk path relatif, gunakan logika yang ada
    // Handle paths that don't have .php extension
    if (strpos($cleanPath, '.') === false && substr($cleanPath, -1) !== '/') {
        // Check if it's a directory that might contain index.php
        if (is_dir(BASE_PATH . '/' . $cleanPath)) {
            $cleanPath .= '/index.php';
        } else {
            $cleanPath .= '.php';
        }
    }
    
    return $baseUrl . '/' . $cleanPath;
}

function isActivePage($path) {
    // Get current request path
    $currentPath = $_SERVER['REQUEST_URI'];
    $targetPath = ltrim($path, '/');
    
    // Handle index paths
    if ($targetPath === 'index.php' || $targetPath === '') {
        return $currentPath === '/' || $currentPath === '/index.php';
    }
    
    // Convert both paths to consistent format
    $currentPathNormalized = rtrim(strtok($currentPath, '?'), '/');
    $targetPathNormalized = rtrim($targetPath, '/');
    
    // Remove .php extension if present for comparison
    if (substr($currentPathNormalized, -4) === '.php') {
        $currentPathNormalized = substr($currentPathNormalized, 0, -4);
    }
    if (substr($targetPathNormalized, -4) === '.php') {
        $targetPathNormalized = substr($targetPathNormalized, 0, -4);
    }
    
    // Handle index files in directories
    if (substr($targetPathNormalized, -5) === 'index') {
        $targetPathNormalized = substr($targetPathNormalized, 0, -5);
        $targetPathNormalized = rtrim($targetPathNormalized, '/');
    }
    if (substr($currentPathNormalized, -5) === 'index') {
        $currentPathNormalized = substr($currentPathNormalized, 0, -5);
        $currentPathNormalized = rtrim($currentPathNormalized, '/');
    }
    
    // Compare normalized paths
    return $currentPathNormalized === $targetPathNormalized || 
           strpos($currentPathNormalized, $targetPathNormalized) !== false;
}


// Utility functions
function sanitize($input) {
    return is_array($input) ? array_map('sanitize', $input) : htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit();
}

// Ensure required directories exist
foreach (['logs', 'data'] as $dir) {
    $dirPath = BASE_PATH . '/' . $dir;
    if (!is_dir($dirPath)) {
        mkdir($dirPath, 0755, true);
    }
}
?>