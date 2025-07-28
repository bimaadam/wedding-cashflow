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
        $required_fields = ['nama_event', 'tanggal'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Field {$field} harus diisi");
            }
        }
        
        $nama_event = trim($_POST['nama_event']);
        $tanggal = $_POST['tanggal'];
        $lokasi = trim($_POST['lokasi'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $id = isset($_POST['id']) ? intval($_POST['id']) : null;
        
        // Validasi format tanggal
        if (!DateTime::createFromFormat('Y-m-d', $tanggal)) {
            throw new Exception("Format tanggal tidak valid");
        }
        
        // Validasi nama event tidak boleh kosong
        if (strlen($nama_event) < 3) {
            throw new Exception("Nama event minimal 3 karakter");
        }
        
        // Cek apakah ini update atau insert
        if ($id) {
            // Update event
            $stmt = $conn->prepare("UPDATE events SET nama_event = ?, tanggal = ?, lokasi = ?, deskripsi = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $nama_event, $tanggal, $lokasi, $deskripsi, $id);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Event berhasil diperbarui!',
                    'action' => 'update',
                    'id' => $id
                ]);
            } else {
                throw new Exception("Gagal memperbarui event: " . $conn->error);
            }
        } else {
            // Cek apakah sudah ada event dengan nama yang sama pada tanggal yang sama
            $check_stmt = $conn->prepare("SELECT id FROM events WHERE nama_event = ? AND tanggal = ?");
            $check_stmt->bind_param("ss", $nama_event, $tanggal);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                throw new Exception("Event dengan nama '{$nama_event}' pada tanggal tersebut sudah ada");
            }
            
            // Insert event baru
            $stmt = $conn->prepare("INSERT INTO events (nama_event, tanggal, lokasi, deskripsi, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $nama_event, $tanggal, $lokasi, $deskripsi);
            
            if ($stmt->execute()) {
                $new_id = $conn->insert_id;
                echo json_encode([
                    'success' => true,
                    'message' => 'Event berhasil ditambahkan!',
                    'action' => 'insert',
                    'id' => $new_id
                ]);
            } else {
                throw new Exception("Gagal menambahkan event: " . $conn->error);
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
    // Jika bukan POST request, redirect ke events
    header('Location: events.php');
    exit();
}

$conn->close();
?>