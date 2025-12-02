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
          <div class="booking-card-header">
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
                <i class="fas fa-tag"></i>
                <span>₱${parseFloat(
                  booking.total_price
                ).toLocaleString()}</span>
              </div>
            </div>

            ${renderPaymentStatus(booking)}
            ${renderBookingActions(booking)}
          </div>
        </div>
      `;
    })
    .join("");
}

/**
 * Render payment status indicators
 */
function renderPaymentStatus(booking) {
  let html = '<div class="payment-status-section">';

  // Downpayment status
  const downStatus = booking.downpayment_status || "Not Paid";
  const downClass =
    downStatus === "Verified"
      ? "verified"
      : downStatus === "Pending Verification"
      ? "pending"
      : "unpaid";

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
        <strong>Downpayment (50%)</strong>
        <span>${downStatus}</span>
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
          <strong>Full Payment (50%)</strong>
          <span>${fullStatus}</span>
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
  let html = '<div class="booking-actions">';

  // Upload downpayment
  if (booking.can_upload_downpayment) {
    // Check payment method - show GCash or Upload button
    if (booking.payment_method === "gcash") {
      html += `
        <button class="btn-primary" onclick="payWithGCash(${booking.reservation_id})">
          <i class="fas fa-mobile-alt"></i> Pay with GCash
        </button>
      `;
    } else {
      html += `
        <button class="btn-primary" onclick="openPaymentUploadModal(${booking.reservation_id}, 'downpayment', ${booking.downpayment_amount})">
          <i class="fas fa-upload"></i> Upload Downpayment
        </button>
      `;
    }
  }

  // Upload full payment
  if (booking.can_upload_full_payment) {
    const remainingBalance = booking.total_price - booking.downpayment_amount;
    html += `
      <button class="btn-primary" onclick="openPaymentUploadModal(${booking.reservation_id}, 'full_payment', ${remainingBalance})">
        <i class="fas fa-upload"></i> Upload Full Payment
      </button>
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

  // Cancel reservation
  if (booking.can_cancel) {
    html += `
      <button class="btn-danger" onclick="openCancelModal(${booking.reservation_id})">
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
      ? `Downpayment (50%) - ₱${parseFloat(amount).toLocaleString()}`
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
  document.getElementById("cancelReservationId").value = reservationId;
  document.getElementById("cancelModal").style.display = "flex";
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

      // Double confirmation
      if (
        !confirm(
          "⚠️ FINAL CONFIRMATION\n\nAre you absolutely sure you want to cancel?\nYour downpayment will NOT be refunded."
        )
      ) {
        return;
      }

      const submitBtn = form.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> Cancelling...';

      const formData = {
        reservation_id: document.getElementById("cancelReservationId").value,
        reason: document.getElementById("cancelReason").value,
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
 * Pay with GCash using PayMongo
 */
async function payWithGCash(reservationId) {
  if (!confirm("You will be redirected to GCash payment page. Continue?")) {
    return;
  }

  try {
    // Show loading state
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    btn.disabled = true;

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

    const result = await response.json();

    if (result.success && result.checkout_url) {
      // Redirect to PayMongo GCash checkout
      window.location.href = result.checkout_url;
    } else {
      throw new Error(result.message || "Failed to create payment");
    }
  } catch (error) {
    alert("Error: " + error.message);
    // Restore button
    if (btn) {
      btn.innerHTML = originalText;
      btn.disabled = false;
    }
  }
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
