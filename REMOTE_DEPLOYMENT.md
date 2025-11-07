# Remote Deployment Guide

## Issue: Version Not Updating on Remote Server

If you've updated `config.php` but the remote server still shows the old version (e.g., v1.0 instead of 0.0.1-alpha), this is caused by **PHP OPcache** caching the old configuration file.

## Solution: Use the Web Cache Clearer

### Step 1: Upload the Web Cache Clearer

Upload `clear-cache-web.php` to your remote server.

### Step 2: Access via Browser

Visit: `https://yoursite.com/clear-cache-web.php?password=wharfdocs2025`

**Important:** Change the password in the file before uploading!

### Step 3: Verify Cache Cleared

The page will show:
- ✓ File cache cleared
- ✓ OPcache cleared (if successful)
- Current configuration (should show your new version)
- Available versions in docs folder

### Step 4: Hard Refresh Browser

Press `Cmd+Shift+R` (Mac) or `Ctrl+Shift+R` (Windows/Linux)

### Step 5: Remove the File (Security!)

After clearing cache, **delete** `clear-cache-web.php` from your server for security.

## Alternative Methods

### Method 1: Via SSH/Terminal

If you have SSH access:

```bash
# Navigate to your project
cd /path/to/wharfdocs

# Run the CLI cache clearer
php clear-cache.php

# Restart PHP-FPM (if you have access)
sudo systemctl restart php-fpm
# OR
sudo service php7.4-fpm restart
```

### Method 2: Restart Web Server

If you have server access:

```bash
# Apache
sudo systemctl restart apache2

# Nginx + PHP-FPM
sudo systemctl restart nginx
sudo systemctl restart php-fpm
```

### Method 3: Disable OPcache (Development Only)

Add to your `php.ini` or `.htaccess`:

```ini
# In php.ini
opcache.enable=0

# OR in .htaccess (if allowed)
php_flag opcache.enable Off
```

**Warning:** This will slow down your site. Only use for development!

### Method 4: Touch Config File

Via FTP or file manager, edit `config.php` and save it. This updates the file modification time and may invalidate OPcache.

## Preventing Future Issues

### Option 1: Auto-invalidate Config (Recommended)

Add this to the top of `index.php`:

```php
// Clear OPcache for config file on every request (development only)
if (function_exists('opcache_invalidate')) {
    opcache_invalidate(__DIR__ . '/config.php', true);
}
```

**Note:** Remove this in production for better performance.

### Option 2: Set OPcache Revalidation

In `php.ini`:

```ini
opcache.revalidate_freq=0  ; Check for changes on every request (dev)
opcache.revalidate_freq=60 ; Check every 60 seconds (production)
```

### Option 3: Use .htaccess

Add to `.htaccess`:

```apache
<IfModule mod_php.c>
    php_value opcache.revalidate_freq 0
</IfModule>
```

## Troubleshooting

### "Still showing old version after clearing cache"

1. **Check config.php directly** - Open it and verify the default version is correct
2. **Browser cache** - Hard refresh (Cmd+Shift+R)
3. **CDN/Proxy cache** - If using Cloudflare, purge cache
4. **Multiple PHP versions** - Ensure you're clearing cache for the right PHP version

### "clear-cache-web.php shows 'Access denied'"

The password doesn't match. Edit the file and check:

```php
$CLEAR_CACHE_PASSWORD = 'wharfdocs2025';
```

Change this to your own password.

### "OPcache clear failed"

This usually means:
- OPcache is enabled but `opcache_reset()` is restricted
- You need to restart the web server instead
- Or use the `opcache_invalidate()` method for specific files

## Security Checklist

- [ ] Change password in `clear-cache-web.php`
- [ ] Delete `clear-cache-web.php` after use
- [ ] Don't commit `clear-cache-web.php` to public repositories
- [ ] Consider IP restriction for cache clearing scripts
- [ ] Use `.htaccess` to protect sensitive files

## Production Best Practices

1. **Enable caching** - Keep OPcache enabled for performance
2. **Clear cache after deployments** - Use deployment scripts
3. **Use version control** - Track config changes
4. **Monitor cache** - Check OPcache statistics regularly
5. **Automate** - Add cache clearing to your deployment pipeline

## Example Deployment Script

```bash
#!/bin/bash
# deploy.sh

echo "Deploying WharfDocs..."

# Pull latest changes
git pull origin main

# Clear cache via CLI
php clear-cache.php

# Restart PHP-FPM
sudo systemctl restart php-fpm

# Or use web cache clearer
curl "https://yoursite.com/clear-cache-web.php?password=YOUR_PASSWORD"

echo "Deployment complete!"
```

## Support

If you're still having issues:

1. Check PHP error logs
2. Verify file permissions on cache directory
3. Ensure PHP version is 7.4+
4. Check if OPcache is enabled: `php -i | grep opcache`
