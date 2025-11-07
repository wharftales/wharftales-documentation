<?php
/**
 * WharfDocs - Static PHP Documentation Generator
 */

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Cache Control Headers (disable caching for development)
// Comment out these lines in production for better performance
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Load Parsedown libraries
require_once __DIR__ . '/lib/Parsedown.php';
require_once __DIR__ . '/lib/ParsedownExtra.php';

// Load WharfDocs classes
require_once __DIR__ . '/src/Cache.php';
require_once __DIR__ . '/src/VersionManager.php';
require_once __DIR__ . '/src/DocumentationEngine.php';
require_once __DIR__ . '/src/MarkdownParser.php';
require_once __DIR__ . '/src/NavigationBuilder.php';
require_once __DIR__ . '/src/SearchIndexer.php';

use WharfDocs\DocumentationEngine;

// Initialize the documentation engine
$docsEngine = new DocumentationEngine();

// Get the requested page from URL
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';

// Support query parameter for servers without mod_rewrite
// Example: index.php?path=getting-started/introduction
if (isset($_GET['path'])) {
    $path = $_GET['path'];
} else {
    // Remove query string
    $path = parse_url($requestUri, PHP_URL_PATH);
    
    // Remove script name if present (for PHP built-in server)
    $path = str_replace('/index.php', '', $path);
    
    // Remove base path
    $basePath = dirname($scriptName);
    if ($basePath !== '/') {
        $path = str_replace($basePath, '', $path);
    }
}

$path = trim($path, '/');

// Handle API requests
if (strpos($path, 'api/') === 0) {
    header('Content-Type: application/json');
    
    if ($path === 'api/search') {
        $query = $_GET['q'] ?? '';
        
        // Validate and sanitize search query
        $query = trim($query);
        if (strlen($query) > 200) {
            http_response_code(400);
            echo json_encode(['error' => 'Query too long']);
            exit;
        }
        
        echo json_encode($docsEngine->search($query));
        exit;
    }
    
    if ($path === 'api/navigation') {
        echo json_encode($docsEngine->getNavigation());
        exit;
    }
    
    http_response_code(404);
    echo json_encode(['error' => 'API endpoint not found']);
    exit;
}

// Serve the documentation page
$docsEngine->render($path);
