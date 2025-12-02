<?php
/**
 * PayMongo Webhook Handler
 * Receives webhook events from PayMongo for payment status updates
 */

require_once '../config/database.php';
require_once '../config/paymongo.php';

// Get raw POST data
$raw_input = file_get_contents('php://input');
$webhook_data = json_decode($raw_input, true);

// Log webhook for debugging
error_log('PayMongo Webhook Received: ' . $raw_input);

// Verify webhook signature (optional but recommended)
$signature = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? '';
if (PAYMONGO_WEBHOOK_SECRET && $signature) {
    $computed_signature = hash_hmac('sha256', $raw_input, PAYMONGO_WEBHOOK_SECRET);
    if (!hash_equals($computed_signature, $signature)) {
        http_response_code(401);
        error_log('Invalid webhook signature');
        exit;
    }
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
    
    if (!$webhook_data || !isset($webhook_data['data'])) {
        throw new Exception('Invalid webhook data');
    }
    
    $event = $webhook_data['data'];
    $event_type = $event['attributes']['type'] ?? '';
    
    // Handle different event types
    switch ($event_type) {
        case 'source.chargeable':
            handleSourceChargeable($pdo, $event);
            break;
            
        case 'payment.paid':
            handlePaymentPaid($pdo, $event);
            break;
            
        case 'payment.failed':
            handlePaymentFailed($pdo, $event);
            break;
            
        default:
            error_log('Unhandled event type: ' . $event_type);
    }
    
    http_response_code(200);
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log('Webhook Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * Handle source.chargeable event
 * This means the GCash payment was authorized and ready to be charged
 */
function handleSourceChargeable($pdo, $event) {
    $source_id = $event['attributes']['data']['id'] ?? null;
    
    if (!$source_id) {
        throw new Exception('Source ID not found in event');
    }
    
    // Find reservation with this source ID
    $stmt = $pdo->prepare("
        SELECT * FROM reservations 
        WHERE paymongo_source_id = ? AND status = 'pending_payment'
    ");
    $stmt->execute([$source_id]);
    $reservation = $stmt->fetch();
    
    if (!$reservation) {
        error_log('No reservation found for source: ' . $source_id);
        return;
    }
    
    // Create payment from the source
    $amount_in_centavos = (int)($reservation['downpayment_amount'] * 100);
    
    $payment_data = [
        'data' => [
            'attributes' => [
                'amount' => $amount_in_centavos,
                'currency' => 'PHP',
                'source' => [
                    'id' => $source_id,
                    'type' => 'source'
                ],
                'description' => 'AR Homes Resort - Reservation #' . $reservation['reservation_id']
            ]
        ]
    ];
    
    $result = makePaymongoRequest('/payments', 'POST', $payment_data);
    
    if ($result['success']) {
        $payment = $result['data']['data'];
        $payment_id = $payment['id'];
        
        // Update reservation
        $stmt = $pdo->prepare("
            UPDATE reservations 
            SET paymongo_payment_id = ?,
                status = 'confirmed',
                payment_reference = ?,
                payment_date = NOW(),
                downpayment_verified = 1
            WHERE reservation_id = ?
        ");
        $stmt->execute([
            $payment_id,
            $payment_id,
            $reservation['reservation_id']
        ]);
        
        error_log('Payment created for reservation: ' . $reservation['reservation_id']);
    }
}

/**
 * Handle payment.paid event
 * Confirmation that payment was successfully processed
 */
function handlePaymentPaid($pdo, $event) {
    $payment_id = $event['attributes']['data']['id'] ?? null;
    
    if (!$payment_id) {
        return;
    }
    
    // Update reservation status
    $stmt = $pdo->prepare("
        UPDATE reservations 
        SET status = 'confirmed',
            payment_date = NOW(),
            downpayment_verified = 1
        WHERE paymongo_payment_id = ?
    ");
    $stmt->execute([$payment_id]);
    
    error_log('Payment confirmed: ' . $payment_id);
}

/**
 * Handle payment.failed event
 */
function handlePaymentFailed($pdo, $event) {
    $payment_id = $event['attributes']['data']['id'] ?? null;
    
    if (!$payment_id) {
        return;
    }
    
    error_log('Payment failed: ' . $payment_id);
    // You can add logic here to notify the user or mark the reservation
}
