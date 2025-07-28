<?php
// Database Setup Script
// File ini akan membuat database dan tabel yang diperlukan

$host = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'cashflow_gfl';

try {
    // Koneksi tanpa database untuk membuat database baru
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Buat database jika belum ada
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $db_name");
    echo "Database '$db_name' berhasil dibuat atau sudah ada.\n";
    
    // Gunakan database
    $pdo->exec("USE $db_name");
    
    // Buat tabel users
    $sql_users = "CREATE TABLE IF NOT EXISTS `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `username` varchar(100) NOT NULL,
        `password` varchar(100) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql_users);
    echo "Tabel 'users' berhasil dibuat.\n";
    
    // Insert default user
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $pdo->exec("INSERT INTO users (username, password) VALUES ('admin', 'admin')");
        echo "User default 'admin' berhasil dibuat.\n";
    }
    
    // Buat tabel events
    $sql_events = "CREATE TABLE IF NOT EXISTS `events` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `nama_event` varchar(150) NOT NULL,
        `tanggal` date NOT NULL,
        `lokasi` varchar(150) DEFAULT NULL,
        `deskripsi` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql_events);
    echo "Tabel 'events' berhasil dibuat.\n";
    
    // Buat tabel booking
    $sql_booking = "CREATE TABLE IF NOT EXISTS `booking` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `nama_klien` varchar(150) NOT NULL,
        `tanggal_booking` date NOT NULL,
        `jenis_event` varchar(100) DEFAULT NULL,
        `kontak_wa` varchar(20) DEFAULT NULL,
        `status` enum('pending','dp','deal','cancel') DEFAULT 'pending',
        `catatan` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql_booking);
    echo "Tabel 'booking' berhasil dibuat.\n";
    
    // Buat tabel pemasukan
    $sql_pemasukan = "CREATE TABLE IF NOT EXISTS `pemasukan` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `event_id` int(11) NOT NULL,
        `tanggal` date NOT NULL,
        `keterangan` varchar(150) DEFAULT NULL,
        `jumlah` decimal(12,2) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `event_id` (`event_id`),
        CONSTRAINT `pemasukan_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql_pemasukan);
    echo "Tabel 'pemasukan' berhasil dibuat.\n";
    
    // Buat tabel pengeluaran
    $sql_pengeluaran = "CREATE TABLE IF NOT EXISTS `pengeluaran` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `event_id` int(11) NOT NULL,
        `tanggal` date NOT NULL,
        `keterangan` varchar(150) DEFAULT NULL,
        `gaji_karyawan` decimal(12,2) DEFAULT 0.00,
        `rental` decimal(12,2) DEFAULT 0.00,
        `bensin` decimal(12,2) DEFAULT 0.00,
        `peralatan` decimal(12,2) DEFAULT 0.00,
        `konsumsi` decimal(12,2) DEFAULT 0.00,
        `modal` decimal(12,2) DEFAULT 0.00,
        `dll` decimal(12,2) DEFAULT 0.00,
        `prive` decimal(12,2) DEFAULT 0.00,
        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `event_id` (`event_id`),
        CONSTRAINT `pengeluaran_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql_pengeluaran);
    echo "Tabel 'pengeluaran' berhasil dibuat.\n";
    
    echo "\n=== SETUP BERHASIL ===\n";
    echo "Database dan semua tabel berhasil dibuat!\n";
    echo "Login dengan:\n";
    echo "Username: admin\n";
    echo "Password: admin\n";
    echo "\nSilakan akses sistem melalui browser.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Pastikan MySQL/MariaDB sudah berjalan dan konfigurasi database benar.\n";
}
?>