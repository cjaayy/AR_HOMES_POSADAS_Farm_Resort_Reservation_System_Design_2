<?php
/**
 * Security Helper Functions
 * AR Homes Posadas Farm Resort Reservation System
 * 
 * Provides CSRF protection, input sanitization, and security utilities
 */

class Security {
    
    /**
     * Generate CSRF token and store in session
     * @return string CSRF token
     */
    public static function generateCSRFToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Generate a new token
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        return $token;
    }
    
    /**
     * Validate CSRF token
     * @param string $token Token to validate
     * @param int $maxAge Maximum age in seconds (default 3600 = 1 hour)
     * @return bool True if valid
     */
    public static function validateCSRFToken(?string $token, int $maxAge = 3600): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        
        // Check token match
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            return false;
        }
        
        // Check token age
        if (isset($_SESSION['csrf_token_time'])) {
            if (time() - $_SESSION['csrf_token_time'] > $maxAge) {
                // Token expired, regenerate
                self::generateCSRFToken();
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get current CSRF token (generate if not exists)
     * @return string CSRF token
     */
    public static function getCSRFToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time'])) {
            return self::generateCSRFToken();
        }
        
        // Regenerate if older than 30 minutes
        if (time() - $_SESSION['csrf_token_time'] > 1800) {
            return self::generateCSRFToken();
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Sanitize string input
     * @param mixed $input Input to sanitize
     * @return string Sanitized string
     */
    public static function sanitizeString($input): string {
        if ($input === null) {
            return '';
        }
        $input = trim((string) $input);
        $input = strip_tags($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }
    
    /**
     * Sanitize email
     * @param string $email Email to sanitize
     * @return string|false Sanitized email or false if invalid
     */
    public static function sanitizeEmail(string $email) {
        $email = trim($email);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
    }
    
    /**
     * Sanitize phone number (keep only digits and basic characters)
     * @param string $phone Phone number to sanitize
     * @return string Sanitized phone number
     */
    public static function sanitizePhone(string $phone): string {
        // Remove all characters except digits, +, -, (, ), and spaces
        return preg_replace('/[^0-9+\-() ]/', '', $phone);
    }
    
    /**
     * Sanitize date string
     * @param string $date Date string
     * @param string $format Expected format
     * @return string|false Validated date or false
     */
    public static function sanitizeDate(string $date, string $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return ($d && $d->format($format) === $date) ? $date : false;
    }
    
    /**
     * Sanitize integer
     * @param mixed $value Value to sanitize
     * @param int $min Minimum allowed value
     * @param int $max Maximum allowed value
     * @return int|false Sanitized integer or false
     */
    public static function sanitizeInt($value, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX) {
        $value = filter_var($value, FILTER_VALIDATE_INT);
        if ($value === false) {
            return false;
        }
        if ($value < $min || $value > $max) {
            return false;
        }
        return $value;
    }
    
    /**
     * Sanitize decimal/float
     * @param mixed $value Value to sanitize
     * @param float $min Minimum allowed value
     * @param float $max Maximum allowed value
     * @return float|false Sanitized float or false
     */
    public static function sanitizeDecimal($value, float $min = 0, float $max = PHP_FLOAT_MAX) {
        $value = filter_var($value, FILTER_VALIDATE_FLOAT);
        if ($value === false) {
            return false;
        }
        if ($value < $min || $value > $max) {
            return false;
        }
        return $value;
    }
    
    /**
     * Validate and sanitize reservation ID format
     * @param string $id Reservation ID
     * @return string|false Valid reservation ID or false
     */
    public static function validateReservationId(string $id) {
        // Format: RES-YYYYMMDD-XXXXX
        if (preg_match('/^RES-\d{8}-[A-Z0-9]{5}$/', $id)) {
            return $id;
        }
        return false;
    }
    
    /**
     * Validate and sanitize user ID format
     * @param string $id User ID
     * @return string|false Valid user ID or false
     */
    public static function validateUserId(string $id) {
        // Format: USR-YYYYMMDD-XXXX
        if (preg_match('/^USR-\d{8}-[A-Z0-9]{4}$/', $id)) {
            return $id;
        }
        return false;
    }
    
    /**
     * Check for duplicate form submission using token
     * @param string $formToken Unique form submission token
     * @return bool True if duplicate (already processed)
     */
    public static function isDuplicateSubmission(string $formToken): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['processed_forms'])) {
            $_SESSION['processed_forms'] = [];
        }
        
        // Clean old tokens (older than 1 hour)
        $currentTime = time();
        $_SESSION['processed_forms'] = array_filter(
            $_SESSION['processed_forms'],
            function($timestamp) use ($currentTime) {
                return ($currentTime - $timestamp) < 3600;
            }
        );
        
        // Check if this token was already processed
        if (isset($_SESSION['processed_forms'][$formToken])) {
            return true;
        }
        
        // Mark as processed
        $_SESSION['processed_forms'][$formToken] = $currentTime;
        return false;
    }
    
    /**
     * Rate limiting check
     * @param string $action Action being rate limited
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $windowSeconds Time window in seconds
     * @return bool True if rate limited (should block)
     */
    public static function isRateLimited(string $action, int $maxAttempts = 5, int $windowSeconds = 60): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $key = 'rate_limit_' . $action;
        $currentTime = time();
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }
        
        // Clean old attempts
        $_SESSION[$key] = array_filter(
            $_SESSION[$key],
            function($timestamp) use ($currentTime, $windowSeconds) {
                return ($currentTime - $timestamp) < $windowSeconds;
            }
        );
        
        // Check if limit exceeded
        if (count($_SESSION[$key]) >= $maxAttempts) {
            return true;
        }
        
        // Record this attempt
        $_SESSION[$key][] = $currentTime;
        return false;
    }
    
    /**
     * Generate a secure random token for form submission tracking
     * @return string Unique form token
     */
    public static function generateFormToken(): string {
        return bin2hex(random_bytes(16));
    }
    
    /**
     * Validate that request is from same origin (basic check)
     * @return bool True if valid origin
     */
    public static function validateOrigin(): bool {
        if (!isset($_SERVER['HTTP_ORIGIN']) && !isset($_SERVER['HTTP_REFERER'])) {
            // Allow requests without origin (e.g., direct API calls in development)
            return true;
        }
        
        $allowedHost = $_SERVER['HTTP_HOST'] ?? '';
        
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            $originHost = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST);
            return $originHost === $allowedHost;
        }
        
        if (isset($_SERVER['HTTP_REFERER'])) {
            $refererHost = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
            return $refererHost === $allowedHost;
        }
        
        return false;
    }
    
    /**
     * Set secure response headers
     */
    public static function setSecurityHeaders(): void {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Content-Security-Policy: default-src \'self\'');
    }
    
    /**
     * Log security event
     * @param string $event Event type
     * @param string $message Event message
     * @param array $context Additional context
     */
    public static function logSecurityEvent(string $event, string $message, array $context = []): void {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'message' => $message,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_id' => $_SESSION['user_id'] ?? null,
            'context' => $context
        ];
        
        error_log('[SECURITY] ' . json_encode($logEntry));
    }
}
?>
