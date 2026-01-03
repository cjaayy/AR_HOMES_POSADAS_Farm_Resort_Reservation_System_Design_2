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


// Use Composer autoloader for PHPMailer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    throw new Exception('Composer autoload not found. Please run composer install.');
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
     * Send email change verification email
     * 
     * @param string $newEmail - The new email address to verify
     * @param string $recipientName
     * @param string $verificationLink
     * @param string $expiresAt
     * @param string $oldEmail - The current/old email address
     * @return bool
     */
    public function sendEmailChangeVerificationEmail($newEmail, $recipientName, $verificationLink, $expiresAt, $oldEmail) {
        try {
            // Send to the NEW email address
            $this->mail->addAddress($newEmail, $recipientName);
            
            // Reply to
            $this->mail->addReplyTo(MAIL_FROM_EMAIL, MAIL_FROM_NAME);

            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Verify Your New Email Address - ' . APP_NAME;
            
            // Email body
            $emailBody = $this->getEmailChangeVerificationTemplate($recipientName, $verificationLink, $expiresAt, $oldEmail, $newEmail);
            $this->mail->Body = $emailBody;
            
            // Alternative plain text body
            $this->mail->AltBody = $this->getEmailChangeVerificationPlainText($recipientName, $verificationLink, $expiresAt, $oldEmail, $newEmail);

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
     * Send staff password reset email (admin-initiated)
     * 
     * @param string $recipientEmail
     * @param string $recipientName
     * @param string $newPassword
     * @param string $username
     * @return bool
     */
    public function sendStaffPasswordResetEmail($recipientEmail, $recipientName, $newPassword, $username) {
        try {
            // Recipients
            $this->mail->addAddress($recipientEmail, $recipientName);
            
            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Your Password Has Been Reset - ' . APP_NAME;
            
            // Email body
            $emailBody = $this->getStaffPasswordResetTemplate($recipientName, $newPassword, $username);
            $this->mail->Body = $emailBody;
            
            // Alternative plain text
            $this->mail->AltBody = "Hello {$recipientName},\n\nYour password for " . APP_NAME . " staff portal has been reset by an administrator.\n\nUsername: {$username}\nNew Password: {$newPassword}\n\nPlease login and change your password immediately.\n\nBest regards,\n" . APP_NAME . " Team";

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
     * Get staff password reset HTML template (admin-initiated)
     */
    private function getStaffPasswordResetTemplate($recipientName, $newPassword, $username) {
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Password Reset</title>
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
                .credentials-box {
                    background-color: #f8f9fa;
                    border: 2px solid #667eea;
                    padding: 20px;
                    margin: 25px 0;
                    border-radius: 8px;
                    text-align: center;
                }
                .credentials-box .label {
                    font-size: 14px;
                    color: #666;
                    margin-bottom: 5px;
                }
                .credentials-box .value {
                    font-size: 18px;
                    font-weight: 600;
                    color: #333;
                    background: #fff;
                    padding: 10px 15px;
                    border-radius: 5px;
                    display: inline-block;
                    margin: 5px 0 15px 0;
                    border: 1px solid #ddd;
                    font-family: 'Courier New', monospace;
                    letter-spacing: 1px;
                }
                .warning-box {
                    background-color: #fff3cd;
                    border-left: 4px solid #ffc107;
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 4px;
                }
                .warning-box strong {
                    color: #856404;
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
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <div class='icon'>🔐</div>
                    <h1>" . APP_NAME . "</h1>
                    <p>Staff Password Reset</p>
                </div>
                <div class='email-body'>
                    <h2>Hello, {$recipientName}!</h2>
                    <p>Your password for the staff portal has been reset by an administrator. Please use the credentials below to log in:</p>
                    
                    <div class='credentials-box'>
                        <div class='label'>Username</div>
                        <div class='value'>{$username}</div>
                        <div class='label'>New Password</div>
                        <div class='value'>{$newPassword}</div>
                    </div>
                    
                    <div class='warning-box'>
                        <strong>⚠️ Important:</strong> For security reasons, please change your password immediately after logging in. Go to Settings → Change Password.
                    </div>
                    
                    <p>If you did not expect this password reset, please contact your administrator immediately.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from " . APP_NAME . "</p>
                    <p>Please do not reply to this email.</p>
                    <p>&copy; " . date('Y') . " " . APP_NAME . ". All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
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
     * Get email change verification email HTML template
     */
    private function getEmailChangeVerificationTemplate($recipientName, $verificationLink, $expiresAt, $oldEmail, $newEmail) {
        $expiryTime = date('F j, Y \a\t g:i A', strtotime($expiresAt));
        
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Verify Your New Email Address</title>
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
                .email-display {
                    font-weight: 600;
                    color: #667eea;
                    background: #f0f0ff;
                    padding: 3px 10px;
                    border-radius: 3px;
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <div style='font-size: 60px; margin-bottom: 15px;'>📧</div>
                    <h1>" . APP_NAME . "</h1>
                </div>
                <div class='email-body'>
                    <h2>Verify Your New Email Address</h2>
                    <p>Hello, <strong>" . htmlspecialchars($recipientName) . "</strong>!</p>
                    <p>A request was made to change your email address for your " . APP_NAME . " account.</p>
                    
                    <div class='info-box'>
                        <p><strong>Email Change Details:</strong></p>
                        <p>Current email: <span class='email-display'>" . htmlspecialchars($oldEmail) . "</span></p>
                        <p>New email: <span class='email-display'>" . htmlspecialchars($newEmail) . "</span></p>
                    </div>
                    
                    <p>To confirm this change, please click the button below:</p>
                    
                    <div style='text-align: center;'>
                        <a href='" . $verificationLink . "' class='verify-button'>Verify New Email</a>
                    </div>
                    
                    <div class='warning-box'>
                        <p><strong>⚠️ Important:</strong></p>
                        <p>• This link expires on: <strong>" . $expiryTime . "</strong></p>
                        <p>• Until you verify, you will continue using your current email.</p>
                        <p>• If you did not request this change, please ignore this email or contact support.</p>
                    </div>
                    
                    <p style='font-size: 14px; color: #888;'>If the button doesn't work, copy and paste this link:</p>
                    <p style='font-size: 12px; word-break: break-all; color: #667eea;'>" . $verificationLink . "</p>
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
     * Get email change verification plain text version
     */
    private function getEmailChangeVerificationPlainText($recipientName, $verificationLink, $expiresAt, $oldEmail, $newEmail) {
        $expiryTime = date('F j, Y \a\t g:i A', strtotime($expiresAt));
        
        return "
" . APP_NAME . "

Verify Your New Email Address

Hello, " . $recipientName . "!

A request was made to change your email address for your " . APP_NAME . " account.

Email Change Details:
- Current email: " . $oldEmail . "
- New email: " . $newEmail . "

To confirm this change, please click the link below:

" . $verificationLink . "

This link will expire on: " . $expiryTime . "

IMPORTANT:
- Until you verify, you will continue using your current email address.
- If you did not request this change, please ignore this email or contact support immediately.

---
© " . date('Y') . " " . APP_NAME . ". All rights reserved.
This is an automated message, please do not reply to this email.
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
     * Send booking confirmation email
     * 
     * @param string $recipientEmail
     * @param string $recipientName
     * @param array $reservationDetails
     * @return bool
     */
    public function sendBookingConfirmationEmail($recipientEmail, $recipientName, $reservationDetails) {
        try {
            $this->mail->addAddress($recipientEmail, $recipientName);
            $this->mail->addReplyTo(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Booking Confirmed - Reservation #' . $reservationDetails['reservation_id'] . ' - ' . APP_NAME;
            
            $emailBody = $this->getBookingConfirmationTemplate($recipientName, $reservationDetails);
            $this->mail->Body = $emailBody;
            
            $this->mail->AltBody = $this->getBookingConfirmationPlainText($recipientName, $reservationDetails);
            
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
     * Get booking confirmation email HTML template
     */
    private function getBookingConfirmationTemplate($recipientName, $details) {
        $checkInDate = date('F j, Y', strtotime($details['check_in_date']));
        $checkOutDate = isset($details['check_out_date']) ? date('F j, Y', strtotime($details['check_out_date'])) : 'N/A';
        $checkInTime = date('g:i A', strtotime($details['check_in_time']));
        $checkOutTime = date('g:i A', strtotime($details['check_out_time']));
        
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Booking Confirmed</title>
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
                    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                    color: #ffffff;
                    padding: 40px 20px;
                    text-align: center;
                }
                .email-header .icon {
                    font-size: 60px;
                    margin-bottom: 15px;
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
                    color: #28a745;
                    font-size: 24px;
                    margin-bottom: 20px;
                }
                .email-body p {
                    margin-bottom: 15px;
                    font-size: 16px;
                    color: #666;
                }
                .success-box {
                    background-color: #d4edda;
                    border-left: 4px solid #28a745;
                    padding: 20px;
                    margin: 20px 0;
                    border-radius: 4px;
                }
                .info-section {
                    background-color: #f8f9fa;
                    padding: 20px;
                    margin: 20px 0;
                    border-radius: 8px;
                }
                .info-row {
                    display: flex;
                    justify-content: space-between;
                    padding: 10px 0;
                    border-bottom: 1px solid #e0e0e0;
                }
                .info-row:last-child {
                    border-bottom: none;
                }
                .info-label {
                    font-weight: 600;
                    color: #333;
                }
                .info-value {
                    color: #666;
                    text-align: right;
                }
                .total-row {
                    background-color: #667eea;
                    color: white;
                    padding: 15px 20px;
                    margin: 20px 0;
                    border-radius: 8px;
                    font-size: 18px;
                    font-weight: 600;
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
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <div class='icon'>✅</div>
                    <h1>Booking Confirmed!</h1>
                    <p>Your reservation has been approved</p>
                </div>
                <div class='email-body'>
                    <div class='success-box'>
                        <h2 style='margin: 0 0 10px 0; color: #28a745;'>🎉 Congratulations!</h2>
                        <p style='margin: 0; font-size: 18px; color: #333;'>Your payment has been verified and your reservation is now <strong>CONFIRMED</strong>!</p>
                    </div>
                    
                    <p>Dear <strong>" . htmlspecialchars($recipientName) . "</strong>,</p>
                    <p>Great news! Your downpayment has been verified by our team, and your reservation is now confirmed and locked.</p>
                    
                    <div class='info-section'>
                        <h3 style='margin-top: 0; color: #333;'>📋 Reservation Details</h3>
                        <div class='info-row'>
                            <span class='info-label'>Reservation ID:</span>
                            <span class='info-value'>#" . htmlspecialchars($details['reservation_id']) . "</span>
                        </div>
                        <div class='info-row'>
                            <span class='info-label'>Booking Type:</span>
                            <span class='info-value'>" . htmlspecialchars(ucfirst($details['booking_type'])) . "</span>
                        </div>
                        <div class='info-row'>
                            <span class='info-label'>Package:</span>
                            <span class='info-value'>" . htmlspecialchars($details['package_type']) . "</span>
                        </div>
                        <div class='info-row'>
                            <span class='info-label'>Check-in Date:</span>
                            <span class='info-value'>" . $checkInDate . "</span>
                        </div>
                        <div class='info-row'>
                            <span class='info-label'>Check-in Time:</span>
                            <span class='info-value'>" . $checkInTime . "</span>
                        </div>
                        <div class='info-row'>
                            <span class='info-label'>Check-out Date:</span>
                            <span class='info-value'>" . $checkOutDate . "</span>
                        </div>
                        <div class='info-row'>
                            <span class='info-label'>Check-out Time:</span>
                            <span class='info-value'>" . $checkOutTime . "</span>
                        </div>
                        <div class='info-row'>
                            <span class='info-label'>Number of Guests:</span>
                            <span class='info-value'>" . htmlspecialchars($details['number_of_guests']) . "</span>
                        </div>
                    </div>
                    
                    <div class='total-row'>
                        <div style='display: flex; justify-content: space-between;'>
                            <span>Total Amount:</span>
                            <span>₱" . number_format($details['total_amount'], 2) . "</span>
                        </div>
                        <div style='display: flex; justify-content: space-between; font-size: 14px; font-weight: normal; margin-top: 5px;'>
                            <span>Downpayment Paid:</span>
                            <span>₱" . number_format($details['downpayment_amount'], 2) . "</span>
                        </div>
                        <div style='display: flex; justify-content: space-between; font-size: 14px; font-weight: normal; margin-top: 5px;'>
                            <span>Balance Due:</span>
                            <span>₱" . number_format($details['remaining_balance'], 2) . "</span>
                        </div>
                    </div>
                    
                    <div class='warning-box'>
                        <p style='margin: 0 0 10px 0;'><strong>⚠️ Important Reminders:</strong></p>
                        <ul style='margin: 0; padding-left: 20px;'>
                            <li>Your date is now <strong>LOCKED</strong> and secured exclusively for you</li>
                            <li>Please pay the remaining balance of <strong>₱" . number_format($details['remaining_balance'], 2) . "</strong> before check-in</li>
                            <li>Don't forget to bring this confirmation and a valid ID</li>
                            <li>Security bond: ₱" . number_format($details['security_bond'], 2) . " (refundable)</li>
                        </ul>
                    </div>
                    
                    <p><strong>What to bring:</strong></p>
                    <ul>
                        <li>This booking confirmation (printed or digital)</li>
                        <li>Valid government-issued ID</li>
                        <li>Remaining balance payment</li>
                        <li>Security bond (cash)</li>
                    </ul>
                    
                    <p>If you have any questions or need to make changes to your reservation, please contact us at:</p>
                    <p><strong>📞 Phone:</strong> +63 917 123 4567<br>
                    <strong>✉️ Email:</strong> " . MAIL_FROM_EMAIL . "</p>
                    
                    <p>We look forward to welcoming you!</p>
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
     * Get booking confirmation plain text version
     */
    private function getBookingConfirmationPlainText($recipientName, $details) {
        $checkInDate = date('F j, Y', strtotime($details['check_in_date']));
        $checkOutDate = isset($details['check_out_date']) ? date('F j, Y', strtotime($details['check_out_date'])) : 'N/A';
        
        return "
" . APP_NAME . "

BOOKING CONFIRMED!

Dear " . $recipientName . ",

Great news! Your payment has been verified and your reservation is now CONFIRMED and LOCKED!

Reservation Details:
- Reservation ID: #" . $details['reservation_id'] . "
- Booking Type: " . ucfirst($details['booking_type']) . "
- Package: " . $details['package_type'] . "
- Check-in: " . $checkInDate . " at " . date('g:i A', strtotime($details['check_in_time'])) . "
- Check-out: " . $checkOutDate . " at " . date('g:i A', strtotime($details['check_out_time'])) . "
- Guests: " . $details['number_of_guests'] . "

Payment Summary:
- Total Amount: ₱" . number_format($details['total_amount'], 2) . "
- Downpayment Paid: ₱" . number_format($details['downpayment_amount'], 2) . "
- Balance Due: ₱" . number_format($details['remaining_balance'], 2) . "
- Security Bond: ₱" . number_format($details['security_bond'], 2) . "

IMPORTANT REMINDERS:
- Your date is now LOCKED and secured exclusively for you
- Please pay the remaining balance before check-in
- Bring this confirmation and a valid ID
- Security bond is refundable

Contact us:
Phone: +63 917 123 4567
Email: " . MAIL_FROM_EMAIL . "

We look forward to welcoming you!

---
© " . date('Y') . " " . APP_NAME . ". All rights reserved.
        ";
    }

    /**
     * Send cancellation email to user
     * 
     * @param string $recipientEmail
     * @param string $recipientName
     * @param array $reservationDetails
     * @return bool
     */
    public function sendCancellationEmail($recipientEmail, $recipientName, $reservationDetails) {
        try {
            $this->mail->addAddress($recipientEmail, $recipientName);
            $this->mail->addReplyTo(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Reservation Cancelled - #' . $reservationDetails['reservation_id'] . ' - ' . APP_NAME;
            
            $emailBody = $this->getCancellationTemplate($recipientName, $reservationDetails);
            $this->mail->Body = $emailBody;
            
            $this->mail->AltBody = $this->getCancellationPlainText($recipientName, $reservationDetails);
            
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
     * Get cancellation email HTML template
     */
    private function getCancellationTemplate($recipientName, $details) {
        $checkInDate = date('F j, Y', strtotime($details['check_in_date']));
        $checkOutDate = isset($details['check_out_date']) ? date('F j, Y', strtotime($details['check_out_date'])) : 'N/A';
        
        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Reservation Cancelled</title>
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
                    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                    color: #ffffff;
                    padding: 40px 20px;
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
                    color: #ef4444;
                    font-size: 24px;
                    margin-bottom: 20px;
                }
                .email-body p {
                    margin-bottom: 15px;
                    font-size: 16px;
                    color: #666;
                }
                .warning-box {
                    background-color: #fef3c7;
                    border-left: 4px solid #f59e0b;
                    padding: 20px;
                    margin: 20px 0;
                    border-radius: 4px;
                }
                .refund-box {
                    background-color: #dcfce7;
                    border-left: 4px solid #22c55e;
                    padding: 20px;
                    margin: 20px 0;
                    border-radius: 4px;
                }
                .info-section {
                    background-color: #f8f9fa;
                    padding: 20px;
                    border-radius: 8px;
                    margin: 20px 0;
                }
                .info-row {
                    display: flex;
                    justify-content: space-between;
                    padding: 10px 0;
                    border-bottom: 1px solid #eee;
                }
                .info-row:last-child {
                    border-bottom: none;
                }
                .email-footer {
                    background-color: #11224e;
                    color: #ffffff;
                    padding: 30px;
                    text-align: center;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <h1>❌ Reservation Cancelled</h1>
                </div>
                
                <div class='email-body'>
                    <h2>Hello, {$recipientName}!</h2>
                    
                    <p>We regret to inform you that your reservation has been <strong>cancelled by our admin/staff</strong>.</p>
                    
                    <div class='warning-box'>
                        <strong style='color: #92400e;'>Reservation Cancelled</strong>
                        <p style='margin: 10px 0 0 0; color: #78350f;'>Reservation ID: <strong>#{$details['reservation_id']}</strong></p>
                    </div>
                    
                    <div class='info-section'>
                        <h3 style='margin-top: 0; color: #333;'>Cancelled Reservation Details</h3>
                        <div class='info-row'>
                            <span>Check-in Date:</span>
                            <strong>{$checkInDate}</strong>
                        </div>
                        <div class='info-row'>
                            <span>Check-out Date:</span>
                            <strong>{$checkOutDate}</strong>
                        </div>
                        <div class='info-row'>
                            <span>Booking Type:</span>
                            <strong>" . ucfirst($details['booking_type'] ?? 'N/A') . "</strong>
                        </div>
                        <div class='info-row'>
                            <span>Total Amount:</span>
                            <strong>₱" . number_format($details['total_amount'] ?? 0, 2) . "</strong>
                        </div>
                    </div>
                    
                    <div class='refund-box'>
                        <strong style='color: #166534;'>💰 Payment Refundable</strong>
                        <p style='margin: 10px 0 0 0; color: #166534;'>Your payment is eligible for a refund. Our staff may also re-approve this reservation within 24 hours. If you have any questions, please contact us.</p>
                    </div>
                    
                    <p>If you believe this cancellation was made in error, please contact us immediately.</p>
                    
                    <p style='margin-top: 30px;'>
                        <strong>Contact Us:</strong><br>
                        Phone: +63 917 123 4567<br>
                        Email: " . MAIL_FROM_EMAIL . "
                    </p>
                </div>
                
                <div class='email-footer'>
                    <p style='margin: 0;'>© " . date('Y') . " " . APP_NAME . ". All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Get cancellation email plain text version
     */
    private function getCancellationPlainText($recipientName, $details) {
        $checkInDate = date('F j, Y', strtotime($details['check_in_date']));
        $checkOutDate = isset($details['check_out_date']) ? date('F j, Y', strtotime($details['check_out_date'])) : 'N/A';
        
        return "
" . APP_NAME . "

RESERVATION CANCELLED

Dear " . $recipientName . ",

We regret to inform you that your reservation has been CANCELLED by our admin/staff.

Cancelled Reservation Details:
- Reservation ID: #" . $details['reservation_id'] . "
- Booking Type: " . ucfirst($details['booking_type'] ?? 'N/A') . "
- Check-in: " . $checkInDate . "
- Check-out: " . $checkOutDate . "
- Total Amount: ₱" . number_format($details['total_amount'] ?? 0, 2) . "

PAYMENT REFUNDABLE:
Your payment is eligible for a refund. Our staff may also re-approve this reservation within 24 hours. If you have any questions, please contact us.

If you believe this cancellation was made in error, please contact us immediately.

Contact us:
Phone: +63 917 123 4567
Email: " . MAIL_FROM_EMAIL . "

---
© " . date('Y') . " " . APP_NAME . ". All rights reserved.
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
