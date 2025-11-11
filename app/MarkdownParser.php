<?php

namespace WharfDocs;

use Parsedown;
use ParsedownExtra;

class MarkdownParser
{
    private $parsedown;

    public function __construct()
    {
        $this->parsedown = new ParsedownExtra();
        $this->parsedown->setSafeMode(false);
    }

    public function parse($markdown)
    {
        // Remove frontmatter if exists
        $markdown = $this->removeFrontmatter($markdown);
        
        // Parse markdown to HTML
        $html = $this->parsedown->text($markdown);
        
        // Generate table of contents
        $toc = $this->generateTOC($markdown);
        
        // Add anchor links to headings
        $html = $this->addHeadingAnchors($html);
        
        return [
            'html' => $html,
            'toc' => $toc
        ];
    }

    public function extractMetadata($markdown)
    {
        $metadata = [];
        
        if (preg_match('/^---\s*\n(.*?)\n---\s*\n/s', $markdown, $matches)) {
            $frontmatter = $matches[1];
            $lines = explode("\n", $frontmatter);
            
            foreach ($lines as $line) {
                if (strpos($line, ':') !== false) {
                    list($key, $value) = explode(':', $line, 2);
                    $metadata[trim($key)] = trim($value);
                }
            }
        }
        
        return $metadata;
    }

    private function removeFrontmatter($markdown)
    {
        return preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $markdown);
    }

    private function generateTOC($markdown)
    {
        $toc = [];
        $lines = explode("\n", $markdown);
        
        foreach ($lines as $line) {
            if (preg_match('/^(#{2,4})\s+(.+)$/', $line, $matches)) {
                $level = strlen($matches[1]);
                $title = trim($matches[2]);
                $slug = $this->slugify($title);
                
                $toc[] = [
                    'level' => $level,
                    'title' => $title,
                    'slug' => $slug
                ];
            }
        }
        
        return $toc;
    }

    private function addHeadingAnchors($html)
    {
        return preg_replace_callback(
            '/<h([2-4])>(.*?)<\/h\1>/',
            function ($matches) {
                $level = $matches[1];
                $title = $matches[2];
                $slug = $this->slugify(strip_tags($title));
                
                return sprintf(
                    '<h%d id="%s"><a href="#%s" class="heading-anchor">#</a>%s</h%d>',
                    $level,
                    $slug,
                    $slug,
                    $title,
                    $level
                );
            },
            $html
        );
    }

    private function slugify($text)
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        
        return empty($text) ? 'n-a' : $text;
    }
}
