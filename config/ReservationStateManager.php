<?php
/**
 * Reservation State Manager
 * AR Homes Posadas Farm Resort Reservation System
 * 
 * Manages reservation state transitions with audit logging
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/ReservationValidator.php';

class ReservationStateManager {
    
    private PDO $pdo;
    private ?string $actorId;
    private string $actorType;
    
    /**
     * Status descriptions for display
     */
    const STATUS_LABELS = [
        'pending_payment' => 'Pending Payment',
        'pending_confirmation' => 'Awaiting Confirmation',
        'confirmed' => 'Confirmed',
        'checked_in' => 'Checked In',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'no_show' => 'No Show',
        'expired' => 'Expired',
        'rebooked' => 'Rebooked'
    ];
    
    /**
     * Status colors for UI
     */
    const STATUS_COLORS = [
        'pending_payment' => '#fbbf24',    // Yellow
        'pending_confirmation' => '#fb923c', // Orange
        'confirmed' => '#22c55e',          // Green
        'checked_in' => '#3b82f6',         // Blue
        'completed' => '#10b981',          // Emerald
        'cancelled' => '#ef4444',          // Red
        'no_show' => '#6b7280',            // Gray
        'expired' => '#9ca3af',            // Light gray
        'rebooked' => '#8b5cf6'            // Purple
    ];
    
    public function __construct(PDO $pdo, ?string $actorId = null, string $actorType = 'system') {
        $this->pdo = $pdo;
        $this->actorId = $actorId;
        $this->actorType = $actorType; // 'user', 'admin', 'staff', 'system'
    }
    
    /**
     * Get reservation by ID
     */
    public function getReservation(string $reservationId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM reservations 
            WHERE reservation_id = :id
        ");
        $stmt->execute([':id' => $reservationId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Get reservation status label
     */
    public static function getStatusLabel(string $status): string {
        return self::STATUS_LABELS[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }
    
    /**
     * Get status color
     */
    public static function getStatusColor(string $status): string {
        return self::STATUS_COLORS[$status] ?? '#6b7280';
    }
    
    /**
     * Transition reservation to new status
     */
    public function transitionTo(string $reservationId, string $newStatus, array $additionalData = []): array {
        try {
            // Get current reservation
            $reservation = $this->getReservation($reservationId);
            if (!$reservation) {
                return [
                    'success' => false,
                    'message' => 'Reservation not found',
                    'error_code' => 'NOT_FOUND'
                ];
            }
            
            $currentStatus = $reservation['status'];
            
            // Validate transition
            $validator = new ReservationValidator($this->pdo);
            if (!$validator->canTransitionTo($currentStatus, $newStatus)) {
                return [
                    'success' => false,
                    'message' => $validator->getFirstError(),
                    'error_code' => 'INVALID_TRANSITION'
                ];
            }
            
            // Prepare update data
            $updateFields = ['status' => $newStatus, 'updated_at' => date('Y-m-d H:i:s')];
            
            // Add status-specific fields
            switch ($newStatus) {
                case 'pending_confirmation':
                    if (!empty($additionalData['downpayment_proof'])) {
                        $updateFields['downpayment_proof'] = $additionalData['downpayment_proof'];
                        $updateFields['downpayment_paid'] = 1;
                        $updateFields['downpayment_paid_at'] = date('Y-m-d H:i:s');
                    }
                    if (!empty($additionalData['downpayment_reference'])) {
                        $updateFields['downpayment_reference'] = $additionalData['downpayment_reference'];
                    }
                    if (!empty($additionalData['payment_method'])) {
                        $updateFields['payment_method'] = $additionalData['payment_method'];
                    }
                    break;
                    
                case 'confirmed':
                    $updateFields['downpayment_verified'] = 1;
                    $updateFields['downpayment_verified_at'] = date('Y-m-d H:i:s');
                    $updateFields['downpayment_verified_by'] = $this->actorId;
                    $updateFields['date_locked'] = 1;
                    break;
                    
                case 'checked_in':
                    $updateFields['checked_in'] = 1;
                    $updateFields['checked_in_at'] = date('Y-m-d H:i:s');
                    $updateFields['checked_in_by'] = $this->actorId;
                    if (isset($additionalData['security_bond_collected'])) {
                        $updateFields['security_bond_paid'] = 1;
                        $updateFields['security_bond_paid_at'] = date('Y-m-d H:i:s');
                    }
                    break;
                    
                case 'completed':
                    $updateFields['checked_out'] = 1;
                    $updateFields['checked_out_at'] = date('Y-m-d H:i:s');
                    $updateFields['checked_out_by'] = $this->actorId;
                    if (isset($additionalData['actual_checkout_time'])) {
                        $updateFields['actual_checkout_time'] = $additionalData['actual_checkout_time'];
                    }
                    if (isset($additionalData['overtime_hours'])) {
                        $updateFields['overtime_hours'] = $additionalData['overtime_hours'];
                    }
                    if (isset($additionalData['overtime_charges'])) {
                        $updateFields['overtime_charges'] = $additionalData['overtime_charges'];
                    }
                    if (isset($additionalData['damage_charges'])) {
                        $updateFields['damage_charges'] = $additionalData['damage_charges'];
                    }
                    if (isset($additionalData['final_amount'])) {
                        $updateFields['final_amount'] = $additionalData['final_amount'];
                    }
                    if (isset($additionalData['security_bond_returned'])) {
                        $updateFields['security_bond_returned'] = 1;
                        $updateFields['security_bond_returned_at'] = date('Y-m-d H:i:s');
                        $updateFields['security_bond_deduction'] = $additionalData['security_bond_deduction'] ?? 0;
                    }
                    break;
                    
                case 'cancelled':
                    $updateFields['cancelled_at'] = date('Y-m-d H:i:s');
                    $updateFields['cancelled_by'] = $this->actorId;
                    if (!empty($additionalData['cancellation_reason'])) {
                        $updateFields['cancellation_reason'] = $additionalData['cancellation_reason'];
                    }
                    $updateFields['date_locked'] = 0;
                    $updateFields['locked_until'] = null;
                    break;
                    
                case 'no_show':
                    $updateFields['no_show_marked_at'] = date('Y-m-d H:i:s');
                    $updateFields['no_show_marked_by'] = $this->actorId;
                    break;
                    
                case 'expired':
                    $updateFields['date_locked'] = 0;
                    $updateFields['locked_until'] = null;
                    break;
                    
                case 'rebooked':
                    if (!empty($additionalData['new_check_in_date'])) {
                        $updateFields['rebooking_requested'] = 1;
                        $updateFields['rebooking_new_date'] = $additionalData['new_check_in_date'];
                    }
                    if (!empty($additionalData['rebooking_reason'])) {
                        $updateFields['rebooking_reason'] = $additionalData['rebooking_reason'];
                    }
                    break;
            }
            
            // Add any notes
            if (!empty($additionalData['admin_notes'])) {
                $notePrefix = "[" . date('Y-m-d H:i:s') . "] [{$this->actorType}] ";
                $updateFields['admin_notes'] = $this->pdo->quote(
                    $notePrefix . $additionalData['admin_notes']
                );
            }
            
            // Build and execute update query
            $setClauses = [];
            $params = [':id' => $reservationId];
            
            foreach ($updateFields as $field => $value) {
                if ($field === 'admin_notes') {
                    // Special handling for notes concatenation
                    $setClauses[] = "admin_notes = CONCAT(COALESCE(admin_notes, ''), '\n', {$value})";
                } else {
                    $setClauses[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $value;
                }
            }
            
            $sql = "UPDATE reservations SET " . implode(', ', $setClauses) . " WHERE reservation_id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            // Log the transition
            $this->logTransition($reservationId, $currentStatus, $newStatus, $additionalData);
            
            // Get updated reservation
            $updatedReservation = $this->getReservation($reservationId);
            
            return [
                'success' => true,
                'message' => 'Status updated successfully',
                'previous_status' => $currentStatus,
                'new_status' => $newStatus,
                'reservation' => $updatedReservation
            ];
            
        } catch (Exception $e) {
            error_log("State transition error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update status',
                'error_code' => 'UPDATE_FAILED',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Log state transition
     */
    private function logTransition(string $reservationId, string $fromStatus, string $toStatus, array $additionalData = []): void {
        // Log to error_log for now
        // TODO: Implement proper audit table
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'reservation_id' => $reservationId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'actor_id' => $this->actorId,
            'actor_type' => $this->actorType,
            'additional_data' => $additionalData
        ];
        
        error_log('[RESERVATION_TRANSITION] ' . json_encode($logEntry));
        
        // Also log to security
        Security::logSecurityEvent('STATUS_CHANGE', "Reservation {$reservationId}: {$fromStatus} -> {$toStatus}", [
            'reservation_id' => $reservationId,
            'actor_id' => $this->actorId
        ]);
    }
    
    /**
     * Record payment (downpayment or full)
     */
    public function recordPayment(string $reservationId, string $paymentType, array $paymentData): array {
        $reservation = $this->getReservation($reservationId);
        if (!$reservation) {
            return ['success' => false, 'message' => 'Reservation not found'];
        }
        
        $updateFields = [];
        $newStatus = null;
        
        if ($paymentType === 'downpayment') {
            if ($reservation['downpayment_paid'] == 1) {
                return ['success' => false, 'message' => 'Downpayment already recorded'];
            }
            
            $updateFields = [
                'downpayment_paid' => 1,
                'downpayment_paid_at' => date('Y-m-d H:i:s'),
                'downpayment_proof' => $paymentData['proof'] ?? null,
                'downpayment_reference' => $paymentData['reference'] ?? null,
                'payment_method' => $paymentData['method'] ?? null
            ];
            $newStatus = 'pending_confirmation';
            
        } elseif ($paymentType === 'full_payment') {
            if ($reservation['full_payment_paid'] == 1) {
                return ['success' => false, 'message' => 'Full payment already recorded'];
            }
            
            $updateFields = [
                'full_payment_paid' => 1,
                'full_payment_paid_at' => date('Y-m-d H:i:s'),
                'full_payment_proof' => $paymentData['proof'] ?? null,
                'full_payment_reference' => $paymentData['reference'] ?? null
            ];
            // Status doesn't change for full payment, just awaits verification
        }
        
        if (empty($updateFields)) {
            return ['success' => false, 'message' => 'Invalid payment type'];
        }
        
        try {
            // Build update query
            $setClauses = [];
            $params = [':id' => $reservationId];
            
            foreach ($updateFields as $field => $value) {
                if ($value !== null) {
                    $setClauses[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $value;
                }
            }
            
            if ($newStatus) {
                $setClauses[] = "status = :status";
                $params[':status'] = $newStatus;
            }
            
            $setClauses[] = "updated_at = NOW()";
            
            $sql = "UPDATE reservations SET " . implode(', ', $setClauses) . " WHERE reservation_id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            Security::logSecurityEvent('PAYMENT_RECORDED', "Payment recorded for {$reservationId}", [
                'payment_type' => $paymentType,
                'reservation_id' => $reservationId
            ]);
            
            return [
                'success' => true,
                'message' => ucfirst($paymentType) . ' recorded successfully',
                'new_status' => $newStatus
            ];
            
        } catch (Exception $e) {
            error_log("Record payment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to record payment'];
        }
    }
    
    /**
     * Verify payment (admin action)
     */
    public function verifyPayment(string $reservationId, string $paymentType): array {
        $reservation = $this->getReservation($reservationId);
        if (!$reservation) {
            return ['success' => false, 'message' => 'Reservation not found'];
        }
        
        try {
            if ($paymentType === 'downpayment') {
                if ($reservation['downpayment_paid'] != 1) {
                    return ['success' => false, 'message' => 'Downpayment has not been paid'];
                }
                if ($reservation['downpayment_verified'] == 1) {
                    return ['success' => false, 'message' => 'Downpayment already verified'];
                }
                
                return $this->transitionTo($reservationId, 'confirmed', [
                    'admin_notes' => 'Downpayment verified'
                ]);
                
            } elseif ($paymentType === 'full_payment') {
                if ($reservation['full_payment_paid'] != 1) {
                    return ['success' => false, 'message' => 'Full payment has not been paid'];
                }
                if ($reservation['full_payment_verified'] == 1) {
                    return ['success' => false, 'message' => 'Full payment already verified'];
                }
                
                $stmt = $this->pdo->prepare("
                    UPDATE reservations 
                    SET full_payment_verified = 1,
                        full_payment_verified_at = NOW(),
                        full_payment_verified_by = :admin_id,
                        remaining_balance = 0,
                        updated_at = NOW()
                    WHERE reservation_id = :id
                ");
                
                $stmt->execute([
                    ':admin_id' => $this->actorId,
                    ':id' => $reservationId
                ]);
                
                Security::logSecurityEvent('PAYMENT_VERIFIED', "Full payment verified for {$reservationId}", [
                    'reservation_id' => $reservationId,
                    'verified_by' => $this->actorId
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Full payment verified successfully'
                ];
            }
            
            return ['success' => false, 'message' => 'Invalid payment type'];
            
        } catch (Exception $e) {
            error_log("Verify payment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to verify payment'];
        }
    }
    
    /**
     * Get all reservations for a user
     */
    public function getUserReservations(string $userId, ?string $statusFilter = null): array {
        $sql = "SELECT * FROM reservations WHERE user_id = :user_id";
        $params = [':user_id' => $userId];
        
        if ($statusFilter) {
            $sql .= " AND status = :status";
            $params[':status'] = $statusFilter;
        }
        
        $sql .= " ORDER BY check_in_date DESC, created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add status labels
        foreach ($reservations as &$res) {
            $res['status_label'] = self::getStatusLabel($res['status']);
            $res['status_color'] = self::getStatusColor($res['status']);
        }
        
        return $reservations;
    }
    
    /**
     * Check for upcoming reservations that need attention
     */
    public function getReservationsNeedingAttention(): array {
        $results = [
            'expiring_soon' => [],
            'pending_checkin_today' => [],
            'overdue_checkout' => [],
            'unverified_payments' => []
        ];
        
        // Reservations expiring within 4 hours
        $stmt = $this->pdo->query("
            SELECT * FROM reservations
            WHERE status = 'pending_payment'
            AND (downpayment_paid = 0 OR downpayment_paid IS NULL)
            AND locked_until BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 4 HOUR)
            ORDER BY locked_until ASC
        ");
        $results['expiring_soon'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Check-ins due today
        $stmt = $this->pdo->query("
            SELECT * FROM reservations
            WHERE status = 'confirmed'
            AND check_in_date = CURDATE()
            AND checked_in = 0
            ORDER BY check_in_time ASC
        ");
        $results['pending_checkin_today'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Overdue checkouts
        $stmt = $this->pdo->query("
            SELECT * FROM reservations
            WHERE status = 'checked_in'
            AND check_out_date < CURDATE()
            ORDER BY check_out_date ASC
        ");
        $results['overdue_checkout'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Unverified payments
        $stmt = $this->pdo->query("
            SELECT * FROM reservations
            WHERE status = 'pending_confirmation'
            AND downpayment_paid = 1
            AND downpayment_verified = 0
            ORDER BY downpayment_paid_at ASC
        ");
        $results['unverified_payments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $results;
    }
}
?>
