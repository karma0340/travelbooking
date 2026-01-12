<?php
/**
 * Analytics functions for tracking page views and user activity
 */

// Define analytics directory and files
define('ANALYTICS_DIR', __DIR__ . '/../analytics/');
define('PAGEVIEWS_FILE', ANALYTICS_DIR . 'pageviews.json');
define('VISITORS_FILE', ANALYTICS_DIR . 'visitors.json');

/**
 * Track a page view
 * 
 * @param string $page The page being viewed
 */
function trackPageView($page = null) {
    // Skip tracking in admin area
    if (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/') !== false) {
        return;
    }
    
    // Create analytics directory if it doesn't exist
    if (!file_exists(ANALYTICS_DIR)) {
        mkdir(ANALYTICS_DIR, 0777, true);
    }
    
    // Default to current page if not provided
    if ($page === null) {
        $page = basename($_SERVER['SCRIPT_NAME'] ?? 'unknown');
    }
    
    // Load existing pageview data
    $pageviews = [];
    if (file_exists(PAGEVIEWS_FILE)) {
        $pageviews = json_decode(file_get_contents(PAGEVIEWS_FILE), true) ?: [];
    }
    
    $today = date('Y-m-d');
    
    // Initialize or increment page view count
    if (!isset($pageviews[$page])) {
        $pageviews[$page] = [];
    }
    
    if (!isset($pageviews[$page][$today])) {
        $pageviews[$page][$today] = 1;
    } else {
        $pageviews[$page][$today]++;
    }
    
    // Save page views data
    file_put_contents(PAGEVIEWS_FILE, json_encode($pageviews, JSON_PRETTY_PRINT));
    
    // Track visitor
    trackVisitor($page);
}

/**
 * Track visitor information
 * 
 * @param string $page The page being visited
 */
function trackVisitor($page) {
    // Load existing visitor data
    $visitors = [];
    if (file_exists(VISITORS_FILE)) {
        $visitors = json_decode(file_get_contents(VISITORS_FILE), true) ?: [];
    }
    
    // Create visitor entry
    $visitor = [
        'timestamp' => date('Y-m-d H:i:s'),
        'page' => $page,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
    ];
    
    // Generate a visitor ID if not already set
    if (!isset($_COOKIE['visitor_id'])) {
        $visitorId = md5($visitor['ip'] . $visitor['user_agent'] . time());
        setcookie('visitor_id', $visitorId, time() + 86400 * 365, '/'); // 1 year
        $visitor['visitor_id'] = $visitorId;
    } else {
        $visitor['visitor_id'] = $_COOKIE['visitor_id'];
    }
    
    // Add to visitors array
    $visitors[] = $visitor;
    
    // Limit to 10,000 entries to prevent file size issues
    if (count($visitors) > 10000) {
        $visitors = array_slice($visitors, -10000);
    }
    
    // Save visitor data
    file_put_contents(VISITORS_FILE, json_encode($visitors, JSON_PRETTY_PRINT));
}

/**
 * Get page view statistics
 * 
 * @param int $days Number of days to include in statistics
 * @return array Page view statistics
 */
function getPageViewStats($days = 30) {
    if (!file_exists(PAGEVIEWS_FILE)) {
        return [];
    }
    
    $pageviews = json_decode(file_get_contents(PAGEVIEWS_FILE), true) ?: [];
    $stats = [];
    
    // Get date range
    $endDate = date('Y-m-d');
    $startDate = date('Y-m-d', strtotime("-$days days"));
    
    $currentDate = $startDate;
    while (strtotime($currentDate) <= strtotime($endDate)) {
        $dailyTotal = 0;
        
        foreach ($pageviews as $page => $dates) {
            if (isset($dates[$currentDate])) {
                $dailyTotal += $dates[$currentDate];
            }
        }
        
        $stats[$currentDate] = $dailyTotal;
        $currentDate = date('Y-m-d', strtotime("$currentDate +1 day"));
    }
    
    return $stats;
}

/**
 * Get statistics for a specific page
 * 
 * @param string $page The page to get statistics for
 * @param int $days Number of days to include in statistics
 * @return array Page statistics
 */
function getPageStats($page, $days = 30) {
    if (!file_exists(PAGEVIEWS_FILE)) {
        return [];
    }
    
    $pageviews = json_decode(file_get_contents(PAGEVIEWS_FILE), true) ?: [];
    
    if (!isset($pageviews[$page])) {
        return [];
    }
    
    $stats = [];
    $pageDates = $pageviews[$page];
    
    // Get date range
    $endDate = date('Y-m-d');
    $startDate = date('Y-m-d', strtotime("-$days days"));
    
    $currentDate = $startDate;
    while (strtotime($currentDate) <= strtotime($endDate)) {
        $stats[$currentDate] = $pageDates[$currentDate] ?? 0;
        $currentDate = date('Y-m-d', strtotime("$currentDate +1 day"));
    }
    
    return $stats;
}

/**
 * Get total page views
 * 
 * @return int Total page views
 */
function getTotalPageViews() {
    if (!file_exists(PAGEVIEWS_FILE)) {
        return 0;
    }
    
    $pageviews = json_decode(file_get_contents(PAGEVIEWS_FILE), true) ?: [];
    $total = 0;
    
    foreach ($pageviews as $page => $dates) {
        foreach ($dates as $date => $count) {
            $total += $count;
        }
    }
    
    return $total;
}

/**
 * Get unique visitor count
 * 
 * @param int $days Number of days to include
 * @return int Number of unique visitors
 */
function getUniqueVisitors($days = 30) {
    if (!file_exists(VISITORS_FILE)) {
        return 0;
    }
    
    $visitors = json_decode(file_get_contents(VISITORS_FILE), true) ?: [];
    $uniqueIds = [];
    $startTimestamp = strtotime("-$days days");
    
    foreach ($visitors as $visitor) {
        $visitorTimestamp = strtotime($visitor['timestamp']);
        if ($visitorTimestamp >= $startTimestamp) {
            $uniqueIds[$visitor['visitor_id']] = true;
        }
    }
    
    return count($uniqueIds);
}

// Automatically track page view on each page load
if (!defined('SKIP_ANALYTICS') && !defined('AJAX_REQUEST')) {
    trackPageView();
}
