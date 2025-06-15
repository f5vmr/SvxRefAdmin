<?php

// Function to parse users and passwords from the configuration file
function parseUsers($file) {
    $users = [];
    $passwords = [];
    
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $section = '';
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        if (empty($line)) {
            continue;
        }
        
        if (strpos($line, "[") === 0) {
            $section = trim($line, "[]");
            continue;
        }
        
        if ($section === 'USERS') {
            list($callsign, $password_ref) = explode("=", $line);
            $users[trim($callsign)] = trim($password_ref);
        }
        
        if ($section === 'PASSWORDS') {
            list($callsign, $password) = explode("=", $line);
            $passwords[trim($callsign)] = trim($password);
        }
    }
    
    return ['users' => $users, 'passwords' => $passwords];
}
// Function to deactivate a user by adding #
function deactivate_user($file, $callsign) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $updated = false;

    foreach ($lines as $key => $line) {
        if (strpos($line, "$callsign=") === 0) {
            $lines[$key] = "#$line";
            $updated = true;
            break;
        }
    }

    if ($updated) {
        file_put_contents($file, implode(PHP_EOL, $lines) . PHP_EOL);
    }
}
// Function to reactivate a user by removing #
function reactivate_user($file, $callsign) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $updated = false;

    foreach ($lines as $key => $line) {
        if (strpos($line, "#$callsign=") === 0) {
            $lines[$key] = ltrim($line, "#");
            $updated = true;
            break;
        }
    }

    if ($updated) {
        file_put_contents($file, implode(PHP_EOL, $lines) . PHP_EOL);
    }
}

// Add these new functions to your existing functions.php

function editUser($callsign, $newPassKey) {
    // Implementation for editing user pass key
    $configFile = 'path/to/your/config/file';
    $lines = file($configFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $updated = false;
    
    foreach ($lines as $key => $line) {
        if (strpos($line, "$callsign=") === 0) {
            $lines[$key] = "$callsign=$newPassKey";
            $updated = true;
            break;
        }
    }
    
    if ($updated) {
        file_put_contents($configFile, implode(PHP_EOL, $lines) . PHP_EOL);
        return true;
    }
    return false;
}

//function deleteUser($callsign) {
//    $configFile = 'path/to/your/config/file';
//    $lines = file($configFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
//    $updated = false;
//    
//    foreach ($lines as $key => $line) {
//        if (strpos($line, "$callsign=") === 0) {
//            unset($lines[$key]);
//            $updated = true;
//            break;
//        }
//    }
//    
//    if ($updated) {
//        file_put_contents($configFile, implode(PHP_EOL, $lines) . PHP_EOL);
//        return true;
//    }
//    return false;
//}

function fetchNewCopy() {
    // Implementation for fetching new copy of configuration
    $sourceFile = 'path/to/source/config';
    $destFile = 'path/to/destination/config';
    return copy($sourceFile, $destFile);
}

function pushLive() {
    // Implementation for pushing changes live
    $stagingFile = 'path/to/staging/config';
    $liveFile = 'path/to/live/config';
    return copy($stagingFile, $liveFile);
}
function editUserPassword($file, $callsign, $new_password_ref) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $updated = false;

    foreach ($lines as $key => $line) {
        if (strpos($line, "$callsign=") === 0) {
            $lines[$key] = "$callsign=$new_password_ref";
            $updated = true;
            break;
        }
    }

    if ($updated) {
        file_put_contents($file, implode(PHP_EOL, $lines) . PHP_EOL);
    }
}

function deleteUser($file, $callsign) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $filtered_lines = array_filter($lines, function($line) use ($callsign) {
        return strpos($line, "$callsign=") !== 0;
    });
    
    file_put_contents($file, implode(PHP_EOL, $filtered_lines) . PHP_EOL);
}
