<?php
/**
 * Guest Dashboard
 * AR Homes Posadas Farm Resort Reservation System
 */

session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Redirect to login page
    header('Location: index.html');
    exit;
}

// Check session timeout (1 hour)
$timeout = 3600;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    // Session has expired
    session_unset();
    session_destroy();
    header('Location: index.html?session_expired=1');
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Check database connection
try {
    require_once 'config/connection.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    // Verify user still exists and is active
    $checkUser = $conn->prepare("SELECT is_active FROM users WHERE user_id = :user_id");
    $userId = $_SESSION['user_id'];
    $checkUser->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $checkUser->execute();
    
    if ($checkUser->rowCount() === 0) {
        // User no longer exists
        session_unset();
        session_destroy();
        header('Location: index.html');
        exit;
    }
    
    $user = $checkUser->fetch();
    if ($user['is_active'] != 1) {
        // Account is inactive
        session_unset();
        session_destroy();
        header('Location: index.html?account_inactive=1');
        exit;
    }
    
} catch (PDOException $e) {
    // Database connection failed - show error
    die('Database Connection Error: Cannot load dashboard without database connection. Please make sure XAMPP MySQL is running.');
}

// Get user data from session
$userFullName = $_SESSION['user_full_name'] ?? 'Guest';
$userGivenName = $_SESSION['user_given_name'] ?? 'Guest';
$userEmail = $_SESSION['user_email'] ?? '';
$userPhone = $_SESSION['user_phone'] ?? '';
$userName = $_SESSION['user_username'] ?? '';

// Calculate member since year
$memberSince = isset($_SESSION['login_time']) ? date('Y', $_SESSION['login_time']) : date('Y');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Dashboard - AR Homes Posadas Farm Resort</title>

    <!-- Favicon -->
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <link
      rel="icon"
      type="image/png"
      sizes="32x32"
      href="logo/ChatGPT Image Sep 15, 2025, 10_25_25 PM.png"
    />
    <link
      rel="icon"
      type="image/png"
      sizes="16x16"
      href="logo/ChatGPT Image Sep 15, 2025, 10_25_25 PM.png"
    />
    <link
      rel="apple-touch-icon"
      sizes="180x180"
      href="logo/ChatGPT Image Sep 15, 2025, 10_25_25 PM.png"
    />
    <link
      rel="shortcut icon"
      href="logo/ChatGPT Image Sep 15, 2025, 10_25_25 PM.png"
    />
    <link rel="manifest" href="site.webmanifest" />

    <!-- Meta tags -->
    <meta name="theme-color" content="#667eea" />
    <meta
      name="description"
      content="User Dashboard for AR Homes Posadas Farm Resort - Manage your reservations and profile."
    />
    <meta
      name="keywords"
      content="dashboard, user, resort reservations, AR Homes, Posadas Farm"
    />
    <meta name="author" content="AR Homes Posadas Farm Resort" />

    <!-- Open Graph meta tags for social sharing -->
    <meta
      property="og:title"
      content="User Dashboard - AR Homes Posadas Farm Resort"
    />
    <meta
      property="og:description"
      content="Manage your reservations and profile at AR Homes Posadas Farm Resort."
    />
    <meta
      property="og:image"
      content="logo/ChatGPT Image Sep 15, 2025, 10_25_25 PM.png"
    />
    <meta property="og:url" content="#" />
    <meta property="og:type" content="website" />

    <!-- Twitter Card meta tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta
      name="twitter:title"
      content="User Dashboard - AR Homes Posadas Farm Resort"
    />
    <meta
      name="twitter:description"
      content="Manage your reservations and profile at AR Homes Posadas Farm Resort."
    />
    <meta
      name="twitter:image"
      content="logo/ChatGPT Image Sep 15, 2025, 10_25_25 PM.png"
    />

    <!-- CSS -->
    <link rel="stylesheet" href="dashboard-styles.css" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />

    <!-- Font Awesome for icons -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
  </head>

  <body>
    <div class="dashboard-container">
      <!-- Header -->
      <header class="dashboard-header">
        <div class="header-left">
          <div class="logo">
            <img
              src="logo/ChatGPT Image Sep 15, 2025, 10_25_25 PM.png"
              alt="AR Homes Resort Logo"
            />
          </div>
          <div class="resort-info">
            <h1>AR Homes Posadas Farm Resort</h1>
            <p>Guest Dashboard</p>
          </div>
        </div>
        <div class="header-right">
          <div class="user-profile">
            <div class="profile-info">
              <span class="user-name" id="userName"><?php echo htmlspecialchars($userFullName); ?></span>
              <span class="user-role">Guest</span>
            </div>
            <div class="profile-avatar">
              <i class="fas fa-user"></i>
            </div>
          </div>
          <button class="logout-btn" onclick="showLogoutPopup()">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
          </button>
        </div>
        <div class="mobile-toggle" onclick="toggleSidebar()">
          <i class="fas fa-bars"></i>
        </div>
      </header>

      <!-- Main Layout Container -->
      <div class="main-layout">
        <!-- Resort Gallery Section -->
        <div class="resort-gallery-section">
          <div class="resort-gallery">
            <div class="gallery-container">
              <div class="gallery-wrapper" id="galleryWrapper">
                <div class="gallery-slide active">
                  <img
                    src="images/545601129_1082516497373944_2891607710179489901_n.jpg"
                    alt="Resort View 1"
                  />
                  <div class="slide-overlay">
                    <h4>Stunning Pool Area</h4>
                    <p>
                      Relax in our pristine pool surrounded by tropical
                      landscapes
                    </p>
                  </div>
                </div>
                <div class="gallery-slide">
                  <img
                    src="images/545644951_1082516484040612_4799621970042989055_n.jpg"
                    alt="Resort View 2"
                  />
                  <div class="slide-overlay">
                    <h4>Luxurious Accommodations</h4>
                    <p>Experience comfort in our well-appointed rooms</p>
                  </div>
                </div>
                <div class="gallery-slide">
                  <img
                    src="images/545660091_1082516497373940_5856437654506077577_n.jpg"
                    alt="Resort View 3"
                  />
                  <div class="slide-overlay">
                    <h4>Beautiful Grounds</h4>
                    <p>Explore our lush gardens and scenic pathways</p>
                  </div>
                </div>
                <div class="gallery-slide">
                  <img
                    src="images/545688841_1082516494040607_6819488055916866169_n.jpg"
                    alt="Resort View 4"
                  />
                  <div class="slide-overlay">
                    <h4>Dining Excellence</h4>
                    <p>Savor exquisite cuisine in our restaurants</p>
                  </div>
                </div>
                <div class="gallery-slide">
                  <img
                    src="images/545688856_1082516490707274_3844063088537899193_n.jpg"
                    alt="Resort View 5"
                  />
                  <div class="slide-overlay">
                    <h4>Relaxation Spaces</h4>
                    <p>Unwind in our peaceful lounging areas</p>
                  </div>
                </div>
              </div>

              <!-- Gallery Navigation -->
              <button class="gallery-nav gallery-prev" onclick="navigateGallery(-1)">
                <i class="fas fa-chevron-left"></i>
              </button>
              <button class="gallery-nav gallery-next" onclick="navigateGallery(1)">
                <i class="fas fa-chevron-right"></i>
              </button>

              <!-- Gallery Indicators -->
              <div class="gallery-indicators" id="galleryIndicators"></div>
            </div>
          </div>
        </div>

        <!-- Navigation Buttons Section -->
        <div class="nav-buttons-section">
          <div class="nav-buttons-container">
            <button
              class="nav-btn"
              data-section="dashboard"
              onclick="showPopup('dashboard')"
            >
              <div class="nav-btn-icon">
                <i class="fas fa-chart-line"></i>
              </div>
              <span class="nav-btn-text">Dashboard</span>
              <div class="nav-btn-arrow">
                <i class="fas fa-chevron-right"></i>
              </div>
            </button>

            <button
              class="nav-btn"
              data-section="make-reservation"
              onclick="showPopup('make-reservation')"
            >
              <div class="nav-btn-icon">
                <i class="fas fa-calendar-plus"></i>
              </div>
              <span class="nav-btn-text">Make Reservation</span>
              <div class="nav-btn-arrow">
                <i class="fas fa-chevron-right"></i>
              </div>
            </button>

            <button
              class="nav-btn"
              data-section="my-reservations"
              onclick="showPopup('my-reservations')"
            >
              <div class="nav-btn-icon">
                <i class="fas fa-calendar-check"></i>
              </div>
              <span class="nav-btn-text">My Reservations</span>
              <div class="nav-btn-arrow">
                <i class="fas fa-chevron-right"></i>
              </div>
            </button>

            <button
              class="nav-btn"
              data-section="promotions"
              onclick="showPopup('promotions')"
            >
              <div class="nav-btn-icon">
                <i class="fas fa-tags"></i>
              </div>
              <span class="nav-btn-text">Promotions</span>
              <div class="nav-btn-arrow">
                <i class="fas fa-chevron-right"></i>
              </div>
            </button>

            <button
              class="nav-btn"
              data-section="profile"
              onclick="showPopup('profile')"
            >
              <div class="nav-btn-icon">
                <i class="fas fa-user-cog"></i>
              </div>
              <span class="nav-btn-text">Profile Settings</span>
              <div class="nav-btn-arrow">
                <i class="fas fa-chevron-right"></i>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Continue with existing popup and content sections from dashboard.html -->
    <!-- I'll create a separate file for content templates -->
    
    <script>
        // Pass PHP session data to JavaScript
        const userData = {
            userId: <?php echo json_encode($_SESSION['user_id']); ?>,
            fullName: <?php echo json_encode($userFullName); ?>,
            givenName: <?php echo json_encode($userGivenName); ?>,
            username: <?php echo json_encode($userName); ?>,
            email: <?php echo json_encode($userEmail); ?>,
            phone: <?php echo json_encode($userPhone); ?>,
            memberSince: <?php echo json_encode($memberSince); ?>,
            loyaltyLevel: 'Regular' // Can be updated based on database
        };
        
        console.log('✅ User logged in:', userData);
    </script>
    <script src="dashboard-script-inline.js"></script>
  </body>
</html>
