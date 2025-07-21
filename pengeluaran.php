<?php
$pageTitle = "Pengeluaran";
ob_start();
?>

<style>
    .modern-header {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        background: white;
        outline: none;
    }

    .form-control::placeholder {
        color: #9ca3af;
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        border: none;
        border-radius: 12px;
        padding: 1rem 2rem;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        width: 100%;
        margin-top: 1rem;
        color: white;
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(239, 68, 68, 0.4);
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: white;
    }

    .btn-danger:active {
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
        <h3>Form Tambah Pengeluaran</h3>
    </div>
    <div class="card-body">
        <form action="#" method="POST">
            <div class="form-group mb-3">
                <label for="tanggal">Tanggal</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" required />
            </div>

            <div class="form-group mb-3">
                <label for="nominal">Nominal</label>
                <input type="number" class="form-control" id="nominal" name="nominal" placeholder="Masukkan jumlah pengeluaran" required />
            </div>

            <div class="form-group mb-3">
                <label for="kategori">Kategori</label>
                <input type="text" class="form-control" id="kategori" name="kategori" placeholder="Contoh: Bensin, Nasi Padang">
            </div>

            <div class="form-group mb-3" data-icon="🏪">
                <label for="tempat">Tempat/Toko</label>
                <input type="text" class="form-control" id="tempat" name="tempat" placeholder="Contoh: Alfamart, Warung Bu Siti" />
            </div>

            <div class="form-group mb-3">
                <label for="keterangan">Keterangan</label>
                <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Keterangan tambahan (opsional)"></textarea>
            </div>

            <button type="submit" class="btn btn-danger">❌ Catat Pengeluaran</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout/main.php';
?>