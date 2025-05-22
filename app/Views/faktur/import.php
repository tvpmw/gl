<?php
// Cek apakah sistem dalam mode development
$isUnderDevelopment = false; // Anda bisa mengubah ini menjadi variabel dari environment atau config

if ($isUnderDevelopment) {
    // Tampilkan halaman under development
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Under Development</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container min-vh-100 d-flex align-items-center justify-content-center">
            <div class="text-center">
                <h1 class="display-4 mb-4">🚧 Under Development</h1>
                <p class="lead mb-4">Halaman ini sedang dalam tahap pengembangan.</p>
                <p class="text-muted">Silakan kembali beberapa saat lagi.</p>
                <a href="<?= base_url() ?>" class="btn btn-primary mt-3">Kembali ke Beranda</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Import Data Coretax</h1>
    
    <!-- Form Card -->
    <div class="card mb-4">
        <div class="card-body">
            <?php if (session()->getFlashdata('error')) : ?>
                <div class="alert alert-danger">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')) : ?>
                <div class="alert alert-success">
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <form id="form-import" enctype="multipart/form-data">
                <div class="row mb-3 justify-content-center">
                    <!-- Database Selection -->
                    <div class="col-md-6 text-center">
                        <label for="sumber_data" class="form-label">Pilih Database</label>
                        <select class="form-select" name="sumber_data" id="sumber_data" required>
                           <?php 
                            $dbList = getSelDb();
                            foreach ($dbList as $key => $name): ?>
                                <option value="<?= $key ?>"><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3 justify-content-center">    
                    <!-- File Upload -->
                    <div class="col-md-6 text-center">
                        <label for="file_import" class="form-label">File Excel</label>
                        <input type="file" class="form-control" name="file_import" id="file_import" accept=".xls,.xlsx" required>
                        <div class="form-text">Format file yang diizinkan: XLS, XLSX. Maksimal ukuran file: 5MB</div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary" id="btnSubmit">
                        <i class="fas fa-upload me-2"></i>Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview Card -->
    <div class="card mb-4" id="preview-table" style="display: none;">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center text-white">
                <div>
                    <i class="fas fa-table me-1"></i>
                    Preview Data
                </div>
                <div>
                    <?php 
                    $fakturAccess = checkMenuAccess('cms/faktur');
                    if ($fakturAccess['can_create']): ?>    
                    <button type="button" class="btn btn-success" id="btnSave">
                        <i class="fas fa-save me-2"></i>Simpan Data
                    </button>
                    <?php endif; ?>
                </div>                
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="previewTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>                            
                            <th>NPWP</th>
                            <th>Nama Pembeli</th>
                            <th>Kode Transaksi</th>
                            <th>No Faktur</th>
                            <th>Tanggal Faktur</th>
                            <th>Masa Pajak</th>
                            <th>Tahun Pajak</th>
                            <th>Status Faktur</th>
                            <th>Harga Jual</th>
                            <th>DPP</th>
                            <th>PPN</th>
                            <th>PPNBm</th>
                            <th>Referensi</th>
                            <th>Dilaporkan Penjual</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    let previewData = [];
    
    $('#form-import').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Show loading
        Swal.fire({
            title: 'Memproses File',
            text: 'Mohon tunggu sebentar...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '<?= base_url('cms/faktur/preview-import') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
            Swal.close();
            
            if (response.success) {
                previewData = response.data;
                
                // Check existing data first before showing preview
                $.ajax({
                    url: '<?= base_url('cms/faktur/check-existing') ?>',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        data: previewData.map(item => item.no_faktur),
                        sumber_data: $('#sumber_data').val()
                    }),
                    success: function(checkResponse) {
                        // Update button state based on existence check
                        if (checkResponse.exists) {
                            $('#btnSave')
                                .html('<i class="fas fa-sync me-2"></i>Update Data')
                                .removeClass('btn-success')
                                .addClass('btn-warning');
                        } else {
                            $('#btnSave')
                                .html('<i class="fas fa-save me-2"></i>Simpan Data')
                                .removeClass('btn-warning')
                                .addClass('btn-success');
                        }
                                                
                        $('#previewTable').DataTable({
                            destroy: true,
                            data: previewData,
                            columns: [
                                { data: 'npwp' },
                                { data: 'nama_pembeli' },
                                { data: 'kode_transaksi' },
                                { data: 'no_faktur' },
                                { data: 'tanggal_faktur' },
                                { data: 'masa_pajak' },
                                { data: 'tahun' },
                                { data: 'status_faktur' },
                                { 
                                    data: 'harga_jual',
                                    render: function(data) {
                                        return formatRupiah(data);
                                    }
                                },
                                { 
                                    data: 'dpp',
                                    render: function(data) {
                                        return formatRupiah(data);
                                    }
                                },
                                { 
                                    data: 'ppn',
                                    render: function(data) {
                                        return formatRupiah(data);
                                    }
                                },
                                { 
                                    data: 'ppnbm',
                                    render: function(data) {
                                        return formatRupiah(data);
                                    }
                                },
                                { data: 'referensi' },
                                { data: 'dilaporkan_penjual' }
                            ],
                            pageLength: 10,
                            ordering: true,
                            searching: true,
                            responsive: true,                            
                        });
                        
                        $('#preview-table').show();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: response.message
                });
            }
        },
            error: function(xhr, status, error) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan: ' + error
                });
            }
        });
    });

    $('#btnSave').on('click', function() {
        if (!previewData.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Tidak ada data yang dapat disimpan'
            });
            return;
        }

        // Get current button state
        const isUpdate = $(this).hasClass('btn-warning');
        
        Swal.fire({
            title: isUpdate ? 'Update Data' : 'Simpan Data',
            text: isUpdate ? 'Apakah Anda yakin akan mengupdate data ini?' : 'Apakah Anda yakin akan menyimpan data ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: isUpdate ? 'Ya, Update' : 'Ya, Simpan',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Memproses Data',
                    text: 'Mohon tunggu sebentar...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '<?= base_url('cms/faktur/save-import') ?>',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        data: previewData,
                        is_update: isUpdate,
                        sumber_data: $('#sumber_data').val()
                    }),
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            }).then(() => {
                                // Reset form dan table
                                $('#form-import')[0].reset();
                                $('#preview-table').hide();
                                previewData = [];
                                // Reset button state
                                $('#btnSave')
                                    .html('<i class="fas fa-save me-2"></i>Simpan Data')
                                    .removeClass('btn-warning')
                                    .addClass('btn-success');
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal memproses data: ' + response.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan: ' + error
                        });
                    }
                });
            }
        });
    });

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 2
        }).format(angka);
    }
});
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>