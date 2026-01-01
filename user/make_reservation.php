<?php
/**
 * AR Homes Posadas Farm Resort Reservation System
 * Make Reservation API
 */

session_start();
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../config/IDGenerator.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Please login to make a reservation'
    ]);
    exit;
}

// Fix for ID migration: If session has old integer ID, fetch new VARCHAR ID
if (is_numeric($_SESSION['user_id']) || !preg_match('/^USR-\d{8}-[A-Z0-9]{4}$/', $_SESSION['user_id'])) {
    try {
        $tempPdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Find user by username or email to get new ID
        if (isset($_SESSION['user_username'])) {
            $stmt = $tempPdo->prepare("SELECT user_id FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$_SESSION['user_username']]);
        } elseif (isset($_SESSION['user_email'])) {
            $stmt = $tempPdo->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$_SESSION['user_email']]);
        } else {
            // Can't recover, force re-login
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Session expired. Please login again.',
                'force_logout' => true
            ]);
            exit;
        }
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $_SESSION['user_id'] = $row['user_id'];
        } else {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Session expired. Please login again.',
                'force_logout' => true
            ]);
            exit;
        }
    } catch (PDOException $e) {
        error_log('Session ID update error: ' . $e->getMessage());
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Session error. Please login again.',
            'force_logout' => true
        ]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
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
    
    // Get POST data
    $raw_input = file_get_contents('php://input');
    $data = json_decode($raw_input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }
    
    // Validate required fields
    $required = ['booking_type', 'package_type', 'check_in_date', 'payment_method'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    $user_id = $_SESSION['user_id'];
    $booking_type = $data['booking_type'];
    $package_type = $data['package_type'];
    $check_in_date = $data['check_in_date'];
    $payment_method = $data['payment_method'];
    
    // Booking type specific validation and pricing
    $booking_config = [
        'daytime' => [
            'check_in_time' => '09:00:00',
            'check_out_time' => '17:00:00',
            'base_price' => 2000.00,
            'security_bond' => 2000.00,
            'overtime_charge' => 500.00
        ],
        'nighttime' => [
            'check_in_time' => '19:00:00',
            'check_out_time' => '07:00:00',
            'base_price' => 2000.00,
            'security_bond' => 2000.00,
            'overtime_charge' => 1000.00,
            'early_checkin_charge' => 500.00
        ],
        '22hours' => [
            'check_in_time' => '14:00:00',
            'check_out_time' => '12:00:00',
            'base_price' => 2000.00,
            'security_bond' => 2000.00,
            'overtime_charge' => 500.00
        ],
        // Venue for All Occasions packages
        'venue-daytime' => [
            'check_in_time' => '09:00:00',
            'check_out_time' => '17:00:00',
            'base_price' => 6000.00,
            'security_bond' => 2000.00,
            'overtime_charge' => 500.00
        ],
        'venue-nighttime' => [
            'check_in_time' => '19:00:00',
            'check_out_time' => '07:00:00',
            'base_price' => 10000.00,
            'security_bond' => 2000.00,
            'overtime_charge' => 1000.00,
            'early_checkin_charge' => 500.00
        ],
        'venue-22hours' => [
            'check_in_time' => '14:00:00',
            'check_out_time' => '12:00:00',
            'base_price' => 18000.00,
            'security_bond' => 2000.00,
            'overtime_charge' => 500.00
        ]
    ];
    
    if (!isset($booking_config[$booking_type])) {
        throw new Exception('Invalid booking type');
    }
    
    $config = $booking_config[$booking_type];
    $check_in_time = $config['check_in_time'];
    $check_out_time = $config['check_out_time'];
    
    // Calculate dates based on booking type
    $number_of_days = null;
    $number_of_nights = null;
    $check_out_date = null;
    
    if ($booking_type === 'daytime' || $booking_type === 'venue-daytime') {
        $number_of_days = isset($data['number_of_days']) ? (int)$data['number_of_days'] : 1;
        $check_out_date = date('Y-m-d', strtotime($check_in_date . ' + ' . ($number_of_days - 1) . ' days'));
    } elseif ($booking_type === 'nighttime' || $booking_type === '22hours' || 
              $booking_type === 'venue-nighttime' || $booking_type === 'venue-22hours') {
        $number_of_nights = isset($data['number_of_nights']) ? (int)$data['number_of_nights'] : 1;
        $check_out_date = date('Y-m-d', strtotime($check_in_date . ' + ' . $number_of_nights . ' days'));
    }
    
    // Validate check-in date is not in the past
    $today = date('Y-m-d');
    if ($check_in_date < $today) {
        throw new Exception('Check-in date cannot be in the past');
    }
    
    // Check if dates are available (not already booked)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM reservations 
        WHERE status IN ('pending_payment', 'confirmed') 
        AND (
            (check_in_date <= ? AND check_out_date >= ?)
            OR (check_in_date <= ? AND check_out_date >= ?)
            OR (check_in_date >= ? AND check_out_date <= ?)
        )
    ");
    $stmt->execute([
        $check_in_date, $check_in_date,
        $check_out_date, $check_out_date,
        $check_in_date, $check_out_date
    ]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing['count'] > 0) {
        throw new Exception('Selected dates are not available. Please choose different dates.');
    }
    
    // Calculate pricing based on package
    $package_prices = [
        'daytime-day' => 6000.00,
        'daytime-night' => 6000.00,
        'nighttime-night' => 10000.00,
        'nighttime-day' => 10000.00,
        '22hours-night' => 18000.00,
        '22hours-day' => 18000.00,
        // Venue packages
        'venue-daytime-day' => 6000.00,
        'venue-daytime-night' => 6000.00,
        'venue-nighttime-night' => 10000.00,
        'venue-nighttime-day' => 10000.00,
        'venue-22hours-night' => 18000.00,
        'venue-22hours-day' => 18000.00,
        // Legacy support for old package names
        'all-rooms-night' => 10000.00,
        'all-rooms-day' => 6000.00,
        'aircon-rooms-night' => 10000.00,
        'aircon-rooms-day' => 6000.00,
        'basic-rooms-night' => 18000.00,
        'basic-rooms-day' => 18000.00,
        'custom' => isset($data['custom_price']) ? (float)$data['custom_price'] : 0
    ];
    
    if (!isset($package_prices[$package_type])) {
        throw new Exception('Invalid package type');
    }
    
    $base_price = $package_prices[$package_type];
    
    // Calculate total based on duration
    $duration = ($booking_type === 'daytime' || $booking_type === 'venue-daytime') ? $number_of_days : $number_of_nights;
    $total_amount = $base_price * $duration;
    
    // Fixed downpayment amount
    $downpayment_amount = 1000;
    $remaining_balance = $total_amount - $downpayment_amount;
    
    // Get optional fields and map to correct column names
    // Parse group_size: can be numeric string or range like "31-40"
    $group_size_input = isset($data['group_size']) ? $data['group_size'] : '1';
    if (is_numeric($group_size_input)) {
        $number_of_guests = (int)$group_size_input;
    } else {
        // Handle ranges like "31-40" - take the middle value
        if (preg_match('/(\d+)-(\d+)/', $group_size_input, $matches)) {
            $min = (int)$matches[1];
            $max = (int)$matches[2];
            $number_of_guests = (int)(($min + $max) / 2);
        } else {
            $number_of_guests = 1;
        }
    }
    
    $group_type = isset($data['group_type']) ? $data['group_type'] : null;
    $special_requests = isset($data['special_requests']) ? $data['special_requests'] : null;
    
    // Get guest information from session
    $guest_name = $_SESSION['user_full_name'] ?? ($_SESSION['user_given_name'] . ' ' . $_SESSION['user_last_name']);
    $guest_email = $_SESSION['user_email'] ?? null;
    $guest_phone = $_SESSION['user_phone'] ?? null;
    
    // Generate new format reservation ID
    $reservation_id = IDGenerator::generateReservationId($pdo);
    
    // Insert reservation
    $stmt = $pdo->prepare("
        INSERT INTO reservations (
            reservation_id, user_id, guest_name, guest_email, guest_phone,
            booking_type, package_type, check_in_date, check_out_date,
            check_in_time, check_out_time, number_of_days, number_of_nights,
            number_of_guests, group_type, special_requests, 
            base_price, total_amount, downpayment_amount, remaining_balance, 
            payment_method, security_bond, status
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, 'pending_payment'
        )
    ");
    
    $stmt->execute([
        $reservation_id, $user_id, $guest_name, $guest_email, $guest_phone,
        $booking_type, $package_type, $check_in_date, $check_out_date,
        $check_in_time, $check_out_time, $number_of_days, $number_of_nights,
        $number_of_guests, $group_type, $special_requests,
        $base_price, $total_amount, $downpayment_amount, $remaining_balance,
        $payment_method, $config['security_bond']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Reservation created successfully',
        'reservation_id' => $reservation_id,
        'total_amount' => $total_amount,
        'downpayment_amount' => $downpayment_amount,
        'remaining_balance' => $remaining_balance,
        'booking_type' => $booking_type,
        'check_in_date' => $check_in_date,
        'check_out_date' => $check_out_date,
        'check_in_time' => $check_in_time,
        'check_out_time' => $check_out_time,
        'security_bond' => $config['security_bond'],
        'policy' => [
            'downpayment_required' => '₱1,000',
            'balance_due' => 'Before check-in',
            'security_bond' => '₱' . number_format($config['security_bond'], 2),
            'rebooking_notice' => '7 days prior',
            'non_refundable' => true
        ]
    ]);
    
} catch (PDOException $e) {
    error_log('Reservation PDO Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.',
        'error_type' => 'database'
    ]);
} catch (Exception $e) {
    error_log('Reservation Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_type' => 'validation'
    ]);
}
