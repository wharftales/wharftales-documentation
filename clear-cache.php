<?php
/**
 * Web-based Cache Clear Utility
 * Access via browser: http://yoursite.com/clear-cache.php
 * 
 * SECURITY: Remove or password-protect this file in production!
 */

// Simple password protection (change this!)
$CLEAR_CACHE_PASSWORD = 'UseALongAndSecurePasswordHere';

// Check password
$password = $_GET['password'] ?? '';
if ($password !== $CLEAR_CACHE_PASSWORD) {
    http_response_code(403);
    die('Access denied. Use: clear-cache.php?password=YOUR_PASSWORD');
}

require_once __DIR__ . '/app/Cache.php';

use WharfDocs\Cache;

// Load config
$config = require __DIR__ . '/config.php';
$cacheDir = $config['cache']['directory'] ?? __DIR__ . '/cache';

// Initialize cache
$cache = new Cache($cacheDir, true);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WharfDocs Cache Cleaner</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 10px;
        }
        .result {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #3b82f6;
            background: #f0f9ff;
        }
        .success {
            border-left-color: #10b981;
            background: #f0fdf4;
            color: #065f46;
        }
        .error {
            border-left-color: #ef4444;
            background: #fef2f2;
            color: #991b1b;
        }
        .info {
            border-left-color: #f59e0b;
            background: #fffbeb;
            color: #92400e;
        }
        .stat {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .stat:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: 600;
        }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .warning {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .button:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧹 WharfDocs Cache Cleaner</h1>
        
        <?php
        $results = [];
        
        // 1. Clear file cache
        echo '<h2>1. File Cache</h2>';
        if ($cache->clear()) {
            echo '<div class="result success">✓ File cache cleared successfully!</div>';
            echo '<div class="stat"><span class="label">Cache Directory:</span><code>' . htmlspecialchars($cacheDir) . '</code></div>';
        } else {
            echo '<div class="result error">✗ Failed to clear cache or cache directory doesn\'t exist.</div>';
        }
        
        // 2. Clear OPcache
        echo '<h2>2. PHP OPcache</h2>';
        if (function_exists('opcache_reset')) {
            if (opcache_reset()) {
                echo '<div class="result success">✓ OPcache cleared successfully!</div>';
                $results['opcache'] = 'cleared';
            } else {
                echo '<div class="result error">✗ Failed to clear OPcache (may need CLI access).</div>';
                $results['opcache'] = 'failed';
            }
        } else {
            echo '<div class="result info">ⓘ OPcache not available or not enabled.</div>';
            $results['opcache'] = 'not_available';
        }
        
        // 3. Invalidate specific config file from OPcache
        echo '<h2>3. Config File Cache</h2>';
        $configFile = __DIR__ . '/config.php';
        if (function_exists('opcache_invalidate')) {
            if (opcache_invalidate($configFile, true)) {
                echo '<div class="result success">✓ Config file invalidated from OPcache</div>';
            } else {
                echo '<div class="result error">✗ Failed to invalidate config file</div>';
            }
        } else {
            echo '<div class="result info">ⓘ opcache_invalidate not available</div>';
        }
        
        // 4. Statistics
        echo '<h2>4. Cache Statistics</h2>';
        $remainingFiles = glob($cacheDir . '/*.cache');
        echo '<div class="stat"><span class="label">Remaining cache files:</span><span>' . count($remainingFiles) . '</span></div>';
        
        if (function_exists('opcache_get_status')) {
            $opcacheStatus = opcache_get_status();
            if ($opcacheStatus !== false) {
                echo '<div class="stat"><span class="label">OPcache enabled:</span><span>' . ($opcacheStatus['opcache_enabled'] ? 'Yes' : 'No') . '</span></div>';
                if (isset($opcacheStatus['opcache_statistics'])) {
                    echo '<div class="stat"><span class="label">OPcache hits:</span><span>' . number_format($opcacheStatus['opcache_statistics']['hits']) . '</span></div>';
                    echo '<div class="stat"><span class="label">OPcache misses:</span><span>' . number_format($opcacheStatus['opcache_statistics']['misses']) . '</span></div>';
                }
            }
        }
        
        // 5. Version check
        echo '<h2>5. Current Configuration</h2>';
        echo '<div class="stat"><span class="label">Default Version:</span><code>' . htmlspecialchars($config['versions']['default'] ?? 'not set') . '</code></div>';
        echo '<div class="stat"><span class="label">Versioning Enabled:</span><span>' . ($config['versions']['enabled'] ? 'Yes' : 'No') . '</span></div>';
        
        // Check available versions
        $docsPath = __DIR__ . '/docs';
        if (is_dir($docsPath)) {
            $versions = [];
            $entries = scandir($docsPath);
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') continue;
                if (is_dir($docsPath . '/' . $entry)) {
                    $versions[] = $entry;
                }
            }
            echo '<div class="stat"><span class="label">Available Versions:</span><code>' . implode(', ', $versions) . '</code></div>';
        }
        ?>
        
        <div class="warning">
            <strong>⚠️ Security Warning:</strong> This file should be removed or password-protected in production!
            <br>Current password is set in the file. Change it or delete this file after use.
        </div>
        
        <h2>Next Steps</h2>
        <ol>
            <li>Hard refresh your browser: <code>Ctrl+Shift+R</code> (Windows/Linux) or <code>Cmd+Shift+R</code> (Mac)</li>
            <li>If still showing old version, restart your web server</li>
            <li>Check that <code>config.php</code> has the correct default version</li>
        </ol>
        
        <a href="?" class="button">🔄 Clear Cache Again</a>
        <a href="/" class="button">🏠 Go to Homepage</a>
    </div>
</body>
</html>
