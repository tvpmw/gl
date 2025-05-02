<?= $this->extend('layouts/admin') ?>

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

            <form action="<?= base_url('cms/faktur/generate') ?>" method="post">
                <div class="row mb-3">
                    <!-- Bulan -->
                    <div class="col-md-4">
                        <label for="bulan" class="form-label">Bulan</label>
                        <select class="form-select" name="bulan" id="bulan" required>
                            <option value="">Pilih Bulan</option>
                            <?php 
                            $bulan_array = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                                4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                                10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ];
                            foreach ($bulan_array as $value => $label) : ?>
                                <option value="<?= $value ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tahun -->
                    <div class="col-md-4">
                        <label for="tahun" class="form-label">Tahun</label>
                        <select class="form-select" name="tahun" id="tahun" required>
                            <option value="">Pilih Tahun</option>
                            <?php 
                            $tahun_sekarang = date('Y');
                            for ($tahun = $tahun_sekarang; $tahun >= $tahun_sekarang - 5; $tahun--) : ?>
                                <option value="<?= $tahun ?>"><?= $tahun ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Sales Type -->
                    <div class="col-md-4">
                        <label for="sales_type" class="form-label">Tipe Sales</label>
                        <select class="form-select" name="sales_type" id="sales_type" required>
                            <option value="">Pilih Tipe Sales</option>
                            <option value="DISTRI">DISTRI</option>
                            <option value="ONLINE">ONLINE</option>
                        </select>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file me-2"></i>Generate Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <?php if(isset($bulan) && isset($tahun) && isset($sales_type)): ?>
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center text-white">
                <div>
                    <i class="fas fa-table me-1"></i>
                    Data Transaksi: <?= $bulan_array[$bulan] ?? '' ?> <?= $tahun ?> - <?= $sales_type ?>
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
                            <th>Kode Customer</th>
                            <th>Nama Customer</th>
                            <th>Kode Sales</th>
                            <th>Nama Sales</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    <?php if(isset($bulan) && isset($tahun) && isset($sales_type)): ?>
    $('#dataTrx').DataTable({
        "responsive": true,
        "processing": true,
        "language": {
            "processing": "Memproses...",
            "lengthMenu": "Tampilkan _MENU_ data per halaman",
            "zeroRecords": "Data tidak ditemukan",
            "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
            "infoEmpty": "Tidak ada data yang tersedia",
            "infoFiltered": "(difilter dari _MAX_ total data)",
            "search": "Cari:",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        },
        "columns": [
            { "data": null, "orderable": false },
            { "data": "kode_trx" },
            { "data": "tanggal" },
            { "data": "grand_total" },
            { "data": "kode_customer" },
            { "data": "nama_customer" },
            { "data": "kode_sales" },
            { "data": "nama_sales" },
            { 
                "data": null,
                "orderable": false,
                "searchable": false,
                "render": function(data, type, row) {
                    return '<button class="btn btn-success btn-sm excel-btn" data-id="'+ row.kode_trx +'"><i class="fas fa-file-excel"></i> Generate Excel</button>';
                }
            }
        ],
        "order": [[ 1, "desc" ]],
        "columnDefs": [{
            "targets": 0,
            "render": function (data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }
        }]
    });
    <?php endif; ?>
});
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>