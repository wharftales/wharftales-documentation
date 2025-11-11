<?php

namespace WharfDocs;

class VersionManager
{
    private $config;
    private $docsBasePath;
    private $cache;

    public function __construct($config, $docsBasePath, $cache = null)
    {
        $this->config = $config;
        $this->docsBasePath = $docsBasePath;
        $this->cache = $cache;
    }

    /**
     * Get all available versions
     */
    public function getVersions()
    {
        if (!$this->isVersioningEnabled()) {
            return [];
        }

        // Try to get cached versions
        if ($this->cache && $this->cache->isEnabled()) {
            $cacheKey = 'versions_list';
            $cachedVersions = $this->cache->get($cacheKey);
            
            if ($cachedVersions !== null) {
                return $cachedVersions;
            }
        }

        $versions = [];
        
        // Scan for version directories
        if (is_dir($this->docsBasePath)) {
            $entries = scandir($this->docsBasePath);
            
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                
                $fullPath = $this->docsBasePath . '/' . $entry;
                
                // Check if it's a version directory (starts with 'v' or is a number)
                if (is_dir($fullPath) && $this->isVersionDirectory($entry)) {
                    $versionInfo = $this->getVersionInfo($entry, $fullPath);
                    if ($versionInfo) {
                        $versions[] = $versionInfo;
                    }
                }
            }
        }

        // Sort versions (latest first)
        usort($versions, function($a, $b) {
            return version_compare($b['number'], $a['number']);
        });

        // Cache the versions
        if ($this->cache && $this->cache->isEnabled()) {
            $this->cache->set('versions_list', $versions);
        }

        return $versions;
    }

    /**
     * Get the current version from the path
     */
    public function extractVersionFromPath($path)
    {
        if (!$this->isVersioningEnabled()) {
            return $this->getDefaultVersion();
        }

        // Check if path starts with a version
        $parts = explode('/', trim($path, '/'));
        
        if (!empty($parts[0]) && $this->isVersionDirectory($parts[0])) {
            return $parts[0];
        }

        return $this->getDefaultVersion();
    }

    /**
     * Get the default version
     */
    public function getDefaultVersion()
    {
        if (!$this->isVersioningEnabled()) {
            return null;
        }

        // Check if default version is set in config
        if (!empty($this->config['versions']['default'])) {
            return $this->config['versions']['default'];
        }

        // Otherwise, get the latest version
        $versions = $this->getVersions();
        
        if (!empty($versions)) {
            return $versions[0]['slug'];
        }

        return null;
    }

    /**
     * Get the latest version
     */
    public function getLatestVersion()
    {
        $versions = $this->getVersions();
        
        if (!empty($versions)) {
            return $versions[0]['slug'];
        }

        return null;
    }

    /**
     * Get version info by slug
     */
    public function getVersionBySlug($slug)
    {
        $versions = $this->getVersions();
        
        foreach ($versions as $version) {
            if ($version['slug'] === $slug) {
                return $version;
            }
        }

        return null;
    }

    /**
     * Get the docs path for a specific version
     */
    public function getVersionDocsPath($version = null)
    {
        if (!$this->isVersioningEnabled()) {
            return $this->docsBasePath;
        }

        if ($version === null) {
            $version = $this->getDefaultVersion();
        }

        if ($version === null) {
            return $this->docsBasePath;
        }

        return $this->docsBasePath . '/' . $version;
    }

    /**
     * Remove version from path
     */
    public function removeVersionFromPath($path)
    {
        if (!$this->isVersioningEnabled()) {
            return $path;
        }

        $parts = explode('/', trim($path, '/'));
        
        if (!empty($parts[0]) && $this->isVersionDirectory($parts[0])) {
            array_shift($parts);
            return implode('/', $parts);
        }

        return $path;
    }

    /**
     * Add version to path
     */
    public function addVersionToPath($path, $version)
    {
        if (!$this->isVersioningEnabled()) {
            return $path;
        }

        $cleanPath = $this->removeVersionFromPath($path);
        
        if (empty($cleanPath)) {
            return $version;
        }

        return $version . '/' . $cleanPath;
    }

    /**
     * Check if versioning is enabled
     */
    public function isVersioningEnabled()
    {
        return !empty($this->config['versions']['enabled']);
    }

    /**
     * Check if a directory name is a version directory
     */
    private function isVersionDirectory($name)
    {
        // Check if it matches version pattern (v1.0, v2.0, 1.0, 2.0, etc.)
        return preg_match('/^v?\d+(\.\d+)*(-[a-z]+)?$/i', $name);
    }

    /**
     * Get version information from directory
     */
    private function getVersionInfo($dirName, $fullPath)
    {
        // Check for version.json file
        $versionFile = $fullPath . '/version.json';
        
        if (file_exists($versionFile)) {
            $versionData = json_decode(file_get_contents($versionFile), true);
            
            if ($versionData) {
                return [
                    'slug' => $dirName,
                    'number' => $versionData['number'] ?? $dirName,
                    'label' => $versionData['label'] ?? $dirName,
                    'status' => $versionData['status'] ?? 'stable',
                    'released' => $versionData['released'] ?? null,
                ];
            }
        }

        // Default version info based on directory name
        $number = preg_replace('/^v/', '', $dirName);
        
        return [
            'slug' => $dirName,
            'number' => $number,
            'label' => $dirName,
            'status' => 'stable',
            'released' => null,
        ];
    }

    /**
     * Get version badge/label
     */
    public function getVersionBadge($version)
    {
        $versionInfo = $this->getVersionBySlug($version);
        
        if (!$versionInfo) {
            return '';
        }

        $badges = [];
        
        // Check if it's the latest version
        $latestVersion = $this->getLatestVersion();
        if ($version === $latestVersion) {
            $badges[] = 'Latest';
        }

        // Add status badge
        if (!empty($versionInfo['status']) && $versionInfo['status'] !== 'stable') {
            $badges[] = ucfirst($versionInfo['status']);
        }

        return !empty($badges) ? ' (' . implode(', ', $badges) . ')' : '';
    }

    /**
     * Check if version exists
     */
    public function versionExists($version)
    {
        return $this->getVersionBySlug($version) !== null;
    }
}
