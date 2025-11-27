/**
 * Frontend Configuration
 * Auto-detects environment and sets appropriate base URLs
 */

// Auto-detect environment
const isLocalhost =
  window.location.hostname === "localhost" ||
  window.location.hostname === "127.0.0.1" ||
  window.location.hostname === "";

// Set base URL based on environment
const BASE_URL = isLocalhost
  ? "http://localhost"
  : "https://arhomesposadas.cjaayy.dev";

// API endpoint base
const API_BASE = BASE_URL;

// Export for use in other scripts
window.CONFIG = {
  BASE_URL: BASE_URL,
  API_BASE: API_BASE,
  IS_LOCALHOST: isLocalhost,
  IS_PRODUCTION: !isLocalhost,
};

// Log configuration
console.log(
  "%c🌐 Environment Configuration",
  "color: #4CAF50; font-weight: bold; font-size: 14px;"
);
console.log("Environment:", isLocalhost ? "LOCALHOST" : "PRODUCTION");
console.log("Base URL:", BASE_URL);
console.log("API Base:", API_BASE);
