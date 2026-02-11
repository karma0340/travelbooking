# Multi-Image Gallery System - Setup Guide

## 🚀 Quick Start

### Step 1: Run Database Migration
1. Navigate to: `http://localhost/git2/admin/migrate-images.php`
2. This will create the necessary database table and upload directories
3. You should see success messages

### Step 2: Using the Image Manager

The image manager is now available in:
- **Tour Edit Page** (admin/tour-edit.php)
- **Vehicle Edit Page** (admin/vehicle-edit.php) - *Coming soon*
- **Category Edit Page** (admin/category-edit.php) - *Coming soon*

## 📸 How to Add Images

### Method 1: Upload from Device
1. Click on the "Upload File" tab
2. Click "Choose images" and select one or multiple images
3. Click "Upload Images"
4. Supported formats: JPG, PNG, GIF, WebP (Max 5MB each)

### Method 2: Add by URL
1. Click on the "Add URL" tab
2. Paste the image URL (e.g., https://example.com/image.jpg)
3. Click "Add"

## 🎯 Image Management Features

### Set Primary Image
- Click the **star icon** on any image to set it as primary
- The primary image will be shown first in carousels
- Only one image can be primary at a time

### Delete Images
- Click the **trash icon** to delete an image
- Uploaded files will be permanently deleted from the server
- URL-based images will only be removed from the database

### Image Order
- Images are displayed in the order they were added
- The number badge shows the current position

## 🎠 Frontend Carousel

### Auto-Rotation
- Multiple images automatically rotate every 2-5 seconds (configurable)
- Users can pause by hovering over the carousel
- Navigation arrows and dots allow manual control

### Usage in Templates
```php
<?php
$entityType = 'tour'; // or 'vehicle', 'category'
$entityId = 123; // The ID of the tour/vehicle/category
$interval = 3000; // Rotation interval in milliseconds (optional)
$fallbackImage = 'path/to/fallback.jpg'; // Fallback if no images (optional)
include 'includes/image-carousel.php';
?>
```

## 📁 File Structure

```
admin/
├── api/
│   └── image-manager.php       # API for image operations
├── includes/
│   └── image-manager.php       # Admin UI component
└── migrate-images.php          # Database migration

includes/
└── image-carousel.php          # Frontend carousel component

uploads/
└── images/                     # Uploaded images directory
```

## 🔒 Security Features

- Admin authentication required
- File type validation
- File size limits (5MB)
- SQL injection protection
- XSS protection
- Secure file uploads

## 🎨 Customization

### Change Auto-Rotation Speed
In your template, set the `$interval` variable:
```php
$interval = 5000; // 5 seconds
```

### Styling
The carousel uses inline styles for portability, but you can override them in your CSS:
```css
.carousel-images { /* Your styles */ }
.carousel-dots { /* Your styles */ }
.carousel-prev, .carousel-next { /* Your styles */ }
```

## 🐛 Troubleshooting

### Images not uploading?
- Check folder permissions: `uploads/images/` should be writable (755 or 777)
- Check PHP upload limits in `php.ini`:
  - `upload_max_filesize = 10M`
  - `post_max_size = 10M`

### Images not showing on frontend?
- Verify the database migration ran successfully
- Check browser console for JavaScript errors
- Ensure image paths are correct

## 📊 Database Schema

```sql
CREATE TABLE `entity_images` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `entity_type` ENUM('tour', 'vehicle', 'category') NOT NULL,
    `entity_id` INT(11) NOT NULL,
    `image_url` VARCHAR(500) NOT NULL,
    `image_type` ENUM('url', 'upload') DEFAULT 'url',
    `display_order` INT(11) DEFAULT 0,
    `is_primary` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);
```

## ✅ Next Steps

1. Run the migration
2. Edit a tour and add some images
3. View the tour on the frontend to see the carousel in action
4. Adjust the rotation interval to your preference

---

**Need help?** Check the code comments or contact your developer.
