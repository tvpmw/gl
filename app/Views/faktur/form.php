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
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTrx" class="table table-striped table-bordered">
                    <thead>
                        <tr>
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

$(document).ready(function() {
    $("#form-filter").submit(function (e) {
        e.preventDefault();
        $('#result-table').show();
        loadTable();
    });
});

function loadTable() {
    $('#dataTrx').DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
        "aaSorting": [],
        "ajax": {
            "url": "<?= base_url('cms/faktur/get-data') ?>",
            "type": "POST",
            "data": function (d) {
                d.startDate = $('#startDate').val();
                d.endDate = $('#endDate').val();
                d.sales_type = $('#sales_type').val();
                d.sumber_data = $('#sumber_data').val();
            },
            "dataSrc": function (json) {
                return json.data;
            }
        },
        "columnDefs": [
          { 
            "targets": [ 0, -1 ], 
            "orderable": false, 
          },
          {
            "targets": [0, -1],
            "className": 'text-center'
          },
          {
            "targets": [3],
            "className": 'text-end'
          },
        ],
    });
}

</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>