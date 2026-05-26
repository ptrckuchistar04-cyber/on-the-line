<?php
// Application Configuration
define('SITE_NAME', 'On The Line');
define('SITE_URL', 'http://localhost/on-the-line/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/products/');
define('MAX_COMPARE_ITEMS', 2);

// Color constants for reference
define('COLOR_NAVY', '#191970');
define('COLOR_ORANGE', '#FF8C00');
define('COLOR_SILVER', '#C0C0C0');

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Timezone
date_default_timezone_set('Asia/Manila');