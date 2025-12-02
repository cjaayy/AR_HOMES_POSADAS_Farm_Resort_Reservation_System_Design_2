# PayMongo Payment Tracking Implementation

## Overview

This document describes the implementation of PayMongo payment tracking to ensure successful payments appear correctly in the "My Bookings" section with the downpayment marked as paid.

## Changes Made

### 1. Webhook Handler (`user/paymongo_webhook.php`)

#### Added `link.payment.paid` Event Handler

- PayMongo Payment Links trigger the `link.payment.paid` event when successfully paid
- Added new function `handleLinkPaymentPaid()` to process this event
- Updates reservation with:
  - `downpayment_paid = 1` (marks downpayment as paid)
  - `downpayment_reference` = payment ID
  - `downpayment_paid_at` = current timestamp
  - `downpayment_verified = 1` (auto-verifies PayMongo payments)
  - `downpayment_verified_at` = current timestamp
  - `payment_method = 'gcash'`
  - `status = 'confirmed'`

#### Updated `handleSourceChargeable()` Function

- Now properly sets downpayment fields when source becomes chargeable
- Marks downpayment as paid and verified

#### Updated `handlePaymentPaid()` Function

- Enhanced to set all downpayment tracking fields
- Ensures consistency across all payment event types

### 2. Payment Success Page (`payment_success.php`)

#### Enhanced Payment Link Status Check

- Added support for checking PayMongo Links API (`/links/{id}`)
- Checks for completed payments in the link's payment array
- Updates all downpayment tracking fields when payment is confirmed
- Includes fallback to Sources API for backward compatibility

#### Database Updates on Success

When a payment is confirmed, the following fields are updated:

- `status = 'confirmed'`
- `paymongo_payment_id` = payment ID
- `payment_method = 'gcash'`
- `downpayment_paid = 1`
- `downpayment_reference` = payment ID
- `downpayment_paid_at` = NOW()
- `downpayment_verified = 1`
- `downpayment_verified_at` = NOW()

### 3. Payment Intent Creation (`user/create_payment_intent.php`)

#### Added Success URL Parameter

- Constructs success redirect URL with reservation ID
- Ensures payment_success.php receives the correct reservation_id
- Format: `http://localhost/payment_success.php?reservation_id={id}`

## Payment Flow

### Step 1: User Creates Reservation

1. User selects GCash as payment method
2. Reservation is created with `status = 'pending_payment'`
3. System creates PayMongo Payment Link

### Step 2: Payment Link Creation

1. `create_payment_intent.php` is called
2. PayMongo Link is created with:
   - Correct downpayment amount
   - Reservation details in description
   - Success redirect URL
3. Link ID is stored in `paymongo_source_id` field
4. User is redirected to PayMongo checkout page

### Step 3: User Pays via GCash

1. User completes payment on PayMongo checkout page
2. PayMongo processes the GCash payment

### Step 4: Webhook Notification (Primary Method)

1. PayMongo sends `link.payment.paid` webhook event
2. `handleLinkPaymentPaid()` function processes the event
3. Reservation is updated with payment details
4. Downpayment is marked as PAID and VERIFIED

### Step 5: Success Page Redirect (Backup Method)

1. User is redirected to `payment_success.php?reservation_id={id}`
2. Page checks payment link status via PayMongo API
3. If payment is found, updates reservation (if not already updated by webhook)
4. Displays confirmation to user

### Step 6: My Bookings Display

1. User navigates to My Bookings section
2. `get_my_reservations.php` retrieves reservations
3. For each reservation, payment status is determined:
   - If `downpayment_verified == 1`: Shows "Verified" ✅
   - If `downpayment_paid == 1`: Shows "Pending Verification" ⏳
   - Otherwise: Shows "Not Paid" ❌
4. Reservation card displays:
   - Status badge: "Confirmed" (green)
   - Payment status: "Confirmed" with green check icon
   - Hides "Upload Payment" button
   - Shows "Pay Balance" button if full payment not yet made

## Database Fields Used

### PayMongo Tracking Fields

- `paymongo_source_id` - Stores Payment Link ID
- `paymongo_payment_id` - Stores successful Payment ID
- `paymongo_payment_type` - Stores payment method (e.g., 'gcash')

### Downpayment Tracking Fields

- `downpayment_paid` - Boolean: 1 = paid, 0 = not paid
- `downpayment_reference` - Payment reference/transaction ID
- `downpayment_paid_at` - Timestamp when payment was made
- `downpayment_verified` - Boolean: 1 = verified by admin/system, 0 = pending
- `downpayment_verified_at` - Timestamp when payment was verified

### Status Fields

- `status` - Overall reservation status (pending_payment → confirmed)
- `payment_method` - Payment method used (gcash, bank_transfer, etc.)

## Testing the Implementation

### Test Scenario 1: Successful PayMongo Payment

1. Create a new reservation with GCash payment method
2. Click "Pay with GCash" button in My Bookings
3. Complete payment on PayMongo page (use test card/account)
4. Verify webhook is received (check server logs)
5. Return to My Bookings
6. Confirm reservation shows:
   - Status: "Confirmed"
   - Payment Status: "Confirmed" with green check
   - Downpayment marked as paid

### Test Scenario 2: Webhook Handling

1. Monitor `error_log()` output during payment
2. Verify entries like:
   - "PayMongo Webhook Received: ..."
   - "Payment link paid - Reservation confirmed: {id}"
3. Check database directly:
   ```sql
   SELECT reservation_id, status, downpayment_paid, downpayment_verified,
          downpayment_paid_at, payment_method
   FROM reservations
   WHERE reservation_id = {your_test_id};
   ```

### Test Scenario 3: Payment Success Page

1. After payment, note the URL in browser
2. Should be: `payment_success.php?reservation_id={id}`
3. Page should display:
   - Green success icon
   - "Payment Successful!" heading
   - Reservation details
   - "Status: Confirmed"

## Webhook Configuration

### Required Webhook Events

Configure these webhook events in PayMongo Dashboard:

- `link.payment.paid` - Primary event for payment links ⭐
- `payment.paid` - General payment success event
- `source.chargeable` - For source-based payments
- `payment.failed` - For failed payment handling

### Webhook URL

Set your webhook endpoint to:

```
https://yourdomain.com/user/paymongo_webhook.php
```

For local testing with ngrok:

```
https://your-ngrok-url.ngrok.io/user/paymongo_webhook.php
```

## Troubleshooting

### Issue: Payment successful but not showing in My Bookings

**Check 1: Webhook Received?**

- Check server error logs for "PayMongo Webhook Received"
- If not received, verify webhook URL in PayMongo Dashboard
- Ensure webhook endpoint is publicly accessible

**Check 2: Database Updated?**

- Query reservation directly in database
- Check if `downpayment_paid = 1` and `status = 'confirmed'`
- If not updated, check for PHP errors in logs

**Check 3: Frontend Displaying Correctly?**

- Check browser console for JavaScript errors
- Verify `get_my_reservations.php` returns correct data
- Check if status badge is rendering properly

### Issue: Webhook returns 401 Unauthorized

**Solution:**

- Webhook signature verification may be failing
- Check `PAYMONGO_WEBHOOK_SECRET` in `config/paymongo.php`
- Temporarily comment out signature verification for testing:
  ```php
  // if (PAYMONGO_WEBHOOK_SECRET && $signature) {
  //     // signature verification code
  // }
  ```

### Issue: Payment link not redirecting to success page

**Solution:**

- PayMongo Links may not support automatic redirects
- Users should manually return to the website
- Webhook handles the update automatically
- Success page is mainly for user confirmation

## Security Considerations

1. **Webhook Signature Verification**: Always verify webhook signatures in production
2. **HTTPS Required**: PayMongo webhooks require HTTPS endpoints
3. **Idempotency**: Webhook handlers check if reservation is already processed
4. **SQL Injection Prevention**: All queries use prepared statements with parameter binding
5. **Session Validation**: Payment endpoints verify user session before processing

## Future Enhancements

1. **Email Notifications**: Send confirmation email when payment is verified
2. **SMS Notifications**: Alert user via SMS when payment is confirmed
3. **Payment Receipt**: Generate PDF receipt for successful payments
4. **Refund Handling**: Add support for refund webhooks
5. **Multiple Payment Methods**: Support credit cards, bank transfers, etc.

## Support

For issues or questions:

- Check PayMongo API documentation: https://developers.paymongo.com/docs
- Review webhook event logs in PayMongo Dashboard
- Check PHP error logs for detailed error messages
- Test webhooks using PayMongo's webhook testing tool
