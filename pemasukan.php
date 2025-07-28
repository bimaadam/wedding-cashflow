<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: auth/login.php');
  exit();
}

require_once 'config/koneksi.php';

$pageTitle = 'Pemasukan - Graceful Decoration';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM pemasukan WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: pemasukan.php?message=deleted");
        exit();
    } else {
        $error_message = "Gagal menghapus data: " . $conn->error;
    }
    $stmt->close();
}

// Handle success message
if (isset($_GET['message']) && $_GET['message'] == 'deleted') {
    $success_message = "Data pemasukan berhasil dihapus!";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['event_id'];
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'];
    $jumlah = floatval($_POST['jumlah']);
    
    $stmt = $conn->prepare("INSERT INTO pemasukan (event_id, tanggal, keterangan, jumlah, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("issd", $event_id, $tanggal, $keterangan, $jumlah);
    
    if ($stmt->execute()) {
        $success_message = "Pemasukan berhasil ditambahkan!";
    } else {
        $error_message = "Gagal menambahkan pemasukan: " . $conn->error;
    }
    $stmt->close();
}

// Get events for dropdown
$events_query = "SELECT id, nama_event, tanggal FROM events ORDER BY tanggal DESC";
$events_result = $conn->query($events_query);

// Get pemasukan data with event info
$pemasukan_query = "SELECT p.*, e.nama_event 
                    FROM pemasukan p 
                    LEFT JOIN events e ON p.event_id = e.id 
                    ORDER BY p.tanggal DESC";
$pemasukan_result = $conn->query($pemasukan_query);

// Get statistics
$stats_query = "SELECT 
                  COUNT(*) as total_pemasukan,
                  SUM(jumlah) as total_jumlah,
                  AVG(jumlah) as rata_rata,
                  MAX(jumlah) as tertinggi,
                  MIN(jumlah) as terendah
                FROM pemasukan";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get monthly income
$monthly_query = "SELECT 
                    DATE_FORMAT(tanggal, '%Y-%m') as bulan,
                    SUM(jumlah) as total_bulan,
                    COUNT(*) as jumlah_transaksi
                  FROM pemasukan 
                  WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                  GROUP BY DATE_FORMAT(tanggal, '%Y-%m')
                  ORDER BY bulan DESC";
$monthly_result = $conn->query($monthly_query);

// Get recent transactions
$recent_query = "SELECT p.*, e.nama_event 
                 FROM pemasukan p 
                 LEFT JOIN events e ON p.event_id = e.id 
                 ORDER BY p.created_at DESC 
                 LIMIT 5";
$recent_result = $conn->query($recent_query);
?>

<?php ob_start(); ?>

<div class="row">
  <div class="col-md-8 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Form Pemasukan</h4>
        <p class="card-description">Tambah data pemasukan</p>
        
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
        
        <form method="POST" class="forms-sample">
          <div class="form-group">
            <label for="event_id">Event</label>
            <select class="form-control" id="event_id" name="event_id" required>
              <option value="">-- Pilih Event --</option>
              <?php if ($events_result && $events_result->num_rows > 0): ?>
                <?php while ($event = $events_result->fetch_assoc()): ?>
                  <option value="<?php echo $event['id']; ?>">
                    <?php echo $event['nama_event'] . ' (' . date('d/m/Y', strtotime($event['tanggal'])) . ')'; ?>
                  </option>
                <?php endwhile; ?>
              <?php endif; ?>
            </select>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="tanggal">Tanggal Pemasukan</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="jumlah">Jumlah (Rp)</label>
                <input type="number" class="form-control" id="jumlah" name="jumlah" step="0.01" min="0" required placeholder="0">
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <input type="text" class="form-control" id="keterangan" name="keterangan" placeholder="Deskripsi pemasukan">
          </div>
          
          <div class="form-group">
            <div class="card bg-light">
              <div class="card-body">
                <h6>Preview Pemasukan:</h6>
                <div id="preview-content">
                  <p class="mb-1"><strong>Tanggal:</strong> <span id="preview-tanggal">-</span></p>
                  <p class="mb-1"><strong>Keterangan:</strong> <span id="preview-keterangan">-</span></p>
                  <p class="mb-0"><strong>Jumlah:</strong> <span id="preview-jumlah" class="text-success">Rp 0</span></p>
                </div>
              </div>
            </div>
          </div>
          
          <button type="submit" class="btn btn-primary me-2">Simpan Pemasukan</button>
          <button type="reset" class="btn btn-light">Reset</button>
        </form>
      </div>
    </div>
  </div>
  
  <div class="col-md-4 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Statistik Pemasukan</h4>
        
        <div class="row">
          <div class="col-12">
            <div class="card bg-gradient-success text-white mb-3">
              <div class="card-body text-center">
                <h4>Rp <?php echo number_format($stats['total_jumlah'] ?? 0, 0, ',', '.'); ?></h4>
                <p class="mb-0">Total Pemasukan</p>
              </div>
            </div>
          </div>
          <div class="col-12">
            <div class="card bg-gradient-info text-white mb-3">
              <div class="card-body text-center">
                <h4><?php echo $stats['total_pemasukan'] ?? 0; ?></h4>
                <p class="mb-0">Total Transaksi</p>
              </div>
            </div>
          </div>
        </div>
        
        <h5 class="mt-4">Detail Statistik</h5>
        <div class="list-group">
          <div class="list-group-item d-flex justify-content-between align-items-center">
            Rata-rata
            <span class="badge badge-primary badge-pill">
              Rp <?php echo number_format($stats['rata_rata'] ?? 0, 0, ',', '.'); ?>
            </span>
          </div>
          <div class="list-group-item d-flex justify-content-between align-items-center">
            Tertinggi
            <span class="badge badge-success badge-pill">
              Rp <?php echo number_format($stats['tertinggi'] ?? 0, 0, ',', '.'); ?>
            </span>
          </div>
          <div class="list-group-item d-flex justify-content-between align-items-center">
            Terendah
            <span class="badge badge-warning badge-pill">
              Rp <?php echo number_format($stats['terendah'] ?? 0, 0, ',', '.'); ?>
            </span>
          </div>
        </div>
        
        <h5 class="mt-4">Pemasukan Bulanan</h5>
        <div class="list-group">
          <?php if ($monthly_result && $monthly_result->num_rows > 0): ?>
            <?php while ($month = $monthly_result->fetch_assoc()): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <strong><?php echo date('M Y', strtotime($month['bulan'] . '-01')); ?></strong><br>
                  <small class="text-muted"><?php echo $month['jumlah_transaksi']; ?> transaksi</small>
                </div>
                <span class="badge badge-success badge-pill">
                  Rp <?php echo number_format($month['total_bulan'], 0, ',', '.'); ?>
                </span>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="list-group-item text-center">
              <p class="mb-0 text-muted">Belum ada data pemasukan</p>
            </div>
          <?php endif; ?>
        </div>
        
        <h5 class="mt-4">Transaksi Terbaru</h5>
        <div class="list-group">
          <?php if ($recent_result && $recent_result->num_rows > 0): ?>
            <?php while ($recent = $recent_result->fetch_assoc()): ?>
              <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                  <h6 class="mb-1"><?php echo $recent['nama_event'] ?: 'Event tidak ditemukan'; ?></h6>
                  <small class="text-success">Rp <?php echo number_format($recent['jumlah'], 0, ',', '.'); ?></small>
                </div>
                <p class="mb-1"><?php echo $recent['keterangan'] ?: '-'; ?></p>
                <small><?php echo date('d/m/Y', strtotime($recent['tanggal'])); ?></small>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="list-group-item text-center">
              <p class="mb-0 text-muted">Belum ada transaksi</p>
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
        <h4 class="card-title">Daftar Pemasukan</h4>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Event</th>
                <th>Keterangan</th>
                <th>Jumlah</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($pemasukan_result && $pemasukan_result->num_rows > 0): ?>
                <?php while ($pemasukan = $pemasukan_result->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo date('d/m/Y', strtotime($pemasukan['tanggal'])); ?></td>
                    <td>
                      <?php if ($pemasukan['nama_event']): ?>
                        <strong><?php echo $pemasukan['nama_event']; ?></strong>
                      <?php else: ?>
                        <span class="text-muted">Event tidak ditemukan</span>
                      <?php endif; ?>
                    </td>
                    <td><?php echo $pemasukan['keterangan'] ?: '-'; ?></td>
                    <td>
                      <strong class="text-success">
                        Rp <?php echo number_format($pemasukan['jumlah'], 0, ',', '.'); ?>
                      </strong>
                    </td>
                    <td>
                      <button class="btn btn-outline-primary btn-sm btn-edit" data-id="<?php echo $pemasukan['id']; ?>">Edit</button>
                      <button class="btn btn-outline-danger btn-sm btn-delete" data-id="<?php echo $pemasukan['id']; ?>">Hapus</button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center">Belum ada data pemasukan</td>
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
// Set default date to today
document.getElementById('tanggal').value = new Date().toISOString().split('T')[0];

// Real-time preview
function updatePreview() {
    const tanggal = document.getElementById('tanggal').value;
    const keterangan = document.getElementById('keterangan').value;
    const jumlah = parseFloat(document.getElementById('jumlah').value) || 0;
    
    document.getElementById('preview-tanggal').textContent = tanggal ? new Date(tanggal).toLocaleDateString('id-ID') : '-';
    document.getElementById('preview-keterangan').textContent = keterangan || '-';
    document.getElementById('preview-jumlah').textContent = 'Rp ' + jumlah.toLocaleString('id-ID');
}

// Add event listeners
document.getElementById('tanggal').addEventListener('change', updatePreview);
document.getElementById('keterangan').addEventListener('input', updatePreview);
document.getElementById('jumlah').addEventListener('input', updatePreview);

// Format number input
document.getElementById('jumlah').addEventListener('input', function(e) {
    let value = e.target.value.replace(/[^\d.]/g, '');
    e.target.value = value;
});

// Handle Edit and Delete buttons
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-edit')) {
        e.preventDefault();
        const id = e.target.getAttribute('data-id');
        if (confirm('Fitur edit akan segera tersedia. Untuk sementara, silakan tambah data baru.')) {
            // window.location.href = 'edit-pemasukan.php?id=' + id;
        }
    }
    
    if (e.target.classList.contains('btn-delete')) {
        e.preventDefault();
        const id = e.target.getAttribute('data-id');
        if (confirm('Apakah Anda yakin ingin menghapus data pemasukan ini?')) {
            window.location.href = 'pemasukan.php?delete=' + id;
        }
    }
});
</script>

<?php $content = ob_get_clean(); ?>
<?php include 'layout/main.php'; ?>