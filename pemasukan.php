<?php
$pageTitle = "Pemasukan"; // kalau mau dynamic title
ob_start();
?>

<h3 class="mb-4">Form Tambah Pemasukan</h3>

<div class="card">
  <div class="card-body">
    <form action="#" method="POST">
      <div class="form-group mb-3">
        <label for="tanggal">Tanggal</label>
        <input type="date" class="form-control" id="tanggal" name="tanggal" required />
      </div>

      <div class="form-group mb-3">
        <label for="nominal">Nominal</label>
        <input type="number" class="form-control" id="nominal" name="nominal" required />
      </div>

      <div class="form-group mb-3">
        <label for="sumber">Sumber Pemasukan</label>
        <input type="text" class="form-control" id="sumber" name="sumber" required />
      </div>

      <div class="form-group mb-3">
        <label for="keterangan">Keterangan</label>
        <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
      </div>

      <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
  </div>
</div>

<?php
$content = ob_get_clean();
include 'layout/main.php'; // layout utama
?>