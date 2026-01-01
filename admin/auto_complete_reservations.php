<?php
/**
 * Auto-Complete Past Reservations
 * AR Homes Posadas Farm Resort Reservation System
 * 
 * This script automatically completes reservations where:
 * - Status is 'confirmed' (fully paid)
 * - Check-in date has passed
 * 
 * Can be run as a cron job or triggered manually by admin.
 * Recommended cron schedule: Daily at midnight
 * 0 0 * * * php /path/to/auto_complete_reservations.php
 */

// Can be run from CLI or web
if (php_sapi_name() !== 'cli') {
    session_start();
    // Allow admin access OR run without auth for cron
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        // Check for API key for cron access
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? '';
        if ($apiKey !== 'ar_homes_cron_key_2026') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
    }
    header('Content-Type: application/json');
}

require_once __DIR__ . '/../config/database.php';

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
    
    $results = [
        'completed' => [],
        'no_show' => [],
        'errors' => []
    ];
    
    // ============================================================
    // PART 1: Auto-complete confirmed reservations where check-in date has passed
    // ============================================================
    
    // Find confirmed reservations where check-in date was yesterday or earlier
    // (giving a buffer of 1 day after check-in to mark as complete)
    $stmt = $pdo->prepare("
        SELECT 
            reservation_id,
            user_id,
            guest_name,
            guest_email,
            check_in_date,
            booking_type,
            total_amount,
            downpayment_verified,
            full_payment_verified
        FROM reservations
        WHERE status = 'confirmed'
        AND check_in_date < CURDATE()
        AND downpayment_verified = 1
    ");
    
    $stmt->execute();
    $pastReservations = $stmt->fetchAll();
    
    if (count($pastReservations) > 0) {
        $pdo->beginTransaction();
        
        try {
            foreach ($pastReservations as $reservation) {
                // Determine final status based on payment
                // If full payment is verified, mark as completed
                // If only downpayment, could be no_show or completed (assume completed for now)
                $newStatus = 'completed';
                $note = 'Auto-completed: Check-in date has passed.';
                
                if ($reservation['full_payment_verified'] == 1) {
                    $note = 'Auto-completed: Fully paid reservation, check-in date passed.';
                } else {
                    // Only downpayment verified, still mark as completed 
                    // (remaining balance can be paid at resort)
                    $note = 'Auto-completed: Downpayment verified, check-in date passed.';
                }
                
                $updateStmt = $pdo->prepare("
                    UPDATE reservations
                    SET 
                        status = :status,
                        date_locked = 0,
                        admin_notes = CONCAT(
                            COALESCE(admin_notes, ''),
                            '\n[', NOW(), '] ', :note
                        ),
                        updated_at = NOW()
                    WHERE reservation_id = :id
                    AND status = 'confirmed'
                ");
                
                $updateStmt->execute([
                    ':status' => $newStatus,
                    ':note' => $note,
                    ':id' => $reservation['reservation_id']
                ]);
                
                if ($updateStmt->rowCount() > 0) {
                    $results['completed'][] = [
                        'reservation_id' => $reservation['reservation_id'],
                        'guest_name' => $reservation['guest_name'],
                        'check_in_date' => $reservation['check_in_date']
                    ];
                    
                    error_log(sprintf(
                        "[RESERVATION_AUTO_COMPLETED] ID: %s, Guest: %s, Check-in: %s",
                        $reservation['reservation_id'],
                        $reservation['guest_name'],
                        $reservation['check_in_date']
                    ));
                }
            }
            
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    
    // ============================================================
    // PART 2: Mark checked_in reservations as checked_out if check-in date passed
    // ============================================================
    
    $stmt2 = $pdo->prepare("
        SELECT 
            reservation_id,
            user_id,
            guest_name,
            check_in_date
        FROM reservations
        WHERE status = 'checked_in'
        AND check_in_date < CURDATE()
    ");
    
    $stmt2->execute();
    $checkedInReservations = $stmt2->fetchAll();
    
    if (count($checkedInReservations) > 0) {
        $pdo->beginTransaction();
        
        try {
            foreach ($checkedInReservations as $reservation) {
                $updateStmt = $pdo->prepare("
                    UPDATE reservations
                    SET 
                        status = 'checked_out',
                        check_out_time = NOW(),
                        admin_notes = CONCAT(
                            COALESCE(admin_notes, ''),
                            '\n[', NOW(), '] Auto checked-out: Booking period ended.'
                        ),
                        updated_at = NOW()
                    WHERE reservation_id = :id
                    AND status = 'checked_in'
                ");
                
                $updateStmt->execute([':id' => $reservation['reservation_id']]);
                
                if ($updateStmt->rowCount() > 0) {
                    $results['completed'][] = [
                        'reservation_id' => $reservation['reservation_id'],
                        'guest_name' => $reservation['guest_name'],
                        'action' => 'auto_checked_out'
                    ];
                    
                    error_log(sprintf(
                        "[RESERVATION_AUTO_CHECKED_OUT] ID: %s, Guest: %s",
                        $reservation['reservation_id'],
                        $reservation['guest_name']
                    ));
                }
            }
            
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    
    // Summary
    $summary = sprintf(
        "Auto-completion completed: %d reservations completed, %d checked out",
        count($results['completed']),
        count(array_filter($results['completed'], fn($r) => ($r['action'] ?? '') === 'auto_checked_out'))
    );
    
    error_log("[AUTO_COMPLETE_RESERVATIONS] " . $summary);
    
    if (php_sapi_name() === 'cli') {
        echo $summary . "\n";
        if (count($results['completed']) > 0) {
            echo "Completed reservations:\n";
            foreach ($results['completed'] as $r) {
                echo "  - {$r['reservation_id']}: {$r['guest_name']}\n";
            }
        }
    } else {
        echo json_encode([
            'success' => true,
            'message' => $summary,
            'results' => $results
        ]);
    }
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    error_log("[AUTO_COMPLETE_ERROR] " . $error);
    
    if (php_sapi_name() === 'cli') {
        echo "ERROR: " . $error . "\n";
        exit(1);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $error]);
    }
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
    error_log("[AUTO_COMPLETE_ERROR] " . $error);
    
    if (php_sapi_name() === 'cli') {
        echo "ERROR: " . $error . "\n";
        exit(1);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $error]);
    }
}
