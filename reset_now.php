<?php
require_once 'config/database.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Count existing reservations
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM reservations");
    $count = $stmt->fetch()['count'];
    
    echo "Found $count reservations in database.\n";
    echo "Deleting all reservations...\n";
    
    // Delete all reservations
    $pdo->exec("TRUNCATE TABLE reservations");
    
    echo "✅ SUCCESS! All reservations have been deleted.\n";
    echo "Database is now fresh and ready for testing.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
