<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<style>
    .select2-container {
        width: 100% !important;
        z-index: 9999 !important;
    }
    
    .select2-dropdown {
        z-index: 10000 !important;
    }

    .select2-container .select2-selection--single {
        height: 38px !important;
    }

    .select2-container--bootstrap-5 .select2-selection--single {
        padding-top: 5px;
    }

    .select2-container .select2-selection--single .select2-selection__rendered {
        padding-left: 12px;
    }

    .select2-results__options {
        max-height: 200px;
        overflow-y: auto;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <h2 class="mt-4 text-center">Master Mapping Coretax</h1>
    
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center text-white">
                <div>
                    <i class="fas fa-table me-1"></i>
                    Data Mapping
                </div>
                <?php 
                $mappingCoretax = checkMenuAccess('cms/mapping-coretax');
                    if ($mappingCoretax['can_create']): ?>    
                <button type="button" class="btn btn-primary btn-sm" id="btnAdd">
                    <i class="fas fa-plus me-1"></i>Tambah Data
                </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Sumber Data</label>
                    <select class="form-select" id="sumber_data">
                       <?php 
                            $dbList = getSelDb();
                            foreach ($dbList as $key => $name): ?>
                                <option value="<?= $key ?>"><?= $name ?></option>
                            <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <table id="dataTable" class="table table-bordered table-striped">
                <thead>
                    <tr>                        
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Kode Tax</th>
                        <th width="100px">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="modalForm" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Form Mapping Coretax</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formMapping">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Barang</label>
                        <select class="form-select" name="kdbrg" id="kdbrg" required>
                            <option value="">Pilih Barang</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode Tax</label>
                        <select class="form-select" name="kdtax" id="kdtax" required>
                            <option value="">Pilih Kode Tax</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#dataTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '<?= base_url('cms/mapping-coretax/data') ?>',
            type: 'POST',
            data: function(d) {
                d.sumber_data = $('#sumber_data').val();
            }
        },
        columns: [
            { data: 0 },            
            { data: 1 },
            { data: 2 },
            { 
                data: 3,
                render: function(data, type, row) {
                    return `
                     <?php 
                $mappingCoretax = checkMenuAccess('cms/mapping-coretax');
                    if ($mappingCoretax['can_edit']): ?>   
                        <button type="button" class="btn btn-warning btn-sm btnEdit" data-id="${row[0]}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <?php endif; ?>
                         <?php 
                $mappingCoretax = checkMenuAccess('cms/mapping-coretax');
                    if ($mappingCoretax['can_delete']): ?>   
                        <button type="button" class="btn btn-danger btn-sm btnDelete" data-id="${row[0]}">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?php endif; ?>
                    `;
                }
            }
        ]
    });

    // Add change event handler for sumber_data
    $('#sumber_data').on('change', function() {
        table.ajax.reload();
        // Clear and reset select2 fields
        $('#kdbrg').val(null).trigger('change');
        $('#kdtax').val(null).trigger('change');
    });

    // Update the Select2 initialization for kdbrg
    $('#kdbrg').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#modalForm'),
        ajax: {
            url: '<?= base_url('cms/barang/search') ?>',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term || '',
                    sumber_data: $('#sumber_data').val() // Add this line
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(function(item) {
                        return {
                            id: item.kdbrg,
                            text: item.nmbrg + ' (' + item.kdbrg + ')'
                        };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 1,
        language: {
            inputTooShort: function() {
                return 'Masukkan minimal 1 karakter';
            }
        }
    });

    // Update the Select2 initialization for kdtax
    $('#kdtax').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#modalForm'),
        ajax: {
            url: '<?= base_url('cms/barang/tax-codes') ?>',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term || '',
                    sumber_data: $('#sumber_data').val() // Add this line
                };
            },
            processResults: function(response) {
                if (!response.success) {
                    console.error('Tax codes error:', response.message);
                    return { results: [] };
                }
                return {
                    results: response.data.map(item => ({
                        id: item.value,
                        text: `${item.text} (${item.value})`
                    }))
                };
            },
            cache: false
        },
        minimumInputLength: 1,
        language: {
            inputTooShort: function() {
                return 'Masukkan minimal 1 karakter';
            },
            noResults: function() {
                return 'Tidak ada data yang sesuai';
            },
            searching: function() {
                return 'Mencari...';
            }
        }
    });

    // Handle form submit
    $('#formMapping').on('submit', function(e) {
        e.preventDefault();
        
        const $kdbrg = $('#kdbrg');
        const formData = {
            kdbrg: $kdbrg.val(),
            nmbrg: $kdbrg.select2('data')[0].text.split(' (')[0],
            kdtax: $('#kdtax').val(),
            sumber_data: $('#sumber_data').val()
        };

        $.ajax({
            url: '<?= base_url('cms/mapping-coretax/save') ?>',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    $('#modalForm').modal('hide'); // Hide modal first
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message
                    }).then(() => {
                        table.ajax.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message
                    });
                }
            }
        });
    });

    // Handle Add button
    $('#btnAdd').on('click', function() {
        $('#formMapping')[0].reset();
        $('#kdbrg').val('').trigger('change');
        $('#kdtax').val('').trigger('change');
        $('#modalForm').modal('show');
    });

    // Handle Edit button
    $(document).on('click', '.btnEdit', function() {
        const id = $(this).data('id');
        const row = table.row($(this).closest('tr')).data();
        
        $('#kdbrg').append(new Option(row[0], row[0], true, true)).trigger('change');
        $('#kdtax').val(row[2]).trigger('change');
        $('#modalForm').modal('show');
    });

    // Handle Delete button
    $(document).on('click', '.btnDelete', function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Hapus Data?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('cms/mapping-coretax/delete') ?>',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ kdbrg: id }),
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            }).then(() => {
                                table.ajax.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message
                            });
                        }
                    }
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>