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

    .card-header {
        background: var(--primary-color) !important;
    }

    .btn-primary {
        background: var(--primary-color) !important;
    }

    .btn-primary:hover {
        background: var(--primary-hover) !important;
    }

</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Form Laporan Jurnal</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="mb-3" autocomplete="off">
                <div class="row align-items-end">
                    <!-- Dropdown Sumber -->
                    <div class="col-md-2">
                        <label for="dbs" class="form-label">Sumber Data</label>
                        <select class="form-control" name="dbs" id="dbs" required>
                             <?php 
                            $dbList = getSelDb();
                            foreach ($dbList as $key => $name): ?>
                                <option value="<?= $key ?>"><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="bulan" class="form-label">Bulan</label>
                        <select class="form-control" name="bulan" id="bulan">
                            <?php foreach ($bln as $key => $value): ?>
                                <option value="<?= $key ?>" <?= ($key == $blnSel) ? "selected" : "" ?>><?= $value ?></option>
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
    const form = document.getElementById("filterForm");
    const filterButton = form.querySelector("button[type='submit']");
    const resetButton = document.getElementById("resetBtn");
    const resultContainer = document.getElementById("resultContainer");

    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        // Tampilkan loading di tombol Filter
        filterButton.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Memproses...`;
        filterButton.disabled = true;
        resetButton.disabled = true;

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
            const response = await fetch("<?= base_url('cms/report/jurnal-filter') ?>", {
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
        resetButton.disabled = false;
    });

    // Tombol Reset
    resetButton.addEventListener("click", function () {
        form.reset();
        resultContainer.innerHTML = "";
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
