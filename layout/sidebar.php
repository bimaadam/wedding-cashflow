<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? 'Pengguna';

// Get current page for active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <li class="nav-item">
      <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
        <i class="mdi mdi-grid-large menu-icon"></i>
        <span class="menu-title">Dashboard</span>
      </a>
    </li>
    
    <li class="nav-item nav-category">Cash Flow Management</li>
    
    <li class="nav-item">
      <a class="nav-link <?php echo ($current_page == 'pengeluaran.php') ? 'active' : ''; ?>" href="pengeluaran.php">
        <i class="menu-icon mdi mdi-cash-minus"></i>
        <span class="menu-title">Pengeluaran Kas</span>
      </a>
    </li>
    
    <li class="nav-item">
      <a class="nav-link <?php echo ($current_page == 'pemasukan.php') ? 'active' : ''; ?>" href="pemasukan.php">
        <i class="menu-icon mdi mdi-cash-plus"></i>
        <span class="menu-title">Pemasukan Kas</span>
      </a>
    </li>
    
    <li class="nav-item">
      <a class="nav-link <?php echo ($current_page == 'jadwal-booking.php') ? 'active' : ''; ?>" href="jadwal-booking.php">
        <i class="menu-icon mdi mdi-calendar-check"></i>
        <span class="menu-title">Jadwal Booking</span>
      </a>
    </li>
    
    <li class="nav-item">
      <a class="nav-link <?php echo ($current_page == 'laporan-cashflow.php') ? 'active' : ''; ?>" href="laporan-cashflow.php">
        <i class="menu-icon mdi mdi-chart-line"></i>
        <span class="menu-title">Laporan Cash Flow</span>
      </a>
    </li>
    
    <li class="nav-item nav-category">Settings</li>
    
    <li class="nav-item">
      <a class="nav-link" href="auth/logout.php">
        <i class="menu-icon mdi mdi-logout"></i>
        <span class="menu-title">Logout</span>
      </a>
    </li>
  </ul>
</nav>