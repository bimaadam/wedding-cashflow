<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

require_once 'config/koneksi.php';

// Get event ID from URL or POST
$event_id = 0;
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET' && isset($_GET['id'])) {
    $event_id = intval($_GET['id']);
} elseif ($method == 'POST' && isset($_POST['id'])) {
    $event_id = intval($_POST['id']);
}

// Validate event ID
if ($event_id <= 0) {
    if ($method == 'POST') {
        echo json_encode([
            'success' => false,
            'message' => 'ID event tidak valid'
        ]);
        exit();
    } else {
        header('Location: events.php?error=invalid_id');
        exit();
    }
}

// Check if event exists and get event data
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    if ($method == 'POST') {
        echo json_encode([
            'success' => false,
            'message' => 'Event tidak ditemukan'
        ]);
        exit();
    } else {
        header('Location: events.php?error=not_found');
        exit();
    }
}

$event = $result->fetch_assoc();
$stmt->close();

// Get related transactions count
$related_stmt = $conn->prepare("SELECT 
                                    COUNT(p.id) as pemasukan_count,
                                    COUNT(pen.id) as pengeluaran_count,
                                    COALESCE(SUM(p.jumlah), 0) as total_pemasukan,
                                    COALESCE(SUM(pen.gaji_karyawan + pen.rental + pen.bensin + pen.peralatan + pen.konsumsi + pen.modal + pen.dll + pen.prive), 0) as total_pengeluaran
                                FROM events e
                                LEFT JOIN pemasukan p ON e.id = p.event_id
                                LEFT JOIN pengeluaran pen ON e.id = pen.event_id
                                WHERE e.id = ?
                                GROUP BY e.id");
$related_stmt->bind_param("i", $event_id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
$related_data = $related_result->fetch_assoc();
$related_stmt->close();

// Handle POST request (actual deletion)
if ($method == 'POST') {
    header('Content-Type: application/json');
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Delete related pemasukan records
        $delete_pemasukan = $conn->prepare("DELETE FROM pemasukan WHERE event_id = ?");
        $delete_pemasukan->bind_param("i", $event_id);
        $delete_pemasukan->execute();
        $deleted_pemasukan = $delete_pemasukan->affected_rows;
        $delete_pemasukan->close();
        
        // Delete related pengeluaran records
        $delete_pengeluaran = $conn->prepare("DELETE FROM pengeluaran WHERE event_id = ?");
        $delete_pengeluaran->bind_param("i", $event_id);
        $delete_pengeluaran->execute();
        $deleted_pengeluaran = $delete_pengeluaran->affected_rows;
        $delete_pengeluaran->close();
        
        // Delete the event
        $delete_event = $conn->prepare("DELETE FROM events WHERE id = ?");
        $delete_event->bind_param("i", $event_id);
        $delete_event->execute();
        $delete_event->close();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Event dan semua data terkait berhasil dihapus',
            'deleted_data' => [
                'event' => [
                    'id' => $event['id'],
                    'nama_event' => $event['nama_event'],
                    'tanggal' => $event['tanggal']
                ],
                'deleted_pemasukan' => $deleted_pemasukan,
                'deleted_pengeluaran' => $deleted_pengeluaran
            ]
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menghapus event: ' . $e->getMessage()
        ]);
    }
    
    $conn->close();
    exit();
}

// Handle GET request (show confirmation page)
$pageTitle = 'Hapus Event - Graceful Decoration';
?>

<?php ob_start(); ?>

<div class="row justify-content-center">
  <div class="col-md-10 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="text-center mb-4">
          <div class="mb-3">
            <i class="mdi mdi-alert-circle-outline text-warning" style="font-size: 4rem;"></i>
          </div>
          <h4 class="card-title text-danger">Konfirmasi Penghapusan Event</h4>
          <p class="card-description">Apakah Anda yakin ingin menghapus event berikut beserta semua data terkait?</p>
        </div>
        
        <div class="row">
          <div class="col-md-6">
            <div class="card bg-light mb-4">
              <div class="card-header">
                <h6 class="mb-0">Informasi Event</h6>
              </div>
              <div class="card-body">
                <div class="event-detail mb-3">
                  <label class="form-label text-muted">ID Event</label>
                  <div class="fw-bold">#<?php echo str_pad($event['id'], 4, '0', STR_PAD_LEFT); ?></div>
                </div>
                
                <div class="event-detail mb-3">
                  <label class="form-label text-muted">Nama Event</label>
                  <div class="fw-bold"><?php echo htmlspecialchars($event['nama_event']); ?></div>
                </div>
                
                <div class="event-detail mb-3">
                  <label class="form-label text-muted">Tanggal Event</label>
                  <div class="fw-bold"><?php echo date('d F Y', strtotime($event['tanggal'])); ?></div>
                </div>
                
                <div class="event-detail mb-3">
                  <label class="form-label text-muted">Lokasi</label>
                  <div class="fw-bold"><?php echo htmlspecialchars($event['lokasi']) ?: '-'; ?></div>
                </div>
                
                <?php if (!empty($event['deskripsi'])): ?>
                  <div class="event-detail">
                    <label class="form-label text-muted">Deskripsi</label>
                    <div class="fw-bold"><?php echo nl2br(htmlspecialchars($event['deskripsi'])); ?></div>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="card bg-light mb-4">
              <div class="card-header">
                <h6 class="mb-0">Data Terkait yang Akan Dihapus</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-6">
                    <div class="card bg-success text-white mb-3">
                      <div class="card-body text-center p-2">
                        <h5><?php echo $related_data['pemasukan_count'] ?? 0; ?></h5>
                        <small>Transaksi Pemasukan</small>
                        <br><small>Rp <?php echo number_format($related_data['total_pemasukan'] ?? 0, 0, ',', '.'); ?></small>
                      </div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="card bg-danger text-white mb-3">
                      <div class="card-body text-center p-2">
                        <h5><?php echo $related_data['pengeluaran_count'] ?? 0; ?></h5>
                        <small>Transaksi Pengeluaran</small>
                        <br><small>Rp <?php echo number_format($related_data['total_pengeluaran'] ?? 0, 0, ',', '.'); ?></small>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="card <?php echo (($related_data['total_pemasukan'] ?? 0) - ($related_data['total_pengeluaran'] ?? 0)) >= 0 ? 'bg-info' : 'bg-warning'; ?> text-white">
                  <div class="card-body text-center p-2">
                    <h6 class="mb-1">Total Profit/Loss</h6>
                    <strong>Rp <?php echo number_format(($related_data['total_pemasukan'] ?? 0) - ($related_data['total_pengeluaran'] ?? 0), 0, ',', '.'); ?></strong>
                  </div>
                </div>
                
                <div class="mt-3">
                  <small class="text-muted">
                    <i class="mdi mdi-information"></i>
                    Event dibuat pada: <?php echo date('d/m/Y H:i', strtotime($event['created_at'])); ?>
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="alert alert-danger" role="alert">
          <i class="mdi mdi-alert-triangle"></i>
          <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan. Event dan semua data pemasukan/pengeluaran terkait akan dihapus secara permanen dari sistem.
        </div>
        
        <div class="d-flex justify-content-center gap-3">
          <button type="button" class="btn btn-danger" id="confirmDelete">
            <i class="mdi mdi-delete"></i> Ya, Hapus Event dan Semua Data
          </button>
          <a href="events.php" class="btn btn-secondary">
            <i class="mdi mdi-arrow-left"></i> Batal
          </a>
          <a href="events-edit.php?id=<?php echo $event['id']; ?>" class="btn btn-outline-primary">
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
        <div class="spinner-border text-danger" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 mb-0">Menghapus event dan data terkait...</p>
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
        <p class="mb-0">Event dan semua data terkait berhasil dihapus!</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="window.location.href='events.php'">
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
    fetch('events-hapus.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=<?php echo $event_id; ?>'
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
        document.getElementById('errorMessage').textContent = 'Terjadi kesalahan saat menghapus event: ' + error.message;
        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        errorModal.show();
        
        // Re-enable the button
        document.getElementById('confirmDelete').disabled = false;
    });
});

// Handle escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        window.location.href = 'events.php';
    }
});
</script>

<style>
.event-detail {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 0.5rem;
}

.event-detail:last-child {
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