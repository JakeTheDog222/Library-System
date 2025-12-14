<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailHelper {
    private $fromEmail = 'angelobadi124@gmail.com';
    private $fromName = 'LIBRARY BOOK BORROWING SYSTEM';
    private $mailer;

    public function __construct() {
        // Try to use PHPMailer if available, otherwise fall back to mail()
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $this->mailer = new PHPMailer(true);
            $this->setupPHPMailer();
        } else {
            // Configure SMTP settings for mail() function (fallback)
            ini_set('SMTP', 'smtp.gmail.com');
            ini_set('smtp_port', 587);
            ini_set('sendmail_from', 'angelobadi124@gmail.com');
        }
    }

    private function setupPHPMailer() {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = 'smtp.gmail.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = 'angelobadi124@gmail.com';
            $this->mailer->Password = ''; // You need to set your Gmail app password here
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = 587;

            // Recipients
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
        } catch (Exception $e) {
            error_log('PHPMailer setup failed: ' . $this->mailer->ErrorInfo);
        }
    }

    public function sendEmail($to, $subject, $body, $altBody = '') {
        if ($this->mailer) {
            // Use PHPMailer
            try {
                $this->mailer->clearAddresses();
                $this->mailer->addAddress($to);
                $this->mailer->isHTML(true);
                $this->mailer->Subject = $subject;
                $this->mailer->Body = $body;
                if ($altBody) {
                    $this->mailer->AltBody = $altBody;
                }

                $this->mailer->send();
                return true;
            } catch (Exception $e) {
                error_log('Email sending failed using PHPMailer: ' . $this->mailer->ErrorInfo);
                return false;
            }
        } else {
            // Fallback to mail() function
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: {$this->fromName} <{$this->fromEmail}>" . "\r\n";

            $message = $body;

            if (mail($to, $subject, $message, $headers)) {
                return true;
            } else {
                error_log('Email sending failed using mail() function');
                return false;
            }
        }
    }

    // Pre-built Email Templates

    public function verificationEmail($to, $verificationLink, $fullName) {
        $subject = 'Please verify your email address - WMSU Library';
        $body = "
        <h1>Email Verification</h1>
        <p>Hi {$fullName},</p>
        <p>Thank you for registering at WMSU Library. Please verify your email by clicking the link below:</p>
        <p><a href='{$verificationLink}'>Verify Email</a></p>
        <p>If you did not create an account, please ignore this email.</p>
        <p>Best regards,<br>WMSU Library Team</p>
        ";
        return $this->sendEmail($to, $subject, $body);
    }

    public function passwordResetEmail($to, $resetLink, $fullName) {
        $subject = 'Password Reset Instructions - WMSU Library';
        $body = "
        <h1>Password Reset</h1>
        <p>Hi {$fullName},</p>
        <p>We received a request to reset your password. You can reset your password by clicking the link below:</p>
        <p><a href='{$resetLink}'>Reset Password</a></p>
        <p>If you did not request this, please ignore this email.</p>
        <p>Best regards,<br>WMSU Library Team</p>
        ";
        return $this->sendEmail($to, $subject, $body);
    }

    public function borrowingRequestConfirmation($to, $fullName, $bookTitle) {
        $subject = 'Borrowing Request Confirmation - WMSU Library';
        $body = "
        <h1>Your Borrowing Request</h1>
        <p>Hi {$fullName},</p>
        <p>Your request to borrow the book titled <strong>{$bookTitle}</strong> has been received. We will notify you once it is approved.</p>
        <p>Thank you for using WMSU Library.</p>
        <p>Best regards,<br>WMSU Library Team</p>
        ";
        return $this->sendEmail($to, $subject, $body);
    }

    public function returnReminderEmail($to, $fullName, $bookTitle, $dueDate) {
        $subject = 'Return Reminder - WMSU Library';
        $body = "
        <h1>Return Reminder</h1>
        <p>Hi {$fullName},</p>
        <p>This is a friendly reminder to return the book titled <strong>{$bookTitle}</strong> by <strong>{$dueDate}</strong>.</p>
        <p>Please return the book on time to avoid any penalties.</p>
        <p>Best regards,<br>WMSU Library Team</p>
        ";
        return $this->sendEmail($to, $subject, $body);
    }

    public function adminRequestStatusUpdateEmail($to, $fullName, $bookTitle, $status) {
        $subject = 'Borrowing Request Status Update - WMSU Library';
        $body = "
        <h1>Request Status Update</h1>
        <p>Hi {$fullName},</p>
        <p>Your borrowing request for the book titled <strong>{$bookTitle}</strong> has been <strong>{$status}</strong>.</p>
        <p>Thank you for using WMSU Library.</p>
        <p>Best regards,<br>WMSU Library Team</p>
        ";
        return $this->sendEmail($to, $subject, $body);
    }

    public function passwordResetFormEmail($to, $token, $fullName) {
        $subject = 'Password Reset - LIBRARY BOOK BORROWING SYSTEM';
        $resetFormUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
            "://{$_SERVER['HTTP_HOST']}/student/password_reset_form.php";

        $body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Password Reset - LIBRARY BOOK BORROWING SYSTEM</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                .header { background-color: #007bff; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { padding: 20px; }
                .logo { max-width: 100px; margin-bottom: 10px; }
                .reset-title { font-size: 24px; font-weight: bold; margin: 20px 0; text-align: center; }
                form { margin-top: 20px; }
                label { display: block; margin-bottom: 5px; font-weight: bold; }
                input[type='password'] { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
                button { background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
                button:hover { background-color: #0056b3; }
                .footer { background-color: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='http://{$_SERVER['HTTP_HOST']}/image/WMSU.png' alt='WMSU Logo' class='logo'>
                    <h1>LIBRARY BOOK BORROWING SYSTEM</h1>
                </div>
                <div class='content'>
                    <div class='reset-title'>RESET PASSWORD</div>
                    <p>Hi {$fullName},</p>
                    <p>We received a request to reset your password. Please fill in the form below to set a new password:</p>

                    <form method='POST' action='{$resetFormUrl}'>
                        <input type='hidden' name='token' value='{$token}'>

                        <label for='new_password'>New Password:</label>
                        <input type='password' id='new_password' name='new_password' required>

                        <label for='confirm_password'>Confirm New Password:</label>
                        <input type='password' id='confirm_password' name='confirm_password' required>

                        <button type='submit'>Submit</button>
                    </form>

                    <p>If you did not request this password reset, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 LIBRARY BOOK BORROWING SYSTEM. All rights reserved.</p>
                    <p>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        return $this->sendEmail($to, $subject, $body);
    }

    public function passwordChangeNotificationEmail($to, $fullName) {
        $subject = 'Password Changed Successfully - WMSU Library System';
        $body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Password Changed - WMSU Library</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                .header { background-color: #28a745; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { padding: 20px; }
                .alert { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .footer { background-color: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; color: #666; }
                .logo { max-width: 100px; margin-bottom: 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <img src='https://your-domain.com/image/wmsulogo.png' alt='WMSU Library Logo' class='logo'>
                    <h1>WMSU Library System</h1>
                    <p>Password Change Confirmation</p>
                </div>
                <div class='content'>
                    <div class='alert'>
                        <strong>✓ Password Successfully Changed</strong>
                    </div>
                    <p>Dear {$fullName},</p>
                    <p>Your password for the WMSU Library System has been successfully changed.</p>
                    <p><strong>Change Details:</strong></p>
                    <ul>
                        <li>Date & Time: " . date('F j, Y \a\t g:i A') . "</li>
                        <li>Account: {$to}</li>
                    </ul>
                    <p>If you did not make this change, please contact our support team immediately at support@wmsulibrary.edu.ph or reset your password using the 'Forgot Password' feature.</p>
                    <p>For your security, we recommend:</p>
                    <ul>
                        <li>Using a unique password for your library account</li>
                        <li>Enabling two-factor authentication if available</li>
                        <li>Regularly updating your password</li>
                    </ul>
                    <p>You can now log in to your account using your new password.</p>
                    <p>If you have any questions or concerns, please don't hesitate to contact us.</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2024 WMSU Library System. All rights reserved.</p>
                    <p>This is an automated security notification. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        return $this->sendEmail($to, $subject, $body);
    }
}
?>
