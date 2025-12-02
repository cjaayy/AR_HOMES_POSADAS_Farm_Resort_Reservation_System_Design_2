# Full Payment Online Option - Implementation Guide

## Overview

This update adds the ability for users to pay their remaining balance online via PayMongo or choose to pay at the resort.

## Database Migration Required

Before using this feature, you MUST run the database migration:

### Steps:

1. Open phpMyAdmin or MySQL command line
2. Select your database (e.g., `ar_homes_resort`)
3. Run the SQL file: `config/full_payment_migration.sql`

OR run this command in MySQL:

```sql
ALTER TABLE reservations
    ADD COLUMN IF NOT EXISTS paymongo_full_payment_link_id VARCHAR(255) NULL
    COMMENT 'PayMongo link ID for full payment/remaining balance'
    AFTER paymongo_full_payment_id;

ALTER TABLE reservations
    ADD INDEX IF NOT EXISTS idx_paymongo_full_link (paymongo_full_payment_link_id);
```

## Features Added

### 1. **User Interface Updates**

- In "My Bookings" section, users with confirmed reservations now see two payment options:
  - **Pay Remaining Balance Online** - Opens PayMongo checkout for online payment
  - **Pay at Resort** - Shows information about paying at the resort

### 2. **Backend Updates**

- `create_payment_intent.php` now supports `payment_type` parameter:
  - `downpayment` - For initial payment (default)
  - `full_payment` - For remaining balance payment
- `paymongo_webhook.php` updated to handle full payment webhooks:
  - Tracks full payment status separately from downpayment
  - Updates `full_payment_paid`, `full_payment_reference`, and `full_payment_paid_at` fields

### 3. **Frontend Updates**

- `booking-flow.js` new functions:
  - `payFullBalanceWithPayMongo()` - Handles full payment via PayMongo
  - `showPayAtResortInfo()` - Shows info modal about paying at resort
- Payment buttons are dynamically rendered based on booking status
- Full payment status now shows the amount and verification status

## User Flow

### For Users:

1. User makes a reservation and pays downpayment
2. Admin verifies the downpayment
3. Booking status changes to "Confirmed"
4. User sees remaining balance payment options in "My Bookings":
   - Option A: Click "Pay Remaining Balance Online" → Redirected to PayMongo
   - Option B: Click "Pay at Resort" → See info about resort payment methods
5. If paid online, payment is tracked and admin can verify later
6. If paying at resort, user settles during check-in or stay

### For Admins:

1. Admin verifies downpayment as usual
2. If user pays remaining balance online:
   - Admin can see payment status in admin dashboard
   - Admin verifies full payment similar to downpayment verification
3. If user pays at resort:
   - Admin marks payment as received manually during check-in/checkout

## Files Modified

### Frontend:

- `booking-flow.js` - Added full payment UI and logic

### Backend:

- `user/create_payment_intent.php` - Added support for full_payment type
- `user/paymongo_webhook.php` - Added full payment webhook handling
- `user/get_my_reservations.php` - Already supports full payment fields

### Database:

- `config/full_payment_migration.sql` - NEW migration file

## Testing Checklist

- [ ] Run database migration
- [ ] Make a test reservation
- [ ] Pay downpayment via PayMongo
- [ ] Admin verifies downpayment
- [ ] Check if "Pay Remaining Balance" buttons appear
- [ ] Click "Pay Remaining Balance Online" - should redirect to PayMongo
- [ ] Click "Pay at Resort" - should show info alert
- [ ] Complete online payment and verify webhook updates database
- [ ] Check admin dashboard shows full payment status

## Important Notes

1. **Payment Amount**: The remaining balance is automatically calculated as:

   ```
   Remaining Balance = Total Amount - Downpayment Amount
   ```

2. **Payment Verification**: Online payments for remaining balance require admin verification (similar to downpayment)

3. **Resort Payment**: Users can always choose to pay at resort. This doesn't require any online transaction.

4. **Webhook**: Make sure PayMongo webhook is configured to receive `link.payment.paid` events

5. **Security**: The payment type is validated server-side to ensure:
   - Downpayment must be verified before allowing full payment
   - User owns the reservation
   - Payment hasn't already been made

## Support

If you encounter any issues:

1. Check browser console for JavaScript errors
2. Check PHP error logs for backend errors
3. Verify database migration was successful
4. Ensure PayMongo credentials are configured correctly in `config/paymongo.php`
