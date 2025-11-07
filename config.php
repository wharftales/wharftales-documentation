<?php

return [
    'site_name' => 'WharfDocs',
    'site_description' => 'Modern Php Documentation System with Version support',
    'theme' => 'default',
    'default_page' => 'getting-started/introduction',
    'github_repo' => 'https://github.com/wharftales/wharfdocs', // Set your GitHub repo URL for edit links
    'logo' => '/assets/logo.svg', // Logo for light mode (leave empty to hide)
    'logo_dark' => '/assets/logo-dark.svg', // Logo for dark mode (leave empty to use light logo)
    'copyright' => 'WharfDocs is an open source project.', // Optional: Custom copyright text (leave empty for default)
    'social_links' => [
        'github' => 'https://github.com/wharftales/wharfdocs',
    ],
    'social_links_sidebar' => [
        'github' => 'https://github.com/wharftales/wharfdocs',
    ],
    'social_links_footer' => [
        'github' => 'https://github.com/wharftales/wharfdocs',
    ],
    'features' => [
        'search' => true,
        'dark_mode' => true,
        'edit_link' => true,
        'toc' => true,
    ],
    'cache' => [
        'enabled' => true,
        'directory' => __DIR__ . '/cache',
    ],
    'versions' => [
        'enabled' => true, // Set to false to disable versioning
        'default' => 'v1.0', // Default version to show (null = latest)
        'show_selector' => true, // Show version selector in UI
        'label' => 'Version', // Label for version selector
    ]
];
