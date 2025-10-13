<?php
/**
 * Email Configuration File - EXAMPLE TEMPLATE
 * 
 * INSTRUCTIONS:
 * 1. Copy this file and rename it to: email_config.php
 * 2. Update the settings below with your actual email credentials
 * 3. Never commit email_config.php to version control (it's in .gitignore)
 */

// Email Service Configuration
// Choose your email provider and update the settings below

// OPTION 1: Gmail SMTP Settings (Recommended)
// NOTE: You need to use an "App Password" if you have 2-factor authentication enabled
// Generate one at: https://myaccount.google.com/apppasswords
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_ENCRYPTION', 'tls'); // or 'ssl' for port 465
define('MAIL_USERNAME', 'your-email@gmail.com'); // Your Gmail address
define('MAIL_PASSWORD', 'your-app-password-here');     // Your Gmail App Password (NOT your regular password)
define('MAIL_FROM_EMAIL', 'your-email@gmail.com');
define('MAIL_FROM_NAME', 'Blood Donation DMS');

// OPTION 2: Outlook/Hotmail SMTP Settings
// Uncomment these lines if you want to use Outlook instead of Gmail
/*
define('MAIL_HOST', 'smtp-mail.outlook.com');
define('MAIL_PORT', 587);
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_USERNAME', 'your-email@outlook.com');
define('MAIL_PASSWORD', 'your-password');
define('MAIL_FROM_EMAIL', 'your-email@outlook.com');
define('MAIL_FROM_NAME', 'Blood Donation DMS');
*/

// Other Email Settings
define('MAIL_REPLY_TO', MAIL_FROM_EMAIL);
define('MAIL_CHARSET', 'UTF-8');

// Email debugging (set to true for troubleshooting)
define('MAIL_DEBUG', false); // Set to true to see detailed debug information

/**
 * IMPORTANT SETUP INSTRUCTIONS:
 * 
 * FOR GMAIL:
 * 1. Enable 2-Step Verification on your Google Account
 * 2. Generate App Password: https://myaccount.google.com/apppasswords
 * 3. Replace 'your-email@gmail.com' with your actual Gmail address
 * 4. Replace 'your-app-password-here' with your generated App Password
 * 
 * FOR OUTLOOK/HOTMAIL:
 * 1. Use your regular Outlook password
 * 2. Make sure SMTP access is enabled in your account settings
 * 
 * FOR TESTING (No Real Emails):
 * 1. Download and run MailHog: https://github.com/mailhog/MailHog
 * 2. Use localhost settings with port 1025
 * 3. All emails will be caught at http://localhost:8025
 */
?>

