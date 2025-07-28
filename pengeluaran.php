<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: auth/login.php');
  exit();
}

require_once 'config/koneksi.php';

$pageTitle = 'Pengeluaran - Graceful Decoration';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM pengeluaran WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: pengeluaran.php?message=deleted");
        exit();
    } else {
        $error_message = "Gagal menghapus data: " . $conn->error;
    }
    $stmt->close();
}

// Handle success message
if (isset($_GET['message']) && $_GET['message'] == 'deleted') {
    $success_message = "Data pengeluaran berhasil dihapus!";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['event_id'];
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'] ?? '';
    $gaji_karyawan = floatval($_POST['gaji_karyawan']);
    $rental = floatval($_POST['rental']);
    $bensin = floatval($_POST['bensin']);
    $peralatan = floatval($_POST['peralatan']);
    $konsumsi = floatval($_POST['konsumsi']);
    $modal = floatval($_POST['modal']);
    $dll = floatval($_POST['dll']);
    $prive = floatval($_POST['prive']);
    
    $stmt = $conn->prepare("INSERT INTO pengeluaran (event_id, tanggal, keterangan, gaji_karyawan, rental, bensin, peralatan, konsumsi, modal, dll, prive, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issddddddddd", $event_id, $tanggal, $keterangan, $gaji_karyawan, $rental, $bensin, $peralatan, $konsumsi, $modal, $dll, $prive);
    
    if ($stmt->execute()) {
        $success_message = "Pengeluaran berhasil ditambahkan!";
    } else {
        $error_message = "Gagal menambahkan pengeluaran: " . $conn->error;
    }
    $stmt->close();
}

// Get events for dropdown
$events_query = "SELECT id, nama_event, tanggal FROM events ORDER BY tanggal DESC";
$events_result = $conn->query($events_query);

// Get pengeluaran data with event info
$pengeluaran_query = "SELECT p.*, e.nama_event,
                      (p.gaji_karyawan + p.rental + p.bensin + p.peralatan + p.konsumsi + p.modal + p.dll + p.prive) as total_pengeluaran
                      FROM pengeluaran p 
                      LEFT JOIN events e ON p.event_id = e.id 
                      ORDER BY p.tanggal DESC";
$pengeluaran_result = $conn->query($pengeluaran_query);

// Get statistics
$stats_query = "SELECT 
                  COUNT(*) as total_transaksi,
                  SUM(gaji_karyawan + rental + bensin + peralatan + konsumsi + modal + dll + prive) as total_pengeluaran,
                  SUM(gaji_karyawan) as total_gaji,
                  SUM(rental) as total_rental,
                  SUM(bensin) as total_bensin,
                  SUM(peralatan) as total_peralatan,
                  SUM(konsumsi) as total_konsumsi,
                  SUM(modal) as total_modal,
                  SUM(dll) as total_dll,
                  SUM(prive) as total_prive,
                  AVG(gaji_karyawan + rental + bensin + peralatan + konsumsi + modal + dll + prive) as rata_rata
                FROM pengeluaran";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get monthly expenses
$monthly_query = "SELECT 
                    DATE_FORMAT(tanggal, '%Y-%m') as bulan,
                    SUM(gaji_karyawan + rental + bensin + peralatan + konsumsi + modal + dll + prive) as total_bulan
                  FROM pengeluaran 
                  WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                  GROUP BY DATE_FORMAT(tanggal, '%Y-%m')
                  ORDER BY bulan DESC";
$monthly_result = $conn->query($monthly_query);
?>

<?php ob_start(); ?>

<div class="row">
  <div class="col-md-8 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Form Pengeluaran</h4>
        <p class="card-description">Tambah data pengeluaran per kategori</p>
        
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
                <label for="tanggal">Tanggal Pengeluaran</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="keterangan">Keterangan</label>
                <input type="text" class="form-control" id="keterangan" name="keterangan" placeholder="Deskripsi pengeluaran">
              </div>
            </div>
          </div>
          
          <h5 class="mt-4 mb-3">Detail Pengeluaran per Kategori</h5>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="gaji_karyawan">Gaji Karyawan (Rp)</label>
                <input type="number" class="form-control expense-input" id="gaji_karyawan" name="gaji_karyawan" step="0.01" min="0" value="0" placeholder="0">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="rental">Rental (Rp)</label>
                <input type="number" class="form-control expense-input" id="rental" name="rental" step="0.01" min="0" value="0" placeholder="0">
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="bensin">Bensin (Rp)</label>
                <input type="number" class="form-control expense-input" id="bensin" name="bensin" step="0.01" min="0" value="0" placeholder="0">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="peralatan">Peralatan (Rp)</label>
                <input type="number" class="form-control expense-input" id="peralatan" name="peralatan" step="0.01" min="0" value="0" placeholder="0">
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="konsumsi">Konsumsi (Rp)</label>
                <input type="number" class="form-control expense-input" id="konsumsi" name="konsumsi" step="0.01" min="0" value="0" placeholder="0">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="modal">Modal (Rp)</label>
                <input type="number" class="form-control expense-input" id="modal" name="modal" step="0.01" min="0" value="0" placeholder="0">
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="dll">DLL/Lainnya (Rp)</label>
                <input type="number" class="form-control expense-input" id="dll" name="dll" step="0.01" min="0" value="0" placeholder="0">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="prive">Prive (Rp)</label>
                <input type="number" class="form-control expense-input" id="prive" name="prive" step="0.01" min="0" value="0" placeholder="0">
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <div class="card bg-light">
              <div class="card-body">
                <h6>Total Pengeluaran:</h6>
                <h4 class="text-danger" id="total-pengeluaran">Rp 0</h4>
              </div>
            </div>
          </div>
          
          <button type="submit" class="btn btn-primary me-2">Simpan Pengeluaran</button>
          <button type="reset" class="btn btn-light" onclick="resetForm()">Reset</button>
        </form>
      </div>
    </div>
  </div>
  
  <div class="col-md-4 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Statistik Pengeluaran</h4>
        
        <div class="row">
          <div class="col-12">
            <div class="card bg-gradient-danger text-white mb-3">
              <div class="card-body text-center">
                <h4>Rp <?php echo number_format($stats['total_pengeluaran'] ?? 0, 0, ',', '.'); ?></h4>
                <p class="mb-0">Total Pengeluaran</p>
              </div>
            </div>
          </div>
          <div class="col-12">
            <div class="card bg-gradient-info text-white mb-3">
              <div class="card-body text-center">
                <h4><?php echo $stats['total_transaksi'] ?? 0; ?></h4>
                <p class="mb-0">Total Transaksi</p>
              </div>
            </div>
          </div>
        </div>
        
        <h5 class="mt-4">Pengeluaran per Kategori</h5>
        <div class="list-group">
          <div class="list-group-item d-flex justify-content-between align-items-center">
            Gaji Karyawan
            <span class="badge badge-primary badge-pill">
              Rp <?php echo number_format($stats['total_gaji'] ?? 0, 0, ',', '.'); ?>
            </span>
          </div>
          <div class="list-group-item d-flex justify-content-between align-items-center">
            Rental
            <span class="badge badge-success badge-pill">
              Rp <?php echo number_format($stats['total_rental'] ?? 0, 0, ',', '.'); ?>
            </span>
          </div>
          <div class="list-group-item d-flex justify-content-between align-items-center">
            Bensin
            <span class="badge badge-warning badge-pill">
              Rp <?php echo number_format($stats['total_bensin'] ?? 0, 0, ',', '.'); ?>
            </span>
          </div>
          <div class="list-group-item d-flex justify-content-between align-items-center">
            Peralatan
            <span class="badge badge-info badge-pill">
              Rp <?php echo number_format($stats['total_peralatan'] ?? 0, 0, ',', '.'); ?>
            </span>
          </div>
          <div class="list-group-item d-flex justify-content-between align-items-center">
            Konsumsi
            <span class="badge badge-secondary badge-pill">
              Rp <?php echo number_format($stats['total_konsumsi'] ?? 0, 0, ',', '.'); ?>
            </span>
          </div>
          <div class="list-group-item d-flex justify-content-between align-items-center">
            Modal
            <span class="badge badge-dark badge-pill">
              Rp <?php echo number_format($stats['total_modal'] ?? 0, 0, ',', '.'); ?>
            </span>
          </div>
          <div class="list-group-item d-flex justify-content-between align-items-center">
            DLL/Lainnya
            <span class="badge badge-light badge-pill">
              Rp <?php echo number_format($stats['total_dll'] ?? 0, 0, ',', '.'); ?>
            </span>
          </div>
          <div class="list-group-item d-flex justify-content-between align-items-center">
            Prive
            <span class="badge badge-danger badge-pill">
              Rp <?php echo number_format($stats['total_prive'] ?? 0, 0, ',', '.'); ?>
            </span>
          </div>
        </div>
        
        <h5 class="mt-4">Pengeluaran Bulanan</h5>
        <div class="list-group">
          <?php if ($monthly_result && $monthly_result->num_rows > 0): ?>
            <?php while ($month = $monthly_result->fetch_assoc()): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <?php echo date('M Y', strtotime($month['bulan'] . '-01')); ?>
                <span class="badge badge-dark badge-pill">
                  Rp <?php echo number_format($month['total_bulan'], 0, ',', '.'); ?>
                </span>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="list-group-item text-center">
              <p class="mb-0 text-muted">Belum ada data pengeluaran</p>
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
        <h4 class="card-title">Daftar Pengeluaran</h4>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Event</th>
                <th>Gaji</th>
                <th>Rental</th>
                <th>Bensin</th>
                <th>Peralatan</th>
                <th>Konsumsi</th>
                <th>Modal</th>
                <th>DLL</th>
                <th>Prive</th>
                <th>Total</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($pengeluaran_result && $pengeluaran_result->num_rows > 0): ?>
                <?php while ($pengeluaran = $pengeluaran_result->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo date('d/m/Y', strtotime($pengeluaran['tanggal'])); ?></td>
                    <td>
                      <?php if ($pengeluaran['nama_event']): ?>
                        <strong><?php echo $pengeluaran['nama_event']; ?></strong>
                      <?php else: ?>
                        <span class="text-muted">Event tidak ditemukan</span>
                      <?php endif; ?>
                    </td>
                    <td>Rp <?php echo number_format($pengeluaran['gaji_karyawan'], 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format($pengeluaran['rental'], 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format($pengeluaran['bensin'], 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format($pengeluaran['peralatan'], 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format($pengeluaran['konsumsi'], 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format($pengeluaran['modal'], 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format($pengeluaran['dll'], 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format($pengeluaran['prive'], 0, ',', '.'); ?></td>
                    <td>
                      <strong class="text-danger">
                        Rp <?php echo number_format($pengeluaran['total_pengeluaran'], 0, ',', '.'); ?>
                      </strong>
                    </td>
                    <td>
                      <button class="btn btn-outline-primary btn-sm btn-edit" data-id="<?php echo $pengeluaran['id']; ?>">Edit</button>
                      <button class="btn btn-outline-danger btn-sm btn-delete" data-id="<?php echo $pengeluaran['id']; ?>">Hapus</button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="12" class="text-center">Belum ada data pengeluaran</td>
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

// Calculate total expenses
function calculateTotal() {
    const inputs = document.querySelectorAll('.expense-input');
    let total = 0;
    
    inputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    
    document.getElementById('total-pengeluaran').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

// Add event listeners to all expense inputs
document.querySelectorAll('.expense-input').forEach(input => {
    input.addEventListener('input', calculateTotal);
});

// Reset form function
function resetForm() {
    document.querySelectorAll('.expense-input').forEach(input => {
        input.value = '0';
    });
    calculateTotal();
}

// Format number inputs
document.querySelectorAll('.expense-input').forEach(input => {
    input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^\d.]/g, '');
        e.target.value = value;
    });
});

// Initial calculation
calculateTotal();

// Handle Edit button
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-edit')) {
        e.preventDefault();
        const id = e.target.getAttribute('data-id');
        // Implementasi edit - bisa menggunakan modal atau redirect
        if (confirm('Fitur edit akan segera tersedia. Untuk sementara, silakan tambah data baru.')) {
            // window.location.href = 'edit-pengeluaran.php?id=' + id;
        }
    }
    
    if (e.target.classList.contains('btn-delete')) {
        e.preventDefault();
        const id = e.target.getAttribute('data-id');
        if (confirm('Apakah Anda yakin ingin menghapus data pengeluaran ini?')) {
            // Implementasi delete
            window.location.href = 'pengeluaran.php?delete=' + id;
        }
    }
});
</script>

<?php $content = ob_get_clean(); ?>
<?php include 'layout/main.php'; ?>