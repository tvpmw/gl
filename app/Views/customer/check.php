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
        <a class="nav-link active" aria-current="page" href="javascript:void(0);">Customer Check Online</a>        
      </li>
    </ul>    
    <h3 class="mt-4">Cek Data Customer</h3>
    
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
                    Data Customer
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="dataCustomer" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Kode Customer</th>
                            <th>Nama Customer</th>
                            <th>NPWP</th>
                            <th>Wilayah</th>
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
let dataTable;

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

    dataTable = $('#dataCustomer').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "<?= base_url('cms/customer/get-data') ?>",
            type: "POST",
            data: function(d) {
                return {
                    sumber_data: $('#sumber_data').val()
                };
            }
        },
        columns: [
            { data: 'kdcust' },
            { data: 'nmcust' },
            { data: 'npwp' },
            { data: 'wil' }    
        ]
    });
}
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>