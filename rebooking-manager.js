// Rebooking Manager for User Dashboard
class RebookingManager {
  constructor() {
    this.currentFilter = "all";
    this.initialize();
  }

  initialize() {
    this.setupEventListeners();
    this.loadRebookingStats();
  }

  setupEventListeners() {
    // Handle rebooking form submission
    const rebookingForm = document.getElementById("rebookingForm");
    if (rebookingForm) {
      rebookingForm.addEventListener("submit", (e) =>
        this.handleRebookingSubmit(e)
      );
    }

    // Handle date validation
    const newDateInput = document.getElementById("newCheckInDate");
    if (newDateInput) {
      newDateInput.addEventListener("change", () => this.validateNewDate());
    }
  }

  // Open rebooking modal for a reservation
  openRebookingModal(reservationId, currentDate) {
    const modal = document.getElementById("rebookingModal");
    const currentDateEl = document.getElementById("currentCheckInDate");
    const reservationIdInput = document.getElementById("rebookReservationId");

    if (currentDateEl) currentDateEl.textContent = this.formatDate(currentDate);
    if (reservationIdInput) reservationIdInput.value = reservationId;

    // Set min date (tomorrow) and max date (3 months from now)
    const newDateInput = document.getElementById("newCheckInDate");
    if (newDateInput) {
      const tomorrow = new Date();
      tomorrow.setDate(tomorrow.getDate() + 1);
      const maxDate = new Date();
      maxDate.setMonth(maxDate.getMonth() + 3);

      newDateInput.min = tomorrow.toISOString().split("T")[0];
      newDateInput.max = maxDate.toISOString().split("T")[0];
      newDateInput.value = "";
    }

    modal.style.display = "flex";
  }

  // Handle rebooking form submission
  async handleRebookingSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const reservationId = formData.get("reservation_id");
    const newDate = formData.get("new_date");
    const reason = formData.get("reason");

    if (!reservationId || !newDate || !reason) {
      this.showAlert("Please fill all required fields", "error");
      return;
    }

    try {
      const response = await fetch("api/request_rebooking.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          reservation_id: reservationId,
          new_date: newDate,
          reason: reason,
        }),
      });

      const data = await response.json();

      if (data.success) {
        this.showAlert(data.message, "success");
        this.closeRebookingModal();

        // Refresh relevant sections
        this.loadRebookingStats();
        this.loadUserRebookings();

        // Also refresh My Reservations if it's visible
        if (typeof loadMyBookings === "function") {
          loadMyBookings();
        }
      } else {
        this.showAlert(data.message, "error");
      }
    } catch (error) {
      console.error("Rebooking error:", error);
      this.showAlert("Failed to submit rebooking request", "error");
    }
  }

  // Load user rebooking statistics
  async loadRebookingStats() {
    try {
      const response = await fetch("api/get_user_rebooking_stats.php");
      const data = await response.json();

      if (data.success) {
        // Update stats display
        const stats = {
          pending: document.getElementById("pendingRebookingsCount"),
          approved: document.getElementById("approvedRebookingsCount"),
          rejected: document.getElementById("rejectedRebookingsCount"),
          total: document.getElementById("totalRebookingsCount"),
        };

        for (const [key, element] of Object.entries(stats)) {
          if (element && data.stats[key] !== undefined) {
            element.textContent = data.stats[key];
          }
        }

        // Update notification badge if exists
        const badge = document.getElementById("rebookingNotificationBadge");
        if (badge && data.stats.pending > 0) {
          badge.textContent = data.stats.pending;
          badge.style.display = "inline";
        } else if (badge) {
          badge.style.display = "none";
        }
      }
    } catch (error) {
      console.error("Error loading rebooking stats:", error);
    }
  }

  // Load user rebooking requests
  async loadUserRebookings(filter = "all") {
    const container = document.getElementById("userRebookingsList");
    if (!container) return;

    container.innerHTML = `
      <div class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Loading your rebooking requests...</p>
      </div>
    `;

    try {
      const response = await fetch(
        `api/get_user_rebookings.php?filter=${filter}`
      );
      const data = await response.json();

      if (data.success && data.rebookings.length > 0) {
        this.renderUserRebookings(data.rebookings, container);
      } else {
        container.innerHTML = `
          <div class="empty-state">
            <i class="fas fa-exchange-alt"></i>
            <h3>No Rebooking Requests</h3>
            <p>You haven't made any rebooking requests yet.</p>
          </div>
        `;
      }
    } catch (error) {
      console.error("Error loading user rebookings:", error);
      container.innerHTML = `
        <div class="empty-state">
          <i class="fas fa-exclamation-triangle"></i>
          <h3>Error Loading Requests</h3>
          <p>Please try again later.</p>
        </div>
      `;
    }
  }

  // Render user rebooking requests
  renderUserRebookings(rebookings, container) {
    container.innerHTML = "";

    rebookings.forEach((rebooking) => {
      const item = document.createElement("div");
      item.className = "rebooking-item";

      const statusClass = rebooking.status;
      const statusText =
        rebooking.status.charAt(0).toUpperCase() + rebooking.status.slice(1);

      item.innerHTML = `
        <div class="rebooking-badge ${statusClass}">${statusText}</div>
        
        <div class="rebooking-header">
          <div>
            <h4>Reservation #${rebooking.reservation_id}</h4>
            <p>${rebooking.booking_type_label} - ${rebooking.package_type}</p>
          </div>
          <div class="rebooking-date">Requested: ${this.formatDateTime(
            rebooking.requested_at
          )}</div>
        </div>
        
        <div class="rebooking-dates">
          <div class="rebooking-date-old">
            <div class="rebooking-date-label">Original Date</div>
            <div class="rebooking-date-value">${this.formatDate(
              rebooking.original_date
            )}</div>
          </div>
          
          <div class="rebooking-arrow">
            <i class="fas fa-arrow-right"></i>
          </div>
          
          <div class="rebooking-date-new">
            <div class="rebooking-date-label">Requested New Date</div>
            <div class="rebooking-date-value">${this.formatDate(
              rebooking.requested_date
            )}</div>
          </div>
        </div>
        
        ${
          rebooking.reason
            ? `
        <div class="rebooking-reason">
          <div class="rebooking-reason-label">Reason for Rebooking:</div>
          <div class="rebooking-reason-text">${rebooking.reason}</div>
        </div>
        `
            : ""
        }
        
        <div class="rebooking-footer">
          <div>
            ${
              rebooking.approved_by
                ? `Approved by: ${rebooking.approved_by}`
                : ""
            }
            ${
              rebooking.approved_at
                ? ` on ${this.formatDateTime(rebooking.approved_at)}`
                : ""
            }
          </div>
          
          <div class="rebooking-actions">
            ${
              rebooking.status === "pending"
                ? `
            <button class="rebooking-action-btn cancel" onclick="cancelRebookingRequest(${rebooking.rebooking_id})">
              <i class="fas fa-times"></i> Cancel Request
            </button>
            `
                : ""
            }
            
            <button class="rebooking-action-btn view" onclick="viewReservationDetails(${
              rebooking.reservation_id
            })">
              <i class="fas fa-eye"></i> View Reservation
            </button>
          </div>
        </div>
      `;

      container.appendChild(item);
    });
  }

  // Filter user rebookings
  filterUserRebookings(filter) {
    this.currentFilter = filter;

    // Update active button
    document.querySelectorAll(".rebooking-filter-btn").forEach((btn) => {
      btn.classList.remove("active");
    });

    const activeBtn = document.querySelector(
      `.rebooking-filter-btn[onclick*="${filter}"]`
    );
    if (activeBtn) activeBtn.classList.add("active");

    this.loadUserRebookings(filter);
  }

  // Cancel rebooking request
  async cancelRebookingRequest(rebookingId) {
    if (!confirm("Are you sure you want to cancel this rebooking request?")) {
      return;
    }

    try {
      const response = await fetch("api/cancel_rebooking_request.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ rebooking_id: rebookingId }),
      });

      const data = await response.json();

      if (data.success) {
        this.showAlert("Rebooking request cancelled successfully", "success");
        this.loadRebookingStats();
        this.loadUserRebookings(this.currentFilter);

        // Refresh My Reservations
        if (typeof loadMyBookings === "function") {
          loadMyBookings();
        }
      } else {
        this.showAlert(data.message, "error");
      }
    } catch (error) {
      console.error("Error cancelling rebooking:", error);
      this.showAlert("Failed to cancel rebooking request", "error");
    }
  }

  // Format date for display
  formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
      weekday: "short",
      year: "numeric",
      month: "short",
      day: "numeric",
    });
  }

  // Format date time for display
  formatDateTime(dateTimeString) {
    const date = new Date(dateTimeString);
    return date.toLocaleDateString("en-US", {
      month: "short",
      day: "numeric",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  // Show alert message
  showAlert(message, type = "info") {
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `
      <span>${message}</span>
      <button onclick="this.parentElement.remove()">&times;</button>
    `;

    document.body.appendChild(alertDiv);

    setTimeout(() => {
      if (alertDiv.parentNode) {
        alertDiv.remove();
      }
    }, 5000);
  }

  // Close rebooking modal
  closeRebookingModal() {
    const modal = document.getElementById("rebookingModal");
    if (modal) {
      modal.style.display = "none";
      document.getElementById("rebookingForm").reset();
    }
  }

  // Validate new date
  validateNewDate() {
    const newDateInput = document.getElementById("newCheckInDate");
    if (!newDateInput || !newDateInput.value) return true;

    const newDate = new Date(newDateInput.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (newDate < today) {
      this.showAlert("New date must be in the future", "error");
      newDateInput.value = "";
      return false;
    }

    return true;
  }
}

// Initialize rebooking manager
document.addEventListener("DOMContentLoaded", () => {
  window.rebookingManager = new RebookingManager();

  // Add rebooking notification badge to navigation
  const notificationsBtn = document.querySelector(
    '[data-section="rebookings-status"]'
  );
  if (notificationsBtn) {
    const badge = document.createElement("span");
    badge.id = "rebookingNotificationBadge";
    badge.className = "notification-badge";
    badge.style.display = "none";
    notificationsBtn.appendChild(badge);
  }
});

// Global functions for onclick handlers
window.filterUserRebookings = (filter) => {
  if (window.rebookingManager) {
    window.rebookingManager.filterUserRebookings(filter);
  }
};

window.cancelRebookingRequest = (rebookingId) => {
  if (window.rebookingManager) {
    window.rebookingManager.cancelRebookingRequest(rebookingId);
  }
};

window.viewReservationDetails = (reservationId) => {
  // Navigate to My Reservations and show details
  const myBookingsBtn = document.querySelector('[data-section="my-bookings"]');
  if (myBookingsBtn) {
    myBookingsBtn.click();

    // Scroll to and highlight the reservation
    setTimeout(() => {
      const reservationCard = document.querySelector(
        `[data-reservation-id="${reservationId}"]`
      );
      if (reservationCard) {
        reservationCard.scrollIntoView({ behavior: "smooth" });
        reservationCard.style.boxShadow = "0 0 0 3px #667eea";
        setTimeout(() => {
          reservationCard.style.boxShadow = "";
        }, 3000);
      }
    }, 500);
  }
};
