<?= $this->extend('layouts/admin') ?>
<?= $this->section('styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Cek Data Tax Generate</h1>
    
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
            <i class="fas fa-table me-1"></i>
            Data Tax Generate
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTaxGenerate" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Kode Transaksi</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Total Tax</th>
                            <th>Current Total</th>
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
let dataTable;
let detailTable;
let detailModal;

$(document).ready(function() {
    detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
    
    // Initialize detail table
    detailTable = $('#detailTable').DataTable({
        processing: true,
        searching: false,
        paging: false,
        info: false
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
            { data: 0 }, // Kode Transaksi
            { data: 1 }, // Tanggal
            { data: 2 }, // Jam
            { data: 3 }, // Total Tax
            { data: 4 }, // Current Total
            { data: 5 }, // Status
            { data: 6 }  // Aksi
        ]
    });
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
                response.data.forEach(function(item) {
                    detailTable.row.add([
                        item.kode_brg,
                        item.nama_brg,
                        item.satuan,
                        item.qty,
                        formatNumber(item.hrg),
                        item.diskon + '%',
                        formatNumber(item.dpp),
                        formatNumber(item.dpp_lain),
                        formatNumber(item.nominal_ppn)
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

function formatNumber(number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(number);
}
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>