<?php

namespace WharfDocs;

class DocumentationEngine
{
    private $docsPath;
    private $parser;
    private $navigation;
    private $searchIndexer;
    private $config;
    private $cache;
    private $versionManager;
    private $currentVersion;

    public function __construct()
    {
        $this->docsPath = __DIR__ . '/../docs';
        $this->loadConfig();
        
        // Initialize cache
        $cacheEnabled = $this->config['cache']['enabled'] ?? true;
        $cacheDir = $this->config['cache']['directory'] ?? __DIR__ . '/../cache';
        $this->cache = new Cache($cacheDir, $cacheEnabled);
        
        // Initialize version manager
        $this->versionManager = new VersionManager($this->config, $this->docsPath, $this->cache);
        
        $this->parser = new MarkdownParser();
        // Navigation and search will be initialized per version
    }

    private function loadConfig()
    {
        $configFile = __DIR__ . '/../config.php';
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        } else {
            $this->config = [
                'site_name' => 'Documentation',
                'site_description' => 'Project Documentation',
                'theme' => 'default',
                'default_page' => 'getting-started/introduction'
            ];
        }
        
        // Make config available globally for templates
        $GLOBALS['config'] = $this->config;
    }

    public function render($path = '')
    {
        // Extract version from path
        $this->currentVersion = $this->versionManager->extractVersionFromPath($path);
        
        // If no version in path and versioning is enabled, redirect to default version
        if ($this->versionManager->isVersioningEnabled() && 
            $this->currentVersion && 
            !$this->versionManager->versionExists($this->currentVersion)) {
            $this->currentVersion = $this->versionManager->getDefaultVersion();
        }
        
        // Initialize navigation and search for current version
        $versionDocsPath = $this->versionManager->getVersionDocsPath($this->currentVersion);
        $this->navigation = new NavigationBuilder($versionDocsPath, $this->cache);
        $this->searchIndexer = new SearchIndexer($versionDocsPath, $this->cache, $this->currentVersion);
        
        // Remove version from path for file lookup
        $docPath = $this->versionManager->removeVersionFromPath($path);
        
        // Default to introduction page
        if (empty($docPath) || $docPath === '/') {
            $docPath = $this->config['default_page'];
        }

        // Find the markdown file
        $mdFile = $this->findMarkdownFile($docPath, $versionDocsPath);
        
        if (!$mdFile) {
            $this->render404();
            return;
        }

        // Try to get cached page data (include version in cache key)
        $cacheKey = 'page_' . md5($this->currentVersion . '_' . $docPath);
        $cachedData = $this->cache->get($cacheKey, [$mdFile]);
        
        if ($cachedData !== null) {
            // Use cached data but rebuild navigation (it has its own cache)
            $cachedData['navigation'] = $this->navigation->build();
            $cachedData['currentVersion'] = $this->currentVersion;
            $cachedData['versions'] = $this->versionManager->getVersions();
            $cachedData['versionManager'] = $this->versionManager;
            $this->renderPage($cachedData);
            return;
        }

        // Parse the markdown
        $content = file_get_contents($mdFile);
        $parsedContent = $this->parser->parse($content);
        
        // Get metadata
        $metadata = $this->parser->extractMetadata($content);
        
        // Generate permalink
        $permalink = $this->generatePermalink($path);
        
        // Get prev/next pages
        $prevNext = $this->getPrevNextPages($docPath);
        
        // Prepare page data
        $pageData = [
            'content' => $parsedContent['html'],
            'title' => $metadata['title'] ?? $this->extractTitle($content),
            'description' => $metadata['description'] ?? '',
            'toc' => $parsedContent['toc'],
            'path' => $docPath,
            'fullPath' => $path,
            'permalink' => $permalink,
            'navigation' => $this->navigation->build(),
            'editUrl' => $this->generateEditUrl($mdFile),
            'prevPage' => $prevNext['prev'],
            'nextPage' => $prevNext['next'],
            'currentVersion' => $this->currentVersion,
            'versions' => $this->versionManager->getVersions(),
            'versionManager' => $this->versionManager
        ];
        
        // Cache the page data
        $this->cache->set($cacheKey, $pageData);
        
        // Render the page
        $this->renderPage($pageData);
    }

    private function findMarkdownFile($path, $basePath = null)
    {
        // Use provided base path or default docs path
        if ($basePath === null) {
            $basePath = $this->docsPath;
        }
        
        // Split path into parts
        $parts = explode('/', $path);
        
        // Try to find the file with numeric prefixes
        $currentPath = $basePath;
        
        foreach ($parts as $part) {
            if (empty($part)) continue;
            
            // Look for directory or file with numeric prefix
            $found = false;
            
            // Check if directory exists before scanning
            if (!is_dir($currentPath)) {
                return null;
            }
            
            $entries = scandir($currentPath);
            
            if ($entries === false) {
                return null;
            }
            
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') continue;
                
                // Remove numeric prefix for comparison
                $cleanName = preg_replace('/^\d+\./', '', $entry);
                $cleanName = preg_replace('/\.md$/', '', $cleanName);
                
                if ($cleanName === $part) {
                    $currentPath .= '/' . $entry;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                return null;
            }
        }
        
        // If currentPath is a directory, look for index.md or README.md
        if (is_dir($currentPath)) {
            $indexFiles = ['index.md', 'README.md'];
            foreach ($indexFiles as $indexFile) {
                $entries = scandir($currentPath);
                
                if ($entries === false) {
                    return null;
                }
                
                foreach ($entries as $entry) {
                    $cleanName = preg_replace('/^\d+\./', '', $entry);
                    if ($cleanName === $indexFile && file_exists($currentPath . '/' . $entry)) {
                        return $currentPath . '/' . $entry;
                    }
                }
            }
            return null;
        }
        
        // If it's a file, return it
        if (file_exists($currentPath)) {
            return $currentPath;
        }
        
        return null;
    }

    private function extractTitle($content)
    {
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }
        return 'Documentation';
    }

    private function generatePermalink($path)
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        return $protocol . '://' . $host . $basePath . '/' . $path;
    }

    private function generateEditUrl($filePath)
    {
        // Check if edit link feature is enabled and GitHub repo is configured
        if (empty($this->config['features']['edit_link']) || empty($this->config['github_repo'])) {
            return '#';
        }
        
        // Get relative path from docs directory
        $relativePath = str_replace($this->docsPath . '/', '', $filePath);
        
        // Build GitHub edit URL
        $githubRepo = rtrim($this->config['github_repo'], '/');
        return $githubRepo . '/blob/main/docs/' . $relativePath;
    }

    private function getPrevNextPages($currentPath)
    {
        // Get all pages in a flat list
        $allPages = $this->flattenNavigation($this->navigation->build());
        
        // Find current page index
        $currentIndex = -1;
        foreach ($allPages as $index => $page) {
            if ($page['path'] === $currentPath) {
                $currentIndex = $index;
                break;
            }
        }
        
        $prev = null;
        $next = null;
        
        if ($currentIndex > 0) {
            $prev = $allPages[$currentIndex - 1];
        }
        
        if ($currentIndex >= 0 && $currentIndex < count($allPages) - 1) {
            $next = $allPages[$currentIndex + 1];
        }
        
        return [
            'prev' => $prev,
            'next' => $next
        ];
    }

    private function flattenNavigation($navigation, &$result = [])
    {
        foreach ($navigation as $item) {
            if ($item['type'] === 'page') {
                $result[] = [
                    'title' => $item['title'],
                    'path' => $item['path']
                ];
            }
            
            if (isset($item['children']) && !empty($item['children'])) {
                $this->flattenNavigation($item['children'], $result);
            }
        }
        
        return $result;
    }

    private function renderPage($data)
    {
        extract($data);
        include __DIR__ . '/../templates/layout.php';
    }

    private function render404()
    {
        http_response_code(404);
        
        // Initialize navigation for current version if not already done
        if (!$this->navigation) {
            $versionDocsPath = $this->versionManager->getVersionDocsPath($this->currentVersion);
            $this->navigation = new NavigationBuilder($versionDocsPath, $this->cache);
        }
        
        $data = [
            'content' => '<h1>404 - Page Not Found</h1><p>The requested documentation page could not be found.</p>',
            'title' => '404 - Not Found',
            'description' => '',
            'toc' => [],
            'path' => '',
            'fullPath' => '',
            'permalink' => '',
            'navigation' => $this->navigation->build(),
            'editUrl' => '#',
            'prevPage' => null,
            'nextPage' => null,
            'currentVersion' => $this->currentVersion,
            'versions' => $this->versionManager->getVersions(),
            'versionManager' => $this->versionManager
        ];
        $this->renderPage($data);
    }

    public function search($query, $version = null)
    {
        // If versioning is disabled, search in the base docs directory
        if (!$this->versionManager->isVersioningEnabled()) {
            $searchIndexer = new SearchIndexer($this->docsPath, $this->cache, null);
            return $searchIndexer->search($query);
        }
        
        // If specific version requested, search only in that version
        if ($version !== null) {
            $versionDocsPath = $this->versionManager->getVersionDocsPath($version);
            $searchIndexer = new SearchIndexer($versionDocsPath, $this->cache, $version);
            return $searchIndexer->search($query);
        }
        
        // Search across all versions
        $allResults = [];
        $versions = $this->versionManager->getVersions();
        
        foreach ($versions as $versionInfo) {
            $versionSlug = $versionInfo['slug'];
            $versionDocsPath = $this->versionManager->getVersionDocsPath($versionSlug);
            $searchIndexer = new SearchIndexer($versionDocsPath, $this->cache, $versionSlug);
            $results = $searchIndexer->search($query);
            
            // Add version-prefixed paths to results
            foreach ($results as &$result) {
                $result['path'] = $versionSlug . '/' . $result['path'];
            }
            
            $allResults = array_merge($allResults, $results);
        }
        
        // Sort all results by score
        usort($allResults, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        // Return top 10 results
        return array_slice($allResults, 0, 10);
    }

    public function getNavigation()
    {
        return $this->navigation->build();
    }
    
    /**
     * Clear all caches
     */
    public function clearCache()
    {
        return $this->cache->clear();
    }
    
    /**
     * Get cache instance
     */
    public function getCache()
    {
        return $this->cache;
    }
    
    /**
     * Get version manager instance
     */
    public function getVersionManager()
    {
        return $this->versionManager;
    }
    
    /**
     * Set current version
     */
    public function setCurrentVersion($version)
    {
        $this->currentVersion = $version;
    }
}
