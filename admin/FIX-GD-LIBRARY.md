# 🔧 FIX: Enable GD Library in XAMPP

## Problem
GD library is not loaded - this is required for image processing.

## Solution (Choose ONE method)

### Method 1: Automatic (Recommended)
1. Right-click on `admin/enable-gd.ps1`
2. Select "Run with PowerShell"
3. Follow the prompts
4. Restart Apache in XAMPP Control Panel

### Method 2: Manual
1. Open XAMPP Control Panel
2. Click "Config" next to Apache
3. Select "PHP (php.ini)"
4. Find this line (around line 900-950):
   ```
   ;extension=gd
   ```
5. Remove the semicolon (;) to make it:
   ```
   extension=gd
   ```
6. Save the file
7. Restart Apache in XAMPP Control Panel

### Method 3: Command Line
1. Open Command Prompt as Administrator
2. Run:
   ```
   cd C:\xampp\php
   notepad php.ini
   ```
3. Press Ctrl+F and search for: `extension=gd`
4. Remove the `;` at the beginning of the line
5. Save (Ctrl+S) and close
6. Restart Apache

## Verify It Works
1. Restart Apache in XAMPP
2. Go to: http://localhost/git2/admin/test-image-upload.php
3. Check "2. PHP GD Library" - should show ✓

## Still Not Working?

### Check if GD DLL exists:
1. Go to: `C:\xampp\php\ext\`
2. Look for: `php_gd2.dll` or `php_gd.dll`
3. If missing, reinstall XAMPP or download the DLL

### Check PHP version:
1. Open: http://localhost/dashboard/phpinfo.php
2. Search for "gd"
3. Should show GD Support enabled

## After Fixing
1. Run migration again: http://localhost/git2/admin/migrate-images.php
2. Test upload: http://localhost/git2/admin/test-image-upload.php
3. Try uploading an image in admin panel

---

## Quick Summary
**What to do:** Edit `C:\xampp\php\php.ini` and change `;extension=gd` to `extension=gd`
**Then:** Restart Apache in XAMPP Control Panel
**Verify:** Run test-image-upload.php
