<?php
/**
 * Get user rebooking requests
 */

session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

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

    $user_id = $_SESSION['user_id'];
    $filter = $_GET['filter'] ?? 'all';

    $query = "
        SELECT 
            r.reservation_id,
            r.booking_type,
            r.package_type,
            r.check_in_date as original_date,
            r.rebooking_new_date as requested_date,
            r.rebooking_reason as reason,
            r.rebooking_approved,
            r.rebooking_approved_by,
            r.rebooking_approved_at,
            r.created_at as requested_at,
            a.full_name as approved_by_name
        FROM reservations r
        LEFT JOIN admin_users a ON r.rebooking_approved_by = a.admin_id
        WHERE r.user_id = :user_id 
        AND r.rebooking_requested = 1
    ";

    // Apply filter
    switch ($filter) {
        case 'pending':
            $query .= " AND r.rebooking_approved IS NULL";
            break;
        case 'approved':
            $query .= " AND r.rebooking_approved = 1";
            break;
        case 'rejected':
            $query .= " AND r.rebooking_approved = -1";
            break;
        // 'all' shows all
    }

    $query .= " ORDER BY r.updated_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $rebookings = $stmt->fetchAll();

    // Format booking type labels
    $booking_labels = [
        'daytime' => 'Daytime',
        'nighttime' => 'Nighttime',
        '22hours' => '22 Hours',
        'venue-daytime' => 'Venue Daytime',
        'venue-nighttime' => 'Venue Nighttime',
        'venue-22hours' => 'Venue 22 Hours'
    ];

    // Format response
    foreach ($rebookings as &$rebooking) {
        $rebooking['booking_type_label'] = $booking_labels[$rebooking['booking_type']] ?? $rebooking['booking_type'];
        
        // Determine status
        if ($rebooking['rebooking_approved'] === null) {
            $rebooking['status'] = 'pending';
        } elseif ($rebooking['rebooking_approved'] == 1) {
            $rebooking['status'] = 'approved';
        } else {
            $rebooking['status'] = 'rejected';
        }
        
        // Format approved_by
        if ($rebooking['approved_by_name']) {
            $rebooking['approved_by'] = $rebooking['approved_by_name'];
        }
    }

    echo json_encode([
        'success' => true,
        'rebookings' => $rebookings
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'rebookings' => []
    ]);
}
