<?php
$pageTitle = "Pemasukan"; // kalau mau dynamic title
ob_start();
?>

<style>
  .modern-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem 0;
    margin: -1.5rem -1.5rem 2rem -1.5rem;
    border-radius: 0 0 24px 24px;
    text-align: center;
  }

  .modern-header h3 {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
  }

  .card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    background: white;
  }

  .card-body {
    padding: 2rem;
  }

  .form-group label {
    font-weight: 600;
    color: #374151;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    display: block;
  }

  .form-control {
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f9fafb;
  }

  .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background: white;
    outline: none;
  }

  .form-control::placeholder {
    color: #9ca3af;
  }

  .btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 12px;
    padding: 1rem 2rem;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    width: 100%;
    margin-top: 1rem;
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
    background: linear-gradient(135deg, #5a67d8 0%, #6b5b95 100%);
  }

  .btn-primary:active {
    transform: translateY(0);
  }

  /* Input icons */
  .form-group {
    position: relative;
  }

  .form-group[data-icon]::before {
    content: attr(data-icon);
    position: absolute;
    right: 1rem;
    top: 2.2rem;
    color: #9ca3af;
    font-size: 1.1rem;
    z-index: 2;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .modern-header {
      margin: -1rem -1rem 1.5rem -1rem;
      padding: 1.5rem 0;
    }

    .card-body {
      padding: 1.5rem;
    }
  }
</style>

<div class="card">
  <div class="modern-header">
    <h3>Form Tambah Pemasukan</h3>
  </div>
  <div class="card-body">
    <form action="#" method="POST">
      <div class="form-group mb-3">
        <label for="tanggal">Tanggal</label>
        <input type="date" class="form-control" id="tanggal" name="tanggal" required />
      </div>

      <div class="form-group mb-3>
        <label for=" nominal">Nominal</label>
        <input type="number" class="form-control" id="nominal" name="nominal" placeholder="Masukkan jumlah nominal" required />
      </div>

      <div class="form-group mb-3">
        <label for="sumber">Sumber Pemasukan</label>
        <input type="text" class="form-control" id="sumber" name="sumber" placeholder="Contoh: Gaji, Freelance, Bisnis" required />
      </div>

      <div class="form-group mb-3">
        <label for="keterangan">Keterangan</label>
        <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Keterangan tambahan (opsional)"></textarea>
      </div>

      <button type="submit" class="btn btn-primary"> Simpan Pemasukan</button>
    </form>
  </div>
</div>

<?php
$content = ob_get_clean();
include 'layout/main.php'; // layout utama
?>