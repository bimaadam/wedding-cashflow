# Dokumentasi Sistem Booking Terpisah

## Overview
Sistem booking telah dipisahkan menjadi beberapa file untuk struktur yang lebih modular dan maintainable. Setiap file memiliki tanggung jawab yang spesifik.

## File Structure

### 1. booking-simpan.php
**Fungsi**: Menangani operasi create dan update booking
**Method**: POST
**Response**: JSON

**Parameter Input**:
- `nama_klien` (required): Nama lengkap klien
- `tanggal_booking` (required): Tanggal event (format: Y-m-d)
- `jenis_event` (required): Jenis event (Pernikahan, Ulang Tahun, dll)
- `kontak_wa` (required): Nomor WhatsApp (format: 08xxxxxxxxxx)
- `status` (optional): Status booking (pending, dp, deal, cancel)
- `catatan` (optional): Catatan tambahan
- `id` (optional): ID booking untuk update

**Fitur**:
- Validasi input lengkap
- Validasi format nomor WhatsApp
- Validasi format tanggal
- Cek duplikasi booking
- Response JSON dengan status dan pesan

**Contoh Response**:
```json
{
  "success": true,
  "message": "Booking berhasil ditambahkan!",
  "action": "insert",
  "id": 123
}
```

### 2. booking-edit.php
**Fungsi**: Menampilkan form edit booking
**Method**: GET, POST
**Parameter**: `id` (booking ID)

**Fitur**:
- Form edit dengan data pre-filled
- Validasi client-side dan server-side
- Quick status update buttons
- Informasi booking di sidebar
- Countdown ke tanggal event
- Link ke delete booking

### 3. booking-hapus.php
**Fungsi**: Menangani penghapusan booking
**Method**: GET (konfirmasi), POST (delete)
**Parameter**: `id` (booking ID)

**Fitur**:
- Halaman konfirmasi dengan detail booking
- Validasi keberadaan booking
- Response JSON untuk AJAX request
- Modal loading dan feedback
- Safety checks sebelum delete

### 4. booking-detail.php
**Fungsi**: Menampilkan detail lengkap booking
**Method**: GET
**Parameter**: `id` (booking ID)

**Fitur**:
- Informasi lengkap booking
- Quick status update
- Link ke WhatsApp
- Transaksi terkait (pemasukan/pengeluaran)
- Countdown ke event
- Aksi cepat

### 5. booking-api.php
**Fungsi**: API endpoints untuk operasi booking
**Method**: GET, POST
**Parameter**: `action` (get_booking, get_bookings, get_stats, check_availability)

**Endpoints**:

#### GET /booking-api.php?action=get_booking&id={id}
Mengambil data booking berdasarkan ID

#### GET /booking-api.php?action=get_bookings
Mengambil daftar booking dengan filter dan pagination
**Parameter**:
- `limit`: Jumlah data per halaman (default: 10)
- `offset`: Offset data (default: 0)
- `status`: Filter berdasarkan status
- `date_from`: Filter tanggal mulai
- `date_to`: Filter tanggal akhir

#### GET /booking-api.php?action=get_stats
Mengambil statistik booking

#### GET /booking-api.php?action=check_availability&date={date}
Mengecek ketersediaan tanggal
**Parameter**:
- `date`: Tanggal yang akan dicek (format: Y-m-d)
- `exclude_id`: ID booking yang dikecualikan (untuk edit)

## Integrasi dengan File Utama

### jadwal-booking.php
File utama telah diperbarui untuk:
- Menggunakan `booking-simpan.php` untuk form submission
- Link ke `booking-edit.php` dan `booking-hapus.php` untuk aksi
- Link ke `booking-detail.php` untuk melihat detail
- AJAX submission dengan feedback

## Keamanan

### Validasi Input
- Semua input divalidasi dan disanitasi
- Prepared statements untuk mencegah SQL injection
- Session validation untuk autentikasi

### Error Handling
- Try-catch blocks untuk error handling
- Proper HTTP status codes
- User-friendly error messages

## Fitur Tambahan

### 1. Auto-format WhatsApp
- Otomatis menambahkan '0' di depan jika tidak ada
- Validasi format nomor Indonesia

### 2. Date Validation
- Minimum date validation
- Format validation

### 3. Duplicate Check
- Cek duplikasi booking untuk klien dan tanggal yang sama

### 4. Status Management
- Quick status update buttons
- Color-coded status badges

### 5. Related Transactions
- Menampilkan pemasukan/pengeluaran terkait
- Kalkulasi profit/loss

## Usage Examples

### 1. Menambah Booking Baru
```javascript
const formData = new FormData();
formData.append('nama_klien', 'John Doe');
formData.append('tanggal_booking', '2024-12-25');
formData.append('jenis_event', 'Pernikahan');
formData.append('kontak_wa', '08123456789');
formData.append('status', 'pending');

fetch('booking-simpan.php', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => console.log(data));
```

### 2. Update Status Booking
```javascript
const formData = new FormData();
formData.append('id', 123);
formData.append('status', 'deal');
// ... other fields

fetch('booking-simpan.php', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => console.log(data));
```

### 3. Cek Ketersediaan Tanggal
```javascript
fetch('booking-api.php?action=check_availability&date=2024-12-25')
.then(response => response.json())
.then(data => {
    if (data.data.available) {
        console.log('Tanggal tersedia');
    } else {
        console.log('Sudah ada booking pada tanggal ini');
    }
});
```

## Database Schema

Sistem menggunakan tabel `booking` dengan struktur:
- `id` (Primary Key)
- `nama_klien` (VARCHAR)
- `tanggal_booking` (DATE)
- `jenis_event` (VARCHAR)
- `kontak_wa` (VARCHAR)
- `status` (ENUM: pending, dp, deal, cancel)
- `catatan` (TEXT)
- `created_at` (TIMESTAMP)

## Future Enhancements

1. **Notification System**: Notifikasi WhatsApp otomatis
2. **Calendar Integration**: Integrasi dengan Google Calendar
3. **Email Notifications**: Notifikasi email untuk klien
4. **File Upload**: Upload kontrak dan dokumen
5. **Payment Integration**: Integrasi payment gateway
6. **Multi-user Support**: Role-based access control
7. **Backup System**: Automated backup
8. **Export Features**: Export ke PDF/Excel

## Troubleshooting

### Common Issues

1. **Error 401 Unauthorized**
   - Pastikan session aktif
   - Login ulang jika diperlukan

2. **Error 404 Not Found**
   - Pastikan ID booking valid
   - Cek apakah booking masih ada di database

3. **Validation Errors**
   - Cek format input (tanggal, nomor WhatsApp)
   - Pastikan field required sudah diisi

4. **Database Errors**
   - Cek koneksi database
   - Pastikan tabel booking ada
   - Cek permission database user

### Debug Mode
Untuk debugging, tambahkan parameter `debug=1` pada URL untuk melihat error detail (hanya untuk development).

## Maintenance

### Regular Tasks
1. Backup database secara berkala
2. Monitor log errors
3. Update dependencies
4. Optimize database queries
5. Clean up old sessions

### Performance Optimization
1. Index pada kolom yang sering diquery
2. Pagination untuk data besar
3. Caching untuk data statis
4. Optimize images dan assets