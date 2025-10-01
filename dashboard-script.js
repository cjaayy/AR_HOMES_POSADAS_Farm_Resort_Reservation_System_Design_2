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

// Load user data from localStorage or use default values
function loadUserData() {
  const storedUser = localStorage.getItem("currentUser");
  if (storedUser) {
    try {
      const user = JSON.parse(storedUser);
      return {
        name: user.name || "Demo Guest",
        email: user.email || "demo@guest.com",
        phone: "+63 (912) 345-6789",
        totalReservations: user.totalReservations || 12,
        pendingReservations: 2,
        approvedReservations: user.upcomingReservations || 2,
        completedStays: user.totalReservations
          ? user.totalReservations - 2
          : 10,
        memberSince: user.memberSince || "2024",
        loyaltyLevel: user.loyaltyLevel || "VIP",
      };
    } catch (e) {
      console.log("Error parsing user data, using defaults");
    }
  }

  // Default data for non-demo users
  return {
    name: "John Doe",
    email: "john.doe@example.com",
    phone: "+1 (555) 123-4567",
    totalReservations: 5,
    pendingReservations: 2,
    approvedReservations: 3,
    completedStays: 8,
    memberSince: "2023",
    loyaltyLevel: "Regular",
  };
}

// Initialize user data
const userData = loadUserData();

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

  // Test if the function is available
  console.log("🧪 Testing showPopup function:", typeof showPopup);

  // Show dashboard content by default
  setTimeout(() => {
    console.log("⏰ Attempting to show default dashboard...");
    showPopup("dashboard");
  }, 500);
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
      console.log("🖱️ Button clicked!", button.getAttribute("data-section"));
      const section = button.getAttribute("data-section");
      if (section) {
        showPopup(section);
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

    this.init();
  }

  init() {
    this.galleryWrapper = document.getElementById("galleryWrapper");
    this.prevBtn = document.getElementById("prevBtn");
    this.nextBtn = document.getElementById("nextBtn");
    this.indicators = document.getElementById("galleryIndicators");

    if (!this.galleryWrapper) return;

    this.slides = document.querySelectorAll(".gallery-slide");
    this.totalSlides = this.slides.length;

    this.setupEventListeners();
    this.startAutoPlay();

    console.log(
      "%c🖼️ Resort Gallery Initialized",
      "color: #4CAF50; font-weight: bold;"
    );
  }

  setupEventListeners() {
    // Button controls
    if (this.prevBtn) {
      this.prevBtn.addEventListener("click", () => this.prevSlide());
    }
    if (this.nextBtn) {
      this.nextBtn.addEventListener("click", () => this.nextSlide());
    }

    // Indicator controls
    if (this.indicators) {
      this.indicators.addEventListener("click", (e) => {
        if (e.target.classList.contains("indicator")) {
          const slideIndex = parseInt(e.target.dataset.slide);
          this.goToSlide(slideIndex);
        }
      });
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
      galleryContainer.addEventListener("mouseenter", () =>
        this.pauseAutoPlay()
      );
      galleryContainer.addEventListener("mouseleave", () =>
        this.startAutoPlay()
      );
    }

    // Keyboard navigation
    document.addEventListener("keydown", (e) => {
      if (e.key === "ArrowLeft") this.prevSlide();
      if (e.key === "ArrowRight") this.nextSlide();
    });
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
    if (this.isTransitioning || index === this.currentSlide) return;

    this.isTransitioning = true;
    this.currentSlide = index;

    const translateX = -this.currentSlide * 100;
    this.galleryWrapper.style.transform = `translateX(${translateX}%)`;

    this.updateIndicators();
    this.updateSlideStates();

    setTimeout(() => {
      this.isTransitioning = false;
    }, 400);
  }

  nextSlide() {
    const nextIndex = (this.currentSlide + 1) % this.totalSlides;
    this.goToSlide(nextIndex);
  }

  prevSlide() {
    const prevIndex =
      (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
    this.goToSlide(prevIndex);
  }

  updateIndicators() {
    const indicators = document.querySelectorAll(".indicator");
    indicators.forEach((indicator, index) => {
      indicator.classList.toggle("active", index === this.currentSlide);
    });
  }

  updateSlideStates() {
    this.slides.forEach((slide, index) => {
      slide.classList.toggle("active", index === this.currentSlide);
    });
  }

  startAutoPlay() {
    this.pauseAutoPlay();
    this.autoPlayInterval = setInterval(() => {
      this.nextSlide();
    }, 5000); // Change slide every 5 seconds
  }

  pauseAutoPlay() {
    if (this.autoPlayInterval) {
      clearInterval(this.autoPlayInterval);
      this.autoPlayInterval = null;
    }
  }

  destroy() {
    this.pauseAutoPlay();
    // Remove event listeners if needed
  }
}

// Initialize gallery when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  // Wait a bit for other initializations to complete
  setTimeout(() => {
    window.resortGallery = new ResortGallery();
  }, 100);
});

console.log("%c🖼️ Gallery System Ready", "color: #2196F3; font-weight: bold;");

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

// Initialize popup system when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  console.log("🚀 DOM loaded, starting initialization...");

  // First, let's check if basic elements exist
  const navButtons = document.querySelectorAll(".nav-btn");
  const popupArea = document.getElementById("popupContentArea");

  console.log("🔍 Found elements:", {
    navButtons: navButtons.length,
    popupArea: !!popupArea,
  });

  // Add immediate click listeners to test
  navButtons.forEach((btn, index) => {
    console.log(`🔧 Setting up button ${index}: ${btn.dataset.section}`);
    btn.onclick = function () {
      console.log(`✅ Button ${index} clicked: ${btn.dataset.section}`);
      alert(`Button clicked: ${btn.dataset.section}`);
    };
  });

  setTimeout(() => {
    try {
      window.popupManager = new PopupContentManager();
      console.log("✅ Popup manager initialized successfully");
    } catch (error) {
      console.error("❌ Error initializing popup manager:", error);
    }
  }, 150);
});

// Add CSS animations for notifications
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
`;
document.head.appendChild(notificationStyles);

console.log(
  "%c🎯 Popup Content System Ready",
  "color: #9C27B0; font-weight: bold;"
);
