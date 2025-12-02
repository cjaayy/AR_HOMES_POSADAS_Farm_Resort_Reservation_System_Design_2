/**
 * AR Homes Booking Flow - Frontend Integration
 * Handles date availability, payment uploads, rebooking, and cancellation
 */

// ===========================
// MY BOOKINGS MANAGEMENT
// ===========================

/**
 * Load user's reservations
 */
async function loadMyBookings() {
  const loadingState = document.getElementById("bookingsLoadingState");
  const emptyState = document.getElementById("bookingsEmptyState");
  const bookingsGrid = document.getElementById("myBookingsGrid");

  // Show loading
  if (loadingState) loadingState.style.display = "block";
  if (emptyState) emptyState.style.display = "none";
  if (bookingsGrid) {
    bookingsGrid.style.display = "none";
    bookingsGrid.innerHTML = "";
  }

  try {
    const response = await fetch("user/get_my_reservations.php", {
      method: "GET",
      credentials: "same-origin",
    });

    const data = await response.json();
    if (loadingState) loadingState.style.display = "none";

    if (data.success && data.reservations && data.reservations.length > 0) {
      // Display bookings
      if (bookingsGrid) {
        bookingsGrid.style.display = "grid";
        renderBookings(data.reservations);
      }
    } else {
      // Show empty state
      if (emptyState) emptyState.style.display = "block";
    }
  } catch (error) {
    if (loadingState) loadingState.style.display = "none";
    if (emptyState) {
      emptyState.style.display = "block";
      emptyState.innerHTML = `
        <i class="fas fa-exclamation-triangle" style="font-size: 64px; color: #dc3545;"></i>
        <h3 style="margin-top: 20px; color: #721c24;">Error Loading Bookings</h3>
        <p style="color: #721c24;">${error.message}</p>
        <button class="btn-primary" onclick="loadMyBookings()" style="margin-top: 20px;">
          <i class="fas fa-sync"></i> Try Again
        </button>
      `;
    }
  }
}

/**
 * Render bookings grid
 */
function renderBookings(bookings) {
  const grid = document.getElementById("myBookingsGrid");
  if (!grid) return;

  grid.innerHTML = bookings
    .map((booking) => {
      const statusClass = getStatusClass(booking.status);
      const statusLabel = booking.status_label || booking.status;

      return `
        <div class="booking-card" data-status="${booking.status}">
          ${
            booking.status === "confirmed" &&
            booking.downpayment_verified == 1 &&
            booking.full_payment_verified == 1
              ? `
          <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 10px 15px; display: flex; align-items: center; gap: 10px; border-radius: 12px 12px 0 0; font-weight: 600;">
            <i class="fas fa-check-double" style="font-size: 1.2em;"></i>
            <span>Fully Paid & Verified ${
              booking.payment_method
                ? "via " + formatPaymentMethod(booking.payment_method)
                : ""
            }</span>
          </div>
          `
              : booking.status === "confirmed" &&
                booking.downpayment_verified == 1
              ? `
          <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 10px 15px; display: flex; align-items: center; gap: 10px; border-radius: 12px 12px 0 0; font-weight: 600;">
            <i class="fas fa-check-circle" style="font-size: 1.2em;"></i>
            <span>Downpayment Paid & Verified ${
              booking.payment_method
                ? "via " + formatPaymentMethod(booking.payment_method)
                : ""
            }</span>
          </div>
          `
              : booking.downpayment_paid == 1 &&
                booking.status === "pending_confirmation"
              ? `
          <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 10px 15px; display: flex; align-items: center; gap: 10px; border-radius: 12px 12px 0 0; font-weight: 600; margin-bottom: 15px;">
            <i class="fas fa-clock" style="font-size: 1.2em;"></i>
            <span>Payment Received ${
              booking.payment_method
                ? "via " + formatPaymentMethod(booking.payment_method)
                : ""
            } - Awaiting Admin Approval</span>
          </div>
          `
              : ""
          }
          <div class="booking-card-header" style="${
            booking.downpayment_paid == 1 || booking.downpayment_verified == 1
              ? "margin-top: 0;"
              : ""
          }">
            <span class="status-badge ${statusClass}">${statusLabel}</span>
            <span class="booking-id">#${booking.reservation_id}</span>
          </div>
          
          <div class="booking-card-body">
            <h3>${booking.package_name || "Package"}</h3>
            <div class="booking-type-badge ${booking.booking_type}">
              <i class="fas ${getBookingTypeIcon(booking.booking_type)}"></i>
              ${booking.booking_type.toUpperCase()}
            </div>
            
            <div class="booking-details-grid">
              <div class="detail-item">
                <i class="fas fa-calendar"></i>
                <span>${formatDate(booking.check_in_date)}</span>
              </div>
              <div class="detail-item">
                <i class="fas fa-clock"></i>
                <span>${booking.check_in_time}</span>
              </div>
              <div class="detail-item">
                <i class="fas fa-user"></i>
                <span>${booking.guest_name}</span>
              </div>
              <div class="detail-item">
                <i class="fas fa-calendar-day"></i>
                <span>${
                  booking.booking_type === "daytime"
                    ? booking.number_of_days
                    : booking.number_of_nights
                } ${
        booking.booking_type === "daytime" ? "Day(s)" : "Night(s)"
      }</span>
              </div>
              <div class="detail-item">
                <i class="fas fa-tag"></i>
                <span>₱${parseFloat(
                  booking.total_amount
                ).toLocaleString()}</span>
              </div>
              <div class="detail-item" style="grid-column: 1/-1; font-size: 0.85em; color: #64748b;">
                <i class="fas fa-info-circle"></i>
                <span>₱${parseFloat(booking.base_price).toLocaleString()} × ${
        booking.booking_type === "daytime"
          ? booking.number_of_days
          : booking.number_of_nights
      } ${
        booking.booking_type === "daytime" ? "day(s)" : "night(s)"
      } = ₱${parseFloat(booking.total_amount).toLocaleString()}</span>
              </div>
            </div>

            ${renderPaymentStatus(booking)}
            ${renderBookingActions(booking)}
          </div>
        </div>
      `;
    })
    .join("");

  // Attach event listeners to payment buttons after rendering
  setTimeout(() => {
    // Downpayment buttons
    const paymentButtons = grid.querySelectorAll(".payment-button");
    console.log("Found downpayment payment buttons:", paymentButtons.length);
    paymentButtons.forEach((btn) => {
      const reservationId = btn.getAttribute("data-reservation-id");
      console.log(
        "Attaching listener to button for reservation:",
        reservationId
      );
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        console.log("Downpayment payment button clicked!", reservationId);
        payWithPayMongo(reservationId, this);
      });
    });

    // Full payment buttons
    const fullPaymentButtons = grid.querySelectorAll(".payment-button-full");
    console.log("Found full payment buttons:", fullPaymentButtons.length);
    fullPaymentButtons.forEach((btn) => {
      const reservationId = btn.getAttribute("data-reservation-id");
      const amount = btn.getAttribute("data-amount");
      console.log(
        "Attaching listener to full payment button for reservation:",
        reservationId,
        "Amount:",
        amount
      );
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        console.log("Full payment button clicked!", reservationId);
        payFullBalanceWithPayMongo(reservationId, amount, this);
      });
    });
  }, 100);
}

/**
 * Render payment status indicators
 */
function renderPaymentStatus(booking) {
  // Don't show payment status for cancelled bookings
  if (booking.status === "cancelled") {
    return "";
  }

  let html = '<div class="payment-status-section">';

  // Downpayment status
  const downStatus = booking.downpayment_status || "Not Paid";
  const downClass =
    downStatus === "Verified"
      ? "verified"
      : downStatus === "Pending Verification"
      ? "pending"
      : "unpaid";

  // Show amount and payment method for paid downpayments
  const downpaymentAmount = parseFloat(
    booking.downpayment_amount || 0
  ).toLocaleString();
  const paymentMethod = booking.payment_method
    ? ` via ${formatPaymentMethod(booking.payment_method)}`
    : "";
  const paidAt = booking.downpayment_paid_at
    ? ` on ${new Date(booking.downpayment_paid_at).toLocaleDateString()}`
    : "";

  html += `
    <div class="payment-status-item ${downClass}">
      <i class="fas ${
        downClass === "verified"
          ? "fa-check-circle"
          : downClass === "pending"
          ? "fa-clock"
          : "fa-times-circle"
      }"></i>
      <div>
        <strong>Downpayment - ₱${downpaymentAmount}</strong>
        <span>${downStatus}${
    downClass === "verified" || downClass === "pending"
      ? paymentMethod + paidAt
      : ""
  }</span>
      </div>
    </div>
  `;

  // Full payment status
  if (booking.downpayment_verified == 1) {
    const fullStatus = booking.full_payment_status || "Not Paid";
    const fullClass =
      fullStatus === "Verified"
        ? "verified"
        : fullStatus === "Pending Verification"
        ? "pending"
        : "unpaid";

    const remainingBalance = booking.total_amount - booking.downpayment_amount;
    const remainingBalanceFormatted =
      parseFloat(remainingBalance).toLocaleString();

    const fullPaidAt = booking.full_payment_paid_at
      ? ` on ${new Date(booking.full_payment_paid_at).toLocaleDateString()}`
      : "";

    html += `
      <div class="payment-status-item ${fullClass}">
        <i class="fas ${
          fullClass === "verified"
            ? "fa-check-circle"
            : fullClass === "pending"
            ? "fa-clock"
            : "fa-times-circle"
        }"></i>
        <div>
          <strong>Remaining Balance - ₱${remainingBalanceFormatted}</strong>
          <span>${fullStatus}${
      fullClass === "verified" || fullClass === "pending" ? fullPaidAt : ""
    }</span>
        </div>
      </div>
    `;
  }

  html += "</div>";
  return html;
}

/**
 * Render booking action buttons
 */
function renderBookingActions(booking) {
  console.log("Rendering actions for booking:", booking.reservation_id);
  console.log("can_upload_downpayment:", booking.can_upload_downpayment);
  console.log("payment_method:", booking.payment_method);

  let html = '<div class="booking-actions">';

  // Show cancellation notice for cancelled bookings
  if (booking.status === "cancelled" && booking.downpayment_paid == 1) {
    html += `
      <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; border-radius: 8px; margin: 10px 0;">
        <div style="display: flex; align-items: start; gap: 10px;">
          <i class="fas fa-info-circle" style="color: #d97706; font-size: 1.2em; margin-top: 2px;"></i>
          <div>
            <strong style="color: #92400e; display: block; margin-bottom: 4px;">Cancelled Reservation</strong>
            <p style="margin: 0; color: #78350f; font-size: 0.9em;">Downpayment of ₱${parseFloat(
              booking.downpayment_amount
            ).toLocaleString()} is non-refundable as per booking policy.</p>
          </div>
        </div>
      </div>
    `;
    html += "</div>";
    return html;
  }

  // Upload downpayment
  if (booking.can_upload_downpayment) {
    // Check payment method - show online payment or upload button
    const onlinePaymentMethods = [
      "gcash",
      "paymaya",
      "grab_pay",
      "card",
      "dob_bpi",
      "dob_ubp",
      "atome",
      "otc",
    ];

    // If payment_method is set and it's an online method, show PayMongo button
    // If payment_method is null/empty, also show PayMongo button as default
    if (
      !booking.payment_method ||
      booking.payment_method === "" ||
      onlinePaymentMethods.includes(booking.payment_method)
    ) {
      console.log(
        "Rendering PayMongo button for reservation:",
        booking.reservation_id
      );
      // Show "Pay Now" button for online payment methods
      html += `
        <button 
          class="btn-primary payment-button" 
          type="button" 
          data-reservation-id="${booking.reservation_id}"
          style="pointer-events: auto !important; cursor: pointer !important; z-index: 10; position: relative;"
        >
          <i class="fas fa-credit-card"></i> Pay Now via PayMongo
        </button>
      `;
    } else {
      // Show upload button for bank transfer or other methods
      html += `
        <button class="btn-primary" type="button" onclick="openPaymentUploadModal(${booking.reservation_id}, 'downpayment', ${booking.downpayment_amount})">
          <i class="fas fa-upload"></i> Upload Downpayment
        </button>
      `;
    }
  }

  // Full payment options - pay online or at resort
  if (booking.can_upload_full_payment) {
    const remainingBalance = booking.total_amount - booking.downpayment_amount;
    html += `
      <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 15px; border-radius: 10px; border-left: 4px solid #0284c7; margin: 10px 0;">
        <div style="display: flex; align-items: start; gap: 12px;">
          <i class="fas fa-wallet" style="color: #0284c7; font-size: 1.3em; margin-top: 2px;"></i>
          <div style="flex: 1;">
            <strong style="color: #0c4a6e; display: block; margin-bottom: 8px; font-size: 1.05em;">Remaining Balance: ₱${remainingBalance.toLocaleString()}</strong>
            <p style="margin: 0 0 12px 0; color: #075985; font-size: 0.9em;">Choose your payment option:</p>
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <button 
                class="btn-primary payment-button-full" 
                type="button" 
                data-reservation-id="${booking.reservation_id}"
                data-amount="${remainingBalance}"
                style="padding: 10px 16px; font-size: 0.95em; width: 100%;">
                <i class="fas fa-credit-card"></i> Pay Remaining Balance Online
              </button>
              <button 
                class="btn-secondary" 
                type="button" 
                onclick="showPayAtResortInfo()"
                style="padding: 10px 16px; font-size: 0.95em; width: 100%; background: #64748b; border-color: #64748b;">
                <i class="fas fa-building"></i> Pay at Resort
              </button>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  // Request rebooking
  if (booking.can_rebook) {
    html += `
      <button class="btn-secondary" onclick="openRebookingModal(${booking.reservation_id}, '${booking.check_in_date}')">
        <i class="fas fa-calendar-alt"></i> Request Rebooking
      </button>
    `;
  }

  // Cancel reservation - only allowed before admin/staff confirmation
  if (
    booking.can_cancel === true ||
    booking.can_cancel === 1 ||
    booking.can_cancel === "1"
  ) {
    html += `
      <button class="btn-danger" onclick="openCancelModal('${booking.reservation_id}')">
        <i class="fas fa-times"></i> Cancel Booking
      </button>
    `;
  } else if (booking.status === "confirmed") {
    // Show disabled button when confirmed by admin
    html += `
      <button class="btn-danger" disabled style="opacity: 0.5; cursor: not-allowed;" title="Cannot cancel - booking has been confirmed by admin/staff">
        <i class="fas fa-times"></i> Cancel Booking
      </button>
    `;
  }

  // Days until check-in indicator
  if (!booking.is_past_checkin && booking.status === "confirmed") {
    html += `
      <div class="checkin-countdown">
        <i class="fas fa-calendar-day"></i>
        <span>${booking.days_until_checkin} days until check-in</span>
      </div>
    `;
  }

  html += "</div>";
  return html;
}

// ===========================
// PAYMENT UPLOAD
// ===========================

/**
 * Open payment upload modal
 */
function openPaymentUploadModal(reservationId, paymentType, amount) {
  document.getElementById("paymentReservationId").value = reservationId;
  document.getElementById("paymentType").value = paymentType;

  const label =
    paymentType === "downpayment"
      ? `Downpayment - ₱${parseFloat(amount).toLocaleString()}`
      : `Full Payment - ₱${parseFloat(amount).toLocaleString()}`;

  document.getElementById("paymentForLabel").textContent = label;
  document.getElementById("paymentUploadModal").style.display = "flex";
}

/**
 * Close payment upload modal
 */
function closePaymentUploadModal() {
  document.getElementById("paymentUploadModal").style.display = "none";
  document.getElementById("paymentUploadForm").reset();
}

/**
 * Handle payment upload form submission
 */
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("paymentUploadForm");
  if (form) {
    form.addEventListener("submit", async function (e) {
      e.preventDefault();

      const submitBtn = form.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> Uploading...';

      const formData = new FormData(form);

      try {
        const response = await fetch("user/upload_payment_proof.php", {
          method: "POST",
          credentials: "same-origin",
          body: formData,
        });

        const data = await response.json();
        if (data.success) {
          alert("✅ " + data.message);
          closePaymentUploadModal();
          loadMyBookings(); // Reload bookings
        } else {
          alert("❌ " + data.message);
        }
      } catch (error) {
        alert("Error uploading payment proof: " + error.message);
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      }
    });
  }
});

// ===========================
// REBOOKING REQUEST
// ===========================

/**
 * Open rebooking modal
 */
function openRebookingModal(reservationId, currentCheckInDate) {
  document.getElementById("rebookReservationId").value = reservationId;
  document.getElementById("currentCheckInDate").textContent =
    formatDate(currentCheckInDate);

  // Set minimum date to today
  const today = new Date().toISOString().split("T")[0];
  document.getElementById("newCheckInDate").setAttribute("min", today);

  document.getElementById("rebookingModal").style.display = "flex";
}

/**
 * Close rebooking modal
 */
function closeRebookingModal() {
  document.getElementById("rebookingModal").style.display = "none";
  document.getElementById("rebookingForm").reset();
}

/**
 * Handle rebooking form submission
 */
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("rebookingForm");
  if (form) {
    form.addEventListener("submit", async function (e) {
      e.preventDefault();

      const submitBtn = form.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> Submitting...';

      const formData = {
        reservation_id: document.getElementById("rebookReservationId").value,
        new_date: document.getElementById("newCheckInDate").value,
        reason: document.getElementById("rebookReason").value,
      };

      try {
        const response = await fetch("user/request_rebooking.php", {
          method: "POST",
          credentials: "same-origin",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(formData),
        });

        const data = await response.json();
        if (data.success) {
          alert(
            "✅ " +
              data.message +
              "\n\nOriginal: " +
              data.original_date +
              "\nNew Date: " +
              data.new_date
          );
          closeRebookingModal();
          loadMyBookings(); // Reload bookings
        } else {
          alert("❌ " + data.message);
        }
      } catch (error) {
        alert("Error submitting rebooking request: " + error.message);
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      }
    });
  }
});

// ===========================
// CANCELLATION
// ===========================

/**
 * Open cancel modal
 */
function openCancelModal(reservationId) {
  try {
    const reservationIdInput = document.getElementById("cancelReservationId");
    const cancelModal = document.getElementById("cancelModal");

    if (!reservationIdInput) {
      console.error("cancelReservationId input not found");
      alert("Error: Cancel form not found. Please refresh the page.");
      return;
    }

    if (!cancelModal) {
      console.error("cancelModal not found");
      alert("Error: Cancel modal not found. Please refresh the page.");
      return;
    }

    reservationIdInput.value = reservationId;

    // Show cancellation policy warning
    const refundInfo = document.getElementById("cancelRefundInfo");
    if (refundInfo) {
      refundInfo.innerHTML = `
        <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; border-radius: 6px; margin: 15px 0;">
          <strong style="color: #92400e; display: block; margin-bottom: 5px;">
            <i class="fas fa-exclamation-triangle"></i> Cancellation Policy
          </strong>
          <p style="margin: 0; color: #78350f; font-size: 0.9em;">
            • Downpayment is <strong>non-refundable</strong> as per booking policy<br>
            • Once confirmed by admin, cancellation is <strong>no longer allowed</strong>
          </p>
        </div>
      `;
    }

    cancelModal.style.display = "flex";
  } catch (error) {
    console.error("Error opening cancel modal:", error);
    alert(
      "Error opening cancel form: " +
        error.message +
        ". Please refresh the page."
    );
  }
}

/**
 * Close cancel modal
 */
function closeCancelModal() {
  document.getElementById("cancelModal").style.display = "none";
  document.getElementById("cancelForm").reset();
}

/**
 * Handle cancellation form submission
 */
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("cancelForm");
  if (form) {
    form.addEventListener("submit", async function (e) {
      e.preventDefault();

      const reservationIdInput = document.getElementById("cancelReservationId");
      const reasonInput = document.getElementById("cancelReason");

      if (!reservationIdInput || !reasonInput) {
        alert("Error: Form elements not found. Please refresh the page.");
        return;
      }

      // Double confirmation
      if (
        !confirm(
          "⚠️ FINAL CONFIRMATION\n\nAre you absolutely sure you want to cancel?\nYour downpayment will NOT be refunded."
        )
      ) {
        return;
      }

      const submitBtn = form.querySelector('button[type="submit"]');
      if (!submitBtn) {
        alert("Error: Submit button not found. Please refresh the page.");
        return;
      }

      const originalText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> Cancelling...';

      const formData = {
        reservation_id: reservationIdInput.value,
        reason: reasonInput.value,
      };

      try {
        const response = await fetch("user/cancel_reservation.php", {
          method: "POST",
          credentials: "same-origin",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(formData),
        });

        const data = await response.json();
        if (data.success) {
          alert("✅ " + data.message);
          closeCancelModal();
          loadMyBookings(); // Reload bookings
        } else {
          alert("❌ " + data.message);
        }
      } catch (error) {
        console.error("Cancellation error:", error);
        alert("Error cancelling reservation: " + error.message);
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
      }
    });
  }
});

// ===========================
// DATE AVAILABILITY CHECKER
// ===========================

/**
 * Check date availability before booking
 */
async function checkDateAvailability(checkInDate, bookingType) {
  try {
    const response = await fetch("user/check_availability.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        check_in_date: checkInDate,
        booking_type: bookingType,
      }),
    });

    const data = await response.json();
    if (data.locked) {
      return {
        available: false,
        message: `❌ This date is already booked by ${data.booked_by}`,
        locked: true,
      };
    }

    if (data.pending_reservations > 0) {
      return {
        available: true,
        message: `⚠️ Warning: ${data.pending_reservations} other user(s) are also trying to book this date. First to pay, first to reserve!`,
        warning: true,
      };
    }

    return {
      available: true,
      message: "✅ This date is available!",
      locked: false,
    };
  } catch (error) {
    return {
      available: false,
      message: "Error checking availability: " + error.message,
      error: true,
    };
  }
}

/**
 * Attach availability checker to booking form
 */
document.addEventListener("DOMContentLoaded", function () {
  const checkInInput = document.getElementById("checkInDate");
  const bookingTypeInputs = document.querySelectorAll(
    'input[name="bookingType"]'
  );

  if (checkInInput && bookingTypeInputs.length > 0) {
    // Check on date change
    checkInInput.addEventListener("change", async function () {
      const selectedDate = this.value;
      const selectedType = document.querySelector(
        'input[name="bookingType"]:checked'
      );

      if (selectedDate && selectedType) {
        const result = await checkDateAvailability(
          selectedDate,
          selectedType.value
        );
        displayAvailabilityMessage(result);
      }
    });

    // Check on booking type change
    bookingTypeInputs.forEach((input) => {
      input.addEventListener("change", async function () {
        const selectedDate = checkInInput.value;
        if (selectedDate) {
          const result = await checkDateAvailability(selectedDate, this.value);
          displayAvailabilityMessage(result);
        }
      });
    });
  }
});

/**
 * Display availability message
 */
function displayAvailabilityMessage(result) {
  // Remove existing message
  const existing = document.getElementById("availabilityMessage");
  if (existing) existing.remove();

  // Create new message
  const messageDiv = document.createElement("div");
  messageDiv.id = "availabilityMessage";
  messageDiv.style.cssText = `
    padding: 15px;
    border-radius: 8px;
    margin-top: 15px;
    font-weight: 600;
    border-left: 4px solid;
  `;

  if (result.locked) {
    messageDiv.style.background = "#fee";
    messageDiv.style.borderColor = "#dc3545";
    messageDiv.style.color = "#721c24";
    messageDiv.innerHTML = `<i class="fas fa-times-circle"></i> ${result.message}`;
  } else if (result.warning) {
    messageDiv.style.background = "#fff3cd";
    messageDiv.style.borderColor = "#ffc107";
    messageDiv.style.color = "#856404";
    messageDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${result.message}`;
  } else if (result.available) {
    messageDiv.style.background = "#d4edda";
    messageDiv.style.borderColor = "#28a745";
    messageDiv.style.color = "#155724";
    messageDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${result.message}`;
  } else {
    messageDiv.style.background = "#fee";
    messageDiv.style.borderColor = "#dc3545";
    messageDiv.style.color = "#721c24";
    messageDiv.innerHTML = `<i class="fas fa-times-circle"></i> ${result.message}`;
  }

  // Insert after check-in date input
  const checkInInput = document.getElementById("checkInDate");
  if (checkInInput && checkInInput.parentElement) {
    checkInInput.parentElement.insertAdjacentElement("afterend", messageDiv);
  }
}

// ===========================
// UTILITY FUNCTIONS
// ===========================

/**
 * Get status badge class
 */
function getStatusClass(status) {
  const statusMap = {
    pending_payment: "pending",
    pending_confirmation: "warning",
    confirmed: "confirmed",
    checked_in: "success",
    checked_out: "success",
    completed: "completed",
    cancelled: "cancelled",
    no_show: "cancelled",
    forfeited: "cancelled",
    rebooked: "info",
  };
  return statusMap[status] || "default";
}

/**
 * Get booking type icon
 */
function getBookingTypeIcon(bookingType) {
  const iconMap = {
    daytime: "fa-sun",
    nighttime: "fa-moon",
    "22hours": "fa-clock",
  };
  return iconMap[bookingType] || "fa-calendar";
}

/**
 * Format payment method name for display
 */
function formatPaymentMethod(method) {
  if (!method) return "";

  const methodMap = {
    card: "Credit/Debit Card",
    gcash: "GCash",
    grab_pay: "GrabPay",
    otc: "OTC/Coins.ph",
    paymaya: "Maya",
    dob_bpi: "BPI Online",
    dob_ubp: "UnionBank Online",
    atome: "Atome",
    bank_transfer: "Bank Transfer",
  };

  return methodMap[method.toLowerCase()] || method.toUpperCase();
}

/**
 * Format date for display
 */
function formatDate(dateString) {
  const date = new Date(dateString);
  const options = { year: "numeric", month: "short", day: "numeric" };
  return date.toLocaleDateString("en-US", options);
}

// ===========================
// GCASH PAYMENT VIA PAYMONGO
// ===========================

/**
 * Pay with PayMongo (supports GCash, Maya, Card, etc.)
 */
async function payWithPayMongo(reservationId, btnElement) {
  console.log("payWithPayMongo called with:", reservationId, btnElement);

  if (!confirm("You will be redirected to the payment page. Continue?")) {
    return;
  }

  // Determine button element
  let btn = btnElement;
  if (!btn && typeof event !== "undefined" && event && event.target) {
    btn = event.target;
  }

  let originalText = "";
  if (btn) {
    originalText = btn.innerHTML;
  }

  try {
    // Show loading state
    if (btn) {
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
      btn.disabled = true;
    }

    console.log("Creating payment intent for reservation:", reservationId);

    // Create payment intent
    const response = await fetch("user/create_payment_intent.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        reservation_id: reservationId,
      }),
    });

    if (!response.ok) {
      const result = await response.json();
      console.error("Payment API error response:", result);
      throw new Error(result.message || `HTTP error ${response.status}`);
    }

    const result = await response.json();
    console.log("Payment intent result:", result);

    if (result.success && result.checkout_url) {
      // Redirect to PayMongo checkout page
      window.location.href = result.checkout_url;
    } else {
      throw new Error(result.message || "Failed to create payment");
    }
  } catch (error) {
    console.error("Payment error:", error);

    // Show user-friendly error message
    let errorMsg = error.message;
    if (errorMsg.includes("Failed to fetch")) {
      errorMsg =
        "Unable to connect to payment server. Please check your internet connection and try again.";
    }

    alert("Payment Error: " + errorMsg);

    // Restore button
    if (btn && originalText) {
      btn.innerHTML = originalText;
      btn.disabled = false;
    }
  }
}

// Keep the old function name for backward compatibility
async function payWithGCash(reservationId) {
  return payWithPayMongo(reservationId);
}

/**
 * Pay full remaining balance with PayMongo
 */
async function payFullBalanceWithPayMongo(reservationId, amount, btnElement) {
  console.log(
    "payFullBalanceWithPayMongo called with:",
    reservationId,
    amount,
    btnElement
  );

  if (
    !confirm(
      `You will be redirected to pay the remaining balance of ₱${parseFloat(
        amount
      ).toLocaleString()}. Continue?`
    )
  ) {
    return;
  }

  // Determine button element
  let btn = btnElement;
  if (!btn && typeof event !== "undefined" && event && event.target) {
    btn = event.target;
  }

  let originalText = "";
  if (btn) {
    originalText = btn.innerHTML;
  }

  try {
    // Show loading state
    if (btn) {
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
      btn.disabled = true;
    }

    console.log("Creating payment intent for full balance:", reservationId);

    // Create payment intent for full balance
    const response = await fetch("user/create_payment_intent.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        reservation_id: reservationId,
        payment_type: "full_payment",
      }),
    });

    if (!response.ok) {
      const result = await response.json();
      console.error("Payment API error response:", result);
      throw new Error(result.message || `HTTP error ${response.status}`);
    }

    const result = await response.json();
    console.log("Payment intent result:", result);

    if (result.success && result.checkout_url) {
      // Redirect to PayMongo checkout page
      window.location.href = result.checkout_url;
    } else {
      throw new Error(result.message || "Failed to create payment");
    }
  } catch (error) {
    console.error("Payment error:", error);

    // Show user-friendly error message
    let errorMsg = error.message;
    if (errorMsg.includes("Failed to fetch")) {
      errorMsg =
        "Unable to connect to payment server. Please check your internet connection and try again.";
    }

    alert("Payment Error: " + errorMsg);

    // Restore button
    if (btn && originalText) {
      btn.innerHTML = originalText;
      btn.disabled = false;
    }
  }
}

/**
 * Show information about paying at resort
 */
function showPayAtResortInfo() {
  alert(
    "💰 Pay at Resort\n\n" +
      "You can pay the remaining balance at the resort upon check-in or during your stay.\n\n" +
      "Payment methods accepted at resort:\n" +
      "• Cash\n" +
      "• GCash\n" +
      "• Bank Transfer\n" +
      "• Credit/Debit Card\n\n" +
      "Please ensure to settle your balance before check-out."
  );
}

// ===========================
// AUTO-LOAD BOOKINGS
// ===========================

// Load bookings when "My Bookings" section is opened
document.addEventListener("DOMContentLoaded", function () {
  const myBookingsBtn = document.querySelector('[data-section="my-bookings"]');
  if (myBookingsBtn) {
    myBookingsBtn.addEventListener("click", function () {
      loadMyBookings();
    });
  }
});
