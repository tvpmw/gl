<?= $this->extend('layouts/admin') ?>
<?= $this->section('styles') ?>
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
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <ul class="nav nav-pills mb-2">
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('cms/dashboard') ?>">Laba/Rugi</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="javascript:void(0);">Neraca</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= base_url('cms/dashboard/coa') ?>">Chart of Accounts</a>
        </li>
    </ul>

    <div class="row mb-3">
        <div class="col">
            <div class="card card-body">
                <form id="form-filter">
                    <div class="row">
		              <!-- Dropdown Sumber -->
		              <div class="col-3">
		                <div class="form-group">
		                  <select class="form-control" name="dbs" id="dbs" style="width: 100%">
		                    <?php foreach ($dbs as $key => $row): ?>
		                      <option value="<?=$row?>"><?=$row?></option>
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

	<!-- Kartu Neraca -->
	<div class="row">
	    <div class="col-md-3">
	        <div class="card p-3" style="border-left: 5px solid #0d6efd;">
	            <h5 class="text-primary">Total Aset</h5>
	            <h3 id="total-aset" class="loading">Loading...</h3>
	        </div>
	    </div>
	    <div class="col-md-3">
	        <div class="card p-3" style="border-left: 5px solid #dc3545;">
	            <h5 class="text-danger">Total Liabilitas</h5>
	            <h3 id="total-liabilitas" class="loading">Loading...</h3>
	        </div>
	    </div>
	    <div class="col-md-3">
	        <div class="card p-3" style="border-left: 5px solid #28a745;">
	            <h5 class="text-success">Total Ekuitas</h5>
	            <h3 id="total-ekuitas" class="loading">Loading...</h3>
	        </div>
	    </div>
	    <div class="col-md-3">
	        <div class="card p-3" style="border-left: 5px solid #ffc107;">
	            <h5 class="text-warning">Balance</h5>
	            <h3 id="total-balance" class="loading">Loading...</h3>
	        </div>
	    </div>
	</div>

	<!-- Chart -->
	<div class="row mt-4">
	    <div class="col-md-12">
	        <div class="card p-3">
	            <h5>Chart Tren Neraca</h5>
	            <div class="loading" id="chart-loading" style="height: 300px;"></div>
	            <canvas id="chartNeraca" style="display: none;"></canvas>
	        </div>
	    </div>
	</div>

	<!-- Detail Neraca -->
    <div class="card border-0 shadow-sm mt-4 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0">Laporan Neraca <span id="thSet"></span></h5>
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
                            <th class="text-end">Aset (Rp)</th>
                            <th class="text-end">Liabilitas (Rp)</th>
                            <th class="text-end">Ekuitas (Rp)</th>
                            <th class="text-end">Laba/Rugi Tahun (Rp)</th>
                            <th class="text-end">Ekuitas + Laba (Rp)</th>
                            <th class="text-end">Balance</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="detail-neraca-body">
                        <tr class="loading"><td colspan="8">Loading data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const formFilter = document.getElementById("form-filter");
    const chartCanvas = document.getElementById("chartNeraca");
    const chartLoading = document.getElementById("chart-loading");

    const totalAset = document.getElementById("total-aset");
    const totalLiabilitas = document.getElementById("total-liabilitas");
    const totalEkuitas = document.getElementById("total-ekuitas");
    const totalBalance = document.getElementById("total-balance");

    document.getElementById("loadingTable").style.display = "block";

    let chartNeraca;

    async function fetchData() {
        const tahun = document.getElementById("tahun").value;
        const dbs = document.getElementById("dbs").value;

        // Tambahkan efek loading
        [totalAset, totalLiabilitas, totalEkuitas, totalBalance].forEach(el => {
            el.classList.add("loading");
            el.textContent = "Loading...";
        });

        chartCanvas.style.display = "none";
        chartLoading.style.display = "block";

        const params = { tahun, dbs, req: "neraca" };

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
            const neracaData = data.data;

            // Hapus efek loading setelah data berhasil dimuat
            [totalAset, totalLiabilitas, totalEkuitas, totalBalance].forEach(el => {
                el.classList.remove("loading");
            });

            chartCanvas.style.display = "block";
            chartLoading.style.display = "none";

            if (neracaData.length > 0) {
                totalAset.textContent = toRupiah(neracaData[0].aset);
                totalLiabilitas.textContent = toRupiah(neracaData[0].liabilitas);
                totalEkuitas.textContent = toRupiah(neracaData[0].ekuitaslaba);
                totalBalance.textContent = neracaData[0].balance.toFixed(2);
            } else {
                totalAset.textContent = "Rp 0";
                totalLiabilitas.textContent = "Rp 0";
                totalEkuitas.textContent = "Rp 0";
                totalBalance.textContent = "0";
            }

            document.getElementById("loadingTable").style.display = "none";

            updateChart(neracaData);
            updateTable(neracaData);

            document.getElementById("thSet").innerHTML="Tahun "+tahun;
        } catch (error) {
            console.error("Error fetching data:", error);
        }
    }

    function updateChart(neracaData) {
        if (chartNeraca) {
            chartNeraca.destroy();
        }

        const labels = neracaData.map(item => item.bulan);
        const asetData = neracaData.map(item => item.aset);
        const liabilitasData = neracaData.map(item => item.liabilitas);
        const ekuitasData = neracaData.map(item => item.ekuitaslaba);

        chartNeraca = new Chart(chartCanvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Total Aset', data: asetData, borderColor: '#007bff', fill: false },
                    { label: 'Total Liabilitas', data: liabilitasData, borderColor: '#dc3545', fill: false },
                    { label: 'Total Ekuitas', data: ekuitasData, borderColor: '#28a745', fill: false }
                ]
            }
        });
    }

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
                        <i class="fas fa-arrow-up"></i> 0.0%
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

    function updateTable(neracaData) {
        const tableBody = document.getElementById("detail-neraca-body");
        tableBody.innerHTML = "";

        neracaData.forEach((item, index) => {
            const previousMonth = neracaData[index + 1];
            
            const row = `<tr>
                <td>${item.bulan}</td>
                <td class="text-end">
                    ${toRupiah(item.aset)}
                    ${getTrendHtml(item.aset, previousMonth?.aset, 'Aset')}
                </td>
                <td class="text-end">
                    ${toRupiah(item.liabilitas)}
                    ${getTrendHtml(item.liabilitas, previousMonth?.liabilitas, 'Liabilitas')}
                </td>
                <td class="text-end">
                    ${toRupiah(item.ekuitas)}
                    ${getTrendHtml(item.ekuitas, previousMonth?.ekuitas, 'Ekuitas')}
                </td>
                <td class="text-end">
                    ${toRupiah(item.labarugi_tahun)}
                    ${getTrendHtml(item.labarugi_tahun, previousMonth?.labarugi_tahun, 'Laba/Rugi')}
                </td>
                <td class="text-end">
                    ${toRupiah(item.ekuitaslaba)}
                    ${getTrendHtml(item.ekuitaslaba, previousMonth?.ekuitaslaba, 'Ekuitas + Laba')}
                </td>
                <td class="text-end">
                    ${item.balance.toFixed(2)}
                    ${getTrendHtml(item.balance, previousMonth?.balance, 'Balance')}
                </td>
                <td>${item.aksi}</td>
            </tr>`;
            tableBody.innerHTML += row;
        });
    }

    formFilter.addEventListener("submit", function (e) {
        e.preventDefault();
        fetchData();
    });

    fetchData();

	function toNumber(value) {
	    return Number(value.toString().replace(/[^0-9,]+/g, "").replace(/[,]+/g, "."));
	}

	const formatter = new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 });
	function toRupiah(str, withSymbol = true) {
	    return withSymbol ? formatter.format(str) : (formatter.format(str)).replace(/(Rp)/, "").trim();
	}

	$(document).on('click', ".detailNR", function(event) {
	  event.preventDefault();
	  let id = $(this).data('id');
	  window.location.replace('<?=base_url('cms/report/neraca')?>/'+id);
	});
});
</script>
<?= $this->endSection() ?>