<?php
include 'send_email.php';  // Include the email function

$test_email = "arunkumar19062003@gmail.com";  // Replace with your email
$subject = "Test Email from Alumni System";
$message = "Hello,<br><br>This is a test email to verify SMTP configuration.<br><br>Regards,<br><b>Alumni Management Team</b>";

if (sendEmail($test_email, $subject, $message)) {
    echo "✅ Test email sent successfully to $test_email!";
} else {
    echo "❌ Failed to send test email.";
}
?>