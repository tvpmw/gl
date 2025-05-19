<?= $this->extend('layouts/admin') ?>
<?= $this->section('styles') ?>
<style>
    .nav-link.active{
        background: var(--primary-color) !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4 py-4">
    <ul class="nav nav-pills mb-2">
      <li class="nav-item">
        <a class="nav-link active" aria-current="page" href="javascript:void(0);">Tax Generate</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="<?=base_url('cms/tax-retur')?>">Retur</a>        
      </li>
    </ul>    
    <h3 class="mt-4">Cek Data Tax Generate</h3>
    
    <!-- Form Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="form-filter">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="startDate" class="form-label">Tanggal Awal</label>
                        <input type="date" class="form-control" name="startDate" id="startDate" value="<?=date('Y-m-d')?>" required>
                    </div>

                    <div class="col-md-4">
                        <label for="endDate" class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" name="endDate" id="endDate" value="<?=date('Y-m-d')?>" required>
                    </div>

                    <!-- Sumber Data -->
                    <div class="col-md-4">
                        <label for="sumber_data" class="form-label">Sumber Data</label>
                        <select class="form-select" name="sumber_data" id="sumber_data" required>
                            <?php foreach ($dbs as $row): ?>
                                <option value="<?= $row ?>"><?= $row ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Filter Data
                    </button>
                </div>                
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card mb-4" id="result-table" style="display: none;">
        <div class="card-header text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-table me-1"></i>
                    Data Tax Generate
                </div>
                <div>
                    <button type="button" id="btnBatalGenerate" class="btn btn-danger">
                        <i class="fas fa-undo me-2"></i>Batal Generate
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTaxGenerate" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all" class="form-check-input">
                            </th>
                            <th>Kode Transaksi</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Current Tax</th>
                            <th>PPN Coretax</th>
                            <th>Selisih</th>
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Tax Generate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="detailTable">
                        <thead>
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>                                
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Diskon <small>(mstr)</small></th>
                                <th>Diskon <small>(tr)</small></th>
                                <th>DPP</th>
                                <th>DPP Lain</th>
                                <th>PPN</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <small><i class="text-danger">* Perubahan baru akan ditandai dengan warna merah</i></small>                                                                                
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Detail Coretax Modal -->
<div class="modal fade" id="coretaxModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Coretax</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="coretaxTable">
                        <thead>
                            <tr>
                                <th>NPWP</th>
                                <th>Nama Pembeli</th>
                                <th>Kode Transaksi</th>
                                <th>No Faktur</th>
                                <th>Tanggal Faktur</th>
                                <th>Masa Pajak</th>
                                <th>Tahun</th>
                                <th>Status Faktur</th>
                                <th>Harga Jual</th>
                                <th>DPP</th>
                                <th>PPN</th>
                                <th>PPnBM</th>
                                <th>Referensi</th>
                                <th>Dilaporkan Penjual</th>
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

// Set default date values and max date
document.addEventListener("DOMContentLoaded", function () {
    const today = new Date().toISOString().split("T")[0];
    startDate.max = today;
    startDate.value = today;
    endDate.max = today;
    endDate.value = today;
});

let dataTable;
let detailTable;
let detailModal;
let coretaxTable; // Declare coretaxTable in the wider scope
let coretaxModal;
let checkedRows = new Set();

$(document).ready(function() {
    // Initialize modals
    detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
    coretaxModal = new bootstrap.Modal(document.getElementById('coretaxModal'));

    // Initialize detailTable
    detailTable = $('#detailTable').DataTable({
        processing: true,
        searching: false,
        paging: false,
        info: false,
        columns: [
            { 
                data: 'nmbrg',      // Kode Barang
                className: 'align-middle',
                render: function(data, type, row) {
                    if (row.status === 'deleted') {
                        return `<span class="text-danger"><del>${row.stored.nmbrg}</del></span>`;
                    }
                    return row.current?.nmbrg || row.stored?.nmbrg || '-';
                }
            },
            { 
                data: 'nama_brg',   // Nama Barang
                className: 'align-middle',
                render: function(data, type, row) {
                    if (row.status === 'deleted') {
                        return `<span class="text-danger"><del>${row.stored.nama_brg}</del></span>`;
                    } else if (row.changes?.nama_brg) {
                        return `<span class="text-danger">${row.current.nama_brg}</span>
                               <small class="d-block text-muted"><del>${row.stored.nama_brg}</del></small>`;
                    }
                    return row.current?.nama_brg || row.stored?.nama_brg || '-';
                }
            },        
            { 
                data: 'qty',        // Qty
                className: 'text-end align-middle',
                render: function(data, type, row) {
                    if (row.status === 'deleted') {
                        return `<span class="text-danger"><del>${row.stored.qty}</del></span>`;
                    } else if (row.changes?.qty) {
                        return `<span class="text-danger">${row.current.qty}</span>
                               <small class="d-block text-muted"><del>${row.stored.qty}</del></small>`;
                    }
                    return row.current?.qty || row.stored?.qty || '0';
                }
            },
            { 
                data: 'hrg',        // Harga
                className: 'text-end align-middle',
                render: function(data, type, row) {
                    if (row.status === 'deleted') {
                        return `<span class="text-danger"><del>${formatNumber(row.stored.hrg)}</del></span>`;
                    } else if (row.changes?.hrg) {
                        return `<span class="text-danger">${formatNumber(row.current.hrg)}</span>
                               <small class="d-block text-muted"><del>${formatNumber(row.stored.hrg)}</del></small>`;
                    }
                    return formatNumber(row.current?.hrg || row.stored?.hrg || 0);
                }
            },
            { 
                data: 'diskon',     // Diskon Mstr
                className: 'text-end align-middle',
                render: function(data, type, row) {
                    if (row.status === 'deleted') {
                        return `<span class="text-danger">
                            <del>${formatNumber(row.stored.diskon)}</del></span>`;
                    } else if (row.changes?.diskon) {
                        return `<span class="text-danger">${formatNumber(row.current.diskon)}</span>
                               <small class="d-block text-muted">
                                   <del>${formatNumber(row.stored.diskon)}</del>
                               </small>`;
                    }
                    return formatNumber(row.current?.diskon || row.stored?.diskon || 0);
                }
            },
            { 
                data: 'diskon_tr',  // Diskon TR
                className: 'text-end align-middle',
                render: function(data, type, row) {
                    if (row.status === 'deleted') {
                        return `<span class="text-danger">
                            <del>${row.stored.diskon_tr}%</del></span>`;
                    } else if (row.changes?.diskon_tr) {
                        return `<span class="text-danger">${row.current.diskon_tr}%</span>
                               <small class="d-block text-muted">
                                   <del>${row.stored.diskon_tr}%</del>
                               </small>`;
                    }
                    return `${row.current?.diskon_tr || row.stored?.diskon_tr || 0}%`;
                }
            },
            { 
                data: 'dpp',        // DPP
                className: 'text-end align-middle',
                render: function(data, type, row) {
                    if (row.status === 'deleted') {
                        return `<span class="text-danger"><del>${formatNumber(row.stored.dpp)}</del></span>`;
                    } else if (row.changes?.dpp) {
                        return `<span class="text-danger">${formatNumber(row.current.dpp)}</span>
                               <small class="d-block text-muted"><del>${formatNumber(row.stored.dpp)}</del></small>`;
                    }
                    return formatNumber(row.current?.dpp || row.stored?.dpp || 0);
                }
            },
            { 
                data: 'dpp_lain',   // DPP Lain
                className: 'text-end align-middle',
                render: function(data, type, row) {
                    if (row.status === 'deleted') {
                        return `<span class="text-danger"><del>${formatNumber(row.stored.dpp_lain)}</del></span>`;
                    } else if (row.changes?.dpp_lain) {
                        return `<span class="text-danger">${formatNumber(row.current.dpp_lain)}</span>
                               <small class="d-block text-muted"><del>${formatNumber(row.stored.dpp_lain)}</del></small>`;
                    }
                    return formatNumber(row.current?.dpp_lain || row.stored?.dpp_lain || 0);
                }
            },
            { 
                data: 'ppn',        // PPN
                className: 'text-end align-middle',
                render: function(data, type, row) {
                    if (row.status === 'deleted') {
                        return `<span class="text-danger"><del>${formatNumber(row.stored.ppn)}</del></span>`;
                    } else if (row.changes?.ppn) {
                        return `<span class="text-danger">${formatNumber(row.current.ppn)}</span>
                               <small class="d-block text-muted"><del>${formatNumber(row.stored.ppn)}</del></small>`;
                    }
                    return formatNumber(row.current?.ppn || row.stored?.ppn || 0);
                }
            }
        ],
        createdRow: function(row, data) {
            // Add background color for new items
            if (data.status === 'new') {
                $(row).addClass('table-success');
            }
            // Add background color for deleted items
            else if (data.status === 'deleted') {
                $(row).addClass('table-danger');
            }
            // Add background color for changed items
            else if (data.status === 'changed') {
                $(row).addClass('table-warning');
            }
        }
    });

    // Initialize coretaxTable with DataTable (not DataTable())
    coretaxTable = $('#coretaxTable').DataTable({
        processing: true,
        searching: false,
        paging: false,
        info: false,
        columns: [
            { data: 'npwp' },
            { data: 'nama_pembeli' },
            { data: 'kode_transaksi' },
            { data: 'no_faktur' },
            { 
                data: 'tanggal_faktur',
                render: function(data) {
                    return data ? moment(data).format('DD/MM/YYYY') : '-';
                }
            },
            { data: 'masa_pajak' },
            { data: 'tahun' },
            { 
                data: 'status_faktur',
                render: function(data) {
                    let badgeClass = 'bg-secondary';
                    if (data === 'APPROVED') badgeClass = 'bg-success';
                    if (data === 'CREATED') badgeClass = 'bg-info';
                    return `<span class="badge ${badgeClass}">${data || 'UNDEFINED'}</span>`;
                }
            },
            { 
                data: 'harga_jual',
                render: function(data) {
                    return formatNumber(data || 0);
                }
            },
            { 
                data: 'dpp',
                render: function(data) {
                    return formatNumber(data || 0);
                }
            },
            { 
                data: 'ppn',
                render: function(data) {
                    return formatNumber(data || 0);
                }
            },
            { 
                data: 'ppnbm',
                render: function(data) {
                    return formatNumber(data || 0);
                }
            },
            { data: 'referensi' },
            { data: 'dilaporkan_penjual' }
        ]
    });

    $("#form-filter").submit(function(e) {
        e.preventDefault();
        $('#result-table').show();
        loadTable();
    });
});

function loadTable() {
    if (dataTable) {
        dataTable.destroy();
    }

    dataTable = $('#dataTaxGenerate').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "<?= base_url('cms/tax-generate/get-data') ?>",
            type: "POST",
            data: function(d) {
                return {
                    startDate: $('#startDate').val(),
                    endDate: $('#endDate').val(),
                    sumber_data: $('#sumber_data').val()
                };
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    const isChecked = checkedRows.has(row[0]) ? 'checked' : '';
                    return `<input type="checkbox" class="form-check-input row-checkbox" value="${row[0]}" ${isChecked}>`;
                }
            },
            { data: 0 }, // Kode Transaksi
            { data: 1 }, // Tanggal
            { data: 2 }, // Jam
            { data: 3 }, // Total Tax
            { data: 4 }, // PPN Coretax
            { 
                data: 5, // Selisih (now comes formatted from backend)
                render: function(data) {
                    return data; // Already formatted with color in backend
                }
            },
            { data: 6 }, // Status badges
            { data: 7 }  // Action buttons
        ],
        drawCallback: function(settings) {
            attachCheckboxHandlers();
            $('.row-checkbox').each(function() {
                const value = $(this).val();
                $(this).prop('checked', checkedRows.has(value));
            });
            updateSelectAllState();
        }
    });
}

function attachCheckboxHandlers() {
    $(document).off('change', '.row-checkbox');
    $(document).off('change', '#select-all');

    $(document).on('change', '.row-checkbox', function() {
        const value = $(this).val();
        if (this.checked) {
            checkedRows.add(value);
        } else {
            checkedRows.delete(value);
        }
        updateSelectAllState();
    });

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
}

// Handle detail button click
$(document).on('click', '.btn-detail', function() {
    const kdtr = $(this).data('kdtr');
    
    detailTable.clear();
    
    $.ajax({
        url: '<?= base_url('cms/tax-generate/get-detail') ?>',
        type: 'POST',
        data: {
            kdtr: kdtr,
            sumber_data: $('#sumber_data').val()
        },
        success: function(response) {
            if (response.success) {
                detailTable.rows.add(response.data).draw();
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

// Handle coretax button click
$(document).on('click', '.btn-coretax', function() {
    const kdtr = $(this).data('kdtr');
    
    if (coretaxTable) {
        coretaxTable.clear().draw();
    }
    
    $.ajax({
        url: '<?= base_url('cms/tax-generate/get-coretax-detail') ?>',
        type: 'POST',
        data: {
            kdtr: kdtr,
            sumber_data: $('#sumber_data').val()
        },
        success: function(response) {
            if (response.success) {
                coretaxTable.rows.add(response.data).draw();
                coretaxModal.show();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Failed to load coretax data'
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Terjadi kesalahan saat mengambil data coretax'
            });
        }
    });
});

function formatNumber(number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(number);
}

// Add batch cancellation handler
$('#btnBatalGenerate').on('click', function() {
    if (checkedRows.size === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Pilih transaksi yang akan dibatalkan'
        });
        return;
    }

    Swal.fire({
        title: 'Konfirmasi Pembatalan',
        text: `Anda yakin ingin membatalkan ${checkedRows.size} transaksi yang dipilih?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Tidak',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= base_url('cms/tax-generate/batal-generate') ?>',
                type: 'POST',
                data: {
                    kode_trx: Array.from(checkedRows),
                    sumber_data: $('#sumber_data').val()
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message
                        }).then(() => {
                            checkedRows.clear();
                            loadTable();
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
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>