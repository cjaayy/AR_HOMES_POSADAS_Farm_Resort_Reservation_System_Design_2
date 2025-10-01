// ===== SIMPLE DASHBOARD JAVASCRIPT =====
console.log("🚀 Loading Simple Dashboard Script...");

// Global functions that need to be available immediately
window.testPopup = function () {
  console.log("🧪 Test button clicked!");
  alert("Test button works! JavaScript is loading properly.");

  const popup = document.getElementById("popupContentArea");
  if (popup) {
    popup.style.display = "block";
    popup.classList.add("active");

    const title = document.getElementById("popupTitle");
    const content = document.getElementById("popupBody");

    if (title) title.textContent = "TEST SUCCESSFUL";
    if (content)
      content.innerHTML =
        '<h2 style="color: green;">✅ POPUP IS WORKING!</h2><p>The popup system is functional.</p>';

    console.log("✅ Popup test completed");
  } else {
    console.error("❌ Popup element not found");
    alert("Error: Popup element not found");
  }
};

window.logout = function () {
  console.log("🚪 Logout clicked");
  alert("Logout clicked - redirecting to login page");
  window.location.href = "index.html";
};

window.showPopup = function (section) {
  console.log("📋 showPopup called for section:", section);

  const popup = document.getElementById("popupContentArea");
  const title = document.getElementById("popupTitle");
  const content = document.getElementById("popupBody");

  if (!popup || !title || !content) {
    console.error("❌ Required popup elements not found");
    alert("Error: Popup elements missing");
    return;
  }

  // Get the content template
  const contentTemplate = document.getElementById(section + "-content");

  let titleText = "Unknown Section";
  let contentHTML = "<p>Content not found for: " + section + "</p>";

  // Set title based on section
  switch (section) {
    case "dashboard":
      titleText = "Dashboard Overview";
      break;
    case "make-reservation":
      titleText = "Make New Reservation";
      break;
    case "my-reservations":
      titleText = "My Reservations";
      break;
    case "promotions":
      titleText = "Special Promotions";
      break;
    case "profile":
      titleText = "Profile Settings";
      break;
  }

  // Get content from template if it exists
  if (contentTemplate) {
    contentHTML = contentTemplate.innerHTML;
    console.log("✅ Found content template for", section);
  } else {
    console.warn("⚠️ No content template found for", section);
    contentHTML =
      '<div style="padding: 2rem; text-align: center;"><h3>' +
      titleText +
      "</h3><p>This section is under development.</p><p>Section ID: " +
      section +
      "</p></div>";
  }

  // Set the content
  title.textContent = titleText;
  content.innerHTML = contentHTML;

  // Show the popup
  popup.style.display = "block";
  popup.classList.add("active");

  // Update active button
  document
    .querySelectorAll(".nav-btn")
    .forEach((btn) => btn.classList.remove("active"));
  const activeBtn = document.querySelector(`[data-section="${section}"]`);
  if (activeBtn) {
    activeBtn.classList.add("active");
  }

  console.log("✅ Popup displayed for section:", section);
};

window.closePopup = function () {
  console.log("❌ Closing popup");
  const popup = document.getElementById("popupContentArea");
  if (popup) {
    popup.classList.remove("active");
    popup.style.display = "none";
  }
};

// Wait for DOM to load
document.addEventListener("DOMContentLoaded", function () {
  console.log("📄 DOM loaded, setting up event listeners...");

  // Setup navigation buttons
  const navButtons = document.querySelectorAll(".nav-btn");
  console.log("🔘 Found", navButtons.length, "navigation buttons");

  navButtons.forEach((button, index) => {
    const section = button.getAttribute("data-section");
    console.log(`Setting up button ${index + 1}: ${section}`);

    button.addEventListener("click", function (e) {
      e.preventDefault();
      console.log("🖱️ Navigation button clicked:", section);
      showPopup(section);
    });
  });

  // Setup close button
  const closeBtn = document.getElementById("closePopup");
  if (closeBtn) {
    closeBtn.addEventListener("click", closePopup);
    console.log("✅ Close button listener attached");
  }

  // Setup ESC key
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      closePopup();
    }
  });

  // Show dashboard by default after a short delay
  setTimeout(() => {
    console.log("📊 Showing default dashboard content...");
    showPopup("dashboard");
  }, 1000);

  console.log("✅ All event listeners setup complete");
});

console.log("✅ Simple Dashboard Script Loaded");
