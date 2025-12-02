<?php
/**
 * ID Generator Helper Class
 * AR Homes Posadas Farm Resort Reservation System
 * 
 * Generates unique IDs for users and reservations in a professional format
 */

class IDGenerator {
    
    /**
     * Generate a User ID in format: USR-YYYYMMDD-XXXX
     * Example: USR-20251202-A7F3
     * 
     * @param PDO $pdo Database connection
     * @return string Unique user ID
     */
    public static function generateUserId($pdo) {
        $maxAttempts = 100;
        $attempt = 0;
        
        do {
            // Format: USR-YYYYMMDD-XXXX (4 random alphanumeric characters)
            $date = date('Ymd');
            $random = self::generateRandomAlphanumeric(4);
            $userId = "USR-{$date}-{$random}";
            
            // Check if ID already exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id = :user_id LIMIT 1");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return $userId;
            }
            
            $attempt++;
        } while ($attempt < $maxAttempts);
        
        throw new Exception("Unable to generate unique user ID after {$maxAttempts} attempts");
    }
    
    /**
     * Generate a Reservation ID in format: RES-YYYYMMDD-XXXXX
     * Example: RES-20251202-A4K9M
     * 
     * @param PDO $pdo Database connection
     * @return string Unique reservation ID
     */
    public static function generateReservationId($pdo) {
        $maxAttempts = 100;
        $attempt = 0;
        
        do {
            // Format: RES-YYYYMMDD-XXXXX (5 random alphanumeric characters)
            $date = date('Ymd');
            $random = self::generateRandomAlphanumeric(5);
            $reservationId = "RES-{$date}-{$random}";
            
            // Check if ID already exists
            $stmt = $pdo->prepare("SELECT reservation_id FROM reservations WHERE reservation_id = :reservation_id LIMIT 1");
            $stmt->bindParam(':reservation_id', $reservationId);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return $reservationId;
            }
            
            $attempt++;
        } while ($attempt < $maxAttempts);
        
        throw new Exception("Unable to generate unique reservation ID after {$maxAttempts} attempts");
    }
    
    /**
     * Generate random alphanumeric string
     * Excludes confusing characters: 0, O, 1, I, L
     * 
     * @param int $length Length of the random string
     * @return string Random alphanumeric string
     */
    private static function generateRandomAlphanumeric($length) {
        // Characters excluding confusing ones (0, O, 1, I, L)
        $characters = '234567889ABCDEFGHJKMNPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
    
    /**
     * Validate User ID format
     * 
     * @param string $userId User ID to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateUserId($userId) {
        // Pattern: USR-YYYYMMDD-XXXX (4 alphanumeric characters)
        $pattern = '/^USR-\d{8}-[A-Z0-9]{4}$/';
        return preg_match($pattern, $userId) === 1;
    }
    
    /**
     * Validate Reservation ID format
     * 
     * @param string $reservationId Reservation ID to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateReservationId($reservationId) {
        // Pattern: RES-YYYYMMDD-XXXXX (5 alphanumeric characters)
        $pattern = '/^RES-\d{8}-[A-Z0-9]{5}$/';
        return preg_match($pattern, $reservationId) === 1;
    }
    
    /**
     * Extract date from User ID
     * 
     * @param string $userId User ID
     * @return string|null Date in Y-m-d format or null if invalid
     */
    public static function extractDateFromUserId($userId) {
        if (!self::validateUserId($userId)) {
            return null;
        }
        
        $parts = explode('-', $userId);
        $dateStr = $parts[1]; // YYYYMMDD
        return substr($dateStr, 0, 4) . '-' . substr($dateStr, 4, 2) . '-' . substr($dateStr, 6, 2);
    }
    
    /**
     * Extract date from Reservation ID
     * 
     * @param string $reservationId Reservation ID
     * @return string|null Date in Y-m-d format or null if invalid
     */
    public static function extractDateFromReservationId($reservationId) {
        if (!self::validateReservationId($reservationId)) {
            return null;
        }
        
        $parts = explode('-', $reservationId);
        $dateStr = $parts[1]; // YYYYMMDD
        return substr($dateStr, 0, 4) . '-' . substr($dateStr, 4, 2) . '-' . substr($dateStr, 6, 2);
    }
}
?>
