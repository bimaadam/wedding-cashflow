<head>
  <link rel="stylesheet" href="./assets/css/navbar.style.css"
    </head>

  <nav class="modern-navbar">
    <div class="navbar-content">
      <!-- Mobile Hamburger -->
      <button class="hamburger-btn" type="button" data-toggle="minimize">
        <i class="mdi mdi-menu"></i>
      </button>

      <!-- Brand Section -->
      <div class="navbar-brand-section">
        <a href="../index.html">
          <img src="" alt="logo" class="brand-logo" />
          GraceFul
        </a>
      </div>

      <!-- Search Section -->
      <div class="search-section">
        <form class="modern-search">
          <i class="mdi mdi-magnify search-icon"></i>
          <input type="text" class="search-input" placeholder="Search anything..." />
        </form>
      </div>

      <!-- Actions Section -->
      <div class="navbar-actions">
        <!-- Fullscreen Toggle -->
        <button class="action-btn" id="fullscreen-button" title="Toggle Fullscreen">
          <i class="mdi mdi-fullscreen"></i>
        </button>

        <!-- Messages Dropdown -->
        <div class="dropdown">
          <button class="action-btn" data-bs-toggle="dropdown" title="Messages">
            <i class="mdi mdi-email-outline"></i>
            <span class="notification-badge"></span>
          </button>
          <div class="dropdown-menu dropdown-menu-end message-dropdown">
            <div class="dropdown-header">
              <i class="mdi mdi-email-outline me-2"></i>Messages
            </div>
            <a href="#" class="message-item">
              <img src="../assets/images/faces/face4.jpg" alt="user" class="message-avatar">
              <div class="item-content">
                <h6>Mark Johnson</h6>
                <p>Hey! How's the project going?</p>
                <small class="text-muted">2 minutes ago</small>
              </div>
            </a>
            <a href="#" class="message-item">
              <img src="./assets/images/faces/face2.jpg" alt="user" class="message-avatar">
              <div class="item-content">
                <h6>Sarah Wilson</h6>
                <p>Meeting rescheduled to 3 PM</p>
                <small class="text-muted">15 minutes ago</small>
              </div>
            </a>
            <a href="#" class="message-item">
              <img src="../assets/images/faces/face3.jpg" alt="user" class="message-avatar">
              <div class="item-content">
                <h6>Alex Chen</h6>
                <p>Design files are ready for review</p>
                <small class="text-muted">1 hour ago</small>
              </div>
            </a>
            <div class="dropdown-footer">
              <a href="#">View All Messages</a>
            </div>
          </div>
        </div>

        <!-- Notifications Dropdown -->
        <div class="dropdown">
          <button class="action-btn" data-bs-toggle="dropdown" title="Notifications">
            <i class="mdi mdi-bell-outline"></i>
            <span class="notification-badge"></span>
          </button>
          <div class="dropdown-menu dropdown-menu-end notification-dropdown">
            <div class="dropdown-header">
              <i class="mdi mdi-bell-outline me-2"></i>Notifications
            </div>
            <a href="#" class="notification-item">
              <div class="notification-icon success">
                <i class="mdi mdi-calendar-check"></i>
              </div>
              <div class="item-content">
                <h6>Event Today</h6>
                <p>You have a wedding consultation at 2 PM</p>
                <small class="text-muted">10 minutes ago</small>
              </div>
            </a>
            <a href="#" class="notification-item">
              <div class="notification-icon warning">
                <i class="mdi mdi-alert-circle"></i>
              </div>
              <div class="item-content">
                <h6>Payment Reminder</h6>
                <p>Invoice #1234 is due tomorrow</p>
                <small class="text-muted">1 hour ago</small>
              </div>
            </a>
            <a href="#" class="notification-item">
              <div class="notification-icon info">
                <i class="mdi mdi-information"></i>
              </div>
              <div class="item-content">
                <h6>System Update</h6>
                <p>New features are now available</p>
                <small class="text-muted">2 hours ago</small>
              </div>
            </a>
            <div class="dropdown-footer">
              <a href="#">View All Notifications</a>
            </div>
          </div>
        </div>

        <!-- Profile Dropdown -->
        <div class="dropdown">
          <div class="profile-section" data-bs-toggle="dropdown">
            <img src="./assets/images/faces-clipart/pic-1.png" alt="profile" class="profile-avatar">
            <div class="profile-info">
              <p class="profile-name"><?= $_SESSION['username'] ?? 'Guest' ?></p>
              <p class="profile-status">● Online</p>
            </div>
            <i class="mdi mdi-chevron-down" style="color: #667eea;"></i>
          </div>
          <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="#">
              <i class="mdi mdi-account-circle"></i>
              My Profile
            </a>
            <a class="dropdown-item" href="#">
              <i class="mdi mdi-cog"></i>
              Settings
            </a>
            <a class="dropdown-item" href="#">
              <i class="mdi mdi-help-circle"></i>
              Help & Support
            </a>
            <hr class="dropdown-divider">
            <a class="dropdown-item" href="auth/logout.php">
              <i class="mdi mdi-logout text-danger"></i>
              Sign Out
            </a>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <script>
    // Fullscreen Toggle
    document.getElementById('fullscreen-button').addEventListener('click', function() {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
        this.innerHTML = '<i class="mdi mdi-fullscreen-exit"></i>';
      } else {
        document.exitFullscreen();
        this.innerHTML = '<i class="mdi mdi-fullscreen"></i>';
      }
    });

    // Search functionality
    document.querySelector('.search-input').addEventListener('input', function(e) {
      const query = e.target.value;
      if (query.length > 2) {
        // Add your search logic here
        console.log('Searching for:', query);
      }
    });
  </script>