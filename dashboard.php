<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: auth/login.php');
  exit();
}

require_once 'config/koneksi.php';

$pageTitle = 'Dashboard - Graceful Decoration';

// Get summary statistics
$total_events_query = "SELECT COUNT(*) as total FROM events";
$total_events_result = $conn->query($total_events_query);
$total_events = $total_events_result->fetch_assoc()['total'] ?? 0;

$total_bookings_query = "SELECT COUNT(*) as total FROM booking";
$total_bookings_result = $conn->query($total_bookings_query);
$total_bookings = $total_bookings_result->fetch_assoc()['total'] ?? 0;

$total_pemasukan_query = "SELECT SUM(jumlah) as total FROM pemasukan";
$total_pemasukan_result = $conn->query($total_pemasukan_query);
$total_pemasukan = $total_pemasukan_result->fetch_assoc()['total'] ?? 0;

$total_pengeluaran_query = "SELECT SUM(gaji_karyawan + rental + bensin + peralatan + konsumsi + modal + dll + prive) as total FROM pengeluaran";
$total_pengeluaran_result = $conn->query($total_pengeluaran_query);
$total_pengeluaran = $total_pengeluaran_result->fetch_assoc()['total'] ?? 0;

$laba_rugi = $total_pemasukan - $total_pengeluaran;

// Get monthly data for chart
$monthly_data_query = "SELECT 
                        DATE_FORMAT(p.tanggal, '%Y-%m') as bulan,
                        SUM(p.jumlah) as pemasukan,
                        COALESCE(pengeluaran.total, 0) as pengeluaran
                      FROM pemasukan p
                      LEFT JOIN (
                        SELECT 
                          DATE_FORMAT(tanggal, '%Y-%m') as bulan,
                          SUM(gaji_karyawan + rental + bensin + peralatan + konsumsi + modal + dll + prive) as total
                        FROM pengeluaran
                        GROUP BY DATE_FORMAT(tanggal, '%Y-%m')
                      ) pengeluaran ON DATE_FORMAT(p.tanggal, '%Y-%m') = pengeluaran.bulan
                      WHERE p.tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                      GROUP BY DATE_FORMAT(p.tanggal, '%Y-%m')
                      ORDER BY bulan ASC";
$monthly_data_result = $conn->query($monthly_data_query);

$chart_labels = [];
$chart_pemasukan = [];
$chart_pengeluaran = [];

while ($row = $monthly_data_result->fetch_assoc()) {
    $chart_labels[] = date('M Y', strtotime($row['bulan'] . '-01'));
    $chart_pemasukan[] = $row['pemasukan'];
    $chart_pengeluaran[] = $row['pengeluaran'];
}

// Get recent bookings
$recent_bookings_query = "SELECT * FROM booking ORDER BY created_at DESC LIMIT 5";
$recent_bookings_result = $conn->query($recent_bookings_query);

// Get recent transactions
$recent_pemasukan_query = "SELECT p.*, e.nama_event FROM pemasukan p 
                           LEFT JOIN events e ON p.event_id = e.id 
                           ORDER BY p.created_at DESC LIMIT 3";
$recent_pemasukan_result = $conn->query($recent_pemasukan_query);

$recent_pengeluaran_query = "SELECT p.*, e.nama_event,
                             (p.gaji_karyawan + p.rental + p.bensin + p.peralatan + p.konsumsi + p.modal + p.dll + p.prive) as total
                             FROM pengeluaran p 
                             LEFT JOIN events e ON p.event_id = e.id 
                             ORDER BY p.created_at DESC LIMIT 3";
$recent_pengeluaran_result = $conn->query($recent_pengeluaran_query);

// Get booking status distribution
$booking_status_query = "SELECT 
                          status,
                          COUNT(*) as jumlah
                        FROM booking 
                        GROUP BY status";
$booking_status_result = $conn->query($booking_status_query);
$booking_status = [];
while ($row = $booking_status_result->fetch_assoc()) {
    $booking_status[$row['status']] = $row['jumlah'];
}
?>

<?php ob_start(); ?>

<div class="row">
  <div class="col-md-6 col-xl-3 grid-margin stretch-card">
    <div class="card bg-gradient-primary text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="font-weight-normal mb-3">Total Events</h4>
            <h2 class="mb-0"><?php echo $total_events; ?></h2>
          </div>
          <i class="mdi mdi-calendar-multiple icon-lg"></i>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-md-6 col-xl-3 grid-margin stretch-card">
    <div class="card bg-gradient-success text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="font-weight-normal mb-3">Total Bookings</h4>
            <h2 class="mb-0"><?php echo $total_bookings; ?></h2>
          </div>
          <i class="mdi mdi-bookmark-multiple icon-lg"></i>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-md-6 col-xl-3 grid-margin stretch-card">
    <div class="card bg-gradient-info text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="font-weight-normal mb-3">Total Pemasukan</h4>
            <h2 class="mb-0">Rp <?php echo number_format($total_pemasukan, 0, ',', '.'); ?></h2>
          </div>
          <i class="mdi mdi-cash-multiple icon-lg"></i>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-md-6 col-xl-3 grid-margin stretch-card">
    <div class="card bg-gradient-danger text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="font-weight-normal mb-3">Total Pengeluaran</h4>
            <h2 class="mb-0">Rp <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?></h2>
          </div>
          <i class="mdi mdi-cash-minus icon-lg"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-8 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Grafik Cash Flow (6 Bulan Terakhir)</h4>
        <canvas id="cashFlowChart"></canvas>
      </div>
    </div>
  </div>
  
  <div class="col-md-4 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Laba/Rugi</h4>
        <div class="text-center">
          <h1 class="<?php echo $laba_rugi >= 0 ? 'text-success' : 'text-danger'; ?>">
            Rp <?php echo number_format(abs($laba_rugi), 0, ',', '.'); ?>
          </h1>
          <p class="mb-0"><?php echo $laba_rugi >= 0 ? 'LABA' : 'RUGI'; ?></p>
        </div>
        
        <div class="mt-4">
          <h5>Status Booking</h5>
          <div class="progress-wrapper mb-3">
            <div class="d-flex justify-content-between">
              <span>Pending</span>
              <span><?php echo $booking_status['pending'] ?? 0; ?></span>
            </div>
            <div class="progress">
              <div class="progress-bar bg-warning" style="width: <?php echo $total_bookings > 0 ? (($booking_status['pending'] ?? 0) / $total_bookings) * 100 : 0; ?>%"></div>
            </div>
          </div>
          
          <div class="progress-wrapper mb-3">
            <div class="d-flex justify-content-between">
              <span>DP</span>
              <span><?php echo $booking_status['dp'] ?? 0; ?></span>
            </div>
            <div class="progress">
              <div class="progress-bar bg-info" style="width: <?php echo $total_bookings > 0 ? (($booking_status['dp'] ?? 0) / $total_bookings) * 100 : 0; ?>%"></div>
            </div>
          </div>
          
          <div class="progress-wrapper mb-3">
            <div class="d-flex justify-content-between">
              <span>Deal</span>
              <span><?php echo $booking_status['deal'] ?? 0; ?></span>
            </div>
            <div class="progress">
              <div class="progress-bar bg-success" style="width: <?php echo $total_bookings > 0 ? (($booking_status['deal'] ?? 0) / $total_bookings) * 100 : 0; ?>%"></div>
            </div>
          </div>
          
          <div class="progress-wrapper">
            <div class="d-flex justify-content-between">
              <span>Cancel</span>
              <span><?php echo $booking_status['cancel'] ?? 0; ?></span>
            </div>
            <div class="progress">
              <div class="progress-bar bg-danger" style="width: <?php echo $total_bookings > 0 ? (($booking_status['cancel'] ?? 0) / $total_bookings) * 100 : 0; ?>%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Booking Terbaru</h4>
        <div class="table-responsive">
          <table class="table table-borderless">
            <thead>
              <tr>
                <th>Klien</th>
                <th>Tanggal</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($recent_bookings_result && $recent_bookings_result->num_rows > 0): ?>
                <?php while ($booking = $recent_bookings_result->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo $booking['nama_klien']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($booking['tanggal_booking'])); ?></td>
                    <td>
                      <span class="badge badge-<?php echo $booking['status'] == 'deal' ? 'success' : ($booking['status'] == 'dp' ? 'info' : ($booking['status'] == 'pending' ? 'warning' : 'danger')); ?>">
                        <?php echo strtoupper($booking['status']); ?>
                      </span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="3" class="text-center">Belum ada booking</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-md-6 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Transaksi Terbaru</h4>
        
        <h6 class="text-success">Pemasukan</h6>
        <div class="list-group mb-3">
          <?php if ($recent_pemasukan_result && $recent_pemasukan_result->num_rows > 0): ?>
            <?php while ($pemasukan = $recent_pemasukan_result->fetch_assoc()): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <strong><?php echo $pemasukan['nama_event'] ?: 'Event tidak ditemukan'; ?></strong><br>
                  <small class="text-muted"><?php echo date('d/m/Y', strtotime($pemasukan['tanggal'])); ?></small>
                </div>
                <span class="text-success">+Rp <?php echo number_format($pemasukan['jumlah'], 0, ',', '.'); ?></span>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="list-group-item text-center">
              <small class="text-muted">Belum ada pemasukan</small>
            </div>
          <?php endif; ?>
        </div>
        
        <h6 class="text-danger">Pengeluaran</h6>
        <div class="list-group">
          <?php if ($recent_pengeluaran_result && $recent_pengeluaran_result->num_rows > 0): ?>
            <?php while ($pengeluaran = $recent_pengeluaran_result->fetch_assoc()): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <strong><?php echo $pengeluaran['nama_event'] ?: 'Event tidak ditemukan'; ?></strong><br>
                  <small class="text-muted"><?php echo date('d/m/Y', strtotime($pengeluaran['tanggal'])); ?></small>
                </div>
                <span class="text-danger">-Rp <?php echo number_format($pengeluaran['total'], 0, ',', '.'); ?></span>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="list-group-item text-center">
              <small class="text-muted">Belum ada pengeluaran</small>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Cash Flow Chart
const ctx = document.getElementById('cashFlowChart').getContext('2d');
const cashFlowChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [{
            label: 'Pemasukan',
            data: <?php echo json_encode($chart_pemasukan); ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Pengeluaran',
            data: <?php echo json_encode($chart_pengeluaran); ?>,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Cash Flow Trend'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value, index, values) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});
</script>

<?php $content = ob_get_clean(); ?>
<?php include 'layout/main.php'; ?>