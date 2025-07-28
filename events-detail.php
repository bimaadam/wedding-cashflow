<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

require_once 'config/koneksi.php';

$pageTitle = 'Detail Event - Graceful Decoration';

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

// Get pemasukan for this event
$pemasukan_stmt = $conn->prepare("SELECT * FROM pemasukan WHERE event_id = ? ORDER BY tanggal DESC");
$pemasukan_stmt->bind_param("i", $event_id);
$pemasukan_stmt->execute();
$pemasukan_result = $pemasukan_stmt->get_result();

// Get pengeluaran for this event
$pengeluaran_stmt = $conn->prepare("SELECT * FROM pengeluaran WHERE event_id = ? ORDER BY tanggal DESC");
$pengeluaran_stmt->bind_param("i", $event_id);
$pengeluaran_stmt->execute();
$pengeluaran_result = $pengeluaran_stmt->get_result();

// Calculate totals
$total_pemasukan = 0;
$total_pengeluaran = 0;

// Reset result pointers and calculate totals
$pemasukan_result->data_seek(0);
while ($row = $pemasukan_result->fetch_assoc()) {
    $total_pemasukan += $row['jumlah'];
}

$pengeluaran_result->data_seek(0);
while ($row = $pengeluaran_result->fetch_assoc()) {
    $total_pengeluaran += $row['gaji_karyawan'] + $row['rental'] + $row['bensin'] + 
                         $row['peralatan'] + $row['konsumsi'] + $row['modal'] + 
                         $row['dll'] + $row['prive'];
}

// Reset result pointers for display
$pemasukan_result->data_seek(0);
$pengeluaran_result->data_seek(0);

$profit_loss = $total_pemasukan - $total_pengeluaran;

// Get related bookings
$booking_stmt = $conn->prepare("SELECT * FROM booking WHERE jenis_event LIKE ? OR catatan LIKE ? ORDER BY tanggal_booking DESC");
$search_term = '%' . $event['nama_event'] . '%';
$booking_stmt->bind_param("ss", $search_term, $search_term);
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result();
?>

<?php ob_start(); ?>

<div class="row">
  <div class="col-md-8 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="card-title">Detail Event</h4>
            <p class="card-description">Informasi lengkap event #<?php echo str_pad($event['id'], 4, '0', STR_PAD_LEFT); ?></p>
          </div>
          <div>
            <a href="events-edit.php?id=<?php echo $event['id']; ?>" class="btn btn-primary me-2">
              <i class="mdi mdi-pencil"></i> Edit
            </a>
            <a href="events.php" class="btn btn-outline-secondary">
              <i class="mdi mdi-arrow-left"></i> Kembali
            </a>
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-6">
            <div class="card bg-light mb-4">
              <div class="card-header">
                <h6 class="mb-0">Informasi Event</h6>
              </div>
              <div class="card-body">
                <div class="event-detail mb-3">
                  <label class="form-label text-muted">Nama Event</label>
                  <div class="fw-bold"><?php echo htmlspecialchars($event['nama_event']); ?></div>
                </div>
                
                <div class="event-detail mb-3">
                  <label class="form-label text-muted">Tanggal Event</label>
                  <div class="fw-bold">
                    <?php 
                    $event_date = new DateTime($event['tanggal']);
                    echo $event_date->format('l, d F Y'); 
                    ?>
                  </div>
                </div>
                
                <div class="event-detail mb-3">
                  <label class="form-label text-muted">Lokasi</label>
                  <div class="fw-bold"><?php echo htmlspecialchars($event['lokasi']) ?: '-'; ?></div>
                </div>
                
                <div class="event-detail">
                  <label class="form-label text-muted">Status</label>
                  <div>
                    <?php 
                    $is_upcoming = strtotime($event['tanggal']) >= strtotime(date('Y-m-d'));
                    ?>
                    <span class="badge badge-<?php echo $is_upcoming ? 'primary' : 'secondary'; ?> fs-6">
                      <?php echo $is_upcoming ? 'Mendatang' : 'Selesai'; ?>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="card bg-light mb-4">
              <div class="card-header">
                <h6 class="mb-0">Ringkasan Keuangan</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-6">
                    <div class="card bg-success text-white mb-2">
                      <div class="card-body p-2 text-center">
                        <h6 class="mb-1">Pemasukan</h6>
                        <small>Rp <?php echo number_format($total_pemasukan, 0, ',', '.'); ?></small>
                      </div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="card bg-danger text-white mb-2">
                      <div class="card-body p-2 text-center">
                        <h6 class="mb-1">Pengeluaran</h6>
                        <small>Rp <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?></small>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="card <?php echo $profit_loss >= 0 ? 'bg-info' : 'bg-warning'; ?> text-white">
                  <div class="card-body p-2 text-center">
                    <h6 class="mb-1">Profit/Loss</h6>
                    <strong><?php echo $profit_loss >= 0 ? '+' : ''; ?>Rp <?php echo number_format($profit_loss, 0, ',', '.'); ?></strong>
                  </div>
                </div>
                
                <div class="mt-3">
                  <div class="event-detail">
                    <label class="form-label text-muted">Margin</label>
                    <div class="fw-bold">
                      <?php 
                      $margin = $total_pemasukan > 0 ? ($profit_loss / $total_pemasukan) * 100 : 0;
                      echo number_format($margin, 1) . '%';
                      ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <?php if (!empty($event['deskripsi'])): ?>
          <div class="card bg-light mb-4">
            <div class="card-header">
              <h6 class="mb-0">Deskripsi</h6>
            </div>
            <div class="card-body">
              <p class="mb-0"><?php echo nl2br(htmlspecialchars($event['deskripsi'])); ?></p>
            </div>
          </div>
        <?php endif; ?>
        
        <!-- Pemasukan Section -->
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Transaksi Pemasukan</h6>
            <a href="pemasukan.php?event_id=<?php echo $event_id; ?>" class="btn btn-sm btn-success">
              <i class="mdi mdi-plus"></i> Tambah
            </a>
          </div>
          <div class="card-body">
            <?php if ($pemasukan_result && $pemasukan_result->num_rows > 0): ?>
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>Tanggal</th>
                      <th>Keterangan</th>
                      <th>Jumlah</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($pemasukan = $pemasukan_result->fetch_assoc()): ?>
                      <tr>
                        <td><?php echo date('d/m/Y', strtotime($pemasukan['tanggal'])); ?></td>
                        <td><?php echo htmlspecialchars($pemasukan['keterangan']); ?></td>
                        <td class="text-success">+Rp <?php echo number_format($pemasukan['jumlah'], 0, ',', '.'); ?></td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <p class="text-muted text-center">Belum ada transaksi pemasukan</p>
            <?php endif; ?>
          </div>
        </div>
        
        <!-- Pengeluaran Section -->
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Transaksi Pengeluaran</h6>
            <a href="pengeluaran.php?event_id=<?php echo $event_id; ?>" class="btn btn-sm btn-warning">
              <i class="mdi mdi-plus"></i> Tambah
            </a>
          </div>
          <div class="card-body">
            <?php if ($pengeluaran_result && $pengeluaran_result->num_rows > 0): ?>
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>Tanggal</th>
                      <th>Keterangan</th>
                      <th>Total</th>
                      <th>Detail</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($pengeluaran = $pengeluaran_result->fetch_assoc()): ?>
                      <?php 
                      $total_row = $pengeluaran['gaji_karyawan'] + $pengeluaran['rental'] + $pengeluaran['bensin'] + 
                                   $pengeluaran['peralatan'] + $pengeluaran['konsumsi'] + $pengeluaran['modal'] + 
                                   $pengeluaran['dll'] + $pengeluaran['prive'];
                      ?>
                      <tr>
                        <td><?php echo date('d/m/Y', strtotime($pengeluaran['tanggal'])); ?></td>
                        <td><?php echo htmlspecialchars($pengeluaran['keterangan']); ?></td>
                        <td class="text-danger">-Rp <?php echo number_format($total_row, 0, ',', '.'); ?></td>
                        <td>
                          <small class="text-muted">
                            <?php
                            $details = [];
                            if ($pengeluaran['gaji_karyawan'] > 0) $details[] = 'Gaji: ' . number_format($pengeluaran['gaji_karyawan'], 0, ',', '.');
                            if ($pengeluaran['rental'] > 0) $details[] = 'Rental: ' . number_format($pengeluaran['rental'], 0, ',', '.');
                            if ($pengeluaran['bensin'] > 0) $details[] = 'Bensin: ' . number_format($pengeluaran['bensin'], 0, ',', '.');
                            if ($pengeluaran['peralatan'] > 0) $details[] = 'Peralatan: ' . number_format($pengeluaran['peralatan'], 0, ',', '.');
                            if ($pengeluaran['konsumsi'] > 0) $details[] = 'Konsumsi: ' . number_format($pengeluaran['konsumsi'], 0, ',', '.');
                            if ($pengeluaran['modal'] > 0) $details[] = 'Modal: ' . number_format($pengeluaran['modal'], 0, ',', '.');
                            if ($pengeluaran['dll'] > 0) $details[] = 'Lainnya: ' . number_format($pengeluaran['dll'], 0, ',', '.');
                            if ($pengeluaran['prive'] > 0) $details[] = 'Prive: ' . number_format($pengeluaran['prive'], 0, ',', '.');
                            echo implode(', ', array_slice($details, 0, 2));
                            if (count($details) > 2) echo '...';
                            ?>
                          </small>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <p class="text-muted text-center">Belum ada transaksi pengeluaran</p>
            <?php endif; ?>
          </div>
        </div>
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
            <label class="form-label">Tanggal Dibuat</label>
            <div class="info-value"><?php echo date('d F Y, H:i', strtotime($event['created_at'])); ?></div>
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
        
        <div class="quick-actions">
          <h6>Aksi Cepat</h6>
          <div class="d-grid gap-2 mb-4">
            <a href="pemasukan.php?event_id=<?php echo $event_id; ?>" class="btn btn-outline-success btn-sm">
              <i class="mdi mdi-plus-circle"></i> Tambah Pemasukan
            </a>
            <a href="pengeluaran.php?event_id=<?php echo $event_id; ?>" class="btn btn-outline-warning btn-sm">
              <i class="mdi mdi-minus-circle"></i> Tambah Pengeluaran
            </a>
            <a href="cashflow.php?event_id=<?php echo $event_id; ?>" class="btn btn-outline-primary btn-sm">
              <i class="mdi mdi-chart-line"></i> Lihat Cashflow
            </a>
            <a href="events-edit.php?id=<?php echo $event_id; ?>" class="btn btn-outline-info btn-sm">
              <i class="mdi mdi-pencil"></i> Edit Event
            </a>
          </div>
        </div>
        
        <hr>
        
        <!-- Related Bookings -->
        <div class="related-bookings">
          <h6>Booking Terkait</h6>
          <?php if ($booking_result && $booking_result->num_rows > 0): ?>
            <?php while ($booking = $booking_result->fetch_assoc()): ?>
              <div class="small-booking-item">
                <div class="d-flex justify-content-between">
                  <span class="fw-bold"><?php echo htmlspecialchars($booking['nama_klien']); ?></span>
                  <span class="badge badge-<?php echo $booking['status'] == 'deal' ? 'success' : ($booking['status'] == 'dp' ? 'info' : ($booking['status'] == 'pending' ? 'warning' : 'danger')); ?>">
                    <?php echo strtoupper($booking['status']); ?>
                  </span>
                </div>
                <small class="text-muted">
                  <?php echo date('d/m/Y', strtotime($booking['tanggal_booking'])); ?> - 
                  <?php echo htmlspecialchars($booking['jenis_event']); ?>
                </small>
                <br>
                <small class="text-muted"><?php echo htmlspecialchars($booking['kontak_wa']); ?></small>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-muted small">Tidak ada booking terkait ditemukan</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.event-detail, .info-item {
    border-bottom: 1px solid #eee;
    padding-bottom: 0.5rem;
}

.event-detail:last-child, .info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.info-value {
    font-weight: 500;
    color: #333;
}

.quick-actions .btn {
    text-align: left;
}

.small-booking-item {
    padding: 0.5rem;
    border: 1px solid #eee;
    border-radius: 0.25rem;
    margin-bottom: 0.5rem;
    background-color: #f8f9fa;
}

.small-booking-item:last-child {
    margin-bottom: 0;
}

.fs-6 {
    font-size: 1rem !important;
}
</style>

<?php $content = ob_get_clean(); ?>
<?php include 'layout/main.php'; ?>