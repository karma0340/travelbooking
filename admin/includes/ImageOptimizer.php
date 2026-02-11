<?php
/**
 * Image Compression and Optimization Utility
 * Automatically compresses, resizes, and converts images to WebP
 */

class ImageOptimizer {
    
    private $uploadDir = '../../uploads/images/';
    private $quality = 80; // Compression quality (0-100)
    
    // Image size presets
    private $sizes = [
        'thumbnail' => ['width' => 300, 'height' => 300],
        'medium' => ['width' => 800, 'height' => 600],
        'large' => ['width' => 1200, 'height' => 900],
        'original' => ['width' => 1920, 'height' => 1440] // Max original size
    ];
    
    /**
     * Process and optimize uploaded image
     * @param array $file - $_FILES array element
     * @return array - Result with paths to all generated sizes
     */
    public function processUpload($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Upload error: ' . $this->getUploadErrorMessage($file['error'])];
        }
        
        // Ensure upload directory exists
        if (!file_exists($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                return ['success' => false, 'message' => 'Failed to create upload directory'];
            }
        }
        
        // Check if directory is writable
        if (!is_writable($this->uploadDir)) {
            return ['success' => false, 'message' => 'Upload directory is not writable'];
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid file type: ' . $file['type']];
        }
        
        // Check file size (max 10MB before compression)
        if ($file['size'] > 10 * 1024 * 1024) {
            return ['success' => false, 'message' => 'File too large (max 10MB)'];
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $baseFilename = uniqid() . '_' . time();
        
        // Load original image
        $sourceImage = $this->loadImage($file['tmp_name'], $file['type']);
        if (!$sourceImage) {
            return ['success' => false, 'message' => 'Failed to load image. File may be corrupted.'];
        }
        
        $originalWidth = imagesx($sourceImage);
        $originalHeight = imagesy($sourceImage);
        
        $generatedImages = [];
        
        // Generate all size variants
        foreach ($this->sizes as $sizeName => $dimensions) {
            // Skip if original is smaller than target size
            if ($sizeName !== 'original' && 
                $originalWidth <= $dimensions['width'] && 
                $originalHeight <= $dimensions['height']) {
                continue;
            }
            
            // Calculate new dimensions maintaining aspect ratio
            $newDimensions = $this->calculateDimensions(
                $originalWidth, 
                $originalHeight, 
                $dimensions['width'], 
                $dimensions['height']
            );
            
            // Create resized image
            $resizedImage = imagecreatetruecolor($newDimensions['width'], $newDimensions['height']);
            
            // Preserve transparency for PNG/GIF
            if ($file['type'] === 'image/png' || $file['type'] === 'image/gif') {
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
                $transparent = imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
                imagefilledrectangle($resizedImage, 0, 0, $newDimensions['width'], $newDimensions['height'], $transparent);
            }
            
            // Resize with high quality
            imagecopyresampled(
                $resizedImage, 
                $sourceImage, 
                0, 0, 0, 0,
                $newDimensions['width'], 
                $newDimensions['height'],
                $originalWidth, 
                $originalHeight
            );
            
            // Save as WebP (best compression)
            $webpFilename = $baseFilename . '_' . $sizeName . '.webp';
            $webpPath = $this->uploadDir . $webpFilename;
            
            if (function_exists('imagewebp')) {
                imagewebp($resizedImage, $webpPath, $this->quality);
                $generatedImages[$sizeName] = [
                    'path' => 'uploads/images/' . $webpFilename,
                    'width' => $newDimensions['width'],
                    'height' => $newDimensions['height'],
                    'size' => filesize($webpPath),
                    'format' => 'webp'
                ];
            }
            
            // Also save as JPEG fallback for older browsers
            $jpgFilename = $baseFilename . '_' . $sizeName . '.jpg';
            $jpgPath = $this->uploadDir . $jpgFilename;
            imagejpeg($resizedImage, $jpgPath, $this->quality);
            
            if (!isset($generatedImages[$sizeName])) {
                $generatedImages[$sizeName] = [
                    'path' => 'uploads/images/' . $jpgFilename,
                    'width' => $newDimensions['width'],
                    'height' => $newDimensions['height'],
                    'size' => filesize($jpgPath),
                    'format' => 'jpg'
                ];
            }
            
            imagedestroy($resizedImage);
        }
        
        imagedestroy($sourceImage);
        
        return [
            'success' => true,
            'images' => $generatedImages,
            'primary_image' => $generatedImages['large'] ?? $generatedImages['medium'] ?? $generatedImages['original']
        ];
    }
    
    /**
     * Load image from file based on type
     */
    private function loadImage($filepath, $type) {
        switch ($type) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagecreatefromjpeg($filepath);
            case 'image/png':
                return imagecreatefrompng($filepath);
            case 'image/gif':
                return imagecreatefromgif($filepath);
            case 'image/webp':
                return imagecreatefromwebp($filepath);
            default:
                return false;
        }
    }
    
    /**
     * Calculate new dimensions maintaining aspect ratio
     */
    private function calculateDimensions($sourceWidth, $sourceHeight, $maxWidth, $maxHeight) {
        $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
        
        // Don't upscale
        if ($ratio > 1) {
            $ratio = 1;
        }
        
        return [
            'width' => round($sourceWidth * $ratio),
            'height' => round($sourceHeight * $ratio)
        ];
    }
    
    /**
     * Delete all variants of an image
     */
    public function deleteImage($imagePath) {
        // Extract base filename
        $pathInfo = pathinfo($imagePath);
        $pattern = $this->uploadDir . $pathInfo['filename'] . '*';
        
        $files = glob($pattern);
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        return true;
    }
    
    /**
     * Get compression statistics
     */
    public function getCompressionStats($originalSize, $compressedSize) {
        $savedBytes = $originalSize - $compressedSize;
        $savedPercent = round(($savedBytes / $originalSize) * 100, 1);
        
        return [
            'original_size' => $this->formatBytes($originalSize),
            'compressed_size' => $this->formatBytes($compressedSize),
            'saved' => $this->formatBytes($savedBytes),
            'saved_percent' => $savedPercent . '%'
        ];
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Get human-readable upload error message
     */
    private function getUploadErrorMessage($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE in HTML form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by PHP extension'
        ];
        
        return $errors[$errorCode] ?? 'Unknown upload error';
    }
}
?>
