<?php
session_start();
$admins = require '/var/www/secure-config/admin_passwd.php';

$user = $_POST['username'] ?? '';
$pass = $_POST['password'] ?? '';

if (isset($admins[$user]) && password_verify($pass, $admins[$user])) {
    $_SESSION['admin'] = $user;
    header("Location: panel.php");
    exit;
} else {
    echo "Access denied. <a href='index.php'>Try again</a>";
}
