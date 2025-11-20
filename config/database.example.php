<?php
/**
 * Database Configuration
 * AR Homes Posadas Farm Resort Reservation System
 * 
 * INSTRUCTIONS:
 * 1. Copy this file to database.php
 * 2. Update the values below with your actual database credentials
 * 3. Never commit database.php to git (it's in .gitignore)
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USER', 'your_database_username');
define('DB_PASS', 'your_database_password');
define('DB_NAME', 'ar_homes_resort_db');
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('APP_NAME', 'AR Homes Posadas Farm Resort');
define('APP_VERSION', '1.0.0');
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('DEVELOPMENT_MODE', true); // Set to false in production

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to prevent breaking JSON responses
ini_set('log_errors', 1); // Log errors instead

// Timezone
date_default_timezone_set('Asia/Manila');
?>
