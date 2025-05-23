<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<style>
    .stat-card {
        transition: all 0.3s ease;
        border-radius: 16px;
        overflow: hidden;
        background: linear-gradient(145deg, #ffffff, #f5f7fa);
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
    .trend-badge {
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .chart-card {
        border-radius: 16px;
        background: linear-gradient(145deg, #ffffff, #f5f7fa);
    }
    .btn-filter {
        padding: 6px 16px;
        border-radius: 8px;
        font-weight: 500;
    }
    .table-modern {
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    .table-modern tbody tr {
        background: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        border-radius: 8px;
        transition: all 0.2s;
    }
    .table-modern tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }
    .table-modern td {
        border: none;
        padding: 16px;
    }
    .table-modern td:first-child {
        border-radius: 8px 0 0 8px;
    }
    .table-modern td:last-child {
        border-radius: 0 8px 8px 0;
    }
    .action-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .action-btn:hover {
        background: #f0f2f5;
    }

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
        <a class="nav-link active" aria-current="page" href="javascript:void(0);">Laba/Rugi</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="<?=base_url('cms/dashboard/neraca')?>">Neraca</a>        
      </li>
      <li class="nav-item">
            <a class="nav-link" href="<?= base_url('cms/dashboard/coa') ?>">Chart of Accounts</a>
      </li>
    </ul>    

    <div class="row mb-3">
      <div class="col">
        <div class="card card-body">
          <form action="#" id="form-filter" class="form-horizontal">
            <div class="row">
              <!-- Dropdown Sumber -->
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
              <!-- Dropdown Tahun -->
              <div class="col-3">
                <div class="form-group">
                  <select class="form-control select2" name="tahun" id="tahun" style="width: 100%">
                    <?php for ($i = date('Y'); $i >= $startYear; $i--): ?>
                      <option value="<?=$i?>" <?=($i == $thnSkg) ? "selected" : ""?>><?=$i?></option>
                    <?php endfor; ?>
                  </select>
                </div>
              </div>
              <div class="col-2">
                <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-filter"></i> <?=isLang('filter')?></button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>     

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fas fa-book-open text-primary fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle text-muted mb-1">Total Pendapatan</h6>
                        <h5 id="pendapatan" class="loading card-title mb-1">Loading...</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-balance-scale text-success fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle text-muted mb-1">Total HPP</h6>
                        <h5 id="hpp" class="loading card-title mb-1">Loading...</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="fas fa-balance-scale-right text-info fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle text-muted mb-1">Total Biaya</h6>
                        <h5 id="biaya" class="loading card-title mb-1">Loading...</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="fas fa-clock text-warning fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="card-subtitle text-muted mb-1">Total Laba/Rugi</h6>
                        <h5 id="lr" class="loading card-title mb-1">Loading...</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Pendapatan & HPP per Bulan -->
        <div class="col-md-12 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Pendapatan & HPP per Bulan</h5>
                    <div class="d-none justify-content-end mb-3">
                        <select class="form-select form-select-sm w-auto">
                            <option>Last 30 Days</option>
                            <option>Last Quarter</option>
                            <option>Last Year</option>
                        </select>
                    </div>
                    <canvas id="chartPendapatanBiaya" height="100"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Laba Rugi per Bulan -->
        <div class="col-md-12 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Laba Rugi per Bulan</h5>
                    <div class="d-none justify-content-end mb-3">
                        <select class="form-select form-select-sm w-auto">
                            <option>By Month</option>
                            <option>By Quarter</option>
                            <option>By Year</option>
                        </select>
                    </div>
                    <canvas id="chartLabaRugi" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Journal Entries -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0">Laporan Laba Rugi <span id="thSet"></span></h5>
            </div>
            <div id="loadingTable" class="text-center my-3">
                <div class="spinner-border text-primary" role="status"></div>
                <p>Memuat data...</p>
            </div>
            <div class="table-responsive">
                <table id="journalEntriesTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Bulan</th>
                            <th class="text-end">Pendapatan</th>
                            <th class="text-end">HPP</th>
                            <th class="text-end">Biaya</th>
                            <th class="text-end">Laba/Rugi</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="dataTable"><!-- Data dari JS --></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function loadData(params = {}) {
    document.getElementById("pendapatan").classList.add("loading");
    document.getElementById("hpp").classList.add("loading");
    document.getElementById("biaya").classList.add("loading");
    document.getElementById("lr").classList.add("loading");

    document.getElementById("loadingTable").style.display = "block";
    document.getElementById("dataTable").innerHTML = "";

    params.req = "labarugi";

    fetch(`<?=base_url('cms/dashboard/get-data')?>`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(params)  
    })
    .then(response => response.json())
    .then(data => {
        ["pendapatan", "hpp", "biaya", "lr"].forEach(id => {
            document.getElementById(id).classList.remove("loading");
        });

        document.getElementById("loadingTable").style.display = "none";

        if (!data.data || !Array.isArray(data.data)) {
            throw new Error("Format data tidak valid!");
        }

        let totalPendapatan = 0, totalHpp = 0, totalBiaya = 0, totalLr = 0;
        const labels = [], pendapatanData = [], hppData = [], biayaData = [], labaRugiData = [];

        function calculatePercentageChange(current, previous) {
            if (!previous) return { percent: 0, isIncrease: true };
            const change = ((current - previous) / Math.abs(previous)) * 100;
            return {
                percent: Math.abs(change).toFixed(1),
                isIncrease: change > 0
            };
        }

        function getTrendHtml(current, previous, type = '') {
            if (!previous) 
                return `
                    <span class="ms-2 small text-black" title="Tetap">
                        <i class="fas fa-minus"></i> 0.0%                        
                    </span>
                `;

            const trend = calculatePercentageChange(current, previous);
            return `
                <span class="ms-2 small ${trend.isIncrease ? 'text-success' : 'text-danger'}" 
                      title="${type} ${trend.isIncrease ? 'naik' : 'turun'} ${trend.percent}% dari bulan sebelumnya">
                    <i class="fas fa-arrow-${trend.isIncrease ? 'up' : 'down'}"></i> 
                    ${trend.percent}%
                </span>
            `;
        }

        const tableData = [...data.data]; 
        const sortedChartData = [...data.data].sort((a, b) => a.bln - b.bln);

        sortedChartData.forEach(item => {
            const lr = item.pendapatan - (item.hpp + item.biaya);
            
            labels.push(`${item.bulan} ${data.tahun}`);
            pendapatanData.push(item.pendapatan);
            hppData.push(item.hpp);
            biayaData.push(item.biaya);
            labaRugiData.push(lr);
        });

        const tableRows = tableData.map((item, index) => {
            const previousMonth = tableData[index + 1];
            const lr = item.pendapatan - (item.hpp + item.biaya);

            totalPendapatan += item.pendapatan;
            totalHpp += item.hpp;
            totalBiaya += item.biaya;
            totalLr += lr;

            const prevLr = previousMonth 
                ? (previousMonth.pendapatan - (previousMonth.hpp + previousMonth.biaya)) 
                : null;

            return `
                <tr>
                    <td>${item.bulan}</td>
                    <td class="text-end">
                        ${toRupiah(item.pendapatan)}
                        ${getTrendHtml(item.pendapatan, previousMonth?.pendapatan, 'Pendapatan')}
                    </td>
                    <td class="text-end">
                        ${toRupiah(item.hpp)}
                        ${getTrendHtml(item.hpp, previousMonth?.hpp, 'HPP')}
                    </td>
                    <td class="text-end">
                        ${toRupiah(item.biaya)}
                        ${getTrendHtml(item.biaya, previousMonth?.biaya, 'Biaya')}
                    </td>
                    <td class="text-end">
                        ${toRupiah(lr)}
                        ${getTrendHtml(lr, prevLr, 'Laba/Rugi')}
                    </td>
                    <td class="text-center">
                        <span class="badge ${item.posting !== '0' ? 'bg-success' : 'bg-danger'}">
                            ${item.posting !== '0' ? 'Posting' : 'Berjalan'}
                        </span>
                    </td>                                        
                    <td class="text-center">${item.aksi}</td>
                </tr>
            `;
        }).join("");

        // Footer dengan total
        const tableFooter = `
            <tfoot>
                <tr class="fw-bold bg-light">
                    <td class="text-center">Total</td>
                    <td class="text-end">${toRupiah(totalPendapatan)}</td>
                    <td class="text-end">${toRupiah(totalHpp)}</td>
                    <td class="text-end">${toRupiah(totalBiaya)}</td>
                    <td class="text-end">${toRupiah(totalLr)}</td>
                    <td></td>
                </tr>
            </tfoot>
        `;

        document.getElementById("pendapatan").textContent = `${toRupiah(totalPendapatan)}`;
        document.getElementById("hpp").textContent = `${toRupiah(totalHpp)}`;
        document.getElementById("biaya").textContent = `${toRupiah(totalBiaya)}`;
        document.getElementById("lr").textContent = `${toRupiah(totalLr)}`;

        document.getElementById("dataTable").innerHTML = tableRows + tableFooter;

        updateChart(chartPendapatanBiaya, labels, ["Pendapatan", "HPP"], [pendapatanData, hppData]);
        updateChart(chartLabaRugi, labels, ["Laba Rugi"], [labaRugiData]);

        document.getElementById("thSet").innerHTML = "Tahun " + params.tahun;
    })
    .catch(error => {
        console.error("Error loading data:", error);
        alert("Gagal memuat data!");
        document.getElementById("loadingTable").style.display = "none";
    });
}

function updateChart(chart, labels, datasetsLabels, datasetsData) {
    chart.data.labels = labels;

    chart.data.datasets = datasetsLabels.map((label, i) => ({
        label,
        data: datasetsData[i],
        backgroundColor: i === 0 ? 'blue' : 'red',
        borderColor: i === 0 ? 'blue' : 'red',
        borderWidth: 1,
        fill: false
    }));
    chart.update();
}

$("#form-filter").submit(function(event) {
    event.preventDefault();  // Mencegah form dari reload halaman

    const formData = $("#form-filter").serializeArray();
    const params = {};

    formData.forEach(({ name, value }) => {
        if (value.trim() !== "") { // Pastikan input tidak kosong
            params[name] = value;
        }
    });

    loadData(params);
});

const ctx1 = document.getElementById("chartPendapatanBiaya").getContext("2d");
const ctx2 = document.getElementById("chartLabaRugi").getContext("2d");
const chartPendapatanBiaya = new Chart(ctx1, { type: "bar", data: { labels: [], datasets: [] } });
const chartLabaRugi = new Chart(ctx2, { type: "line", data: { labels: [], datasets: [] } });

loadData({ tahun:'<?=$thnSkg?>',dbs:'sdkom' });

function toNumber(value) {
    return Number(value.toString().replace(/[^0-9,]+/g, "").replace(/[,]+/g, "."));
}

const formatter = new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 });
function toRupiah(str, withSymbol = true) {
    return withSymbol ? formatter.format(str) : (formatter.format(str)).replace(/(Rp)/, "").trim();
}

$(document).on('click', ".detailLR", function(event) {
  event.preventDefault();
  let id = $(this).data('id');      
  window.location.replace('<?=base_url('cms/report/labarugi')?>/'+id);
});

</script>
<?= $this->endSection() ?>