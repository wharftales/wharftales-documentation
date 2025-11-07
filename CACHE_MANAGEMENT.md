# Cache Management Guide

## Understanding WharfDocs Caching

WharfDocs uses multiple caching layers to improve performance:

### 1. **File Cache** (Application Level)
- Location: `cache/` directory
- Stores: Parsed markdown, navigation, search index
- Cleared by: `php clear-cache.php`

### 2. **PHP OPcache** (Server Level)
- Caches compiled PHP bytecode
- Improves PHP execution speed
- May prevent code changes from taking effect immediately

### 3. **Browser Cache** (Client Level)
- Your browser caches HTML, CSS, JS, images
- Can show stale content even after server cache is cleared

### 4. **Web Server Cache** (Herd/Apache/Nginx)
- Some servers have their own caching mechanisms
- Herd may cache responses

## How to Clear Cache Completely

### Step 1: Run the Cache Clear Script
```bash
php clear-cache.php
```

This will:
- ✓ Clear all `.cache` files
- ✓ Attempt to clear PHP OPcache
- ✓ Show statistics

### Step 2: Hard Refresh Your Browser
- **Mac**: `Cmd + Shift + R`
- **Windows/Linux**: `Ctrl + Shift + R`
- **Or**: Open DevTools > Network tab > Check "Disable cache"

### Step 3: Restart Web Server (if needed)
```bash
# For Herd
herd restart

# For PHP built-in server
# Stop (Ctrl+C) and restart:
php -S localhost:8000
```

## Development vs Production

### Development Mode (Current Setup)
The following headers are enabled in `index.php` to prevent browser caching:

```php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
```

**Pros**: Changes appear immediately  
**Cons**: Slower page loads

### Production Mode
Comment out the cache-control headers in `index.php` for better performance:

```php
// Cache Control Headers (disable caching for development)
// Comment out these lines in production for better performance
// header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
// header('Cache-Control: post-check=0, pre-check=0', false);
// header('Pragma: no-cache');
```

**Pros**: Much faster page loads  
**Cons**: Need to clear cache to see changes

## Troubleshooting

### "Homepage not updating after cache clear"

**Possible causes:**
1. **Browser cache** - Do a hard refresh (Cmd+Shift+R)
2. **PHP OPcache** - Restart your web server
3. **Herd cache** - Run `herd restart`
4. **File permissions** - Ensure cache directory is writable

### "Cache clear script shows 0 files but content is cached"

This means the issue is **not** the file cache. Check:
- Browser cache (hard refresh)
- PHP OPcache (restart server)
- Server-level caching (restart Herd/Apache/Nginx)

### "Changes to PHP files not appearing"

This is **PHP OPcache**. Solutions:
1. Restart your web server
2. Disable OPcache in development (php.ini):
   ```ini
   opcache.enable=0
   ```

## Cache File Naming

Cache files use MD5 hashes of cache keys:
- `page_[md5(version_path)].cache` - Page content
- `versions_list.cache` - Available versions
- `navigation_[version].cache` - Navigation tree
- `search_index_[version].cache` - Search data

## Automatic Cache Invalidation

WharfDocs automatically invalidates cache when:
- Source markdown files are modified (checks `filemtime`)
- Version configuration changes
- Navigation structure changes

You only need to manually clear cache when:
- Switching between versions
- Template/layout changes
- PHP code changes
- Debugging cache issues
