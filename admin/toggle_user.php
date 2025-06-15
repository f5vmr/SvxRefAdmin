<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$config = include('/var/www/secure-config/config.php');

$data = json_decode(file_get_contents("php://input"), true);
$userToToggle = trim($data['user'] ?? '');

if (!$userToToggle) {
    echo json_encode(['success' => false, 'message' => 'No user specified']);
    exit;
}

// Step 1: Pull remote file
$remoteContent = shell_exec(sprintf(
    "ssh -p %d %s@%s 'cat %s'",
    $config['port'],
    escapeshellarg($config['username']),
    escapeshellarg($config['host']),
    escapeshellarg($config['file_path'])
));

if (!$remoteContent) {
    echo json_encode(['success' => false, 'message' => 'Could not retrieve file']);
    exit;
}

// Step 2: Edit lines in USERS block
$lines = explode("\n", $remoteContent);
$inUsers = false;
$updatedLines = [];

foreach ($lines as $line) {
    $trimmed = ltrim($line, "#");
    if (preg_match('/^\[USERS\]/i', $trimmed)) {
        $inUsers = true;
        $updatedLines[] = $line;
        continue;
    }

    if ($inUsers && preg_match('/^\[.*\]/', $trimmed)) {
        $inUsers = false; // End of USERS block
    }

    if ($inUsers && preg_match('/^(#?)(\s*' . preg_quote($userToToggle, '/') . '\s*=.*)/', $line, $m)) {
        $currentlyDisabled = $m[1] === "#";
        $line = ($currentlyDisabled ? "" : "#") . $m[2];
    }

    $updatedLines[] = $line;
}

$newContent = implode("\n", $updatedLines);
$tempFile = "/tmp/svx_temp_" . uniqid() . ".txt";
file_put_contents($tempFile, $newContent);

// Step 3: Send updated file back and restart svxreflector
$scpCmd = sprintf(
    "scp -P %d %s %s@%s:%s",
    $config['port'],
    escapeshellarg($tempFile),
    escapeshellarg($config['username']),
    escapeshellarg($config['host']),
    escapeshellarg($config['file_path'])
);

$restartCmd = sprintf(
    "ssh -p %d %s@%s 'sudo systemctl restart svxreflector'",
    $config['port'],
    escapeshellarg($config['username']),
    escapeshellarg($config['host'])
);

unlink($tempFile);

exec($scpCmd, $scpOut, $scpRet);
exec($restartCmd, $restartOut, $restartRet);

if ($scpRet === 0 && $restartRet === 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Update failed: SCP or restart command failed'
    ]);
}
