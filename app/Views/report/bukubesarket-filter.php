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
            <h5 class="mb-0">Form Filter Buku Besar Keterangan</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="mb-3" autocomplete="off">
                <div class="row align-items-end">
                    <!-- Dropdown Sumber -->
                    <div class="col-md-2">
                        <label for="dbs" class="form-label">Sumber Data</label>
                        <select class="form-control" name="dbs" id="dbs" required>
                            <?php foreach ($dbs as $key => $row): ?>
                                <option value="<?= $row ?>"><?= $row ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Dropdown Tahun -->
                    <div class="col-md-2">
                        <label for="tahun" class="form-label">Tahun</label>
                        <select class="form-control" name="tahun" id="tahun">
                            <?php for ($i = date('Y'); $i >= $startYear; $i--): ?>
                                <option value="<?=$i?>" <?=($i == $thnSkg) ? "selected" : ""?>><?=$i?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Input Keterangan -->
                    <div class="col-md-4">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <input type="text" class="form-control" name="keterangan" id="keterangan" placeholder="Cari keterangan..." required>
                    </div>

                    <!-- Tombol Filter & Reset -->
                    <div class="col-md-4 d-flex">
                        <button type="submit" class="btn btn-primary w-50 me-2">Filter</button>
                        <button type="button" id="resetBtn" class="btn btn-secondary w-50">Reset</button>
                    </div>
                </div>
            </form>
            <hr>
            <div id="resultContainer" class="mt-3"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("filterForm");
    const filterButton = form.querySelector("button[type='submit']");
    const resetButton = document.getElementById("resetBtn");
    const resultContainer = document.getElementById("resultContainer");

    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        // Tampilkan loading di tombol Filter
        filterButton.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Memproses...`;
        filterButton.disabled = true;

        // Tampilkan loading di resultContainer
        resultContainer.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2">Memproses data, mohon tunggu...</p>
            </div>
        `;

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch("<?= base_url('cms/report/bukubesar-filterket') ?>", {
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

        // Kembalikan tombol seperti semula
        filterButton.innerHTML = "Filter";
        filterButton.disabled = false;
    });

    // Tombol Reset
    resetButton.addEventListener("click", function () {
        form.reset();
        resultContainer.innerHTML = "";
    });
});
</script>
<?= $this->endSection() ?>
