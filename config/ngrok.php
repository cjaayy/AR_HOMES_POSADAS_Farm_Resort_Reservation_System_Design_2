<?php
/**
 * Ngrok Configuration
 * This allows email verification links to work from anywhere (including phones)
 * 
 * Setup Instructions:
 * 1. Download ngrok from https://ngrok.com/download
 * 2. Sign up for free account at https://ngrok.com
 * 3. Get your auth token from https://dashboard.ngrok.com/get-started/your-authtoken
 * 4. Run: ngrok config add-authtoken YOUR_AUTH_TOKEN
 * 5. Start ngrok: ngrok http 80
 * 6. Copy the https URL (e.g., https://abc123.ngrok.io)
 * 7. Set NGROK_URL below to your ngrok URL
 * 8. Set USE_NGROK to true
 */

// Set to true to use ngrok URL for email verification links
define('USE_NGROK', true);

// Your ngrok URL (get this by running: ngrok http 80)
// Example: 'https://abc123.ngrok-free.app'
define('NGROK_URL', 'https://sally-interimperial-pura.ngrok-free.dev');

// Fallback to localhost if ngrok is not configured
define('LOCAL_URL', 'http://localhost');

/**
 * Get the base URL for verification links
 * @return string The base URL to use
 */
function getBaseUrl() {
    if (USE_NGROK && NGROK_URL !== 'https://your-ngrok-url-here.ngrok-free.app') {
        return NGROK_URL;
    }
    
    // Fallback to auto-detected URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return "{$protocol}://{$host}";
}

/**
 * Build full verification URL
 * @param string $path The path to append to base URL
 * @return string The full URL
 */
function buildVerificationUrl($path) {
    $baseUrl = getBaseUrl();
    $cleanPath = ltrim($path, '/');
    return "{$baseUrl}/{$cleanPath}";
}
?>
