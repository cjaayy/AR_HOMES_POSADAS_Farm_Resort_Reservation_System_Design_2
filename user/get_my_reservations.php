<?php
/**
 * User: Get My Reservations
 * Fetch all reservations for logged-in user
 */

session_start();
header('Content-Type: application/json');

require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $user_id = $_SESSION['user_id'];
    $status_filter = $_GET['status'] ?? 'all'; // Filter by status if needed
    
    $sql = "
        SELECT 
            reservation_id,
            user_id,
            guest_name,
            guest_email,
            guest_phone,
            booking_type,
            package_type,
            room,
            check_in_date,
            check_out_date,
            check_in_time,
            check_out_time,
            number_of_days,
            number_of_nights,
            number_of_guests,
            group_type,
            special_requests,
            base_price,
            total_amount,
            downpayment_amount,
            remaining_balance,
            security_bond,
            overtime_hours,
            overtime_charges,
            damage_charges,
            final_amount,
            downpayment_paid,
            downpayment_proof,
            downpayment_reference,
            downpayment_paid_at,
            downpayment_verified,
            downpayment_verified_by,
            downpayment_verified_at,
            full_payment_paid,
            full_payment_proof,
            full_payment_reference,
            full_payment_paid_at,
            full_payment_verified,
            full_payment_verified_by,
            full_payment_verified_at,
            payment_method,
            security_bond_paid,
            security_bond_paid_at,
            security_bond_returned,
            security_bond_returned_at,
            security_bond_deduction,
            checked_in,
            checked_in_at,
            checked_in_by,
            checked_out,
            checked_out_at,
            checked_out_by,
            actual_checkout_time,
            status,
            date_locked,
            locked_until,
            terms_accepted,
            terms_accepted_at,
            terms_ip_address,
            rebooking_requested,
            rebooking_reason,
            rebooking_new_date,
            rebooking_approved,
            rebooking_approved_by,
            rebooking_approved_at,
            original_booking_id,
            cancellation_reason,
            cancelled_at,
            cancelled_by,
            no_show_marked_at,
            no_show_marked_by,
            admin_notes,
            staff_notes,
            created_at,
            updated_at
        FROM reservations 
        WHERE user_id = :user_id
    ";
    
    if ($status_filter !== 'all') {
        $sql .= " AND status = :status";
    }
    
    $sql .= " ORDER BY check_in_date DESC, created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    
    if ($status_filter !== 'all') {
        $stmt->bindParam(':status', $status_filter, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for frontend
    foreach ($reservations as &$reservation) {
        // Calculate days until check-in
        $check_in = new DateTime($reservation['check_in_date']);
        $check_in->setTime(0, 0, 0);
        $now = new DateTime();
        $now->setTime(0, 0, 0);
        
        // Calculate interval and determine direction
        $diff = $now->diff($check_in);
        // If invert=1, check-in is in the past (now > check_in), so make it negative
        // If invert=0, check-in is in the future (now < check_in), so keep it positive
        $reservation['days_until_checkin'] = $diff->days * ($diff->invert ? -1 : 1);
        $reservation['is_past_checkin'] = $reservation['days_until_checkin'] < 0;
        
        // Can request rebooking? (7+ days before check-in, downpayment paid, within 3 months)
        // Downpayment is non-refundable, so rebooking is allowed once downpayment is verified
        $three_months_from_now = date('Y-m-d', strtotime('+3 months'));
        $reservation['can_rebook'] = (
            in_array($reservation['status'], ['confirmed', 'rebooked', 'pending_confirmation']) &&
            $reservation['downpayment_verified'] == 1 &&
            $reservation['days_until_checkin'] >= 7 &&
            !$reservation['rebooking_requested'] &&
            $reservation['check_in_date'] <= $three_months_from_now
        );
        
        // Can cancel? (only before admin/staff confirmation - once confirmed cannot cancel)
        $reservation['can_cancel'] = (
            in_array($reservation['status'], ['pending_payment', 'pending_confirmation']) &&
            !$reservation['is_past_checkin'] &&
            $reservation['checked_in'] == 0
        );
        
        // Can upload payment?
        $reservation['can_upload_downpayment'] = (
            $reservation['status'] === 'pending_payment' &&
            $reservation['downpayment_paid'] == 0
        );
        
        // Can show full payment options (only if not yet paid)
        $reservation['can_upload_full_payment'] = (
            $reservation['status'] === 'confirmed' &&
            $reservation['downpayment_verified'] == 1 &&
            $reservation['full_payment_paid'] == 0
        );
        
        // Show payment info if already paid but not verified
        $reservation['show_full_payment_pending'] = (
            $reservation['status'] === 'confirmed' &&
            $reservation['full_payment_paid'] == 1 &&
            $reservation['full_payment_verified'] == 0
        );
        
        // Payment status labels
        if ($reservation['downpayment_verified'] == 1) {
            $reservation['downpayment_status'] = 'Verified';
        } elseif ($reservation['downpayment_paid'] == 1) {
            $reservation['downpayment_status'] = 'Pending Verification';
        } else {
            $reservation['downpayment_status'] = 'Not Paid';
        }
        
        if ($reservation['full_payment_verified'] == 1) {
            $reservation['full_payment_status'] = 'Verified';
        } elseif ($reservation['full_payment_paid'] == 1) {
            $reservation['full_payment_status'] = 'Pending Verification';
        } else {
            $reservation['full_payment_status'] = 'Not Paid';
        }
        
        // Status display
        $status_labels = [
            'pending_payment' => 'Pending Payment',
            'pending_confirmation' => 'Pending Admin Verification',
            'confirmed' => 'Confirmed',
            'checked_in' => 'Checked In',
            'checked_out' => 'Checked Out',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
            'forfeited' => 'Forfeited',
            'rebooked' => 'Rebooked'
        ];
        $reservation['status_label'] = $status_labels[$reservation['status']] ?? $reservation['status'];
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($reservations),
        'reservations' => $reservations
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
