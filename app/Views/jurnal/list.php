<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<style>
    .bg-primary {
        --bs-bg-opacity: 1;
        background-color: #4f46e5 !important;
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
    .card {
        border-radius: 12px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    }
    .table th {
        background-color: #4f46e5;
        color: white;
        text-align: center;
    }
    .table tbody tr:hover {
        background-color: #f1f1f1;
    }
    .total-box {
        background: #e9ecef;
        padding: 15px;
        border-radius: 8px;
    }
    .status-balance {
        font-size: 18px;
        font-weight: bold;
    }
    .select2-container {
      z-index: 9999 !important;
    }
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

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
            <div class="col mb-3">
                <button type="button" class="btn btn-success" id="btnAddJurnal" data-bs-toggle="modal" data-bs-target="#formModal">
                    <i class="fa fa-plus"></i> Buat Jurnal
                </button>

            </div>
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
                        <th width="120px">Aksi</th>
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

<div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white d-flex align-items-center">
                <button type="button" class="btn btn-outline-light me-2" data-bs-dismiss="modal" aria-label="Back">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h5 class="modal-title flex-grow-1" id="modalTitle">Form Jurnal Umum</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card p-4">
                    <h3 class="text-center mb-4">📒 Jurnal Umum</h3>

                    <form id="jurnalForm">
                        <input type="hidden" name="data_id" id="data_id">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">📅 Tanggal</label>
                                <input type="date" class="form-control" name="tanggal" id="tanggalInput" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">🗄️ Database</label>
                                <select class="form-control" id="databaseSelect" name="database" required>
                                    <option value="">Pilih Database</option>
                                    <?php foreach ($dbs as $row): ?>
                                        <option value="<?= $row ?>"><?= $row ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">📝 Kode Jurnal</label>
                                <input type="text" class="form-control" name="kode_jurnal" id="kodeJurnalInput" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">💬 Keterangan</label>
                            <input type="text" class="form-control" name="keterangan" id="keteranganInput" required>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Akun</th>
                                    <th>Debit (Rp)</th>
                                    <th>Kredit (Rp)</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="jurnalBody">
                                <tr data-index="0">
                                    <td><select class="form-control akun-select2" name="jurnal[0][akun]" required style="width: 100%"></select></td>
                                    <td><input type="text" class="form-control debit" name="jurnal[0][debet]" value="0" min="0"></td>
                                    <td><input type="text" class="form-control kredit" name="jurnal[0][kredit]" value="0" min="0"></td>
                                    <td><input type="text" class="form-control" name="jurnal[0][ket]"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">🗑 Hapus</button></td>
                                </tr>
                            </tbody>
                        </table>

                        <button type="button" class="btn btn-primary btn-sm" id="addRow">➕ Tambah Baris</button>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="total-box text-center">
                                    <h5>Total Debit: <span id="totalDebit">0</span></h5>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="total-box text-center">
                                    <h5>Total Kredit: <span id="totalKredit">0</span></h5>
                                </div>
                            </div>
                            <div class="col-md-12 mt-3 text-center">
                                <span id="balanceStatus" class="status-balance text-danger">🔴 Belum Balance</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success mt-4 w-100">💾 Simpan Jurnal</button>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $('#btnAddJurnal').on('click', function () {
            $('#dataIdInput').val('');
            $('#jurnalForm')[0].reset();
            $('#jurnalBody').html('');
            $('#kodeJurnalInput').prop('readonly', false);
            $('#databaseSelect').prop('disabled', false);
            calculateTotals();
        });

        $('.akun-select2').select2({
          dropdownParent: $('#formModal')
        });
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

        const tanggalInput = document.getElementById("tanggalInput");

        tanggalInput.addEventListener("click", function () {
            if (this.showPicker) {
                this.showPicker();
            } else {
                this.focus();
            }
        });

        document.addEventListener("DOMContentLoaded", function () {
            const tanggalInput = document.getElementById("tanggalInput");
            const today = new Date().toISOString().split("T")[0];
            tanggalInput.max = today;
            tanggalInput.value = today; // Set nilai default ke hari ini
        });

        const akunCache = {};
        let isEditing = false;

        // Fungsi untuk mendapatkan data akun menggunakan AJAX
        function getAkunData(database, callback) {
            if (!database) {
                callback([]);
                return;
            }

            // Cek apakah data sudah ada di cache
            if (akunCache[database]) {
                callback(akunCache[database]);
                return;
            }

            // Jika belum ada, ambil dari server dan simpan ke cache
            $.ajax({
                url: "<?= base_url('cms/jurnal/get-akun') ?>",
                type: "GET",
                data: { database: database },
                success: function (data) {
                    akunCache[database] = data; // Simpan ke cache
                    callback(data);
                },
                error: function () {
                    alert("Error fetching akun data.");
                }
            });
        }

        // Fungsi autocomplete akun
        function applySelect2(element, database, selectedValue = null, callback = null) {
            getAkunData(database, function (akunList) {
                // Siapkan data untuk Select2
                var selectData = akunList.map(item => ({
                    id: item.kode_akun,
                    text: item.kode_akun + " - " + item.nama_akun
                }));

                // Cek dan destroy Select2 jika sudah diinisialisasi
                if ($(element).hasClass("select2-hidden-accessible")) {
                    $(element).select2('destroy');
                }

                // Kosongkan dan isi ulang option
                $(element).empty().append('<option></option>');

                // Tambahkan semua opsi
                selectData.forEach(function (item) {
                    $(element).append(new Option(item.text, item.id));
                });

                // Inisialisasi Select2
                $(element).select2({
                    theme: "bootstrap4",
                    placeholder: "-- Pilih Akun --",
                    allowClear: true,
                    dropdownParent: $('#formModal')
                });

                // Set selected value setelah select2 selesai render
                if (selectedValue) {
                    $(element).val(selectedValue).trigger('change');
                }

                if (callback && typeof callback === "function") {
                    callback();
                }
            });
        }

        // Terapkan autocomplete ke elemen input akun
        $(".akun-select2").each(function () {
            applySelect2(this, $("#databaseSelect").val());
        });

        // Tambah baris baru
        $("#addRow").click(function () {
            let totalDebit = 0, totalKredit = 0;

            $(".debit").each(function () {
                let val = $(this).val().replace(/\./g, "");
                totalDebit += val ? parseInt(val) : 0;
            });

            $(".kredit").each(function () {
                let val = $(this).val().replace(/\./g, "");
                totalKredit += val ? parseInt(val) : 0;
            });

            let selisih = totalDebit - totalKredit;

            let debitValue = 0;
            let kreditValue = 0;

            if (selisih > 0) {
                kreditValue = selisih;
            } else if (selisih < 0) {
                debitValue = Math.abs(selisih);
            }

            let rowIndex = $("#jurnalBody tr").length;

            // Pastikan nilai aman sebelum formatting
            let debitFormatted = typeof debitValue === "number" ? debitValue.toLocaleString("id-ID") : '0';
            let kreditFormatted = typeof kreditValue === "number" ? kreditValue.toLocaleString("id-ID") : '0';

            let newRow = `<tr data-index="${rowIndex}">
                <td><select class="form-control akun-select2" name="jurnal[${rowIndex}][akun]" required style="width: 100%"></select></td>
                <td><input type="text" class="form-control debit" name="jurnal[${rowIndex}][debet]" value="${debitFormatted}" min="0"></td>
                <td><input type="text" class="form-control kredit" name="jurnal[${rowIndex}][kredit]" value="${kreditFormatted}" min="0"></td>
                <td><input type="text" class="form-control" name="jurnal[${rowIndex}][ket]"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">🗑 Hapus</button></td>
            </tr>`;

            $("#jurnalBody").append(newRow);
            applySelect2($("#jurnalBody tr:last-child .akun-select2"), $("#databaseSelect").val());

            if (debitValue > 0) {
                $("#jurnalBody tr:last-child .kredit").prop("disabled", true);
            } else if (kreditValue > 0) {
                $("#jurnalBody tr:last-child .debit").prop("disabled", true);
            }

            calculateTotals();
        });

        $(document).on("click", ".remove-row", function () {
            $(this).closest("tr").remove();
            calculateTotals();
            reindexJurnalRows();
        });

        function reindexJurnalRows() {
            $("#jurnalBody tr").each(function (i, tr) {
                $(tr).attr("data-index", i);
                $(tr).find("[name^='jurnal']").each(function () {
                    let field = $(this).attr("name").match(/\[(\w+)\]$/)[1]; // ambil nama field: akun, debet, dll
                    $(this).attr("name", `jurnal[${i}][${field}]`);
                });
            });
        }

        function calculateTotals() {
            let totalDebit = 0, totalKredit = 0;
            
            $(".debit").each(function () {
                let val = $(this).val().replace(/\./g, "");
                totalDebit += val ? parseInt(val) : 0;
            });

            $(".kredit").each(function () {
                let val = $(this).val().replace(/\./g, "");
                totalKredit += val ? parseInt(val) : 0;
            });

            $("#totalDebit").text(totalDebit.toLocaleString("id-ID"));
            $("#totalKredit").text(totalKredit.toLocaleString("id-ID"));

            if (totalDebit === totalKredit) {
                $("#balanceStatus").html("🟢 Balance").removeClass("text-danger").addClass("text-success");
            } else {
                $("#balanceStatus").html("🔴 Belum Balance").removeClass("text-success").addClass("text-danger");
            }
        }

        $(document).on("keyup", ".debit, .kredit", function () {
            let $row = $(this).closest("tr");
            let debitVal = $row.find(".debit").val().replace(/\./g, "");
            let kreditVal = $row.find(".kredit").val().replace(/\./g, "");

            // Handle debit
            if ($(this).hasClass("debit")) {
                if (debitVal && parseInt(debitVal) > 0) {
                    $row.find(".kredit").prop("disabled", true).val("0");
                } else {
                    $row.find(".kredit").prop("disabled", false);
                }
            }

            // Handle kredit
            if ($(this).hasClass("kredit")) {
                if (kreditVal && parseInt(kreditVal) > 0) {
                    $row.find(".debit").prop("disabled", true).val("0");
                } else {
                    $row.find(".debit").prop("disabled", false);
                }
            }

            calculateTotals();
        });

        // Format ribuan saat blur
        $(document).on("blur", ".debit, .kredit", function () {
            let rawVal = $(this).val().replace(/\./g, "");
            if (!isNaN(rawVal) && rawVal !== "") {
                let formatted = parseInt(rawVal).toLocaleString("id-ID");
                $(this).val(formatted);
            }
        });

        // Bersihkan format saat fokus
        $(document).on("focus", ".debit, .kredit", function () {
            let unformatted = $(this).val().replace(/\./g, "");
            $(this).val(unformatted);
        });

        $(document).ready(function () {
            $("#jurnalForm").submit(function (e) {
                e.preventDefault();

                if ($("#balanceStatus").text() !== "🟢 Balance") {
                    alert("⚠️ Total Debit dan Kredit harus balance sebelum disimpan!");
                    return;
                }

                let totalDebit = 0, totalKredit = 0;

                $(".debit").each(function () {
                    let val = $(this).val().replace(/\./g, "");
                    totalDebit += val ? parseInt(val) : 0;
                });

                $(".kredit").each(function () {
                    let val = $(this).val().replace(/\./g, "");
                    totalKredit += val ? parseInt(val) : 0;
                });

                if (totalDebit === 0 || totalKredit === 0) {
                    alert("⚠️ Total Debit dan Kredit tidak boleh 0!");
                    return;
                }

                $(".debit, .kredit").each(function () {
                    let cleanVal = $(this).val().replace(/\./g, "");
                    $(this).val(cleanVal);
                });

                let formData = $(this).serialize();

                $.ajax({
                    type: "POST",
                    url: "<?= base_url('cms/jurnal/save') ?>",
                    data: formData,
                    success: function (response) {
                        if(response.status == true){
                            alert("✅ Jurnal berhasil disimpan!");
                            $("#jurnalForm")[0].reset();
                            $("#jurnalBody").html("");

                            calculateTotals();

                            // Tutup modal
                            $("#formModal").modal('hide');

                            // Refresh DataTable
                            $("#jurnalTable").DataTable().ajax.reload(null, false); // false = tetap di halaman sekarang
                        }else{
                            alert("❌ Jurnal gagal disimpan!");
                        }
                    },
                    error: function () {
                        alert("❌ Terjadi kesalahan, coba lagi.");
                    }
                });
            });
        });

        $("#databaseSelect").change(function () {
            if (isEditing) return; // ❌ Skip jika sedang edit

            const db = $(this).val();
            const tanggal = $("#tanggalInput").val();

            if (db) {
                akunCache[db] = null;

                $.ajax({
                    url: "<?= base_url('cms/jurnal/get-kode') ?>",
                    type: "POST",
                    data: {
                        database: db,
                        tanggal: tanggal
                    },
                    success: function (response) {
                        $(".akun-select2").val(null).trigger("change");
                        $("#kodeJurnalInput").val(response.kode);
                    },
                    error: function () {
                        alert("❌ Gagal mengambil kode jurnal.");
                    }
                });

                $(".akun-select2").each(function () {
                    applySelect2(this, db);
                });
            }
        });

        function edit_data(id) {
            isEditing = true; // ✅ Set flag sebelum proses

            $.ajax({
                type: "GET",
                url: "<?= base_url('cms/jurnal/edit') ?>",
                data: { id: id },
                dataType: "json",
                success: function (response) {
                    if (response.status) {
                        const data = response.data;

                        $('#databaseSelect').val(data.database).prop('disabled', true).trigger('change');
                        $('#kodeJurnalInput').val(data.kode_jurnal).trigger('change').prop('readonly', true);;
                        $('#tanggalInput').val(data.tanggal);
                        $('#keteranganInput').val(data.keterangan);


                        $('#jurnalBody').html('');

                        getAkunData(data.database, function (akunList) {
                            data.jurnal_detail.forEach((item, index) => {
                                let newRow = `
                                    <tr>
                                        <td><select class="form-control akun-select2" name="jurnal[${index}][akun]" required style="width: 100%"></select></td>
                                        <td><input type="text" class="form-control debit" name="jurnal[${index}][debet]" value="${parseFloat(item.debet).toLocaleString("id-ID")}"></td>
                                        <td><input type="text" class="form-control kredit" name="jurnal[${index}][kredit]" value="${parseFloat(item.kredit).toLocaleString("id-ID")}"></td>
                                        <td><input type="text" class="form-control" name="jurnal[${index}][ket]" value="${item.ket || ''}"></td>
                                        <td><button type="button" class="btn btn-danger btn-sm remove-row">🗑 Hapus</button></td>
                                    </tr>
                                `;
                                $("#jurnalBody").append(newRow);

                                // Terapkan select2 dan set akun
                                const selectEl = $("#jurnalBody tr:last-child .akun-select2");
                                // Langsung pakai data akun tanpa fetch ulang
                                const selectData = akunList.map(item => ({
                                    id: item.kode_akun,
                                    text: item.kode_akun + " - " + item.nama_akun
                                }));

                                // Init Select2
                                if ($(selectEl).hasClass("select2-hidden-accessible")) {
                                    $(selectEl).select2('destroy');
                                }

                                $(selectEl).empty().append('<option></option>');
                                selectData.forEach(function(opt) {
                                    $(selectEl).append(new Option(opt.text, opt.id));
                                });

                                $(selectEl).select2({
                                    theme: "bootstrap4",
                                    placeholder: "-- Pilih Akun --",
                                    allowClear: true,
                                    dropdownParent: $('#formModal')
                                });

                                $(selectEl).val(item.akun).trigger("change");
                            });

                            calculateTotals();
                            $('#formModal').modal('show');
                            $('#data_id').val(data.kode_jurnal + '|' + data.database);

                            isEditing = false; // ✅ Aktifkan lagi setelah selesai edit
                        });

                    } else {
                        alert("❌ Gagal ambil data.");
                        isEditing = false;
                    }
                },
                error: function () {
                    alert("❌ Terjadi kesalahan, coba lagi.");
                    isEditing = false;
                }
            });
        }

        function delete_data(id) {
            if (!confirm("⚠️ Yakin ingin menghapus jurnal ini?")) return;

            $.ajax({
                url: "<?= base_url('cms/jurnal/delete') ?>",
                type: "POST",
                data: { id: id },
                dataType: "json",
                success: function (res) {
                    if (res.status === true) {
                        alert("✅ Jurnal berhasil dihapus.");
                        $("#jurnalTable").DataTable().ajax.reload(null, false);
                    } else {
                        alert("❌ Gagal menghapus jurnal.");
                    }
                },
                error: function () {
                    alert("❌ Terjadi kesalahan saat menghapus jurnal.");
                }
            });
        }
    </script>
<?= $this->endSection() ?>
