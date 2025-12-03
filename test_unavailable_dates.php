<?php
/**
 * Test script to check what dates are being returned by get_unavailable_dates.php
 */

require_once 'config/database.php';

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
    
    echo "<h2>Testing Unavailable Dates Query</h2>";
    echo "<h3>Looking for reservations with booking_type = 'nighttime'</h3>";
    
    // Check what's in the reservations table for nighttime
    $stmt = $pdo->prepare("
        SELECT 
            reservation_id,
            guest_name,
            booking_type,
            check_in_date,
            status,
            downpayment_verified,
            full_payment_verified,
            date_locked
        FROM reservations 
        WHERE booking_type = 'nighttime'
        ORDER BY check_in_date ASC
    ");
    
    $stmt->execute();
    $all_nighttime = $stmt->fetchAll();
    
    echo "<h4>All Nighttime Reservations:</h4>";
    echo "<pre>" . print_r($all_nighttime, true) . "</pre>";
    
    // Now check which ones should be unavailable
    $stmt2 = $pdo->prepare("
        SELECT DISTINCT 
            check_in_date, 
            reservation_id, 
            guest_name, 
            status, 
            downpayment_verified,
            full_payment_verified,
            date_locked
        FROM reservations 
        WHERE booking_type = 'nighttime'
        AND status IN ('confirmed', 'checked_in', 'checked_out', 'pending_confirmation')
        AND (downpayment_verified = 1 OR date_locked = 1)
        AND check_in_date >= CURDATE()
        ORDER BY check_in_date ASC
    ");
    
    $stmt2->execute();
    $unavailable = $stmt2->fetchAll();
    
    echo "<h4>Unavailable Dates (Should be disabled in calendar):</h4>";
    echo "<pre>" . print_r($unavailable, true) . "</pre>";
    
    echo "<h4>Dates to disable:</h4>";
    echo "<ul>";
    foreach ($unavailable as $row) {
        echo "<li><strong>" . $row['check_in_date'] . "</strong> - " . $row['guest_name'] . " (Res: " . $row['reservation_id'] . ", Status: " . $row['status'] . ")</li>";
    }
    echo "</ul>";
    
    // Check specifically for December 18, 2025
    echo "<h3>Checking specifically for December 18, 2025:</h3>";
    $stmt3 = $pdo->prepare("
        SELECT * FROM reservations 
        WHERE check_in_date = '2025-12-18'
    ");
    $stmt3->execute();
    $dec18 = $stmt3->fetchAll();
    
    echo "<pre>" . print_r($dec18, true) . "</pre>";
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
