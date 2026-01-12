<?php

/**
 * Image SEO Helper
 * Optimizes images with proper alt text and attributes
 */

/**
 * Generate SEO-friendly alt text for images
 */
function generateImageAlt($imageName, $context = '') {
    $altText = '';
    
    // Extract meaningful name from image filename
    $nameWithoutExt = pathinfo($imageName, PATHINFO_FILENAME);
    $nameParts = explode('-', str_replace('_', '-', $nameWithoutExt));
    
    // Capitalize words properly
    $nameParts = array_map('ucfirst', $nameParts);
    $altText = implode(' ', $nameParts);
    
    // Add context if provided
    if (!empty($context)) {
        $altText = $context . ' - ' . $altText;
    }
    
    return $altText;
}

/**
 * Generate complete image tag with SEO attributes
 */
function seoImage($src, $alt = '', $class = '', $loading = 'lazy') {
    $alt = $alt ?: generateImageAlt($src);
    $src = htmlspecialchars($src);
    $alt = htmlspecialchars($alt);
    $class = htmlspecialchars($class);
    
    return "<img src=\"$src\" alt=\"$alt\" class=\"$class\" loading=\"$loading\" decoding=\"async\" />";
}

/**
 * Generate responsive picture element with multiple sources
 */
function seoPicture($imagePath, $alt = '', $class = '', $sizes = []) {
    $alt = $alt ?: generateImageAlt($imagePath);
    $imagePath = htmlspecialchars($imagePath);
    $alt = htmlspecialchars($alt);
    $class = htmlspecialchars($class);
    
    $html = "<picture class=\"$class\">";
    
    // Add different sizes if provided
    if (!empty($sizes)) {
        foreach ($sizes as $size => $src) {
            $html .= "<source media=\"(max-width: {$size}px)\" srcset=\"{$src}\">";
        }
    }
    
    $html .= "<img src=\"{$imagePath}\" alt=\"{$alt}\" loading=\"lazy\" decoding=\"async\" />";
    $html .= "</picture>";
    
    return $html;
}

?>
