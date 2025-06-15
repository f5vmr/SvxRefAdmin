<?php
// Initialize any PHP variables or session handling here
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVXLink Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>SVXLink User Registration</h1>
        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert <?php echo $_SESSION['message_type']; ?>">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>
        <p class="welcome-message">Welcome to the SVXLink registration page.<br>Please fill in the form below to register.</p>
        <form action="process_registration.php" method="POST" onsubmit="convertCallSignToUppercase()" >
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" required>

            <label for="callsign">Ham Radio Callsign:</label>
            <input type="text" id="callsign" name="callsign" required style="text-transform: uppercase;">
            <label for="repeater">Is this a repeater?:</label>
            <div class="radio-group">
            <input type="radio" id="repeater_yes" name="repeater" value="1" required>
            <label for="repeater_yes">Yes</label>
            <input type="radio" id="repeater_no" name="repeater" value="0" checked required>
            <label for="repeater_no">No</label>
            </div>


            <label for="dmr_id">Either DMR ID:</label>
            <input type="text" id="dmr_id" name="dmr_id">

            <label for="echolink_id">or Echolink ID:</label>
            <input type="text" id="echolink_id" name="echolink_id">
            
            <button type="submit">Register</button>

            <td>
            <pre>
            If you have neither a DMR ID or Echolink ID, 
               then please email 'support@svxlink.uk'.
            </pre>
            </td>

            
        </form>
        <script>
        function convertCallSignToUppercase() {
        // Get the callsign input field
        var callsignField = document.getElementById("callsign");

        // Convert the value to uppercase
        callsignField.value = callsignField.value.toUpperCase();
        }
        </script>
        
        <div class="portals">
            <br>
            <h2>SvxLink Dashboards</h2>
            <div class="portal-links">
                <div class="portal">
                    <a href="https://portal.svxlink.uk:8443" target="_blank">
                        <img src="images/uk-wide-logo.png" alt="UK Wide Portal" class="portal-logo">
                        <p>UK Wide</p>
                    </a>
                </div>
                <div class="portal">
                    <a href="https://yorkshire.svxlink.uk:8443" target="_blank">
                        <img src="images/yorkshire-svx-logo.png" alt="YorkshireSVX Portal" class="portal-logo">
                        <p>YorkshireSVX</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
