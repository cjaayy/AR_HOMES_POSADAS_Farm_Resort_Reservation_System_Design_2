# Rebooking for Fully Paid Reservations - Implementation Summary

**Date:** December 3, 2024  
**Feature:** Rebooking button for fully paid bookings with date release mechanism

---

## Overview

This implementation enables **fully paid guests** to request rebooking when they cannot attend their reserved date. When approved by admin, the **original date becomes available** for other users to book, and the guest can select a new date **within 3 months** of the original booking.

---

## Business Rules

### Eligibility Criteria

1. ✅ **Full payment verified** (`full_payment_verified = 1`)
2. ✅ **Status:** `confirmed` or `rebooked`
3. ✅ **Timing:** At least **7 days before** check-in date
4. ✅ **No pending requests:** `rebooking_requested = 0`
5. ✅ **Within 3 months:** Original date must be within 3 months from today

### Date Selection Rules

1. ✅ New date must be **in the future**
2. ✅ New date must be **within 3 months** of original check-in date
3. ✅ New date must be **available** (cross-package blocking applies)
4. ✅ Date picker enforces min/max constraints

### Approval Process

1. 🔄 User submits rebooking request → status remains `confirmed`, original date **still occupied**
2. ✅ Admin approves → date changes to new date, status remains `confirmed`, original date **released**
3. ❌ Admin rejects → rebooking fields cleared, original date **remains booked**

---

## Technical Implementation

### 1. Database Changes

#### Updated can_rebook Logic

**File:** `user/get_my_reservations.php` (lines 137-145)

```php
// Can request rebooking? (7+ days before check-in, fully paid, within 3 months)
$three_months_from_now = date('Y-m-d', strtotime('+3 months'));
$reservation['can_rebook'] = (
    in_array($reservation['status'], ['confirmed', 'rebooked']) &&
    $reservation['full_payment_verified'] == 1 &&
    $reservation['days_until_checkin'] >= 7 &&
    !$reservation['rebooking_requested'] &&
    $reservation['check_in_date'] <= $three_months_from_now
);
```

**Changes:**

- ✅ Added `full_payment_verified = 1` requirement
- ✅ Added 3-month window validation
- ✅ Allows rebooking for `rebooked` status (for re-rebooking)

---

### 2. Rebooking Request Validation

**File:** `user/request_rebooking.php` (lines 85-125)

#### Validation Checks

1. **Full payment required:**

   ```php
   if ($reservation['full_payment_verified'] != 1) {
       throw new Exception('Rebooking is only available for fully paid reservations');
   }
   ```

2. **7-day minimum:**

   ```php
   if ($days_until_checkin < 7) {
       throw new Exception('Rebooking must be requested at least 7 days before check-in date');
   }
   ```

3. **3-month maximum:**

   ```php
   $three_months_later = clone $original_date;
   $three_months_later->modify('+3 months');

   if ($new_date_obj > $three_months_later) {
       throw new Exception('New date must be within 3 months of original check-in date');
   }
   ```

4. **Cross-package availability:**
   ```php
   SELECT COUNT(*) as count FROM reservations
   WHERE status IN ('confirmed', 'checked_in', 'rebooked')
   AND reservation_id != :id
   AND (
       (check_in_date <= :new_date AND check_out_date >= :new_date)
       OR (check_in_date <= :new_check_out AND check_out_date >= :new_check_out)
       OR (check_in_date >= :new_date AND check_out_date <= :new_check_out)
   )
   ```

---

### 3. Admin Approval Updates

**File:** `admin/approve_rebooking.php` (lines 66-90)

#### Key Changes

```php
// Calculate check_out_date based on booking type
$check_out_date = $reservation['rebooking_new_date'];
if ($reservation['booking_type'] === 'nighttime' || $reservation['booking_type'] === '22hours') {
    $check_out_date = date('Y-m-d', strtotime($reservation['rebooking_new_date'] . ' +1 day'));
}

// Approve rebooking: Update dates, keep payment status, lock new date
// Old date automatically becomes available for other users
UPDATE reservations
SET check_in_date = rebooking_new_date,
    check_out_date = :check_out_date,
    rebooking_approved = 1,
    rebooking_approved_by = :admin_id,
    rebooking_approved_at = NOW(),
    date_locked = 1,
    locked_until = DATE_ADD(rebooking_new_date, INTERVAL 1 DAY),
    status = 'confirmed',
    updated_at = NOW()
WHERE reservation_id = :id
```

**Important:**

- ✅ Original date is **automatically released** when dates change
- ✅ Payment status (`full_payment_verified`, `downpayment_verified`) is **preserved**
- ✅ Status changes to `confirmed` (ready for check-in)

---

### 4. Frontend Updates

#### Rebooking Button Display

**File:** `booking-flow.js` (lines 422-434)

```javascript
// Request rebooking - available for FULLY PAID bookings within 3 months
// When approved, original date becomes available for other users
if (booking.can_rebook) {
  html += `
    <button class="btn-primary" onclick="openRebookingModal('${booking.reservation_id}', '${booking.check_in_date}')" 
            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
      <i class="fas fa-calendar-alt"></i> Request Rebooking (Within 3 Months)
    </button>
    <div style="background: #e8f5e9; border-left: 4px solid #4caf50; padding: 10px; border-radius: 6px; margin-top: 8px; font-size: 0.85em;">
      <i class="fas fa-check-circle" style="color: #2e7d32;"></i>
      <strong style="color: #2e7d32;">Rebooking Available:</strong>
      <span style="color: #1b5e20;"> Your original date will be released for other guests when rebooking is approved.</span>
    </div>
  `;
}
```

#### Policy Notices

**File:** `booking-flow.js` (lines 437-465)

```javascript
// For fully paid reservations (if rebooking not yet available)
if (
  (booking.status === "confirmed" || booking.status === "rebooked") &&
  booking.full_payment_verified == 1
) {
  if (!booking.can_rebook) {
    html += `
      <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; border-radius: 6px; margin-top: 10px;">
        <strong style="color: #856404; display: block; margin-bottom: 5px;">
          <i class="fas fa-info-circle"></i> Rebooking Policy
        </strong>
        <p style="margin: 0; color: #856404; font-size: 0.9em;">
          • Rebooking available <strong>7 days before</strong> check-in<br>
          • New date must be <strong>within 3 months</strong><br>
          • Original date will be released to other guests
        </p>
      </div>
    `;
  }
}
```

#### Date Picker Constraints

**File:** `booking-flow.js` (lines 601-621)

```javascript
// Set date constraints: min = today, max = 3 months from original date
const todayStr = new Date().toISOString().split("T")[0];
const originalDate = new Date(currentCheckInDate);
const threeMonthsLater = new Date(originalDate);
threeMonthsLater.setMonth(threeMonthsLater.getMonth() + 3);
const maxDateStr = threeMonthsLater.toISOString().split("T")[0];

newCheckInElement.setAttribute("min", todayStr);
newCheckInElement.setAttribute("max", maxDateStr);

// Show date range info
const dateRangeInfo = document.createElement("div");
dateRangeInfo.innerHTML = `
  <i class="fas fa-calendar-check"></i> 
  <strong>Valid Date Range:</strong> ${formatDate(todayStr)} to ${formatDate(
  maxDateStr
)} (within 3 months)
`;
```

---

## User Flow

### Step 1: View Reservation

- User sees their **fully paid** reservation
- If eligible (7+ days before, within 3 months), **"Request Rebooking"** button appears
- Green info box explains: _"Your original date will be released for other guests when rebooking is approved"_

### Step 2: Select New Date

- User clicks **"Request Rebooking"** button
- Modal opens with date picker
- Date picker shows **valid date range** (today to +3 months from original)
- Min/max attributes prevent invalid selection
- Blue info banner displays: _"Valid Date Range: [date] to [date] (within 3 months)"_

### Step 3: Submit Request

- User selects new date, enters reason
- System validates:
  - ✅ Full payment verified
  - ✅ At least 7 days remaining
  - ✅ Within 3-month window
  - ✅ New date available
  - ✅ Date in the future
- Success message: _"Rebooking request submitted successfully! Your original date will be released once admin approves your request."_

### Step 4: Admin Review

- Admin sees rebooking request in staff dashboard
- Admin checks availability of new date
- Admin approves or rejects

### Step 5: Confirmation

- **If approved:**
  - Reservation dates update to new date
  - Status remains `confirmed`
  - Payment status preserved
  - Original date **immediately available** for other users
- **If rejected:**
  - Rebooking fields cleared
  - Original date **remains booked**
  - User can try again with different date

---

## Date Release Mechanism

### When Original Date Becomes Available

#### ❌ NOT Available During Request

```
User Request Submitted
↓
rebooking_requested = 1
rebooking_new_date = '2025-02-15'
check_in_date = '2025-01-10' ← STILL OCCUPIED
↓
Admin Reviews
```

#### ✅ Available After Approval

```
Admin Approves
↓
check_in_date = '2025-02-15' ← UPDATED
rebooking_approved = 1
rebooking_requested = 1 (historical record)
↓
Original date '2025-01-10' RELEASED
Other users can now book this date
```

### Cross-Package Blocking

- When original date is released, it becomes available for **ALL packages** (DAYTIME, NIGHTTIME, 22 HOURS)
- New date follows same exclusive resort access rule

---

## Testing Checklist

### Functional Tests

- [ ] Rebooking button appears only for fully paid reservations
- [ ] Rebooking button hidden if less than 7 days before check-in
- [ ] Rebooking button hidden if already requested
- [ ] Date picker enforces min (today) and max (+3 months from original)
- [ ] Error shown if new date not available
- [ ] Error shown if new date beyond 3-month window
- [ ] Success message after submission
- [ ] Admin can approve/reject request
- [ ] Original date released after approval
- [ ] Payment status preserved after approval
- [ ] Check-out date calculated correctly for NIGHTTIME/22HOURS

### Edge Cases

- [ ] User tries to rebook past reservation → Blocked
- [ ] User tries to rebook 6 days before check-in → Blocked
- [ ] User tries date 3 months + 1 day → Blocked
- [ ] User tries already booked date → Error message
- [ ] Admin approves but date becomes unavailable → Error shown
- [ ] User requests rebooking twice → Second blocked

### Cross-Package Tests

- [ ] DAYTIME booking rebooked → Original date available for NIGHTTIME
- [ ] NIGHTTIME booking rebooked → Original date available for 22 HOURS
- [ ] 22 HOURS booking rebooked → Original date available for DAYTIME

---

## Error Messages

### User-Facing Errors

1. **Not fully paid:**

   > "Rebooking is only available for fully paid reservations"

2. **Too close to check-in:**

   > "Rebooking must be requested at least 7 days before check-in date"

3. **Beyond 3-month window:**

   > "New date must be within 3 months of original check-in date (Jan 10, 2025 - Apr 10, 2025)"

4. **Date unavailable:**

   > "The selected date is not available. Please choose another date."

5. **Already requested:**

   > "Rebooking has already been requested for this reservation"

6. **Past date:**
   > "New date must be in the future"

---

## Files Modified

### Backend (PHP)

1. ✅ `user/get_my_reservations.php` - Updated can_rebook logic
2. ✅ `user/request_rebooking.php` - Enhanced validation (3-month, full payment, cross-package)
3. ✅ `admin/approve_rebooking.php` - Updated to calculate check_out_date correctly

### Frontend (JavaScript)

4. ✅ `booking-flow.js` - Added rebooking button, policy notices, date constraints
5. ✅ `dashboard.html` - Updated script version (v=2.1)

---

## Version History

- **v2.1** (Dec 3, 2024) - Rebooking for fully paid reservations
  - Added full payment requirement
  - Implemented 3-month date window
  - Enhanced date picker with min/max constraints
  - Added visual date range indicator
  - Updated policy notices
- **v2.0** (Dec 3, 2024) - Rebooking-only policy
  - Removed cancellation for confirmed bookings
  - Added rebooking guidance notices

---

## Future Enhancements (Optional)

1. **Email Notifications:**

   - Notify user when rebooking approved/rejected
   - Notify admin when rebooking requested

2. **Calendar Integration:**

   - Show unavailable dates in rebooking modal date picker
   - Use Flatpickr with disable array

3. **Auto-Rejection:**

   - Auto-reject if new date becomes unavailable before admin reviews

4. **Re-Rebooking:**

   - Allow multiple rebooking requests (currently supports via `rebooked` status)

5. **Admin Notes:**
   - Add admin_notes field to rebooking approval/rejection

---

## Support & Troubleshooting

### Common Issues

**Q: User says rebooking button doesn't appear**

- ✅ Check if fully paid (`full_payment_verified = 1`)
- ✅ Check if at least 7 days before check-in
- ✅ Check if within 3-month window
- ✅ Check if no pending rebooking request

**Q: User can't select certain dates**

- ✅ Date picker enforces min (today) and max (+3 months)
- ✅ Check browser console for date picker errors

**Q: Original date not released after approval**

- ✅ Verify `check_in_date` updated in database
- ✅ Check `user/get_unavailable_dates.php` query

**Q: Check-out date wrong after rebooking**

- ✅ Verify booking_type (nighttime/22hours should +1 day)
- ✅ Check `approve_rebooking.php` check_out_date calculation

---

## Database Schema Reference

### Reservations Table Fields (Rebooking)

```sql
rebooking_requested TINYINT(1) DEFAULT 0
rebooking_new_date DATE NULL
rebooking_reason TEXT NULL
rebooking_requested_at DATETIME NULL
rebooking_approved TINYINT(1) DEFAULT 0
rebooking_approved_by INT NULL
rebooking_approved_at DATETIME NULL
```

---

## Conclusion

✅ **Fully paid guests** can now request rebooking within 3 months  
✅ **Original dates** are released when admin approves  
✅ **Date picker** enforces 3-month window  
✅ **Cross-package blocking** ensures exclusive resort access  
✅ **Payment status** preserved through rebooking process

**Result:** Enhanced flexibility for guests while maintaining booking integrity and resort exclusivity.
