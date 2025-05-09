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
        <a class="nav-link" href="<?=base_url('cms/tax-generate')?>">Tax Generate</a>
      </li>
      <li class="nav-item">
        <a class="nav-link active" aria-current="page" href="javascript:void(0);">Retur</a>        
      </li>
    </ul>    
    <h3 class="mt-4">Cek Data Retur</h3>
    
    <!-- Form Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="form-filter">
                <div class="row mb-3 align-items-end">
                    <!-- Sumber Data -->
                    <div class="col-md-4">
                        <label for="sumber_data" class="form-label">Sumber Data</label>
                        <select class="form-select" name="sumber_data" id="sumber_data" required>
                            <?php foreach ($dbs as $row): ?>
                                <option value="<?= $row ?>"><?= $row ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Filter Data
                        </button>
                    </div>
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
                    <button type="button" data-id="Diedit" class="btn btn-warning btnSudahLapor">
                        <i class="fas fa-undo me-2"></i>Sudah Dilaporkan (Diedit)
                    </button>
                    <button type="button" data-id="Penggantian" class="btn btn-danger btnSudahLapor">
                        <i class="fas fa-undo me-2"></i>Sudah Dilaporkan (Penggantian)
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTaxRetur" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all" class="form-check-input">
                            </th>
                            <th>Tanggal Trx</th>
                            <th>Kode Trx</th>
                            <th>Nama Barang</th>
                            <th>Qty Awal</th>
                            <th>Qty Retur</th>
                            <th>Qty Stlh Retur</th>
                            <th>Kode Retur</th>
                            <th>Tanggal Retur</th>
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


<?= $this->section('scripts') ?>
<script>

let dataTable;
let detailTable;
let detailModal;
let coretaxTable; // Declare coretaxTable in the wider scope
let coretaxModal;
let checkedRows = new Set();

$(document).ready(function() {
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

    dataTable = $('#dataTaxRetur').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "<?= base_url('cms/tax-retur/get-data') ?>",
            type: "POST",
            data: function(d) {
                return {
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
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5 },
            { data: 6 },
            { data: 7 },
            { data: 8 },
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
        url: '<?= base_url('cms/tax-retur/get-detail') ?>',
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

function formatNumber(number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(number);
}
// Add batch cancellation handler
$('.btnSudahLapor').on('click', function() {
    if (checkedRows.size === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Pilih transaksi terlebih dahulu'
        });
        return;
    }

    Swal.fire({
        title: 'Konfirmasi!!!',
        text: `Anda yakin ingin ${checkedRows.size} transaksi yang dipilih?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Tidak',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            var catatan = $(this).data('id');
            $.ajax({
                url: '<?= base_url('cms/tax-retur/sudah-lapor') ?>',
                type: 'POST',
                data: {
                    kode_trx: Array.from(checkedRows),
                    sumber_data: $('#sumber_data').val(),
                    catatan: catatan
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