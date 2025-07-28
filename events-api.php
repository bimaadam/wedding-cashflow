<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once 'config/koneksi.php';

// Set content type untuk JSON response
header('Content-Type: application/json');

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_event':
        getEvent();
        break;
    case 'get_events':
        getEvents();
        break;
    case 'get_stats':
        getStats();
        break;
    case 'get_cashflow':
        getCashflow();
        break;
    case 'get_upcoming':
        getUpcomingEvents();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getEvent() {
    global $conn;
    
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid event ID']);
        return;
    }
    
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Event not found']);
        return;
    }
    
    $event = $result->fetch_assoc();
    $stmt->close();
    
    // Get cashflow data
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
    $cashflow_stmt->bind_param("i", $id);
    $cashflow_stmt->execute();
    $cashflow_result = $cashflow_stmt->get_result();
    $cashflow = $cashflow_result->fetch_assoc();
    $cashflow_stmt->close();
    
    $event['cashflow'] = $cashflow;
    
    echo json_encode([
        'success' => true,
        'data' => $event
    ]);
}

function getEvents() {
    global $conn;
    
    $limit = intval($_GET['limit'] ?? 10);
    $offset = intval($_GET['offset'] ?? 0);
    $status = $_GET['status'] ?? ''; // upcoming, past, all
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $include_cashflow = $_GET['include_cashflow'] ?? 'false';
    
    $where_conditions = [];
    $params = [];
    $types = '';
    
    if ($status === 'upcoming') {
        $where_conditions[] = "tanggal >= CURDATE()";
    } elseif ($status === 'past') {
        $where_conditions[] = "tanggal < CURDATE()";
    }
    
    if (!empty($date_from)) {
        $where_conditions[] = "tanggal >= ?";
        $params[] = $date_from;
        $types .= 's';
    }
    
    if (!empty($date_to)) {
        $where_conditions[] = "tanggal <= ?";
        $params[] = $date_to;
        $types .= 's';
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    if ($include_cashflow === 'true') {
        // Get events with cashflow data
        $query = "SELECT 
                    e.*,
                    COALESCE(SUM(p.jumlah), 0) as total_pemasukan,
                    COALESCE(SUM(pen.gaji_karyawan + pen.rental + pen.bensin + pen.peralatan + pen.konsumsi + pen.modal + pen.dll + pen.prive), 0) as total_pengeluaran
                  FROM events e
                  LEFT JOIN pemasukan p ON e.id = p.event_id
                  LEFT JOIN pengeluaran pen ON e.id = pen.event_id
                  $where_clause
                  GROUP BY e.id
                  ORDER BY e.tanggal DESC 
                  LIMIT ? OFFSET ?";
    } else {
        // Get events only
        $query = "SELECT * FROM events $where_clause ORDER BY tanggal DESC LIMIT ? OFFSET ?";
    }
    
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM events $where_clause";
    $count_stmt = $conn->prepare($count_query);
    if (!empty($params)) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'];
    $count_stmt->close();
    
    // Get events
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        if ($include_cashflow === 'true') {
            $row['profit_loss'] = $row['total_pemasukan'] - $row['total_pengeluaran'];
        }
        $events[] = $row;
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'data' => $events,
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset
    ]);
}

function getStats() {
    global $conn;
    
    $stats_query = "SELECT 
                      COUNT(*) as total_events,
                      COUNT(CASE WHEN tanggal >= CURDATE() THEN 1 END) as upcoming_events,
                      COUNT(CASE WHEN tanggal < CURDATE() THEN 1 END) as past_events,
                      COUNT(CASE WHEN tanggal = CURDATE() THEN 1 END) as today_events,
                      COUNT(CASE WHEN tanggal BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as this_week_events,
                      COUNT(CASE WHEN MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE()) THEN 1 END) as this_month_events
                    FROM events";
    
    $result = $conn->query($stats_query);
    $stats = $result->fetch_assoc();
    
    // Get cashflow stats
    $cashflow_query = "SELECT 
                        COALESCE(SUM(p.jumlah), 0) as total_pemasukan,
                        COALESCE(SUM(pen.gaji_karyawan + pen.rental + pen.bensin + pen.peralatan + pen.konsumsi + pen.modal + pen.dll + pen.prive), 0) as total_pengeluaran
                       FROM events e
                       LEFT JOIN pemasukan p ON e.id = p.event_id
                       LEFT JOIN pengeluaran pen ON e.id = pen.event_id";
    
    $cashflow_result = $conn->query($cashflow_query);
    $cashflow_stats = $cashflow_result->fetch_assoc();
    
    $stats['total_pemasukan'] = $cashflow_stats['total_pemasukan'];
    $stats['total_pengeluaran'] = $cashflow_stats['total_pengeluaran'];
    $stats['total_profit_loss'] = $cashflow_stats['total_pemasukan'] - $cashflow_stats['total_pengeluaran'];
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
}

function getCashflow() {
    global $conn;
    
    $event_id = intval($_GET['event_id'] ?? 0);
    
    if ($event_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid event ID']);
        return;
    }
    
    // Get pemasukan
    $pemasukan_stmt = $conn->prepare("SELECT * FROM pemasukan WHERE event_id = ? ORDER BY tanggal DESC");
    $pemasukan_stmt->bind_param("i", $event_id);
    $pemasukan_stmt->execute();
    $pemasukan_result = $pemasukan_stmt->get_result();
    
    $pemasukan = [];
    while ($row = $pemasukan_result->fetch_assoc()) {
        $pemasukan[] = $row;
    }
    $pemasukan_stmt->close();
    
    // Get pengeluaran
    $pengeluaran_stmt = $conn->prepare("SELECT * FROM pengeluaran WHERE event_id = ? ORDER BY tanggal DESC");
    $pengeluaran_stmt->bind_param("i", $event_id);
    $pengeluaran_stmt->execute();
    $pengeluaran_result = $pengeluaran_stmt->get_result();
    
    $pengeluaran = [];
    while ($row = $pengeluaran_result->fetch_assoc()) {
        $row['total'] = $row['gaji_karyawan'] + $row['rental'] + $row['bensin'] + 
                       $row['peralatan'] + $row['konsumsi'] + $row['modal'] + 
                       $row['dll'] + $row['prive'];
        $pengeluaran[] = $row;
    }
    $pengeluaran_stmt->close();
    
    // Calculate totals
    $total_pemasukan = array_sum(array_column($pemasukan, 'jumlah'));
    $total_pengeluaran = array_sum(array_column($pengeluaran, 'total'));
    
    echo json_encode([
        'success' => true,
        'data' => [
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'summary' => [
                'total_pemasukan' => $total_pemasukan,
                'total_pengeluaran' => $total_pengeluaran,
                'profit_loss' => $total_pemasukan - $total_pengeluaran
            ]
        ]
    ]);
}

function getUpcomingEvents() {
    global $conn;
    
    $limit = intval($_GET['limit'] ?? 5);
    $days = intval($_GET['days'] ?? 30);
    
    $query = "SELECT * FROM events 
              WHERE tanggal BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY) 
              ORDER BY tanggal ASC 
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $days, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'data' => $events
    ]);
}

$conn->close();
?>