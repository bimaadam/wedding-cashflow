<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

require_once 'config/koneksi.php';

$pageTitle = 'Edit Booking - Graceful Decoration';

// Get booking ID from URL
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($booking_id <= 0) {
    header('Location: jadwal-booking.php?error=invalid_id');
    exit();
}

// Get booking data
$stmt = $conn->prepare("SELECT * FROM booking WHERE id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: jadwal-booking.php?error=not_found');
    exit();
}

$booking = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_klien = trim($_POST['nama_klien']);
    $tanggal_booking = $_POST['tanggal_booking'];
    $jenis_event = $_POST['jenis_event'];
    $kontak_wa = trim($_POST['kontak_wa']);
    $status = $_POST['status'];
    $catatan = trim($_POST['catatan']);
    
    // Validasi input
    if (empty($nama_klien) || empty($tanggal_booking) || empty($jenis_event) || empty($kontak_wa)) {
        $error_message = "Semua field wajib harus diisi";
    } else {
        $stmt = $conn->prepare("UPDATE booking SET nama_klien = ?, tanggal_booking = ?, jenis_event = ?, kontak_wa = ?, status = ?, catatan = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $nama_klien, $tanggal_booking, $jenis_event, $kontak_wa, $status, $catatan, $booking_id);
        
        if ($stmt->execute()) {
            header('Location: jadwal-booking.php?message=updated');
            exit();
        } else {
            $error_message = "Gagal memperbarui booking: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<?php ob_start(); ?>

<div class="row">
  <div class="col-md-8 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="card-title">Edit Booking Event</h4>
            <p class="card-description">Perbarui data booking event</p>
          </div>
          <a href="jadwal-booking.php" class="btn btn-outline-secondary">
            <i class="mdi mdi-arrow-left"></i> Kembali
          </a>
        </div>
        
        <?php if (isset($error_message)): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
        
        <form method="POST" class="forms-sample">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="nama_klien">Nama Klien <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama_klien" name="nama_klien" 
                       value="<?php echo htmlspecialchars($booking['nama_klien']); ?>" 
                       required placeholder="Nama lengkap klien">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="kontak_wa">Kontak WhatsApp <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" id="kontak_wa" name="kontak_wa" 
                       value="<?php echo htmlspecialchars($booking['kontak_wa']); ?>" 
                       required placeholder="08xxxxxxxxxx">
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="tanggal_booking">Tanggal Booking <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="tanggal_booking" name="tanggal_booking" 
                       value="<?php echo $booking['tanggal_booking']; ?>" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="jenis_event">Jenis Event <span class="text-danger">*</span></label>
                <select class="form-control" id="jenis_event" name="jenis_event" required>
                  <option value="">-- Pilih Jenis Event --</option>
                  <option value="Pernikahan" <?php echo $booking['jenis_event'] == 'Pernikahan' ? 'selected' : ''; ?>>Pernikahan</option>
                  <option value="Ulang Tahun" <?php echo $booking['jenis_event'] == 'Ulang Tahun' ? 'selected' : ''; ?>>Ulang Tahun</option>
                  <option value="Wisuda" <?php echo $booking['jenis_event'] == 'Wisuda' ? 'selected' : ''; ?>>Wisuda</option>
                  <option value="Engagement" <?php echo $booking['jenis_event'] == 'Engagement' ? 'selected' : ''; ?>>Engagement</option>
                  <option value="Corporate Event" <?php echo $booking['jenis_event'] == 'Corporate Event' ? 'selected' : ''; ?>>Corporate Event</option>
                  <option value="Lainnya" <?php echo $booking['jenis_event'] == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="status">Status</label>
            <select class="form-control" id="status" name="status">
              <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
              <option value="dp" <?php echo $booking['status'] == 'dp' ? 'selected' : ''; ?>>DP</option>
              <option value="deal" <?php echo $booking['status'] == 'deal' ? 'selected' : ''; ?>>Deal</option>
              <option value="cancel" <?php echo $booking['status'] == 'cancel' ? 'selected' : ''; ?>>Cancel</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="catatan">Catatan</label>
            <textarea class="form-control" id="catatan" name="catatan" rows="3" 
                      placeholder="Catatan tambahan tentang booking"><?php echo htmlspecialchars($booking['catatan']); ?></textarea>
          </div>
          
          <div class="form-group">
            <small class="text-muted">
              <i class="mdi mdi-information"></i>
              Booking dibuat pada: <?php echo date('d/m/Y H:i', strtotime($booking['created_at'])); ?>
            </small>
          </div>
          
          <div class="d-flex justify-content-between">
            <div>
              <button type="submit" class="btn btn-primary me-2">
                <i class="mdi mdi-content-save"></i> Update Booking
              </button>
              <a href="jadwal-booking.php" class="btn btn-light">
                <i class="mdi mdi-cancel"></i> Batal
              </a>
            </div>
            <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
              <i class="mdi mdi-delete"></i> Hapus Booking
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <div class="col-md-4 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Informasi Booking</h4>
        
        <div class="booking-info">
          <div class="info-item mb-3">
            <label class="form-label">ID Booking</label>
            <div class="info-value">#<?php echo str_pad($booking['id'], 4, '0', STR_PAD_LEFT); ?></div>
          </div>
          
          <div class="info-item mb-3">
            <label class="form-label">Status Saat Ini</label>
            <div class="info-value">
              <span class="badge badge-<?php echo $booking['status'] == 'deal' ? 'success' : ($booking['status'] == 'dp' ? 'info' : ($booking['status'] == 'pending' ? 'warning' : 'danger')); ?>">
                <?php echo strtoupper($booking['status']); ?>
              </span>
            </div>
          </div>
          
          <div class="info-item mb-3">
            <label class="form-label">Tanggal Dibuat</label>
            <div class="info-value"><?php echo date('d F Y, H:i', strtotime($booking['created_at'])); ?></div>
          </div>
          
          <div class="info-item mb-3">
            <label class="form-label">Hari Event</label>
            <div class="info-value">
              <?php 
              $event_date = new DateTime($booking['tanggal_booking']);
              echo $event_date->format('l, d F Y'); 
              ?>
            </div>
          </div>
          
          <div class="info-item mb-3">
            <label class="form-label">Countdown</label>
            <div class="info-value">
              <?php 
              $today = new DateTime();
              $event_date = new DateTime($booking['tanggal_booking']);
              $diff = $today->diff($event_date);
              
              if ($event_date < $today) {
                  echo '<span class="text-muted">Event sudah berlalu</span>';
              } else {
                  echo '<span class="text-primary">' . $diff->days . ' hari lagi</span>';
              }
              ?>
            </div>
          </div>
        </div>
        
        <hr>
        
        <div class="quick-actions">
          <h6>Aksi Cepat</h6>
          <div class="d-grid gap-2">
            <button type="button" class="btn btn-outline-success btn-sm" onclick="updateStatus('deal')">
              <i class="mdi mdi-check-circle"></i> Tandai Deal
            </button>
            <button type="button" class="btn btn-outline-info btn-sm" onclick="updateStatus('dp')">
              <i class="mdi mdi-cash"></i> Tandai DP
            </button>
            <button type="button" class="btn btn-outline-warning btn-sm" onclick="updateStatus('pending')">
              <i class="mdi mdi-clock"></i> Tandai Pending
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="updateStatus('cancel')">
              <i class="mdi mdi-close-circle"></i> Tandai Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Auto format phone number
document.getElementById('kontak_wa').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 0 && !value.startsWith('0')) {
        value = '0' + value;
    }
    e.target.value = value;
});

// Quick status update
function updateStatus(status) {
    document.getElementById('status').value = status;
    
    // Show confirmation
    const statusText = {
        'pending': 'Pending',
        'dp': 'DP',
        'deal': 'Deal',
        'cancel': 'Cancel'
    };
    
    if (confirm(`Ubah status booking menjadi ${statusText[status]}?`)) {
        document.querySelector('form').submit();
    }
}

// Confirm delete
function confirmDelete() {
    if (confirm('Apakah Anda yakin ingin menghapus booking ini? Tindakan ini tidak dapat dibatalkan.')) {
        window.location.href = 'booking-hapus.php?id=<?php echo $booking_id; ?>';
    }
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const requiredFields = ['nama_klien', 'tanggal_booking', 'jenis_event', 'kontak_wa'];
    let isValid = true;
    
    requiredFields.forEach(field => {
        const input = document.getElementById(field);
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Mohon lengkapi semua field yang wajib diisi');
    }
});
</script>

<style>
.info-item {
    border-bottom: 1px solid #eee;
    padding-bottom: 0.5rem;
}

.info-item:last-child {
    border-bottom: none;
}

.info-value {
    font-weight: 500;
    color: #333;
}

.quick-actions .btn {
    text-align: left;
}

.is-invalid {
    border-color: #dc3545;
}
</style>

<?php $content = ob_get_clean(); ?>
<?php include 'layout/main.php'; ?>