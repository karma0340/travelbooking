<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing Email Helper...\n";

require_once __DIR__ . '/includes/email-helper.php';

echo "Email helper included.\n";

$to = "test@example.com";
$subject = "Test Object";
$body = "Test Body";

echo "Attempting to send email...\n";
$result = sendEmail($to, $subject, $body);

echo "Result: " . ($result ? "Success" : "Failed") . "\n";
?>
