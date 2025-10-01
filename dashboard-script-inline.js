// ===== DASHBOARD INLINE LAYOUT SCRIPT =====

// Gallery and Navigation functionality for inline layout
class DashboardInlineManager {
  constructor() {
    this.currentSlide = 0;
    this.slides = [];
    this.currentSection = "dashboard";
    this.init();
  }

  init() {
    console.log("Initializing Dashboard Inline Manager...");
    this.initGallery();
    this.initNavigation();
    this.showInitialSection();
  }

  // Initialize Gallery functionality
  initGallery() {
    console.log("Initializing gallery...");
    this.slides = document.querySelectorAll(".gallery-slide");

    if (this.slides.length === 0) {
      console.warn("No gallery slides found");
      return;
    }

    // Gallery controls
    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");
    const indicators = document.querySelectorAll(".indicator");

    if (prevBtn) {
      prevBtn.addEventListener("click", () => this.previousSlide());
    }

    if (nextBtn) {
      nextBtn.addEventListener("click", () => this.nextSlide());
    }

    // Indicator controls
    indicators.forEach((indicator, index) => {
      indicator.addEventListener("click", () => this.goToSlide(index));
    });

    // Auto-slide (optional)
    this.startAutoSlide();
  }

  // Initialize Navigation functionality
  initNavigation() {
    console.log("Initializing navigation...");
    const navButtons = document.querySelectorAll(".nav-btn");

    navButtons.forEach((button) => {
      button.addEventListener("click", (e) => {
        const section = e.currentTarget.dataset.section;
        console.log(`Navigation clicked: ${section}`);
        this.showSection(section);
        this.setActiveNavButton(e.currentTarget);
      });
    });

    // Logout button functionality is handled by onclick in HTML
  }

  // Gallery Methods
  nextSlide() {
    this.currentSlide = (this.currentSlide + 1) % this.slides.length;
    this.updateGallery();
  }

  previousSlide() {
    this.currentSlide =
      this.currentSlide === 0 ? this.slides.length - 1 : this.currentSlide - 1;
    this.updateGallery();
  }

  goToSlide(index) {
    this.currentSlide = index;
    this.updateGallery();
  }

  updateGallery() {
    // Update slides
    this.slides.forEach((slide, index) => {
      slide.classList.toggle("active", index === this.currentSlide);
    });

    // Update indicators
    const indicators = document.querySelectorAll(".indicator");
    indicators.forEach((indicator, index) => {
      indicator.classList.toggle("active", index === this.currentSlide);
    });
  }

  startAutoSlide() {
    setInterval(() => {
      this.nextSlide();
    }, 5000); // Change slide every 5 seconds
  }

  // Navigation Methods
  showSection(sectionName) {
    console.log(`Showing section: ${sectionName}`);

    // Hide all sections
    const allSections = document.querySelectorAll(".content-section");
    allSections.forEach((section) => {
      section.classList.remove("active");
    });

    // Show the selected section
    const targetSection = document.getElementById(`${sectionName}-section`);
    if (targetSection) {
      targetSection.classList.add("active");
      this.currentSection = sectionName;
      console.log(`Section ${sectionName} is now active`);
    } else {
      console.error(`Section ${sectionName}-section not found!`);
    }
  }

  // Show/Hide booking navigation tab based on room selection
  toggleBookingTab(show) {
    const bookingTab = document.getElementById("booking-details-tab");
    if (bookingTab) {
      if (show) {
        bookingTab.style.display = "block";
        console.log("Booking tab is now visible");
      } else {
        bookingTab.style.display = "none";
        console.log("Booking tab is now hidden");
      }
    }
  }

  setActiveNavButton(activeButton) {
    // Remove active class from all nav buttons
    const navButtons = document.querySelectorAll(".nav-btn");
    navButtons.forEach((button) => {
      button.classList.remove("active");
    });

    // Add active class to clicked button
    activeButton.classList.add("active");
  }

  showInitialSection() {
    // Show dashboard section by default
    this.showSection("dashboard");

    // Hide booking tab initially (only show when room is selected)
    this.toggleBookingTab(false);

    // Set dashboard button as active
    const dashboardBtn = document.querySelector('[data-section="dashboard"]');
    if (dashboardBtn) {
      this.setActiveNavButton(dashboardBtn);
    }
  }
}

// Utility Functions
function validatePromoCode() {
  const promoInput = document.getElementById("promoCodeInput");
  const promoCode = promoInput?.value.trim().toUpperCase();

  const validCodes = {
    EARLYBIRD25: "25% Early Bird Discount",
    WEEKEND20: "20% Weekend Escape",
    FAMILY30: "30% Family Package",
    VIP40: "40% VIP Member Rate",
  };

  if (validCodes[promoCode]) {
    alert(`✅ Promo code applied! ${validCodes[promoCode]}`);
    promoInput.value = "";
  } else {
    alert("❌ Invalid promo code. Please try again.");
  }
}

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  console.log("DOM loaded, starting dashboard...");
  window.dashboardManager = new DashboardInlineManager();
});

// ===== LOGOUT POPUP FUNCTIONS =====
window.showLogoutPopup = function () {
  console.log("Showing logout popup...");
  const popup = document.getElementById("logoutPopupOverlay");
  if (popup) {
    popup.classList.add("active");
    document.body.style.overflow = "hidden"; // Prevent background scrolling
  }
};

window.hideLogoutPopup = function () {
  console.log("Hiding logout popup...");
  const popup = document.getElementById("logoutPopupOverlay");
  if (popup) {
    popup.classList.remove("active");
    document.body.style.overflow = ""; // Restore scrolling
  }
};

window.confirmLogout = function () {
  console.log("Confirming logout...");
  // Show loading state
  const confirmBtn = document.querySelector(".logout-popup-btn.confirm");
  if (confirmBtn) {
    confirmBtn.innerHTML =
      '<i class="fas fa-spinner fa-spin"></i> Logging out...';
    confirmBtn.disabled = true;
  }

  // Simulate logout process
  setTimeout(() => {
    console.log("Redirecting to login page...");
    window.location.href = "index.html";
  }, 1000);
};

// Close popup when clicking outside
window.addEventListener("click", function (event) {
  const popup = document.getElementById("logoutPopupOverlay");
  if (event.target === popup) {
    hideLogoutPopup();
  }
});

// Close popup with Escape key
window.addEventListener("keydown", function (event) {
  if (event.key === "Escape") {
    hideLogoutPopup();
  }
});

// Debug functions for testing
window.testNavigation = function (section) {
  console.log(`Testing navigation to: ${section}`);
  const manager = new DashboardInlineManager();
  manager.showSection(section);
};

window.testGallery = function () {
  console.log("Testing gallery functionality");
  const manager = new DashboardInlineManager();
  setTimeout(() => manager.nextSlide(), 1000);
  setTimeout(() => manager.nextSlide(), 2000);
  setTimeout(() => manager.previousSlide(), 3000);
};

// ===== ROOM PACKAGE BOOKING FUNCTIONALITY =====
let selectedPackageData = null;

// Function for room package selection
window.selectRoomPackage = function (packageType, nightPrice, dayPrice) {
  console.log("Selecting room package:", packageType);

  // Check if dashboard manager is available
  if (!window.dashboardManager) {
    console.error("Dashboard manager not available yet");
    return;
  }

  // Remove previous selection
  document.querySelectorAll(".room-package-card").forEach((card) => {
    card.classList.remove("selected");
  });

  // Add selection to clicked package
  const selectedCard = document.querySelector(
    `[data-package="${packageType}"]`
  );
  if (selectedCard) {
    selectedCard.classList.add("selected");
  } else {
    console.warn(`No card found for package: ${packageType}`);
  }

  // Store selected package data
  selectedPackageData = {
    type: packageType,
    nightPrice: nightPrice,
    dayPrice: dayPrice,
    nightPriceValue: parseInt(nightPrice.replace(/[₱,]/g, "")),
    dayPriceValue: parseInt(dayPrice.replace(/[₱,]/g, "")),
  };

  // Package details
  const packageNames = {
    "all-rooms": "All Rooms Package",
    "aircon-rooms": "Aircon Rooms Package",
    "basic-rooms": "Basic Rooms Package",
    custom: "Custom Room Selection",
  };

  const packageDescriptions = {
    "all-rooms":
      "Complete resort access + all accommodations (up to 20 guests)",
    "aircon-rooms":
      "Complete resort access + air-conditioned rooms (up to 12 guests)",
    "basic-rooms": "Complete resort access + basic fan rooms (up to 8 guests)",
    custom: "Complete resort access + your selected rooms",
  };

  // Update selected package info
  const selectedRoomInfo = document.getElementById("selectedRoomInfo");
  if (selectedRoomInfo) {
    selectedRoomInfo.innerHTML = `
      <h3>🏖️ Selected: ${packageNames[packageType]}</h3>
      <p><strong>Full Resort Access:</strong> All pools, BBQ areas, sports facilities, gardens, parking & WiFi</p>
      <p><strong>Room Package:</strong> ${packageDescriptions[packageType]}</p>
      <p><strong>Pricing:</strong> ${nightPrice} per night | ${dayPrice} per day</p>
      <p>Please select your booking duration and group details below.</p>
    `;
    selectedRoomInfo.style.display = "block";
    console.log("Updated selectedRoomInfo");
  } else {
    console.error("selectedRoomInfo element not found");
  }

  // Show the booking navigation tab
  window.dashboardManager.toggleBookingTab(true);

  // Navigate to booking details section
  window.dashboardManager.showSection("booking-details");

  // Set the booking tab as active
  const bookingTab = document.getElementById("booking-details-tab");
  if (bookingTab) {
    window.dashboardManager.setActiveNavButton(bookingTab);
  }

  // Scroll to selected room info
  if (selectedRoomInfo) {
    selectedRoomInfo.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }

  // Set minimum dates
  const today = new Date().toISOString().split("T")[0];
  const startDate = document.getElementById("startDate");
  const checkInDate = document.getElementById("checkInDate");
  if (startDate) startDate.min = today;
  if (checkInDate) checkInDate.min = today;
};

// Function for custom room selection
window.selectCustomRooms = function () {
  const selectedRooms = document.querySelectorAll(
    'input[name="customRoom"]:checked'
  );

  if (selectedRooms.length === 0) {
    alert("Please select at least one room for your custom package");
    return;
  }

  let totalNightPrice = 0;
  let totalDayPrice = 0;
  let roomsList = [];

  const roomPrices = {
    deluxe: { night: 3500, day: 2000 },
    suite: { night: 5500, day: 3500 },
    family: { night: 4200, day: 2500 },
    villa: { night: 8500, day: 5000 },
  };

  const roomNames = {
    deluxe: "Deluxe Room",
    suite: "Executive Suite",
    family: "Family Room",
    villa: "Private Villa",
  };

  selectedRooms.forEach((room) => {
    const roomType = room.value;
    totalNightPrice += roomPrices[roomType].night;
    totalDayPrice += roomPrices[roomType].day;
    roomsList.push(roomNames[roomType]);
  });

  // Store custom package data
  selectedPackageData = {
    type: "custom",
    nightPrice: `₱${totalNightPrice.toLocaleString()}`,
    dayPrice: `₱${totalDayPrice.toLocaleString()}`,
    nightPriceValue: totalNightPrice,
    dayPriceValue: totalDayPrice,
    customRooms: roomsList,
  };

  // Update selected package info
  const selectedRoomInfo = document.getElementById("selectedRoomInfo");
  if (selectedRoomInfo) {
    selectedRoomInfo.innerHTML = `
      <h3>🏖️ Selected: Custom Room Package</h3>
      <p><strong>Full Resort Access:</strong> All pools, BBQ areas, sports facilities, gardens, parking & WiFi</p>
      <p><strong>Selected Rooms:</strong> ${roomsList.join(", ")}</p>
      <p><strong>Pricing:</strong> ₱${totalNightPrice.toLocaleString()} per night | ₱${totalDayPrice.toLocaleString()} per day</p>
      <p>Please select your booking duration and group details below.</p>
    `;
    selectedRoomInfo.style.display = "block";
  } else {
    console.error("selectedRoomInfo element not found");
  }

  // Show the booking navigation tab
  window.dashboardManager.toggleBookingTab(true);

  // Navigate to booking details section
  window.dashboardManager.showSection("booking-details");

  // Set the booking tab as active
  const bookingTab = document.getElementById("booking-details-tab");
  if (bookingTab) {
    window.dashboardManager.setActiveNavButton(bookingTab);
  }

  const today = new Date().toISOString().split("T")[0];
  const startDate = document.getElementById("startDate");
  const checkInDate = document.getElementById("checkInDate");
  if (startDate) startDate.min = today;
  if (checkInDate) checkInDate.min = today;
};

// Updated validation function for room package booking
window.validateReservationType = function () {
  const reservationType = document.querySelector(
    'input[name="reservationType"]:checked'
  );

  if (!reservationType) {
    alert("Please select a reservation type (Whole Day or Overnight)");
    return false;
  }

  if (reservationType.value === "whole-day") {
    const startDate = document.getElementById("startDate").value;
    const numberOfDays = document.getElementById("numberOfDays").value;

    if (!startDate || !numberOfDays) {
      alert("Please select start date and number of days for whole day access");
      return false;
    }
  } else if (reservationType.value === "overnight") {
    const checkInDate = document.getElementById("checkInDate").value;
    const numberOfNights = document.getElementById("numberOfNights").value;

    if (!checkInDate || !numberOfNights) {
      alert("Please select check-in date and number of nights");
      return false;
    }
  }

  // Validate group details
  const groupSize = document.getElementById("groupSize").value;
  const groupType = document.getElementById("groupType").value;

  if (!groupSize || !groupType) {
    alert("Please provide group size and type information");
    return false;
  }

  return true;
};

// Updated function to get reservation data
window.getReservationTypeData = function () {
  const reservationType = document.querySelector(
    'input[name="reservationType"]:checked'
  );

  if (reservationType.value === "whole-day") {
    const startDate = document.getElementById("startDate").value;
    const numberOfDays = parseInt(
      document.getElementById("numberOfDays").value
    );

    return {
      isDayUse: true,
      isOvernight: false,
      startDate: startDate,
      numberOfDays: numberOfDays,
      totalPrice: selectedPackageData.dayPriceValue * numberOfDays,
    };
  } else {
    const checkInDate = document.getElementById("checkInDate").value;
    const numberOfNights = parseInt(
      document.getElementById("numberOfNights").value
    );

    return {
      isDayUse: false,
      isOvernight: true,
      checkInDate: checkInDate,
      numberOfNights: numberOfNights,
      totalPrice: selectedPackageData.nightPriceValue * numberOfNights,
    };
  }
};

// Keep backward compatibility
window.bookExclusivePackage = function () {
  // Redirect to all rooms package
  selectRoomPackage("all-rooms", "₱25,000", "₱15,000");
};

window.selectRoom = function (roomType, price) {
  // Redirect to all rooms package
  selectRoomPackage("all-rooms", "₱25,000", "₱15,000");
};

window.goBackToRooms = function () {
  console.log("Going back to room selection");

  // Hide the booking navigation tab
  window.dashboardManager.toggleBookingTab(false);

  // Navigate back to make reservation section
  window.dashboardManager.showSection("my-reservations");

  // Set the Make Reservation tab as active
  const makeReservationTab = document.querySelector(
    '[data-section="my-reservations"]'
  );
  if (makeReservationTab) {
    window.dashboardManager.setActiveNavButton(makeReservationTab);
  }

  // Clear selection
  document.querySelectorAll(".room-package-card").forEach((card) => {
    card.classList.remove("selected");
  });

  // Clear selected package data
  selectedPackageData = null;
};

window.proceedToPayment = function () {
  console.log("Proceeding to payment for room package booking...");

  // First validate reservation type selection
  if (!validateReservationType()) {
    return;
  }

  // Get reservation type data
  const reservationData = getReservationTypeData();

  // Validate payment method
  const paymentMethod = document.querySelector(
    'input[name="paymentMethod"]:checked'
  );

  if (!paymentMethod) {
    alert("Please select a payment method");
    return;
  }

  // Get group information
  const groupSize = document.getElementById("groupSize").value;
  const groupType = document.getElementById("groupType").value;

  // Calculate total and build date info
  let total = reservationData.totalPrice;
  let dateInfo = "";
  let durationText = "";

  if (reservationData.isDayUse) {
    dateInfo = `Start Date: ${reservationData.startDate}`;
    durationText = `${reservationData.numberOfDays} day${
      reservationData.numberOfDays > 1 ? "s" : ""
    }`;
  } else {
    dateInfo = `Check-in: ${reservationData.checkInDate}`;
    durationText = `${reservationData.numberOfNights} night${
      reservationData.numberOfNights > 1 ? "s" : ""
    }`;
  }

  // Generate booking reference
  const bookingRef = "AR-PKG-" + Date.now().toString().slice(-8);

  // Get payment method name
  const paymentMethodName =
    paymentMethod.value === "gcash" ? "GCash" : "Over the Counter";

  // Show confirmation
  const specialRequests = document.getElementById("specialRequests").value;
  const reservationTypeText = reservationData.isDayUse
    ? "Whole Day Exclusive Access"
    : "Overnight Exclusive Access";

  const confirmMessage = `
🏖️ EXCLUSIVE RESORT BOOKING SUMMARY:

- Package: ${reservationTypeText}
- Duration: ${durationText}
- ${dateInfo}
- Group Size: ${groupSize}
- Group Type: ${groupType}
- Total Price: ₱${total.toLocaleString()}
- Payment Method: ${paymentMethodName}
- Booking Reference: ${bookingRef}

✨ INCLUDED:
• All rooms (up to 20 guests)
• Complete resort facilities
• All swimming pools
• BBQ and kitchen areas
• Private parking
• High-speed WiFi

${specialRequests ? "- Special Requests: " + specialRequests : ""}

Confirm exclusive resort booking?
  `;

  if (confirm(confirmMessage)) {
    if (paymentMethod.value === "gcash") {
      alert(`
🎉 EXCLUSIVE RESORT BOOKING CONFIRMED!

Booking Reference: ${bookingRef}
Package: ${reservationTypeText}
Total Amount: ₱${total.toLocaleString()}

💳 GCash Payment Instructions:
📱 Send payment to: 0917-123-4567
👤 Account Name: AR Homes Posadas Farm Resort
💬 Include reference: ${bookingRef}

📧 Confirmation email will be sent with:
• Detailed package inclusions
• Check-in instructions
• Contact information
• Resort layout and facilities guide

After payment, please screenshot the receipt for verification.
      `);
    } else {
      alert(`
🎉 EXCLUSIVE RESORT BOOKING CONFIRMED!

Booking Reference: ${bookingRef}
Package: ${reservationTypeText}
Total Amount: ₱${total.toLocaleString()}

🏨 Over the Counter Payment:
� Pay at: AR Homes Posadas Farm Resort Reception
⏰ Office Hours: 8:00 AM - 6:00 PM (Daily)
📄 Bring: This booking confirmation and valid ID

📧 Confirmation email sent with complete package details.
Your exclusive resort access is secured!
      `);
    }

    // Reset form (optional)
    // document.getElementById('bookingDetailsSection').style.display = 'none';
  }
};

// Update booking summary when dates change
document.addEventListener("DOMContentLoaded", function () {
  const checkInInput = document.getElementById("checkInDate");
  const checkOutInput = document.getElementById("checkOutDate");

  function updateSummary() {
    if (!selectedRoomData || !checkInInput.value || !checkOutInput.value) {
      document.getElementById("summaryNights").textContent = "-";
      document.getElementById("summaryTotal").textContent = "-";
      return;
    }

    const checkIn = new Date(checkInInput.value);
    const checkOut = new Date(checkOutInput.value);

    if (checkOut > checkIn) {
      const nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
      const total = selectedRoomData.priceValue * nights;

      document.getElementById("summaryNights").textContent = nights;
      document.getElementById(
        "summaryTotal"
      ).textContent = `₱${total.toLocaleString()}`;
    } else {
      document.getElementById("summaryNights").textContent = "-";
      document.getElementById("summaryTotal").textContent = "-";
    }
  }

  if (checkInInput && checkOutInput) {
    checkInInput.addEventListener("change", updateSummary);
    checkOutInput.addEventListener("change", updateSummary);

    // Update checkout min date when checkin changes
    checkInInput.addEventListener("change", function () {
      const checkInDate = new Date(this.value);
      checkInDate.setDate(checkInDate.getDate() + 1);
      checkOutInput.min = checkInDate.toISOString().split("T")[0];
    });
  }

  // Payment method selection handlers
  const paymentMethods = document.querySelectorAll(
    'input[name="paymentMethod"]'
  );
  const paymentInstructions = document.getElementById("paymentInstructions");
  const gcashInstructions = document.getElementById("gcashInstructions");
  const otcInstructions = document.getElementById("otcInstructions");

  if (paymentMethods.length > 0) {
    paymentMethods.forEach((method) => {
      method.addEventListener("change", function () {
        if (this.checked) {
          // Show payment instructions section
          paymentInstructions.style.display = "block";

          // Hide all instruction contents first
          gcashInstructions.style.display = "none";
          otcInstructions.style.display = "none";

          // Show relevant instructions
          if (this.value === "gcash") {
            gcashInstructions.style.display = "block";
          } else if (this.value === "otc") {
            otcInstructions.style.display = "block";
          }
        }
      });
    });
  }

  // ===== RESERVATION TYPE SELECTION HANDLERS =====

  // Handle reservation type selection
  const reservationTypes = document.querySelectorAll(
    'input[name="reservationType"]'
  );
  const dayUseTimes = document.getElementById("dayUseTimes");
  const overnightDetails = document.getElementById("overnightDetails");

  if (reservationTypes.length > 0) {
    reservationTypes.forEach((type) => {
      type.addEventListener("change", function () {
        if (this.checked) {
          // Update selected type styling
          document.querySelectorAll(".type-option").forEach((option) => {
            option.classList.remove("active");
          });
          this.closest(".type-option").classList.add("active");

          // Show/hide appropriate form sections
          if (this.value === "dayuse") {
            dayUseTimes.style.display = "block";
            overnightDetails.style.display = "none";
            console.log("Day use selected - showing time slots");
          } else if (this.value === "overnight") {
            dayUseTimes.style.display = "none";
            overnightDetails.style.display = "block";
            console.log("Overnight selected - showing date selection");
          }
        }
      });
    });
  }

  // Handle time slot selection for day use
  const timeSlots = document.querySelectorAll('input[name="timeSlot"]');

  if (timeSlots.length > 0) {
    timeSlots.forEach((slot) => {
      slot.addEventListener("change", function () {
        if (this.checked) {
          // Update selected time slot styling
          document.querySelectorAll(".time-slot").forEach((slotElement) => {
            slotElement.classList.remove("active");
          });
          this.closest(".time-slot").classList.add("active");
          console.log("Time slot selected:", this.value);
        }
      });
    });
  }

  // Set initial state - hide both sections
  if (dayUseTimes) dayUseTimes.style.display = "none";
  if (overnightDetails) overnightDetails.style.display = "none";

  // ===== EXCLUSIVE BOOKING EVENT HANDLERS =====

  // Handle reservation type change (whole day vs overnight)
  const exclusiveReservationTypes = document.querySelectorAll(
    'input[name="reservationType"]'
  );

  if (exclusiveReservationTypes.length > 0) {
    exclusiveReservationTypes.forEach((type) => {
      type.addEventListener("change", function () {
        const durationSection = document.getElementById(
          "durationSelectionSection"
        );
        const groupSection = document.getElementById("groupDetailsSection");
        const wholeDayRow = document.getElementById("wholeDayRow");
        const overnightRow = document.getElementById("overnightRow");

        if (this.checked) {
          // Show duration selection
          if (durationSection) durationSection.style.display = "block";
          if (groupSection) groupSection.style.display = "block";

          // Update type option styling
          document
            .querySelectorAll(".reservation-type-option")
            .forEach((option) => {
              option.classList.remove("active");
            });
          this.closest(".reservation-type-option").classList.add("active");

          // Show appropriate duration fields
          if (this.value === "whole-day") {
            if (wholeDayRow) wholeDayRow.style.display = "flex";
            if (overnightRow) overnightRow.style.display = "none";
            console.log("Whole day selected");
          } else if (this.value === "overnight") {
            if (wholeDayRow) wholeDayRow.style.display = "none";
            if (overnightRow) overnightRow.style.display = "flex";
            console.log("Overnight selected");
          }
        }
      });
    });
  }

  // Initially hide duration and group sections
  const durationSection = document.getElementById("durationSelectionSection");
  const groupSection = document.getElementById("groupDetailsSection");
  if (durationSection) durationSection.style.display = "none";
  if (groupSection) groupSection.style.display = "none";
});

// ===== RESERVATION TYPE HELPER FUNCTIONS =====

// Function to get selected reservation type and related data
window.getReservationTypeData = function () {
  const selectedType = document.querySelector(
    'input[name="reservationType"]:checked'
  );

  if (!selectedType) {
    return null;
  }

  const reservationData = {
    type: selectedType.value,
    isDayUse: selectedType.value === "dayuse",
    isOvernight: selectedType.value === "overnight",
  };

  if (reservationData.isDayUse) {
    const selectedTimeSlot = document.querySelector(
      'input[name="timeSlot"]:checked'
    );
    if (selectedTimeSlot) {
      reservationData.timeSlot = selectedTimeSlot.value;
      reservationData.timeSlotLabel = selectedTimeSlot
        .closest(".time-slot")
        .querySelector(".time-label").textContent;
    }
  } else if (reservationData.isOvernight) {
    const checkInDate = document.getElementById("overnightCheckIn").value;
    const checkOutDate = document.getElementById("overnightCheckOut").value;

    reservationData.checkInDate = checkInDate;
    reservationData.checkOutDate = checkOutDate;

    if (checkInDate && checkOutDate) {
      const nights = Math.ceil(
        (new Date(checkOutDate) - new Date(checkInDate)) / (1000 * 60 * 60 * 24)
      );
      reservationData.nights = nights;
    }
  }

  return reservationData;
};

// Function to validate reservation type selection
window.validateReservationType = function () {
  const reservationData = getReservationTypeData();

  if (!reservationData) {
    alert("Please select a reservation type (Day Use or Overnight)");
    return false;
  }

  if (reservationData.isDayUse) {
    if (!reservationData.timeSlot) {
      alert("Please select a time slot for your day use reservation");
      return false;
    }
  } else if (reservationData.isOvernight) {
    if (!reservationData.checkInDate || !reservationData.checkOutDate) {
      alert(
        "Please select both check-in and check-out dates for overnight stay"
      );
      return false;
    }

    if (
      new Date(reservationData.checkOutDate) <=
      new Date(reservationData.checkInDate)
    ) {
      alert("Check-out date must be after check-in date");
      return false;
    }
  }

  return true;
};

// ===== BOOKING NAVIGATION FUNCTIONS =====

// Function to hide booking details and go back to room selection
window.hideBookingDetails = function () {
  console.log("Hiding booking details...");

  // Hide the booking form
  const bookingSection = document.getElementById("bookingDetailsSection");
  if (bookingSection) {
    bookingSection.style.display = "none";
  }

  // Hide the selected room info
  const selectedRoomInfo = document.getElementById("selectedRoomInfo");
  if (selectedRoomInfo) {
    selectedRoomInfo.style.display = "none";
  }

  // Hide the booking navigation tab
  window.dashboardManager.toggleBookingTab(false);

  // Scroll back to room selection
  const reservationSection = document.getElementById("my-reservations-section");
  if (reservationSection) {
    reservationSection.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }

  // Reset form
  const form = document.querySelector(".booking-form");
  if (form) {
    form.reset();
  }

  // Remove selection from room cards
  document.querySelectorAll(".room-package-card").forEach((card) => {
    card.classList.remove("selected");
  });
};

// Function to handle proceed to payment
window.proceedToPayment = function () {
  console.log("Processing payment...");

  // Validate booking form first
  const form = document.querySelector(".booking-form");
  if (!form) {
    alert("Booking form not found");
    return;
  }

  // Basic validation
  const reservationType = document.querySelector(
    'input[name="reservationType"]:checked'
  );
  const paymentMethod = document.querySelector(
    'input[name="paymentMethod"]:checked'
  );

  if (!reservationType) {
    alert("Please select a reservation type (Whole Day or Overnight)");
    return;
  }

  if (!paymentMethod) {
    alert("Please select a payment method");
    return;
  }

  // Additional validation based on reservation type
  if (reservationType.value === "whole-day") {
    const startDate = document.getElementById("startDate").value;
    const numberOfDays = document.getElementById("numberOfDays").value;

    if (!startDate || !numberOfDays) {
      alert("Please fill in all required fields for day use booking");
      return;
    }
  } else if (reservationType.value === "overnight") {
    const checkInDate = document.getElementById("checkInDate").value;
    const numberOfNights = document.getElementById("numberOfNights").value;

    if (!checkInDate || !numberOfNights) {
      alert("Please fill in all required fields for overnight booking");
      return;
    }
  }

  const groupSize = document.getElementById("groupSize").value;
  const groupType = document.getElementById("groupType").value;

  if (!groupSize || !groupType) {
    alert("Please fill in group details");
    return;
  }

  // If validation passes, show confirmation
  alert(
    "Booking submitted successfully! You will receive a confirmation email shortly."
  );

  // Reset form and hide booking section
  setTimeout(() => {
    // Reset the form
    form.reset();

    // Hide booking details
    hideBookingDetails();

    // Go to dashboard
    if (window.dashboardManager) {
      window.dashboardManager.showSection("dashboard");

      // Set dashboard button as active
      const dashboardButton = document.querySelector(
        '[data-section="dashboard"]'
      );
      if (dashboardButton) {
        window.dashboardManager.setActiveNavButton(dashboardButton);
      }
    }
  }, 2000);
};
