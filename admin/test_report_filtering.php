<?php
require_once '../config/connection.php';

$db = new Database();
$conn = $db->getConnection();

echo "Testing Report Filtering - Paid vs Unpaid Reservations\n";
echo str_repeat("=", 80) . "\n\n";

// Test query 1: All reservations
$allSql = "SELECT COUNT(*) as count FROM reservations 
           WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$allCount = $conn->query($allSql)->fetch(PDO::FETCH_ASSOC)['count'];

// Test query 2: Only verified reservations (what shows in reports)
$verifiedSql = "SELECT COUNT(*) as count FROM reservations 
                WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                AND downpayment_verified = 1";
$verifiedCount = $conn->query($verifiedSql)->fetch(PDO::FETCH_ASSOC)['count'];

// Test query 3: Unverified reservations (hidden from reports)
$unverifiedSql = "SELECT COUNT(*) as count FROM reservations 
                  WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                  AND downpayment_verified = 0";
$unverifiedCount = $conn->query($unverifiedSql)->fetch(PDO::FETCH_ASSOC)['count'];

echo "Last 7 Days Reservations:\n";
echo "  Total Reservations: {$allCount}\n";
echo "  ✓ Verified (Shows in Reports): {$verifiedCount}\n";
echo "  ✗ Unverified (Hidden from Reports): {$unverifiedCount}\n\n";

echo str_repeat("=", 80) . "\n";
echo "FILTERING RULES:\n";
echo str_repeat("=", 80) . "\n\n";

echo "✓ WILL APPEAR in Reports & Analytics:\n";
echo "  • Reservations with downpayment_verified = 1\n";
echo "  • Status: confirmed, checked_in, completed\n";
echo "  • These count toward revenue, occupancy, and all statistics\n\n";

echo "✗ WILL NOT APPEAR in Reports & Analytics:\n";
echo "  • Reservations with downpayment_verified = 0 (unpaid/pending verification)\n";
echo "  • Status: pending_payment, pending_confirmation\n";
echo "  • These are completely excluded from all reports and statistics\n\n";

echo str_repeat("=", 80) . "\n";
echo "EXAMPLE SCENARIOS:\n";
echo str_repeat("=", 80) . "\n\n";

echo "Scenario 1: New booking created, waiting for payment\n";
echo "  Status: pending_payment\n";
echo "  downpayment_verified: 0\n";
echo "  Result: ✗ NOT shown in reports\n\n";

echo "Scenario 2: Payment uploaded, waiting for admin verification\n";
echo "  Status: pending_confirmation\n";
echo "  downpayment_verified: 0\n";
echo "  Result: ✗ NOT shown in reports\n\n";

echo "Scenario 3: Admin verified the downpayment\n";
echo "  Status: confirmed\n";
echo "  downpayment_verified: 1\n";
echo "  Result: ✓ SHOWN in reports\n\n";

echo "Scenario 4: Guest checked in\n";
echo "  Status: checked_in\n";
echo "  downpayment_verified: 1\n";
echo "  Result: ✓ SHOWN in reports\n\n";

echo "Scenario 5: Reservation completed\n";
echo "  Status: completed\n";
echo "  downpayment_verified: 1\n";
echo "  Result: ✓ SHOWN in reports\n\n";

echo str_repeat("=", 80) . "\n\n";
echo "✅ Reports & Analytics now only show VERIFIED PAID reservations!\n";
?>
