<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head><title>SVX Admin Login</title></head>
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
