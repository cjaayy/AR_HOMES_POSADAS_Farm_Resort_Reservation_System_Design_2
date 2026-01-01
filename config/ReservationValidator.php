<?php
/**
 * Reservation Validator Class
 * AR Homes Posadas Farm Resort Reservation System
 * 
 * Comprehensive validation for reservation operations
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/security.php';

class ReservationValidator {
    
    private PDO $pdo;
    private array $errors = [];
    
    /**
     * Reservation status constants
     */
    const STATUS_PENDING_PAYMENT = 'pending_payment';
    const STATUS_PENDING_CONFIRMATION = 'pending_confirmation';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CHECKED_IN = 'checked_in';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NO_SHOW = 'no_show';
    const STATUS_EXPIRED = 'expired';
    const STATUS_REBOOKED = 'rebooked';
    
    /**
     * Valid status transitions
     */
    const VALID_TRANSITIONS = [
        self::STATUS_PENDING_PAYMENT => [self::STATUS_PENDING_CONFIRMATION, self::STATUS_CANCELLED, self::STATUS_EXPIRED],
        self::STATUS_PENDING_CONFIRMATION => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED => [self::STATUS_CHECKED_IN, self::STATUS_CANCELLED, self::STATUS_NO_SHOW, self::STATUS_REBOOKED],
        self::STATUS_CHECKED_IN => [self::STATUS_COMPLETED],
        self::STATUS_COMPLETED => [], // Final state
        self::STATUS_CANCELLED => [], // Final state
        self::STATUS_NO_SHOW => [], // Final state
        self::STATUS_EXPIRED => [self::STATUS_PENDING_PAYMENT], // Can be renewed
        self::STATUS_REBOOKED => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
    ];
    
    /**
     * Booking type configurations
     */
    const BOOKING_CONFIGS = [
        'daytime' => [
            'check_in_time' => '09:00:00',
            'check_out_time' => '17:00:00',
            'duration_type' => 'days',
            'base_price' => 6000.00,
            'security_bond' => 2000.00,
            'overtime_charge' => 500.00,
            'min_duration' => 1,
            'max_duration' => 7
        ],
        'nighttime' => [
            'check_in_time' => '19:00:00',
            'check_out_time' => '07:00:00',
            'duration_type' => 'nights',
            'base_price' => 10000.00,
            'security_bond' => 2000.00,
            'overtime_charge' => 1000.00,
            'min_duration' => 1,
            'max_duration' => 7
        ],
        '22hours' => [
            'check_in_time' => '14:00:00',
            'check_out_time' => '12:00:00',
            'duration_type' => 'nights',
            'base_price' => 18000.00,
            'security_bond' => 2000.00,
            'overtime_charge' => 500.00,
            'min_duration' => 1,
            'max_duration' => 7
        ],
        'venue-daytime' => [
            'check_in_time' => '09:00:00',
            'check_out_time' => '17:00:00',
            'duration_type' => 'days',
            'base_price' => 6000.00,
            'security_bond' => 2000.00,
            'overtime_charge' => 500.00,
            'min_duration' => 1,
            'max_duration' => 3
        ],
        'venue-nighttime' => [
            'check_in_time' => '19:00:00',
            'check_out_time' => '07:00:00',
            'duration_type' => 'nights',
            'base_price' => 10000.00,
            'security_bond' => 2000.00,
            'overtime_charge' => 1000.00,
            'min_duration' => 1,
            'max_duration' => 3
        ],
        'venue-22hours' => [
            'check_in_time' => '14:00:00',
            'check_out_time' => '12:00:00',
            'duration_type' => 'nights',
            'base_price' => 18000.00,
            'security_bond' => 2000.00,
            'overtime_charge' => 500.00,
            'min_duration' => 1,
            'max_duration' => 3
        ]
    ];
    
    /**
     * Fixed downpayment amount
     */
    const DOWNPAYMENT_AMOUNT = 1000.00;
    
    /**
     * Reservation expiry time in hours (for unpaid reservations)
     */
    const RESERVATION_EXPIRY_HOURS = 24;
    
    /**
     * Minimum days before check-in for rebooking
     */
    const MIN_REBOOKING_DAYS = 7;
    
    /**
     * Maximum months ahead for rebooking
     */
    const MAX_REBOOKING_MONTHS = 3;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get validation errors
     */
    public function getErrors(): array {
        return $this->errors;
    }
    
    /**
     * Get first error message
     */
    public function getFirstError(): ?string {
        return $this->errors[0] ?? null;
    }
    
    /**
     * Clear errors
     */
    public function clearErrors(): void {
        $this->errors = [];
    }
    
    /**
     * Add error
     */
    private function addError(string $error): void {
        $this->errors[] = $error;
    }
    
    /**
     * Validate booking type
     */
    public function validateBookingType(?string $bookingType): bool {
        if (empty($bookingType)) {
            $this->addError('Booking type is required');
            return false;
        }
        
        if (!isset(self::BOOKING_CONFIGS[$bookingType])) {
            $this->addError('Invalid booking type');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate check-in date
     */
    public function validateCheckInDate(?string $date, string $bookingType = null): bool {
        if (empty($date)) {
            $this->addError('Check-in date is required');
            return false;
        }
        
        // Validate date format
        $sanitizedDate = Security::sanitizeDate($date);
        if (!$sanitizedDate) {
            $this->addError('Invalid date format. Please use YYYY-MM-DD');
            return false;
        }
        
        // Check if date is not in the past
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        $checkInDate = new DateTime($sanitizedDate);
        $checkInDate->setTime(0, 0, 0);
        
        if ($checkInDate < $today) {
            $this->addError('Check-in date cannot be in the past');
            return false;
        }
        
        // Check minimum advance booking (at least today)
        // Future: Could add minimum advance booking requirement
        
        // Check maximum advance booking (1 year ahead)
        $maxDate = (clone $today)->modify('+1 year');
        if ($checkInDate > $maxDate) {
            $this->addError('Cannot book more than 1 year in advance');
            return false;
        }
        
        return true;
    }
    
    /**
     * Check date availability for booking type
     * Returns: ['available' => bool, 'locked' => bool, 'pending_count' => int, 'message' => string]
     */
    public function checkDateAvailability(string $checkInDate, string $bookingType, ?string $excludeReservationId = null): array {
        // Check for confirmed/locked reservations on this date
        $sql = "
            SELECT 
                reservation_id,
                status,
                guest_name,
                downpayment_verified,
                date_locked
            FROM reservations 
            WHERE check_in_date = :date 
            AND booking_type = :type
            AND status IN ('confirmed', 'checked_in', 'pending_confirmation')
            AND (downpayment_verified = 1 OR date_locked = 1)
        ";
        
        $params = [':date' => $checkInDate, ':type' => $bookingType];
        
        if ($excludeReservationId) {
            $sql .= " AND reservation_id != :exclude_id";
            $params[':exclude_id'] = $excludeReservationId;
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $lockedBooking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lockedBooking) {
            return [
                'available' => false,
                'locked' => true,
                'pending_count' => 0,
                'message' => 'This date is already booked and confirmed.',
                'locked_by' => $lockedBooking['guest_name'] ?? 'Another guest'
            ];
        }
        
        // Count pending (unpaid) reservations
        $sql = "
            SELECT COUNT(*) as pending_count
            FROM reservations 
            WHERE check_in_date = :date 
            AND booking_type = :type
            AND status = 'pending_payment'
            AND downpayment_verified = 0
        ";
        
        $params = [':date' => $checkInDate, ':type' => $bookingType];
        
        if ($excludeReservationId) {
            $sql .= " AND reservation_id != :exclude_id";
            $params[':exclude_id'] = $excludeReservationId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $pendingCount = (int)($result['pending_count'] ?? 0);
        
        $message = 'Date is available! First to pay, first to reserve.';
        $warning = null;
        
        if ($pendingCount > 0) {
            $warning = "Note: {$pendingCount} pending reservation(s) for this date. Complete payment quickly to secure your booking!";
        }
        
        return [
            'available' => true,
            'locked' => false,
            'pending_count' => $pendingCount,
            'message' => $message,
            'warning' => $warning
        ];
    }
    
    /**
     * Validate duration based on booking type
     */
    public function validateDuration(?int $duration, string $bookingType): bool {
        if (!isset(self::BOOKING_CONFIGS[$bookingType])) {
            $this->addError('Invalid booking type');
            return false;
        }
        
        $config = self::BOOKING_CONFIGS[$bookingType];
        
        if ($duration === null || $duration < $config['min_duration']) {
            $durationType = $config['duration_type'];
            $this->addError("Minimum duration is {$config['min_duration']} {$durationType}");
            return false;
        }
        
        if ($duration > $config['max_duration']) {
            $durationType = $config['duration_type'];
            $this->addError("Maximum duration is {$config['max_duration']} {$durationType}");
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate number of guests
     */
    public function validateGuestCount(?int $guestCount, int $min = 1, int $max = 100): bool {
        if ($guestCount === null || $guestCount < $min) {
            $this->addError("Minimum guest count is {$min}");
            return false;
        }
        
        if ($guestCount > $max) {
            $this->addError("Maximum guest count is {$max}");
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate payment method
     */
    public function validatePaymentMethod(?string $method): bool {
        $validMethods = [
            'gcash', 'paymaya', 'grab_pay', 'card', 
            'dob_bpi', 'dob_ubp', 'atome', 'otc',
            'bank_transfer', 'cash'
        ];
        
        if (empty($method)) {
            $this->addError('Payment method is required');
            return false;
        }
        
        if (!in_array($method, $validMethods)) {
            $this->addError('Invalid payment method');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate status transition
     */
    public function canTransitionTo(string $currentStatus, string $newStatus): bool {
        if (!isset(self::VALID_TRANSITIONS[$currentStatus])) {
            $this->addError('Invalid current status');
            return false;
        }
        
        if (!in_array($newStatus, self::VALID_TRANSITIONS[$currentStatus])) {
            $this->addError("Cannot transition from '{$currentStatus}' to '{$newStatus}'");
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if reservation can be cancelled
     */
    public function canCancel(array $reservation): bool {
        // Cannot cancel if already checked in
        if ($reservation['checked_in'] == 1) {
            $this->addError('Cannot cancel after check-in');
            return false;
        }
        
        // Cannot cancel if downpayment is verified (must rebook instead)
        if ($reservation['downpayment_verified'] == 1) {
            $this->addError('Cancellation not allowed after payment verification. Please request rebooking instead.');
            return false;
        }
        
        // Cannot cancel if already cancelled
        if ($reservation['status'] === self::STATUS_CANCELLED) {
            $this->addError('Reservation is already cancelled');
            return false;
        }
        
        // Can only cancel pending reservations
        $cancellableStatuses = [self::STATUS_PENDING_PAYMENT, self::STATUS_PENDING_CONFIRMATION];
        if (!in_array($reservation['status'], $cancellableStatuses)) {
            $this->addError('This reservation cannot be cancelled');
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if reservation can be rebooked
     */
    public function canRebook(array $reservation): bool {
        // Must have verified downpayment
        if ($reservation['downpayment_verified'] != 1) {
            $this->addError('Downpayment must be verified before rebooking');
            return false;
        }
        
        // Cannot rebook if already checked in
        if ($reservation['checked_in'] == 1) {
            $this->addError('Cannot rebook after check-in');
            return false;
        }
        
        // Cannot rebook if rebooking already requested
        if ($reservation['rebooking_requested'] == 1 && !$reservation['rebooking_approved']) {
            $this->addError('A rebooking request is already pending');
            return false;
        }
        
        // Check days until check-in (minimum 7 days)
        $checkInDate = new DateTime($reservation['check_in_date']);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        $daysUntil = $today->diff($checkInDate)->days;
        $isPast = $checkInDate < $today;
        
        if ($isPast || $daysUntil < self::MIN_REBOOKING_DAYS) {
            $this->addError('Rebooking must be requested at least ' . self::MIN_REBOOKING_DAYS . ' days before check-in');
            return false;
        }
        
        // Valid statuses for rebooking
        $rebookableStatuses = [self::STATUS_CONFIRMED, self::STATUS_PENDING_CONFIRMATION, self::STATUS_REBOOKED];
        if (!in_array($reservation['status'], $rebookableStatuses)) {
            $this->addError('This reservation cannot be rebooked');
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate new rebooking date
     */
    public function validateRebookingDate(string $newDate, string $bookingType): bool {
        // First validate as a regular check-in date
        if (!$this->validateCheckInDate($newDate, $bookingType)) {
            return false;
        }
        
        // Check maximum rebooking window (3 months from now)
        $maxDate = new DateTime();
        $maxDate->modify('+' . self::MAX_REBOOKING_MONTHS . ' months');
        $newCheckIn = new DateTime($newDate);
        
        if ($newCheckIn > $maxDate) {
            $this->addError('New date must be within ' . self::MAX_REBOOKING_MONTHS . ' months from today');
            return false;
        }
        
        // Check availability
        $availability = $this->checkDateAvailability($newDate, $bookingType);
        if (!$availability['available']) {
            $this->addError($availability['message']);
            return false;
        }
        
        return true;
    }
    
    /**
     * Calculate pricing for reservation
     */
    public function calculatePricing(string $bookingType, int $duration, ?string $packageType = null): array {
        if (!isset(self::BOOKING_CONFIGS[$bookingType])) {
            throw new InvalidArgumentException('Invalid booking type');
        }
        
        $config = self::BOOKING_CONFIGS[$bookingType];
        $basePrice = $config['base_price'];
        
        // Calculate total amount
        $totalAmount = $basePrice * $duration;
        
        return [
            'base_price' => $basePrice,
            'duration' => $duration,
            'duration_type' => $config['duration_type'],
            'total_amount' => $totalAmount,
            'downpayment_amount' => self::DOWNPAYMENT_AMOUNT,
            'remaining_balance' => $totalAmount - self::DOWNPAYMENT_AMOUNT,
            'security_bond' => $config['security_bond'],
            'overtime_charge_per_hour' => $config['overtime_charge'],
            'check_in_time' => $config['check_in_time'],
            'check_out_time' => $config['check_out_time']
        ];
    }
    
    /**
     * Calculate check-out date based on booking type and duration
     */
    public function calculateCheckOutDate(string $checkInDate, string $bookingType, int $duration): string {
        if (!isset(self::BOOKING_CONFIGS[$bookingType])) {
            throw new InvalidArgumentException('Invalid booking type');
        }
        
        $config = self::BOOKING_CONFIGS[$bookingType];
        $checkIn = new DateTime($checkInDate);
        
        if ($config['duration_type'] === 'days') {
            // For daytime, check-out is on the same day or after (duration - 1) days
            $checkIn->modify('+' . ($duration - 1) . ' days');
        } else {
            // For nighttime/22hours, check-out is duration days after check-in
            $checkIn->modify('+' . $duration . ' days');
        }
        
        return $checkIn->format('Y-m-d');
    }
    
    /**
     * Get booking configuration
     */
    public function getBookingConfig(string $bookingType): ?array {
        return self::BOOKING_CONFIGS[$bookingType] ?? null;
    }
    
    /**
     * Check if reservation has expired (unpaid for too long)
     */
    public function hasExpired(array $reservation): bool {
        if ($reservation['status'] !== self::STATUS_PENDING_PAYMENT) {
            return false;
        }
        
        if ($reservation['downpayment_paid'] == 1) {
            return false;
        }
        
        $createdAt = new DateTime($reservation['created_at']);
        $now = new DateTime();
        $hoursDiff = ($now->getTimestamp() - $createdAt->getTimestamp()) / 3600;
        
        return $hoursDiff > self::RESERVATION_EXPIRY_HOURS;
    }
    
    /**
     * Full validation for new reservation
     */
    public function validateNewReservation(array $data): bool {
        $this->clearErrors();
        
        // Required fields
        $requiredFields = ['booking_type', 'check_in_date', 'payment_method'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $this->addError("Missing required field: {$field}");
            }
        }
        
        if (!empty($this->errors)) {
            return false;
        }
        
        // Validate booking type
        if (!$this->validateBookingType($data['booking_type'])) {
            return false;
        }
        
        // Validate check-in date
        if (!$this->validateCheckInDate($data['check_in_date'], $data['booking_type'])) {
            return false;
        }
        
        // Validate payment method
        if (!$this->validatePaymentMethod($data['payment_method'])) {
            return false;
        }
        
        // Validate duration
        $config = self::BOOKING_CONFIGS[$data['booking_type']];
        $durationKey = $config['duration_type'] === 'days' ? 'number_of_days' : 'number_of_nights';
        $duration = isset($data[$durationKey]) ? (int)$data[$durationKey] : 1;
        
        if (!$this->validateDuration($duration, $data['booking_type'])) {
            return false;
        }
        
        // Validate guest count if provided
        if (isset($data['number_of_guests'])) {
            $guestCount = (int)$data['number_of_guests'];
            if (!$this->validateGuestCount($guestCount)) {
                return false;
            }
        }
        
        // Check availability
        $availability = $this->checkDateAvailability($data['check_in_date'], $data['booking_type']);
        if (!$availability['available']) {
            $this->addError($availability['message']);
            return false;
        }
        
        return true;
    }
}
?>
