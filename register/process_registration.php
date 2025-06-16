<?php
// Initialize any PHP variables or session handling here
session_start();
include_once '../../secure-config/config.php';
include_once 'functions.php';
//require '../vendor/autoload.php';
$validated = ""; // Initialize the variable otherwise it will be undefined
// Replace error reporting with:
error_reporting(0);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/apache2/error.log');

// Define $_POST variables

$email = $_POST['email'];
$callsign = $_POST['callsign'];
$repeater = $_POST['repeater'];
$dmr_id = $_POST['dmr_id'];
$echolink_id = $_POST['echolink_id'];


// Check if both DMR ID and EchoLink ID are empty
// If so, display a 'get yourself sorted' message and exit
if (empty($dmr_id) && empty($echolink_id)) {

    echo '<body>';
    echo '<div class="container">';
    echo '<h1>SVXLink User Registration</h1>';
    echo '<head>';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<p class="welcome-message"><h2>Registration Failed</h2></p>';
    echo '<link rel="stylesheet" href="style.css">';
    echo '</head>';
    echo '<div class="message-container alert alert-warning">';
    echo "<p>An ID number from either RadioId.net or EchoLink is required to validate your Callsign.<br></p>";
    echo "<p><br>Please register with either of these, then run this page again.<br><br>In case of difficulty, email help@svxlink.uk.<br></p>";
    echo '</div>';
    echo '</div>';
    echo '</body>';
    exit; // Terminate the script
}
// This is where we check if the user is already registered
// Firstly with a dmr id but NOT a repeater
if ($repeater == 0 && intval($dmr_id) > 0){
    $result = validate_dmruser($callsign, $dmr_id);
    //var_dump($result); // Debug output
    $validated = $result['valid'];
}
// Secondly with a dmr id and is a repeater
elseif ($repeater == 1 && intval($dmr_id) > 0){
    $result = validate_dmrrepeater($callsign, $dmr_id);
    //var_dump($result); // Debug output
    $validated = $result['valid'];
}
// Thirdly with an echolink id but NOT a repeater
elseif ($repeater == 0 && intval($echolink_id) > 0){
    $result = validate_echolink_id($callsign,$echolink_id);
    //var_dump($result); // Debug output
    $validated = $result['valid'];
}
// Fourthly with an echolink id and is a repeater or link but use only the callsign without suffix
elseif ($repeater == 1 && intval($echolink_id) > 0){
    $result = validate_echolink_id($callsign,$echolink_id);
    //var_dump($result); // Debug output
    $validated = $result['valid'];
}

//Continue with the rest of the script
// Validated callsigns need a password.
if ($validated === true) {
    
    // Here we shall call the Reflector ssh2 open function
    $connection = ssh2_connect($host, $port);
    if (!$connection) {
        die("Failed to connect to $host on port $port.\n");
    }

    if (!ssh2_auth_pubkey_file($connection, $srv_username, $publicKeyFile, $privateKeyFile,'')) {
        die("Public key authentication failed.\n");
    }

    //echo "Connection successful!\n";
    
    //here we back up  the svxreflector.txt file
    backup_reflector_file($connection, $remoteFile);

    // Here we shall download the svxreflector.txt file
    $download = ssh2_download_file($connection, $remoteFile, $localFile);
    if ($download === false) {
    die("Failed to download the file from the server.\n");
    }
    // Here we shall parse the svxreflector.txt file
    $parsedData = parse_svx_file($localFile);
    if (check_existing_callsign($parsedData, $callsign)) {
        echo '<body>';
        echo '<div class="container">';
        echo '<h1>SVXLink User Registration</h1>';
        echo '<head>';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<p class="welcome-message"><h2>Registration Failed</h2></p>';
        echo '<link rel="stylesheet" href="style.css">';
        echo '</head>';
        echo '<div class="message-container alert alert-warning">';
        echo "<p>Callsign $callsign is already registered in the SvxReflector.<br><br>If you need to update your registration, please email help@svxlink.uk.</p>";
        echo '</div>';
        echo '</div>';
        echo '</body>';
        exit;
    }
    $password = generate_reflector_password();
    // Registration successful, redirect to the success page
    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>SVXLink User Registration</title>';
    echo '<link rel="stylesheet" href="style.css">';
    echo '</head>';
    echo '<body>';
    echo '<div class="container" style="text-align: justify;">'; // Added justify style here
    echo '<h1>SVXLink User Registration</h1>';
    echo '<div class="message-container alert alert-warning">';
    echo '<h2>Registration Succeeded</h2>';
    echo "<p> Callsign $callsign Your password (AUTH_KEY) $password will be emailed to you and the SVXPortal will be updated for your connection.</p>";
    echo '<p>';
    echo '<br>Open your node dashboard and authorize yourself to edit <code>svxlink.conf</code>. ';
    echo 'Scroll down to the <strong>ReflectorLogic Section</strong>, and locate the line <code>AUTH_KEY</code>.';
    echo '</p>';
    echo '<p>';
    echo '<br>Copy the password from your email and paste it into the box to the right, enclosing it with double quotes (<code>""</code>).<br>';
    echo '<br>Then scroll to the bottom and click the <b><class style="color: red;">Big Red Button.</b></p>';
    echo '<p><br>You may now find your callsign in the <strong>SVXLink Users</strong> section of the <strong>portal.svxlink.uk</strong>.</p>';
    echo '<p><br>If you have any further questions, please email help@svxlink.uk.</p>';
    echo '</div>';
    echo '</div>';
    echo '</body>';
    echo '</html>';
    
    //var_dump($parsedData);
    // Here we shall construct the callsign requirements
    // We need to take $callsign and make a copy in lower case and attach it to 'pass' and call is $pseudo_call.
    // as a place holder then construct variable $upload_callsign = $callsign ."=" . $pseudo_call;
    // Now we construct a variable $upload_password = $pseudo_call . "=" . $password;
    $pwd_callsign = strtolower($callsign) . "pass";
    $upload_callsign_line = $callsign . "=" . $pwd_callsign;
    $upload_password_line =  $pwd_callsign . "=" . $password;
    //var_dump($upload_callsign,$upload_password);
    
    // Here we shall call the ssh2_add function
    add_entry_to_section($parsedData, "USERS", $upload_callsign_line);
    add_entry_to_section($parsedData, "PASSWORDS", $upload_password_line);
    // Here we shall write update_reflector_password data to the svxreflector.txt file
    try {
        write_svx_file($localFile, $parsedData);
        //echo "Local file written back successfully.\n";
    } catch (Exception $e) {
        die("Error writing to local file: " . $e->getMessage() . "\n");
    }
    $upload = ssh2_scp_send($connection, $localFile, $remoteFile, 0644);
    if ($upload === false) {
    die("Failed to upload the updated file to the server.\n");
}
    //echo "File uploaded successfully.\n";
    // Here we shall call the svxreflector restart function one time
    $restart = ssh2_exec($connection, 'systemctl restart svxreflector');
    if ($restart === false) {
    die("Failed to restart the svxreflector service.<br>");
    }
    //echo "svxreflector service restarted.<br>";
    // Here we shall exit the ssh2_exec function
    //
    // Here we shall call the send_email function
    //if (mail_out($email, $callsign, $password)) {
    //    echo "Email sent successfully!";
    //} else {
    //    echo "Failed to send email.";
    //}
    //
    //send_email($email, $password);

    exit; // Terminate the script
}
    elseif($validated === false) {
        echo '<body>';
        echo '<div class="container">';
        echo '<h1>SVXLink User Registration</h1>';
        echo '<head>';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<p class="welcome-message"><h2>Registration Failed</h2</p>';
        echo '<link rel="stylesheet" href="style.css">';
        echo '</head>';
        echo '<div class="message-container alert alert-warning">';
        echo "<p>Registration failed for '. $callsign .'. Please check your input and try again.</p>";
        echo '</div>';
        echo '</div>';
        echo '</body>';
        exit; // Terminate the script
    }
    exit;
    


// Continue processing if validation passes

