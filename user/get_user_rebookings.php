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

    $query .= " ORDER BY r.created_at DESC";

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
    $formattedRebookings = [];
    foreach ($rebookings as $rebooking) {
        $formatted = [
            'reservation_id' => $rebooking['reservation_id'],
            'booking_type' => $rebooking['booking_type'],
            'booking_type_label' => $booking_labels[$rebooking['booking_type']] ?? $rebooking['booking_type'],
            'package_type' => $rebooking['package_type'],
            'original_date' => $rebooking['original_date'],
            'requested_date' => $rebooking['requested_date'],
            'reason' => $rebooking['reason'],
            'rebooking_approved' => $rebooking['rebooking_approved'],
            'rebooking_approved_by' => $rebooking['rebooking_approved_by'],
            'rebooking_approved_at' => $rebooking['rebooking_approved_at'],
            'requested_at' => $rebooking['requested_at'],
            'approved_by' => $rebooking['approved_by_name']
        ];
        
        // Determine status
        if ($rebooking['rebooking_approved'] === null) {
            $formatted['status'] = 'pending';
        } elseif ($rebooking['rebooking_approved'] == 1) {
            $formatted['status'] = 'approved';
        } else {
            $formatted['status'] = 'rejected';
        }
        
        $formattedRebookings[] = $formatted;
    }

    echo json_encode([
        'success' => true,
        'rebookings' => $formattedRebookings
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'rebookings' => []
    ]);
}
