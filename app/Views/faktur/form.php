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
                    <button type="button" id="btnTidakDibuat" class="btn btn-warning">
                        <i class="fas fa-ban me-2"></i>Tidak Dibuatkan
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
            { data: 9, orderable: false, className: 'text-center' } // Aksi
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
        alert('Pilih transaksi yang akan ditandai tidak dibuatkan');
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

    if (confirm('Apakah Anda yakin akan menandai ' + selectedRows.length + ' transaksi sebagai tidak dibuatkan?')) {
        $.ajax({
            url: '<?= base_url('cms/faktur/tidak-dibuat') ?>',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ data: selectedRows }),
            success: function(response) {
                if (response.success) {
                    alert('Data berhasil disimpan/update!');
                    $('#dataTrx').DataTable().ajax.reload();
                } else {
                    alert('Gagal menyimpan data: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Terjadi kesalahan: ' + error);
            }
        });
    }
});

// Reset checkedRows when form is submitted
$("#form-filter").submit(function (e) {
    e.preventDefault();
    checkedRows.clear();
    $('#result-table').show();
    loadTable();
});

</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>