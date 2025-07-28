<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

require_once 'config/koneksi.php';

$pageTitle = 'Edit Event - Graceful Decoration';

// Get event ID from URL
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($event_id <= 0) {
    header('Location: events.php?error=invalid_id');
    exit();
}

// Get event data
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: events.php?error=not_found');
    exit();
}

$event = $result->fetch_assoc();
$stmt->close();

// Get cashflow data for this event
$cashflow_stmt = $conn->prepare("SELECT 
                                    COALESCE(SUM(p.jumlah), 0) as total_pemasukan,
                                    COALESCE(SUM(pen.gaji_karyawan + pen.rental + pen.bensin + pen.peralatan + pen.konsumsi + pen.modal + pen.dll + pen.prive), 0) as total_pengeluaran,
                                    COUNT(p.id) as jumlah_pemasukan,
                                    COUNT(pen.id) as jumlah_pengeluaran
                                 FROM events e
                                 LEFT JOIN pemasukan p ON e.id = p.event_id
                                 LEFT JOIN pengeluaran pen ON e.id = pen.event_id
                                 WHERE e.id = ?
                                 GROUP BY e.id");
$cashflow_stmt->bind_param("i", $event_id);
$cashflow_stmt->execute();
$cashflow_result = $cashflow_stmt->get_result();
$cashflow = $cashflow_result->fetch_assoc();
$cashflow_stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_event = trim($_POST['nama_event']);
    $tanggal = $_POST['tanggal'];
    $lokasi = trim($_POST['lokasi']);
    $deskripsi = trim($_POST['deskripsi']);
    
    // Validasi input
    if (empty($nama_event) || empty($tanggal)) {
        $error_message = "Nama event dan tanggal harus diisi";
    } else {
        $stmt = $conn->prepare("UPDATE events SET nama_event = ?, tanggal = ?, lokasi = ?, deskripsi = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nama_event, $tanggal, $lokasi, $deskripsi, $event_id);
        
        if ($stmt->execute()) {
            header('Location: events.php?message=updated');
            exit();
        } else {
            $error_message = "Gagal memperbarui event: " . $conn->error;
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
            <h4 class="card-title">Edit Event</h4>
            <p class="card-description">Perbarui data event</p>
          </div>
          <a href="events.php" class="btn btn-outline-secondary">
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
          <div class="form-group">
            <label for="nama_event">Nama Event <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nama_event" name="nama_event" 
                   value="<?php echo htmlspecialchars($event['nama_event']); ?>" 
                   required placeholder="Nama event">
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="tanggal">Tanggal Event <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" 
                       value="<?php echo $event['tanggal']; ?>" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="lokasi">Lokasi</label>
                <input type="text" class="form-control" id="lokasi" name="lokasi" 
                       value="<?php echo htmlspecialchars($event['lokasi']); ?>" 
                       placeholder="Lokasi event">
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" 
                      placeholder="Deskripsi event"><?php echo htmlspecialchars($event['deskripsi']); ?></textarea>
          </div>
          
          <div class="form-group">
            <small class="text-muted">
              <i class="mdi mdi-information"></i>
              Event dibuat pada: <?php echo date('d/m/Y H:i', strtotime($event['created_at'])); ?>
            </small>
          </div>
          
          <div class="d-flex justify-content-between">
            <div>
              <button type="submit" class="btn btn-primary me-2">
                <i class="mdi mdi-content-save"></i> Update Event
              </button>
              <a href="events.php" class="btn btn-light">
                <i class="mdi mdi-cancel"></i> Batal
              </a>
            </div>
            <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
              <i class="mdi mdi-delete"></i> Hapus Event
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <div class="col-md-4 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Informasi Event</h4>
        
        <div class="event-info">
          <div class="info-item mb-3">
            <label class="form-label">ID Event</label>
            <div class="info-value">#<?php echo str_pad($event['id'], 4, '0', STR_PAD_LEFT); ?></div>
          </div>
          
          <div class="info-item mb-3">
            <label class="form-label">Status Event</label>
            <div class="info-value">
              <?php 
              $is_upcoming = strtotime($event['tanggal']) >= strtotime(date('Y-m-d'));
              ?>
              <span class="badge badge-<?php echo $is_upcoming ? 'primary' : 'secondary'; ?> fs-6">
                <?php echo $is_upcoming ? 'Mendatang' : 'Selesai'; ?>
              </span>
            </div>
          </div>
          
          <div class="info-item mb-3">
            <label class="form-label">Tanggal Dibuat</label>
            <div class="info-value"><?php echo date('d F Y, H:i', strtotime($event['created_at'])); ?></div>
          </div>
          
          <div class="info-item mb-3">
            <label class="form-label">Hari Event</label>
            <div class="info-value">
              <?php 
              $event_date = new DateTime($event['tanggal']);
              echo $event_date->format('l, d F Y'); 
              ?>
            </div>
          </div>
          
          <div class="info-item mb-3">
            <label class="form-label">Countdown</label>
            <div class="info-value">
              <?php 
              $today = new DateTime();
              $event_date = new DateTime($event['tanggal']);
              $diff = $today->diff($event_date);
              
              if ($event_date < $today) {
                  echo '<span class="text-muted">Event sudah berlalu (' . $diff->days . ' hari yang lalu)</span>';
              } elseif ($event_date->format('Y-m-d') == $today->format('Y-m-d')) {
                  echo '<span class="text-success fw-bold">HARI INI!</span>';
              } else {
                  echo '<span class="text-primary">' . $diff->days . ' hari lagi</span>';
              }
              ?>
            </div>
          </div>
        </div>
        
        <hr>
        
        <div class="cashflow-summary">
          <h6>Ringkasan Cashflow</h6>
          
          <div class="row">
            <div class="col-6">
              <div class="card bg-success text-white mb-2">
                <div class="card-body p-2 text-center">
                  <h6 class="mb-1">Pemasukan</h6>
                  <small>Rp <?php echo number_format($cashflow['total_pemasukan'] ?? 0, 0, ',', '.'); ?></small>
                  <br><small><?php echo $cashflow['jumlah_pemasukan'] ?? 0; ?> transaksi</small>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="card bg-danger text-white mb-2">
                <div class="card-body p-2 text-center">
                  <h6 class="mb-1">Pengeluaran</h6>
                  <small>Rp <?php echo number_format($cashflow['total_pengeluaran'] ?? 0, 0, ',', '.'); ?></small>
                  <br><small><?php echo $cashflow['jumlah_pengeluaran'] ?? 0; ?> transaksi</small>
                </div>
              </div>
            </div>
          </div>
          
          <div class="card <?php echo ($cashflow['total_pemasukan'] - $cashflow['total_pengeluaran']) >= 0 ? 'bg-success' : 'bg-danger'; ?> text-white">
            <div class="card-body p-2 text-center">
              <h6 class="mb-1">Profit/Loss</h6>
              <strong>Rp <?php echo number_format(($cashflow['total_pemasukan'] ?? 0) - ($cashflow['total_pengeluaran'] ?? 0), 0, ',', '.'); ?></strong>
            </div>
          </div>
        </div>
        
        <hr>
        
        <div class="quick-actions">
          <h6>Aksi Cepat</h6>
          <div class="d-grid gap-2">
            <a href="events-detail.php?id=<?php echo $event_id; ?>" class="btn btn-outline-info btn-sm">
              <i class="mdi mdi-eye"></i> Lihat Detail
            </a>
            <a href="pemasukan.php?event_id=<?php echo $event_id; ?>" class="btn btn-outline-success btn-sm">
              <i class="mdi mdi-plus-circle"></i> Tambah Pemasukan
            </a>
            <a href="pengeluaran.php?event_id=<?php echo $event_id; ?>" class="btn btn-outline-warning btn-sm">
              <i class="mdi mdi-minus-circle"></i> Tambah Pengeluaran
            </a>
            <a href="cashflow.php?event_id=<?php echo $event_id; ?>" class="btn btn-outline-primary btn-sm">
              <i class="mdi mdi-chart-line"></i> Lihat Cashflow
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Confirm delete
function confirmDelete() {
    if (confirm('Apakah Anda yakin ingin menghapus event ini? Semua data pemasukan dan pengeluaran terkait akan ikut terhapus.')) {
        window.location.href = 'events-hapus.php?id=<?php echo $event_id; ?>';
    }
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const requiredFields = ['nama_event', 'tanggal'];
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

// Auto-resize textarea
document.getElementById('deskripsi').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
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

.fs-6 {
    font-size: 1rem !important;
}
</style>

<?php $content = ob_get_clean(); ?>
<?php include 'layout/main.php'; ?>