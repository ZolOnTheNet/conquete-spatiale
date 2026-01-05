<?php
/**
 * Script pour convertir les vues admin vers le nouveau layout
 */

$viewsDir = __DIR__ . '/../../resources/views/admin/';

$filesToConvert = [
    'univers.blade.php',
    'planetes.blade.php',
    'production.blade.php',
    'carte.blade.php',
    'backup.blade.php',
    'mines.blade.php',
    'univers-detail.blade.php',
    'planete-detail.blade.php',
    'carte-secteur.blade.php',
];

foreach ($filesToConvert as $file) {
    $filePath = $viewsDir . $file;

    if (!file_exists($filePath)) {
        echo "Skip: $file (not found)\n";
        continue;
    }

    $content = file_get_contents($filePath);

    // Skip if already using layouts.admin
    if (strpos($content, "@extends('layouts.admin')") !== false) {
        echo "Skip: $file (already converted)\n";
        continue;
    }

    // Extract title
    preg_match("/@section\('title',\s*'([^']+)'\)/", $content, $titleMatch);
    $title = $titleMatch[1] ?? 'Admin';

    // Extract admin title from h1
    preg_match('/<h1[^>]*>([^<]+)<\/h1>/', $content, $h1Match);
    $adminTitle = $h1Match[1] ?? 'ADMINISTRATION';
    $adminTitle = trim(str_replace('ADMINISTRATION', '', strip_tags($adminTitle)));
    if (empty($adminTitle)) {
        $adminTitle = 'ADMINISTRATION';
    }

    // Extract main content (between <main> tags)
    preg_match('/<main[^>]*>(.*?)<\/main>/s', $content, $mainMatch);
    $mainContent = $mainMatch[1] ?? '';

    if (empty($mainContent)) {
        echo "Skip: $file (no main content found)\n";
        continue;
    }

    // Clean up the content
    $mainContent = trim($mainContent);

    // Build new content
    $newContent = "@extends('layouts.admin')\n\n";
    $newContent .= "@section('title', '$title')\n\n";
    $newContent .= "@section('admin-title', '$adminTitle')\n\n";
    $newContent .= "@section('admin-content')\n";
    $newContent .= $mainContent . "\n";
    $newContent .= "@endsection\n";

    // Backup original
    $backupPath = $filePath . '.backup';
    copy($filePath, $backupPath);

    // Write new content
    file_put_contents($filePath, $newContent);

    echo "Converted: $file\n";
}

echo "\nDone! Backup files created with .backup extension\n";
