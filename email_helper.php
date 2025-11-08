<?php
/**
 * Email Helper Functions
 * Reusable functions for sending emails using PHPMailer
 * Compatible with PHP 5.2+
 */

// Include PHPMailer - Old version compatible with PHP 5.2+
require_once 'phpmailer/class.phpmailer.php';
require_once 'phpmailer/class.smtp.php';
require_once 'email_config.php';

/**
 * Send an email using PHPMailer
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $html_body HTML email body
 * @param string $recipient_name Recipient's name (optional)
 * @return array array('success' => bool, 'message' => string, 'error' => string)
 */
function send_email($to, $subject, $html_body, $recipient_name = '') {
    $mail = new PHPMailer();
    
    try {
        // Server settings
        if (MAIL_DEBUG) {
            $mail->SMTPDebug = 2; // Enable verbose debug output
        }
        
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = (MAIL_USERNAME != '');
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        
        if (MAIL_ENCRYPTION != '') {
            $mail->SMTPSecure = MAIL_ENCRYPTION;
        }
        
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = MAIL_CHARSET;
        
        // Recipients
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addAddress($to, $recipient_name);
        $mail->addReplyTo(MAIL_REPLY_TO, MAIL_FROM_NAME);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html_body;
        
        // Create plain text version (strip HTML tags for email clients that don't support HTML)
        $mail->AltBody = strip_tags($html_body);
        
        // Send the email
        if (!$mail->send()) {
            return array(
                'success' => false,
                'message' => 'Email could not be sent',
                'error' => $mail->ErrorInfo
            );
        }
        
        return array(
            'success' => true,
            'message' => 'Email sent successfully to ' . $to,
            'error' => ''
        );
        
    } catch (phpmailerException $e) {
        return array(
            'success' => false,
            'message' => 'Email could not be sent',
            'error' => $mail->ErrorInfo
        );
    } catch (Exception $e) {
        return array(
            'success' => false,
            'message' => 'Email could not be sent',
            'error' => $e->getMessage()
        );
    }
}

/**
 * Send a notification email with Blood Donation DMS template
 * 
 * @param string $to Recipient email address
 * @param string $recipient_name Recipient's full name
 * @param string $title Notification title
 * @param string $message Notification message
 * @return array array('success' => bool, 'message' => string, 'error' => string)
 */
function send_notification_email($to, $recipient_name, $title, $message) {
    // Create HTML email body with Blood Donation DMS template
    $html_body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                margin: 0;
                padding: 0;
                background-color: #f4f4f4;
            }
            .email-container { 
                max-width: 600px; 
                margin: 20px auto; 
                background-color: #ffffff;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .header { 
                background-color: #E21C3D; 
                color: white; 
                padding: 30px 20px; 
                text-align: center; 
            }
            .header h1 { 
                margin: 0; 
                font-size: 28px;
                font-weight: bold;
            }
            .content { 
                background-color: #f9f9f9; 
                padding: 30px 20px; 
            }
            .content h2 {
                color: #E21C3D;
                font-size: 22px;
                margin-top: 0;
                margin-bottom: 15px;
            }
            .greeting {
                font-size: 16px;
                color: #333;
                margin-bottom: 20px;
            }
            .message-box { 
                background-color: white; 
                padding: 20px; 
                border-radius: 5px;
                border-left: 4px solid #E21C3D;
                margin: 20px 0;
            }
            .message-text {
                color: #555;
                font-size: 15px;
                line-height: 1.8;
            }
            .footer { 
                background-color: #333; 
                color: #ffffff; 
                padding: 20px; 
                text-align: center; 
                font-size: 12px; 
            }
            .footer p {
                margin: 5px 0;
                color: #cccccc;
            }
            .footer a {
                color: #E21C3D;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <div class='header'>
                <h1>Blood Donation DMS</h1>
            </div>
            <div class='content'>
                <h2>" . htmlspecialchars($title) . "</h2>
                <p class='greeting'>Dear " . htmlspecialchars($recipient_name) . ",</p>
                <div class='message-box'>
                    <div class='message-text'>
                        " . nl2br(htmlspecialchars($message)) . "
                    </div>
                </div>
                <p style='margin-top: 30px; color: #666; font-size: 14px;'>
                    Thank you for being part of our blood donation community.
                </p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Blood Donation Database Management System. All rights reserved.</p>
                <p>This is an automated message. Please do not reply to this email.</p>
                <p style='margin-top: 10px;'>Powered by Group G</p>
            </div>
        </div>
    </body>
    </html>";
    
    return send_email($to, $title, $html_body, $recipient_name);
}

/**
 * Get error message from email sending result
 * 
 * @param array $result Result array from send_email or send_notification_email
 * @return string User-friendly error message
 */
function get_email_error_message($result) {
    if ($result['success']) {
        return '';
    }
    
    $error = $result['error'];
    
    // Provide user-friendly error messages
    if (strpos($error, 'authenticate') !== false || strpos($error, 'Username and Password not accepted') !== false) {
        return 'Email authentication failed. Please check your email username and password in email_config.php';
    } elseif (strpos($error, 'connect') !== false) {
        return 'Could not connect to email server. Please check your SMTP settings in email_config.php';
    } elseif (strpos($error, 'Invalid address') !== false) {
        return 'Invalid recipient email address';
    } else {
        return 'Email error: ' . $error;
    }
}
?>
