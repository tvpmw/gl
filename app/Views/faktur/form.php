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
    <h1 class="mt-4">Generate Coretax</h1>
    
    <!-- Form Card -->
    <div class="card mb-4">
        <div class="card-body">
            <?php if (session()->getFlashdata('error')) : ?>
                <div class="alert alert-danger">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <form id="form-filter">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="startDate" class="form-label">Tanggal Awal</label>
                        <input type="date" class="form-control" name="startDate" id="startDate" value="<?=date('Y-m-d')?>" required>
                    </div>

                    <div class="col-md-3">
                        <label for="endDate" class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" name="endDate" id="endDate" value="<?=date('Y-m-d')?>" required>
                    </div>

                    <!-- Sales Type -->
                    <div class="col-md-3">
                        <label for="sales_type" class="form-label">Tipe Sales</label>
                        <select class="form-select" name="sales_type" id="sales_type" required>
                            <!-- <option value=""  hidden selected>Pilih Tipe Sales</option> -->
                            <option value="DISTRI">DISTRI</option>
                            <option value="ONLINE">ONLINE</option>
                        </select>
                    </div>

                    <!-- Sumber Data -->
                    <div class="col-md-3">
                        <label for="sumber_data" class="form-label">Sumber Data</label>
                        <select class="form-select" name="sumber_data" id="sumber_data" required>
                            <!-- <option value="" hidden selected>Pilih Sumber Data</option> -->
                            <?php foreach ($dbs as $row): ?>
                                <option value="<?= $row ?>"><?= $row ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file me-2"></i>Filter Data
                    </button>
                </div>                
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card mb-4" id="result-table" style=" display: none;">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center text-white">
                <div>
                    <i class="fas fa-table me-1"></i>
                    Data Transaksi
                </div>
                <div>
                    <button type="button" id="btnBatalGenerate" class="btn btn-danger">
                        <i class="fas fa-undo me-2"></i>Batal Generate
                    </button>
                    <button type="button" id="btnTidakDibuat" class="btn btn-warning">
                        <i class="fas fa-ban me-2"></i>Tidak Dibuat
                    </button>
                    <button type="button" class="btn btn-success" id="btnGenerateExcel">
                        <i class="fas fa-file-excel me-2"></i>Generate
                    </button>
                </div>                
            </div>
        </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTrx" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all" class="form-check-input">
                            </th>
                            <th>No</th>
                            <th>Kode Trx</th>
                            <th>Tanggal</th>
                            <th>Grand Total</th>
                            <th>Nama Customer</th>
                            <th>NPWP</th>
                            <th>NPWP Nama</th>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="detailTable">
                        <thead>
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Satuan</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Diskon</th>
                                <th>DPP</th>
                                <th>DPP Lain</th>
                                <th>PPN</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    const startDate = document.getElementById("startDate");
    const endDate = document.getElementById("endDate");

    startDate.addEventListener("click", function () {
        if (this.showPicker) {
            this.showPicker();
        } else {
            this.focus();
        }
    });

    endDate.addEventListener("click", function () {
        if (this.showPicker) {
            this.showPicker();
        } else {
            this.focus();
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        const startDate = document.getElementById("startDate");
        const endDate = document.getElementById("endDate");
        const today = new Date().toISOString().split("T")[0];
        startDate.max = today;
        startDate.value = today; // Set nilai default ke hari ini
        endDate.max = today;
        endDate.value = today; // Set nilai default ke hari ini
    });

let checkedRows = new Set();
let dataTable;

function loadTable() {
    dataTable = $('#dataTrx').DataTable({
        destroy: true,
        processing: true,
        serverSide: false,
        pageLength: 10,
        aaSorting: [],
        ajax: {
            url: "<?= base_url('cms/faktur/get-data') ?>",
            type: "POST",
            data: function(d) {
                return {
                    startDate: $('#startDate').val(),
                    endDate: $('#endDate').val(),
                    sales_type: $('#sales_type').val(),
                    sumber_data: $('#sumber_data').val()
                };
            },
            dataSrc: function(json) {
                // Store complete data for later use
                window.completeData = json.data;
                return json.data;
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    const isChecked = checkedRows.has(row[1]) ? 'checked' : '';
                    return `<input type="checkbox" class="form-check-input row-checkbox" value="${row[1]}" ${isChecked}>`;
                }
            },
            { 
                data: null,
                render: function(data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { data: 1 }, // Kode Trx
            { data: 2 }, // Tanggal
            { data: 3, className: 'text-end' }, // Grand Total
            { data: 4 }, // Nama Customer
            { data: 5 }, // NPWP
            { data: 6 }, // NPWP Nama 
            { data: 7 }, // Jenis
            { data: 8 }, // Status
            { 
                data: null,
                orderable: false, 
                className: 'text-center',
                render: function(data, type, row) {
                    return `<button type="button" class="btn btn-sm btn-dark text-white btn-detail" data-kdtr="${row[1]}">
                                <i class="fas fa-eye text-white"></i>
                            </button>`;
                }
            }
        ],
        drawCallback: function(settings) {
            // Reattach event handlers
            attachCheckboxHandlers();
            
            // Restore checked state
            $('.row-checkbox').each(function() {
                const value = $(this).val();
                $(this).prop('checked', checkedRows.has(value));
            });
            
            updateSelectAllState();
        }
    });

    // Initial attachment of handlers
    attachCheckboxHandlers();
}

function attachCheckboxHandlers() {
    // Remove existing handlers first
    $(document).off('change', '.row-checkbox');
    $(document).off('change', '#select-all');

    // Handle individual checkboxes using event delegation
    $(document).on('change', '.row-checkbox', function() {
        const value = $(this).val();
        if (this.checked) {
            checkedRows.add(value);
        } else {
            checkedRows.delete(value);
        }
        updateSelectAllState();
    });

    // Handle select all checkbox
    $(document).on('change', '#select-all', function() {
        const isChecked = $(this).prop('checked');
        
        $('.row-checkbox:visible').each(function() {
            const value = $(this).val();
            if (isChecked) {
                checkedRows.add(value);
            } else {
                checkedRows.delete(value);
            }
            $(this).prop('checked', isChecked);
        });
    });
}

function updateSelectAllState() {
    const visibleCheckboxes = $('.row-checkbox:visible');
    const checkedVisibleCheckboxes = $('.row-checkbox:visible:checked');
    $('#select-all').prop('checked', 
        visibleCheckboxes.length > 0 && 
        visibleCheckboxes.length === checkedVisibleCheckboxes.length
    );
    
    // Update counter or show selected count if needed
    const totalChecked = checkedRows.size;
    // Optional: Display total checked somewhere
    console.log(`Total selected: ${totalChecked}`);
}

// Modify btnTidakDibuat click handler
$('#btnTidakDibuat').on('click', function() {
    if (checkedRows.size === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Pilih transaksi yang akan ditandai tidak dibuatkan'
        });
        return;
    }

    const selectedRows = [];
    
    // Use stored complete data to find selected rows
    window.completeData.forEach(row => {
        if (checkedRows.has(row[1])) {
            const grandTotal = row[3].replace(/[Rp\s.]/g, '').replace(/,\d{2}$/, '');
            selectedRows.push({
                kode_trx: row[1],
                tanggal: row[2],
                grand_total: grandTotal,
                nama_customer: row[4],
                sumber_data: $('#sumber_data').val()
            });
        }
    });

    Swal.fire({
        title: 'Konfirmasi',
        text: 'Apakah Anda yakin akan menandai ' + selectedRows.length + ' transaksi sebagai tidak dibuatkan?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Tandai',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url('cms/faktur/tidak-dibuat') ?>',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ data: selectedRows }),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data berhasil disimpan/update!'
                        });
                        $('#dataTrx').DataTable().ajax.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Gagal menyimpan data: ' + response.message
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

// Reset checkedRows when form is submitted
$("#form-filter").submit(function (e) {
    e.preventDefault();
    checkedRows.clear();
    $('#result-table').show();
    loadTable();
});

// Modifikasi tombol Generate Excel
$('#btnGenerateExcel').on('click', function() {
    // Cek apakah ada data di tabel
    if (!window.completeData || window.completeData.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Tidak ada data yang dapat di-generate ke Excel'
        });
        return;
    }

    let selectedRows = [];

    // If there are checked rows, only include those
    if (checkedRows.size > 0) {
        window.completeData.forEach(row => {
            if (checkedRows.has(row[1])) {
                selectedRows.push(row[1]); // Get transaction code
            }
        });
    } else {
        // If no rows checked, include all transaction codes
        window.completeData.forEach(row => {
            selectedRows.push(row[1]);
        });
    }

    const params = new URLSearchParams({
        startDate: $('#startDate').val(),
        endDate: $('#endDate').val(),
        sales_type: $('#sales_type').val(),
        sumber_data: $('#sumber_data').val(),
        selected_trx: selectedRows.join(',')
    });
    
    window.location.href = `<?= base_url('cms/faktur/generate_excel') ?>?${params.toString()}`;
});

// Initialize detail modal
let detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
let detailTable;

// Handle detail button click
$(document).on('click', '.btn-detail', function() {
    const kdtr = $(this).data('kdtr');
    const sumber_data = $('#sumber_data').val();
    
    // Initialize DataTable if not already initialized
    if (!detailTable) {
        detailTable = $('#detailTable').DataTable({
            processing: true,
            searching: false,
            paging: false,
            info: false
        });
    } else {
        detailTable.clear();
    }

    // Fetch detail data
    $.ajax({
        url: '<?= base_url('cms/faktur/get-detail') ?>',
        type: 'POST',
        data: {
            kdtr: kdtr,
            sumber_data: sumber_data
        },
        success: function(response) {
            if (response.success) {
                response.data.forEach(function(item) {
                    detailTable.row.add([
                        item.kode_brg ? item.kode_brg : '000000',
                        item.nama_brg,
                        item.satuan,
                        item.qty,
                        formatPrice(item.hrg),
                        item.diskon + '%',
                        formatPrice(item.dpp),
                        formatPrice(item.dpp_lain),
                        formatPrice(item.nominal_ppn)
                    ]).draw(false);
                });
                detailModal.show();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan saat mengambil data detail'
            });
        }
    });
});

// Handler untuk batal generate
$('#btnBatalGenerate').on('click', function() {
    Swal.fire({
        title: 'Konfirmasi Pembatalan',
        text: 'Anda yakin ingin membatalkan generate data untuk periode ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Tidak',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url('cms/faktur/batal-generate') ?>',
                type: 'POST',
                data: {
                    startDate: $('#startDate').val(),
                    endDate: $('#endDate').val(),
                    sumber_data: $('#sumber_data').val()
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message
                        }).then(() => {
                            // Reload table data
                            $('#dataTrx').DataTable().ajax.reload();
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
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan sistem'
                    });
                }
            });
        }
    });
});

// Helper function to format price
function formatPrice(price) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(price);
}
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>