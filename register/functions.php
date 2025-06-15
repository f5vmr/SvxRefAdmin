<?php
include_once 'config.php';
// This function will handle making the API request using cURL
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    error_log("PHPMailer class found");
} else {
    error_log("PHPMailer class not found");
}
// Ensure PHPMailer is autoloaded
require '/var/www/html/vendor/autoload.php';
require_once '/var/www/html/vendor/phpmailer/phpmailer/src/Exception.php';
require_once '/var/www/html/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once '/var/www/html/vendor/phpmailer/phpmailer/src/SMTP.php';
function makeApiRequest($url) {
    // Validate the URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        echo 'Invalid URL provided.';
        return false;
    }

    // Initialize cURL session
    $ch = curl_init($url);
    if (!$ch) {
        echo 'cURL initialization failed.';
        return false;
    }

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string
    curl_setopt($ch, CURLOPT_FAILONERROR, true);    // Fail on HTTP errors
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);          // Timeout in seconds
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);    // Verify the SSL host
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Verify the SSL certificate

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        //echo 'cURL error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }

    // Get the HTTP status code
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Handle non-200 HTTP responses
    if ($http_code !== 200) {
        echo "API request failed with HTTP status code: $http_code.";
        return false;
    }

    return $response; // Return the response on success
}

// This function will validate the DMR ID
// Example refactoring of the DMR validation function
function validate_dmruser($callsign, $dmr_id) {
    $api_url = "https://radioid.net/api/dmr/user/?id=" . urlencode($dmr_id);
    $response = makeApiRequest($api_url);

    if ($response === false) {
        return ['valid' => false, 'message' => "Error occurred while calling RadioId.net API for DMR ID"];
    }

    $dmr_data = decodeJsonResponse($response);
    if (!$dmr_data) {
        return ['valid' => false, 'message' => 'Failed to decode JSON response'];
    }

    if (isset($dmr_data['results'][0])) {
        $api_callsign = strtoupper(trim($dmr_data['results'][0]['callsign']));
        $callsign = strtoupper(trim($callsign));

        if ($api_callsign === $callsign) {
            return ['valid' => true, 'message' => 'Callsign is valid'];
        } else {
            return ['valid' => false, 'message' => "Callsign does not match"];
        }
    }

    return ['valid' => false, 'message' => 'DMR ID not found or invalid'];
}


function decodeJsonResponse($response) {
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Failed to decode JSON response: ' . json_last_error_msg());
        return false;
    }
    return $data;
}

// This function will validate the DMR Repeater ID
// Example refactoring of the DMR validation function
function validate_dmrrepeater($callsign, $dmr_id) {
    $api_url = "https://radioid.net/api/dmr/repeater/?id=" . urlencode($dmr_id);
    $response = makeApiRequest($api_url);

    if ($response === false) {
        return ['valid' => false, 'message' => "Error occurred while calling RadioId.net API for DMR ID"];
    }

    $dmr_data = decodeJsonResponse($response);
    if (!$dmr_data) {
        return ['valid' => false, 'message' => 'Failed to decode JSON response'];
    }

    if (isset($dmr_data['results'][0])) {
        $api_callsign = strtoupper(trim($dmr_data['results'][0]['callsign']));
        $callsign = strtoupper(trim($callsign));

        if ($api_callsign === $callsign) {
            return ['valid' => true, 'message' => 'Callsign is valid'];
        } else {
            return ['valid' => false, 'message' => "Callsign does not match"];
        }
    }

    return ['valid' => false, 'message' => 'DMR ID not found or invalid'];
}

// This function will validate the EchoLink ID
function validate_echolink_id($callsign, $echolink_id) {
    // URL for EchoLink validation
    $url = "https://www.echolink.org/validation/node_lookup.jsp";

    // Initialize cURL session
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
    curl_setopt($ch, CURLOPT_FAILONERROR, true);    // Fail on HTTP errors
    curl_setopt($ch, CURLOPT_POST, true);           // Use POST method
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'call' => $echolink_id // Send the echolink ID as the form input (form field is 'call')
    ]));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);          // Set a timeout for the request

    // Add a Referer header to simulate the form submission
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Referer: https://www.echolink.org/validation/node_lookup.jsp'
    ]);

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        //echo 'cURL error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }

    // Get the HTTP status code
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // If the HTTP status code is not 200, return false
    if ($http_code !== 200) {
        echo "API request failed with status code: $http_code";
        return false;
    }

    // Parse the response (full HTML page)
    return parse_echolink_response($response, $echolink_id, $callsign);
}

function parse_echolink_response($html, $echolink_id, $callsign) {
    // Load the HTML into DOMDocument
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Suppress HTML parsing errors
    $dom->loadHTML($html);
    libxml_clear_errors();

    // Use XPath to extract data from the table
    $xpath = new DOMXPath($dom);

    // Look for the "is not currently validated" message
    $not_validated_message = $xpath->query("//*[contains(text(), 'is not currently validated for EchoLink')]");
    if ($not_validated_message->length > 0) {
        // Debugging: print the validation failure message
        //echo "EchoLink ID $echolink_id is not validated.\n";
        return ['valid' => false];
    }

    // Look for the table rows containing the results (ignoring the header row)
    $table_rows = $xpath->query("//table//tr[position() > 1]");  // This will exclude the header row

    // Debugging: Print the number of rows found
    //echo "Number of rows in the table: " . $table_rows->length . "\n";

    // Ensure that we have at least 1 data row
    if ($table_rows->length > 0) {
        // Extract the first data row (since we're ignoring the header)
        $result_row = $table_rows->item(0);  // Get the first data row (not header)

        // Debugging: Print the raw HTML of the row
        //echo "Row raw HTML: " . $result_row->C14N() . "\n";  // This will print the entire HTML content of the row

        $cells = $result_row->getElementsByTagName('td');
        
        // Debugging: Print the number of cells in this row
        //echo "Number of cells in the row: " . $cells->length . "\n";

        // Check if we have the right number of cells (2 cells for Callsign and Node Number)
        if ($cells->length >= 2) {
            $api_callsign = trim($cells->item(0)->textContent);  // Callsign from the table
            $api_node_number = trim($cells->item(1)->textContent);  // Node number from the table

            // Debugging: print extracted callsign and node number
         //   echo "Extracted Callsign: $api_callsign\n";
         //   echo "Extracted Node Number: $api_node_number\n";

            // **Correcting the comparison logic**:
            // Ensure that we're comparing the correct values.
            if ($api_callsign === $callsign && $api_node_number === $echolink_id) {
        //        echo "Validation successful for EchoLink ID: $echolink_id and Callsign: $callsign.\n";
                return ['valid' => true, 'callsign' => $api_callsign, 'node_number' => $api_node_number];
            } else {
                // Debugging: print the failed comparison
         //       echo "Validation failed: Expected callsign $callsign, got $api_callsign. Expected EchoLink ID $echolink_id, got $api_node_number.\n";
            }
        } else {
        //    echo "Error: Expected 2 cells in the table row, but found " . $cells->length . " cells.\n";
        }
    } else {
        //    echo "Error: Table does not have any data rows. Rows found: " . $table_rows->length . "\n";
    }

    // If no valid result is found
    // echo "Unexpected response format for EchoLink ID $echolink_id.\n";
    return ['valid' => false];
}
function generate_reflector_password() {
    // Define the character set for the password
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    
    // Initialize password variable
    $password = '';
    
    // Get the total length of our character set
    $charLength = strlen($chars);
    
    // Generate 13 random characters
    for ($i = 0; $i < 13; $i++) {
        $password .= $chars[random_int(0, $charLength - 1)];
    }
    
    // Add double quotes to the password string
    $password = '"' . $password . '"';
    
    return $password;
}

function getSSHConnection($host, $port, $username, $publicKeyFile, $privateKeyFile) {
    $connection = ssh2_connect($host, $port);
    if (!$connection) {
        throw new Exception("Failed to connect to $host on port $port.");
    }

    if (!ssh2_auth_pubkey_file($connection, $username, $publicKeyFile, $privateKeyFile, '')) {
        throw new Exception("Failed to authenticate with the server using public key.");
    }

    return $connection;
}

function ssh2_download_file($connection, $remoteFile, $localFile) {
    if (!ssh2_scp_recv($connection, $remoteFile, $localFile)) {
        throw new Exception("Failed to download file $remoteFile.");
    }
}

function ssh2_upload_file($connection, $localFile, $remoteFile) {
    if (!ssh2_scp_send($connection, $localFile, $remoteFile)) {
        throw new Exception("Failed to upload file $localFile to $remoteFile.");
    }
}
//Parsing the file to read.
//Open Data File
function parse_svx_file($filePath) {
    $parsedData = [];
    $currentSection = null;

    $file = fopen($filePath, 'r');
    if ($file) {
        while (($line = fgets($file)) !== false) {
            $line = trim($line);

            // Ignore empty lines
            if ($line === '') {
                continue;
            }

            // Detect section headers (e.g., [GLOBAL], [USERS])
            if (preg_match('/^\[(.+)\]$/', $line, $matches)) {
                $currentSection = $matches[1];
                if (!isset($parsedData[$currentSection])) {
                    $parsedData[$currentSection] = [];
                }
            }
            // Treat comments and entries differently
            elseif (strpos($line, '#') === 0) {
                $parsedData[$currentSection][] = ['type' => 'comment', 'content' => $line];
            } else {
                $parsedData[$currentSection][] = ['type' => 'entry', 'content' => $line];
            }
        }
        fclose($file);
    } else {
        throw new Exception("Could not open file: $filePath");
    }

    return $parsedData;
}
//Add Data entry
function add_entry_to_section(&$parsedData, $section, $newEntry) {
    if (!isset($parsedData[$section])) {
        $parsedData[$section] = [];
    }
    $parsedData[$section][] = ['type' => 'entry', 'content' => $newEntry];
}
//Remove Data
function remove_entry_from_section(&$parsedData, $section, $entryKey) {
    if (isset($parsedData[$section])) {
        foreach ($parsedData[$section] as $key => $line) {
            if ($line['type'] === 'entry' && $line['content'] === $entryKey) {
                unset($parsedData[$section][$key]);
                $parsedData[$section] = array_values($parsedData[$section]); // Reindex the array
                break;
            }
        }
    }
}
//Edit Data
function edit_entry_in_section(&$parsedData, $section, $oldEntry, $newEntry) {
    if (isset($parsedData[$section])) {
        foreach ($parsedData[$section] as $key => $line) {
            if ($line['type'] === 'entry' && $line['content'] === $oldEntry) {
                $parsedData[$section][$key]['content'] = $newEntry;
                break;
            }
        }
    }
}
//Write Data
function write_svx_file($filePath, $parsedData) {
    $file = fopen($filePath, 'w');
    if ($file) {
        foreach ($parsedData as $section => $lines) {
            // Check if the section has valid entries
            if (!empty($section) && !empty($lines)) {
                // Write section header
                fwrite($file, "[$section]" . PHP_EOL);

                // Write all lines in the section
                foreach ($lines as $line) {
                    if (!empty($line['content'])) {
                        fwrite($file, $line['content'] . PHP_EOL);
                    }
                }

                // Add a blank line between sections
                fwrite($file, PHP_EOL);
            }
        }
        fclose($file);
    } else {
        throw new Exception("Could not open file for writing: $filePath");
    }
}


function ensure_connection(&$connection, $host, $port, $srv_username) {
    if (!$connection || !@ssh2_auth_password($connection, $srv_username)) {
        echo "Reconnecting to the server...\n";
        $connection = ssh2_connect($host, $port);
        if (!$connection) {
            throw new Exception("Failed to reconnect to the server.");
        }
        if (!ssh2_auth_password($connection, $srv_username)) {
            throw new Exception("Failed to authenticate after reconnecting.");
        }
        echo "Reconnected successfully.\n";
    }
}
function display_parsed_data($data) {
    foreach ($data as $section => $entries) {
        foreach ($entries as $entry) {
            // Output only the content of each entry on a separate line
            echo $entry['content'] . "\n";
        }
    }
}

// Call the function to display the output
display_parsed_data($parsedData);



// Call the function to display the output



function backup_reflector_file($connection, $remoteFile) {
    $backupFile = $remoteFile . '.bak';
    $command = "cp -f $remoteFile $backupFile";
    $backup = ssh2_exec($connection, $command);
    stream_set_blocking($backup, true);
    $output = stream_get_contents($backup);
    
    if ($backup === false) {
        throw new Exception("Failed to create backup file");
    }
    return true;
}

function check_existing_callsign($parsedData, $callsign) {
    if (isset($parsedData['USERS'])) {
        foreach ($parsedData['USERS'] as $entry) {
            if ($entry['type'] === 'entry') {
                $parts = explode('=', $entry['content']);
                if ($parts[0] === $callsign) {
                    return true;
                }
            }
        }
    }
    return false;
}
function mail_out($email, $callsign, $password) {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        //debugging
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer: $str");
        };
        // Server settings
        $mail->isSMTP();                            // Send using SMTP
        $mail->Host       = 'mail.qsos.uk';  // Replace with your SMTP host
        $mail->SMTPAuth   = true;                   // Enable SMTP authentication
        $mail->Username   = 'support@svxlink.uk';   // SMTP username
        $mail->Password   = '94,sNoMiD,?';         // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port       = 587;                    // TCP port to connect to

        // Recipients
        $mail->setFrom('support@svxlink.uk', 'SvxReflector Support');
        $mail->addAddress($email, $callsign);       // Add a recipient
        $mail->addReplyTo('support@svxlink.uk', 'Support Team');

        // Content
        $mail->isHTML(true);                        // Set email format to HTML
        $mail->Subject = 'SvxReflector Registration';
        $mail->Body    = "
            <html>
            <body>
            <p>Dear $callsign,</p>
            <p>The Svxlink Team are pleased to inform you that your callsign has been registered in the SvxReflector, with the following password:</p>
            <p><strong>$password</strong></p>
            <p>Please save it in a safe place.</p>
            <p>Open your dashboard, and authorise yourself, and edit svxlink.</p>
            <p>Scroll to the <strong>ReflectorLogic</strong> section and locate the line '<strong>AUTH_KEY</strong>', and copy it and paste it into the right-hand box. Ensure that the box between the AUTH_KEY and your pasted password is ticked.</p>
            <p>Scroll to the bottom of the configuration, and click on the large red button, to incorporate your password into the settings, and it will restart immediately.</p>
            <p>Best regards,</p>
            <p>The SvxReflector Team</p>
            </body>
            </html>
        ";
        $mail->AltBody = "Dear $callsign,\n\n"
            . "The Svxlink Team are pleased to inform you that your callsign has been registered in the SvxReflector, with the following password: $password\n\n"
            . "Please save it in a safe place.\n\n"
            . "Open your dashboard, and authorise yourself, and edit svxlink.\n"
            . "Scroll to the ReflectorLogic section and locate the line 'AUTH_KEY', and copy it and paste it into the right-hand box. Ensure that the box between the AUTH_KEY and your pasted password is ticked.\n"
            . "Scroll to the bottom of the configuration, and click on the large red button, to incorporate your password into the settings, and it will restart immediately.\n\n"
            . "Best regards,\n"
            . "The SvxReflector Team";

        // Send the email
        $mail->send();
        return true; // Email sent successfully
    } catch (Exception $e) {
        // Log the error message if needed
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false; // Email failed to send
    }
    // Notify support of new user
$mail = new PHPMailer(true);
$subject = "New User";
$message = "New User "$callsign" has registered on the svxreflector with password "$password; 

        $mail->isSMTP();
        $mail->Host = 'mail.qsos.uk'; // SMTP server address (e.g., mail.example.com)
        $mail->SMTPAuth = true;
        $mail->Username = 'support@svxlink.uk'; // SMTP username
        $mail->Password = '94,sNoMiD,?';    // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('support@qsos.uk', 'SVXLink Registration');
        $mail->addAddress('support@qsos.uk'); // Support email address

        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        // Send the email
        $mail->send();
        echo "<script>alert('Support Notified !'); window.location.href = 'index.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('There was an error with your registration: {$mail->ErrorInfo}'); window.location.href = 'i>
    }
}