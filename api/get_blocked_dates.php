<?php
/**
 * Get all blocked dates for calendar
 */

require_once '../config/database.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Get all confirmed reservations (including approved rebookings)
    $stmt = $pdo->prepare("
        SELECT DISTINCT check_in_date as date 
        FROM reservations 
        WHERE status IN ('confirmed', 'checked_in', 'rebooked')
        AND downpayment_verified = 1
        AND check_in_date >= CURDATE()
        ORDER BY check_in_date
    ");
    
    $stmt->execute();
    $dates = $stmt->fetchAll();

    // Also get dates from pending rebooking requests (original dates still blocked)
    $stmt = $pdo->prepare("
        SELECT DISTINCT check_in_date as date 
        FROM reservations 
        WHERE rebooking_requested = 1 
        AND rebooking_approved IS NULL
        AND check_in_date >= CURDATE()
    ");
    
    $stmt->execute();
    $pendingRebookings = $stmt->fetchAll();

    // Merge all dates
    $allDates = array_merge($dates, $pendingRebookings);
    
    // Remove duplicates
    $uniqueDates = [];
    foreach ($allDates as $dateInfo) {
        $uniqueDates[$dateInfo['date']] = true;
    }

    echo json_encode([
        'success' => true,
        'dates' => array_keys($uniqueDates)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'dates' => []
    ]);
}
