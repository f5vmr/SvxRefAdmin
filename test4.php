<?php
$host = 'portal.svxlink.uk';
$port = 19022;
$srv_username = 'root';

$privateKeyFile = '/root/.ssh/id_rsa';
$publicKeyFile = '/root/.ssh/id_rsa.pub';

try {
    error_log("Attempting SSH connection to $host:$port as user $srv_username");

    error_log("Private Key File: " . $privateKeyFile);
    error_log("Public Key File: " . $publicKeyFile);

    $connection = ssh2_connect($host, $port, array('hostkey' => 'ssh-rsa'));
    if (!$connection) {
        $error = "Failed to connect to $host on port $port. Error: " . error_get_last()['message'];
        error_log($error);
        die($error . "\n");
    }
    error_log("Connection established!");


    // try with explicit ssh-rsa key type
    if (!ssh2_auth_pubkey_file($connection, $srv_username, $publicKeyFile, $privateKeyFile, 'ssh-rsa')) {
            $error = "Public key authentication failed with ssh-rsa type.";
            error_log($error);
            die($error . "\n");
    }
    error_log("Connection successful with ssh-rsa key type!\n");
    echo "Connection successful with ssh-rsa key type!\n";
    die;


     // try with no explicit type
    if (!ssh2_auth_pubkey_file($connection, $srv_username, $publicKeyFile, $privateKeyFile, '')) {
            $error = "Public key authentication failed with no type.";
            error_log($error);
            die($error . "\n");
    }
    error_log("Connection successful with no key type\n");
    echo "Connection successful with no key type!\n";
    die;

    // try without passphrase
    if (!ssh2_auth_pubkey_file($connection, $srv_username, $publicKeyFile, $privateKeyFile)) {
         $error = "Public key authentication failed without a passphrase.";
         error_log($error);
        die($error . "\n");
    }
    error_log("Connection successful with no passphrase\n");
    echo "Connection successful with no passphrase!\n";
    die;

    error_log("No connection was made");


} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
    error_log($error);
    die($error . "\n");
}
?>
