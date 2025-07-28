<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: auth/login.php');
  exit();
}

require_once 'config/koneksi.php';

$pageTitle = 'Laporan Cash Flow - Graceful Decoration';

// Get filter parameters
$filter_month = $_GET['month'] ?? date('Y-m');
$filter_event = $_GET['event_id'] ?? '';

// Get events for filter dropdown
$events_query = "SELECT id, nama_event, tanggal FROM events ORDER BY tanggal DESC";
$events_result = $conn->query($events_query);

// Build where clause for filters
$where_clause = "WHERE 1=1";
$params = [];
$param_types = "";

if ($filter_month) {
    $where_clause .= " AND DATE_FORMAT(p.tanggal, '%Y-%m') = ?";
    $params[] = $filter_month;
    $param_types .= "s";
}

if ($filter_event) {
    $where_clause .= " AND p.event_id = ?";
    $params[] = $filter_event;
    $param_types .= "i";
}

// Get cashflow summary
$summary_query = "SELECT 
                    COALESCE(SUM(pemasukan.total), 0) as total_pemasukan,
                    COALESCE(SUM(pengeluaran.total), 0) as total_pengeluaran,
                    COUNT(DISTINCT pemasukan.event_id) as events_pemasukan,
                    COUNT(DISTINCT pengeluaran.event_id) as events_pengeluaran
                  FROM (
                    SELECT event_id, SUM(jumlah) as total, tanggal FROM pemasukan $where_clause GROUP BY event_id
                  ) pemasukan
                  FULL OUTER JOIN (
                    SELECT event_id, SUM(gaji_karyawan + rental + bensin + peralatan + konsumsi + modal + dll + prive) as total, tanggal FROM pengeluaran $where_clause GROUP BY event_id
                  ) pengeluaran ON pemasukan.event_id = pengeluaran.event_id";

// For MySQL, use UNION instead of FULL OUTER JOIN
$summary_query = "SELECT 
                    COALESCE(SUM(p.jumlah), 0) as total_pemasukan,
                    COALESCE(SUM(pen.gaji_karyawan + pen.rental + pen.bensin + pen.peralatan + pen.konsumsi + pen.modal + pen.dll + pen.prive), 0) as total_pengeluaran
                  FROM pemasukan p
                  LEFT JOIN pengeluaran pen ON p.event_id = pen.event_id";

if ($filter_month) {
    $summary_query .= " WHERE DATE_FORMAT(p.tanggal, '%Y-%m') = '$filter_month'";
    if ($filter_event) {
        $summary_query .= " AND p.event_id = '$filter_event'";
    }
} elseif ($filter_event) {
    $summary_query .= " WHERE p.event_id = '$filter_event'";
}

$summary_result = $conn->query($summary_query);
$summary = $summary_result->fetch_assoc();

// Get detailed cashflow by event
$detail_query = "SELECT 
                    e.id,
                    e.nama_event,
                    e.tanggal as tanggal_event,
                    e.lokasi,
                    COALESCE(SUM(p.jumlah), 0) as total_pemasukan,
                    COALESCE(SUM(pen.gaji_karyawan + pen.rental + pen.bensin + pen.peralatan + pen.konsumsi + pen.modal + pen.dll + pen.prive), 0) as total_pengeluaran,
                    COUNT(DISTINCT p.id) as jumlah_pemasukan,
                    COUNT(DISTINCT pen.id) as jumlah_pengeluaran
                FROM events e
                LEFT JOIN pemasukan p ON e.id = p.event_id";

if ($filter_month) {
    $detail_query .= " AND DATE_FORMAT(p.tanggal, '%Y-%m') = '$filter_month'";
}

$detail_query .= " LEFT JOIN pengeluaran pen ON e.id = pen.event_id";

if ($filter_month) {
    $detail_query .= " AND DATE_FORMAT(pen.tanggal, '%Y-%m') = '$filter_month'";
}

$detail_query .= " WHERE 1=1";

if ($filter_event) {
    $detail_query .= " AND e.id = '$filter_event'";
}

$detail_query .= " GROUP BY e.id, e.nama_event, e.tanggal, e.lokasi
                   HAVING total_pemasukan > 0 OR total_pengeluaran > 0
                   ORDER BY e.tanggal DESC";

$detail_result = $conn->query($detail_query);

// Get monthly trend
$trend_query = "SELECT 
                  DATE_FORMAT(tanggal, '%Y-%m') as bulan,
                  'Pemasukan' as tipe,
                  SUM(jumlah) as total
                FROM pemasukan 
                WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(tanggal, '%Y-%m')
                UNION ALL
                SELECT 
                  DATE_FORMAT(tanggal, '%Y-%m') as bulan,
                  'Pengeluaran' as tipe,
                  SUM(gaji_karyawan + rental + bensin + peralatan + konsumsi + modal + dll + prive) as total
                FROM pengeluaran 
                WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(tanggal, '%Y-%m')
                ORDER BY bulan ASC";

$trend_result = $conn->query($trend_query);

$trend_data = [];
while ($row = $trend_result->fetch_assoc()) {
    $trend_data[$row['bulan']][$row['tipe']] = $row['total'];
}

// Get top performing events
$top_events_query = "SELECT 
                      e.nama_event,
                      e.tanggal,
                      COALESCE(SUM(p.jumlah), 0) as pemasukan,
                      COALESCE(SUM(pen.gaji_karyawan + pen.rental + pen.bensin + pen.peralatan + pen.konsumsi + pen.modal + pen.dll + pen.prive), 0) as pengeluaran,
                      (COALESCE(SUM(p.jumlah), 0) - COALESCE(SUM(pen.gaji_karyawan + pen.rental + pen.bensin + pen.peralatan + pen.konsumsi + pen.modal + pen.dll + pen.prive), 0)) as profit
                    FROM events e
                    LEFT JOIN pemasukan p ON e.id = p.event_id
                    LEFT JOIN pengeluaran pen ON e.id = pen.event_id
                    GROUP BY e.id, e.nama_event, e.tanggal
                    HAVING pemasukan > 0 OR pengeluaran > 0
                    ORDER BY profit DESC
                    LIMIT 10";

$top_events_result = $conn->query($top_events_query);

$profit_loss = $summary['total_pemasukan'] - $summary['total_pengeluaran'];
?>

<?php ob_start(); ?>

<div class="row">
  <div class="col-md-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Filter Laporan</h4>
        <form method="GET" class="row g-3">
          <div class="col-md-4">
            <label for="month" class="form-label">Bulan</label>
            <input type="month" class="form-control" id="month" name="month" value="<?php echo $filter_month; ?>">
          </div>
          <div class="col-md-4">
            <label for="event_id" class="form-label">Event</label>
            <select class="form-control" id="event_id" name="event_id">
              <option value="">-- Semua Event --</option>
              <?php if ($events_result && $events_result->num_rows > 0): ?>
                <?php while ($event = $events_result->fetch_assoc()): ?>
                  <option value="<?php echo $event['id']; ?>" <?php echo $filter_event == $event['id'] ? 'selected' : ''; ?>>
                    <?php echo $event['nama_event'] . ' (' . date('d/m/Y', strtotime($event['tanggal'])) . ')'; ?>
                  </option>
                <?php endwhile; ?>
              <?php endif; ?>
            </select>
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary me-2">Filter</button>
            <a href="laporan-cashflow.php" class="btn btn-secondary">Reset</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card bg-gradient-success text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="font-weight-normal mb-3">Total Pemasukan</h4>
            <h2 class="mb-0">Rp <?php echo number_format($summary['total_pemasukan'], 0, ',', '.'); ?></h2>
          </div>
          <i class="mdi mdi-cash-plus icon-lg"></i>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card bg-gradient-danger text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="font-weight-normal mb-3">Total Pengeluaran</h4>
            <h2 class="mb-0">Rp <?php echo number_format($summary['total_pengeluaran'], 0, ',', '.'); ?></h2>
          </div>
          <i class="mdi mdi-cash-minus icon-lg"></i>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card bg-gradient-<?php echo $profit_loss >= 0 ? 'info' : 'warning'; ?> text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="font-weight-normal mb-3"><?php echo $profit_loss >= 0 ? 'Profit' : 'Loss'; ?></h4>
            <h2 class="mb-0">Rp <?php echo number_format(abs($profit_loss), 0, ',', '.'); ?></h2>
          </div>
          <i class="mdi mdi-chart-line icon-lg"></i>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-md-3 grid-margin stretch-card">
    <div class="card bg-gradient-primary text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="font-weight-normal mb-3">Margin</h4>
            <h2 class="mb-0">
              <?php 
                $margin = $summary['total_pemasukan'] > 0 ? ($profit_loss / $summary['total_pemasukan']) * 100 : 0;
                echo number_format($margin, 1) . '%';
              ?>
            </h2>
          </div>
          <i class="mdi mdi-percent icon-lg"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-8 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Tren Cashflow (12 Bulan Terakhir)</h4>
        <canvas id="trendChart"></canvas>
      </div>
    </div>
  </div>
  
  <div class="col-md-4 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Top 5 Event Terbaik</h4>
        <div class="list-group">
          <?php if ($top_events_result && $top_events_result->num_rows > 0): ?>
            <?php $rank = 1; ?>
            <?php while ($event = $top_events_result->fetch_assoc()): ?>
              <?php if ($rank <= 5): ?>
                <div class="list-group-item">
                  <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">#<?php echo $rank; ?> <?php echo $event['nama_event']; ?></h6>
                    <small class="<?php echo $event['profit'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                      <?php echo $event['profit'] >= 0 ? '+' : ''; ?>Rp <?php echo number_format($event['profit'], 0, ',', '.'); ?>
                    </small>
                  </div>
                  <p class="mb-1">
                    <small class="text-success">Pemasukan: Rp <?php echo number_format($event['pemasukan'], 0, ',', '.'); ?></small><br>
                    <small class="text-danger">Pengeluaran: Rp <?php echo number_format($event['pengeluaran'], 0, ',', '.'); ?></small>
                  </p>
                  <small><?php echo date('d/m/Y', strtotime($event['tanggal'])); ?></small>
                </div>
                <?php $rank++; ?>
              <?php endif; ?>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="list-group-item text-center">
              <p class="mb-0 text-muted">Belum ada data event</p>
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
        <h4 class="card-title">Detail Cashflow per Event</h4>
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
                <th>Margin</th>
                <th>Transaksi</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($detail_result && $detail_result->num_rows > 0): ?>
                <?php while ($event = $detail_result->fetch_assoc()): ?>
                  <?php 
                    $event_profit = $event['total_pemasukan'] - $event['total_pengeluaran'];
                    $event_margin = $event['total_pemasukan'] > 0 ? ($event_profit / $event['total_pemasukan']) * 100 : 0;
                  ?>
                  <tr>
                    <td><?php echo date('d/m/Y', strtotime($event['tanggal_event'])); ?></td>
                    <td>
                      <strong><?php echo $event['nama_event']; ?></strong>
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
                      <span class="<?php echo $event_profit >= 0 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo $event_profit >= 0 ? '+' : ''; ?>Rp <?php echo number_format($event_profit, 0, ',', '.'); ?>
                      </span>
                    </td>
                    <td>
                      <span class="<?php echo $event_margin >= 0 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo number_format($event_margin, 1); ?>%
                      </span>
                    </td>
                    <td>
                      <small>
                        <?php echo $event['jumlah_pemasukan']; ?> in<br>
                        <?php echo $event['jumlah_pengeluaran']; ?> out
                      </small>
                    </td>
                    <td>
                      <button class="btn btn-outline-info btn-sm btn-detail" data-id="<?php echo $event['id']; ?>">Detail</button>
                      <button class="btn btn-outline-primary btn-sm btn-export" data-id="<?php echo $event['id']; ?>">Export</button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="9" class="text-center">Tidak ada data untuk filter yang dipilih</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Trend Chart
const ctx = document.getElementById('trendChart').getContext('2d');

const trendData = <?php echo json_encode($trend_data); ?>;
const months = Object.keys(trendData).sort();
const pemasukanData = months.map(month => trendData[month]['Pemasukan'] || 0);
const pengeluaranData = months.map(month => trendData[month]['Pengeluaran'] || 0);
const labels = months.map(month => {
    const date = new Date(month + '-01');
    return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'short' });
});

const trendChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Pemasukan',
            data: pemasukanData,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1,
            fill: false
        }, {
            label: 'Pengeluaran',
            data: pengeluaranData,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1,
            fill: false
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Tren Cashflow Bulanan'
            },
            legend: {
                display: true,
                position: 'top'
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
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    }
});

// Handle Detail and Export buttons
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-detail')) {
        e.preventDefault();
        const id = e.target.getAttribute('data-id');
        if (confirm('Fitur detail akan segera tersedia. Untuk sementara, lihat data di tabel.')) {
            // window.location.href = 'detail-cashflow.php?id=' + id;
        }
    }
    
    if (e.target.classList.contains('btn-export')) {
        e.preventDefault();
        const id = e.target.getAttribute('data-id');
        if (confirm('Fitur export akan segera tersedia. Untuk sementara, gunakan print browser.')) {
            // window.location.href = 'export-cashflow.php?id=' + id;
        }
    }
});
</script>

<?php $content = ob_get_clean(); ?>
<?php include 'layout/main.php'; ?>