<?php
/**
 * Clean up room_inventory table - not needed for package-based system
 */
require_once '../../config/connection.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "Cleaning up room_inventory table...\n\n";
    
    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'room_inventory'");
    
    if ($tableCheck->rowCount() > 0) {
        $conn->exec("DROP TABLE IF EXISTS room_inventory");
        echo "✓ Removed room_inventory table\n";
        echo "  (Not needed for package-based venue booking system)\n\n";
    } else {
        echo "✓ room_inventory table doesn't exist - nothing to clean up\n\n";
    }
    
    echo "System is now configured for package-based venue bookings!\n";
    echo "Occupancy rate is calculated based on days with bookings vs total days.\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
