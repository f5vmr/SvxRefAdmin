<?php
session_start();

$valid_users = [
    'admin' => 'SuperSecurePassword123',
    'backupadmin' => 'AnotherStrongPassword!'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (isset($valid_users[$user]) && $valid_users[$user] === $pass) {
        $_SESSION['admin'] = true;
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid credentials.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>

</head>
<body>
<h2>SVXReflector Admin Login</h2>
<link rel="stylesheet" href="../style.css">
<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post">
    <label>Username: <input name="username" type="text" required></label><br>
    <label>Password: <input name="password" type="password" required></label><br>
    <button type="submit">Login</button>
</form>
</body>
</html>
