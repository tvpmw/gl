<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="container mt-4">

        <!-- FORM FILTER -->
        <div class="card card-body mb-3">
            <h2 class="text-center">Jurnal Umum</h2>
            <form id="form-filter">
                <div class="row">
                    <div class="col-2">
                        <select class="form-control" name="dbs" id="dbs">
                            <?php foreach ($dbs as $row): ?>
                                <option value="<?= $row ?>"><?= $row ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-3">
                        <select class="form-control" name="bulan" id="bulan">
                            <?php foreach ($bln as $key => $value): ?>
                                <option value="<?= $key ?>" <?= ($key == $blnSel) ? "selected" : "" ?>><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-2">
                        <select class="form-control" name="tahun" id="tahun">
                            <?php for ($i = date('Y'); $i >= $startYear; $i--): ?>
                                <option value="<?= $i ?>" <?= ($i == $thnSel) ? "selected" : "" ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-eye"></i> Tampilkan</button>
                    </div>
                </div>
            </form>
            <hr class="mb-3">
            <table id="jurnalTable" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Jurnal</th>
                        <th>Keterangan</th>
                        <th>Tanggal</th>
                        <th>Tahun</th>
                        <th>Bulan</th>
                        <th>Total</th>
                        <th>Posting</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        function loadTable() {
            $('#jurnalTable').DataTable({
                "destroy": true,
                "processing": true,
                "serverSide": true,
                "aaSorting": [],
                "ajax": {
                    "url": "<?= base_url('cms/jurnal/get-data') ?>",
                    "type": "POST",
                    "data": function (d) {
                        d.dbs = $('#dbs').val();
                        d.bulan = $('#bulan').val();
                        d.tahun = $('#tahun').val();
                    },
                    "dataSrc": function (json) {
                        // console.log(json);
                        return json.data;
                    }
                },
                "columnDefs": [
                  { 
                    "targets": [ 0, -2, -1 ], 
                    "orderable": false, 
                  },
                  {
                    "targets": [0, 4, 5, -2, -1],
                    "className": 'text-center'
                  },
                  {
                    "targets": [-3],
                    "className": 'text-end'
                  },
                ],
            });
        }

        $(document).ready(function () {
            loadTable();

            $("#form-filter").submit(function (e) {
                e.preventDefault();
                loadTable();
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
