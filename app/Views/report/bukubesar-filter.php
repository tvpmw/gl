<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<style>
	#rekeningList {
		position: absolute;
		z-index: 1000;
		width: 100%;
		background: white;
		border: 1px solid #ccc;
		border-radius: 5px;
		max-height: 200px;
		overflow-y: auto;
		display: none;
	}

	#rekeningList .list-group-item {
		cursor: pointer;
	}

	#rekeningList .list-group-item:hover {
		background: #f8f9fa;
	}

	/* Pastikan posisi kalender sesuai dengan input */
	input[type="date"] {
	    position: relative;
	    z-index: 10;
	}

	/* Atasi bug date picker yang muncul di tanggal awal */
	input[type="date"]:focus {
	    z-index: 20;
	}

	.dateshow::-webkit-calendar-picker-indicator {
		background: transparent;
		bottom: 0;
		color: transparent;
		cursor: pointer;
		height: auto;
		left: 0;
		position: absolute;
		right: 0;
		top: 0;
		width: auto;
	}  

</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Form Filter Buku Besar Rekening</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="mb-3" autocomplete="off">
                <div class="row">
                    <!-- Dropdown Sumber -->
                    <div class="col-md-2">
                        <label for="dbs" class="form-label">Sumber Data</label>
                        <select class="form-control" name="dbs" id="dbs" style="width: 100%" required>
                            <?php foreach ($dbs as $key => $row): ?>
                                <option value="<?= $row ?>"><?= $row ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tanggal Awal -->
                    <div class="col-md-3">
                        <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                        <input type="date" class="form-control dateshow" id="tanggal_awal" name="tanggal_awal" required>
                    </div>

                    <!-- Tanggal Akhir -->
                    <div class="col-md-3">
                        <label for="tanggal_akhir" class="form-label dateshow">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" required>
                    </div>

                    <!-- Rekening -->
                    <div class="col-md-4 position-relative">
                        <label for="rekening" class="form-label">Rekening</label>
                        <input type="text" class="form-control" name="rekening" id="rekening" placeholder="Cari rekening..." required>
                        <input type="hidden" id="rekening_id" name="rekening_id" required>
                        <div class="list-group mt-1 position-absolute w-100" id="rekeningList"></div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <button type="reset" class="btn btn-secondary d-none" id="resetBtn">Reset</button>
                </div>
            </form>
            <hr>
            <div id="resultContainer" class="mt-3"></div>
        </div>
    </div>
</div>

<!-- Modal Detail Jurnal -->
<div class="modal fade" id="modal_detail" tabindex="-1" aria-labelledby="detailJurnalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white text-white d-flex align-items-center">
                <h5 class="modal-title" id="detailJurnalModalLabel">Detail Jurnal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-body p-1" id="content_detail"></div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    let typingTimer;
    const debounceTime = 300;
    let validRekening = false;

    async function searchRekening(query) {
        const rekeningList = document.getElementById("rekeningList");
        rekeningList.innerHTML = "";

        if (query.length < 2) {
            rekeningList.innerHTML = '<div class="list-group-item text-muted">Ketik minimal 2 karakter...</div>';
            rekeningList.style.display = "block";
            return;
        }

        const dbs = document.getElementById("dbs").value;
        if (!dbs) {
            console.warn("Sumber data (dbs) belum dipilih.");
            return;
        }

        try {
            const response = await fetch(`<?= base_url('cms/report/search-rekening') ?>?search=${encodeURIComponent(query)}&dbs=${encodeURIComponent(dbs)}`);
            const data = await response.json();

            rekeningList.innerHTML = "";

            if (data.length > 0) {
                data.forEach(item => {
                    const div = document.createElement("div");
                    div.className = "list-group-item list-group-item-action";
                    div.textContent = item.nama_rekening;
                    div.setAttribute("data-id", item.id);
                    div.onclick = () => {
                        document.getElementById("rekening").value = item.nama_rekening;
                        document.getElementById("rekening_id").value = item.id;
                        validRekening = true;
                        rekeningList.innerHTML = "";
                        rekeningList.style.display = "none";
                    };
                    rekeningList.appendChild(div);
                });
                rekeningList.style.display = "block";
            } else {
                rekeningList.innerHTML = '<div class="list-group-item text-muted">Tidak ditemukan.</div>';
                rekeningList.style.display = "block";
            }
        } catch (error) {
            console.error("Gagal mengambil data rekening:", error);
        }
    }

    const form = document.getElementById("filterForm");
    const filterButton = form.querySelector("button[type='submit']");
    const rekeningInput = document.getElementById("rekening");
    const rekeningIdInput = document.getElementById("rekening_id");
    const dbs = document.getElementById("dbs");
    const rekeningList = document.getElementById("rekeningList");
    const resultContainer = document.getElementById("resultContainer");

    dbs.addEventListener("change", function () {
        rekeningInput.value = "";
        rekeningIdInput.value = "";
        rekeningList.innerHTML = "";
        rekeningList.style.display = "none";
        validRekening = false;
    });

    rekeningInput.addEventListener("input", function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            searchRekening(this.value);
        }, debounceTime);
        validRekening = false;
        rekeningIdInput.value = "";
    });

    rekeningInput.addEventListener("blur", function () {
        setTimeout(() => {
            rekeningList.style.display = "none";
        }, 200);
    });

    document.getElementById("resetBtn").addEventListener("click", function () {
        rekeningInput.value = "";
        rekeningIdInput.value = "";
        rekeningList.innerHTML = "";
        rekeningList.style.display = "none";
        validRekening = false;
        resultContainer.innerHTML = "";
    });

    document.addEventListener("click", function (event) {
        if (!rekeningInput.contains(event.target) && !rekeningList.contains(event.target)) {
            rekeningList.style.display = "none";
        }
    });

    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        if (!validRekening || !document.getElementById("rekening_id").value) {
            alert("Silakan pilih rekening dari daftar yang tersedia!");
            return;
        }

        // Menampilkan loading pada tombol
        filterButton.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Memproses...`;
        filterButton.disabled = true;

        // Menampilkan loading pada resultContainer
        resultContainer.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memproses data, mohon tunggu...</p>
            </div>
        `;

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch("<?= base_url('cms/report/bukubesar-filter') ?>", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data)
            });

            const result = await response.text();
            if (response.ok) {
                resultContainer.innerHTML = result;
            } else {
                resultContainer.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan saat memproses data.</div>`;
            }
        } catch (error) {
            console.error("Gagal mengirim data:", error);
            resultContainer.innerHTML = `<div class="alert alert-danger">Gagal mengirim data ke server!</div>`;
        }

        // Mengembalikan tombol seperti semula setelah request selesai
        filterButton.innerHTML = "Filter";
        filterButton.disabled = false;
    });

    const tanggalAwal = document.getElementById("tanggal_awal");
    const tanggalAkhir = document.getElementById("tanggal_akhir");

    tanggalAwal.addEventListener("change", function () {
        tanggalAkhir.min = tanggalAwal.value;
        if (tanggalAkhir.value) {
            tanggalAkhir.value = "";
        }
    });

    tanggalAkhir.addEventListener("change", function () {
        if (tanggalAkhir.value < tanggalAwal.value) {
            alert("Tanggal akhir tidak boleh lebih kecil dari tanggal awal!");
            tanggalAkhir.value = "";
        }
    });

    tanggalAkhir.addEventListener("focus", function () {
        this.showPicker();
    });
});

function detail_data(id)
{
  $.ajax({
    url : "<?= base_url('cms/jurnal/detail') ?>",
    type: "POST",
    data: {id:id},
    success: function(data)
    {
      $('#content_detail').html(data);
      $('#modal_detail').modal('show');
    },
    error: function (jqXHR, textStatus, errorThrown)
    {
      alert('<?=isLang('terjadi_kesalahan')?>');
    }
  });
}

</script>
<?= $this->endSection() ?>
