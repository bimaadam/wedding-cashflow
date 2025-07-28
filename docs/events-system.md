# Dokumentasi Sistem Events Terpisah

## Overview
Sistem events telah dipisahkan menjadi beberapa file untuk struktur yang lebih modular dan maintainable. Setiap file memiliki tanggung jawab yang spesifik, mengikuti pola yang sama dengan sistem booking.

## File Structure

### 1. events-simpan.php
**Fungsi**: Menangani operasi create dan update events
**Method**: POST
**Response**: JSON

**Parameter Input**:
- `nama_event` (required): Nama event
- `tanggal` (required): Tanggal event (format: Y-m-d)
- `lokasi` (optional): Lokasi event
- `deskripsi` (optional): Deskripsi event
- `id` (optional): ID event untuk update

**Fitur**:
- Validasi input lengkap
- Validasi format tanggal
- Validasi panjang nama event minimal 3 karakter
- Cek duplikasi event (nama dan tanggal sama)
- Response JSON dengan status dan pesan

**Contoh Response**:
```json
{
  "success": true,
  "message": "Event berhasil ditambahkan!",
  "action": "insert",
  "id": 123
}
```

### 2. events-edit.php
**Fungsi**: Menampilkan form edit event
**Method**: GET, POST
**Parameter**: `id` (event ID)

**Fitur**:
- Form edit dengan data pre-filled
- Validasi client-side dan server-side
- Informasi event di sidebar
- Ringkasan cashflow event
- Countdown ke tanggal event
- Quick actions untuk cashflow management
- Link ke delete event

### 3. events-hapus.php
**Fungsi**: Menangani penghapusan event beserta data terkait
**Method**: GET (konfirmasi), POST (delete)
**Parameter**: `id` (event ID)

**Fitur**:
- Halaman konfirmasi dengan detail event
- Menampilkan data terkait yang akan dihapus (pemasukan/pengeluaran)
- Validasi keberadaan event
- Transaction untuk delete cascade
- Response JSON untuk AJAX request
- Modal loading dan feedback
- Safety checks sebelum delete

### 4. events-detail.php
**Fungsi**: Menampilkan detail lengkap event
**Method**: GET
**Parameter**: `id` (event ID)

**Fitur**:
- Informasi lengkap event
- Ringkasan keuangan dengan profit/loss
- Daftar transaksi pemasukan
- Daftar transaksi pengeluaran
- Booking terkait
- Countdown ke event
- Quick actions untuk management
- Margin calculation

### 5. events-api.php
**Fungsi**: API endpoints untuk operasi events
**Method**: GET, POST
**Parameter**: `action` (get_event, get_events, get_stats, get_cashflow, get_upcoming)

**Endpoints**:

#### GET /events-api.php?action=get_event&id={id}
Mengambil data event berdasarkan ID dengan cashflow data

#### GET /events-api.php?action=get_events
Mengambil daftar events dengan filter dan pagination
**Parameter**:
- `limit`: Jumlah data per halaman (default: 10)
- `offset`: Offset data (default: 0)
- `status`: Filter berdasarkan status (upcoming, past, all)
- `date_from`: Filter tanggal mulai
- `date_to`: Filter tanggal akhir
- `include_cashflow`: Include cashflow data (true/false)

#### GET /events-api.php?action=get_stats
Mengambil statistik events dan cashflow

#### GET /events-api.php?action=get_cashflow&event_id={id}
Mengambil data cashflow lengkap untuk event tertentu

#### GET /events-api.php?action=get_upcoming
Mengambil events mendatang
**Parameter**:
- `limit`: Jumlah events (default: 5)
- `days`: Rentang hari (default: 30)

## Integrasi dengan File Utama

### events.php
File utama telah diperbarui untuk:
- Menggunakan `events-simpan.php` untuk form submission
- Link ke `events-edit.php`, `events-hapus.php`, dan `events-detail.php` untuk aksi
- AJAX submission dengan feedback
- Improved error handling dan success messages

## Keamanan

### Validasi Input
- Semua input divalidasi dan disanitasi
- Prepared statements untuk mencegah SQL injection
- Session validation untuk autentikasi
- Transaction untuk operasi delete cascade

### Error Handling
- Try-catch blocks untuk error handling
- Proper HTTP status codes
- User-friendly error messages
- Rollback untuk failed transactions

## Fitur Tambahan

### 1. Cashflow Integration
- Otomatis menghitung total pemasukan dan pengeluaran
- Profit/loss calculation
- Margin percentage
- Related transactions display

### 2. Date Management
- Countdown to event date
- Status berdasarkan tanggal (upcoming/past)
- Date validation

### 3. Duplicate Prevention
- Cek duplikasi berdasarkan nama dan tanggal
- Validation sebelum insert

### 4. Cascade Delete
- Menghapus semua data terkait (pemasukan/pengeluaran)
- Transaction untuk data integrity
- Confirmation dengan detail data yang akan dihapus

### 5. Related Data Display
- Booking terkait berdasarkan nama event
- Transaksi pemasukan dan pengeluaran
- Summary statistics

## Usage Examples

### 1. Menambah Event Baru
```javascript
const formData = new FormData();
formData.append('nama_event', 'Wedding John & Jane');
formData.append('tanggal', '2024-12-25');
formData.append('lokasi', 'Hotel Grand');
formData.append('deskripsi', 'Wedding decoration package');

fetch('events-simpan.php', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => console.log(data));
```

### 2. Get Events dengan Cashflow
```javascript
fetch('events-api.php?action=get_events&include_cashflow=true&limit=20')
.then(response => response.json())
.then(data => {
    data.data.forEach(event => {
        console.log(`${event.nama_event}: Profit ${event.profit_loss}`);
    });
});
```

### 3. Get Cashflow Detail
```javascript
fetch('events-api.php?action=get_cashflow&event_id=123')
.then(response => response.json())
.then(data => {
    console.log('Pemasukan:', data.data.pemasukan);
    console.log('Pengeluaran:', data.data.pengeluaran);
    console.log('Summary:', data.data.summary);
});
```

### 4. Get Upcoming Events
```javascript
fetch('events-api.php?action=get_upcoming&days=7&limit=5')
.then(response => response.json())
.then(data => {
    console.log('Events minggu ini:', data.data);
});
```

## Database Schema

Sistem menggunakan tabel `events` dengan relasi ke `pemasukan` dan `pengeluaran`:

### Tabel `events`
- `id` (Primary Key)
- `nama_event` (VARCHAR)
- `tanggal` (DATE)
- `lokasi` (VARCHAR)
- `deskripsi` (TEXT)
- `created_at` (TIMESTAMP)

### Relasi
- `pemasukan.event_id` → `events.id`
- `pengeluaran.event_id` → `events.id`

## Perbedaan dengan Sistem Booking

1. **Cashflow Integration**: Events memiliki relasi langsung dengan pemasukan dan pengeluaran
2. **Cascade Delete**: Menghapus event akan menghapus semua transaksi terkait
3. **Profit/Loss Calculation**: Otomatis menghitung keuntungan per event
4. **Related Bookings**: Menampilkan booking yang terkait dengan event
5. **Margin Analysis**: Analisis margin keuntungan

## Future Enhancements

1. **Budget Planning**: Set budget target per event
2. **Cost Analysis**: Breakdown detail pengeluaran per kategori
3. **ROI Calculation**: Return on investment analysis
4. **Event Templates**: Template untuk jenis event tertentu
5. **Client Management**: Integrasi dengan data klien
6. **Inventory Tracking**: Track penggunaan peralatan per event
7. **Timeline Management**: Jadwal persiapan event
8. **Photo Gallery**: Gallery foto per event
9. **Invoice Generation**: Generate invoice otomatis
10. **Performance Metrics**: KPI per event

## Troubleshooting

### Common Issues

1. **Error 401 Unauthorized**
   - Pastikan session aktif
   - Login ulang jika diperlukan

2. **Error 404 Not Found**
   - Pastikan ID event valid
   - Cek apakah event masih ada di database

3. **Validation Errors**
   - Cek format input (tanggal, nama event)
   - Pastikan field required sudah diisi

4. **Cascade Delete Issues**
   - Cek foreign key constraints
   - Pastikan transaction berjalan dengan benar

5. **Cashflow Calculation Errors**
   - Verify data pemasukan dan pengeluaran
   - Check SUM calculation in queries

### Debug Mode
Untuk debugging, tambahkan parameter `debug=1` pada URL untuk melihat error detail (hanya untuk development).

## Maintenance

### Regular Tasks
1. Backup database secara berkala
2. Monitor cashflow calculations
3. Clean up old events (optional)
4. Optimize database queries
5. Update related transactions

### Performance Optimization
1. Index pada kolom yang sering diquery (tanggal, event_id)
2. Pagination untuk data besar
3. Caching untuk cashflow calculations
4. Optimize JOIN queries untuk cashflow data
5. Regular ANALYZE TABLE untuk performance

## Integration Points

### Dengan Sistem Booking
- Link booking ke event berdasarkan nama/jenis
- Cross-reference untuk tracking klien

### Dengan Cashflow
- Otomatis link pemasukan/pengeluaran ke event
- Real-time profit/loss calculation

### Dengan Reporting
- Data source untuk laporan keuangan
- Event performance analysis

## Security Considerations

1. **Input Validation**: Semua input divalidasi sebelum database operation
2. **SQL Injection Prevention**: Menggunakan prepared statements
3. **Session Management**: Validasi session untuk setiap request
4. **Transaction Safety**: Rollback untuk failed operations
5. **Access Control**: Session-based access control