<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

require_once 'config/koneksi.php';

$pageTitle = 'Detail Booking - Graceful Decoration';

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

// Get related pemasukan (if any)
$pemasukan_query = "SELECT * FROM pemasukan WHERE keterangan LIKE ? ORDER BY tanggal DESC";
$search_term = '%' . $booking['nama_klien'] . '%';
$pemasukan_stmt = $conn->prepare($pemasukan_query);
$pemasukan_stmt->bind_param("s", $search_term);
$pemasukan_stmt->execute();
$pemasukan_result = $pemasukan_stmt->get_result();

// Get related pengeluaran (if any)
$pengeluaran_query = "SELECT * FROM pengeluaran WHERE keterangan LIKE ? ORDER BY tanggal DESC";
$pengeluaran_stmt = $conn->prepare($pengeluaran_query);
$pengeluaran_stmt->bind_param("s", $search_term);
$pengeluaran_stmt->execute();
$pengeluaran_result = $pengeluaran_stmt->get_result();
?>

<?php ob_start(); ?>

<div class="row">
  <div class="col-md-8 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="card-title">Detail Booking Event</h4>
            <p class="card-description">Informasi lengkap booking #<?php echo str_pad($booking['id'], 4, '0', STR_PAD_LEFT); ?></p>
          </div>
          <div>
            <a href="booking-edit.php?id=<?php echo $booking['id']; ?>" class="btn btn-primary me-2">
              <i class="mdi mdi-pencil"></i> Edit
            </a>
            <a href="jadwal-booking.php" class="btn btn-outline-secondary">
              <i class="mdi mdi-arrow-left"></i> Kembali
            </a>
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-6">
            <div class="card bg-light mb-4">
              <div class="card-header">
                <h6 class="mb-0">Informasi Klien</h6>
              </div>
              <div class="card-body">
                <div class="booking-detail mb-3">
                  <label class="form-label text-muted">Nama Klien</label>
                  <div class="fw-bold"><?php echo htmlspecialchars($booking['nama_klien']); ?></div>
                </div>
                
                <div class="booking-detail mb-3">
                  <label class="form-label text-muted">Kontak WhatsApp</label>
                  <div class="fw-bold">
                    <?php echo htmlspecialchars($booking['kontak_wa']); ?>
                    <a href="https://wa.me/62<?php echo substr($booking['kontak_wa'], 1); ?>" 
                       target="_blank" class="btn btn-sm btn-success ms-2">
                      <i class="mdi mdi-whatsapp"></i> Chat
                    </a>
                  </div>
                </div>
                
                <div class="booking-detail">
                  <label class="form-label text-muted">Jenis Event</label>
                  <div class="fw-bold"><?php echo htmlspecialchars($booking['jenis_event']); ?></div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="card bg-light mb-4">
              <div class="card-header">
                <h6 class="mb-0">Informasi Event</h6>
              </div>
              <div class="card-body">
                <div class="booking-detail mb-3">
                  <label class="form-label text-muted">Tanggal Event</label>
                  <div class="fw-bold">
                    <?php 
                    $event_date = new DateTime($booking['tanggal_booking']);
                    echo $event_date->format('l, d F Y'); 
                    ?>
                  </div>
                </div>
                
                <div class="booking-detail mb-3">
                  <label class="form-label text-muted">Status</label>
                  <div>
                    <span class="badge badge-<?php echo $booking['status'] == 'deal' ? 'success' : ($booking['status'] == 'dp' ? 'info' : ($booking['status'] == 'pending' ? 'warning' : 'danger')); ?> fs-6">
                      <?php echo strtoupper($booking['status']); ?>
                    </span>
                  </div>
                </div>
                
                <div class="booking-detail">
                  <label class="form-label text-muted">Countdown</label>
                  <div class="fw-bold">
                    <?php 
                    $today = new DateTime();
                    $event_date = new DateTime($booking['tanggal_booking']);
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
            </div>
          </div>
        </div>
        
        <?php if (!empty($booking['catatan'])): ?>
          <div class="card bg-light mb-4">
            <div class="card-header">
              <h6 class="mb-0">Catatan</h6>
            </div>
            <div class="card-body">
              <p class="mb-0"><?php echo nl2br(htmlspecialchars($booking['catatan'])); ?></p>
            </div>
          </div>
        <?php endif; ?>
        
        <div class="card bg-light">
          <div class="card-header">
            <h6 class="mb-0">Informasi Sistem</h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="booking-detail">
                  <label class="form-label text-muted">Tanggal Dibuat</label>
                  <div class="fw-bold"><?php echo date('d F Y, H:i', strtotime($booking['created_at'])); ?></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="booking-detail">
                  <label class="form-label text-muted">ID Booking</label>
                  <div class="fw-bold">#<?php echo str_pad($booking['id'], 4, '0', STR_PAD_LEFT); ?></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-md-4 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Aksi Cepat</h4>
        
        <div class="d-grid gap-2 mb-4">
          <button type="button" class="btn btn-outline-success" onclick="updateStatus('deal')">
            <i class="mdi mdi-check-circle"></i> Tandai Deal
          </button>
          <button type="button" class="btn btn-outline-info" onclick="updateStatus('dp')">
            <i class="mdi mdi-cash"></i> Tandai DP
          </button>
          <button type="button" class="btn btn-outline-warning" onclick="updateStatus('pending')">
            <i class="mdi mdi-clock"></i> Tandai Pending
          </button>
          <button type="button" class="btn btn-outline-danger" onclick="updateStatus('cancel')">
            <i class="mdi mdi-close-circle"></i> Tandai Cancel
          </button>
        </div>
        
        <hr>
        
        <h6>Transaksi Terkait</h6>
        
        <!-- Pemasukan Section -->
        <div class="mb-3">
          <h6 class="text-success">Pemasukan</h6>
          <?php if ($pemasukan_result && $pemasukan_result->num_rows > 0): ?>
            <?php $total_pemasukan = 0; ?>
            <?php while ($pemasukan = $pemasukan_result->fetch_assoc()): ?>
              <?php $total_pemasukan += $pemasukan['jumlah']; ?>
              <div class="small-transaction-item">
                <div class="d-flex justify-content-between">
                  <span><?php echo date('d/m/Y', strtotime($pemasukan['tanggal'])); ?></span>
                  <span class="text-success">+Rp <?php echo number_format($pemasukan['jumlah'], 0, ',', '.'); ?></span>
                </div>
                <small class="text-muted"><?php echo htmlspecialchars($pemasukan['keterangan']); ?></small>
              </div>
            <?php endwhile; ?>
            <div class="fw-bold text-success">
              Total: Rp <?php echo number_format($total_pemasukan, 0, ',', '.'); ?>
            </div>
          <?php else: ?>
            <p class="text-muted small">Belum ada pemasukan terkait</p>
          <?php endif; ?>
        </div>
        
        <!-- Pengeluaran Section -->
        <div class="mb-3">
          <h6 class="text-danger">Pengeluaran</h6>
          <?php if ($pengeluaran_result && $pengeluaran_result->num_rows > 0): ?>
            <?php $total_pengeluaran = 0; ?>
            <?php while ($pengeluaran = $pengeluaran_result->fetch_assoc()): ?>
              <?php 
              $jumlah_pengeluaran = $pengeluaran['gaji_karyawan'] + $pengeluaran['rental'] + 
                                  $pengeluaran['bensin'] + $pengeluaran['peralatan'] + 
                                  $pengeluaran['konsumsi'] + $pengeluaran['modal'] + 
                                  $pengeluaran['dll'] + $pengeluaran['prive'];
              $total_pengeluaran += $jumlah_pengeluaran;
              ?>
              <div class="small-transaction-item">
                <div class="d-flex justify-content-between">
                  <span><?php echo date('d/m/Y', strtotime($pengeluaran['tanggal'])); ?></span>
                  <span class="text-danger">-Rp <?php echo number_format($jumlah_pengeluaran, 0, ',', '.'); ?></span>
                </div>
                <small class="text-muted"><?php echo htmlspecialchars($pengeluaran['keterangan']); ?></small>
              </div>
            <?php endwhile; ?>
            <div class="fw-bold text-danger">
              Total: Rp <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?>
            </div>
          <?php else: ?>
            <p class="text-muted small">Belum ada pengeluaran terkait</p>
          <?php endif; ?>
        </div>
        
        <?php if (isset($total_pemasukan) && isset($total_pengeluaran)): ?>
          <hr>
          <div class="fw-bold <?php echo ($total_pemasukan - $total_pengeluaran) >= 0 ? 'text-success' : 'text-danger'; ?>">
            Net: Rp <?php echo number_format($total_pemasukan - $total_pengeluaran, 0, ',', '.'); ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
// Quick status update
function updateStatus(status) {
    const statusText = {
        'pending': 'Pending',
        'dp': 'DP',
        'deal': 'Deal',
        'cancel': 'Cancel'
    };
    
    if (confirm(`Ubah status booking menjadi ${statusText[status]}?`)) {
        // Create form data
        const formData = new FormData();
        formData.append('id', <?php echo $booking_id; ?>);
        formData.append('nama_klien', '<?php echo addslashes($booking['nama_klien']); ?>');
        formData.append('tanggal_booking', '<?php echo $booking['tanggal_booking']; ?>');
        formData.append('jenis_event', '<?php echo addslashes($booking['jenis_event']); ?>');
        formData.append('kontak_wa', '<?php echo $booking['kontak_wa']; ?>');
        formData.append('status', status);
        formData.append('catatan', '<?php echo addslashes($booking['catatan']); ?>');
        
        // Send update request
        fetch('booking-simpan.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload page to show updated status
                window.location.reload();
            } else {
                alert('Gagal mengubah status: ' + data.message);
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error.message);
        });
    }
}
</script>

<style>
.booking-detail {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 0.5rem;
    margin-bottom: 0.5rem;
}

.booking-detail:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.small-transaction-item {
    padding: 0.5rem;
    border: 1px solid #eee;
    border-radius: 0.25rem;
    margin-bottom: 0.5rem;
    background-color: #f8f9fa;
}

.small-transaction-item:last-child {
    margin-bottom: 0;
}

.fs-6 {
    font-size: 1rem !important;
}
</style>

<?php $content = ob_get_clean(); ?>
<?php include 'layout/main.php'; ?>