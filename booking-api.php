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
    case 'get_booking':
        getBooking();
        break;
    case 'get_bookings':
        getBookings();
        break;
    case 'get_stats':
        getStats();
        break;
    case 'check_availability':
        checkAvailability();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getBooking() {
    global $conn;
    
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
        return;
    }
    
    $stmt = $conn->prepare("SELECT * FROM booking WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        return;
    }
    
    $booking = $result->fetch_assoc();
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'data' => $booking
    ]);
}

function getBookings() {
    global $conn;
    
    $limit = intval($_GET['limit'] ?? 10);
    $offset = intval($_GET['offset'] ?? 0);
    $status = $_GET['status'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    
    $where_conditions = [];
    $params = [];
    $types = '';
    
    if (!empty($status)) {
        $where_conditions[] = "status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    if (!empty($date_from)) {
        $where_conditions[] = "tanggal_booking >= ?";
        $params[] = $date_from;
        $types .= 's';
    }
    
    if (!empty($date_to)) {
        $where_conditions[] = "tanggal_booking <= ?";
        $params[] = $date_to;
        $types .= 's';
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM booking $where_clause";
    $count_stmt = $conn->prepare($count_query);
    if (!empty($params)) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'];
    $count_stmt->close();
    
    // Get bookings
    $query = "SELECT * FROM booking $where_clause ORDER BY tanggal_booking DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'data' => $bookings,
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset
    ]);
}

function getStats() {
    global $conn;
    
    $stats_query = "SELECT 
                      COUNT(*) as total_bookings,
                      COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_bookings,
                      COUNT(CASE WHEN status = 'dp' THEN 1 END) as dp_bookings,
                      COUNT(CASE WHEN status = 'deal' THEN 1 END) as deal_bookings,
                      COUNT(CASE WHEN status = 'cancel' THEN 1 END) as cancel_bookings,
                      COUNT(CASE WHEN tanggal_booking >= CURDATE() THEN 1 END) as upcoming_bookings,
                      COUNT(CASE WHEN tanggal_booking = CURDATE() THEN 1 END) as today_bookings,
                      COUNT(CASE WHEN tanggal_booking BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as this_week_bookings,
                      COUNT(CASE WHEN MONTH(tanggal_booking) = MONTH(CURDATE()) AND YEAR(tanggal_booking) = YEAR(CURDATE()) THEN 1 END) as this_month_bookings
                    FROM booking";
    
    $result = $conn->query($stats_query);
    $stats = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
}

function checkAvailability() {
    global $conn;
    
    $date = $_GET['date'] ?? '';
    $exclude_id = intval($_GET['exclude_id'] ?? 0);
    
    if (empty($date)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Date is required']);
        return;
    }
    
    $query = "SELECT COUNT(*) as count FROM booking WHERE tanggal_booking = ?";
    $params = [$date];
    $types = 's';
    
    if ($exclude_id > 0) {
        $query .= " AND id != ?";
        $params[] = $exclude_id;
        $types .= 'i';
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    
    // Get bookings for that date
    $bookings_query = "SELECT nama_klien, jenis_event, status FROM booking WHERE tanggal_booking = ?";
    if ($exclude_id > 0) {
        $bookings_query .= " AND id != ?";
    }
    
    $bookings_stmt = $conn->prepare($bookings_query);
    $bookings_stmt->bind_param($types, ...$params);
    $bookings_stmt->execute();
    $bookings_result = $bookings_stmt->get_result();
    
    $existing_bookings = [];
    while ($booking = $bookings_result->fetch_assoc()) {
        $existing_bookings[] = $booking;
    }
    $bookings_stmt->close();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'available' => $count == 0,
            'count' => $count,
            'existing_bookings' => $existing_bookings,
            'message' => $count == 0 ? 'Tanggal tersedia' : "Sudah ada {$count} booking pada tanggal ini"
        ]
    ]);
}

$conn->close();
?>