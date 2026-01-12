<?php
/**
 * Clear cache endpoint
 * This script is called via AJAX when a user leaves the page
 * It accepts a security token to prevent abuse
 */

// Very basic security check - verify request came with proper token
if (!isset($_POST['token']) || $_POST['token'] !== md5('shimla_air_lines_cache_token')) {
    // Return 403 Forbidden if token is invalid
    http_response_code(403);
    die('Unauthorized');
}

// Function to clear cache files
function clearCache() {
    $cacheDir = sys_get_temp_dir();
    $prefix = '';
    
    // If specific cache files are specified, clear only those
    if (isset($_POST['cache_files']) && !empty($_POST['cache_files'])) {
        $cacheFiles = json_decode($_POST['cache_files'], true);
        
        if (is_array($cacheFiles)) {
            foreach ($cacheFiles as $file) {
                $cacheFile = $cacheDir . '/' . md5($file) . '.cache';
                if (file_exists($cacheFile)) {
                    @unlink($cacheFile);
                }
            }
            return count($cacheFiles);
        }
    } 
    // Otherwise, clear all cache files that match our pattern
    else {
        $pattern = $cacheDir . '/*.cache';
        $files = glob($pattern);
        
        if ($files) {
            $count = 0;
            foreach ($files as $file) {
                // Only delete files older than 10 minutes to avoid issues with current sessions
                if (filemtime($file) < time() - 600) {
                    @unlink($file);
                    $count++;
                }
            }
            return $count;
        }
    }
    
    return 0;
}

// Clear the cache
$deleted = clearCache();

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => "Cache cleared successfully", 'deleted' => $deleted]);
