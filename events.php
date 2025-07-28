<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: auth/login.php');
  exit();
}

require_once 'config/koneksi.php';

$pageTitle = 'Events Management - Graceful Decoration';

// Handle success messages
$success_message = '';
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'deleted':
            $success_message = "Data event berhasil dihapus!";
            break;
        case 'updated':
            $success_message = "Data event berhasil diperbarui!";
            break;
        case 'created':
            $success_message = "Data event berhasil ditambahkan!";
            break;
    }
}

// Handle error messages
$error_message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_id':
            $error_message = "ID event tidak valid!";
            break;
        case 'not_found':
            $error_message = "Data event tidak ditemukan!";
            break;
    }
}

// Get events
$events_query = "SELECT * FROM events ORDER BY tanggal DESC";
$events_result = $conn->query($events_query);

// Get upcoming events
$upcoming_query = "SELECT * FROM events WHERE tanggal >= CURDATE() ORDER BY tanggal ASC LIMIT 5";
$upcoming_result = $conn->query($upcoming_query);

// Get statistics
$stats_query = "SELECT 
                  COUNT(*) as total_events,
                  COUNT(CASE WHEN tanggal >= CURDATE() THEN 1 END) as upcoming_events,
                  COUNT(CASE WHEN tanggal < CURDATE() THEN 1 END) as past_events
                FROM events";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get events with cashflow data
$cashflow_query = "SELECT 
                    e.*,
                    COALESCE(SUM(p.jumlah), 0) as total_pemasukan,
                    COALESCE(SUM(pen.gaji_karyawan + pen.rental + pen.bensin + pen.peralatan + pen.konsumsi + pen.modal + pen.dll + pen.prive), 0) as total_pengeluaran
                   FROM events e
                   LEFT JOIN pemasukan p ON e.id = p.event_id
                   LEFT JOIN pengeluaran pen ON e.id = pen.event_id
                   GROUP BY e.id
                   ORDER BY e.tanggal DESC";
$cashflow_result = $conn->query($cashflow_query);
?>

<?php ob_start(); ?>

<div class="row">
  <div class="col-md-8 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Form Event</h4>
        <p class="card-description">Tambah event baru</p>
        
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
        
        <form method="POST" action="events-simpan.php" class="forms-sample" id="eventForm">
          <div class="form-group">
            <label for="nama_event">Nama Event</label>
            <input type="text" class="form-control" id="nama_event" name="nama_event" required placeholder="Nama event">
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="tanggal">Tanggal Event</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="lokasi">Lokasi</label>
                <input type="text" class="form-control" id="lokasi" name="lokasi" placeholder="Lokasi event">
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="deskripsi">Deskripsi</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" placeholder="Deskripsi event"></textarea>
          </div>
          
          <button type="submit" class="btn btn-primary me-2">Simpan Event</button>
          <button type="reset" class="btn btn-light">Reset</button>
        </form>
      </div>
    </div>
  </div>
  
  <div class="col-md-4 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Statistik Event</h4>
        
        <div class="row">
          <div class="col-12">
            <div class="card bg-gradient-primary text-white mb-3">
              <div class="card-body text-center">
                <h4><?php echo $stats['total_events']; ?></h4>
                <p class="mb-0">Total Events</p>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="card bg-gradient-success text-white mb-3">
              <div class="card-body text-center">
                <h4><?php echo $stats['upcoming_events']; ?></h4>
                <p class="mb-0">Mendatang</p>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="card bg-gradient-secondary text-white mb-3">
              <div class="card-body text-center">
                <h4><?php echo $stats['past_events']; ?></h4>
                <p class="mb-0">Selesai</p>
              </div>
            </div>
          </div>
        </div>
        
        <h5 class="mt-4">Events Mendatang</h5>
        <div class="list-group">
          <?php if ($upcoming_result && $upcoming_result->num_rows > 0): ?>
            <?php while ($event = $upcoming_result->fetch_assoc()): ?>
              <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                  <h6 class="mb-1"><?php echo $event['nama_event']; ?></h6>
                  <small><?php echo date('d/m/Y', strtotime($event['tanggal'])); ?></small>
                </div>
                <p class="mb-1"><?php echo $event['lokasi'] ?: '-'; ?></p>
                <small><?php echo substr($event['deskripsi'], 0, 50) . (strlen($event['deskripsi']) > 50 ? '...' : ''); ?></small>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="list-group-item text-center">
              <p class="mb-0 text-muted">Tidak ada event mendatang</p>
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
        <h4 class="card-title">Daftar Events dengan Cashflow</h4>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Nama Event</th>
                <th>Lokasi</th>
                <th>Pemasukan</th>
                <th>Pengeluaran</th>
                <th>Profit/Loss</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($cashflow_result && $cashflow_result->num_rows > 0): ?>
                <?php while ($event = $cashflow_result->fetch_assoc()): ?>
                  <?php 
                    $profit_loss = $event['total_pemasukan'] - $event['total_pengeluaran'];
                    $is_upcoming = strtotime($event['tanggal']) >= strtotime(date('Y-m-d'));
                  ?>
                  <tr>
                    <td>
                      <strong><?php echo date('d/m/Y', strtotime($event['tanggal'])); ?></strong>
                    </td>
                    <td>
                      <strong><?php echo $event['nama_event']; ?></strong><br>
                      <small class="text-muted">
                        <a href="events-detail.php?id=<?php echo $event['id']; ?>" class="text-decoration-none">
                          Lihat Detail
                        </a>
                      </small><br>
                      <small class="text-muted"><?php echo substr($event['deskripsi'], 0, 30) . (strlen($event['deskripsi']) > 30 ? '...' : ''); ?></small>
                    </td>
                    <td><?php echo $event['lokasi'] ?: '-'; ?></td>
                    <td>
                      <span class="text-success">
                        Rp <?php echo number_format($event['total_pemasukan'], 0, ',', '.'); ?>
                      </span>
                    </td>
                    <td>
                      <span class="text-danger">
                        Rp <?php echo number_format($event['total_pengeluaran'], 0, ',', '.'); ?>
                      </span>
                    </td>
                    <td>
                      <span class="<?php echo $profit_loss >= 0 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo $profit_loss >= 0 ? '+' : ''; ?>Rp <?php echo number_format($profit_loss, 0, ',', '.'); ?>
                      </span>
                    </td>
                    <td>
                      <span class="badge badge-<?php echo $is_upcoming ? 'primary' : 'secondary'; ?>">
                        <?php echo $is_upcoming ? 'Mendatang' : 'Selesai'; ?>
                      </span>
                    </td>
                    <td>
                      <a href="events-edit.php?id=<?php echo $event['id']; ?>" class="btn btn-outline-primary btn-sm">
                        <i class="mdi mdi-pencil"></i> Edit
                      </a>
                      <a href="events-detail.php?id=<?php echo $event['id']; ?>" class="btn btn-outline-info btn-sm">
                        <i class="mdi mdi-eye"></i> Detail
                      </a>
                      <a href="events-hapus.php?id=<?php echo $event['id']; ?>" class="btn btn-outline-danger btn-sm">
                        <i class="mdi mdi-delete"></i> Hapus
                      </a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center">Belum ada event yang terdaftar</td>
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
// Set minimum date to today for new events
document.getElementById('tanggal').min = new Date().toISOString().split('T')[0];

// Handle form submission with AJAX
document.getElementById('eventForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Disable submit button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Menyimpan...';
    
    fetch('events-simpan.php', {
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
                window.location.href = 'events.php?message=created';
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
    const form = document.getElementById('eventForm');
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