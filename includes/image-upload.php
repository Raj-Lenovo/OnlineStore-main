<?php
require_once 'config.php';

/**
 * Handle image upload
 * @param array $file $_FILES array element
 * @param string $uploadDir Directory to upload to (relative to project root)
 * @return array ['success' => bool, 'message' => string, 'filename' => string]
 */
function uploadImage($file, $uploadDir = 'assets/images/products/') {
    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error occurred.'];
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = $file['type'];
    
    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.'];
    }
    
    // Validate file size (max 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB in bytes
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size exceeds 5MB limit.'];
    }
    
    // Create upload directory if it doesn't exist
    $fullPath = __DIR__ . '/../' . $uploadDir;
    if (!file_exists($fullPath)) {
        mkdir($fullPath, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('product_', true) . '_' . time() . '.' . $extension;
    $targetPath = $fullPath . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [
            'success' => true, 
            'message' => 'Image uploaded successfully.',
            // we store a relative path, e.g. "assets/images/products/product_xxx.jpg"
            'filename' => $uploadDir . $filename
        ];
    } else {
        return ['success' => false, 'message' => 'Failed to move uploaded file.'];
    }
}

/**
 * Delete image file
 * @param string $imagePath Path to image file
 * @return bool
 */
function deleteImage($imagePath) {
    if (empty($imagePath)) {
        return true;
    }
    
    $fullPath = __DIR__ . '/../' . $imagePath;
    if (file_exists($fullPath) && is_file($fullPath)) {
        return unlink($fullPath);
    }
    return true;
}

/**
 * Get image URL
 * @param string $imagePath Image path from database
 * @return string Full URL to image
 */
function getImageUrl($imagePath) {
    // If no image, use placeholder
    if (empty($imagePath)) {
        return SITE_URL . '/assets/images/placeholder.jpg';
    }

    // If it's already a full URL, return as is
    if (strpos($imagePath, 'http://') === 0 || strpos($imagePath, 'https://') === 0) {
        return $imagePath;
    }

    // If path already starts with a known folder (assets/ or uploads/), use it directly
    if (preg_match('#^(assets/|uploads/)#', $imagePath)) {
        return SITE_URL . '/' . ltrim($imagePath, '/');
    }

    // Otherwise assume it's just a bare filename stored in DB
    // and that the file lives in assets/images/products/
    return SITE_URL . '/assets/images/products/' . ltrim($imagePath, '/');
}
