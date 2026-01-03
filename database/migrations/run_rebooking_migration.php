<?php
/**
 * Migration: Add Rebooking Columns
 * Run this script to add missing rebooking columns to the reservations table
 */

require_once '../../config/database.php';

echo "Starting Rebooking Columns Migration...\n\n";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ Connected to database: " . DB_NAME . "\n\n";
    
    // Define columns to add
    $columns = [
        'rebooking_requested' => 'TINYINT(1) DEFAULT 0',
        'rebooking_new_date' => 'DATE NULL',
        'rebooking_reason' => 'TEXT NULL',
        'rebooking_requested_at' => 'DATETIME NULL',
        'rebooking_approved' => 'TINYINT(1) NULL',
        'rebooking_approved_by' => 'INT NULL',
        'rebooking_approved_at' => 'DATETIME NULL'
    ];
    
    // Check existing columns
    $stmt = $pdo->query("SHOW COLUMNS FROM reservations");
    $existingColumns = [];
    while ($row = $stmt->fetch()) {
        $existingColumns[] = $row['Field'];
    }
    
    echo "Checking rebooking columns...\n";
    
    $addedCount = 0;
    $skippedCount = 0;
    
    foreach ($columns as $columnName => $columnDef) {
        if (in_array($columnName, $existingColumns)) {
            echo "   ⏭ Column '$columnName' already exists - skipping\n";
            $skippedCount++;
        } else {
            try {
                $sql = "ALTER TABLE reservations ADD COLUMN $columnName $columnDef";
                $pdo->exec($sql);
                echo "   ✅ Added column '$columnName'\n";
                $addedCount++;
            } catch (PDOException $e) {
                echo "   ❌ Failed to add column '$columnName': " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n";
    echo "=================================\n";
    echo "Migration Complete!\n";
    echo "=================================\n";
    echo "Columns added: $addedCount\n";
    echo "Columns skipped (already exist): $skippedCount\n";
    echo "\n";
    
    // Verify all columns exist
    echo "Verifying all rebooking columns...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM reservations LIKE 'rebooking%'");
    $rebookingColumns = $stmt->fetchAll();
    
    echo "Found " . count($rebookingColumns) . " rebooking columns:\n";
    foreach ($rebookingColumns as $col) {
        echo "   • " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
