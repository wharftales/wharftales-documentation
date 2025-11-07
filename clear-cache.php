#!/usr/bin/env php
<?php
/**
 * Clear Cache Utility
 * Run this script to clear all cached content
 */

require_once __DIR__ . '/src/Cache.php';

use WharfDocs\Cache;

// Load config
$config = require __DIR__ . '/config.php';
$cacheDir = $config['cache']['directory'] ?? __DIR__ . '/cache';

// Initialize cache
$cache = new Cache($cacheDir, true);

echo "=== WharfDocs Cache Cleaner ===\n\n";

// 1. Clear file cache
echo "1. Clearing file cache...\n";
if ($cache->clear()) {
    echo "   ✓ File cache cleared successfully!\n";
    echo "   Cache directory: $cacheDir\n";
} else {
    echo "   ✗ Failed to clear cache or cache directory doesn't exist.\n";
}

// 2. Clear OPcache if available
echo "\n2. Clearing PHP OPcache...\n";
if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "   ✓ OPcache cleared successfully!\n";
    } else {
        echo "   ✗ Failed to clear OPcache.\n";
    }
} else {
    echo "   ⓘ OPcache not available or not enabled.\n";
}

// 3. Show cache statistics
echo "\n3. Cache Statistics:\n";
$remainingFiles = glob($cacheDir . '/*.cache');
echo "   Remaining cache files: " . count($remainingFiles) . "\n";

if (function_exists('opcache_get_status')) {
    $opcacheStatus = opcache_get_status();
    if ($opcacheStatus !== false) {
        echo "   OPcache enabled: " . ($opcacheStatus['opcache_enabled'] ? 'Yes' : 'No') . "\n";
    }
}

echo "\n=== Cache Clearing Complete ===\n";
echo "\nNote: If you're still seeing cached content:\n";
echo "  • Hard refresh your browser (Ctrl+Shift+R or Cmd+Shift+R)\n";
echo "  • Restart your web server (Herd, Apache, Nginx, etc.)\n";
echo "  • Check browser DevTools > Network > Disable cache\n";
