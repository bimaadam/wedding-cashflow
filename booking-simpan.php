<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

require_once 'config/koneksi.php';

// Set content type untuk JSON response
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validasi input
        $required_fields = ['nama_klien', 'tanggal_booking', 'jenis_event', 'kontak_wa'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Field {$field} harus diisi");
            }
        }
        
        $nama_klien = trim($_POST['nama_klien']);
        $tanggal_booking = $_POST['tanggal_booking'];
        $jenis_event = $_POST['jenis_event'];
        $kontak_wa = trim($_POST['kontak_wa']);
        $status = $_POST['status'] ?? 'pending';
        $catatan = trim($_POST['catatan'] ?? '');
        $id = isset($_POST['id']) ? intval($_POST['id']) : null;
        
        // Validasi format tanggal
        if (!DateTime::createFromFormat('Y-m-d', $tanggal_booking)) {
            throw new Exception("Format tanggal tidak valid");
        }
        
        // Validasi nomor WhatsApp
        if (!preg_match('/^08[0-9]{8,13}$/', $kontak_wa)) {
            throw new Exception("Format nomor WhatsApp tidak valid. Gunakan format 08xxxxxxxxxx");
        }
        
        // Validasi status
        $valid_status = ['pending', 'dp', 'deal', 'cancel'];
        if (!in_array($status, $valid_status)) {
            throw new Exception("Status tidak valid");
        }
        
        // Cek apakah ini update atau insert
        if ($id) {
            // Update booking
            $stmt = $conn->prepare("UPDATE booking SET nama_klien = ?, tanggal_booking = ?, jenis_event = ?, kontak_wa = ?, status = ?, catatan = ? WHERE id = ?");
            $stmt->bind_param("ssssssi", $nama_klien, $tanggal_booking, $jenis_event, $kontak_wa, $status, $catatan, $id);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Booking berhasil diperbarui!',
                    'action' => 'update',
                    'id' => $id
                ]);
            } else {
                throw new Exception("Gagal memperbarui booking: " . $conn->error);
            }
        } else {
            // Cek apakah sudah ada booking di tanggal yang sama dengan klien yang sama
            $check_stmt = $conn->prepare("SELECT id FROM booking WHERE nama_klien = ? AND tanggal_booking = ?");
            $check_stmt->bind_param("ss", $nama_klien, $tanggal_booking);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                throw new Exception("Booking untuk klien {$nama_klien} pada tanggal tersebut sudah ada");
            }
            
            // Insert booking baru
            $stmt = $conn->prepare("INSERT INTO booking (nama_klien, tanggal_booking, jenis_event, kontak_wa, status, catatan, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssssss", $nama_klien, $tanggal_booking, $jenis_event, $kontak_wa, $status, $catatan);
            
            if ($stmt->execute()) {
                $new_id = $conn->insert_id;
                echo json_encode([
                    'success' => true,
                    'message' => 'Booking berhasil ditambahkan!',
                    'action' => 'insert',
                    'id' => $new_id
                ]);
            } else {
                throw new Exception("Gagal menambahkan booking: " . $conn->error);
            }
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    // Jika bukan POST request, redirect ke jadwal booking
    header('Location: jadwal-booking.php');
    exit();
}

$conn->close();
?>