// ===== DASHBOARD JAVASCRIPT =====

// Test function to verify popup works
window.testPopup = function () {
  console.log("🧪 Testing popup functionality...");
  const popup = document.getElementById("popupContentArea");
  const content = document.getElementById("popupBody");
  const title = document.getElementById("popupTitle");

  if (!popup) {
    console.error("❌ Popup element not found!");
    return false;
  }

  if (!content) {
    console.error("❌ Popup body not found!");
    return false;
  }

  if (!title) {
    console.error("❌ Popup title not found!");
    return false;
  }

  console.log("✅ All popup elements found");

  // Set test content
  title.textContent = "TEST POPUP";
  content.innerHTML =
    '<h2 style="color: red;">THIS IS A TEST!</h2><p>If you can see this, the popup is working!</p>';

  // Show popup
  popup.classList.add("active");
  popup.style.display = "block";

  console.log("🎯 Popup should be visible now");

  return true;
};

// Simple popup function - MUST BE FIRST
function showPopup(section) {
  console.log("🔍 showPopup called with section:", section);

  const popup = document.getElementById("popupContentArea");
  const content = document.getElementById("popupBody");
  const title = document.getElementById("popupTitle");

  console.log("📋 Elements found:", {
    popup: !!popup,
    content: !!content,
    title: !!title,
  });

  if (!popup || !content || !title) {
    console.error("❌ Required popup elements not found");
    alert("Error: Popup elements not found. Check the console.");
    return;
  }

  // Clear existing content
  content.innerHTML = "";

  // Get content based on section
  let contentHTML = "";
  let titleText = "";

  console.log("🎯 Processing section:", section);

  // Simplified content loading - try to get content from templates
  const contentElement = document.getElementById(section + "-content");
  console.log("� Content element found:", !!contentElement);

  if (contentElement) {
    titleText = getTitleForSection(section);
    contentHTML = contentElement.innerHTML;
    console.log("✅ Got content from template");
  } else {
    titleText = "Content Not Available";
    contentHTML =
      '<div style="padding: 2rem; text-align: center;"><h3>Section: ' +
      section +
      "</h3><p>Content template not found, but popup is working!</p></div>";
    console.log("⚠️ Using fallback content");
  }

  console.log("✏️ Setting content:", titleText);

  // Set title and content
  title.textContent = titleText;
  content.innerHTML = contentHTML;

  // Show popup with animation
  popup.classList.add("active");
  popup.style.display = "block";

  // Update active button
  document
    .querySelectorAll(".nav-btn")
    .forEach((btn) => btn.classList.remove("active"));
  const targetBtn = document.querySelector(`[data-section="${section}"]`);
  if (targetBtn) {
    targetBtn.classList.add("active");
    console.log("🎯 Updated active button");
  }

  console.log("✅ Popup should be visible now");
}

function getTitleForSection(section) {
  const titles = {
    dashboard: "Dashboard Overview",
    "make-reservation": "Make New Reservation",
    "my-reservations": "My Reservations",
    promotions: "Special Promotions",
    profile: "Profile Settings",
  };
  return titles[section] || "Unknown Section";
}

// Close popup function
function closePopup() {
  console.log("closePopup called");
  const popup = document.getElementById("popupContentArea");
  if (popup) {
    popup.classList.remove("active");
    popup.style.display = "none";
  }
}

// Load user data from PHP session (passed via userData global variable from dashboard.html)
function loadUserData() {
  // Check if userData exists (set by dashboard.html after session check)
  if (
    typeof window.userData !== "undefined" &&
    window.userData &&
    window.userData.user_id
  ) {
    return {
      name: window.userData.full_name,
      email: window.userData.email,
      phone: window.userData.phone_number,
      totalReservations: 0, // Will be loaded from database
      pendingReservations: 0,
      approvedReservations: 0,
      completedStays: 0,
      memberSince: window.userData.memberSince || "2023",
      loyaltyLevel: window.userData.loyaltyLevel || "Regular",
    };
  }

  // If no session data, DON'T redirect here (dashboard.html handles auth check)
  console.warn(
    "⚠️ window.userData not yet loaded - waiting for session check..."
  );
  // Return default values and let dashboard.html handle authentication
  return {
    name: "Guest",
    email: "",
    phone: "",
    totalReservations: 0,
    pendingReservations: 0,
    approvedReservations: 0,
    completedStays: 0,
    memberSince: "2023",
    loyaltyLevel: "Regular",
  };
}

// Initialize user data (will be null until dashboard.html completes session check)
let userData = loadUserData();

// Function to refresh userData after session check completes
function refreshUserData() {
  const newData = loadUserData();
  if (newData) {
    userData = newData;
    console.log("✅ User data refreshed:", userData);
  }
}

// Sample reservations data
const reservationsData = [
  {
    id: 1,
    dates: "December 20-23, 2025",
    roomType: "VIP Suite",
    guests: 2,
    nights: 3,
    status: "pending",
  },
  {
    id: 2,
    dates: "January 15-19, 2026",
    roomType: "Deluxe Ocean View",
    guests: 4,
    nights: 4,
    status: "approved",
  },
  {
    id: 3,
    dates: "November 10-13, 2025",
    roomType: "Family Suite",
    guests: 3,
    nights: 3,
    status: "completed",
  },
  {
    id: 4,
    dates: "September 5-8, 2025",
    roomType: "Premium Room",
    guests: 2,
    nights: 3,
    status: "completed",
  },
];

// Sample activity data
const activityData = [
  {
    icon: "fas fa-crown",
    title: "VIP Status Renewed",
    description: "Your VIP membership has been automatically renewed for 2026.",
    date: "3 days ago",
  },
  {
    icon: "fas fa-calendar-check",
    title: "Reservation Confirmed",
    description:
      "Your VIP Suite booking for December 20-23, 2025 has been approved.",
    date: "5 days ago",
  },
  {
    icon: "fas fa-gift",
    title: "Exclusive Offer Available",
    description: "Special 25% discount on spa services - VIP member exclusive!",
    date: "1 week ago",
  },
  {
    icon: "fas fa-star",
    title: "Stay Completed",
    description:
      "Thank you for your recent stay! Your feedback helps us improve.",
    date: "2 weeks ago",
  },
  {
    icon: "fas fa-cocktail",
    title: "Complimentary Welcome Drink",
    description:
      "Enjoy a free welcome cocktail on your next arrival - VIP perk!",
    date: "3 weeks ago",
  },
];

// ===== DOM ELEMENTS =====
// Updated for new popup system - old sidebar and nav-links no longer exist
const mobileToggle = document.querySelector(".mobile-toggle");

// ===== INITIALIZATION =====
document.addEventListener("DOMContentLoaded", function () {
  console.log("🚀 Dashboard initializing...");

  initializeDashboard();
  updateUserData();
  setupEventListeners();
  setupFormValidation();

  // Show dashboard section by default
  const dashboardSection = document.getElementById("dashboard-section");
  if (dashboardSection) {
    dashboardSection.style.display = "block";
    dashboardSection.classList.add("active");
    console.log("✅ Dashboard section displayed");
  }

  // Hide all other sections
  document.querySelectorAll(".content-section").forEach((section) => {
    if (section.id !== "dashboard-section") {
      section.style.display = "none";
      section.classList.remove("active");
    }
  });
});

// ===== DASHBOARD INITIALIZATION =====
function initializeDashboard() {
  // The popup system will handle section management
  // Set minimum date for date inputs to today
  const today = new Date().toISOString().split("T")[0];
  const checkInDate = document.getElementById("checkIn");
  const checkOutDate = document.getElementById("checkOut");

  if (checkInDate) checkInDate.min = today;
  if (checkOutDate) checkOutDate.min = today;

  // Console welcome message
  console.log(
    "%c🏨 AR Homes Posadas Farm Resort Dashboard Loaded",
    "color: #667eea; font-size: 16px; font-weight: bold;"
  );
}

// ===== USER DATA MANAGEMENT =====
function updateUserData() {
  // Update user name displays
  const userNameElements = document.querySelectorAll(
    "#userName, #welcomeUserName"
  );
  userNameElements.forEach((element) => {
    if (element) element.textContent = userData.name;
  });

  // Update statistics
  updateStatistic("totalReservations", userData.totalReservations);
  updateStatistic("pendingReservations", userData.pendingReservations);
  updateStatistic("approvedReservations", userData.approvedReservations);
  updateStatistic("completedStays", userData.completedStays);

  // Update profile form
  updateProfileForm();

  // Show demo welcome message if it's a demo user
  checkAndShowDemoWelcome();
}

function checkAndShowDemoWelcome() {
  // Check if user is demo user based on email or if stored in localStorage
  const storedUser = localStorage.getItem("currentUser");
  if (storedUser) {
    try {
      const user = JSON.parse(storedUser);
      if (
        user.email === "demo@guest.com" &&
        !sessionStorage.getItem("demoWelcomeShown")
      ) {
        // Show demo welcome notification
        setTimeout(() => {
          showNotification(
            `🎉 Welcome to the Demo Dashboard, ${user.name}! You're logged in as a ${user.loyaltyLevel} member with ${user.totalReservations} total reservations. All data shown is sample data for demonstration purposes.`,
            "info",
            8000
          );
          sessionStorage.setItem("demoWelcomeShown", "true");
        }, 1500);
      }
    } catch (e) {
      console.log("Error parsing demo user data");
    }
  }
}

function updateStatistic(elementId, value) {
  const element = document.getElementById(elementId);
  if (element) {
    // Animate number counting
    animateCount(element, 0, value, 1000);
  }
}

function animateCount(element, start, end, duration) {
  const startTime = Date.now();
  const difference = end - start;

  function step() {
    const elapsed = Date.now() - startTime;
    const progress = Math.min(elapsed / duration, 1);
    const current = Math.floor(start + difference * progress);

    element.textContent = current;

    if (progress < 1) {
      requestAnimationFrame(step);
    }
  }

  requestAnimationFrame(step);
}

function updateProfileForm() {
  // Update profile form fields with user data
  const firstNameField = document.getElementById("firstName");
  const lastNameField = document.getElementById("lastName");
  const emailField = document.getElementById("email");
  const phoneField = document.getElementById("phone");

  if (firstNameField && lastNameField) {
    const nameParts = userData.name.split(" ");
    firstNameField.value = nameParts[0] || "";
    lastNameField.value = nameParts.slice(1).join(" ") || "";
  }

  if (emailField) emailField.value = userData.email;
  if (phoneField) phoneField.value = userData.phone;
}

// ===== NAVIGATION MANAGEMENT =====
function setupEventListeners() {
  console.log("🔧 Setting up event listeners...");

  // Set up navigation button click events
  const navButtons = document.querySelectorAll(".nav-btn");
  console.log("🔘 Found nav buttons:", navButtons.length);

  navButtons.forEach((button, index) => {
    console.log(
      `🎯 Setting up button ${index}:`,
      button.getAttribute("data-section")
    );
    button.addEventListener("click", (e) => {
      e.preventDefault();
      const section = button.getAttribute("data-section");
      console.log("🖱️ Button clicked!", section);

      if (section) {
        // Remove active class from all buttons
        navButtons.forEach((btn) => btn.classList.remove("active"));
        // Add active to clicked button
        button.classList.add("active");

        // Hide all content sections
        document.querySelectorAll(".content-section").forEach((sec) => {
          sec.classList.remove("active");
          sec.style.display = "none";
        });

        // Show selected section
        const targetSection = document.getElementById(section + "-section");
        if (targetSection) {
          targetSection.classList.add("active");
          targetSection.style.display = "block";
          console.log("✅ Showing section:", section);
        } else {
          console.error("❌ Section not found:", section + "-section");
        }
      }
    });
  });

  // Set up close popup button
  const closePopupBtn = document.getElementById("closePopup");
  console.log("❌ Close popup button found:", !!closePopupBtn);
  if (closePopupBtn) {
    closePopupBtn.addEventListener("click", closePopup);
  }

  // Mobile toggle (if it exists)
  if (mobileToggle) {
    mobileToggle.addEventListener("click", toggleSidebar);
  }

  // Handle window resize for responsive behavior
  window.addEventListener("resize", function () {
    // Handle any responsive behavior if needed
    if (window.innerWidth > 768) {
      // Reset any mobile-specific states
    }
  });

  // Check-in/Check-out date validation
  const checkInDate = document.getElementById("checkIn");
  const checkOutDate = document.getElementById("checkOut");

  if (checkInDate && checkOutDate) {
    checkInDate.addEventListener("change", function () {
      checkOutDate.min = this.value;
      if (checkOutDate.value && checkOutDate.value <= this.value) {
        checkOutDate.value = "";
      }
    });
  }

  // Promo code input - Enter key support
  const promoCodeInput = document.getElementById("promoCodeInput");
  if (promoCodeInput) {
    promoCodeInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        validatePromoCode();
      }
    });
  }

  // Add ESC key support to close popup
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      closePopup();
    }
  });

  console.log("✅ Event listeners setup complete");
}

function showSection(sectionId) {
  // Use the new popup system instead of the old section switching
  if (window.popupManager) {
    window.popupManager.showSection(sectionId);
  } else {
    // Fallback: try to initialize popup manager and then show section
    setTimeout(() => {
      if (window.popupManager) {
        window.popupManager.showSection(sectionId);
      }
    }, 100);
  }

  // Load data for specific sections
  if (sectionId === "bookings-history") {
    loadMyReservations();
  }

  if (sectionId === "notifications" || sectionId === "notifications-section") {
    loadUserNotifications();
  }
}

function updateActiveNavigation(activeSection) {
  // Updated for new navigation system
  // Remove active class from all nav buttons
  document.querySelectorAll(".nav-btn").forEach((btn) => {
    btn.classList.remove("active");
  });

  // Add active class to current nav button
  const activeBtn = document.querySelector(
    `.nav-btn[data-section="${activeSection}"]`
  );
  if (activeBtn) {
    activeBtn.classList.add("active");
  }
}

function toggleSidebar() {
  // No longer needed with new layout, but keeping for compatibility
  console.log("Sidebar toggle - using new popup system");
}

function closeSidebar() {
  // No longer needed with new layout, but keeping for compatibility
  console.log("Sidebar close - using new popup system");
}

// ===== FORM MANAGEMENT =====
function setupFormValidation() {
  // Reservation form
  const reservationForm = document.querySelector(".reservation-form");
  if (reservationForm) {
    reservationForm.addEventListener("submit", handleReservationSubmit);
  }

  // Profile form
  const profileForm = document.querySelector(".profile-form");
  if (profileForm) {
    profileForm.addEventListener("submit", handleProfileSubmit);
  }
}

// Setup form listeners for dynamically loaded content
function setupDynamicFormListeners() {
  // Setup any forms that were just loaded in the popup
  const popupArea = document.getElementById("popupContentArea");
  if (!popupArea) return;

  // Reservation form in popup
  const reservationForm = popupArea.querySelector(".reservation-form");
  if (reservationForm) {
    // Remove any existing listeners to avoid duplicates
    reservationForm.replaceWith(reservationForm.cloneNode(true));
    const newReservationForm = popupArea.querySelector(".reservation-form");
    newReservationForm.addEventListener("submit", handleReservationSubmit);

    // Setup date validation for reservation form
    const checkInDate = newReservationForm.querySelector("#checkIn");
    const checkOutDate = newReservationForm.querySelector("#checkOut");

    if (checkInDate && checkOutDate) {
      // Set minimum dates
      const today = new Date().toISOString().split("T")[0];
      checkInDate.min = today;
      checkOutDate.min = today;

      checkInDate.addEventListener("change", function () {
        checkOutDate.min = this.value;
        if (checkOutDate.value && checkOutDate.value <= this.value) {
          checkOutDate.value = "";
        }
      });
    }
  }

  // Profile form in popup
  const profileForm = popupArea.querySelector(".profile-form");
  if (profileForm) {
    // Remove any existing listeners to avoid duplicates
    profileForm.replaceWith(profileForm.cloneNode(true));
    const newProfileForm = popupArea.querySelector(".profile-form");
    newProfileForm.addEventListener("submit", handleProfileSubmit);
  }

  // Setup any buttons with onclick handlers
  const promoButtons = popupArea.querySelectorAll(".promo-btn");
  promoButtons.forEach((btn) => {
    btn.addEventListener("click", function () {
      showNotification("Promo code applied successfully!", "success");
    });
  });

  // Setup promo code input
  const promoCodeInput = popupArea.querySelector("#promoCodeInput");
  if (promoCodeInput) {
    promoCodeInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        validatePromoCode();
      }
    });
  }

  // Setup reservation action buttons
  const reservationButtons = popupArea.querySelectorAll(
    ".reservation-actions .btn"
  );
  reservationButtons.forEach((btn) => {
    btn.addEventListener("click", function () {
      const action = this.textContent.trim();
      showNotification(`${action} action triggered`, "info");
    });
  });
}

function handleReservationSubmit(e) {
  e.preventDefault();

  const formData = new FormData(e.target);
  const reservationData = {
    checkIn: formData.get("checkIn"),
    checkOut: formData.get("checkOut"),
    guests: formData.get("guests"),
    roomType: formData.get("roomType"),
    specialRequests: formData.get("specialRequests"),
  };

  // Validate form data
  if (
    !reservationData.checkIn ||
    !reservationData.checkOut ||
    !reservationData.guests ||
    !reservationData.roomType
  ) {
    showNotification("Please fill in all required fields.", "error");
    return;
  }

  // Check if check-out date is after check-in date
  if (new Date(reservationData.checkOut) <= new Date(reservationData.checkIn)) {
    showNotification("Check-out date must be after check-in date.", "error");
    return;
  }

  // Simulate form submission
  const submitBtn = e.target.querySelector(".submit-btn");
  submitBtn.classList.add("loading");
  submitBtn.textContent = "Submitting...";

  setTimeout(() => {
    submitBtn.classList.remove("loading");
    submitBtn.innerHTML =
      '<i class="fas fa-calendar-plus"></i> Submit Reservation';
    showNotification(
      "Reservation submitted successfully! We will review and confirm shortly.",
      "success"
    );
    e.target.reset();

    // Update statistics
    userData.totalReservations++;
    userData.pendingReservations++;
    updateStatistic("totalReservations", userData.totalReservations);
    updateStatistic("pendingReservations", userData.pendingReservations);
  }, 2000);
}

function handleProfileSubmit(e) {
  e.preventDefault();

  const formData = new FormData(e.target);
  const profileData = {
    firstName: formData.get("firstName"),
    lastName: formData.get("lastName"),
    email: formData.get("email"),
    phone: formData.get("phone"),
    address: formData.get("address"),
    preferences: formData.get("preferences"),
  };

  // Validate required fields
  if (
    !profileData.firstName ||
    !profileData.lastName ||
    !profileData.email ||
    !profileData.phone
  ) {
    showNotification("Please fill in all required fields.", "error");
    return;
  }

  // Simulate form submission
  const submitBtn = e.target.querySelector(".submit-btn");
  submitBtn.classList.add("loading");
  submitBtn.textContent = "Updating...";

  setTimeout(() => {
    submitBtn.classList.remove("loading");
    submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Profile';

    // Update user data
    userData.name = `${profileData.firstName} ${profileData.lastName}`;
    userData.email = profileData.email;
    userData.phone = profileData.phone;

    // Update UI
    updateUserData();
    showNotification("Profile updated successfully!", "success");
  }, 1500);
}

// ===== UTILITY FUNCTIONS =====
function showNotification(message, type = "info") {
  // Create notification element
  const notification = document.createElement("div");
  notification.className = `notification notification-${type}`;
  notification.innerHTML = `
    <div class="notification-content">
      <i class="fas ${getNotificationIcon(type)}"></i>
      <span>${message}</span>
    </div>
    <button class="notification-close" onclick="this.parentElement.remove()">
      <i class="fas fa-times"></i>
    </button>
  `;

  // Add styles
  notification.style.cssText = `
    position: fixed;
    top: 100px;
    right: 20px;
    background: ${getNotificationColor(type)};
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    min-width: 300px;
    max-width: 400px;
    animation: slideInRight 0.3s ease-out;
  `;

  // Add to DOM
  document.body.appendChild(notification);

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (notification.parentElement) {
      notification.style.animation = "slideOutRight 0.3s ease-in";
      setTimeout(() => notification.remove(), 300);
    }
  }, 5000);
}

function getNotificationIcon(type) {
  switch (type) {
    case "success":
      return "fa-check-circle";
    case "error":
      return "fa-exclamation-circle";
    case "warning":
      return "fa-exclamation-triangle";
    default:
      return "fa-info-circle";
  }
}

function getNotificationColor(type) {
  switch (type) {
    case "success":
      return "linear-gradient(135deg, #66bb6a, #43a047)";
    case "error":
      return "linear-gradient(135deg, #ff6b6b, #ee5a24)";
    case "warning":
      return "linear-gradient(135deg, #ffa726, #ff8a65)";
    default:
      return "linear-gradient(135deg, #667eea, #764ba2)";
  }
}

function logout() {
  if (confirm("Are you sure you want to logout?")) {
    showNotification("Logging out...", "info");
    setTimeout(() => {
      // Redirect to login page
      window.location.href = "index.html";
    }, 1000);
  }
}

// ===== RESERVATION MANAGEMENT =====
function viewReservationDetails(reservationId) {
  const reservation = reservationsData.find((r) => r.id === reservationId);
  if (reservation) {
    alert(
      `Reservation Details:\n\nDates: ${reservation.dates}\nRoom: ${
        reservation.roomType
      }\nGuests: ${
        reservation.guests
      }\nStatus: ${reservation.status.toUpperCase()}`
    );
  }
}

function cancelReservation(reservationId) {
  if (confirm("Are you sure you want to cancel this reservation?")) {
    showNotification("Reservation cancelled successfully.", "success");
    // In a real app, you would make an API call here
    userData.totalReservations--;
    userData.pendingReservations--;
    updateStatistic("totalReservations", userData.totalReservations);
    updateStatistic("pendingReservations", userData.pendingReservations);
  }
}

function modifyReservation(reservationId) {
  showNotification("Redirecting to modification form...", "info");
  showSection("make-reservation");
  updateActiveNavigation("make-reservation");
}

function reviewStay(reservationId) {
  showNotification("Review form will be available soon!", "info");
}

// ===== GLOBAL FUNCTIONS FOR ONCLICK HANDLERS =====
window.logout = logout;
window.showSection = showSection;
window.toggleSidebar = toggleSidebar;
window.viewReservationDetails = viewReservationDetails;
window.cancelReservation = cancelReservation;
window.modifyReservation = modifyReservation;
window.reviewStay = reviewStay;

// ===== ADD NOTIFICATION ANIMATIONS TO CSS =====
const notificationStyles = document.createElement("style");
notificationStyles.textContent = `
  @keyframes slideInRight {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
  
  @keyframes slideOutRight {
    from {
      transform: translateX(0);
      opacity: 1;
    }
    to {
      transform: translateX(100%);
      opacity: 0;
    }
  }
  
  .notification-content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .notification-close {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: 0;
    font-size: 0.9rem;
    opacity: 0.8;
    transition: opacity 0.3s ease;
  }
  
  .notification-close:hover {
    opacity: 1;
  }
`;
document.head.appendChild(notificationStyles);

// ===== PERFORMANCE MONITORING =====
console.log(
  "%c✅ Dashboard JavaScript Loaded Successfully",
  "color: #66bb6a; font-weight: bold;"
);

// Log performance metrics
window.addEventListener("load", function () {
  setTimeout(() => {
    const perfData = performance.getEntriesByType("navigation")[0];
    console.log(
      `%c⚡ Page Load Time: ${Math.round(
        perfData.loadEventEnd - perfData.loadEventStart
      )}ms`,
      "color: #667eea;"
    );
  }, 100);
});

// ===== ERROR HANDLING =====
window.addEventListener("error", function (e) {
  console.error("Dashboard Error:", e.error);
  showNotification("An error occurred. Please refresh the page.", "error");
});

// ===== KEYBOARD SHORTCUTS =====
document.addEventListener("keydown", function (e) {
  // Alt + number keys for quick navigation
  if (e.altKey && !e.ctrlKey && !e.shiftKey) {
    switch (e.key) {
      case "1":
        e.preventDefault();
        showSection("dashboard");
        updateActiveNavigation("dashboard");
        break;
      case "2":
        e.preventDefault();
        showSection("make-reservation");
        updateActiveNavigation("make-reservation");
        break;
      case "3":
        e.preventDefault();
        showSection("my-reservations");
        updateActiveNavigation("my-reservations");
        break;
      case "4":
        e.preventDefault();
        showSection("promotions");
        updateActiveNavigation("promotions");
        break;
      case "5":
        e.preventDefault();
        showSection("profile");
        updateActiveNavigation("profile");
        break;
    }
  }

  // ESC to close sidebar on mobile
  if (e.key === "Escape" && window.innerWidth <= 768) {
    closeSidebar();
  }
});

// Show keyboard shortcuts info in console
console.log(
  "%c🔤 Keyboard Shortcuts Available:",
  "color: #764ba2; font-weight: bold;"
);
console.log("Alt + 1: Dashboard");
console.log("Alt + 2: Make Reservation");
console.log("Alt + 3: My Reservations");
console.log("Alt + 4: Promotions");
console.log("Alt + 5: Profile");
console.log("ESC: Close sidebar (mobile)");

// ===== PROMOTIONS DATA =====
const promoData = {
  isRegularCustomer: true,
  loyaltyLevel: "Gold",
  availablePromos: [
    {
      code: "EARLYBIRD25",
      title: "Early Bird Special",
      discount: 25,
      description: "Book 30 days in advance and save big!",
      validUntil: "Dec 31, 2025",
      active: true,
    },
    {
      code: "WEEKEND20",
      title: "Weekend Escape",
      discount: 20,
      description: "Perfect for weekend warriors!",
      validUntil: "Ongoing",
      active: true,
    },
    {
      code: "FAMILY30",
      title: "Family Package",
      discount: 30,
      description: "Bring the whole family!",
      validUntil: "Dec 31, 2025",
      active: true,
    },
    {
      code: "VIPLOYALTY35",
      title: "VIP Loyalty Bonus",
      discount: 35,
      description: "Exclusive for regular customers!",
      validUntil: "Limited time",
      active: true,
      vipOnly: true,
    },
    {
      code: "HOLIDAY40",
      title: "Holiday Season",
      discount: 40,
      description: "Celebrate the holidays with us!",
      validUntil: "Jan 15, 2026",
      active: true,
    },
    {
      code: "ROMANCE45",
      title: "Romantic Getaway",
      discount: 45,
      description: "Perfect for couples!",
      validUntil: "Feb 14, 2026",
      active: true,
    },
  ],
  appliedPromos: [],
};

// ===== PROMOTIONS MANAGEMENT =====
function applyPromo(promoCode) {
  const promo = promoData.availablePromos.find((p) => p.code === promoCode);

  if (!promo) {
    showNotification("Invalid promo code!", "error");
    return;
  }

  if (!promo.active) {
    showNotification("This promotion is no longer active.", "error");
    return;
  }

  if (promo.vipOnly && !promoData.isRegularCustomer) {
    showNotification(
      "This promotion is only available for VIP members.",
      "error"
    );
    return;
  }

  // Check if promo is already applied
  if (promoData.appliedPromos.some((p) => p.code === promoCode)) {
    showNotification("This promotion is already applied!", "warning");
    return;
  }

  // Apply the promo
  promoData.appliedPromos.push(promo);
  showNotification(
    `🎉 ${promo.title} applied! You saved ${promo.discount}%`,
    "success"
  );

  // Update UI to show applied promo
  updateAppliedPromos();

  // Redirect to make reservation page with promo pre-applied
  setTimeout(() => {
    showSection("make-reservation");
    updateActiveNavigation("make-reservation");

    // Add promo info to the form (you could enhance this further)
    const form = document.querySelector(".reservation-form");
    if (form) {
      let promoInfo = form.querySelector(".applied-promo-info");
      if (!promoInfo) {
        promoInfo = document.createElement("div");
        promoInfo.className = "applied-promo-info";
        promoInfo.style.cssText = `
          background: linear-gradient(135deg, #66bb6a, #43a047);
          color: white;
          padding: 1rem;
          border-radius: 8px;
          margin-bottom: 1rem;
          display: flex;
          align-items: center;
          gap: 0.5rem;
        `;
        form.insertBefore(promoInfo, form.firstChild);
      }
      promoInfo.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>Promo Applied: ${promo.title} (${promo.discount}% OFF)</span>
        <button onclick="removeAppliedPromo('${promo.code}')" style="
          background: none;
          border: none;
          color: white;
          margin-left: auto;
          cursor: pointer;
          font-size: 1rem;
        ">
          <i class="fas fa-times"></i>
        </button>
      `;
    }
  }, 1000);
}

function validatePromoCode() {
  const input = document.getElementById("promoCodeInput");
  const code = input.value.trim().toUpperCase();

  if (!code) {
    showNotification("Please enter a promo code.", "warning");
    return;
  }

  const promo = promoData.availablePromos.find((p) => p.code === code);

  if (promo) {
    input.value = "";
    applyPromo(code);
  } else {
    showNotification(
      "Invalid promo code. Please check and try again.",
      "error"
    );
    input.value = "";
  }
}

function removeAppliedPromo(promoCode) {
  const index = promoData.appliedPromos.findIndex((p) => p.code === promoCode);
  if (index > -1) {
    const promo = promoData.appliedPromos[index];
    promoData.appliedPromos.splice(index, 1);
    showNotification(`${promo.title} removed.`, "info");

    // Remove from UI
    const promoInfo = document.querySelector(".applied-promo-info");
    if (promoInfo) {
      promoInfo.remove();
    }

    updateAppliedPromos();
  }
}

function updateAppliedPromos() {
  // This function could update a display of currently applied promos
  // For now, we'll just log it
  console.log("Applied Promos:", promoData.appliedPromos);
}

function initializePromotions() {
  // Check if user is a regular customer and update loyalty status
  if (promoData.isRegularCustomer) {
    const loyaltyBadge = document.querySelector(".loyalty-badge");
    if (loyaltyBadge) {
      // Remove moving animation - keep badge static
      // loyaltyBadge.style.animation = "shimmer 3s linear infinite";
    }

    // Show VIP-only promotions
    const vipPromos = document.querySelectorAll(".promo-card.exclusive");
    vipPromos.forEach((card) => {
      card.style.display = "block";
    });
  } else {
    // Hide VIP-only promotions for non-regular customers
    const vipPromos = document.querySelectorAll(".promo-card.exclusive");
    vipPromos.forEach((card) => {
      card.style.display = "none";
    });

    // Hide loyalty badge
    const loyaltyStatus = document.getElementById("loyaltyStatus");
    if (loyaltyStatus) {
      loyaltyStatus.style.display = "none";
    }
  }
}

// ===== GLOBAL FUNCTIONS FOR PROMO MANAGEMENT =====
window.applyPromo = applyPromo;
window.validatePromoCode = validatePromoCode;
window.removeAppliedPromo = removeAppliedPromo;

// ===== UPDATE INITIALIZATION =====
document.addEventListener("DOMContentLoaded", function () {
  initializeDashboard();
  updateUserData();
  setupEventListeners();
  setupFormValidation();
  initializePromotions(); // Add this line
});

console.log(
  "%c🎯 Promotions System Loaded",
  "color: #ffd700; font-weight: bold;"
);

// ===== RESORT GALLERY FUNCTIONALITY =====
class ResortGallery {
  constructor() {
    this.currentSlide = 0;
    this.slides = [];
    this.autoPlayInterval = null;
    this.touchStartX = 0;
    this.touchEndX = 0;
    this.isTransitioning = false;
    this.isDragging = false;

    this.init();
  }

  init() {
    console.log("🔍 Looking for gallery elements...");

    this.galleryWrapper = document.getElementById("galleryWrapper");
    this.prevBtn = document.getElementById("prevBtn");
    this.nextBtn = document.getElementById("nextBtn");
    this.indicators = document.getElementById("galleryIndicators");

    console.log("Gallery elements found:", {
      wrapper: !!this.galleryWrapper,
      prevBtn: !!this.prevBtn,
      nextBtn: !!this.nextBtn,
      indicators: !!this.indicators,
    });

    if (!this.galleryWrapper) {
      console.error("❌ Gallery wrapper not found");
      return;
    }

    this.slides = this.galleryWrapper.querySelectorAll(".gallery-slide");
    this.totalSlides = this.slides.length;

    console.log("📸 Found", this.totalSlides, "slides");

    if (this.totalSlides === 0) {
      console.error("❌ No gallery slides found");
      return;
    }

    // Force display first slide immediately
    this.showSlide(0);
    this.setupEventListeners();
    this.startAutoPlay();

    console.log(
      "%c✅ Resort Gallery Initialized with " + this.totalSlides + " slides",
      "color: #4CAF50; font-weight: bold;"
    );
  }

  showSlide(index) {
    if (!this.galleryWrapper || this.totalSlides === 0) {
      console.error("❌ Cannot show slide - wrapper or slides missing");
      return;
    }

    // Debug dimensions
    const wrapperWidth = this.galleryWrapper.offsetWidth;
    const wrapperScrollWidth = this.galleryWrapper.scrollWidth;
    console.log(
      `📏 Wrapper width: ${wrapperWidth}px, scroll width: ${wrapperScrollWidth}px`
    );
    console.log(
      `📏 Expected scroll width: ${wrapperWidth * this.totalSlides}px`
    );

    this.currentSlide = index;
    const translateX = -this.currentSlide * 100;

    console.log(`📍 Attempting to show slide ${index + 1}/${this.totalSlides}`);
    console.log(`   Transform value: translateX(${translateX}%)`);

    // Remove any inline transition first to prevent conflicts
    this.galleryWrapper.style.transition = "none";

    // Force browser reflow
    void this.galleryWrapper.offsetWidth;

    // Re-enable transition
    this.galleryWrapper.style.transition =
      "transform 0.6s cubic-bezier(0.4, 0, 0.2, 1)";

    // Apply transform
    this.galleryWrapper.style.transform = `translateX(${translateX}%)`;

    // Verify transform was applied
    setTimeout(() => {
      const actualTransform = window.getComputedStyle(
        this.galleryWrapper
      ).transform;
      const wrapperRect = this.galleryWrapper.getBoundingClientRect();
      console.log(
        `   ✅ Applied transform:`,
        this.galleryWrapper.style.transform
      );
      console.log(`   📊 Computed transform:`, actualTransform);
      console.log(`   📐 Wrapper position: left=${wrapperRect.left}px`);

      // Check each slide's visibility
      const slides = this.galleryWrapper.querySelectorAll(".gallery-slide");
      slides.forEach((slide, i) => {
        const slideRect = slide.getBoundingClientRect();
        const isVisible = slideRect.left >= 0 && slideRect.left < wrapperWidth;
        console.log(
          `   Slide ${i}: left=${slideRect.left.toFixed(0)}px ${
            isVisible ? "✅ VISIBLE" : "❌ hidden"
          }`
        );
      });

      // Check if transform actually moved
      if (
        actualTransform === "none" ||
        actualTransform === "matrix(1, 0, 0, 1, 0, 0)"
      ) {
        console.error("⚠️ Transform not applied! Checking for conflicts...");
        console.log("Wrapper computed styles:", {
          display: window.getComputedStyle(this.galleryWrapper).display,
          position: window.getComputedStyle(this.galleryWrapper).position,
          overflow: window.getComputedStyle(this.galleryWrapper).overflow,
          width: window.getComputedStyle(this.galleryWrapper).width,
        });
      }
    }, 100);

    this.updateIndicators();
  }

  setupEventListeners() {
    console.log("🎛️ Setting up gallery controls...");

    // Button controls
    if (this.prevBtn) {
      console.log("Previous button element:", this.prevBtn);
      console.log("Previous button computed style:", {
        zIndex: window.getComputedStyle(this.prevBtn).zIndex,
        pointerEvents: window.getComputedStyle(this.prevBtn).pointerEvents,
        display: window.getComputedStyle(this.prevBtn).display,
      });

      this.prevBtn.addEventListener(
        "click",
        (e) => {
          console.log("⬅️ Previous button clicked!", e);
          e.preventDefault();
          e.stopPropagation();
          this.prevSlide();
        },
        true
      );

      // Also add mousedown for testing
      this.prevBtn.addEventListener("mousedown", () => {
        console.log("🖱️ Previous button mousedown detected");
      });

      console.log("✅ Previous button ready");
    } else {
      console.error("❌ Previous button not found!");
    }

    if (this.nextBtn) {
      console.log("Next button element:", this.nextBtn);
      console.log("Next button computed style:", {
        zIndex: window.getComputedStyle(this.nextBtn).zIndex,
        pointerEvents: window.getComputedStyle(this.nextBtn).pointerEvents,
        display: window.getComputedStyle(this.nextBtn).display,
      });

      this.nextBtn.addEventListener(
        "click",
        (e) => {
          console.log("➡️ Next button clicked!", e);
          e.preventDefault();
          e.stopPropagation();
          this.nextSlide();
        },
        true
      );

      // Also add mousedown for testing
      this.nextBtn.addEventListener("mousedown", () => {
        console.log("🖱️ Next button mousedown detected");
      });

      console.log("✅ Next button ready");
    } else {
      console.error("❌ Next button not found!");
    }

    // Indicator controls
    if (this.indicators) {
      this.indicators.addEventListener("click", (e) => {
        if (e.target.classList.contains("indicator")) {
          const slideIndex = parseInt(e.target.dataset.slide);
          console.log("🎯 Indicator clicked:", slideIndex);
          this.goToSlide(slideIndex);
        }
      });
      console.log("✅ Indicators ready");
    }

    // Touch/swipe events
    if (this.galleryWrapper) {
      this.galleryWrapper.addEventListener(
        "touchstart",
        (e) => this.handleTouchStart(e),
        { passive: true }
      );
      this.galleryWrapper.addEventListener(
        "touchmove",
        (e) => this.handleTouchMove(e),
        { passive: true }
      );
      this.galleryWrapper.addEventListener(
        "touchend",
        (e) => this.handleTouchEnd(e),
        { passive: true }
      );

      // Mouse events for desktop dragging
      this.galleryWrapper.addEventListener("mousedown", (e) =>
        this.handleMouseDown(e)
      );
      this.galleryWrapper.addEventListener("mousemove", (e) =>
        this.handleMouseMove(e)
      );
      this.galleryWrapper.addEventListener("mouseup", (e) =>
        this.handleMouseUp(e)
      );
      this.galleryWrapper.addEventListener("mouseleave", (e) =>
        this.handleMouseUp(e)
      );

      // Prevent drag on images
      this.galleryWrapper.addEventListener("dragstart", (e) =>
        e.preventDefault()
      );
    }

    // Pause auto-play on hover
    const galleryContainer = document.querySelector(".gallery-container");
    if (galleryContainer) {
      galleryContainer.addEventListener("mouseenter", () => {
        console.log("🖱️ Mouse entered gallery - pausing auto-play");
        this.pauseAutoPlay();
      });
      galleryContainer.addEventListener("mouseleave", () => {
        console.log("🖱️ Mouse left gallery - resuming auto-play");
        this.startAutoPlay();
      });
      console.log("✅ Hover pause/resume ready");
    }

    // Keyboard navigation
    document.addEventListener("keydown", (e) => {
      if (e.key === "ArrowLeft") {
        console.log("⌨️ Left arrow pressed");
        this.prevSlide();
      }
      if (e.key === "ArrowRight") {
        console.log("⌨️ Right arrow pressed");
        this.nextSlide();
      }
    });
    console.log("✅ Keyboard navigation ready");

    // Debug: Log all setup complete
    console.log("🎉 All event listeners setup complete!");
  }

  handleTouchStart(e) {
    this.touchStartX = e.touches[0].clientX;
    this.pauseAutoPlay();
  }

  handleTouchMove(e) {
    this.touchEndX = e.touches[0].clientX;
  }

  handleTouchEnd(e) {
    const swipeThreshold = 50;
    const swipeDistance = this.touchStartX - this.touchEndX;

    if (Math.abs(swipeDistance) > swipeThreshold) {
      if (swipeDistance > 0) {
        this.nextSlide();
      } else {
        this.prevSlide();
      }
    }

    this.startAutoPlay();
  }

  handleMouseDown(e) {
    this.isDragging = true;
    this.touchStartX = e.clientX;
    this.pauseAutoPlay();
    this.galleryWrapper.style.cursor = "grabbing";
  }

  handleMouseMove(e) {
    if (!this.isDragging) return;
    this.touchEndX = e.clientX;
  }

  handleMouseUp(e) {
    if (!this.isDragging) return;

    this.isDragging = false;
    this.galleryWrapper.style.cursor = "grab";

    const swipeThreshold = 50;
    const swipeDistance = this.touchStartX - this.touchEndX;

    if (Math.abs(swipeDistance) > swipeThreshold) {
      if (swipeDistance > 0) {
        this.nextSlide();
      } else {
        this.prevSlide();
      }
    }

    this.startAutoPlay();
  }

  goToSlide(index) {
    if (!this.galleryWrapper) {
      console.error("❌ Gallery wrapper not found in goToSlide");
      return;
    }

    // Prevent going to the same slide
    if (index === this.currentSlide) return;

    console.log(`🔄 Going to slide ${index + 1}/${this.totalSlides}`);
    this.showSlide(index);
  }

  nextSlide() {
    const nextIndex = (this.currentSlide + 1) % this.totalSlides;
    console.log("➡️ Next slide:", nextIndex);
    this.goToSlide(nextIndex);
  }

  prevSlide() {
    const prevIndex =
      (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
    console.log("⬅️ Previous slide:", prevIndex);
    this.goToSlide(prevIndex);
  }

  updateIndicators() {
    if (!this.indicators) return;

    const indicators = this.indicators.querySelectorAll(".indicator");
    indicators.forEach((indicator, index) => {
      indicator.classList.toggle("active", index === this.currentSlide);
    });
  }

  startAutoPlay() {
    console.log("▶️ Starting auto-play (5 seconds per slide)");
    this.pauseAutoPlay();
    this.autoPlayInterval = setInterval(() => {
      console.log("⏰ Auto-advancing to next slide");
      this.nextSlide();
    }, 5000);
  }

  pauseAutoPlay() {
    if (this.autoPlayInterval) {
      console.log("⏸️ Pausing auto-play");
      clearInterval(this.autoPlayInterval);
      this.autoPlayInterval = null;
    }
  }

  destroy() {
    console.log("🛑 Destroying gallery");
    this.pauseAutoPlay();
    // Remove event listeners if needed
  }
}

// Backup initialization - try again after page fully loads
window.addEventListener("load", () => {
  if (!window.resortGallery) {
    console.log("🔄 Gallery not initialized, trying backup initialization...");
    setTimeout(initGallery, 100);
  }
});

// Manual test function - call testGallery() in console to test
window.testGallery = function () {
  const wrapper = document.getElementById("galleryWrapper");
  const slides = document.querySelectorAll(".gallery-slide");

  console.log("=== GALLERY TEST ===");
  console.log("Wrapper found:", !!wrapper);
  console.log("Number of slides:", slides.length);
  console.log("Current transform:", wrapper ? wrapper.style.transform : "N/A");

  if (wrapper && slides.length > 0) {
    console.log("Testing slide to position 1...");
    wrapper.style.transition = "transform 0.6s ease";
    wrapper.style.transform = "translateX(-100%)";
    console.log("✅ Transform applied: translateX(-100%)");

    setTimeout(() => {
      console.log("Testing slide to position 2...");
      wrapper.style.transform = "translateX(-200%)";
      console.log("✅ Transform applied: translateX(-200%)");

      setTimeout(() => {
        console.log("Testing back to position 0...");
        wrapper.style.transform = "translateX(0%)";
        console.log("✅ Transform applied: translateX(0%)");
      }, 2000);
    }, 2000);
  } else {
    console.error("❌ Gallery elements not found!");
  }
};

// Initialize gallery when DOM is loaded
function initGallery() {
  console.log(
    "%c🖼️ Initializing Gallery...",
    "color: #2196F3; font-weight: bold;"
  );

  // Check if gallery wrapper exists
  const wrapper = document.getElementById("galleryWrapper");
  if (!wrapper) {
    console.error("❌ Gallery wrapper not found! Retrying in 500ms...");
    setTimeout(initGallery, 500);
    return;
  }

  console.log("✅ Gallery wrapper found, creating ResortGallery instance");
  window.resortGallery = new ResortGallery();
}

// Gallery debug function - available immediately
window.testGalleryButtons = function () {
  console.log("\n🧪 Testing gallery button accessibility...");

  const prevBtn = document.getElementById("prevBtn");
  const nextBtn = document.getElementById("nextBtn");

  if (!prevBtn || !nextBtn) {
    console.error("❌ Buttons not found!");
    console.log("Looking for prevBtn:", prevBtn);
    console.log("Looking for nextBtn:", nextBtn);
    return;
  }

  console.log("✅ Buttons found");
  console.log("Previous button:", prevBtn);
  console.log("Next button:", nextBtn);

  // Check if buttons are visible and clickable
  const prevRect = prevBtn.getBoundingClientRect();
  const nextRect = nextBtn.getBoundingClientRect();

  console.log("Previous button position:", prevRect);
  console.log("Next button position:", nextRect);

  // Check z-index and pointer events
  const prevStyle = window.getComputedStyle(prevBtn);
  const nextStyle = window.getComputedStyle(nextBtn);

  console.log("Previous button styles:", {
    zIndex: prevStyle.zIndex,
    pointerEvents: prevStyle.pointerEvents,
    display: prevStyle.display,
    visibility: prevStyle.visibility,
    opacity: prevStyle.opacity,
  });

  console.log("Next button styles:", {
    zIndex: nextStyle.zIndex,
    pointerEvents: nextStyle.pointerEvents,
    display: nextStyle.display,
    visibility: nextStyle.visibility,
    opacity: nextStyle.opacity,
  });

  // Check what element is on top
  const nextCenter = {
    x: nextRect.left + nextRect.width / 2,
    y: nextRect.top + nextRect.height / 2,
  };
  const elementAtPoint = document.elementFromPoint(nextCenter.x, nextCenter.y);
  console.log("Element at next button center:", elementAtPoint);
  console.log(
    "Is it the button or its child?",
    elementAtPoint === nextBtn || nextBtn.contains(elementAtPoint)
  );

  // Try to click programmatically
  console.log("🖱️ Attempting programmatic click on next button...");
  nextBtn.click();
};

console.log("✅ testGalleryButtons function registered");

// Initialize immediately or wait for DOM
if (document.readyState === "loading") {
  console.log("⏳ Waiting for DOM to load...");
  document.addEventListener("DOMContentLoaded", () => {
    setTimeout(initGallery, 300);
  });
} else {
  console.log("✅ DOM already loaded");
  setTimeout(initGallery, 300);
}

// ===== POPUP CONTENT SYSTEM =====
class PopupContentManager {
  constructor() {
    this.popupArea = null;
    this.popupTitle = null;
    this.popupBody = null;
    this.closeBtn = null;
    this.navButtons = [];
    this.currentSection = null;

    this.init();
  }

  init() {
    console.log("🔧 Initializing PopupContentManager...");
    this.popupArea = document.getElementById("popupContentArea");
    this.popupTitle = document.getElementById("popupTitle");
    this.popupBody = document.getElementById("popupBody");
    this.closeBtn = document.getElementById("closePopup");
    this.navButtons = document.querySelectorAll(".nav-btn");

    console.log("📋 Elements found:", {
      popupArea: !!this.popupArea,
      popupTitle: !!this.popupTitle,
      popupBody: !!this.popupBody,
      closeBtn: !!this.closeBtn,
      navButtons: this.navButtons.length,
    });

    if (!this.popupArea) {
      console.error("❌ Popup area not found!");
      return;
    }

    this.setupEventListeners();
    this.loadSectionContent("dashboard"); // Load default content

    console.log(
      "%c🚀 Popup Content System Initialized",
      "color: #4CAF50; font-weight: bold;"
    );
  }

  setupEventListeners() {
    console.log("🎯 Setting up event listeners...");

    // Navigation button clicks
    console.log(`Found ${this.navButtons.length} navigation buttons`);
    this.navButtons.forEach((btn, index) => {
      console.log(
        `Setting up listener for button ${index}:`,
        btn.dataset.section
      );
      btn.addEventListener("click", (e) => {
        console.log("🖱️ Navigation button clicked:", btn.dataset.section);
        const section = btn.dataset.section;
        this.showSection(section);
        this.setActiveNavButton(btn);
      });
    });

    // Close button
    if (this.closeBtn) {
      console.log("✅ Setting up close button listener");
      this.closeBtn.addEventListener("click", () => {
        console.log("🖱️ Close button clicked");
        this.hidePopup();
      });
    } else {
      console.log("❌ Close button not found");
    }

    // Close on overlay click (outside popup content)
    document.addEventListener("click", (e) => {
      if (e.target === this.popupArea) {
        console.log("🖱️ Overlay clicked, hiding popup");
        this.hidePopup();
      }
    });

    // Close on Escape key
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && this.popupArea.classList.contains("active")) {
        console.log("⌨️ Escape key pressed, hiding popup");
        this.hidePopup();
      }
    });

    console.log("✅ Event listeners setup complete");
  }

  showSection(sectionName) {
    console.log("📂 Showing section:", sectionName);
    this.loadSectionContent(sectionName);
    this.showPopup();
    this.currentSection = sectionName;
    console.log("✅ Section shown successfully");
  }

  loadSectionContent(sectionName) {
    const contentElement = document.getElementById(`${sectionName}-content`);
    const titles = {
      dashboard: "Dashboard Overview",
      "make-reservation": "Make a Reservation",
      "my-reservations": "My Reservations",
      promotions: "Exclusive Promotions",
      profile: "Profile Settings",
    };

    if (this.popupTitle) {
      this.popupTitle.textContent = titles[sectionName] || "AR Homes Resort";
    }

    if (this.popupBody && contentElement) {
      this.popupBody.innerHTML = contentElement.innerHTML;

      // Re-initialize any interactive elements if needed
      this.reinitializeContent(sectionName);
    }
  }

  reinitializeContent(sectionName) {
    // Reinitialize form handlers, etc. based on section
    switch (sectionName) {
      case "make-reservation":
        this.initReservationForm();
        break;
      case "profile":
        this.initProfileForm();
        break;
      case "dashboard":
        this.updateDashboardStats();
        break;
    }
  }

  initReservationForm() {
    const form = this.popupBody.querySelector(".reservation-form");
    if (form) {
      form.addEventListener("submit", (e) => {
        e.preventDefault();
        this.handleReservationSubmit(new FormData(form));
      });
    }
  }

  initProfileForm() {
    const form = this.popupBody.querySelector(".profile-form");
    if (form) {
      form.addEventListener("submit", (e) => {
        e.preventDefault();
        this.handleProfileUpdate(new FormData(form));
      });
    }
  }

  updateDashboardStats() {
    // Update stats with current user data
    const userData = loadUserData();

    const statsElements = {
      totalReservations: this.popupBody.querySelector("#totalReservations"),
      pendingReservations: this.popupBody.querySelector("#pendingReservations"),
      approvedReservations: this.popupBody.querySelector(
        "#approvedReservations"
      ),
      completedStays: this.popupBody.querySelector("#completedStays"),
    };

    if (statsElements.totalReservations) {
      statsElements.totalReservations.textContent = userData.totalReservations;
    }
    if (statsElements.pendingReservations) {
      statsElements.pendingReservations.textContent =
        userData.pendingReservations;
    }
    if (statsElements.approvedReservations) {
      statsElements.approvedReservations.textContent =
        userData.approvedReservations;
    }
    if (statsElements.completedStays) {
      statsElements.completedStays.textContent = userData.completedStays;
    }
  }

  handleReservationSubmit(formData) {
    // Handle reservation form submission
    console.log("Reservation submitted:", Object.fromEntries(formData));

    // Show success message
    this.showSuccessMessage("Reservation submitted successfully!");

    // Optionally close popup after success
    setTimeout(() => {
      this.hidePopup();
    }, 2000);
  }

  handleProfileUpdate(formData) {
    // Handle profile update
    console.log("Profile updated:", Object.fromEntries(formData));

    // Update user name in header
    const userName = formData.get("firstName") + " " + formData.get("lastName");
    const userNameElements = document.querySelectorAll(
      "#userName, #welcomeUserName"
    );
    userNameElements.forEach((el) => {
      if (el) el.textContent = userName;
    });

    this.showSuccessMessage("Profile updated successfully!");
  }

  showSuccessMessage(message) {
    // Create and show success notification
    const notification = document.createElement("div");
    notification.className = "success-notification";
    notification.innerHTML = `
      <i class="fas fa-check-circle"></i>
      <span>${message}</span>
    `;

    // Add notification styles
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      background: linear-gradient(135deg, #4CAF50, #45a049);
      color: white;
      padding: 1rem 1.5rem;
      border-radius: 8px;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      z-index: 9999;
      animation: slideInRight 0.3s ease;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    `;

    document.body.appendChild(notification);

    // Remove after 3 seconds
    setTimeout(() => {
      notification.style.animation = "slideOutRight 0.3s ease";
      setTimeout(() => {
        document.body.removeChild(notification);
      }, 300);
    }, 3000);
  }

  showPopup() {
    if (this.popupArea) {
      this.popupArea.classList.add("active");
      document.body.style.overflow = "hidden";
    }
  }

  hidePopup() {
    if (this.popupArea) {
      this.popupArea.classList.remove("active");
      document.body.style.overflow = "";
      this.resetActiveNavButton();
    }
  }

  setActiveNavButton(activeBtn) {
    this.navButtons.forEach((btn) => btn.classList.remove("active"));
    activeBtn.classList.add("active");
  }

  resetActiveNavButton() {
    this.navButtons.forEach((btn) => btn.classList.remove("active"));
    // Optionally set a default active button
    const dashboardBtn = document.querySelector('[data-section="dashboard"]');
    if (dashboardBtn) {
      dashboardBtn.classList.add("active");
    }
  }
}

// Note: PopupContentManager removed as navigation is now handled directly
// Navigation is managed by setupEventListeners() function

console.log(
  "%c🎯 Popup Content System Ready",
  "color: #9C27B0; font-weight: bold;"
);

// ===== QUICK ACTIONS FUNCTIONS =====
function quickAction(action) {
  switch (action) {
    case "book":
      showSection("my-reservations");
      updateActiveNavigation("my-reservations");
      showNotification("Opening booking page...", "info");
      break;
    case "view":
      showSection("bookings-history");
      updateActiveNavigation("bookings-history");
      showNotification("Loading your bookings...", "info");
      break;
    case "promo":
      showSection("promotions");
      updateActiveNavigation("promotions");
      showNotification("Check out our latest promotions!", "success");
      break;
    case "support":
      showNotification("Customer support: +63 917 123 4567", "info", 5000);
      break;
  }
}

// ===== BOOKING HISTORY FUNCTIONS =====
function viewBookingDetails(bookingId) {
  showNotification(`Loading details for booking ${bookingId}...`, "info");
  // In production, this would fetch and display full booking details
}

function leaveReview(bookingId) {
  showSection("reviews");
  updateActiveNavigation("reviews");
  showNotification("Write a review for your stay!", "success");
  // In production, this would open a review form for that specific booking
}

function bookAgain(packageType) {
  showSection("my-reservations");
  updateActiveNavigation("my-reservations");
  showNotification(`Pre-filling ${packageType} package...`, "info");
  // In production, this would pre-fill the booking form
}

function modifyBooking(bookingId) {
  showNotification(`Opening modification form for ${bookingId}...`, "info");
  // In production, this would open an editable booking form
}

function cancelBooking(bookingId) {
  if (
    confirm(
      "Are you sure you want to cancel this booking? This action cannot be undone."
    )
  ) {
    showNotification(`Cancelling booking ${bookingId}...`, "warning");
    // In production, this would call an API to cancel the booking
    setTimeout(() => {
      showNotification("Booking cancelled successfully.", "success");
    }, 1500);
  }
}

function completePayment(reservationId, paymentType = "downpayment") {
  // Open payment upload modal
  showPaymentUploadModal(reservationId, paymentType);
}

// ===== LOAD MY RESERVATIONS =====
async function loadMyReservations() {
  try {
    const response = await fetch("user/get_my_reservations.php");
    const result = await response.json();

    if (result.success) {
      displayReservations(result.reservations);
      updateDashboardCounts(result.reservations);
    } else {
      showNotification(
        result.message || "Failed to load reservations",
        "error"
      );
    }
  } catch (error) {
    console.error("Error loading reservations:", error);
    showNotification("Failed to load reservations", "error");
  }
}

function updateDashboardCounts(reservations) {
  const total = reservations.length;
  const pending = reservations.filter(
    (r) => r.status === "pending_payment" || r.status === "pending_confirmation"
  ).length;
  const confirmed = reservations.filter((r) => r.status === "confirmed").length;
  const completed = reservations.filter((r) => r.status === "completed").length;

  const totalEl = document.getElementById("totalReservations");
  const pendingEl = document.getElementById("pendingReservations");
  const approvedEl = document.getElementById("approvedReservations");
  const completedEl = document.getElementById("completedReservations");

  if (totalEl) totalEl.textContent = total;
  if (pendingEl) pendingEl.textContent = pending;
  if (approvedEl) approvedEl.textContent = confirmed;
  if (completedEl) completedEl.textContent = completed;
}

function displayReservations(reservations) {
  const container = document.querySelector(".bookings-history-grid");
  if (!container) return;

  if (reservations.length === 0) {
    container.innerHTML = `
      <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: #94a3b8;">
        <i class="fas fa-calendar-times" style="font-size: 64px; margin-bottom: 20px; opacity: 0.3;"></i>
        <p style="font-size: 18px; font-weight: 600;">No reservations yet</p>
        <p style="margin-top: 10px;">Make your first reservation to see it here!</p>
        <button onclick="showSection('my-reservations'); updateActiveNavigation('my-reservations');" 
                style="margin-top: 20px; padding: 12px 24px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; border-radius: 8px; cursor: pointer;">
          <i class="fas fa-calendar-plus"></i> Make Reservation
        </button>
      </div>
    `;
    return;
  }

  container.innerHTML = reservations
    .map((r) => createReservationCard(r))
    .join("");
}

function createReservationCard(r) {
  const statusColors = {
    pending_payment: "pending",
    pending_confirmation: "pending",
    confirmed: "confirmed",
    checked_in: "confirmed",
    checked_out: "completed",
    completed: "completed",
    cancelled: "cancelled",
    no_show: "cancelled",
    forfeited: "cancelled",
  };

  const statusClass = statusColors[r.status] || "pending";

  return `
    <div class="booking-history-card" data-status="${statusClass}" data-reservation-id="${
    r.reservation_id
  }">
      <div class="booking-card-header">
        <span class="status-badge ${statusClass}">${r.status_label}</span>
        <span class="booking-date">${formatDate(r.check_in_date)}</span>
      </div>
      <div class="booking-card-body">
        <h4>Reservation #${r.reservation_id}</h4>
        <div class="booking-details">
          <span><i class="fas fa-calendar"></i> ${r.booking_type}</span>
          <span><i class="fas fa-box"></i> ${r.package_type || "Package"}</span>
          <span><i class="fas fa-users"></i> ${
            r.number_of_guests || 1
          } Guests</span>
        </div>
        <div class="booking-details" style="margin-top: 10px;">
          <span><i class="fas fa-money-bill-wave"></i> Total: ₱${parseFloat(
            r.total_amount
          ).toLocaleString("en-PH", { minimumFractionDigits: 2 })}</span>
          <span><i class="fas fa-wallet"></i> Down: ₱${parseFloat(
            r.downpayment_amount
          ).toLocaleString("en-PH", { minimumFractionDigits: 2 })}</span>
        </div>
        ${getPaymentStatusHTML(r)}
      </div>
      <div class="booking-card-actions">
        ${getActionButtons(r)}
      </div>
    </div>
  `;
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
}

function getPaymentStatusHTML(r) {
  let html =
    '<div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; font-size: 13px;">';

  if (r.status === "pending_payment") {
    html +=
      '<div style="color: #dc3545;"><i class="fas fa-exclamation-circle"></i> <strong>Payment Required</strong></div>';
    html +=
      '<div style="margin-top: 5px; color: #6c757d;">Please upload downpayment proof to proceed</div>';
  } else if (r.status === "pending_confirmation") {
    html +=
      '<div style="color: #ffc107;"><i class="fas fa-clock"></i> <strong>Payment Submitted</strong></div>';
    html +=
      '<div style="margin-top: 5px; color: #6c757d;">Waiting for admin verification</div>';
  } else if (r.status === "confirmed") {
    html +=
      '<div style="color: #28a745;"><i class="fas fa-check-circle"></i> <strong>Confirmed</strong></div>';
    if (!r.full_payment_paid) {
      html +=
        '<div style="margin-top: 5px; color: #6c757d;">Full payment due before check-in</div>';
    }
  }

  html += "</div>";
  return html;
}

function getActionButtons(r) {
  let buttons = `<button class="btn-secondary" onclick="viewReservationDetails(${r.reservation_id})">
    <i class="fas fa-eye"></i> View Details
  </button>`;

  if (r.can_upload_downpayment) {
    buttons += `<button class="btn-primary" onclick="completePayment(${r.reservation_id}, 'downpayment')">
      <i class="fas fa-upload"></i> Upload Payment
    </button>`;
  }

  if (r.can_upload_full_payment) {
    buttons += `<button class="btn-primary" onclick="completePayment(${r.reservation_id}, 'full_payment')">
      <i class="fas fa-upload"></i> Pay Balance
    </button>`;
  }

  if (r.can_cancel) {
    buttons += `<button class="btn-danger" onclick="cancelReservation(${r.reservation_id})">
      <i class="fas fa-times"></i> Cancel
    </button>`;
  }

  return buttons;
}

// ===== PAYMENT UPLOAD MODAL =====
function showPaymentUploadModal(reservationId, paymentType) {
  const modalHTML = `
    <div id="paymentModal" style="
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.7);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10000;
      animation: fadeIn 0.3s ease;
    ">
      <div style="
        background: white;
        border-radius: 20px;
        padding: 30px;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      ">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
          <h2 style="margin: 0; color: #333;">
            <i class="fas fa-upload"></i> Upload Payment Proof
          </h2>
          <button onclick="closePaymentModal()" style="
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
          ">&times;</button>
        </div>
        
        <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
          <p style="margin: 0 0 10px 0;"><strong>Reservation ID:</strong> #${reservationId}</p>
          <p style="margin: 0;"><strong>Payment Type:</strong> ${
            paymentType === "downpayment" ? "Downpayment (50%)" : "Full Payment"
          }</p>
        </div>
        
        <form id="paymentUploadForm" enctype="multipart/form-data">
          <input type="hidden" name="reservation_id" value="${reservationId}">
          <input type="hidden" name="payment_type" value="${paymentType}">
          
          <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">
              <i class="fas fa-mobile-alt"></i> Payment Method *
            </label>
            <select name="payment_method" required style="
              width: 100%;
              padding: 12px;
              border: 2px solid #e0e0e0;
              border-radius: 8px;
              font-size: 14px;
            ">
              <option value="">Select payment method</option>
              <option value="gcash">GCash</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="online_banking">Online Banking</option>
              <option value="cash">Cash (OTC)</option>
            </select>
          </div>
          
          <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">
              <i class="fas fa-hashtag"></i> Reference Number *
            </label>
            <input type="text" name="reference_number" required placeholder="e.g., GCash Ref# or Bank Confirmation #" style="
              width: 100%;
              padding: 12px;
              border: 2px solid #e0e0e0;
              border-radius: 8px;
              font-size: 14px;
            ">
          </div>
          
          <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">
              <i class="fas fa-image"></i> Payment Screenshot/Proof *
            </label>
            <input type="file" name="payment_proof" accept="image/*,application/pdf" required style="
              width: 100%;
              padding: 12px;
              border: 2px dashed #e0e0e0;
              border-radius: 8px;
              font-size: 14px;
            ">
            <small style="color: #666; display: block; margin-top: 5px;">
              Accepted: JPG, PNG, GIF, PDF (Max 5MB)
            </small>
          </div>
          
          <div style="background: #e7f3ff; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #2196F3;">
            <p style="margin: 0; font-size: 13px; color: #555;">
              <i class="fas fa-info-circle"></i> <strong>Important:</strong> Make sure your screenshot clearly shows:
            </p>
            <ul style="margin: 10px 0 0 20px; font-size: 13px; color: #555;">
              <li>Transaction amount</li>
              <li>Reference number</li>
              <li>Date and time</li>
              <li>Recipient name/account</li>
            </ul>
          </div>
          
          <button type="submit" style="
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
          " onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="fas fa-check-circle"></i> Submit Payment Proof
          </button>
        </form>
      </div>
    </div>
  `;

  document.body.insertAdjacentHTML("beforeend", modalHTML);

  // Handle form submission
  document
    .getElementById("paymentUploadForm")
    .addEventListener("submit", handlePaymentUpload);
}

function closePaymentModal() {
  const modal = document.getElementById("paymentModal");
  if (modal) {
    modal.style.animation = "fadeOut 0.3s ease";
    setTimeout(() => modal.remove(), 300);
  }
}

async function handlePaymentUpload(e) {
  e.preventDefault();

  const form = e.target;
  const formData = new FormData(form);
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalText = submitBtn.innerHTML;

  submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
  submitBtn.disabled = true;

  try {
    const response = await fetch("user/upload_payment_proof.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      showNotification(result.message, "success");
      closePaymentModal();
      // Reload reservations to show updated status
      loadMyReservations();
    } else {
      showNotification(
        result.message || "Failed to upload payment proof",
        "error"
      );
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    }
  } catch (error) {
    console.error("Error uploading payment:", error);
    showNotification("Failed to upload payment proof", "error");
    submitBtn.innerHTML = originalText;
    submitBtn.disabled = false;
  }
}

// ===== CANCEL RESERVATION =====
async function cancelReservation(reservationId) {
  if (
    !confirm(
      "Are you sure you want to cancel this reservation? This action cannot be undone."
    )
  ) {
    return;
  }

  try {
    const response = await fetch("user/cancel_reservation.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ reservation_id: reservationId }),
    });

    const result = await response.json();

    if (result.success) {
      showNotification("Reservation cancelled successfully", "success");
      loadMyReservations();
    } else {
      showNotification(
        result.message || "Failed to cancel reservation",
        "error"
      );
    }
  } catch (error) {
    console.error("Error cancelling reservation:", error);
    showNotification("Failed to cancel reservation", "error");
  }
}

function viewReservationDetails(reservationId) {
  showNotification(
    `Loading details for reservation #${reservationId}...`,
    "info"
  );
  // TODO: Implement detailed view modal
}

// Filter booking history
document.addEventListener("DOMContentLoaded", function () {
  const filterButtons = document.querySelectorAll(".filter-btn");
  const bookingCards = document.querySelectorAll(".booking-history-card");

  filterButtons.forEach((button) => {
    button.addEventListener("click", function () {
      // Remove active from all buttons
      filterButtons.forEach((btn) => btn.classList.remove("active"));
      // Add active to clicked button
      this.classList.add("active");

      const filter = this.getAttribute("data-filter");

      bookingCards.forEach((card) => {
        if (filter === "all") {
          card.style.display = "block";
        } else {
          const status = card.getAttribute("data-status");
          card.style.display = status === filter ? "block" : "none";
        }
      });
    });
  });

  // Search functionality
  const searchInput = document.getElementById("bookingSearch");
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      const searchTerm = this.value.toLowerCase();
      bookingCards.forEach((card) => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(searchTerm) ? "block" : "none";
      });
    });
  }
});

// ===== NOTIFICATIONS FUNCTIONS =====

// Initialize notification badge on page load
async function initNotificationBadge() {
  try {
    const response = await fetch("user/get_notifications.php?limit=1");
    if (response.ok) {
      const result = await response.json();
      if (result.success) {
        updateNotificationBadge(result.unread_count);
      }
    }
  } catch (error) {
    console.error("Error initializing notification badge:", error);
  }
}

// Load user notifications from database
async function loadUserNotifications() {
  try {
    const response = await fetch("user/get_notifications.php");

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();
    console.log("Notifications API Response:", result);

    if (result.success) {
      displayNotifications(result.notifications);
      updateNotificationBadge(result.unread_count);

      // Show message if table doesn't exist
      if (result.message) {
        console.warn(result.message);
      }
    } else {
      console.error("Error loading notifications:", result.message);
      const notificationsList = document.getElementById("notificationsList");
      if (notificationsList) {
        notificationsList.innerHTML = `
          <div style="text-align: center; padding: 40px; color: #f44336;">
            <i class="fas fa-exclamation-circle" style="font-size: 48px; margin-bottom: 16px;"></i>
            <p>Error loading notifications</p>
            <p style="font-size: 14px; color: #999;">${result.message}</p>
          </div>
        `;
      }
    }
  } catch (error) {
    console.error("Error loading notifications:", error);
    const notificationsList = document.getElementById("notificationsList");
    if (notificationsList) {
      notificationsList.innerHTML = `
        <div style="text-align: center; padding: 40px; color: #f44336;">
          <i class="fas fa-exclamation-circle" style="font-size: 48px; margin-bottom: 16px;"></i>
          <p>Failed to load notifications</p>
          <p style="font-size: 14px; color: #999;">${error.message}</p>
        </div>
      `;
    }
  }
}

// Display notifications in the UI
function displayNotifications(notifications) {
  console.log("displayNotifications called with:", notifications);
  const notificationsList = document.getElementById("notificationsList");
  console.log("notificationsList element:", notificationsList);

  if (!notificationsList) {
    console.error("Notifications list container not found");
    return;
  }

  if (!notifications || notifications.length === 0) {
    notificationsList.innerHTML = `
      <div style="text-align: center; padding: 40px; color: #999;">
        <i class="fas fa-bell-slash" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
        <p>No notifications yet</p>
      </div>
    `;
    return;
  }

  // Build notifications HTML
  let notificationsHTML = "";
  notifications.forEach((notification) => {
    const isUnread = notification.is_read == 0;
    const unreadClass = isUnread ? "unread" : "";

    // Icon based on notification type
    let icon = "bell";
    let iconColor = "#4CAF50";
    if (notification.type === "booking_confirmed") {
      icon = "check-circle";
      iconColor = "#4CAF50";
    } else if (notification.type === "booking_cancelled") {
      icon = "times-circle";
      iconColor = "#f44336";
    } else if (notification.type === "payment_reminder") {
      icon = "credit-card";
      iconColor = "#FF9800";
    } else if (notification.type === "promo") {
      icon = "tag";
      iconColor = "#2196F3";
    }

    // Format date
    const createdDate = new Date(notification.created_at);
    const now = new Date();
    const diffTime = Math.abs(now - createdDate);
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
    const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
    const diffMinutes = Math.floor(diffTime / (1000 * 60));

    let timeAgo = "";
    if (diffDays > 0) {
      timeAgo = `${diffDays} day${diffDays > 1 ? "s" : ""} ago`;
    } else if (diffHours > 0) {
      timeAgo = `${diffHours} hour${diffHours > 1 ? "s" : ""} ago`;
    } else if (diffMinutes > 0) {
      timeAgo = `${diffMinutes} minute${diffMinutes > 1 ? "s" : ""} ago`;
    } else {
      timeAgo = "Just now";
    }

    notificationsHTML += `
      <div class="notification-item ${unreadClass}" data-notification-id="${
      notification.notification_id
    }">
        <div class="notification-icon" style="background-color: ${iconColor};">
          <i class="fas fa-${icon}"></i>
        </div>
        <div class="notification-content">
          <h4>${notification.title}</h4>
          <p>${notification.message}</p>
          <span class="notification-time">${timeAgo}</span>
          ${
            notification.link
              ? `<a href="${notification.link}" class="notification-link">View Details</a>`
              : ""
          }
        </div>
        <button class="dismiss-btn" onclick="dismissNotification(this)" title="Dismiss">
          <i class="fas fa-times"></i>
        </button>
      </div>
    `;
  });

  notificationsList.innerHTML = notificationsHTML;
}

// Update notification badge count
function updateNotificationBadge(unreadCount) {
  const badge = document.getElementById("notificationCount");
  if (badge) {
    if (unreadCount > 0) {
      badge.textContent = unreadCount;
      badge.style.display = "block";
    } else {
      badge.style.display = "none";
    }
  }
}

// Mark all notifications as read
async function markAllAsRead() {
  try {
    const response = await fetch("user/mark_notifications_read.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ mark_all: true }),
    });

    const result = await response.json();

    if (result.success) {
      // Update UI
      const unreadNotifications = document.querySelectorAll(
        ".notification-item.unread"
      );
      unreadNotifications.forEach((notif) => {
        notif.classList.remove("unread");
      });

      // Update badge
      updateNotificationBadge(0);
      showNotification("All notifications marked as read.", "success");
    } else {
      showNotification("Failed to mark notifications as read.", "error");
    }
  } catch (error) {
    console.error("Error marking notifications as read:", error);
    showNotification("Failed to mark notifications as read.", "error");
  }
}

// Dismiss a notification
async function dismissNotification(button) {
  const notificationItem = button.closest(".notification-item");
  const notificationId = notificationItem.dataset.notificationId;

  try {
    const response = await fetch("user/dismiss_notification.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ notification_id: notificationId }),
    });

    const result = await response.json();

    if (result.success) {
      // Animate out and remove
      notificationItem.style.animation = "slideOutRight 0.3s ease";
      setTimeout(() => {
        notificationItem.remove();

        // Update badge count
        const remainingUnread = document.querySelectorAll(
          ".notification-item.unread"
        ).length;
        updateNotificationBadge(remainingUnread);

        // Check if no notifications left
        const allNotifications =
          document.querySelectorAll(".notification-item");
        if (allNotifications.length === 0) {
          displayNotifications([]);
        }
      }, 300);
    } else {
      showNotification("Failed to dismiss notification.", "error");
    }
  } catch (error) {
    console.error("Error dismissing notification:", error);
    showNotification("Failed to dismiss notification.", "error");
  }
}

// Initialize notifications on page load
// Initialize notifications badge on page load
document.addEventListener("DOMContentLoaded", function () {
  initNotificationBadge();
});

// ===== REWARDS FUNCTIONS =====
function redeemReward(points, rewardType) {
  const currentPoints = 2500; // This would come from user data

  if (currentPoints < points) {
    showNotification("Insufficient points for this reward.", "error");
    return;
  }

  if (confirm(`Redeem this reward for ${points} points?`)) {
    showNotification(
      `Redeeming reward... ${points} points will be deducted.`,
      "info"
    );

    setTimeout(() => {
      showNotification(
        "Reward redeemed successfully! Check your email for details.",
        "success"
      );
      // In production, this would update the points balance
    }, 1500);
  }
}

// ===== REVIEWS FUNCTIONS =====
function writeNewReview() {
  showNotification("Opening review form...", "info");
  // In production, this would show a modal with review form
  setTimeout(() => {
    alert(
      "Review form would appear here!\n\nFeatures:\n- Rating stars\n- Text area for review\n- Photo upload\n- Submit button"
    );
  }, 500);
}

function editReview(reviewId) {
  showNotification(`Loading review ${reviewId} for editing...`, "info");
  // In production, this would open the review in edit mode
}

function deleteReview(reviewId) {
  if (
    confirm(
      "Are you sure you want to delete this review? This action cannot be undone."
    )
  ) {
    showNotification("Deleting review...", "warning");

    setTimeout(() => {
      showNotification("Review deleted successfully.", "success");
      // In production, this would remove the review from the list
      document.querySelector(`[data-review-id="${reviewId}"]`)?.remove();
    }, 1000);
  }
}

// ===== RESERVATION SYSTEM =====
// Global reservation state
window.reservationData = {
  bookingType: null,
  packageType: null,
  checkInDate: null,
  checkOutDate: null,
  checkInTime: null,
  checkOutTime: null,
  duration: null,
  basePrice: 0,
  totalAmount: 0,
  downpayment: 0,
  balance: 0,
};

// Booking type configurations
window.bookingTypes = {
  daytime: {
    label: "DAYTIME",
    checkInTime: "9:00 AM",
    checkOutTime: "5:00 PM",
    durationLabel: "day(s)",
    prices: {
      "all-rooms": 15000,
      "aircon-rooms": 12000,
      "basic-rooms": 8000,
    },
  },
  nighttime: {
    label: "NIGHTTIME",
    checkInTime: "7:00 PM",
    checkOutTime: "7:00 AM",
    durationLabel: "night(s)",
    prices: {
      "all-rooms": 25000,
      "aircon-rooms": 20000,
      "basic-rooms": 12000,
    },
  },
  "22hours": {
    label: "22 HOURS",
    checkInTime: "2:00 PM",
    checkOutTime: "12:00 NN",
    durationLabel: "night(s)",
    prices: {
      "all-rooms": 25000,
      "aircon-rooms": 20000,
      "basic-rooms": 12000,
    },
  },
};

// Select booking type and show room selection
function selectBookingType(type) {
  console.log("🎯 selectBookingType called with type:", type);
  console.log("📋 bookingTypes object:", window.bookingTypes);
  console.log("✅ Function is working!");

  if (!window.bookingTypes[type]) {
    console.error("❌ Invalid booking type:", type);
    showNotification("Invalid booking type", "error");
    return;
  }

  console.log("📦 Storing booking data...");

  // Store booking type
  window.reservationData.bookingType = type;
  window.reservationData.checkInTime = window.bookingTypes[type].checkInTime;
  window.reservationData.checkOutTime = window.bookingTypes[type].checkOutTime;

  // Update prices in room packages
  const prices = window.bookingTypes[type].prices;
  document.getElementById("all-rooms-price").textContent =
    prices["all-rooms"].toLocaleString();
  document.getElementById("aircon-rooms-price").textContent =
    prices["aircon-rooms"].toLocaleString();
  document.getElementById("basic-rooms-price").textContent =
    prices["basic-rooms"].toLocaleString();

  // Update price period labels
  const periodLabel =
    window.bookingTypes[type].durationLabel === "day(s)"
      ? "per day"
      : "per night";
  document.querySelectorAll(".price-period").forEach((el) => {
    el.textContent = periodLabel;
  });

  // Hide step 1, show step 2
  document.getElementById("step1").style.display = "none";
  document.getElementById("step2").style.display = "block";

  showNotification(
    `${window.bookingTypes[type].label} booking selected`,
    "success"
  );
}

// Select room package and show booking form
function selectRoomPackage(packageType) {
  console.log("Selected package:", packageType);

  const type = window.reservationData.bookingType;
  if (!type) {
    showNotification("Please select a booking type first", "error");
    return;
  }

  // Store package type and price
  window.reservationData.packageType = packageType;
  window.reservationData.basePrice =
    window.bookingTypes[type].prices[packageType];

  // Update summary
  const packageNames = {
    "all-rooms": "All Rooms Package",
    "aircon-rooms": "Aircon Rooms Package",
    "basic-rooms": "Basic Rooms Package",
  };

  document.getElementById("summaryBookingType").textContent =
    window.bookingTypes[type].label;
  document.getElementById("summaryPackage").textContent =
    packageNames[packageType];
  document.getElementById("summaryCheckInTime").textContent =
    window.bookingTypes[type].checkInTime;
  document.getElementById("summaryCheckOutTime").textContent =
    window.bookingTypes[type].checkOutTime;

  // Update duration label
  const durationGroup = document.getElementById("durationGroup");
  const durationLabel = durationGroup.querySelector("label");
  durationLabel.textContent =
    window.bookingTypes[type].durationLabel === "day(s)"
      ? "Number of Days *"
      : "Number of Nights *";

  // Update price summary
  updatePriceSummary();

  // Hide step 2, show step 3
  document.getElementById("step2").style.display = "none";
  document.getElementById("step3").style.display = "block";

  // Set minimum date to today
  const today = new Date().toISOString().split("T")[0];
  document.getElementById("checkInDate").min = today;

  showNotification(`${packageNames[packageType]} selected`, "success");
}

// Go back to specific step
function goBackToStep(stepNumber) {
  // Hide all steps
  document.querySelectorAll(".booking-step").forEach((step) => {
    step.style.display = "none";
  });

  // Show requested step
  document.getElementById(`step${stepNumber}`).style.display = "block";
}

// Update price summary when duration changes
function updatePriceSummary() {
  const duration = parseInt(document.getElementById("duration").value) || 0;
  const basePrice = window.reservationData.basePrice;
  const type = window.reservationData.bookingType;

  const totalAmount = basePrice * duration;
  const downpayment = totalAmount * 0.5;
  const balance = totalAmount - downpayment;

  // Store values
  window.reservationData.duration = duration;
  window.reservationData.totalAmount = totalAmount;
  window.reservationData.downpayment = downpayment;
  window.reservationData.balance = balance;

  // Update UI
  document.getElementById("priceBase").textContent =
    "₱" + basePrice.toLocaleString("en-PH", { minimumFractionDigits: 2 });
  document.getElementById(
    "priceDuration"
  ).textContent = `${duration} ${window.bookingTypes[type].durationLabel}`;
  document.getElementById("priceTotal").textContent =
    "₱" + totalAmount.toLocaleString("en-PH", { minimumFractionDigits: 2 });
  document.getElementById("priceDownpayment").textContent =
    "₱" + downpayment.toLocaleString("en-PH", { minimumFractionDigits: 2 });
  document.getElementById("priceBalance").textContent =
    "₱" + balance.toLocaleString("en-PH", { minimumFractionDigits: 2 });
}

// Setup reservation form listeners
document.addEventListener("DOMContentLoaded", function () {
  // Duration change listener
  const durationSelect = document.getElementById("duration");
  if (durationSelect) {
    durationSelect.addEventListener("change", updatePriceSummary);
  }

  // Reservation form submission
  const reservationForm = document.getElementById("reservationForm");
  if (reservationForm) {
    reservationForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      // Validate booking data
      if (
        !window.reservationData.bookingType ||
        !window.reservationData.packageType
      ) {
        showNotification("Please complete all booking steps", "error");
        return;
      }

      // Collect form data
      const formData = new FormData(reservationForm);
      const checkInDate = formData.get("checkInDate");
      const duration = parseInt(formData.get("duration"));
      const groupSize = formData.get("groupSize");
      const groupType = formData.get("groupType");
      const specialRequests = formData.get("specialRequests");
      const paymentMethod = formData.get("paymentMethod");
      const agreeTerms = formData.get("agreeTerms"); // checkbox returns 'on' if checked, null if not

      console.log("📋 Form data collected:", {
        checkInDate,
        duration,
        groupSize,
        groupType,
        paymentMethod,
        agreeTerms,
        hasBookingType: !!window.reservationData.bookingType,
        hasPackageType: !!window.reservationData.packageType,
      });

      // Validate required fields
      if (!checkInDate) {
        showNotification("Please select a check-in date", "error");
        return;
      }

      if (!duration || isNaN(duration) || duration < 1) {
        showNotification("Please select a valid duration", "error");
        return;
      }

      if (!groupSize) {
        showNotification("Please select group size", "error");
        return;
      }

      if (!groupType) {
        showNotification("Please select group type", "error");
        return;
      }

      if (!paymentMethod) {
        showNotification("Please select a payment method", "error");
        return;
      }

      if (!agreeTerms) {
        showNotification("Please agree to the terms and conditions", "error");
        return;
      }

      // Calculate check-out date
      const checkIn = new Date(checkInDate);
      const checkOut = new Date(checkIn);
      checkOut.setDate(checkOut.getDate() + duration);
      const checkOutDate = checkOut.toISOString().split("T")[0];

      // Format package type with booking suffix (e.g., 'all-rooms-night' or 'all-rooms-day')
      const bookingType = window.reservationData.bookingType;
      const packageBase = window.reservationData.packageType;
      const packageSuffix = bookingType === "daytime" ? "-day" : "-night";
      const packageType = packageBase + packageSuffix;

      console.log("📦 Formatting package type:", {
        bookingType,
        packageBase,
        packageSuffix,
        final: packageType,
      });

      // Prepare reservation data
      const reservationPayload = {
        booking_type: bookingType,
        package_type: packageType,
        check_in_date: checkInDate,
        check_out_date: checkOutDate,
        number_of_days: bookingType === "daytime" ? duration : null,
        number_of_nights: bookingType !== "daytime" ? duration : null,
        group_size: groupSize,
        group_type: groupType,
        special_requests: specialRequests,
        payment_method: paymentMethod,
      };

      console.log("🚀 Sending reservation payload:", reservationPayload);

      // Show loading
      const submitBtn = reservationForm.querySelector(".btn-submit");
      const originalText = submitBtn.innerHTML;
      submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> Processing...';
      submitBtn.disabled = true;

      try {
        // Submit reservation
        const response = await fetch("user/make_reservation.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(reservationPayload),
        });

        console.log("📡 Response status:", response.status);
        console.log("📡 Response OK:", response.ok);

        // Try to parse response as JSON
        let result;
        const responseText = await response.text();
        console.log("📡 Raw response:", responseText);

        try {
          result = JSON.parse(responseText);
          console.log("📦 Response data:", result);
        } catch (parseError) {
          console.error("❌ Failed to parse JSON response:", parseError);
          console.error("Response text:", responseText);
          throw new Error(
            "Server returned invalid JSON: " + responseText.substring(0, 100)
          );
        }

        if (result.success) {
          showNotification("Reservation created successfully!", "success");

          // Show success details
          setTimeout(() => {
            alert(
              `Reservation Confirmed!\n\nReservation ID: ${
                result.reservation_id
              }\nTotal Amount: ₱${result.total_amount.toLocaleString("en-PH", {
                minimumFractionDigits: 2,
              })}\nDownpayment: ₱${result.downpayment_amount.toLocaleString(
                "en-PH",
                { minimumFractionDigits: 2 }
              )}\n\nPlease pay the downpayment to confirm your booking.\n\nPayment Instructions:\n- For GCash: Send to 0917-123-4567\n- For OTC: Visit resort reception\n\nReference: ${
                result.reservation_id
              }`
            );

            // Reset form and go back to step 1
            reservationForm.reset();
            window.reservationData = {
              bookingType: null,
              packageType: null,
              basePrice: 0,
              totalAmount: 0,
              downpayment: 0,
              balance: 0,
            };
            goBackToStep(1);

            // Switch to booking history
            showSection("bookings-history");
            updateActiveNavigation("bookings-history");
          }, 1500);
        } else {
          console.error("❌ Reservation failed:", result);
          showNotification(
            result.message || "Failed to create reservation",
            "error"
          );

          // Show detailed error in alert for debugging
          if (result.error_type) {
            console.error("Error type:", result.error_type);
          }
        }
      } catch (error) {
        console.error("❌ Reservation error:", error);
        showNotification(
          "Network error. Please check console for details.",
          "error"
        );
      } finally {
        // Restore button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      }
    });
  }
});

// Show booking policy modal
function showBookingPolicy() {
  const policyHTML = `
    <div class="policy-modal">
      <h2>Booking Policy</h2>
      <div class="policy-content">
        <h3>General Policies</h3>
        <ul>
          <li><strong>First to Pay, First to Reserve Policy</strong></li>
          <li><strong>50% Downpayment Required</strong> to confirm reservation</li>
          <li>Remaining balance must be paid <strong>before check-in time</strong></li>
          <li>Reservation fee is <strong>non-refundable/transferable</strong></li>
          <li>We only do <strong>rebooking within 3 months</strong></li>
          <li>Rebooking allowed <strong>7 days prior</strong> to schedule date</li>
          <li>Non-appearance on schedule date will be <strong>forfeited</strong></li>
        </ul>
        
        <h3>Security & Charges</h3>
        <ul>
          <li><strong>₱2,000 Security Bond</strong> upon check-in (refundable if no damage)</li>
          <li>Additional charges apply for exceeding hours</li>
        </ul>
        
        <h3>House Rules</h3>
        <ul>
          <li>Noise beyond 10:00 PM may be disruptive - please be considerate</li>
          <li>Videoke/loudspeaker: <strong>9AM to 10PM only</strong></li>
          <li>Penalty of <strong>₱2,000</strong> for excessive noise/unruly behavior</li>
          <li><strong>Strict No Smoking</strong> - Penalty: <strong>₱5,000</strong></li>
          <li>Small breed pets accepted (not on bed/near pool)</li>
          <li>Practice <strong>Clean As You Go</strong></li>
        </ul>
      </div>
      <button onclick="closeModal()" class="btn-primary">Close</button>
    </div>
  `;

  // Show in modal or alert
  alert(
    "BOOKING POLICY\n\n" +
      "FIRST TO PAY, FIRST TO RESERVE\n" +
      "50% DOWNPAYMENT REQUIRED\n" +
      "REMAINING BALANCE BEFORE CHECK-IN\n" +
      "NON-REFUNDABLE (Rebooking only within 3 months)\n" +
      "Rebooking allowed 7 days prior\n" +
      "₱2,000 Security Bond (refundable)\n\n" +
      "HOUSE RULES:\n" +
      "- Noise curfew at 10PM\n" +
      "- No Smoking (₱5,000 penalty)\n" +
      "- Clean as you go\n" +
      "- Respect the property"
  );
}

// ===== GLOBAL FUNCTION EXPORTS =====
window.quickAction = quickAction;
window.viewBookingDetails = viewBookingDetails;
window.leaveReview = leaveReview;
window.bookAgain = bookAgain;
window.modifyBooking = modifyBooking;
window.cancelBooking = cancelBooking;
window.completePayment = completePayment;
window.markAllAsRead = markAllAsRead;
window.dismissNotification = dismissNotification;
window.redeemReward = redeemReward;
window.writeNewReview = writeNewReview;
window.editReview = editReview;
window.deleteReview = deleteReview;
window.selectBookingType = selectBookingType;
window.selectRoomPackage = selectRoomPackage;
window.goBackToStep = goBackToStep;
window.showBookingPolicy = showBookingPolicy;
window.refreshUserData = refreshUserData;

console.log(
  "%c✨ Enhanced Features Loaded",
  "color: #4CAF50; font-weight: bold;"
);

console.log(
  "%c🎫 Reservation System Loaded",
  "color: #2196F3; font-weight: bold;"
);

// Debug: Verify functions are accessible
console.log(
  "%c🔍 DEBUGGING: Checking if selectBookingType is accessible...",
  "color: #FF9800; font-weight: bold;"
);
console.log(
  "typeof window.selectBookingType:",
  typeof window.selectBookingType
);
console.log("typeof selectBookingType:", typeof selectBookingType);
console.log("typeof window.bookingTypes:", typeof window.bookingTypes);
console.log(
  '✅ If you see "function" and "object" above, the buttons should work!'
);

// Test button onclick attributes
setTimeout(() => {
  const buttons = document.querySelectorAll(".select-booking-type-btn");
  console.log("🔘 Found booking type buttons:", buttons.length);
  buttons.forEach((btn, index) => {
    console.log(`Button ${index + 1}:`, {
      onclick: btn.getAttribute("onclick"),
      hasOnclickFunction: typeof btn.onclick === "function",
    });
  });
}, 1000);

// BACKUP: Add direct event listeners in case onclick doesn't work
document.addEventListener("DOMContentLoaded", function () {
  console.log(
    "%c🔧 Adding backup event listeners to booking buttons...",
    "color: #9C27B0; font-weight: bold;"
  );

  setTimeout(() => {
    // Find buttons by their onclick attribute
    const daytimeBtn = document.querySelector(
      '.select-booking-type-btn[onclick*="daytime"]'
    );
    const nighttimeBtn = document.querySelector(
      '.select-booking-type-btn[onclick*="nighttime"]'
    );
    const hours22Btn = document.querySelector(
      '.select-booking-type-btn[onclick*="22hours"]'
    );

    let addedListeners = 0;

    if (daytimeBtn) {
      daytimeBtn.addEventListener("click", function (e) {
        console.log(
          "%c☀️ Daytime button backup listener triggered!",
          "color: #FF9800; font-weight: bold;"
        );
        selectBookingType("daytime");
      });
      addedListeners++;
      console.log("✅ Daytime button backup listener added");
    }

    if (nighttimeBtn) {
      nighttimeBtn.addEventListener("click", function (e) {
        console.log(
          "%c🌙 Nighttime button backup listener triggered!",
          "color: #2196F3; font-weight: bold;"
        );
        selectBookingType("nighttime");
      });
      addedListeners++;
      console.log("✅ Nighttime button backup listener added");
    }

    if (hours22Btn) {
      hours22Btn.addEventListener("click", function (e) {
        console.log(
          "%c⏰ 22 Hours button backup listener triggered!",
          "color: #4CAF50; font-weight: bold;"
        );
        selectBookingType("22hours");
      });
      addedListeners++;
      console.log("✅ 22 Hours button backup listener added");
    }

    if (addedListeners > 0) {
      console.log(
        `%c🎉 SUCCESS! Added ${addedListeners} backup event listeners!`,
        "background: #4CAF50; color: white; padding: 8px; font-weight: bold;"
      );
      console.log(
        "💡 Buttons should now work even if onclick attributes fail!"
      );
    } else {
      console.warn(
        '⚠️ Could not find booking type buttons. Make sure you are on the "Make Reservation" section.'
      );
    }
  }, 1500);
});
