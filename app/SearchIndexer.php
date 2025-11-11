<?php

namespace WharfDocs;

class SearchIndexer
{
    private $docsPath;
    private $index;
    private $cache;
    private $version;

    public function __construct($docsPath, $cache = null, $version = null)
    {
        $this->docsPath = $docsPath;
        $this->cache = $cache;
        $this->version = $version;
        $this->buildIndex();
    }

    private function buildIndex()
    {
        // Try to get cached index
        if ($this->cache && $this->cache->isEnabled()) {
            // Make cache key unique per version
            $cacheKey = 'search_index_' . ($this->version ?? 'default');
            $cachedIndex = $this->cache->get($cacheKey, [$this->docsPath]);
            
            if ($cachedIndex !== null) {
                // Check if any file in docs directory is newer than cache
                $newestTime = $this->cache->getNewestModificationTime([$this->docsPath]);
                $cacheTime = $this->cache->get($cacheKey . '_time');
                
                if ($cacheTime !== null && $newestTime <= $cacheTime) {
                    $this->index = $cachedIndex;
                    return;
                }
            }
        }
        
        // Build fresh index
        $this->index = [];
        
        if (!is_dir($this->docsPath)) {
            return;
        }
        
        $this->indexDirectory($this->docsPath);
        
        // Cache the index
        if ($this->cache && $this->cache->isEnabled()) {
            // Make cache key unique per version
            $cacheKey = 'search_index_' . ($this->version ?? 'default');
            $this->cache->set($cacheKey, $this->index);
            $this->cache->set($cacheKey . '_time', time());
        }
    }

    private function indexDirectory($dir, $basePath = '')
    {
        $entries = scandir($dir);
        
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            
            $fullPath = $dir . '/' . $entry;
            $relativePath = $basePath ? $basePath . '/' . $entry : $entry;
            
            if (is_dir($fullPath)) {
                $this->indexDirectory($fullPath, $relativePath);
            } elseif (pathinfo($entry, PATHINFO_EXTENSION) === 'md') {
                $this->indexFile($fullPath, $relativePath);
            }
        }
    }

    private function indexFile($filePath, $relativePath)
    {
        $content = file_get_contents($filePath);
        
        // Remove frontmatter
        $content = preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $content);
        
        // Extract title
        $title = '';
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            $title = trim($matches[1]);
        }
        
        // Extract headings
        $headings = [];
        if (preg_match_all('/^#{2,4}\s+(.+)$/m', $content, $matches)) {
            $headings = $matches[1];
        }
        
        // Remove markdown syntax for plain text
        $plainText = strip_tags($this->markdownToPlainText($content));
        
        // Create document path
        $docPath = preg_replace('/\.md$/', '', $relativePath);
        
        // Remove numeric prefixes from all path segments
        $parts = explode('/', $docPath);
        $cleanParts = array_map(function($part) {
            return preg_replace('/^\d+\./', '', $part);
        }, $parts);
        $docPath = implode('/', $cleanParts);
        
        // Remove index or README from path
        $docPath = preg_replace('/(\/|^)(index|README)$/', '', $docPath);
        
        $this->index[] = [
            'path' => $docPath,
            'title' => $title,
            'headings' => $headings,
            'content' => $plainText,
            'excerpt' => $this->createExcerpt($plainText),
            'version' => $this->version
        ];
    }

    private function markdownToPlainText($markdown)
    {
        // Remove code blocks
        $text = preg_replace('/```[\s\S]*?```/', '', $markdown);
        
        // Remove inline code
        $text = preg_replace('/`[^`]+`/', '', $text);
        
        // Remove links but keep text
        $text = preg_replace('/\[([^\]]+)\]\([^\)]+\)/', '$1', $text);
        
        // Remove images
        $text = preg_replace('/!\[([^\]]*)\]\([^\)]+\)/', '', $text);
        
        // Remove headings markers
        $text = preg_replace('/^#{1,6}\s+/m', '', $text);
        
        // Remove bold/italic
        $text = preg_replace('/[*_]{1,2}([^*_]+)[*_]{1,2}/', '$1', $text);
        
        return $text;
    }

    private function createExcerpt($text, $length = 200)
    {
        $text = trim($text);
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . '...';
    }

    public function search($query)
    {
        if (empty($query)) {
            return [];
        }
        
        $query = strtolower(trim($query));
        $results = [];
        
        foreach ($this->index as $doc) {
            $score = 0;
            $titleLower = strtolower($doc['title']);
            $contentLower = strtolower($doc['content']);
            
            // Title match (highest priority)
            if (stripos($titleLower, $query) !== false) {
                $score += 100;
            }
            
            // Heading match
            foreach ($doc['headings'] as $heading) {
                if (stripos(strtolower($heading), $query) !== false) {
                    $score += 50;
                }
            }
            
            // Content match
            $contentMatches = substr_count($contentLower, $query);
            $score += $contentMatches * 10;
            
            if ($score > 0) {
                $results[] = [
                    'path' => $doc['path'],
                    'title' => $doc['title'],
                    'excerpt' => $this->highlightExcerpt($doc['excerpt'], $query),
                    'score' => $score,
                    'version' => $doc['version']
                ];
            }
        }
        
        // Sort by score
        usort($results, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        // Return top 10 results
        return array_slice($results, 0, 10);
    }

    private function highlightExcerpt($excerpt, $query)
    {
        return preg_replace(
            '/(' . preg_quote($query, '/') . ')/i',
            '<mark>$1</mark>',
            $excerpt
        );
    }
}
