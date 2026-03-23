<?php
/**
 * TripNexus - Gmail SMTP Configuration
 * Configure your Gmail App Password here
 */

// Gmail SMTP Settings
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'tripnexus.business@gmail.com');  // Change to your Gmail
define('MAIL_PASSWORD', 'fuus hgih eeit tovl');      // Change to your 16-digit app password
define('MAIL_FROM_EMAIL', 'tripnexus.business@gmail.com'); // Change to your Gmail
define('MAIL_FROM_NAME', 'TripNexus');

// Security & Expiry Settings
define('VERIFICATION_CODE_EXPIRY', 15);  // Minutes
define('RESET_TOKEN_EXPIRY', 60);        // Minutes
define('VERIFICATION_CODE_LENGTH', 6);   // Digits

// Application Settings
define('APP_URL', 'http://localhost/SGP');
define('APP_NAME', 'TripNexus');

// Enable/Disable Email Sending (for testing)
define('ENABLE_EMAIL', true);

// Email Directory Logs
define('EMAIL_LOG_DIR', __DIR__ . '/../logs/emails/');
?>