<?php
// mailer.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include the Composer autoloader
require '/var/www/html/vendor/autoload.php';
$mail = new PHPMailer(true);
$subject = "Test Email";
$message = "This is a test email.";


    try {
        // Set up SMTP
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
        echo "<script>alert('Registration successful!'); window.location.href = 'index.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('There was an error with your registration: {$mail->ErrorInfo}'); window.location.href = 'index.php';</script>";
    }