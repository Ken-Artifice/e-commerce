<?php
// Base path configuration
// Try to detect project root (the folder that contains `index.php`) and compute
// a stable BASE_PATH that does not change when visiting files inside subfolders
if (!defined('BASE_PATH')) {
    // Filesystem document root (normalized)
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');

    // Start from this config directory and walk up to find project root (index.php)
    $startDir = str_replace('\\', '/', realpath(__DIR__ . '/..')) ?: str_replace('\\', '/', __DIR__ . '/..');
    $current = $startDir;
    $found = false;
    $maxLevels = 10;
    for ($i = 0; $i < $maxLevels; $i++) {
        if (file_exists($current . '/index.php')) {
            $found = true;
            break;
        }
        $parent = dirname($current);
        if ($parent === $current) break;
        $current = $parent;
    }

    if ($found) {
        // Convert filesystem project path to a URL path by removing document root
        if (!empty($docRoot) && strpos($current, $docRoot) === 0) {
            $basePath = substr($current, strlen($docRoot));
            $basePath = str_replace('\\', '/', $basePath);
            $basePath = rtrim($basePath, '/');
            if ($basePath === '') $basePath = '';
        } else {
            // Fallback: compute from SCRIPT_NAME but strip common subfolders
            $script_name = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
            $script_dir = dirname($script_name);
            $script_dir = str_replace('\\', '/', $script_dir);
            $script_dir = rtrim($script_dir, '/');
            $basePath = preg_replace('#/(admin|seller)$#', '', $script_dir);
            if ($basePath === '/' || $basePath === '.' ) $basePath = '';
        }
    } else {
        // Last resort: use SCRIPT_NAME detection and strip admin/seller suffix
        $script_name = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $script_dir = dirname($script_name);
        $script_dir = str_replace('\\', '/', $script_dir);
        $script_dir = rtrim($script_dir, '/');
        $basePath = preg_replace('#/(admin|seller)$#', '', $script_dir);
        if ($basePath === '/' || $basePath === '.' ) $basePath = '';
    }

    define('BASE_PATH', $basePath);
}

// Helper function to generate URLs
function url($path = '') {
    // Remove leading slash from path if it exists
    $path = ltrim($path, '/');
    
    if (empty($path)) {
        return BASE_PATH . '/';
    }
    
    return BASE_PATH . '/' . $path;
}
?>

