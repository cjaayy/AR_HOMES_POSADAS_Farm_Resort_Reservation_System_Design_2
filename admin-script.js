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
