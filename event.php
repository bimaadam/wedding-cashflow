<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: auth/login.php');
  exit();
}

require_once 'config/koneksi.php';

$pageTitle = 'Jadwal Booking - Graceful Decoration';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_klien = $_POST['nama_klien'];
    $tanggal_booking = $_POST['tanggal_booking'];
    $jenis_event = $_POST['jenis_event'];
    $kontak_wa = $_POST['kontak_wa'];
    $status = $_POST['status'] ?? 'pending';
    $catatan = $_POST['catatan'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO booking (nama_klien, tanggal_booking, jenis_event, kontak_wa, status, catatan, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssss", $nama_klien, $tanggal_booking, $jenis_event, $kontak_wa, $status, $catatan);
    
    if ($stmt->execute()) {
        $success_message = "Booking berhasil ditambahkan!";
    } else {
        $error_message = "Gagal menambahkan booking: " . $conn->error;
    }
    $stmt->close();
}

// Get bookings
$bookings_query = "SELECT * FROM booking ORDER BY tanggal_booking ASC";
$bookings_result = $conn->query($bookings_query);

// Get upcoming bookings
$upcoming_query = "SELECT * FROM booking WHERE tanggal_booking >= CURDATE() ORDER BY tanggal_booking ASC LIMIT 5";
$upcoming_result = $conn->query($upcoming_query);
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
        
        <form method="POST" class="forms-sample">
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
        
        <?php
        $stats_query = "SELECT 
                          COUNT(*) as total_bookings,
                          COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_bookings,
                          COUNT(CASE WHEN status = 'dp' THEN 1 END) as dp_bookings,
                          COUNT(CASE WHEN status = 'deal' THEN 1 END) as deal_bookings,
                          COUNT(CASE WHEN status = 'cancel' THEN 1 END) as cancel_bookings,
                          COUNT(CASE WHEN tanggal_booking >= CURDATE() THEN 1 END) as upcoming_bookings
                        FROM booking";
        $stats_result = $conn->query($stats_query);
        $stats = $stats_result->fetch_assoc();
        ?>
        
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
          <div class="col-6">
            <div class="card bg-gradient-success text-white mb-3">
              <div class="card-body text-center">
                <h4><?php echo $stats['deal_bookings']; ?></h4>
                <p class="mb-0">Deal</p>
              </div>
            </div>
          </div>
        </div>
        
        <h5 class="mt-4">Booking Mendatang</h5>
        <div class="list-group">
          <?php if ($upcoming_result && $upcoming_result->num_rows > 0): ?>
            <?php while ($booking = $upcoming_result->fetch_assoc()): ?>
              <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                  <h6 class="mb-1"><?php echo $booking['nama_klien']; ?></h6>
                  <small class="badge badge-<?php echo $booking['status'] == 'deal' ? 'success' : ($booking['status'] == 'dp' ? 'info' : ($booking['status'] == 'pending' ? 'warning' : 'danger')); ?>">
                    <?php echo strtoupper($booking['status']); ?>
                  </small>
                </div>
                <p class="mb-1"><?php echo $booking['jenis_event']; ?></p>
                <small><?php echo date('d/m/Y', strtotime($booking['tanggal_booking'])); ?></small>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="list-group-item text-center">
              <p class="mb-0 text-muted">Tidak ada booking mendatang</p>
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
        <h4 class="card-title">Daftar Semua Booking</h4>
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
                      <strong><?php echo date('d/m/Y', strtotime($booking['tanggal_booking'])); ?></strong>
                    </td>
                    <td>
                      <strong><?php echo $booking['nama_klien']; ?></strong>
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
                      <button class="btn btn-outline-primary btn-sm">Edit</button>
                      <button class="btn btn-outline-danger btn-sm">Hapus</button>
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
</script>

<?php $content = ob_get_clean(); ?>
<?php include 'layout/main.php'; ?>