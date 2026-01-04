// Admin Rebooking Management
class AdminRebookingManager {
  constructor() {
    this.currentFilter = "pending";
    this.initialize();
  }

  initialize() {
    this.loadRebookingStats();
    this.loadRebookingRequests();
    this.setupEventListeners();
  }

  setupEventListeners() {
    // Date availability check
    document.addEventListener("change", (e) => {
      if (e.target.matches("[data-check-availability]")) {
        this.checkDateAvailability(e.target);
      }
    });
  }

  // Load rebooking statistics
  async loadRebookingStats() {
    try {
      const response = await fetch("api/get_rebooking_requests.php?status=all");
      const data = await response.json();

      if (data.success && data.counts) {
        const counts = data.counts;

        // Update stats display
        document.getElementById("rebookingPendingCount").textContent =
          counts.pending || 0;
        document.getElementById("rebookingApprovedCount").textContent =
          counts.approved || 0;
        document.getElementById("rebookingRejectedCount").textContent =
          counts.rejected || 0;
        document.getElementById("rebookingTotalCount").textContent =
          counts.total || 0;

        // Update filter badges
        document.getElementById("filter-pending-count").textContent =
          counts.pending || 0;
        document.getElementById("filter-approved-count").textContent =
          counts.approved || 0;
        document.getElementById("filter-rejected-count").textContent =
          counts.rejected || 0;
      }
    } catch (error) {
      console.error("Error loading rebooking stats:", error);
    }
  }

  // Load rebooking requests
  async loadRebookingRequests(filter = "pending") {
    this.currentFilter = filter;

    const container = document.getElementById("rebookingRequestsContainer");
    if (!container) return;

    container.innerHTML = `
            <div style="background:white; padding:40px; border-radius:16px; text-align:center; color:#94a3b8;">
                <i class="fas fa-spinner fa-spin" style="font-size:48px; margin-bottom:16px;"></i>
                <p style="font-size:16px; margin:0;">Loading rebooking requests...</p>
            </div>
        `;

    try {
      const response = await fetch(
        `api/get_rebooking_requests.php?status=${filter}`
      );
      const data = await response.json();

      if (data.success && data.requests.length > 0) {
        this.renderRebookingRequests(data.requests, container);
      } else {
        container.innerHTML = `
                    <div style="background:white; padding:60px 20px; border-radius:16px; text-align:center;">
                        <i class="fas fa-inbox" style="font-size:64px; color:#e2e8f0; margin-bottom:20px;"></i>
                        <h3 style="color:#64748b; margin:0 0 8px 0;">No Rebooking Requests</h3>
                        <p style="color:#94a3b8; margin:0;">No ${filter} rebooking requests found.</p>
                    </div>
                `;
      }
    } catch (error) {
      console.error("Error loading rebooking requests:", error);
      container.innerHTML = `
                <div style="background:white; padding:40px; border-radius:16px; text-align:center; color:#ef4444;">
                    <i class="fas fa-exclamation-triangle" style="font-size:48px; margin-bottom:16px;"></i>
                    <p style="font-size:16px; margin:0;">Error loading requests. Please try again.</p>
                </div>
            `;
    }
  }

  // Render rebooking requests
  renderRebookingRequests(requests, container) {
    container.innerHTML = "";

    requests.forEach((request) => {
      const requestCard = this.createRequestCard(request);
      container.appendChild(requestCard);
    });
  }

  // Create request card HTML
  createRequestCard(request) {
    const card = document.createElement("div");
    card.className = "rebooking-request-card";
    card.style.cssText = `
            background: white;
            border: 2px solid #11224e;
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s ease;
        `;

    const status = this.getRequestStatus(request);
    const statusColor = this.getStatusColor(status);

    card.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px;">
                <div>
                    <h3 style="color:#11224e; margin:0 0 8px 0; font-size:18px;">Reservation #${
                      request.reservation_id
                    }</h3>
                    <div style="display:flex; gap:12px; flex-wrap:wrap;">
                        <span style="background:#f1f5f9; color:#64748b; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:500;">
                            ${request.booking_type_label}
                        </span>
                        <span style="background:#f1f5f9; color:#64748b; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:500;">
                            ${request.package_type}
                        </span>
                        <span style="background:${
                          statusColor.background
                        }; color:${
      statusColor.color
    }; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:500;">
                            ${status.text}
                        </span>
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:12px; color:#64748b; margin-bottom:4px;">Requested on</div>
                    <div style="font-size:14px; font-weight:600; color:#11224e;">
                        ${this.formatDate(request.created_at)}
                    </div>
                </div>
            </div>
            
            <!-- Guest Information -->
            <div style="background:#f8fafc; padding:16px; border-radius:12px; margin-bottom:20px;">
                <h4 style="color:#11224e; margin:0 0 12px 0; font-size:14px;">
                    <i class="fas fa-user" style="margin-right:8px;"></i>Guest Information
                </h4>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px;">
                    <div>
                        <div style="font-size:12px; color:#64748b;">Name</div>
                        <div style="font-weight:600; color:#1e293b;">${
                          request.guest_name || request.user_full_name
                        }</div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:#64748b;">Email</div>
                        <div style="color:#1e293b;">${
                          request.guest_email || request.user_email_account
                        }</div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:#64748b;">Phone</div>
                        <div style="color:#1e293b;">${
                          request.guest_phone || "Not provided"
                        }</div>
                    </div>
                </div>
            </div>
            
            <!-- Date Comparison -->
            <div style="display:grid; grid-template-columns:1fr auto 1fr; gap:20px; margin-bottom:20px; text-align:center;">
                <!-- Original Date -->
                <div style="background:#f8f9fa; padding:20px; border-radius:12px; border:1px solid #e9ecef;">
                    <div style="font-size:12px; color:#64748b; margin-bottom:8px;">Original Check-in</div>
                    <div style="font-size:20px; font-weight:700; color:#11224e; margin-bottom:4px;">
                        ${this.formatDate(request.check_in_date)}
                    </div>
                    <div style="font-size:12px; color:#64748b;">
                        ${request.check_in_time || "N/A"}
                    </div>
                </div>
                
                <!-- Arrow -->
                <div style="display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-arrow-right" style="font-size:24px; color:#11224e;"></i>
                </div>
                
                <!-- New Date -->
                <div style="background:#e7f5ff; padding:20px; border-radius:12px; border:1px solid #a5d8ff;">
                    <div style="font-size:12px; color:#0c63e4; margin-bottom:8px;">Requested New Date</div>
                    <div style="font-size:20px; font-weight:700; color:#0c63e4; margin-bottom:4px;">
                        ${this.formatDate(request.rebooking_new_date)}
                    </div>
                    <div style="font-size:12px; color:#0c63e4;">
                        Same time slot
                    </div>
                </div>
            </div>
            
            <!-- Reason -->
            ${
              request.rebooking_reason
                ? `
            <div style="background:#fef9e7; padding:16px; border-radius:12px; margin-bottom:20px; border-left:4px solid#ffc107;">
                <h4 style="color:#856404; margin:0 0 8px 0; font-size:14px;">
                    <i class="fas fa-comment" style="margin-right:8px;"></i>Reason for Rebooking
                </h4>
                <p style="color:#856404; margin:0; line-height:1.5;">
                    ${request.rebooking_reason}
                </p>
            </div>
            `
                : ""
            }
            
            <!-- Actions -->
            ${
              status.value === "pending"
                ? `
            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <button onclick="adminRebookingManager.rejectRequest(${request.reservation_id})" 
                        style="padding:12px 24px; background:#fef2f2; color:#dc2626; border:1px solid#fecaca; border-radius:10px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-times"></i> Reject
                </button>
                <button onclick="adminRebookingManager.showApproveModal(${request.reservation_id})" 
                        style="padding:12px 24px; background:#11224e; color:white; border:none; border-radius:10px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-check"></i> Approve
                </button>
            </div>
            `
                : ""
            }
            
            ${
              status.value === "approved"
                ? `
            <div style="background:#d4edda; color:#155724; padding:12px 16px; border-radius:8px; border-left:4px solid#28a745;">
                <div style="display:flex; align-items:center; gap:8px; font-weight:500;">
                    <i class="fas fa-check-circle"></i>
                    <span>Approved by ${
                      request.approved_by_name || "Admin"
                    } on ${this.formatDateTime(
                    request.rebooking_approved_at
                  )}</span>
                </div>
            </div>
            `
                : ""
            }
            
            ${
              status.value === "rejected"
                ? `
            <div style="background:#f8d7da; color:#721c24; padding:12px 16px; border-radius:8px; border-left:4px solid#dc3545;">
                <div style="display:flex; align-items:center; gap:8px; font-weight:500;">
                    <i class="fas fa-times-circle"></i>
                    <span>Rejected by ${
                      request.approved_by_name || "Admin"
                    } on ${this.formatDateTime(
                    request.rebooking_approved_at
                  )}</span>
                </div>
            </div>
            `
                : ""
            }
        `;

    return card;
  }

  // Get request status
  getRequestStatus(request) {
    if (request.rebooking_approved === null) {
      return { value: "pending", text: "Pending Approval", color: "#ff9800" };
    } else if (request.rebooking_approved == 1) {
      return { value: "approved", text: "Approved", color: "#10b981" };
    } else {
      return { value: "rejected", text: "Rejected", color: "#ef4444" };
    }
  }

  // Get status color
  getStatusColor(status) {
    const colors = {
      pending: { background: "#fff3cd", color: "#856404" },
      approved: { background: "#d4edda", color: "#155724" },
      rejected: { background: "#f8d7da", color: "#721c24" },
    };
    return colors[status.value] || { background: "#f1f5f9", color: "#64748b" };
  }

  // Show approve modal
  async showApproveModal(reservationId) {
    // Check date availability first
    const isAvailable = await this.checkDateAvailabilityForRequest(
      reservationId
    );

    if (!isAvailable) {
      alert(
        "The requested date is no longer available. Please inform the guest."
      );
      return;
    }

    if (confirm("Are you sure you want to approve this rebooking request?")) {
      await this.approveRequest(reservationId);
    }
  }

  // Approve rebooking request
  async approveRequest(reservationId) {
    try {
      const formData = new FormData();
      formData.append("reservation_id", reservationId);
      formData.append("action", "approve");

      const response = await fetch("api/approve_rebooking.php", {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        alert(data.message);
        this.loadRebookingStats();
        this.loadRebookingRequests(this.currentFilter);
      } else {
        alert("Error: " + data.message);
      }
    } catch (error) {
      console.error("Error approving request:", error);
      alert("Failed to approve request");
    }
  }

  // Reject rebooking request
  async rejectRequest(reservationId) {
    const reason = prompt("Please provide a reason for rejection (optional):");

    if (reason === null) return; // User cancelled

    try {
      const formData = new FormData();
      formData.append("reservation_id", reservationId);
      formData.append("action", "reject");
      if (reason) formData.append("reason", reason);

      const response = await fetch("api/approve_rebooking.php", {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        alert(data.message);
        this.loadRebookingStats();
        this.loadRebookingRequests(this.currentFilter);
      } else {
        alert("Error: " + data.message);
      }
    } catch (error) {
      console.error("Error rejecting request:", error);
      alert("Failed to reject request");
    }
  }

  // Check date availability
  async checkDateAvailabilityForRequest(reservationId) {
    try {
      const response = await fetch(
        `api/check_date_availability.php?reservation_id=${reservationId}`
      );
      const data = await response.json();
      return data.success && data.available;
    } catch (error) {
      console.error("Error checking availability:", error);
      return false;
    }
  }

  // Format date
  formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
      weekday: "short",
      year: "numeric",
      month: "short",
      day: "numeric",
    });
  }

  // Format date time
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
}

// Initialize admin rebooking manager
document.addEventListener("DOMContentLoaded", () => {
  window.adminRebookingManager = new AdminRebookingManager();
});

// Filter functions for admin side
window.filterRebookingRequests = (filter) => {
  if (window.adminRebookingManager) {
    // Update active button
    document.querySelectorAll(".rebooking-filter-btn").forEach((btn) => {
      btn.classList.remove("active");
    });

    const activeBtn = document.querySelector(
      `.rebooking-filter-btn[onclick*="${filter}"]`
    );
    if (activeBtn) {
      activeBtn.classList.add("active");
      activeBtn.style.background =
        filter === "pending"
          ? "#ff9800"
          : filter === "approved"
          ? "#10b981"
          : filter === "rejected"
          ? "#ef4444"
          : "#11224e";
      activeBtn.style.color = "white";
    }

    // Reset other buttons
    document
      .querySelectorAll(".rebooking-filter-btn:not(.active)")
      .forEach((btn) => {
        btn.style.background = "#f1f5f9";
        btn.style.color = "#64748b";
      });

    window.adminRebookingManager.loadRebookingRequests(filter);
  }
};

window.loadRebookingRequests = () => {
  if (window.adminRebookingManager) {
    window.adminRebookingManager.loadRebookingStats();
    window.adminRebookingManager.loadRebookingRequests(
      window.adminRebookingManager.currentFilter
    );
  }
};
