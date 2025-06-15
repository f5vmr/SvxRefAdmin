<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

switch($data['action']) {
    case 'update':
        $response = handleUpdate($data['callsign'], $data['passKey']);
        break;
        
    case 'delete':
        $response = handleDelete($data['callsign']);
        break;
        
    default:
        $response = ['success' => false, 'error' => 'Invalid action'];
}

header('Content-Type: application/json');
echo json_encode($response);

function handleUpdate($callsign, $passKey) {
    $config = parseConfigFile(file_get_contents('/tmp/svxreflector.conf'));
    
    if ($passKey === 'new') {
        $passKey = $callsign . 'pass';
        $config['passwords'][$passKey] = generatePassword();
    }
    
    $config['users'][$callsign] = $passKey;
    $success = saveConfigFile($config['users'], $config['passwords']);
    
    return [
        'success' => $success,
        'newPassKey' => $passKey,
        'password' => $config['passwords'][$passKey] ?? null
    ];
}

function handleDelete($callsign) {
    $config = parseConfigFile(file_get_contents('/tmp/svxreflector.conf'));
    
    if (isset($config['users'][$callsign])) {
        $passKey = $config['users'][$callsign];
        unset($config['users'][$callsign]);
        
        // Remove password if no other users are using it
        if (!in_array($passKey, $config['users'])) {
            unset($config['passwords'][$passKey]);
        }
        
        $success = saveConfigFile($config['users'], $config['passwords']);
        return ['success' => $success];
    }
    
    return ['success' => false, 'error' => 'User not found'];
}

function generatePassword($length = 13) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

function saveConfigFile($users, $passwords) {
    global $WORKING_CONF;
    
    // Preserve existing GLOBAL and TG sections
    $sections = parse_ini_file($WORKING_CONF, true);
    
    $output = "[GLOBAL]\n";
    foreach ($sections['GLOBAL'] as $key => $value) {
        $output .= "$key=$value\n";
    }
    
    $output .= "\n[USERS]\n";
    foreach ($users as $callsign => $passKey) {
        $output .= "$callsign=$passKey\n";
    }
    
    $output .= "\n[PASSWORDS]\n";
    foreach ($passwords as $key => $value) {
        $output .= "$key=\"$value\"\n";
    }
    
    if (isset($sections['TG#9999'])) {
        $output .= "\n[TG#9999]\n";
        foreach ($sections['TG#9999'] as $key => $value) {
            $output .= "$key=$value\n";
        }
    }
    
    return file_put_contents($WORKING_CONF, $output) !== false;
}

function parseConfigFile($filePath) {
    $sections = parse_ini_file($filePath, true);
    return [
        'users' => $sections['USERS'] ?? [],
        'passwords' => $sections['PASSWORDS'] ?? []
    ];
}
