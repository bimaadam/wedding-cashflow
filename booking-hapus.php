<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

require_once 'config/koneksi.php';

// Get booking ID from URL or POST
$booking_id = 0;
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET' && isset($_GET['id'])) {
    $booking_id = intval($_GET['id']);
} elseif ($method == 'POST' && isset($_POST['id'])) {
    $booking_id = intval($_POST['id']);
}

// Validate booking ID
if ($booking_id <= 0) {
    if ($method == 'POST') {
        echo json_encode([
            'success' => false,
            'message' => 'ID booking tidak valid'
        ]);
        exit();
    } else {
        header('Location: jadwal-booking.php?error=invalid_id');
        exit();
    }
}

// Check if booking exists and get booking data
$stmt = $conn->prepare("SELECT * FROM booking WHERE id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    if ($method == 'POST') {
        echo json_encode([
            'success' => false,
            'message' => 'Booking tidak ditemukan'
        ]);
        exit();
    } else {
        header('Location: jadwal-booking.php?error=not_found');
        exit();
    }
}

$booking = $result->fetch_assoc();
$stmt->close();

// Handle POST request (actual deletion)
if ($method == 'POST') {
    header('Content-Type: application/json');
    
    try {
        // Check if there are related records in other tables
        // You might want to add checks for related pemasukan or pengeluaran records
        
        // Delete the booking
        $delete_stmt = $conn->prepare("DELETE FROM booking WHERE id = ?");
        $delete_stmt->bind_param("i", $booking_id);
        
        if ($delete_stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Booking berhasil dihapus',
                'deleted_booking' => [
                    'id' => $booking['id'],
                    'nama_klien' => $booking['nama_klien'],
                    'tanggal_booking' => $booking['tanggal_booking']
                ]
            ]);
        } else {
            throw new Exception("Gagal menghapus booking: " . $conn->error);
        }
        
        $delete_stmt->close();
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    
    $conn->close();
    exit();
}

// Handle GET request (show confirmation page)
$pageTitle = 'Hapus Booking - Graceful Decoration';
?>

<?php ob_start(); ?>

<div class="row justify-content-center">
  <div class="col-md-8 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="text-center mb-4">
          <div class="mb-3">
            <i class="mdi mdi-alert-circle-outline text-warning" style="font-size: 4rem;"></i>
          </div>
          <h4 class="card-title text-danger">Konfirmasi Penghapusan Booking</h4>
          <p class="card-description">Apakah Anda yakin ingin menghapus booking berikut?</p>
        </div>
        
        <div class="card bg-light mb-4">
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="booking-detail mb-3">
                  <label class="form-label text-muted">ID Booking</label>
                  <div class="fw-bold">#<?php echo str_pad($booking['id'], 4, '0', STR_PAD_LEFT); ?></div>
                </div>
                
                <div class="booking-detail mb-3">
                  <label class="form-label text-muted">Nama Klien</label>
                  <div class="fw-bold"><?php echo htmlspecialchars($booking['nama_klien']); ?></div>
                </div>
                
                <div class="booking-detail mb-3">
                  <label class="form-label text-muted">Kontak WhatsApp</label>
                  <div class="fw-bold"><?php echo htmlspecialchars($booking['kontak_wa']); ?></div>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="booking-detail mb-3">
                  <label class="form-label text-muted">Tanggal Booking</label>
                  <div class="fw-bold"><?php echo date('d F Y', strtotime($booking['tanggal_booking'])); ?></div>
                </div>
                
                <div class="booking-detail mb-3">
                  <label class="form-label text-muted">Jenis Event</label>
                  <div class="fw-bold"><?php echo htmlspecialchars($booking['jenis_event']); ?></div>
                </div>
                
                <div class="booking-detail mb-3">
                  <label class="form-label text-muted">Status</label>
                  <div>
                    <span class="badge badge-<?php echo $booking['status'] == 'deal' ? 'success' : ($booking['status'] == 'dp' ? 'info' : ($booking['status'] == 'pending' ? 'warning' : 'danger')); ?>">
                      <?php echo strtoupper($booking['status']); ?>
                    </span>
                  </div>
                </div>
              </div>
            </div>
            
            <?php if (!empty($booking['catatan'])): ?>
              <div class="booking-detail">
                <label class="form-label text-muted">Catatan</label>
                <div class="fw-bold"><?php echo htmlspecialchars($booking['catatan']); ?></div>
              </div>
            <?php endif; ?>
          </div>
        </div>
        
        <div class="alert alert-warning" role="alert">
          <i class="mdi mdi-alert-triangle"></i>
          <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan. Data booking akan dihapus secara permanen dari sistem.
        </div>
        
        <div class="d-flex justify-content-center gap-3">
          <button type="button" class="btn btn-danger" id="confirmDelete">
            <i class="mdi mdi-delete"></i> Ya, Hapus Booking
          </button>
          <a href="jadwal-booking.php" class="btn btn-secondary">
            <i class="mdi mdi-arrow-left"></i> Batal
          </a>
          <a href="booking-edit.php?id=<?php echo $booking['id']; ?>" class="btn btn-outline-primary">
            <i class="mdi mdi-pencil"></i> Edit Saja
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 mb-0">Menghapus booking...</p>
      </div>
    </div>
  </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
          <i class="mdi mdi-check-circle"></i> Berhasil
        </h5>
      </div>
      <div class="modal-body text-center">
        <p class="mb-0">Booking berhasil dihapus!</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="window.location.href='jadwal-booking.php'">
          OK
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <i class="mdi mdi-alert-circle"></i> Error
        </h5>
      </div>
      <div class="modal-body">
        <p id="errorMessage" class="mb-0"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
          OK
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('confirmDelete').addEventListener('click', function() {
    // Show loading modal
    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
    loadingModal.show();
    
    // Disable the button to prevent double clicks
    this.disabled = true;
    
    // Send delete request
    fetch('booking-hapus.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=<?php echo $booking_id; ?>'
    })
    .then(response => response.json())
    .then(data => {
        loadingModal.hide();
        
        if (data.success) {
            // Show success modal
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
        } else {
            // Show error modal
            document.getElementById('errorMessage').textContent = data.message;
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
            
            // Re-enable the button
            document.getElementById('confirmDelete').disabled = false;
        }
    })
    .catch(error => {
        loadingModal.hide();
        
        // Show error modal
        document.getElementById('errorMessage').textContent = 'Terjadi kesalahan saat menghapus booking: ' + error.message;
        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        errorModal.show();
        
        // Re-enable the button
        document.getElementById('confirmDelete').disabled = false;
    });
});

// Handle escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        window.location.href = 'jadwal-booking.php';
    }
});
</script>

<style>
.booking-detail {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 0.5rem;
}

.booking-detail:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.gap-3 {
    gap: 1rem !important;
}
</style>

<?php $content = ob_get_clean(); ?>
<?php include 'layout/main.php'; ?>