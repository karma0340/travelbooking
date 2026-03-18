<?php
@session_start();
$_SESSION['admin_user'] = 'admin';
$_POST = [
    'action' => 'add_url',
    'entity_type' => 'tour',
    'entity_id' => '1',
    'image_url' => 'https://example.com/tour1_test_sync.jpg'
];
// Move to the right directory so relative requires work
chdir(__DIR__ . '/admin/api');
require_once 'image-manager.php';
?>
