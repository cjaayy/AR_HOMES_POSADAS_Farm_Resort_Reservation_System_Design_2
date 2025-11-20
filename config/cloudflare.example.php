<?php
/**
 * Cloudflare Tunnel Configuration
 * AR Homes Posadas Farm Resort Reservation System
 * 
 * INSTRUCTIONS:
 * 1. Copy this file to cloudflare.php
 * 2. Set up your Cloudflare Tunnel (optional, for external access)
 * 3. Update TUNNEL_URL with your actual tunnel URL
 * 4. Never commit cloudflare.php to git (it's in .gitignore)
 * 
 * Cloudflare Named Tunnel Setup:
 * - Install cloudflared: https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/install-and-setup/
 * - Create tunnel: cloudflared tunnel create <tunnel-name>
 * - Configure tunnel to point to localhost:80
 * - Run tunnel: cloudflared tunnel run <tunnel-name>
 */

// Set to true to use Cloudflare Tunnel URL for email verification links
define('USE_TUNNEL', false);

// Your Cloudflare Tunnel URL (if configured)
define('TUNNEL_URL', 'https://your-tunnel-url-here.trycloudflare.com');

// Fallback to localhost if tunnel is not configured
define('LOCAL_URL', 'http://localhost');

/**
 * Get the base URL for verification links
 * @return string The base URL to use
 */
function getBaseUrl() {
    if (USE_TUNNEL && TUNNEL_URL !== 'https://your-tunnel-url-here.trycloudflare.com') {
        return TUNNEL_URL;
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
