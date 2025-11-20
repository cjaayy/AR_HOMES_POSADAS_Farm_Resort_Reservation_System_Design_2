<?php
/**
 * Mail Configuration for PHPMailer
 * AR Homes Posadas Farm Resort Reservation System
 * 
 * INSTRUCTIONS:
 * 1. Copy this file to mail.php
 * 2. Update the values below with your actual SMTP credentials
 * 3. Never commit mail.php to git (it's in .gitignore)
 * 
 * For Gmail:
 * - Enable 2-factor authentication
 * - Generate an App Password: https://myaccount.google.com/apppasswords
 * - Use the App Password for MAIL_PASSWORD
 */

define('MAIL_HOST', 'smtp.gmail.com'); // SMTP server
define('MAIL_USERNAME', 'your-email@gmail.com'); // SMTP username (your Gmail address)
define('MAIL_PASSWORD', 'your-app-password-here'); // SMTP password (your Gmail App Password)
define('MAIL_ENCRYPTION', 'tls'); // Encryption: 'tls' or 'ssl'
define('MAIL_PORT', 587); // SMTP port (587 for TLS, 465 for SSL)
define('MAIL_FROM_EMAIL', 'your-email@gmail.com'); // From email address
define('MAIL_FROM_NAME', 'AR Homes Posadas Farm Resort'); // From name
?>
