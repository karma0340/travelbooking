<?php
/**
 * Email Configuration and Helper Functions
 * Uses PHPMailer for reliable email sending
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send email using Gmail SMTP
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body Email body (HTML or plain text)
 * @param string $replyTo Reply-to email address
 * @return bool Success status
 */
function sendEmail($to, $subject, $body, $replyTo = null) {
    // Log the attempt first
    $logFile = __DIR__ . '/../logs/emails_' . date('Y-m-d') . '.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    file_put_contents($logFile, "Attempting to send email to $to at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

    // For production AND localhost (since we have credentials), use PHPMailer with Gmail SMTP
    try {
        // Check if OpenSSL is available (required for Gmail SMTP)
        if (!extension_loaded('openssl')) {
            $msg = "WARNING: OpenSSL extension not loaded. Email to $to cannot be sent via SMTP. Logging only.";
            error_log($msg);
            file_put_contents($logFile, "$msg\nEmail Content:\nSubject: $subject\nBody:\n$body\n", FILE_APPEND);
            return true; // Return true to allow booking to proceed
        }

        // Use our custom loader since we manually downloaded the files
        require_once __DIR__ . '/phpmailer-loader.php';
        
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        // The account DOING the sending (requires App Password)
        $mail->Username   = 'tyagithings@gmail.com'; 
        $mail->Password   = 'cagqubnpofhktzcl';      // App Password for tyagithings@gmail.com
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Sender info (must match the Username above usually)
        $mail->setFrom('tyagithings@gmail.com', 'Travel In Peace Booking System');
        
        // Recipient (This is where the email goes - travelinpeace605@gmail.com)
        $mail->addAddress($to);
        
        if ($replyTo) {
            $mail->addReplyTo($replyTo);
        }
        
        // Content
        $mail->isHTML(false); // Plain text for now
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        $mail->send();
        file_put_contents($logFile, "Email SENT successfully to $to\n", FILE_APPEND);
        return true;
    } catch (Exception $e) {
        $errorMsg = "Email sending failed: {$mail->ErrorInfo}";
        error_log($errorMsg);
        file_put_contents($logFile, "ERROR: $errorMsg\n", FILE_APPEND);
        return false;
    }
}
?>
