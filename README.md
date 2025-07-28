# Wedding Cashflow Management System

Sistem manajemen cashflow untuk bisnis wedding decoration "Graceful Decoration" yang membantu mengelola pemasukan, pengeluaran, booking, dan laporan keuangan.

## Fitur Utama

### 1. Dashboard
- Overview statistik keuangan
- Grafik tren cashflow 6 bulan terakhir
- Statistik booking berdasarkan status
- Transaksi terbaru (pemasukan & pengeluaran)

### 2. Booking Management
- Form booking event baru
- Tracking status booking (Pending, DP, Deal, Cancel)
- Kalender booking
- Statistik booking

### 3. Events Management
- Manajemen event/acara
- Tracking cashflow per event
- Analisis profit/loss per event

### 4. Pemasukan (Income)
- Input pemasukan per event
- Statistik pemasukan
- Riwayat transaksi pemasukan

### 5. Pengeluaran (Expenses)
- Input pengeluaran dengan kategori:
  - Gaji Karyawan
  - Rental
  - Bensin
  - Peralatan
  - Konsumsi
  - Modal
  - DLL (Lainnya)
  - Prive
- Statistik pengeluaran per kategori
- Riwayat transaksi pengeluaran

### 6. Laporan Cashflow
- Filter laporan berdasarkan bulan dan event
- Analisis profit/loss
- Grafik tren bulanan
- Top performing events
- Export laporan

## Teknologi yang Digunakan

- **Backend**: PHP 8.x
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 5
- **Charts**: Chart.js
- **Icons**: Material Design Icons

## Instalasi

### 1. Persiapan Database
```sql
-- Import file database/cashflow_gfl.sql ke MySQL
-- Atau jalankan query berikut:

CREATE DATABASE cashflow_gfl;
USE cashflow_gfl;

-- Kemudian import struktur tabel dari file SQL
```

### 2. Konfigurasi Database
Edit file `config/koneksi.php`:
```php
<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'cashflow_gfl';
?>
```

### 3. Setup Web Server
- Pastikan Apache dan MySQL/MariaDB sudah berjalan
- Copy folder project ke htdocs (untuk XAMPP) atau www (untuk WAMP)
- Akses melalui browser: `http://localhost/wedding-cashflow`

## Login Default
- **Username**: admin
- **Password**: admin

## Struktur Database

### Tabel `users`
- `id` (Primary Key)
- `username`
- `password`
- `created_at`

### Tabel `events`
- `id` (Primary Key)
- `nama_event`
- `tanggal`
- `lokasi`
- `deskripsi`
- `created_at`

### Tabel `booking`
- `id` (Primary Key)
- `nama_klien`
- `tanggal_booking`
- `jenis_event`
- `kontak_wa`
- `status` (pending, dp, deal, cancel)
- `catatan`
- `created_at`

### Tabel `pemasukan`
- `id` (Primary Key)
- `event_id` (Foreign Key ke events)
- `tanggal`
- `keterangan`
- `jumlah`
- `created_at`

### Tabel `pengeluaran`
- `id` (Primary Key)
- `event_id` (Foreign Key ke events)
- `tanggal`
- `keterangan`
- `gaji_karyawan`
- `rental`
- `bensin`
- `peralatan`
- `konsumsi`
- `modal`
- `dll`
- `prive`
- `created_at`

## Struktur File Booking

Sistem booking telah dipisahkan menjadi beberapa file untuk struktur yang lebih modular:

### File Utama
- `jadwal-booking.php` - Halaman utama booking dengan form dan daftar
- `booking-simpan.php` - Handler untuk create/update booking
- `booking-edit.php` - Form edit booking
- `booking-hapus.php` - Handler dan konfirmasi delete booking
- `booking-detail.php` - Detail lengkap booking dan transaksi terkait
- `booking-api.php` - API endpoints untuk operasi booking

### Fitur File Booking
- **Modular Structure**: Setiap operasi dalam file terpisah
- **AJAX Support**: Form submission dengan AJAX dan feedback real-time
- **Input Validation**: Validasi client-side dan server-side
- **Security**: Prepared statements dan session validation
- **User Experience**: Loading states, confirmations, dan error handling
- **API Ready**: RESTful API endpoints untuk integrasi

Lihat dokumentasi lengkap di `docs/booking-system.md`

## Struktur File Events

Sistem events juga telah dipisahkan menjadi beberapa file untuk struktur yang lebih modular:

### File Utama
- `events.php` - Halaman utama events dengan form dan daftar
- `events-simpan.php` - Handler untuk create/update events
- `events-edit.php` - Form edit events dengan cashflow summary
- `events-hapus.php` - Handler dan konfirmasi delete events (cascade delete)
- `events-detail.php` - Detail lengkap events dengan cashflow dan booking terkait
- `events-api.php` - API endpoints untuk operasi events

### Fitur File Events
- **Cashflow Integration**: Otomatis menghitung profit/loss per event
- **Cascade Delete**: Menghapus event beserta semua transaksi terkait
- **Related Data**: Menampilkan booking dan transaksi terkait
- **Margin Analysis**: Kalkulasi margin keuntungan
- **AJAX Support**: Form submission dengan AJAX dan feedback real-time
- **API Ready**: RESTful API endpoints dengan cashflow data

Lihat dokumentasi lengkap di `docs/events-system.md`

## Navigasi Menu

1. **Dashboard** - Halaman utama dengan overview
2. **Pengeluaran Kas** - Manajemen pengeluaran
3. **Pemasukan Kas** - Manajemen pemasukan
4. **Jadwal Booking** - Manajemen booking event
5. **Laporan Cash Flow** - Laporan dan analisis keuangan

## Fitur Keamanan

- Session management untuk autentikasi
- Prepared statements untuk mencegah SQL injection
- Input validation dan sanitization
- Redirect otomatis jika belum login

## Responsive Design

Sistem ini menggunakan Bootstrap 5 yang membuatnya responsive dan dapat diakses dari berbagai perangkat:
- Desktop
- Tablet
- Mobile

## Pengembangan Lebih Lanjut

Fitur yang dapat ditambahkan:
- Export laporan ke PDF/Excel
- Sistem notifikasi
- Multi-user dengan role management
- Backup database otomatis
- Integration dengan WhatsApp API
- Sistem pembayaran online
- Inventory management
- Customer relationship management (CRM)

## Troubleshooting

### Error Database Connection
- Pastikan MySQL/MariaDB sudah berjalan
- Cek konfigurasi di `config/koneksi.php`
- Pastikan database `cashflow_gfl` sudah dibuat

### Error Session
- Pastikan session sudah distart
- Cek permission folder untuk session storage

### Error 404
- Pastikan semua file ada di lokasi yang benar
- Cek .htaccess jika menggunakan URL rewriting

## Kontributor

Sistem ini dikembangkan untuk membantu bisnis wedding decoration dalam mengelola keuangan dengan lebih efisien dan terstruktur.

## Lisensi

MIT License - Bebas digunakan untuk keperluan komersial dan non-komersial.