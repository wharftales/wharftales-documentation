<?php

namespace WharfDocs;

class Cache
{
    private $cacheDir;
    private $enabled;

    public function __construct($cacheDir = null, $enabled = true)
    {
        $this->cacheDir = $cacheDir ?? __DIR__ . '/../cache';
        $this->enabled = $enabled;
        
        // Create cache directory if it doesn't exist
        if ($this->enabled && !is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Get cached data if it exists and is still valid
     * 
     * @param string $key Cache key
     * @param array $sourceFiles Files to check for modifications
     * @return mixed|null Cached data or null if invalid/missing
     */
    public function get($key, $sourceFiles = [])
    {
        if (!$this->enabled) {
            return null;
        }

        $cacheFile = $this->getCacheFilePath($key);
        
        if (!file_exists($cacheFile)) {
            return null;
        }

        // Check if cache is still valid based on source file modifications
        if (!empty($sourceFiles)) {
            $cacheTime = filemtime($cacheFile);
            
            foreach ($sourceFiles as $sourceFile) {
                if (file_exists($sourceFile) && filemtime($sourceFile) > $cacheTime) {
                    // Source file is newer than cache, invalidate
                    return null;
                }
            }
        }

        $data = file_get_contents($cacheFile);
        return unserialize($data);
    }

    /**
     * Store data in cache
     * 
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @return bool Success status
     */
    public function set($key, $data)
    {
        if (!$this->enabled) {
            return false;
        }

        $cacheFile = $this->getCacheFilePath($key);
        $serialized = serialize($data);
        
        return file_put_contents($cacheFile, $serialized) !== false;
    }

    /**
     * Delete cached data
     * 
     * @param string $key Cache key
     * @return bool Success status
     */
    public function delete($key)
    {
        if (!$this->enabled) {
            return false;
        }

        $cacheFile = $this->getCacheFilePath($key);
        
        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }
        
        return true;
    }

    /**
     * Clear all cache
     * 
     * @return bool Success status
     */
    public function clear()
    {
        if (!$this->enabled) {
            return false;
        }

        // Create cache directory if it doesn't exist
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
            return true; // Nothing to clear, but directory is ready
        }

        $files = glob($this->cacheDir . '/*.cache');
        
        if ($files === false) {
            return false; // glob failed
        }
        
        $deletedCount = 0;
        foreach ($files as $file) {
            if (is_file($file) && unlink($file)) {
                $deletedCount++;
            }
        }
        
        return true;
    }
    
    /**
     * Get count of cached files
     * 
     * @return int Number of cache files
     */
    public function getCacheCount()
    {
        if (!$this->enabled || !is_dir($this->cacheDir)) {
            return 0;
        }
        
        $files = glob($this->cacheDir . '/*.cache');
        return $files ? count($files) : 0;
    }

    /**
     * Get the most recent modification time from a list of files/directories
     * 
     * @param array $paths Array of file or directory paths
     * @return int Most recent modification timestamp
     */
    public function getNewestModificationTime($paths)
    {
        $newestTime = 0;
        
        foreach ($paths as $path) {
            if (is_file($path)) {
                $mtime = filemtime($path);
                if ($mtime > $newestTime) {
                    $newestTime = $mtime;
                }
            } elseif (is_dir($path)) {
                $dirTime = $this->getDirectoryModificationTime($path);
                if ($dirTime > $newestTime) {
                    $newestTime = $dirTime;
                }
            }
        }
        
        return $newestTime;
    }

    /**
     * Get the most recent modification time in a directory (recursive)
     * 
     * @param string $dir Directory path
     * @return int Most recent modification timestamp
     */
    private function getDirectoryModificationTime($dir)
    {
        $newestTime = 0;
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $mtime = $file->getMTime();
                if ($mtime > $newestTime) {
                    $newestTime = $mtime;
                }
            }
        }
        
        return $newestTime;
    }

    /**
     * Get cache file path for a given key
     * 
     * @param string $key Cache key
     * @return string Cache file path
     */
    private function getCacheFilePath($key)
    {
        $hash = md5($key);
        return $this->cacheDir . '/' . $hash . '.cache';
    }

    /**
     * Check if caching is enabled
     * 
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
}
