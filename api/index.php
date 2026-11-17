<?php

/**
 * Vercel Serverless Function Entry Point for Laravel
 */

// Prepare serverless writeable directories in /tmp for Laravel
$storagePath = '/tmp/storage';
$subDirs = [
    '/framework/views',
    '/framework/cache',
    '/framework/cache/data',
    '/framework/sessions',
    '/logs',
    '/app',
];

foreach ($subDirs as $subDir) {
    $dir = $storagePath . $subDir;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Forward request to Laravel public entrypoint
require __DIR__ . '/../public/index.php';
