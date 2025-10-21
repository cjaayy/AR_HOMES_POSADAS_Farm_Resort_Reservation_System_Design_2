// ===== NAVIGATION AND SIDEBAR FUNCTIONALITY =====

// Get DOM elements
const sidebar = document.getElementById("sidebar");
const navLinks = document.querySelectorAll(".nav-link");
const contentSections = document.querySelectorAll(".content-section");

// Initialize dashboard
document.addEventListener("DOMContentLoaded", function () {
  // Show dashboard section by default
  showSection("dashboard");
  setActiveNavItem("dashboard");
});

// Show specific content section
function showSection(sectionId) {
  // Hide all sections
  contentSections.forEach((section) => {
    section.classList.remove("active");
  });

  // Show target section
  const targetSection = document.getElementById(sectionId);
  if (targetSection) {
    targetSection.classList.add("active");
  }

  // Update active navigation item
  setActiveNavItem(sectionId);

  // Close mobile sidebar if open
  if (window.innerWidth <= 768) {
    closeSidebar();
  }
}

// Set active navigation item
function setActiveNavItem(sectionId) {
  // Remove active class from all nav items
  document.querySelectorAll(".nav-item").forEach((item) => {
    item.classList.remove("active");
  });

  // Add active class to current nav item
  const activeLink = document.querySelector(`[data-section="${sectionId}"]`);
  if (activeLink) {
    activeLink.closest(".nav-item").classList.add("active");
  }
}

// Navigation link click handlers
navLinks.forEach((link) => {
  link.addEventListener("click", function (e) {
    e.preventDefault();
    const sectionId = this.getAttribute("data-section");
    showSection(sectionId);
  });
});

// ===== MOBILE SIDEBAR FUNCTIONALITY =====

// Toggle sidebar for mobile
function toggleSidebar() {
  sidebar.classList.toggle("open");

  // Create or remove overlay
  if (sidebar.classList.contains("open")) {
    createSidebarOverlay();
  } else {
    removeSidebarOverlay();
  }
}

// Close sidebar
function closeSidebar() {
  sidebar.classList.remove("open");
  removeSidebarOverlay();
}

// Create sidebar overlay for mobile
function createSidebarOverlay() {
  if (window.innerWidth <= 768) {
    let overlay = document.querySelector(".sidebar-overlay");
    if (!overlay) {
      overlay = document.createElement("div");
      overlay.className = "sidebar-overlay";
      document.body.appendChild(overlay);

      // Close sidebar when overlay is clicked
      overlay.addEventListener("click", closeSidebar);
    }
    overlay.classList.add("active");
  }
}

// Remove sidebar overlay
function removeSidebarOverlay() {
  const overlay = document.querySelector(".sidebar-overlay");
  if (overlay) {
    overlay.classList.remove("active");
    setTimeout(() => {
      if (overlay.parentNode) {
        overlay.parentNode.removeChild(overlay);
      }
    }, 300);
  }
}

// ===== LOGOUT FUNCTIONALITY =====

// Make functions globally accessible
window.logout = function logout() {
  console.log("🚪 Logout button clicked");
  // Show logout modal instead of confirm dialog
  showLogoutModal();
};

// Show logout modal
window.showLogoutModal = function showLogoutModal() {
  const modal = document.getElementById("logoutModal");

  if (!modal) {
    console.error("❌ Logout modal not found! Logging out directly...");
    // If modal doesn't exist, logout directly
    confirmLogout();
    return;
  }

  console.log("✅ Showing logout modal");
  modal.classList.add("show");
  modal.style.display = "flex";

  // Add event listener to overlay for closing modal
  const overlay = modal.querySelector(".logout-modal-overlay");
  if (overlay) {
    overlay.onclick = hideLogoutModal;
  }

  // Prevent body scroll when modal is open
  document.body.style.overflow = "hidden";
};

// Hide logout modal
window.hideLogoutModal = function hideLogoutModal() {
  const modal = document.getElementById("logoutModal");

  if (!modal) {
    console.warn("⚠️ Modal not found when trying to hide");
    return;
  }

  console.log("👋 Hiding logout modal");

  // Add hide animation
  modal.classList.add("hide");
  modal.classList.remove("show");

  // Remove modal after animation
  setTimeout(() => {
    modal.classList.remove("hide");
    modal.style.display = "none";
    modal.style.visibility = "hidden";
    modal.style.opacity = "0";
    modal.style.pointerEvents = "none";

    // Reset body scroll
    document.body.style.overflow = "auto";
    document.body.style.pointerEvents = "auto";

    // Remove the overlay click listener to prevent duplicates
    const overlay = modal.querySelector(".logout-modal-overlay");
    if (overlay) {
      overlay.onclick = null;
    }

    console.log("✅ Modal fully hidden and reset");
  }, 300);
};

// Confirm logout action
window.confirmLogout = function confirmLogout() {
  console.log("✅ Logout confirmed, processing...");

  // Hide modal first (if it exists)
  const modal = document.getElementById("logoutModal");
  if (modal) {
    hideLogoutModal();
  }

  // Add logout animation to entire page
  setTimeout(() => {
    document.body.style.opacity = "0";
    document.body.style.transition = "opacity 0.5s ease";

    // Show loading state
    const confirmBtn = document.querySelector(".logout-confirm-btn");
    if (confirmBtn) {
      confirmBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> <span>Logging out...</span>';
      confirmBtn.disabled = true;
    }

    // Call logout API - use relative path from where the page is loaded
    // Determine the correct path based on current location
    const currentPath = window.location.pathname;
    const isInAdminFolder = currentPath.includes("/admin/");
    const logoutPath = isInAdminFolder ? "logout.php" : "admin/logout.php";

    console.log("🔓 Logging out...", {
      currentPath,
      isInAdminFolder,
      logoutPath,
    });

    fetch(logoutPath, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
    })
      .then((response) => {
        console.log("Logout response status:", response.status);
        return response.json();
      })
      .then((data) => {
        console.log("Logout response:", data);
        // Clear any stored session data
        localStorage.removeItem("adminSession");
        sessionStorage.clear();

        // Redirect to login page - adjust path based on current location
        const indexPath = isInAdminFolder ? "../index.html" : "index.html";
        console.log("Redirecting to:", indexPath);

        setTimeout(() => {
          window.location.href = indexPath;
        }, 500);
      })
      .catch((error) => {
        console.error("Logout error:", error);
        // Redirect anyway - adjust path based on current location
        const indexPath = isInAdminFolder ? "../index.html" : "index.html";
        console.log("Error occurred, redirecting to:", indexPath);
        window.location.href = indexPath;
      });
  }, 100);
};

// Handle ESC key to close modal
document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") {
    const modal = document.getElementById("logoutModal");
    if (modal && modal.classList.contains("show")) {
      hideLogoutModal();
    }
  }
});

// ===== RESPONSIVE FUNCTIONALITY =====

// Handle window resize
window.addEventListener("resize", function () {
  if (window.innerWidth > 768) {
    // Desktop view - ensure sidebar is visible and remove mobile overlay
    sidebar.classList.remove("open");
    removeSidebarOverlay();
  }
});

// ===== DASHBOARD INTERACTIONS =====

// Quick action button handlers
document.addEventListener("DOMContentLoaded", function () {
  // Add click handlers for action buttons if they exist
  const actionButtons = document.querySelectorAll(".action-btn");
  actionButtons.forEach((button) => {
    button.addEventListener("click", function () {
      // Add click animation
      this.style.transform = "translateY(-3px) scale(0.98)";
      setTimeout(() => {
        this.style.transform = "";
      }, 150);
    });
  });

  // Add hover effects for stat cards
  const statCards = document.querySelectorAll(".stat-card");
  statCards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-5px) scale(1.02)";
    });

    card.addEventListener("mouseleave", function () {
      this.style.transform = "";
    });
  });
});

// ===== KEYBOARD NAVIGATION =====

document.addEventListener("keydown", function (e) {
  // ESC key closes mobile sidebar
  if (e.key === "Escape" && window.innerWidth <= 768) {
    closeSidebar();
  }

  // Alt + number keys for quick navigation
  if (e.altKey) {
    switch (e.key) {
      case "1":
        e.preventDefault();
        showSection("dashboard");
        break;
      case "2":
        e.preventDefault();
        showSection("reservations");
        break;
      case "3":
        e.preventDefault();
        showSection("users");
        break;
      case "4":
        e.preventDefault();
        showSection("rooms");
        break;
      case "5":
        e.preventDefault();
        showSection("reports");
        break;
      case "6":
        e.preventDefault();
        showSection("settings");
        break;
    }
  }
});

// ===== UTILITY FUNCTIONS =====

// Smooth scroll to top when switching sections
function scrollToTop() {
  window.scrollTo({
    top: 0,
    behavior: "smooth",
  });
}

// Format numbers for display
function formatNumber(num) {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + "M";
  } else if (num >= 1000) {
    return (num / 1000).toFixed(1) + "K";
  }
  return num.toString();
}

// Format currency for display
function formatCurrency(amount) {
  return new Intl.NumberFormat("en-PH", {
    style: "currency",
    currency: "PHP",
    minimumFractionDigits: 0,
  }).format(amount);
}

// ===== ANIMATION UTILITIES =====

// Add entrance animation to elements
function animateIn(element, delay = 0) {
  setTimeout(() => {
    element.style.opacity = "0";
    element.style.transform = "translateY(20px)";
    element.style.transition = "all 0.5s ease";

    setTimeout(() => {
      element.style.opacity = "1";
      element.style.transform = "translateY(0)";
    }, 50);
  }, delay);
}

// Stagger animations for multiple elements
function staggerAnimations(elements, delayBetween = 100) {
  elements.forEach((element, index) => {
    animateIn(element, index * delayBetween);
  });
}

// ===== DASHBOARD DATA SIMULATION =====

// Simulate real-time updates (in a real app, this would be WebSocket or polling)
function simulateDataUpdates() {
  setInterval(() => {
    // Update random stat
    const statNumbers = document.querySelectorAll(".stat-info h3");
    if (statNumbers.length > 0) {
      const randomStat =
        statNumbers[Math.floor(Math.random() * statNumbers.length)];
      const currentValue = parseInt(
        randomStat.textContent.replace(/[^\d]/g, "")
      );
      const change = Math.floor(Math.random() * 3) - 1; // -1, 0, or 1
      const newValue = Math.max(0, currentValue + change);

      if (randomStat.textContent.includes("₱")) {
        randomStat.textContent = formatCurrency(newValue);
      } else {
        randomStat.textContent = newValue.toString();
      }
    }
  }, 30000); // Update every 30 seconds
}

// Start data simulation
setTimeout(simulateDataUpdates, 5000);

// ===== ACCESSIBILITY ENHANCEMENTS =====

// Focus management for keyboard navigation
document.addEventListener("keydown", function (e) {
  // Tab navigation enhancement
  if (e.key === "Tab") {
    const focusableElements = document.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );

    if (e.shiftKey) {
      // Shift + Tab (backwards)
      if (document.activeElement === focusableElements[0]) {
        e.preventDefault();
        focusableElements[focusableElements.length - 1].focus();
      }
    } else {
      // Tab (forwards)
      if (
        document.activeElement ===
        focusableElements[focusableElements.length - 1]
      ) {
        e.preventDefault();
        focusableElements[0].focus();
      }
    }
  }
});

// Add focus indicators for better accessibility
document.addEventListener("DOMContentLoaded", function () {
  const focusableElements = document.querySelectorAll(
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
  );

  focusableElements.forEach((element) => {
    element.addEventListener("focus", function () {
      this.style.outline = "2px solid #667eea";
      this.style.outlineOffset = "2px";
    });

    element.addEventListener("blur", function () {
      this.style.outline = "";
      this.style.outlineOffset = "";
    });
  });
});

// ===== ERROR HANDLING =====

// Global error handler
window.addEventListener("error", function (e) {
  console.error("Dashboard Error:", e.error);

  // Show user-friendly error message
  const errorMessage = document.createElement("div");
  errorMessage.className = "error-toast";
  errorMessage.textContent = "An error occurred. Please refresh the page.";
  errorMessage.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #ff6b6b;
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;

  document.body.appendChild(errorMessage);

  setTimeout(() => {
    errorMessage.remove();
  }, 5000);
});

// ===== PERFORMANCE OPTIMIZATION =====

// Debounce function for resize events
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Debounced resize handler
const debouncedResize = debounce(() => {
  if (window.innerWidth > 768) {
    sidebar.classList.remove("open");
    removeSidebarOverlay();
  }
}, 250);

window.addEventListener("resize", debouncedResize);

// ===== SETTINGS FUNCTIONALITY =====

// Toggle settings panel visibility
function toggleSettingsPanel(panelId) {
  const option = document
    .querySelector(`#${panelId}-panel`)
    .closest(".settings-option");
  const panel = document.getElementById(`${panelId}-panel`);
  const arrow = option.querySelector(".option-arrow");

  // Close all other panels first
  const allOptions = document.querySelectorAll(".settings-option");
  const allPanels = document.querySelectorAll(".settings-panel");

  allOptions.forEach((opt) => {
    if (opt !== option) {
      opt.classList.remove("active");
      opt.querySelector(".option-arrow").style.transform = "rotate(0deg)";
    }
  });

  allPanels.forEach((p) => {
    if (p !== panel) {
      p.classList.remove("active");
    }
  });

  // Toggle current panel
  const isActive = option.classList.contains("active");

  if (isActive) {
    option.classList.remove("active");
    panel.classList.remove("active");
    arrow.style.transform = "rotate(0deg)";
  } else {
    option.classList.add("active");
    panel.classList.add("active");
    arrow.style.transform = "rotate(90deg)";
  }
}

// Prevent settings panels from closing when clicking inside them
document.addEventListener("DOMContentLoaded", function () {
  const settingsPanels = document.querySelectorAll(".settings-panel");

  settingsPanels.forEach((panel) => {
    panel.addEventListener("click", function (e) {
      e.stopPropagation();
    });
  });

  // Also prevent form elements from closing the panel
  const formElements = document.querySelectorAll(
    ".settings-panel input, .settings-panel button, .settings-panel label"
  );

  formElements.forEach((element) => {
    element.addEventListener("click", function (e) {
      e.stopPropagation();
    });
  });
});

// Toggle password visibility in settings
function toggleSettingsPassword(inputId) {
  const input = document.getElementById(inputId);
  const toggleBtn = input.parentElement.querySelector(".password-toggle i");

  if (input.type === "password") {
    input.type = "text";
    toggleBtn.className = "fas fa-eye-slash";
  } else {
    input.type = "password";
    toggleBtn.className = "fas fa-eye";
  }
}

// Reset profile form
function resetProfileForm() {
  const form = document.getElementById("adminProfileForm");
  if (form) {
    // Reset to default values
    document.getElementById("adminFullName").value = "Administrator";
    document.getElementById("adminUsername").value = "admin@resort.com";
    document.getElementById("currentPassword").value = "";
    document.getElementById("newPassword").value = "";
    document.getElementById("confirmPassword").value = "";

    // Reset any error states
    clearFormErrors();

    // Show confirmation
    showNotification("Form reset to default values", "info");
  }
}

// Handle admin profile form submission
document.addEventListener("DOMContentLoaded", function () {
  const profileForm = document.getElementById("adminProfileForm");
  if (profileForm) {
    profileForm.addEventListener("submit", function (e) {
      e.preventDefault();
      handleProfileUpdate();
    });
  }
});

// Handle profile update
function handleProfileUpdate() {
  const form = document.getElementById("adminProfileForm");
  const formData = new FormData(form);

  // Get form values
  const fullName = formData.get("fullName").trim();
  const username = formData.get("username").trim();
  const currentPassword = formData.get("currentPassword").trim();
  const newPassword = formData.get("newPassword").trim();
  const confirmPassword = formData.get("confirmPassword").trim();

  // Clear previous errors
  clearFormErrors();

  // Validation
  let isValid = true;

  if (!fullName) {
    showFieldError("adminFullName", "Full name is required");
    isValid = false;
  }

  if (!username || !isValidEmail(username)) {
    showFieldError("adminUsername", "Valid email is required");
    isValid = false;
  }

  if (!currentPassword) {
    showFieldError("currentPassword", "Current password is required");
    isValid = false;
  }

  // If new password is provided, validate it
  if (newPassword) {
    if (newPassword.length < 6) {
      showFieldError(
        "newPassword",
        "New password must be at least 6 characters"
      );
      isValid = false;
    }

    if (newPassword !== confirmPassword) {
      showFieldError("confirmPassword", "Passwords do not match");
      isValid = false;
    }
  }

  if (!isValid) {
    showNotification("Please fix the errors below", "error");
    return;
  }

  // Simulate API call
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalText = submitBtn.innerHTML;

  submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
  submitBtn.disabled = true;

  setTimeout(() => {
    // Simulate successful update
    submitBtn.innerHTML = '<i class="fas fa-check"></i> Saved!';

    // Update header display name if changed
    const headerName = document.querySelector(".admin-name");
    if (headerName && fullName !== "Administrator") {
      headerName.textContent = fullName;
    }

    // Clear password fields for security
    document.getElementById("currentPassword").value = "";
    document.getElementById("newPassword").value = "";
    document.getElementById("confirmPassword").value = "";

    showNotification("Profile updated successfully!", "success");

    setTimeout(() => {
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    }, 2000);
  }, 1500);
}

// Email validation function
function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

// Show field error
function showFieldError(fieldId, message) {
  const field = document.getElementById(fieldId);
  const wrapper = field.closest(".input-wrapper");

  // Remove existing error
  const existingError = wrapper.parentElement.querySelector(".error-message");
  if (existingError) {
    existingError.remove();
  }

  // Add error styling
  wrapper.style.borderColor = "#e74c3c";
  field.style.borderColor = "#e74c3c";

  // Add error message
  const errorDiv = document.createElement("div");
  errorDiv.className = "error-message";
  errorDiv.style.cssText = `
    color: #e74c3c;
    font-size: 0.8rem;
    margin-top: 0.3rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
  `;
  errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;

  wrapper.parentElement.appendChild(errorDiv);
}

// Clear form errors
function clearFormErrors() {
  const errorMessages = document.querySelectorAll(".error-message");
  errorMessages.forEach((error) => error.remove());

  const inputs = document.querySelectorAll("#adminProfileForm input");
  inputs.forEach((input) => {
    input.style.borderColor = "";
    const wrapper = input.closest(".input-wrapper");
    if (wrapper) {
      wrapper.style.borderColor = "";
    }
  });
}

// Show notification
function showNotification(message, type = "info") {
  const notification = document.createElement("div");
  notification.className = `notification notification-${type}`;

  const colors = {
    success: "#27ae60",
    error: "#e74c3c",
    info: "#3498db",
    warning: "#f39c12",
  };

  const icons = {
    success: "check-circle",
    error: "exclamation-circle",
    info: "info-circle",
    warning: "exclamation-triangle",
  };

  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: ${colors[type]};
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    z-index: 10000;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    max-width: 300px;
    animation: slideIn 0.3s ease;
  `;

  notification.innerHTML = `<i class="fas fa-${icons[type]}"></i> ${message}`;

  document.body.appendChild(notification);

  setTimeout(() => {
    notification.style.animation = "slideOut 0.3s ease";
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 300);
  }, 3000);
}

// Add CSS animations for notifications
const notificationStyles = document.createElement("style");
notificationStyles.textContent = `
  @keyframes slideIn {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
  
  @keyframes slideOut {
    from {
      transform: translateX(0);
      opacity: 1;
    }
    to {
      transform: translateX(100%);
      opacity: 0;
    }
  }
`;
document.head.appendChild(notificationStyles);

// Attach logout button event listener when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
  const logoutButton = document.getElementById("logoutButton");

  if (logoutButton) {
    // Remove any existing listeners and add new one
    logoutButton.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();

      console.log("🖱️ Logout button clicked via event listener");

      // Ensure button is not disabled
      if (this.disabled) {
        console.warn("⚠️ Button is disabled, ignoring click");
        return;
      }

      logout();
    });

    // Ensure button is always clickable
    logoutButton.disabled = false;
    logoutButton.style.pointerEvents = "auto";
    logoutButton.style.cursor = "pointer";

    console.log("✅ Logout button event listener attached");
  } else {
    console.warn("⚠️ Logout button not found!");
  }
});

console.log(
  "🏨 AR Homes Posadas Farm Resort - Admin Dashboard Loaded Successfully!"
);

// ===== USER MANAGEMENT FUNCTIONALITY =====

let allUsers = [];
let filteredUsers = [];
let currentPage = 1;
const usersPerPage = 10;

// Load users when users section is shown
document.addEventListener("DOMContentLoaded", function () {
  // Add click handler for users nav link
  const usersNavLink = document.querySelector('[data-section="users"]');
  if (usersNavLink) {
    usersNavLink.addEventListener("click", function () {
      loadUsers();
    });
  }

  // Add search functionality
  const searchInput = document.getElementById("searchUsers");
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      filterUsers();
    });
  }
});

// Fetch users from the server
async function loadUsers() {
  console.log("Loading users from database...");

  const tableBody = document.getElementById("usersTableBody");

  // Show loading state
  tableBody.innerHTML = `
    <tr>
      <td colspan="10" style="text-align: center; padding: 2rem;">
        <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #667eea;"></i>
        <p style="margin-top: 1rem; color: #666;">Loading users...</p>
      </td>
    </tr>
  `;

  try {
    const response = await fetch("get_users.php");
    const data = await response.json();

    if (data.success) {
      allUsers = data.users;
      filteredUsers = [...allUsers];

      console.log(`Loaded ${allUsers.length} users successfully`);

      // Update statistics
      updateUserStatistics();

      // Display users
      displayUsers();
    } else {
      showError("Failed to load users: " + data.message);
    }
  } catch (error) {
    console.error("Error loading users:", error);
    showError("Error loading users. Please check the console for details.");
  }
}

// Update user statistics
function updateUserStatistics() {
  const totalUsers = allUsers.length;
  const activeUsers = allUsers.filter((u) => u.is_active == 1).length;
  const vipUsers = allUsers.filter((u) => u.loyalty_level === "VIP").length;

  // Count new users this month
  const currentDate = new Date();
  const currentMonth = currentDate.getMonth();
  const currentYear = currentDate.getFullYear();
  const newUsers = allUsers.filter((user) => {
    const createdDate = new Date(user.created_at);
    return (
      createdDate.getMonth() === currentMonth &&
      createdDate.getFullYear() === currentYear
    );
  }).length;

  // Update UI
  document.getElementById("totalUsersCount").textContent = totalUsers;
  document.getElementById("activeUsersCount").textContent = activeUsers;
  document.getElementById("vipUsersCount").textContent = vipUsers;
  document.getElementById("newUsersCount").textContent = newUsers;
}

// Display users in the table
function displayUsers() {
  const tableBody = document.getElementById("usersTableBody");

  if (filteredUsers.length === 0) {
    tableBody.innerHTML = `
      <tr>
        <td colspan="10" style="text-align: center; padding: 2rem;">
          <i class="fas fa-users" style="font-size: 2rem; color: #999;"></i>
          <p style="margin-top: 1rem; color: #666;">No users found</p>
        </td>
      </tr>
    `;
    return;
  }

  // Calculate pagination
  const startIndex = (currentPage - 1) * usersPerPage;
  const endIndex = startIndex + usersPerPage;
  const usersToDisplay = filteredUsers.slice(startIndex, endIndex);

  // Generate table rows
  const rows = usersToDisplay
    .map((user) => {
      const statusClass = user.is_active == 1 ? "active" : "inactive";
      const statusText = user.is_active == 1 ? "Active" : "Inactive";
      const loyaltyClass = user.loyalty_level.toLowerCase();
      const loyaltyIcon = getLoyaltyIcon(user.loyalty_level);

      return `
      <tr data-user-id="${user.user_id}">
        <td><strong>#${user.user_id}</strong></td>
        <td>
          <strong>${escapeHtml(user.full_name)}</strong>
        </td>
        <td>${escapeHtml(user.username)}</td>
        <td>${escapeHtml(user.email)}</td>
        <td>${escapeHtml(user.phone_formatted)}</td>
        <td>
          <span class="status-badge ${statusClass}">${statusText}</span>
        </td>
        <td>
          <span class="loyalty-badge ${loyaltyClass}">
            ${loyaltyIcon} ${user.loyalty_level}
          </span>
        </td>
        <td>${user.member_since}</td>
        <td>${user.last_login_formatted}</td>
        <td>
          <div class="action-buttons">
            <button class="btn-action btn-view" onclick="viewUser(${
              user.user_id
            })" title="View Details">
              <i class="fas fa-eye"></i>
            </button>
            <button class="btn-action btn-edit" onclick="editUser(${
              user.user_id
            })" title="Edit User">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn-action btn-toggle" onclick="toggleUserStatus(${
              user.user_id
            }, ${user.is_active})" title="Toggle Status">
              <i class="fas fa-power-off"></i>
            </button>
            <button class="btn-action btn-delete" onclick="deleteUser(${
              user.user_id
            })" title="Delete User">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `;
    })
    .join("");

  tableBody.innerHTML = rows;

  // Update pagination
  updatePagination();
}

// Get loyalty level icon
function getLoyaltyIcon(level) {
  const icons = {
    Regular: '<i class="fas fa-user"></i>',
    Silver: '<i class="fas fa-medal"></i>',
    Gold: '<i class="fas fa-star"></i>',
    VIP: '<i class="fas fa-crown"></i>',
  };
  return icons[level] || '<i class="fas fa-user"></i>';
}

// Filter users based on search and filters
function filterUsers() {
  const searchTerm = document.getElementById("searchUsers").value.toLowerCase();
  const statusFilter = document.getElementById("statusFilter").value;
  const loyaltyFilter = document.getElementById("loyaltyFilter").value;

  filteredUsers = allUsers.filter((user) => {
    // Search filter
    const matchesSearch =
      user.full_name.toLowerCase().includes(searchTerm) ||
      user.username.toLowerCase().includes(searchTerm) ||
      user.email.toLowerCase().includes(searchTerm) ||
      user.phone_number.includes(searchTerm);

    // Status filter
    const matchesStatus =
      statusFilter === "all" ||
      (statusFilter === "active" && user.is_active == 1) ||
      (statusFilter === "inactive" && user.is_active == 0);

    // Loyalty filter
    const matchesLoyalty =
      loyaltyFilter === "all" || user.loyalty_level === loyaltyFilter;

    return matchesSearch && matchesStatus && matchesLoyalty;
  });

  // Reset to first page
  currentPage = 1;

  // Display filtered users
  displayUsers();
}

// Update pagination controls
function updatePagination() {
  const totalPages = Math.ceil(filteredUsers.length / usersPerPage);
  const pagination = document.getElementById("usersPagination");

  if (totalPages <= 1) {
    pagination.innerHTML = "";
    return;
  }

  let paginationHTML = `
    <button onclick="changePage(${currentPage - 1})" ${
    currentPage === 1 ? "disabled" : ""
  }>
      <i class="fas fa-chevron-left"></i> Previous
    </button>
  `;

  // Show page numbers
  for (let i = 1; i <= totalPages; i++) {
    if (
      i === 1 ||
      i === totalPages ||
      (i >= currentPage - 2 && i <= currentPage + 2)
    ) {
      paginationHTML += `
        <button 
          onclick="changePage(${i})" 
          class="${i === currentPage ? "active" : ""}">
          ${i}
        </button>
      `;
    } else if (i === currentPage - 3 || i === currentPage + 3) {
      paginationHTML += '<span style="padding: 0 0.5rem;">...</span>';
    }
  }

  paginationHTML += `
    <button onclick="changePage(${currentPage + 1})" ${
    currentPage === totalPages ? "disabled" : ""
  }>
      Next <i class="fas fa-chevron-right"></i>
    </button>
  `;

  pagination.innerHTML = paginationHTML;
}

// Change page
function changePage(page) {
  const totalPages = Math.ceil(filteredUsers.length / usersPerPage);

  if (page < 1 || page > totalPages) return;

  currentPage = page;
  displayUsers();

  // Scroll to top of table
  document
    .querySelector(".users-container")
    .scrollIntoView({ behavior: "smooth" });
}

// View user details
function viewUser(userId) {
  const user = allUsers.find((u) => u.user_id === userId);
  if (!user) return;

  const modalContent = `
    <div style="padding: 1rem;">
      <h3 style="color: #667eea; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-user-circle"></i> User Details
      </h3>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div>
          <strong>User ID:</strong><br>
          <span>#${user.user_id}</span>
        </div>
        <div>
          <strong>Status:</strong><br>
          <span class="status-badge ${
            user.is_active == 1 ? "active" : "inactive"
          }">
            ${user.is_active == 1 ? "Active" : "Inactive"}
          </span>
        </div>
        <div>
          <strong>Full Name:</strong><br>
          <span>${escapeHtml(user.full_name)}</span>
        </div>
        <div>
          <strong>Username:</strong><br>
          <span>${escapeHtml(user.username)}</span>
        </div>
        <div>
          <strong>Email:</strong><br>
          <span>${escapeHtml(user.email)}</span>
        </div>
        <div>
          <strong>Phone:</strong><br>
          <span>${escapeHtml(user.phone_number)}</span>
        </div>
        <div>
          <strong>Loyalty Level:</strong><br>
          <span class="loyalty-badge ${user.loyalty_level.toLowerCase()}">
            ${getLoyaltyIcon(user.loyalty_level)} ${user.loyalty_level}
          </span>
        </div>
        <div>
          <strong>Member Since:</strong><br>
          <span>${user.member_since}</span>
        </div>
        <div>
          <strong>Last Login:</strong><br>
          <span>${user.last_login_formatted}</span>
        </div>
        <div>
          <strong>Account Created:</strong><br>
          <span>${user.created_at_formatted}</span>
        </div>
        <div style="grid-column: 1 / -1;">
          <strong>Last Updated:</strong><br>
          <span>${user.updated_at_formatted}</span>
        </div>
      </div>
    </div>
  `;

  showModal("User Details", modalContent);
}

// Edit user
function editUser(userId) {
  const user = allUsers.find((u) => u.user_id === userId);
  if (!user) return;

  showNotification("User edit functionality will be implemented soon.", "info");
}

// Toggle user status
function toggleUserStatus(userId, currentStatus) {
  const newStatus = currentStatus == 1 ? 0 : 1;
  const statusText = newStatus == 1 ? "activate" : "deactivate";

  if (confirm(`Are you sure you want to ${statusText} this user?`)) {
    showNotification(
      `User status update functionality will be implemented soon.`,
      "info"
    );
    // TODO: Implement API call to update user status
  }
}

// Delete user
function deleteUser(userId) {
  const user = allUsers.find((u) => u.user_id === userId);
  if (!user) return;

  if (
    confirm(
      `Are you sure you want to delete user "${user.full_name}"? This action cannot be undone.`
    )
  ) {
    showNotification(
      "User deletion functionality will be implemented soon.",
      "warning"
    );
    // TODO: Implement API call to delete user
  }
}

// Show modal
function showModal(title, content) {
  // Create modal overlay
  const modal = document.createElement("div");
  modal.className = "user-modal";
  modal.innerHTML = `
    <div class="user-modal-overlay" onclick="closeUserModal()"></div>
    <div class="user-modal-content">
      <div class="user-modal-header">
        <h3>${title}</h3>
        <button onclick="closeUserModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666;">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="user-modal-body">
        ${content}
      </div>
    </div>
  `;

  // Add modal styles
  const style = document.createElement("style");
  style.textContent = `
    .user-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 9999;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .user-modal-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
    }
    .user-modal-content {
      position: relative;
      background: white;
      border-radius: 12px;
      max-width: 600px;
      width: 90%;
      max-height: 80vh;
      overflow-y: auto;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
      animation: modalSlideIn 0.3s ease;
    }
    .user-modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1.5rem;
      border-bottom: 1px solid #e0e0e0;
    }
    .user-modal-body {
      padding: 1.5rem;
    }
    @keyframes modalSlideIn {
      from {
        transform: translateY(-50px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }
  `;

  document.head.appendChild(style);
  document.body.appendChild(modal);
  document.body.style.overflow = "hidden";
}

// Close modal
function closeUserModal() {
  const modal = document.querySelector(".user-modal");
  if (modal) {
    modal.remove();
    document.body.style.overflow = "";
  }
}

// Show error message
function showError(message) {
  const tableBody = document.getElementById("usersTableBody");
  tableBody.innerHTML = `
    <tr>
      <td colspan="10" style="text-align: center; padding: 2rem;">
        <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #f44336;"></i>
        <p style="margin-top: 1rem; color: #f44336;">${message}</p>
        <button onclick="loadUsers()" style="
          margin-top: 1rem;
          padding: 0.75rem 1.5rem;
          background: #667eea;
          color: white;
          border: none;
          border-radius: 8px;
          cursor: pointer;
          font-weight: 600;
        ">
          <i class="fas fa-redo"></i> Retry
        </button>
      </td>
    </tr>
  `;
}

// Utility function to escape HTML
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

// Make functions globally available
window.loadUsers = loadUsers;
window.filterUsers = filterUsers;
window.changePage = changePage;
window.viewUser = viewUser;
window.editUser = editUser;
window.toggleUserStatus = toggleUserStatus;
window.deleteUser = deleteUser;
window.closeUserModal = closeUserModal;

console.log("✅ User Management System Loaded");
