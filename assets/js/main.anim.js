// Loading Animation
window.addEventListener("load", function () {
  const loadingOverlay = document.getElementById("loadingOverlay");
  setTimeout(() => {
    loadingOverlay.classList.remove("show");
  }, 500);
});

// Show loading on page transitions
function showLoading() {
  const loadingOverlay = document.getElementById("loadingOverlay");
  loadingOverlay.classList.add("show");
}

// Mobile Sidebar Toggle
function toggleSidebar() {
  const sidebar = document.querySelector(".modern-sidebar");
  const overlay = document.getElementById("sidebarOverlay");

  sidebar.classList.toggle("show");
  overlay.classList.toggle("show");
}

// Close sidebar when clicking overlay
document
  .getElementById("sidebarOverlay")
  .addEventListener("click", function () {
    const sidebar = document.querySelector(".modern-sidebar");
    const overlay = document.getElementById("sidebarOverlay");

    sidebar.classList.remove("show");
    overlay.classList.remove("show");
  });

// Hamburger menu functionality
document.addEventListener("DOMContentLoaded", function () {
  const hamburgerBtn = document.querySelector(".hamburger-btn");
  if (hamburgerBtn) {
    hamburgerBtn.addEventListener("click", toggleSidebar);
  }

  // Auto-hide sidebar on mobile when clicking nav links
  const navLinks = document.querySelectorAll(".nav-link");
  navLinks.forEach((link) => {
    link.addEventListener("click", function () {
      if (window.innerWidth <= 1024) {
        setTimeout(() => {
          const sidebar = document.querySelector(".modern-sidebar");
          const overlay = document.getElementById("sidebarOverlay");
          sidebar.classList.remove("show");
          overlay.classList.remove("show");
        }, 300);
      }
    });
  });
});

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute("href"));
    if (target) {
      target.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    }
  });
});

// Add loading to all form submissions
document.querySelectorAll("form").forEach((form) => {
  form.addEventListener("submit", function () {
    showLoading();
  });
});

// Add loading to navigation links
document.querySelectorAll("a[href]").forEach((link) => {
  link.addEventListener("click", function (e) {
    const href = this.getAttribute("href");
    if (
      href &&
      !href.startsWith("#") &&
      !href.startsWith("javascript:") &&
      !this.target
    ) {
      showLoading();
    }
  });
});

// Active navigation highlighting
function setActiveNavigation() {
  const currentPath = window.location.pathname;
  const navLinks = document.querySelectorAll(".nav-link");

  navLinks.forEach((link) => {
    link.classList.remove("active");
    const linkPath = link.getAttribute("href");
    if (linkPath && currentPath.includes(linkPath.split("/").pop())) {
      link.classList.add("active");
    }
  });
}

// Initialize active navigation
document.addEventListener("DOMContentLoaded", setActiveNavigation);

// Notification system
function showNotification(message, type = "info") {
  const notification = document.createElement("div");
  notification.className = `notification notification-${type}`;
  notification.innerHTML = `
                <div class="notification-content">
                    <i class="mdi mdi-${
                      type === "success"
                        ? "check-circle"
                        : type === "error"
                        ? "alert-circle"
                        : "information"
                    }"></i>
                    <span>${message}</span>
                    <button class="notification-close">&times;</button>
                </div>
            `;

  // Add notification styles
  const style = document.createElement("style");
  style.textContent = `
                .notification {
                    position: fixed;
                    top: 90px;
                    right: 20px;
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(20px);
                    border: 1px solid rgba(0, 0, 0, 0.1);
                    border-radius: 10px;
                    padding: 15px 20px;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
                    z-index: 10000;
                    transform: translateX(100%);
                    transition: all 0.3s ease;
                    min-width: 300px;
                }
                
                .notification.show {
                    transform: translateX(0);
                }
                
                .notification-content {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                
                .notification-success {
                    border-left: 4px solid #4CAF50;
                }
                
                .notification-error {
                    border-left: 4px solid #ff4757;
                }
                
                .notification-info {
                    border-left: 4px solid #667eea;
                }
                
                .notification-close {
                    background: none;
                    border: none;
                    font-size: 18px;
                    cursor: pointer;
                    margin-left: auto;
                    color: #666;
                }
                
                .notification-close:hover {
                    color: #333;
                }
            `;

  if (!document.querySelector("style[data-notification-styles]")) {
    style.setAttribute("data-notification-styles", "true");
    document.head.appendChild(style);
  }

  document.body.appendChild(notification);

  // Show notification
  setTimeout(() => {
    notification.classList.add("show");
  }, 100);

  // Auto remove after 5 seconds
  setTimeout(() => {
    notification.classList.remove("show");
    setTimeout(() => {
      document.body.removeChild(notification);
    }, 300);
  }, 5000);

  // Close button functionality
  notification
    .querySelector(".notification-close")
    .addEventListener("click", function () {
      notification.classList.remove("show");
      setTimeout(() => {
        document.body.removeChild(notification);
      }, 300);
    });
}

// Expose notification function globally
window.showNotification = showNotification;

// Enhanced card hover effects
document.querySelectorAll(".modern-card").forEach((card) => {
  card.addEventListener("mouseenter", function () {
    this.style.transform = "translateY(-8px) scale(1.02)";
  });

  card.addEventListener("mouseleave", function () {
    this.style.transform = "translateY(0) scale(1)";
  });
});

// Responsive layout adjustments
function handleResize() {
  if (window.innerWidth > 1024) {
    const sidebar = document.querySelector(".modern-sidebar");
    const overlay = document.getElementById("sidebarOverlay");
    sidebar.classList.remove("show");
    overlay.classList.remove("show");
  }
}

window.addEventListener("resize", handleResize);

// Initialize tooltips (if Bootstrap is available)
if (typeof bootstrap !== "undefined") {
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
}

// Page transition effects
function fadeIn(element) {
  element.style.opacity = "0";
  element.style.transform = "translateY(20px)";
  element.style.transition = "all 0.6s ease";

  setTimeout(() => {
    element.style.opacity = "1";
    element.style.transform = "translateY(0)";
  }, 100);
}

// Initialize page animations
document.addEventListener("DOMContentLoaded", function () {
  const contentElements = document.querySelectorAll(
    ".modern-card, .page-title, .page-subtitle"
  );
  contentElements.forEach((element, index) => {
    setTimeout(() => {
      fadeIn(element);
    }, index * 100);
  });
});

// Console welcome message
console.log(
  "%c🎉 GraceFull Wedding Dashboard",
  "color: #667eea; font-size: 16px; font-weight: bold;"
);
console.log(
  "%cModern UI loaded successfully!",
  "color: #4CAF50; font-size: 14px;"
);
