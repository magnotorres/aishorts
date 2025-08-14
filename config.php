<?php

// Global Project Configuration
// ----------------------------

// Load Composer's autoloader to use installed packages.
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables from .env file
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Exception $e) {
    die("Error: Could not load .env file. Please copy .env.example to .env and fill it out. Message: " . $e->getMessage());
}


// 1. Database Configuration
// ---
// Credentials are loaded from the .env file.
define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'video_automation_db');
define('DB_CHARSET', 'utf8mb4');

// 2. Directory Paths
// ---
// Paths for storing generated files. Ensure these directories are writable.
define('DIR_VIDEOS', __DIR__ . '/generated_videos');
define('DIR_LOGS', __DIR__ . '/logs');

// 3. Google AI (Veo) API ration
// ---
define('GOOGLE_AI_API_KEY', $_ENV['GOOGLE_AI_API_KEY'] ?? '');

// 4. Admin Panel Configuration
// ---
define('ADMIN_USER', $_ENV['ADMIN_USER'] ?? 'admin');
// IMPORTANT: The password from .env is hashed here.
define('ADMIN_PASS_HASH', password_hash($_ENV['ADMIN_PASSWORD'] ?? 'password', PASSWORD_DEFAULT));

// 5. General Settings
// ---
// Set the default timezone for all date/time functions.
date_default_timezone_set('UTC');

/**
 * Initializes the required directories for logs and videos.
 * This function is called automatically when config.php is included.
 */
function initialize_project_directories() {
    if (!is_dir(DIR_VIDEOS)) {
        mkdir(DIR_VIDEOS, 0775, true);
    }
    if (!is_dir(DIR_LOGS)) {
        mkdir(DIR_LOGS, 0775, true);
    }
}

// Run the directory setup.
initialize_project_directories();

?>
