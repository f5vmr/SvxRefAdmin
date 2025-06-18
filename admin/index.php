<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVX Admin Login</title>
</head>
<link rel="stylesheet" href="../style.css">
<body>
<h2>SVX Reflector Admin Login</h2>
<form method="POST" action="verify.php">
    <label>Username:</label><br>
    <input type="text" name="username" required><br><br>
    
    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>
    
    <button type="submit">Login</button>
</form>
</body>
</html>
