<?php
/**
 * Email Configuration File
 * Settings for sending emails via Gmail SMTP
 */

// Gmail SMTP Settings
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_USERNAME', 'info.groupg.aits@gmail.com');
define('MAIL_PASSWORD', 'sevd hbeg snjj lgbq');
define('MAIL_FROM_EMAIL', 'info.groupg.aits@gmail.com');
define('MAIL_FROM_NAME', 'Blood Donation DMS');

// Other Email Settings
define('MAIL_REPLY_TO', MAIL_FROM_EMAIL);
define('MAIL_CHARSET', 'UTF-8');

// Email debugging (set to true for troubleshooting)
define('MAIL_DEBUG', false);
?>
