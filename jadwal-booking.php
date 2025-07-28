<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: auth/login.php');
  exit();
}

require_once 'config/koneksi.php';

$pageTitle = 'Jadwal Booking - Graceful Decoration';

// Handle success messages
$success_message = '';
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'deleted':
            $success_message = "Data booking berhasil dihapus!";
            break;
        case 'updated':
            $success_message = "Data booking berhasil diperbarui!";
            break;
        case 'created':
            $success_message = "Data booking berhasil ditambahkan!";
            break;
    }
}

// Handle error messages
$error_message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_id':
            $error_message = "ID booking tidak valid!";
            break;
        case 'not_found':
            $error_message = "Data booking tidak ditemukan!";
            break;
    }
}

// Get bookings grouped by date
$bookings_query = "SELECT * FROM booking ORDER BY tanggal_booking ASC";
$bookings_result = $conn->query($bookings_query);

// Get today's bookings
$today_query = "SELECT * FROM booking WHERE tanggal_booking = CURDATE() ORDER BY created_at ASC";
$today_result = $conn->query($today_query);

// Get upcoming bookings (next 7 days)
$upcoming_query = "SELECT * FROM booking WHERE tanggal_booking BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) ORDER BY tanggal_booking ASC";
$upcoming_result = $conn->query($upcoming_query);

// Get monthly bookings for calendar view
$monthly_query = "SELECT 
                    DATE_FORMAT(tanggal_booking, '%Y-%m-%d') as tanggal,
                    COUNT(*) as jumlah_booking,
                    GROUP_CONCAT(CONCAT(nama_klien, ' (', status, ')') SEPARATOR ', ') as detail_booking
                  FROM booking 
                  WHERE tanggal_booking >= CURDATE() 
                  GROUP BY tanggal_booking 
                  ORDER BY tanggal_booking ASC";
$monthly_result = $conn->query($monthly_query);

// Get statistics
$stats_query = "SELECT 
                  COUNT(*) as total_bookings,
                  COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_bookings,
                  COUNT(CASE WHEN status = 'dp' THEN 1 END) as dp_bookings,
                  COUNT(CASE WHEN status = 'deal' THEN 1 END) as deal_bookings,
                  COUNT(CASE WHEN status = 'cancel' THEN 1 END) as cancel_bookings,
                  COUNT(CASE WHEN tanggal_booking >= CURDATE() THEN 1 END) as upcoming_bookings,
                  COUNT(CASE WHEN tanggal_booking = CURDATE() THEN 1 END) as today_bookings
                FROM booking";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<?php ob_start(); ?>

<div class="row">
  <div class="col-md-8 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Form Booking Event</h4>
        <p class="card-description">Tambah jadwal booking event baru</p>
        
        <?php if (isset($success_message)): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
        
        <form method="POST" action="booking-simpan.php" class="forms-sample" id="bookingForm">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="nama_klien">Nama Klien</label>
                <input type="text" class="form-control" id="nama_klien" name="nama_klien" required placeholder="Nama lengkap klien">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="kontak_wa">Kontak WhatsApp</label>
                <input type="tel" class="form-control" id="kontak_wa" name="kontak_wa" required placeholder="08xxxxxxxxxx">
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="tanggal_booking">Tanggal Booking</label>
                <input type="date" class="form-control" id="tanggal_booking" name="tanggal_booking" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="jenis_event">Jenis Event</label>
                <select class="form-control" id="jenis_event" name="jenis_event" required>
                  <option value="">-- Pilih Jenis Event --</option>
                  <option value="Pernikahan">Pernikahan</option>
                  <option value="Ulang Tahun">Ulang Tahun</option>
                  <option value="Wisuda">Wisuda</option>
                  <option value="Engagement">Engagement</option>
                  <option value="Corporate Event">Corporate Event</option>
                  <option value="Lainnya">Lainnya</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="status">Status</label>
            <select class="form-control" id="status" name="status">
              <option value="pending">Pending</option>
              <option value="dp">DP</option>
              <option value="deal">Deal</option>
              <option value="cancel">Cancel</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="catatan">Catatan</label>
            <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Catatan tambahan tentang booking"></textarea>
          </div>
          
          <button type="submit" class="btn btn-primary me-2">Simpan Booking</button>
          <button type="reset" class="btn btn-light">Reset</button>
        </form>
      </div>
    </div>
  </div>
  
  <div class="col-md-4 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Statistik Booking</h4>
        
        <div class="row">
          <div class="col-6">
            <div class="card bg-gradient-primary text-white mb-3">
              <div class="card-body text-center">
                <h4><?php echo $stats['total_bookings']; ?></h4>
                <p class="mb-0">Total Booking</p>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="card bg-gradient-success text-white mb-3">
              <div class="card-body text-center">
                <h4><?php echo $stats['today_bookings']; ?></h4>
                <p class="mb-0">Hari Ini</p>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="card bg-gradient-warning text-white mb-3">
              <div class="card-body text-center">
                <h4><?php echo $stats['pending_bookings']; ?></h4>
                <p class="mb-0">Pending</p>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="card bg-gradient-info text-white mb-3">
              <div class="card-body text-center">
                <h4><?php echo $stats['dp_bookings']; ?></h4>
                <p class="mb-0">DP</p>
              </div>
            </div>
          </div>
        </div>
        
        <h5 class="mt-4">Booking Hari Ini</h5>
        <div class="list-group">
          <?php if ($today_result && $today_result->num_rows > 0): ?>
            <?php while ($booking = $today_result->fetch_assoc()): ?>
              <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                  <h6 class="mb-1"><?php echo $booking['nama_klien']; ?></h6>
                  <small class="badge badge-<?php echo $booking['status'] == 'deal' ? 'success' : ($booking['status'] == 'dp' ? 'info' : ($booking['status'] == 'pending' ? 'warning' : 'danger')); ?>">
                    <?php echo strtoupper($booking['status']); ?>
                  </small>
                </div>
                <p class="mb-1"><?php echo $booking['jenis_event']; ?></p>
                <small><?php echo $booking['kontak_wa']; ?></small>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="list-group-item text-center">
              <p class="mb-0 text-muted">Tidak ada booking hari ini</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Booking Minggu Ini</h4>
        <div class="table-responsive">
          <table class="table table-borderless">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Klien</th>
                <th>Jenis Event</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($upcoming_result && $upcoming_result->num_rows > 0): ?>
                <?php while ($booking = $upcoming_result->fetch_assoc()): ?>
                  <tr>
                    <td>
                      <strong><?php echo date('d/m/Y', strtotime($booking['tanggal_booking'])); ?></strong><br>
                      <small class="text-muted"><?php echo date('l', strtotime($booking['tanggal_booking'])); ?></small>
                    </td>
                    <td>
                      <strong><?php echo $booking['nama_klien']; ?></strong><br>
                      <small class="text-muted"><?php echo $booking['kontak_wa']; ?></small>
                    </td>
                    <td><?php echo $booking['jenis_event']; ?></td>
                    <td>
                      <span class="badge badge-<?php echo $booking['status'] == 'deal' ? 'success' : ($booking['status'] == 'dp' ? 'info' : ($booking['status'] == 'pending' ? 'warning' : 'danger')); ?>">
                        <?php echo strtoupper($booking['status']); ?>
                      </span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" class="text-center">Tidak ada booking minggu ini</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-md-6 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Kalender Booking</h4>
        <div class="calendar-container">
          <?php if ($monthly_result && $monthly_result->num_rows > 0): ?>
            <?php while ($day = $monthly_result->fetch_assoc()): ?>
              <div class="calendar-day mb-3">
                <div class="card bg-light">
                  <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <h6 class="mb-1"><?php echo date('d M Y', strtotime($day['tanggal'])); ?></h6>
                        <small class="text-muted"><?php echo date('l', strtotime($day['tanggal'])); ?></small>
                      </div>
                      <span class="badge badge-primary"><?php echo $day['jumlah_booking']; ?> booking</span>
                    </div>
                    <div class="mt-2">
                      <small class="text-muted">
                        <?php echo substr($day['detail_booking'], 0, 100) . (strlen($day['detail_booking']) > 100 ? '...' : ''); ?>
                      </small>
                    </div>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="text-center">
              <p class="text-muted">Tidak ada booking mendatang</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Semua Booking</h4>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Klien</th>
                <th>Jenis Event</th>
                <th>Kontak</th>
                <th>Status</th>
                <th>Catatan</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($bookings_result && $bookings_result->num_rows > 0): ?>
                <?php while ($booking = $bookings_result->fetch_assoc()): ?>
                  <tr>
                    <td>
                      <strong><?php echo date('d/m/Y', strtotime($booking['tanggal_booking'])); ?></strong><br>
                      <small class="text-muted"><?php echo date('l', strtotime($booking['tanggal_booking'])); ?></small>
                    </td>
                    <td>
                      <strong><?php echo $booking['nama_klien']; ?></strong><br>
                      <small class="text-muted">
                        <a href="booking-detail.php?id=<?php echo $booking['id']; ?>" class="text-decoration-none">
                          Lihat Detail
                        </a>
                      </small>
                    </td>
                    <td><?php echo $booking['jenis_event']; ?></td>
                    <td><?php echo $booking['kontak_wa']; ?></td>
                    <td>
                      <span class="badge badge-<?php echo $booking['status'] == 'deal' ? 'success' : ($booking['status'] == 'dp' ? 'info' : ($booking['status'] == 'pending' ? 'warning' : 'danger')); ?>">
                        <?php echo strtoupper($booking['status']); ?>
                      </span>
                    </td>
                    <td><?php echo substr($booking['catatan'], 0, 30) . (strlen($booking['catatan']) > 30 ? '...' : ''); ?></td>
                    <td>
                      <a href="booking-edit.php?id=<?php echo $booking['id']; ?>" class="btn btn-outline-primary btn-sm">
                        <i class="mdi mdi-pencil"></i> Edit
                      </a>
                      <a href="booking-hapus.php?id=<?php echo $booking['id']; ?>" class="btn btn-outline-danger btn-sm">
                        <i class="mdi mdi-delete"></i> Hapus
                      </a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="text-center">Belum ada booking yang terdaftar</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Set minimum date to today
document.getElementById('tanggal_booking').min = new Date().toISOString().split('T')[0];

// Auto format phone number
document.getElementById('kontak_wa').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 0 && !value.startsWith('0')) {
        value = '0' + value;
    }
    e.target.value = value;
});

// Handle form submission with AJAX
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Disable submit button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Menyimpan...';
    
    fetch('booking-simpan.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert('success', data.message);
            
            // Reset form
            this.reset();
            
            // Reload page after 1.5 seconds
            setTimeout(() => {
                window.location.href = 'jadwal-booking.php?message=created';
            }, 1500);
        } else {
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        showAlert('danger', 'Terjadi kesalahan: ' + error.message);
    })
    .finally(() => {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Function to show alert messages
function showAlert(type, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert alert before form
    const form = document.getElementById('bookingForm');
    form.parentNode.insertBefore(alertDiv, form);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>

<?php $content = ob_get_clean(); ?>
<?php include 'layout/main.php'; ?>