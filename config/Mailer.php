<?php
/**
 * Mailer Class - PHPMailer Wrapper
 * AR Homes Posadas Farm Resort Reservation System
 * 
 * This class provides a simple interface for sending emails using PHPMailer
 * 
 * Note: If PHPMailer is not installed via Composer, download it manually:
 * https://github.com/PHPMailer/PHPMailer/releases
 * Extract to vendor/phpmailer/phpmailer/src/
 */

// Include PHPMailer files
// Attempt to load from vendor directory (Composer installation)
if (file_exists(__DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
} else {
    // Manual installation fallback
    if (file_exists(__DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php')) {
        require_once __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/../vendor/PHPMailer/src/SMTP.php';
        require_once __DIR__ . '/../vendor/PHPMailer/src/Exception.php';
    } else {
        throw new Exception('PHPMailer not found. Please install PHPMailer library.');
    }
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include database configuration first (for APP_NAME constant)
require_once __DIR__ . '/database.php';
// Include mail configuration
require_once __DIR__ . '/mail.php';

class Mailer {
    private $mail;
    private $error;

    /**
     * Constructor - Initialize PHPMailer
     */
    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->configureSMTP();
    }

    /**
     * Configure SMTP settings
     */
    private function configureSMTP() {
        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host       = MAIL_HOST;
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = MAIL_USERNAME;
            $this->mail->Password   = MAIL_PASSWORD;
            $this->mail->SMTPSecure = MAIL_ENCRYPTION;
            $this->mail->Port       = MAIL_PORT;
            $this->mail->CharSet    = 'UTF-8';

            // Enable verbose debug output (disable in production)
            // $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;

            // Set default from address
            $this->mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        } catch (Exception $e) {
            $this->error = "SMTP Configuration Error: {$e->getMessage()}";
            error_log($this->error);
        }
    }

    /**
     * Send password reset email
     * 
     * @param string $recipientEmail
     * @param string $recipientName
     * @param string $resetLink
     * @param string $expiresAt
     * @return bool
     */
    public function sendPasswordResetEmail($recipientEmail, $recipientName, $resetLink, $expiresAt) {
        try {
            // Recipients
            $this->mail->addAddress($recipientEmail, $recipientName);
            
            // Reply to
            $this->mail->addReplyTo(MAIL_FROM_EMAIL, MAIL_FROM_NAME);

            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Password Reset Request - ' . APP_NAME;
            
            // Email body
            $emailBody = $this->getPasswordResetTemplate($recipientName, $resetLink, $expiresAt);
            $this->mail->Body = $emailBody;
            
            // Alternative plain text body
            $this->mail->AltBody = $this->getPasswordResetPlainText($recipientName, $resetLink, $expiresAt);

            // Send email
            $result = $this->mail->send();
            
            // Clear all addresses and attachments for next email
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            
            return $result;

        } catch (Exception $e) {
            $this->error = "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
            error_log($this->error);
            return false;
        }
    }

    /**
     * Send email verification email
     * 
     * @param string $recipientEmail
     * @param string $recipientName
     * @param string $verificationLink
     * @param string $expiresAt
     * @return bool
     */
    public function sendEmailVerificationEmail($recipientEmail, $recipientName, $verificationLink, $expiresAt) {
        try {
            // Recipients
            $this->mail->addAddress($recipientEmail, $recipientName);
            
            // Reply to
            $this->mail->addReplyTo(MAIL_FROM_EMAIL, MAIL_FROM_NAME);

            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Verify Your Email Address - ' . APP_NAME;
            
            // Email body
            $emailBody = $this->getEmailVerificationTemplate($recipientName, $verificationLink, $expiresAt);
            $this->mail->Body = $emailBody;
            
            // Alternative plain text body
            $this->mail->AltBody = $this->getEmailVerificationPlainText($recipientName, $verificationLink, $expiresAt);

            // Send email
            $result = $this->mail->send();
            
            // Clear all addresses and attachments for next email
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            
            return $result;

        } catch (Exception $e) {
            $this->error = "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
            error_log($this->error);
            return false;
        }
    }

    /**
     * Send welcome/registration email
     * 
     * @param string $recipientEmail
     * @param string $recipientName
     * @param string $username
     * @return bool
     */
    public function sendWelcomeEmail($recipientEmail, $recipientName, $username) {
        try {
            // Recipients
            $this->mail->addAddress($recipientEmail, $recipientName);
            
            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Welcome to ' . APP_NAME;
            
            // Email body
            $emailBody = $this->getWelcomeEmailTemplate($recipientName, $username);
            $this->mail->Body = $emailBody;
            
            // Alternative plain text
            $this->mail->AltBody = "Welcome to " . APP_NAME . ", " . $recipientName . "! Your username is: " . $username;

            // Send email
            $result = $this->mail->send();
            
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            
            return $result;

        } catch (Exception $e) {
            $this->error = "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
            error_log($this->error);
            return false;
        }
    }

    /**
     * Get email verification email HTML template
     */
    private function getEmailVerificationTemplate($recipientName, $verificationLink, $expiresAt) {
        $expiryTime = date('F j, Y \a\t g:i A', strtotime($expiresAt));
        
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Verify Your Email Address</title>
            <style>
                body {
                    font-family: 'Arial', 'Helvetica', sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .email-container {
                    max-width: 600px;
                    margin: 30px auto;
                    background: #ffffff;
                    border-radius: 10px;
                    overflow: hidden;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }
                .email-header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: #ffffff;
                    padding: 40px 20px;
                    text-align: center;
                }
                .email-header h1 {
                    margin: 0;
                    font-size: 28px;
                    font-weight: 600;
                }
                .email-header .icon {
                    font-size: 60px;
                    margin-bottom: 15px;
                }
                .email-body {
                    padding: 40px 30px;
                }
                .email-body h2 {
                    color: #333;
                    font-size: 24px;
                    margin-bottom: 20px;
                }
                .email-body p {
                    margin-bottom: 15px;
                    font-size: 16px;
                    color: #666;
                }
                .verify-button {
                    display: inline-block;
                    padding: 15px 40px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: #ffffff !important;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: 600;
                    margin: 20px 0;
                    text-align: center;
                    font-size: 18px;
                }
                .verify-button:hover {
                    background: linear-gradient(135deg, #5568d3 0%, #6a408b 100%);
                }
                .info-box {
                    background-color: #f8f9fa;
                    border-left: 4px solid #667eea;
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 4px;
                }
                .warning-box {
                    background-color: #fff3cd;
                    border-left: 4px solid #ffc107;
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 4px;
                }
                .footer {
                    background-color: #f8f9fa;
                    padding: 20px;
                    text-align: center;
                    font-size: 14px;
                    color: #666;
                }
                .footer p {
                    margin: 5px 0;
                }
                .link-text {
                    word-break: break-all;
                    color: #667eea;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <div class='icon'>✉️</div>
                    <h1>" . APP_NAME . "</h1>
                    <p>Verify Your Email Address</p>
                </div>
                <div class='email-body'>
                    <h2>Welcome, " . htmlspecialchars($recipientName) . "! 🎉</h2>
                    <p>Thank you for registering with <strong>" . APP_NAME . "</strong>!</p>
                    <p>To complete your registration and access all features, please verify your email address by clicking the button below:</p>
                    
                    <center>
                        <a href='" . htmlspecialchars($verificationLink) . "' class='verify-button'>Verify Email Address</a>
                    </center>
                    
                    <div class='info-box'>
                        <p><strong>⏱ This link will expire on:</strong> " . $expiryTime . "</p>
                    </div>
                    
                    <p>If the button above doesn't work, copy and paste the following link into your browser:</p>
                    <p class='link-text'>" . htmlspecialchars($verificationLink) . "</p>
                    
                    <div class='warning-box'>
                        <p><strong>⚠ Important:</strong></p>
                        <p>You must verify your email before you can log in to your account. If you did not create this account, please ignore this email.</p>
                    </div>
                    
                    <p>Once verified, you'll be able to:</p>
                    <ul>
                        <li>Access your account dashboard</li>
                        <li>Make reservations</li>
                        <li>Manage your bookings</li>
                        <li>Reset your password if needed</li>
                    </ul>
                    
                    <p>We're excited to have you with us!</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " " . APP_NAME . ". All rights reserved.</p>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Get email verification plain text version
     */
    private function getEmailVerificationPlainText($recipientName, $verificationLink, $expiresAt) {
        $expiryTime = date('F j, Y \a\t g:i A', strtotime($expiresAt));
        
        return "
" . APP_NAME . "

Verify Your Email Address

Welcome, " . $recipientName . "!

Thank you for registering with " . APP_NAME . "!

To complete your registration and access all features, please verify your email address by clicking the link below:

" . $verificationLink . "

This link will expire on: " . $expiryTime . "

IMPORTANT:
You must verify your email before you can log in to your account. If you did not create this account, please ignore this email.

Once verified, you'll be able to access your account dashboard, make reservations, manage your bookings, and reset your password if needed.

We're excited to have you with us!

---
© " . date('Y') . " " . APP_NAME . ". All rights reserved.
This is an automated message, please do not reply to this email.
        ";
    }

    /**
     * Get password reset email HTML template
     */
    private function getPasswordResetTemplate($recipientName, $resetLink, $expiresAt) {
        $expiryTime = date('F j, Y \a\t g:i A', strtotime($expiresAt));
        
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Password Reset Request</title>
            <style>
                body {
                    font-family: 'Arial', 'Helvetica', sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .email-container {
                    max-width: 600px;
                    margin: 30px auto;
                    background: #ffffff;
                    border-radius: 10px;
                    overflow: hidden;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }
                .email-header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: #ffffff;
                    padding: 30px 20px;
                    text-align: center;
                }
                .email-header h1 {
                    margin: 0;
                    font-size: 28px;
                    font-weight: 600;
                }
                .email-body {
                    padding: 40px 30px;
                }
                .email-body h2 {
                    color: #333;
                    font-size: 24px;
                    margin-bottom: 20px;
                }
                .email-body p {
                    margin-bottom: 15px;
                    font-size: 16px;
                    color: #666;
                }
                .reset-button {
                    display: inline-block;
                    padding: 15px 40px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: #ffffff !important;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: 600;
                    margin: 20px 0;
                    text-align: center;
                }
                .reset-button:hover {
                    background: linear-gradient(135deg, #5568d3 0%, #6a408b 100%);
                }
                .info-box {
                    background-color: #f8f9fa;
                    border-left: 4px solid #667eea;
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 4px;
                }
                .warning-box {
                    background-color: #fff3cd;
                    border-left: 4px solid #ffc107;
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 4px;
                }
                .footer {
                    background-color: #f8f9fa;
                    padding: 20px;
                    text-align: center;
                    font-size: 14px;
                    color: #666;
                }
                .footer p {
                    margin: 5px 0;
                }
                .link-text {
                    word-break: break-all;
                    color: #667eea;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <h1>" . APP_NAME . "</h1>
                </div>
                <div class='email-body'>
                    <h2>Password Reset Request</h2>
                    <p>Hello <strong>" . htmlspecialchars($recipientName) . "</strong>,</p>
                    <p>We received a request to reset your password for your account at " . APP_NAME . ".</p>
                    <p>Click the button below to reset your password:</p>
                    
                    <center>
                        <a href='" . htmlspecialchars($resetLink) . "' class='reset-button'>Reset Password</a>
                    </center>
                    
                    <div class='info-box'>
                        <p><strong>⏱ This link will expire on:</strong> " . $expiryTime . "</p>
                    </div>
                    
                    <p>If the button above doesn't work, copy and paste the following link into your browser:</p>
                    <p class='link-text'>" . htmlspecialchars($resetLink) . "</p>
                    
                    <div class='warning-box'>
                        <p><strong>⚠ Security Notice:</strong></p>
                        <p>If you did not request a password reset, please ignore this email. Your password will remain unchanged.</p>
                    </div>
                    
                    <p>For security reasons, this link will expire after 1 hour.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " " . APP_NAME . ". All rights reserved.</p>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Get password reset plain text version
     */
    private function getPasswordResetPlainText($recipientName, $resetLink, $expiresAt) {
        $expiryTime = date('F j, Y \a\t g:i A', strtotime($expiresAt));
        
        return "
" . APP_NAME . "

Password Reset Request

Hello " . $recipientName . ",

We received a request to reset your password for your account at " . APP_NAME . ".

Click the link below to reset your password:
" . $resetLink . "

This link will expire on: " . $expiryTime . "

SECURITY NOTICE:
If you did not request a password reset, please ignore this email. Your password will remain unchanged.

For security reasons, this link will expire after 1 hour.

---
© " . date('Y') . " " . APP_NAME . ". All rights reserved.
This is an automated message, please do not reply to this email.
        ";
    }

    /**
     * Get welcome email template
     */
    private function getWelcomeEmailTemplate($recipientName, $username) {
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Welcome to " . APP_NAME . "</title>
            <style>
                body {
                    font-family: 'Arial', 'Helvetica', sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .email-container {
                    max-width: 600px;
                    margin: 30px auto;
                    background: #ffffff;
                    border-radius: 10px;
                    overflow: hidden;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }
                .email-header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: #ffffff;
                    padding: 40px 20px;
                    text-align: center;
                }
                .email-header h1 {
                    margin: 0 0 10px 0;
                    font-size: 32px;
                }
                .email-body {
                    padding: 40px 30px;
                }
                .email-body h2 {
                    color: #333;
                    font-size: 24px;
                    margin-bottom: 20px;
                }
                .email-body p {
                    margin-bottom: 15px;
                    font-size: 16px;
                    color: #666;
                }
                .info-box {
                    background-color: #f8f9fa;
                    border-left: 4px solid #667eea;
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 4px;
                }
                .footer {
                    background-color: #f8f9fa;
                    padding: 20px;
                    text-align: center;
                    font-size: 14px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <h1>Welcome! 🎉</h1>
                    <p>Your account has been created successfully</p>
                </div>
                <div class='email-body'>
                    <h2>Welcome to " . APP_NAME . "</h2>
                    <p>Hello <strong>" . htmlspecialchars($recipientName) . "</strong>,</p>
                    <p>Thank you for registering with us! We're excited to have you on board.</p>
                    
                    <div class='info-box'>
                        <p><strong>Your Account Details:</strong></p>
                        <p>Username: <strong>" . htmlspecialchars($username) . "</strong></p>
                    </div>
                    
                    <p>You can now log in and start exploring our services.</p>
                    <p>If you have any questions or need assistance, feel free to contact us.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " " . APP_NAME . ". All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Get last error message
     */
    public function getError() {
        return $this->error;
    }
}
?>
