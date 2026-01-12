<?php
// Custom loader for PHPMailer since we couldn't use Composer's autoloader
// due to gitignore restrictions on the vendor directory for the editor

$vendorDir = __DIR__ . '/../vendor/phpmailer/phpmailer/src/';

if (file_exists($vendorDir . 'PHPMailer.php')) {
    require_once $vendorDir . 'Exception.php';
    require_once $vendorDir . 'PHPMailer.php';
    require_once $vendorDir . 'SMTP.php';
} else {
    // Fallback or log error
    error_log("PHPMailer source files not found in " . $vendorDir);
}
?>
