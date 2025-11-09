<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - <?php echo htmlspecialchars($GLOBALS['config']['site_name'] ?? 'Documentation'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($description); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($permalink); ?>">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    
    <!-- Highlight.js for code syntax highlighting -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    
    <!-- Custom styles -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <style>
        :root {
            --color-primary: #949494;
            --color-primary-dark: #5f5f5f;
            --color-bg: #ffffff;
            --color-bg-secondary: #ffffff;
            --color-text: #1f2937;
            --color-text-secondary: #6b7280;
            --color-border: #e5e7eb;
        }
        
        .dark {
            --color-bg: #212121;
            --color-bg-secondary: #212121;
            --color-text: #f1f5f9;
            --color-text-secondary: #9a9a9aff;
            --color-border: #535353;
        }
        
        body {
            background-color: var(--color-bg);
            color: var(--color-text);
            overflow-x: hidden;
        }
        
        .sidebar {
            background-color: var(--color-bg-secondary);
            border-right: 1px solid var(--color-border);
        }
        
        .content-wrapper {
            max-width: 1280px;
        }
        
        .prose {
            max-width: 65ch;
            width: 100%;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
        
        .prose h1 { font-size: 2.25rem; font-weight: 800; margin-top: 0; margin-bottom: 1rem; }
        .prose h2 { font-size: 1.875rem; font-weight: 700; margin-top: 2rem; margin-bottom: 1rem; }
        .prose h3 { font-size: 1.5rem; font-weight: 600; margin-top: 1.5rem; margin-bottom: 0.75rem; }
        .prose h4 { font-size: 1.25rem; font-weight: 600; margin-top: 1.25rem; margin-bottom: 0.5rem; }
        .prose p { margin-bottom: 1rem; line-height: 1.75; }
        .prose ul, .prose ol { margin-bottom: 1rem; padding-left: 1.5rem; }
        .prose li { margin-bottom: 0.5rem; }
        .prose code { background-color: var(--color-bg-secondary); padding: 0.2rem 0.4rem; border-radius: 0.25rem; font-size: 0.875rem; }
        .prose pre { background-color: #1e293b; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; margin-bottom: 1rem; max-width: 100%; }
        .prose pre code { background-color: transparent; padding: 0; white-space: pre; }
        .prose a { color: var(--color-primary); text-decoration: none; }
        .prose a:hover { text-decoration: underline; }
        .prose blockquote { border-left: 4px solid var(--color-primary); padding-left: 1rem; margin: 1rem 0; font-style: italic; }
        .prose table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; display: block; overflow-x: auto; }
        .prose th, .prose td { border: 1px solid var(--color-border); padding: 0.5rem; text-align: left; white-space: nowrap; }
        .prose th { background-color: var(--color-bg-secondary); font-weight: 600; }
        
        .heading-anchor { 
            opacity: 0; 
            margin-left: -1.5rem; 
            padding-right: 0.5rem;
            color: var(--color-primary);
        }
        
        h2:hover .heading-anchor,
        h3:hover .heading-anchor,
        h4:hover .heading-anchor { 
            opacity: 1; 
        }
        
        .nav-item { 
            padding: 0.5rem 1rem; 
            border-radius: 0.375rem; 
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .nav-item:hover { 
            background-color: var(--color-bg); 
        }
        
        .nav-item.active { 
            background-color: var(--color-text-secondary); 
            color: white; 
        }
        
        .search-result {
            padding: 1rem;
            border-bottom: 1px solid var(--color-border);
            cursor: pointer;
        }
        
        .search-result:hover {
            background-color: var(--color-bg-secondary);
        }
        
        .search-result mark {
            background-color: #fef08a;
            padding: 0.1rem 0.2rem;
        }
        
        /* Mobile sidebar wider */
        @media (max-width: 1023px) {
            #sidebar {
                width: 80%;
                max-width: 320px;
                left: 0;
                top: 73px; /* Below header and mobile search */
                height: calc(100vh - 73px);
            }
            
            .prose {
                max-width: 100%;
            }
            
            .prose pre {
                font-size: 0.875rem;
            }
            
            .prose table {
                font-size: 0.875rem;
            }
        }
    </style>
</head>
<body class="antialiased">
    <!-- Header -->
    <header class="sticky top-0 z-50 border-b" style="background-color: var(--color-bg); border-color: var(--color-border);">
        <div class="container-fluid mx-auto px-2 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <button id="sidebar-toggle" class="lg:hidden p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <a href="/" class="flex items-center space-x-3 text-xl font-bold hover:text-zinc-600 transition-colors">
                    <?php if (!empty($GLOBALS['config']['logo'])): ?>
                        <div class="h-8 w-8 flex-shrink-0">
                            <!-- Light mode logo -->
                            <img src="<?php echo htmlspecialchars($GLOBALS['config']['logo']); ?>" 
                                 alt="<?php echo htmlspecialchars($GLOBALS['config']['site_name'] ?? 'Documentation'); ?>" 
                                 class="h-8 w-8 block dark:hidden">
                            <!-- Dark mode logo -->
                            <img src="<?php echo htmlspecialchars($GLOBALS['config']['logo_dark'] ?? $GLOBALS['config']['logo']); ?>" 
                                 alt="<?php echo htmlspecialchars($GLOBALS['config']['site_name'] ?? 'Documentation'); ?>" 
                                 class="h-8 w-8 hidden dark:block">
                        </div>
                    <?php endif; ?>
                    <span><?php echo htmlspecialchars($GLOBALS['config']['site_name'] ?? 'Documentation'); ?></span>
                </a>
                
                <!-- Version Selector -->
                <?php if (!empty($GLOBALS['config']['versions']['enabled']) && !empty($GLOBALS['config']['versions']['show_selector']) && !empty($versions)): ?>
                <div class="relative ml-4">
                    <button id="version-selector" class="px-3 py-1.5 text-sm border rounded-lg flex items-center space-x-2 hover:bg-gray-50 dark:hover:bg-gray-800" style="border-color: var(--color-border);">
                        <span><?php echo htmlspecialchars($currentVersion ?? 'Select Version'); ?></span>
                        <?php if (isset($versionManager)): ?>
                            <span class="text-xs" style="color: var(--color-text-secondary);"><?php echo $versionManager->getVersionBadge($currentVersion); ?></span>
                        <?php endif; ?>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="version-dropdown" class="absolute top-full mt-2 left-0 w-48 rounded-lg shadow-lg hidden z-50" style="background-color: var(--color-bg); border: 1px solid var(--color-border);">
                        <?php foreach ($versions as $version): ?>
                            <?php 
                                $isActive = ($version['slug'] === $currentVersion);
                                $versionPath = isset($versionManager) ? $versionManager->addVersionToPath($path ?? '', $version['slug']) : $version['slug'];
                            ?>
                            <a href="/<?php echo htmlspecialchars($versionPath); ?>" 
                               class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800 <?php echo $isActive ? 'font-semibold' : ''; ?>">
                                <?php echo htmlspecialchars($version['label']); ?>
                                <?php if (!empty($version['status']) && $version['status'] !== 'stable'): ?>
                                    <span class="text-xs ml-1" style="color: var(--color-text-secondary);">(<?php echo htmlspecialchars(ucfirst($version['status'])); ?>)</span>
                                <?php endif; ?>
                                <?php if ($version['slug'] === ($versions[0]['slug'] ?? '')): ?>
                                    <span class="text-xs ml-1" style="color: var(--color-primary);">Latest</span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="flex items-center space-x-2">
                <!-- Search (Desktop only) -->
                <div class="relative hidden md:block">
                    <input 
                        type="text" 
                        id="search-input" 
                        placeholder="Search documentation..." 
                        class="px-4 py-2 rounded-lg border w-64"
                        style="background-color: var(--color-bg-secondary); border-color: var(--color-border);"
                    >
                    <div id="search-results" class="absolute top-full mt-2 w-full rounded-lg shadow-lg hidden" style="background-color: var(--color-bg); border: 1px solid var(--color-border); max-height: 400px; overflow-y: auto;"></div>
                </div>

                <!-- Dark mode toggle -->
                <button id="theme-toggle" class="p-2">
                    <svg class="w-6 h-6 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                    <svg class="w-6 h-6 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </button>
                
                <!-- Social Links (Desktop only) -->
                <?php if (!empty($GLOBALS['config']['social_links'])): ?>
                    <div class="separator hidden md:block">|</div>
                    <?php foreach ($GLOBALS['config']['social_links'] as $platform => $url): ?>
                        <?php if (!empty($url) && $url !== '#'): ?>
                            <a href="<?php echo htmlspecialchars($url); ?>" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               class="hidden md:block p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
                               title="<?php echo ucfirst($platform); ?>">
                                <?php if ($platform === 'github'): ?>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"></path>
                                    </svg>
                                <?php elseif ($platform === 'twitter'): ?>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path>
                                    </svg>
                                <?php elseif ($platform === 'discord'): ?>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20.317 4.37a19.791 19.791 0 00-4.885-1.515.074.074 0 00-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 00-5.487 0 12.64 12.64 0 00-.617-1.25.077.077 0 00-.079-.037A19.736 19.736 0 003.677 4.37a.07.07 0 00-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 00.031.057 19.9 19.9 0 005.993 3.03.078.078 0 00.084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 00-.041-.106 13.107 13.107 0 01-1.872-.892.077.077 0 01-.008-.128 10.2 10.2 0 00.372-.292.074.074 0 01.077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 01.078.01c.12.098.246.198.373.292a.077.077 0 01-.006.127 12.299 12.299 0 01-1.873.892.077.077 0 00-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 00.084.028 19.839 19.839 0 006.002-3.03.077.077 0 00.032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 00-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"></path>
                                    </svg>
                                <?php else: ?>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                    </svg>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                
            </div>
        </div>
    </header>

    <!-- Mobile Search Bar (below header) -->
    <div class="md:hidden border-b px-4 py-3" style="background-color: var(--color-bg); border-color: var(--color-border);">
        <div class="relative">
            <input 
                type="text" 
                id="search-input-mobile" 
                placeholder="Search documentation..." 
                class="w-full px-4 py-2 rounded-lg border"
                style="background-color: var(--color-bg-secondary); border-color: var(--color-border);"
            >
            <div id="search-results-mobile" class="absolute top-full mt-2 w-full rounded-lg shadow-lg hidden" style="background-color: var(--color-bg); border: 1px solid var(--color-border); max-height: 400px; overflow-y: auto; z-index: 40;"></div>
        </div>
    </div>

    <!-- Mobile sidebar backdrop -->
    <div id="sidebar-backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden"></div>

    <div class="flex">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar w-64 lg:w-64 h-screen overflow-y-auto hidden lg:block fixed lg:sticky lg:top-16 top-0 lg:relative z-40">
            <nav class="p-4">
                <?php echo renderNavigation($navigation, $path); ?>
                
                <!-- Social Links in Sidebar (Mobile) -->
                <?php if (!empty($GLOBALS['config']['social_links_sidebar'])): ?>
                <div class="mt-8 pt-4 border-t" style="border-color: var(--color-border);">
                    <h4 class="text-xs font-semibold uppercase mb-3" style="color: var(--color-text-secondary);">Connect</h4>
                    <div class="flex items-center space-x-3">
                        <?php foreach ($GLOBALS['config']['social_links_sidebar'] as $platform => $url): ?>
                            <?php if (!empty($url) && $url !== '#'): ?>
                                <a href="<?php echo htmlspecialchars($url); ?>" 
                                   target="_blank" 
                                   rel="noopener noreferrer"
                                   class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                   title="<?php echo ucfirst($platform); ?>">
                                    <?php if ($platform === 'github'): ?>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"></path>
                                        </svg>
                                    <?php elseif ($platform === 'twitter'): ?>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path>
                                        </svg>
                                    <?php elseif ($platform === 'discord'): ?>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M20.317 4.37a19.791 19.791 0 00-4.885-1.515.074.074 0 00-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 00-5.487 0 12.64 12.64 0 00-.617-1.25.077.077 0 00-.079-.037A19.736 19.736 0 003.677 4.37a.07.07 0 00-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 00.031.057 19.9 19.9 0 005.993 3.03.078.078 0 00.084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 00-.041-.106 13.107 13.107 0 01-1.872-.892.077.077 0 01-.008-.128 10.2 10.2 0 00.372-.292.074.074 0 01.077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 01.078.01c.12.098.246.198.373.292a.077.077 0 01-.006.127 12.299 12.299 0 01-1.873.892.077.077 0 00-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 00.084.028 19.839 19.839 0 006.002-3.03.077.077 0 00.032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 00-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"></path>
                                        </svg>
                                    <?php else: ?>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                        </svg>
                                    <?php endif; ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 px-4 py-8 lg:px-8 min-w-0">
            <div class="content-wrapper mx-auto max-w-full">
                <article class="prose">
                    <?php echo $content; ?>
                </article>
                
                <!-- Previous/Next Navigation -->
                <?php if (!empty($prevPage) || !empty($nextPage)): ?>
                <div class="mt-12 pt-8 border-t" style="border-color: var(--color-border);">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php if (!empty($prevPage)): ?>
                        <?php 
                            $prevPath = $prevPage['path'];
                            if (isset($versionManager) && $versionManager->isVersioningEnabled() && isset($currentVersion)) {
                                $prevPath = $versionManager->addVersionToPath($prevPage['path'], $currentVersion);
                            }
                        ?>
                        <a href="/<?php echo htmlspecialchars($prevPath); ?>" 
                           class="group p-4 rounded-lg border transition-colors hover:border-blue-500"
                           style="border-color: var(--color-border);">
                            <div class="text-sm font-semibold mb-1" style="color: var(--color-text-secondary);">
                                ← Previous
                            </div>
                            <div class="font-medium group-hover:text-zinc-600">
                                <?php echo htmlspecialchars($prevPage['title']); ?>
                            </div>
                        </a>
                        <?php else: ?>
                        <div></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($nextPage)): ?>
                        <?php 
                            $nextPath = $nextPage['path'];
                            if (isset($versionManager) && $versionManager->isVersioningEnabled() && isset($currentVersion)) {
                                $nextPath = $versionManager->addVersionToPath($nextPage['path'], $currentVersion);
                            }
                        ?>
                        <a href="/<?php echo htmlspecialchars($nextPath); ?>" 
                           class="group p-4 rounded-lg border transition-colors hover:border-blue-500 text-right"
                           style="border-color: var(--color-border);">
                            <div class="text-sm font-semibold mb-1" style="color: var(--color-text-secondary);">
                                Next →
                            </div>
                            <div class="font-medium group-hover:text-zinc-600">
                                <?php echo htmlspecialchars($nextPage['title']); ?>
                            </div>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($editUrl) && $editUrl !== '#'): ?>
                <div class="mt-8 pt-8 border-t" style="border-color: var(--color-border);">
                    <a href="<?php echo htmlspecialchars($editUrl); ?>" class="text-sm" style="color: var(--color-text-secondary);">
                        Edit this page on GitHub
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </main>

        <!-- Table of Contents -->
        <?php if (!empty($toc)): ?>
        <aside class="hidden xl:block w-64 h-screen sticky top-20 overflow-y-auto p-4">
            <h3 class="font-semibold mb-4 text-sm uppercase" style="color: var(--color-text-secondary);">On This Page</h3>
            <nav class="space-y-2">
                <?php foreach ($toc as $item): ?>
                    <a href="#<?php echo htmlspecialchars($item['slug']); ?>" 
                       class="block text-sm hover:text-zinc-600"
                       style="padding-left: <?php echo ($item['level'] - 2) * 0.75; ?>rem; color: var(--color-text-secondary);">
                        <?php echo htmlspecialchars($item['title']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="border-t" style="background-color: var(--color-bg); border-color: var(--color-border);">
        <div class="container-fluid mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <!-- Copyright -->
                <div class="text-sm" style="color: var(--color-text-secondary);">
                    <?php if (!empty($GLOBALS['config']['copyright'])): ?>
                        <?php echo htmlspecialchars($GLOBALS['config']['copyright']); ?>
                    <?php else: ?>
                        © <?php echo date('Y'); ?> <?php echo htmlspecialchars($GLOBALS['config']['site_name'] ?? 'Documentation'); ?>. All rights reserved.
                    <?php endif; ?>
                </div>
                
                <!-- Social Icons + Theme Switcher -->
                <div class="flex items-center space-x-3">
                    <?php if (!empty($GLOBALS['config']['social_links_footer'])): ?>
                        <?php foreach ($GLOBALS['config']['social_links_footer'] as $platform => $url): ?>
                            <?php if (!empty($url) && $url !== '#'): ?>
                                <a href="<?php echo htmlspecialchars($url); ?>" 
                                   target="_blank" 
                                   rel="noopener noreferrer"
                                   class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                   title="<?php echo ucfirst($platform); ?>">
                                    <?php if ($platform === 'github'): ?>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"></path>
                                        </svg>
                                    <?php elseif ($platform === 'twitter'): ?>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path>
                                        </svg>
                                    <?php elseif ($platform === 'discord'): ?>
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M20.317 4.37a19.791 19.791 0 00-4.885-1.515.074.074 0 00-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 00-5.487 0 12.64 12.64 0 00-.617-1.25.077.077 0 00-.079-.037A19.736 19.736 0 003.677 4.37a.07.07 0 00-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 00.031.057 19.9 19.9 0 005.993 3.03.078.078 0 00.084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 00-.041-.106 13.107 13.107 0 01-1.872-.892.077.077 0 01-.008-.128 10.2 10.2 0 00.372-.292.074.074 0 01.077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 01.078.01c.12.098.246.198.373.292a.077.077 0 01-.006.127 12.299 12.299 0 01-1.873.892.077.077 0 00-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 00.084.028 19.839 19.839 0 006.002-3.03.077.077 0 00.032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 00-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"></path>
                                        </svg>
                                    <?php else: ?>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                        </svg>
                                    <?php endif; ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        
                        <!-- Separator -->
                        <div class="h-6 w-px" style="background-color: var(--color-border);"></div>
                    <?php endif; ?>
                    
                    <!-- Theme Toggle -->
                    <button id="theme-toggle-footer" class="p-2" title="Toggle theme">
                        <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                        <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Syntax highlighting
        document.addEventListener('DOMContentLoaded', (event) => {
            document.querySelectorAll('pre code').forEach((block) => {
                hljs.highlightElement(block);
            });
        });

        // Dark mode toggle
        const themeToggle = document.getElementById('theme-toggle');
        const themeToggleFooter = document.getElementById('theme-toggle-footer');
        const html = document.documentElement;
        
        // Check for saved theme preference or default to light mode
        const currentTheme = localStorage.getItem('theme') || 'light';
        if (currentTheme === 'dark') {
            html.classList.add('dark');
        }

        function toggleTheme() {
            html.classList.toggle('dark');
            const theme = html.classList.contains('dark') ? 'dark' : 'light';
            localStorage.setItem('theme', theme);
        }

        themeToggle.addEventListener('click', toggleTheme);
        themeToggleFooter.addEventListener('click', toggleTheme);

        // Search functionality
        function setupSearch(inputId, resultsId) {
            const searchInput = document.getElementById(inputId);
            const searchResults = document.getElementById(resultsId);
            
            if (!searchInput || !searchResults) return;
            
            let searchTimeout;

            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                const query = e.target.value.trim();
                
                if (query.length < 2) {
                    searchResults.classList.add('hidden');
                    return;
                }

                searchTimeout = setTimeout(() => {
                    fetch(`/api/search?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(results => {
                            if (results.length === 0) {
                                searchResults.innerHTML = '<div class="p-4 text-sm" style="color: var(--color-text-secondary);">No results found</div>';
                            } else {
                                searchResults.innerHTML = results.map(result => `
                                    <div class="search-result" onclick="window.location.href='/${result.path}'">
                                        <div class="font-semibold">${result.title}</div>
                                        <div class="text-sm mt-1" style="color: var(--color-text-secondary);">${result.excerpt}</div>
                                    </div>
                                `).join('');
                            }
                            searchResults.classList.remove('hidden');
                        });
                }, 300);
            });

            // Close search results when clicking outside
            document.addEventListener('click', (e) => {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('hidden');
                }
            });
        }
        
        // Setup both desktop and mobile search
        setupSearch('search-input', 'search-results');
        setupSearch('search-input-mobile', 'search-results-mobile');

        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarBackdrop = document.getElementById('sidebar-backdrop');
        
        function toggleSidebar() {
            sidebar.classList.toggle('hidden');
            sidebarBackdrop.classList.toggle('hidden');
        }
        
        sidebarToggle?.addEventListener('click', toggleSidebar);
        sidebarBackdrop?.addEventListener('click', toggleSidebar);

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        // Version selector dropdown
        const versionSelector = document.getElementById('version-selector');
        const versionDropdown = document.getElementById('version-dropdown');
        
        if (versionSelector && versionDropdown) {
            versionSelector.addEventListener('click', (e) => {
                e.stopPropagation();
                versionDropdown.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!versionSelector.contains(e.target) && !versionDropdown.contains(e.target)) {
                    versionDropdown.classList.add('hidden');
                }
            });
        }
    </script>

    <script data-site-id="site_f15c8f40bdf9b977011cdcf89fc30ccc" src="https://analytics.wharftales.org/track.js"></script>
    
</body>
</html>

<?php
function renderNavigation($items, $currentPath, $level = 0) {
    $html = '';
    
    // Get current version for building links
    $currentVersion = $GLOBALS['currentVersion'] ?? null;
    $versionManager = $GLOBALS['versionManager'] ?? null;
    
    foreach ($items as $item) {
        if ($item['type'] === 'section') {
            $html .= '<div class="mb-4">';
            $html .= '<h4 class="font-semibold text-sm mb-2 uppercase" style="color: var(--color-text-secondary);">' . htmlspecialchars($item['title']) . '</h4>';
            if (!empty($item['children'])) {
                $html .= '<div class="space-y-1">';
                $html .= renderNavigation($item['children'], $currentPath, $level + 1);
                $html .= '</div>';
            }
            $html .= '</div>';
        } else {
            $isActive = $item['path'] === $currentPath;
            $activeClass = $isActive ? ' active' : '';
            
            // Add version to path if versioning is enabled
            $linkPath = $item['path'];
            if ($versionManager && $versionManager->isVersioningEnabled() && $currentVersion) {
                $linkPath = $versionManager->addVersionToPath($item['path'], $currentVersion);
            }
            
            $html .= '<a href="/' . htmlspecialchars($linkPath) . '" class="nav-item block' . $activeClass . '">';
            $html .= htmlspecialchars($item['title']);
            $html .= '</a>';
        }
    }
    
    return $html;
}

// Make version data available globally for the function
if (isset($currentVersion)) {
    $GLOBALS['currentVersion'] = $currentVersion;
}
if (isset($versionManager)) {
    $GLOBALS['versionManager'] = $versionManager;
}
?>
