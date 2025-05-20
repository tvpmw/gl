<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    .loading {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite linear;
        color: transparent !important;
        border-radius: 5px;
    }
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    canvas {
        max-height: 300px !important;
    }
    .card-header {
        background: var(--primary-color) !important;
    }

    .btn-primary {
        background: var(--primary-color) !important;
    }

    .btn-primary:hover {
        background: var(--primary-hover) !important;
    }

    .nav-link.active{
        background: var(--primary-color) !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <ul class="nav nav-pills mb-2">
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('cms/dashboard') ?>">Laba/Rugi</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('cms/dashboard/neraca') ?>">Neraca</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="javascript:void(0);">Chart of Accounts</a>
        </li>
    </ul>

    <div class="row mb-3">
        <div class="col">
            <div class="card card-body">
                <form id="form-filter">
                    <div class="row">
                        <div class="col-3">
                            <div class="form-group">
                                <select class="form-control" name="dbs" id="dbs" style="width: 100%">
                                    <?php 
                                    $dbList = getSelDb();
                                    foreach ($dbList as $key => $name): ?>
                                        <option value="<?= $key ?>"><?= $name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-3">
                            <select class="form-control" name="tahun" id="tahun">
                                <?php for ($i = date('Y'); $i >= $startYear; $i--): ?>
                                    <option value="<?= $i ?>" <?= ($i == $thnSkg) ? "selected" : "" ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-2">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-4">
            <div class="card p-3" style="border-left: 5px solid #0d6efd;">
                <h5 class="text-primary">Total Akun</h5>
                <h3 id="total-akun" class="loading">Loading...</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3" style="border-left: 5px solid #dc3545;">
                <h5 class="text-danger">Akun Aktif</h5>
                <h3 id="total-aktif" class="loading">Loading...</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3" style="border-left: 5px solid #28a745;">
                <h5 class="text-success">Akun Non-Aktif</h5>
                <h3 id="total-nonaktif" class="loading">Loading...</h3>
            </div>
        </div>        
    </div>

    <!-- Detail COA -->
    <div class="card border-0 shadow-sm mt-4 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0">Chart of Accounts <span id="thSet"></span></h5>
            </div>
            <div id="loadingTable" class="text-center my-3">
                <div class="spinner-border text-primary" role="status"></div>
                <p>Memuat data...</p>
            </div>
            <div class="table-responsive">
            <table id="coaTable" class="table table-hover table-striped align-middle w-100">
                <thead class="table-light">
                    <tr>
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Kategori</th>
                        <th>Level</th>
                        <th>Status</th>                            
                        <!-- <th>Aksi</th> -->
                    </tr>
                </thead>
                <tbody id="coa-body">
                    <tr class="loading"><td colspan="5">Loading data...</td></tr>
                </tbody>
            </table>
        </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Add DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const formFilter = document.getElementById("form-filter");        

    const totalAkun = document.getElementById("total-akun");
    const totalAktif = document.getElementById("total-aktif");
    const totalNonaktif = document.getElementById("total-nonaktif");    

    document.getElementById("loadingTable").style.display = "block";    

    async function fetchData() {
        const tahun = document.getElementById("tahun").value;
        const dbs = document.getElementById("dbs").value;

        // Add loading effects
        [totalAkun, totalAktif, totalNonaktif].forEach(el => {
            el.classList.add("loading");
            el.textContent = "Loading...";
        });        

        const params = { tahun, dbs, req: "coa" };

        try {
            const response = await fetch(`<?= base_url('cms/dashboard/get-data') ?>`, {            
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify(params)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const data = await response.json();
            const coaData = data.data;

            // Remove loading effects
            [totalAkun, totalAktif, totalNonaktif].forEach(el => {
                el.classList.remove("loading");
            });    

            // Update summary cards
            if (coaData.length > 0) {
                const stats = calculateStats(coaData);
                totalAkun.textContent = stats.totalAkun;
                totalAktif.textContent = stats.aktif;
                totalNonaktif.textContent = stats.nonAktif;            
            } else {
                totalAkun.textContent = "0";
                totalAktif.textContent = "0";
                totalNonaktif.textContent = "0";                
            }

            document.getElementById("loadingTable").style.display = "none";
            document.getElementById("thSet").innerHTML = "Tahun " + tahun;

            updateTable(coaData);

        } catch (error) {
            console.error("Error fetching data:", error);
        }
    }

    function calculateStats(data) {
        return {
            totalAkun: data.length,
            aktif: data.filter(item => item.nilai !== 0).length,
            nonAktif: data.filter(item => item.nilai === 0).length,            
        };
    }        

    let coaTable;

    function updateTable(coaData) {
        const tableBody = document.getElementById("coa-body");
        
        // Destroy existing DataTable if it exists
        if (coaTable) {
            coaTable.destroy();
        }
        
        tableBody.innerHTML = "";

        coaData.forEach((item) => {
            const row = `
                <tr>
                    <td>${item.kode_akun}</td>
                    <td>${item.nama_akun}</td>
                    <td>${item.kategori}</td>        
                    <td>${item.level}</td>           
                    <td>
                        <span class="badge ${item.nilai !== 0 ? 'bg-success' : 'bg-danger'}">
                            ${item.nilai !== 0 ? 'Aktif' : 'Non-Aktif'}
                        </span>
                    </td>                                        
                </tr>
            `;
            tableBody.innerHTML += row;
        });

        // Initialize DataTable
        coaTable = $('#coaTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
            order: [[0, 'asc']], // Sort by kode_akun ascending
            responsive: true,
            columnDefs: [
                {
                    targets: -1, // Last column (Aksi)
                    orderable: false,
                    searchable: false
                }
            ]
        });
    }

    formFilter.addEventListener("submit", function (e) {
        e.preventDefault();
        fetchData();
    });

    // Initial load
    fetchData();

    // Helper function for formatting currency
    function toRupiah(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(number);
    }

    // Helper function for calculating percentage change
    function calculatePercentageChange(current, previous) {
        if (!previous) return { percent: 0, isIncrease: true };
        const change = ((current - previous) / Math.abs(previous)) * 100;
        return {
            percent: Math.abs(change).toFixed(1),
            isIncrease: change > 0
        };
    }

    $(document).on('click', ".detailCOA", function(event) {
	  event.preventDefault();
	  let id = $(this).data('id');
	  window.location.replace('<?=base_url('cms/report/coa')?>/'+id);
	});
});
</script>
<?= $this->endSection() ?>