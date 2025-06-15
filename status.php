<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the content type to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow cross-origin requests if needed

// Remote status source URL
$remoteStatusUrl = "http://portal.svxlink.uk:8181/status";

// Validate the URL
if (!filter_var($remoteStatusUrl, FILTER_VALIDATE_URL)) {
    echo json_encode([
        "error" => "Invalid remote status URL."
    ]);
    exit;
}

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $remoteStatusUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout after 10 seconds
curl_setopt($ch, CURLOPT_FAILONERROR, true); // Treat HTTP errors as failures

// Execute the cURL request
$jsonData = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Check for cURL execution errors
if ($curl_error) {
    echo json_encode([
        "error" => "cURL error occurred.",
        "details" => $curl_error,
        "http_status" => $http_status
    ]);
    exit;
}

// Check the HTTP status code and validate the JSON
if ($http_status === 200 && $jsonData) {
    // Validate the JSON structure
    $decodedData = json_decode($jsonData, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        // Output the valid JSON data
        echo json_encode($decodedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } else {
        echo json_encode([
            "error" => "Invalid JSON received from the remote source.",
            "json_error" => json_last_error_msg()
        ]);
    }
} else {
    // Handle HTTP errors or no data received
    echo json_encode([
        "error" => "Failed to fetch data from the remote source.",
        "http_status" => $http_status,
        "details" => $http_status !== 200 ? "HTTP error." : "No data returned."
    ]);
}
