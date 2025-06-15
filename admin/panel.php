<?php
session_start();

// Load config outside web root
$config = include('/var/www/secure-config/config.php');

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (in_array($_POST['password'], $config['admin_passwords'])) {
        $_SESSION['logged_in'] = true;
        header("Location: panel.php");
        exit();
    } else {
        $error = "Invalid password.";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: panel.php");
    exit();
}

// Redirect to login form if not logged in
if (!isset($_SESSION['logged_in'])):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="/style.css">
    <script>
function toggleUser(username) {
    if (!confirm("Are you sure you want to toggle this user's access?")) {
        return; // Exit if cancelled
    }

    fetch('toggle_user.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ user: username })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Refresh table
        } else {
            alert('Error: ' + data.message);
        }
    });
}

// Auto-refresh every 30 seconds
setInterval(() => {
    location.reload();
}, 30000);
</script>


</head>
<body>
    <div class="login-container">
  <form method="post" action="login.php">
    <h2>Admin Login</h2>

    <label for="username">Username</label>
    <input type="text" id="username" name="username" required>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Login</button>

    <?php if (isset($error)): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
  </form>
</div>

</body>
</html>
<?php
exit;
endif;

// === Logged in, proceed to fetch and parse file ===
$sshCmd = sprintf(
    "ssh -p %d %s@%s 'cat %s'",
    $config['port'],
    escapeshellarg($config['username']),
    escapeshellarg($config['host']),
    escapeshellarg($config['file_path'])
);

$output = shell_exec($sshCmd);
if (!$output) {
    die("<p style='color:red;text-align:center;'>Failed to retrieve configuration file.</p>");
}

// === Parse config file ===
preg_match('/\[USERS\](.*?)\n\[/', $output . "\n[", $usersMatch);
preg_match('/\[PASSWORDS\](.*?)\n\[/', $output . "\n[", $passMatch);

$users = [];
$pseudoMap = [];

// Parse USERS block
if (!empty($usersMatch[1])) {
    foreach (explode("\n", trim($usersMatch[1])) as $line) {
        if (preg_match('/^#?(.*?)=(.*)/', trim($line), $m)) {
            $users[trim($m[1])] = [
                'enabled' => $line[0] !== '#',
                'pseudo' => trim($m[2])
            ];
        }
    }
}

// Parse PASSWORDS block
if (!empty($passMatch[1])) {
    foreach (explode("\n", trim($passMatch[1])) as $line) {
        if (preg_match('/^(.*?)=(.*)/', trim($line), $m)) {
            $pseudoMap[trim($m[1])] = trim($m[2]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SVXReflector User Panel</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <div class="admin-container">
        <header>
            <h1>SVXReflector Admin Panel</h1>
            <a href="?logout=true" class="deactivate-button">Logout</a>
        </header>
        <table class="users-list">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>User</th>
                    <th>Password</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $username => $data): ?>
                    <tr class="<?= $data['enabled'] ? 'active' : 'inactive' ?>">
                        <td>
                        <?= $data['enabled'] ? '✅ Active' : '❌ Disabled' ?>
                        <button onclick="toggleUser('<?= htmlspecialchars($username) ?>')">
                        <?= $data['enabled'] ? 'Disable' : 'Enable' ?>
                        </button>
</td>
</tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
