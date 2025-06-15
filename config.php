<?php
#This file sits in the /var/www/directory
$ADMIN_USERS = [
    'admin1' => password_hash('admin1_secure_password', PASSWORD_DEFAULT),
    'admin2' => password_hash('admin2_secure_password', PASSWORD_DEFAULT),
    'admin3' => password_hash('admin3_secure_password', PASSWORD_DEFAULT)
];

define('ADMIN_LOG_FILE', '/var/log/admin.log');

function logAdminAction($username, $action) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "$timestamp | User: $username | Action: $action | IP: {$_SERVER['REMOTE_ADDR']}\n";
    file_put_contents(ADMIN_LOG_FILE, $logEntry, FILE_APPEND);
}
?>

