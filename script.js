// DOM Elements
const loginForm = document.getElementById("loginForm");
const emailInput = document.getElementById("email");
const passwordInput = document.getElementById("password");
const loginBtn = document.querySelector(".login-btn");
const toggleIcon = document.getElementById("toggleIcon");

// Slideshow Elements
const slides = document.querySelectorAll(".slide");
const indicators = document.querySelectorAll(".indicator");
let currentSlide = 0;
let slideInterval;

// Initialize slideshow
function initSlideshow() {
  // Start automatic slideshow
  startSlideshow();

  // Add click handlers to indicators
  indicators.forEach((indicator, index) => {
    indicator.addEventListener("click", () => {
      goToSlide(index);
    });
  });
}

// Start automatic slideshow
function startSlideshow() {
  slideInterval = setInterval(() => {
    nextSlide();
  }, 4000); // Change slide every 4 seconds
}

// Stop slideshow
function stopSlideshow() {
  clearInterval(slideInterval);
}

// Go to specific slide
function goToSlide(index) {
  // Remove active class from current slide and indicator
  slides[currentSlide].classList.remove("active");
  indicators[currentSlide].classList.remove("active");

  // Add flash transition effect
  slides[currentSlide].classList.add("flash-transition");

  // Remove flash transition after animation
  setTimeout(() => {
    slides[currentSlide].classList.remove("flash-transition");
  }, 1000);

  // Update current slide
  currentSlide = index;

  // Add active class to new slide and indicator
  slides[currentSlide].classList.add("active");
  indicators[currentSlide].classList.add("active");

  // Restart slideshow timer
  stopSlideshow();
  startSlideshow();
}

// Go to next slide
function nextSlide() {
  const nextIndex = (currentSlide + 1) % slides.length;
  goToSlide(nextIndex);
}

// Go to previous slide
function prevSlide() {
  const prevIndex = (currentSlide - 1 + slides.length) % slides.length;
  goToSlide(prevIndex);
}

// Add keyboard navigation for slideshow
document.addEventListener("keydown", (e) => {
  if (e.key === "ArrowLeft") {
    prevSlide();
  } else if (e.key === "ArrowRight") {
    nextSlide();
  }
});

// Pause slideshow on hover
const imageSection = document.querySelector(".image-section");
if (imageSection) {
  imageSection.addEventListener("mouseenter", stopSlideshow);
  imageSection.addEventListener("mouseleave", startSlideshow);
}

// Initialize slideshow when page loads
document.addEventListener("DOMContentLoaded", initSlideshow);

// Demo credentials auto-fill function
function fillDemoCredentials(type) {
  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");

  if (type === "demo") {
    emailInput.value = "demo@guest.com";
    passwordInput.value = "demo123";
  } else if (type === "admin") {
    emailInput.value = "admin@resort.com";
    passwordInput.value = "admin123";
  }

  // Add visual feedback
  emailInput.style.background = "rgba(102, 126, 234, 0.1)";
  passwordInput.style.background = "rgba(102, 126, 234, 0.1)";

  // Reset background after animation
  setTimeout(() => {
    emailInput.style.background = "";
    passwordInput.style.background = "";
  }, 1000);

  // Focus on login button
  document.querySelector(".login-btn").focus();
}

// Form validation and submission
loginForm.addEventListener("submit", function (e) {
  e.preventDefault();

  // Get form values
  const email = emailInput.value.trim();
  const password = passwordInput.value.trim();

  // Reset error states
  clearErrors();

  // Validation
  let isValid = true;

  if (!email) {
    showError(emailInput, "Please enter your username or email");
    isValid = false;
  } else if (!isValidEmail(email) && !isValidUsername(email)) {
    showError(emailInput, "Please enter a valid email or username");
    isValid = false;
  }

  if (!password) {
    showError(passwordInput, "Please enter your password");
    isValid = false;
  } else if (password.length < 6) {
    showError(passwordInput, "Password must be at least 6 characters");
    isValid = false;
  }

  if (!isValid) {
    // Show alert for empty fields as requested
    alert("Please fill in all required fields correctly!");
    return;
  }

  // If validation passes, simulate login
  handleLogin(email, password);
});

// Handle login with database authentication
function handleLogin(email, password) {
  // Add loading state
  loginBtn.classList.add("loading");
  loginBtn.disabled = true;

  const cleanEmail = email.trim().toLowerCase();

  // Check if this is an admin login attempt
  const isAdminLogin =
    cleanEmail === "admin" || cleanEmail === "admin@resort.com";

  if (isAdminLogin) {
    // Admin login - use admin endpoint
    console.log("🔧 Attempting admin login...");

    fetch("admin/login.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        username: email.trim(),
        password: password,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        loginBtn.classList.remove("loading");
        loginBtn.disabled = false;

        if (data.success) {
          console.log("✅ Admin login successful");
          loginBtn.classList.add("success");
          loginBtn.innerHTML =
            '<i class="fas fa-check"></i> Admin Access Granted!';

          setTimeout(() => {
            window.location.href = "admin/dashboard.php";
          }, 1000);
        } else {
          console.log("❌ Admin login failed:", data.message);
          showLoginError(data.message || "Invalid admin credentials");
        }
      })
      .catch((error) => {
        console.error("❌ Admin login error:", error);
        loginBtn.classList.remove("loading");
        loginBtn.disabled = false;
        showLoginError("Network error. Please try again.");
      });
  } else {
    // Guest user login - use user endpoint
    console.log("👤 Attempting guest user login...");

    const requestBody = {
      usernameOrEmail: email.trim(),
      password: password,
    };

    console.log("📤 Sending request:", requestBody);

    fetch("user/login.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(requestBody),
    })
      .then((response) => {
        console.log("📡 Response status:", response.status);
        return response.text();
      })
      .then((responseText) => {
        console.log("📄 Raw response:", responseText);

        let data;
        try {
          data = JSON.parse(responseText);
        } catch (e) {
          console.error("❌ Failed to parse JSON:", e);
          loginBtn.classList.remove("loading");
          loginBtn.disabled = false;
          showLoginError("Server error. Please try again.");
          return;
        }

        console.log("📦 Parsed data:", data);
        loginBtn.classList.remove("loading");
        loginBtn.disabled = false;

        if (data.success) {
          console.log("✅ Guest user login successful");
          loginBtn.classList.add("success");
          loginBtn.innerHTML = '<i class="fas fa-check"></i> Welcome Back!';

          setTimeout(() => {
            window.location.href = "dashboard.html";
          }, 1000);
        } else {
          console.log("❌ Guest login failed:", data.message);
          showLoginError(data.message || "Invalid credentials");
        }
      })
      .catch((error) => {
        console.error("❌ Guest login error:", error);
        loginBtn.classList.remove("loading");
        loginBtn.disabled = false;
        showLoginError("Network error. Please try again.");
      });
  }
}

// Show login error message
function showLoginError(message) {
  loginBtn.classList.add("error");
  loginBtn.innerHTML = '<i class="fas fa-times"></i> ' + message;

  setTimeout(() => {
    loginBtn.classList.remove("error");
    loginBtn.innerHTML = "Sign In";
  }, 3000);
}

// Email validation
function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

// Username validation (alphanumeric and underscore)
function isValidUsername(username) {
  const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
  return usernameRegex.test(username);
}

// Show error state
function showError(input, message) {
  const wrapper = input.closest(".input-wrapper");
  wrapper.classList.add("error");

  // Create error message element if it doesn't exist
  let errorMsg = wrapper.nextElementSibling;
  if (!errorMsg || !errorMsg.classList.contains("error-message")) {
    errorMsg = document.createElement("div");
    errorMsg.className = "error-message";
    errorMsg.style.color = "#ff6b6b";
    errorMsg.style.fontSize = "0.8rem";
    errorMsg.style.marginTop = "5px";
    errorMsg.style.marginLeft = "5px";
    wrapper.parentNode.insertBefore(errorMsg, wrapper.nextSibling);
  }
  errorMsg.textContent = message;

  // Remove error state when user starts typing
  input.addEventListener(
    "input",
    function () {
      wrapper.classList.remove("error");
      if (errorMsg) {
        errorMsg.remove();
      }
    },
    { once: true }
  );
}

// Clear all error states
function clearErrors() {
  const errorWrappers = document.querySelectorAll(".input-wrapper.error");
  errorWrappers.forEach((wrapper) => {
    wrapper.classList.remove("error");
  });

  const errorMessages = document.querySelectorAll(".error-message");
  errorMessages.forEach((msg) => msg.remove());
}

// Password toggle functionality
function togglePassword() {
  const passwordField = document.getElementById("password");
  const toggleIcon = document.getElementById("toggleIcon");

  if (passwordField.type === "password") {
    passwordField.type = "text";
    toggleIcon.classList.remove("fa-eye");
    toggleIcon.classList.add("fa-eye-slash");
  } else {
    passwordField.type = "password";
    toggleIcon.classList.remove("fa-eye-slash");
    toggleIcon.classList.add("fa-eye");
  }
}

// Input focus effects
document.querySelectorAll(".input-wrapper input").forEach((input) => {
  input.addEventListener("focus", function () {
    this.closest(".input-wrapper").style.transform = "translateY(-1px)";
    this.closest(".input-wrapper").style.boxShadow =
      "0 4px 12px rgba(0,0,0,0.1)";
  });

  input.addEventListener("blur", function () {
    this.closest(".input-wrapper").style.transform = "translateY(0)";
    this.closest(".input-wrapper").style.boxShadow = "none";
  });
});

// Remember me functionality
const rememberCheckbox = document.getElementById("remember");
rememberCheckbox.addEventListener("change", function () {
  if (this.checked) {
    console.log("Remember me enabled");
    // In a real application, you would set localStorage or cookie preferences here
  } else {
    console.log("Remember me disabled");
  }
});

// Handle "Forgot Password" link
document.querySelector(".forgot-link").addEventListener("click", function (e) {
  e.preventDefault();
  openForgotPasswordModal();
});

// Register link now works with standard HTML navigation to registration.html

// Add keyboard navigation
document.addEventListener("keydown", function (e) {
  if (e.key === "Enter" && e.target.tagName !== "BUTTON") {
    loginForm.dispatchEvent(new Event("submit"));
  }
});

// Auto-focus first input on page load
window.addEventListener("load", function () {
  emailInput.focus();
});

// Add smooth scrolling for mobile keyboards
window.addEventListener("resize", function () {
  if (window.innerWidth <= 768) {
    const activeElement = document.activeElement;
    if (activeElement && activeElement.tagName === "INPUT") {
      setTimeout(() => {
        activeElement.scrollIntoView({ behavior: "smooth", block: "center" });
      }, 100);
    }
  }
});

// Demo credentials hint (development only)
console.log(`
🏖️ AR Homes Posadas Farm Resort - Login Demo
=============================================
This is a frontend-only demonstration.

Demo credentials (for testing):
📧 Email: demo@arhomes.com
🔑 Password: demo123

You can also use any valid email format or username.
The form includes validation for empty fields and basic format checking.
`);

// Add a subtle animation to the logo
const logo = document.querySelector(".logo");
if (logo) {
  logo.addEventListener("mouseenter", function () {
    this.style.transform = "scale(1.1) rotate(5deg)";
    this.style.transition = "all 0.3s ease";
  });

  logo.addEventListener("mouseleave", function () {
    this.style.transform = "scale(1) rotate(0deg)";
  });
}

// Prevent form submission with Enter key on password toggle button
document
  .querySelector(".password-toggle")
  .addEventListener("keydown", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      togglePassword();
    }
  });

// ===== MAP MODAL FUNCTIONALITY =====

// Open location map modal
function openLocationMap() {
  const modal = document.getElementById("mapModal");
  modal.classList.add("show");
  modal.style.display = "flex";

  // Prevent body scrolling when modal is open
  document.body.style.overflow = "hidden";

  // Add escape key listener
  document.addEventListener("keydown", handleMapModalEscape);

  // Load map with a small delay for better animation
  setTimeout(() => {
    loadResortMap();
  }, 300);
}

// Close location map modal
function closeLocationMap() {
  const modal = document.getElementById("mapModal");
  modal.classList.remove("show");

  // Restore body scrolling
  document.body.style.overflow = "auto";

  // Remove escape key listener
  document.removeEventListener("keydown", handleMapModalEscape);

  // Hide modal after animation
  setTimeout(() => {
    modal.style.display = "none";
  }, 300);
}

// Handle escape key for modal
function handleMapModalEscape(e) {
  if (e.key === "Escape") {
    closeLocationMap();
  }
}

// Load resort map (you can customize the coordinates)
function loadResortMap() {
  const mapFrame = document.getElementById("resortMap");

  // 🎯 AR HOMES POSADAS FARM RESORT EXACT COORDINATES
  // Original: 14°26'24.2"N 120°27'39.2"E
  const latitude = 14.4400556; // 📍 14°26'24.2"N
  const longitude = 120.4608889; // 📍 120°27'39.2"E

  // Option 1: Basic Google Maps embed with pin
  const mapUrl = `https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d3925.123!2d${longitude}!3d${latitude}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zAR+Homes+Posadas+Farm+Resort!5e0!3m2!1sen!2sph!4v${Date.now()}!5m2!1sen!2sph`;

  // Option 2: Google Maps with custom marker and zoom
  // const mapUrlWithMarker = `https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY&q=${latitude},${longitude}&zoom=15&maptype=satellite`;

  mapFrame.src = mapUrl;

  // Add load event listener to show when map is ready
  mapFrame.onload = function () {
    console.log(
      "🗺️ Resort map loaded successfully at coordinates:",
      latitude,
      longitude
    );
  };
}

// Open directions to resort
function openDirections() {
  // 🎯 AR HOMES POSADAS FARM RESORT EXACT COORDINATES
  // Original: 14°26'24.2"N 120°27'39.2"E
  const latitude = 14.4400556; // 📍 14°26'24.2"N
  const longitude = 120.4608889; // 📍 120°27'39.2"E

  // Create Google Maps directions URL to your pinned location
  const directionsUrl = `https://www.google.com/maps/dir/?api=1&destination=${latitude},${longitude}&destination_place_id=AR+Homes+Posadas+Farm+Resort`;

  // Open in new tab
  window.open(directionsUrl, "_blank");

  // Show success message
  showMapNotification("Opening directions in Google Maps...", "success");
}

// Share resort location
function shareLocation() {
  const resortInfo = {
    name: "AR Homes Posadas Farm Resort",
    address: "2488 Maangay, Balon-Anito, Mariveles, Bataan, Philippines",
    phone: "+63 (32) 123-4567",
    website: window.location.origin,
  };

  // Try to use Web Share API if available
  if (navigator.share) {
    navigator
      .share({
        title: resortInfo.name,
        text: `Check out ${resortInfo.name} - ${resortInfo.address}`,
        url: window.location.origin,
      })
      .then(() => {
        showMapNotification("Location shared successfully!", "success");
      })
      .catch((error) => {
        console.log("Error sharing:", error);
        fallbackShare(resortInfo);
      });
  } else {
    // Fallback for browsers that don't support Web Share API
    fallbackShare(resortInfo);
  }
}

// Fallback share method
function fallbackShare(resortInfo) {
  const shareText = `${resortInfo.name}\n${resortInfo.address}\nPhone: ${resortInfo.phone}\nWebsite: ${resortInfo.website}`;

  // Copy to clipboard
  if (navigator.clipboard) {
    navigator.clipboard
      .writeText(shareText)
      .then(() => {
        showMapNotification("Resort location copied to clipboard!", "success");
      })
      .catch(() => {
        showMapNotification("Please copy manually: " + shareText, "info");
      });
  } else {
    // Older browser fallback
    const textArea = document.createElement("textarea");
    textArea.value = shareText;
    document.body.appendChild(textArea);
    textArea.select();

    try {
      document.execCommand("copy");
      showMapNotification("Resort location copied to clipboard!", "success");
    } catch (err) {
      showMapNotification("Please copy manually: " + shareText, "info");
    }

    document.body.removeChild(textArea);
  }
}

// Show map notification
function showMapNotification(message, type = "info") {
  // Create notification element
  const notification = document.createElement("div");
  notification.className = `map-notification map-notification-${type}`;
  notification.textContent = message;

  // Style the notification
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: ${getNotificationColor(type)};
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    z-index: 10001;
    font-weight: 500;
    font-size: 0.9rem;
    max-width: 300px;
    animation: slideInRight 0.3s ease-out;
  `;

  // Add to DOM
  document.body.appendChild(notification);

  // Remove after 3 seconds
  setTimeout(() => {
    notification.style.animation = "slideOutRight 0.3s ease-in";
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 300);
  }, 3000);
}

// Get notification color based on type
function getNotificationColor(type) {
  switch (type) {
    case "success":
      return "linear-gradient(135deg, #28a745, #20c997)";
    case "error":
      return "linear-gradient(135deg, #dc3545, #e74c3c)";
    case "warning":
      return "linear-gradient(135deg, #ffc107, #f39c12)";
    default:
      return "linear-gradient(135deg, #667eea, #764ba2)";
  }
}

// Close modal when clicking outside
document.addEventListener("click", function (e) {
  const modal = document.getElementById("mapModal");
  if (e.target === modal) {
    closeLocationMap();
  }
});

// Add CSS animation for notifications
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

// Add map icon hover effect
const mapIconBtn = document.querySelector(".map-icon-btn");
if (mapIconBtn) {
  mapIconBtn.addEventListener("mouseenter", function () {
    this.style.animation = "pulse 1s infinite";
  });

  mapIconBtn.addEventListener("mouseleave", function () {
    this.style.animation = "";
  });
}

// Add pulse animation
const pulseAnimation = document.createElement("style");
pulseAnimation.textContent = `
  @keyframes pulse {
    0% { box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2); }
    50% { box-shadow: 0 6px 25px rgba(102, 126, 234, 0.4); }
    100% { box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2); }
  }
`;
document.head.appendChild(pulseAnimation);

console.log("🗺️ Map functionality loaded successfully!");
console.log("Click the map icon next to the logo to view resort location.");

// Make functions globally available
window.openLocationMap = openLocationMap;
window.closeLocationMap = closeLocationMap;
window.openDirections = openDirections;
window.shareLocation = shareLocation;

// ===== FORGOT PASSWORD MODAL FUNCTIONALITY =====

// Open forgot password modal
function openForgotPasswordModal() {
  const modal = document.getElementById("forgotPasswordModal");
  modal.style.display = "flex";

  // Prevent body scrolling
  document.body.style.overflow = "hidden";

  // Focus on email input
  setTimeout(() => {
    document.getElementById("resetEmail").focus();
  }, 100);

  // Add escape key listener
  document.addEventListener("keydown", handleForgotPasswordEscape);
}

// Close forgot password modal
function closeForgotPasswordModal() {
  const modal = document.getElementById("forgotPasswordModal");
  modal.style.display = "none";

  // Restore body scrolling
  document.body.style.overflow = "auto";

  // Clear form
  document.getElementById("forgotPasswordForm").reset();

  // Remove escape key listener
  document.removeEventListener("keydown", handleForgotPasswordEscape);

  // Reset button state
  const sendResetBtn = document.getElementById("sendResetBtn");
  sendResetBtn.disabled = false;
  sendResetBtn.innerHTML =
    '<span>Send Reset Link</span><i class="fas fa-paper-plane"></i>';
}

// Handle escape key for forgot password modal
function handleForgotPasswordEscape(e) {
  if (e.key === "Escape") {
    closeForgotPasswordModal();
  }
}

// Handle forgot password form submission
document
  .getElementById("forgotPasswordForm")
  .addEventListener("submit", function (e) {
    e.preventDefault();

    const resetEmail = document.getElementById("resetEmail").value.trim();
    const sendResetBtn = document.getElementById("sendResetBtn");

    // Validation
    if (!resetEmail) {
      alert("Please enter your email address");
      return;
    }

    if (!isValidEmail(resetEmail)) {
      alert("Please enter a valid email address");
      return;
    }

    // Disable button and show loading state
    sendResetBtn.disabled = true;
    sendResetBtn.innerHTML =
      '<i class="fas fa-spinner fa-spin"></i> Sending...';

    // Send password reset request
    fetch("user/forgot_password.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ email: resetEmail }),
    })
      .then((response) => response.json())
      .then((data) => {
        sendResetBtn.disabled = false;
        sendResetBtn.innerHTML =
          '<span>Send Reset Link</span><i class="fas fa-paper-plane"></i>';

        if (data.success) {
          // Show success message
          alert(
            "Password reset instructions have been sent to your email.\n\nFor development: Check the browser console for the reset link."
          );

          // Log reset link for development
          if (data.data && data.data.reset_link) {
            console.log("🔐 PASSWORD RESET LINK (Development Mode)");
            console.log("==========================================");
            console.log("Reset Link:", data.data.reset_link);
            console.log("Expires At:", data.data.expires_at);
            console.log("Note:", data.data.note);
            console.log("==========================================");
            console.log(
              "\nClick the link above or copy it to your browser to reset your password."
            );

            // Show modal with reset link for development
            showResetLinkModal(data.data.reset_link);
          }

          closeForgotPasswordModal();
        } else {
          alert(data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Failed to send reset link. Please try again.");
        sendResetBtn.disabled = false;
        sendResetBtn.innerHTML =
          '<span>Send Reset Link</span><i class="fas fa-paper-plane"></i>';
      });
  });

// Show reset link modal for development
function showResetLinkModal(resetLink) {
  const modalHtml = `
    <div id="resetLinkModal" style="
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10000;
    ">
      <div style="
        background: white;
        padding: 30px;
        border-radius: 15px;
        max-width: 600px;
        width: 90%;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
      ">
        <div style="text-align: center; margin-bottom: 20px;">
          <div style="
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 30px;
          ">
            <i class="fas fa-envelope"></i>
          </div>
          <h3 style="color: #333; margin-bottom: 10px;">Password Reset Link Generated</h3>
          <p style="color: #666; font-size: 0.9rem;">
            For development: Use this link to reset your password
          </p>
        </div>
        
        <div style="
          background: #f8f9fa;
          padding: 15px;
          border-radius: 8px;
          margin-bottom: 20px;
          word-break: break-all;
          font-size: 0.85rem;
          color: #333;
        ">
          ${resetLink}
        </div>
        
        <div style="display: flex; gap: 10px; justify-content: center;">
          <button onclick="copyResetLink('${resetLink}')" style="
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
          ">
            <i class="fas fa-copy"></i> Copy Link
          </button>
          <button onclick="window.location.href='${resetLink}'" style="
            background: linear-gradient(135deg, #00b894, #00cec9);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
          ">
            <i class="fas fa-arrow-right"></i> Go to Reset Page
          </button>
        </div>
        
        <button onclick="closeResetLinkModal()" style="
          background: transparent;
          border: 2px solid #ddd;
          color: #666;
          padding: 10px 24px;
          border-radius: 8px;
          cursor: pointer;
          font-weight: 600;
          width: 100%;
          margin-top: 10px;
        ">
          Close
        </button>
      </div>
    </div>
  `;

  document.body.insertAdjacentHTML("beforeend", modalHtml);
}

// Copy reset link to clipboard
function copyResetLink(link) {
  navigator.clipboard
    .writeText(link)
    .then(() => {
      alert("Reset link copied to clipboard!");
    })
    .catch((err) => {
      console.error("Failed to copy:", err);
      alert("Failed to copy link. Please copy it manually from the console.");
    });
}

// Close reset link modal
function closeResetLinkModal() {
  const modal = document.getElementById("resetLinkModal");
  if (modal) {
    modal.remove();
  }
}

// Close modal when clicking outside
document.addEventListener("click", function (e) {
  const forgotModal = document.getElementById("forgotPasswordModal");
  if (e.target === forgotModal) {
    closeForgotPasswordModal();
  }
});

// Make functions globally available
window.openForgotPasswordModal = openForgotPasswordModal;
window.closeForgotPasswordModal = closeForgotPasswordModal;
window.copyResetLink = copyResetLink;
window.closeResetLinkModal = closeResetLinkModal;

console.log("🔐 Forgot Password functionality loaded successfully!");
