<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<style>
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
            <h5 class="mb-0">Form Laporan Laba Rugi</h5>
        </div>
        <div class="card-body">
            <form id="form-filter" class="mb-3" autocomplete="off">
                <div class="row">
	              <!-- Dropdown Sumber -->
	              <div class="col-2">
	                <div class="form-group">
	                  <select class="form-control" name="dbs" id="dbs" style="width: 100%">
	                    <?php foreach ($dbs as $key => $row): ?>
	                      <option value="<?=$row?>" <?=($row == $dbSel) ? "selected" : ""?>><?=$row?></option>
	                    <?php endforeach; ?>
	                  </select>
	                </div>
	              </div>
	              <!-- Dropdown Bulan -->
	              <div class="col-3">
	                <div class="form-group">
	                  <select class="form-control select2" name="bulan" id="bulan" style="width: 100%">
	                    <?php foreach ($bln as $key => $value): ?>
	                      <option value="<?=$key?>" <?=($key == $blnSel) ? "selected" : ""?>><?=$value?></option>
	                    <?php endforeach; ?>
	                  </select>
	                </div>
	              </div>
	              <!-- Dropdown Tahun -->
	              <div class="col-2">
	                <div class="form-group">
	                  <select class="form-control select2" name="tahun" id="tahun" style="width: 100%">
	                    <?php for ($i = date('Y'); $i >= $startYear; $i--): ?>
	                      <option value="<?=$i?>" <?=($i == $thnSel) ? "selected" : ""?>><?=$i?></option>
	                    <?php endfor; ?>
	                  </select>
	                </div>
	              </div>

	              <div class="col-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
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
$("#form-filter").submit(function(event) {
    event.preventDefault();  // Mencegah form dari reload halaman

    const formData = $("#form-filter").serializeArray();
    const params = {};

    formData.forEach(({ name, value }) => {
        if (value.trim() !== "") { // Pastikan input tidak kosong
            params[name] = value;
        }
    });

	let id = params['tahun']+'/'+params['bulan']+'/'+params['dbs'];      
	window.location.replace('<?=base_url('cms/report/labarugi')?>/'+id);
});

$(document).on('click', ".btn-back", function(event) {
  event.preventDefault();
  window.location.replace('<?=base_url('cms/dashboard')?>');
});
</script>

<?= $this->endSection() ?>