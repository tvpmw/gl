<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<style>
    /* Card styling */
    .card {
        border-radius: var(--border-radius-lg);
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        box-shadow: var(--shadow-sm);
        transition: var(--transition-base);
    }

    .card:hover {
        box-shadow: var(--shadow-md);
    }

    /* Modal styling */
    .modal-content {
        border-radius: var(--border-radius-lg);
        border: 1px solid var(--border-color);
        background: var(--bg-card);
    }

    .modal-header {
        border-bottom: 1px solid var(--border-color);
        padding: 1rem 1.5rem;
    }

    .modal-header.bg-primary {
        background: var(--primary-color) !important;
    }    

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        border-top: 1px solid var(--border-color);
        padding: 1rem 1.5rem;
    }

    /* Form controls */
    .form-control {
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: 0.5rem 1rem;
        background: var(--bg-main);
        color: var(--text-main);
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
    }

    /* Select2 customization */
    .select2-container--bootstrap5 .select2-selection {
        border-color: var(--border-color);
        border-radius: var(--border-radius);
        background: var(--bg-main);
        color: var(--text-main);
    }

    /* Button styling */
    .btn {
        border-radius: var(--border-radius);
        padding: 0.5rem 1rem;
        font-weight: 500;
        transition: var(--transition-base);
    }

    .btn-primary {
        background: var(--primary-color);
        border: none;
    }

    .btn-primary:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
    }

    .btn-primary {
        background: var(--warning-color);
        border: none;
        color: white;
    }

    /* Table styling */
    .table {
        --bs-table-bg: var(--bg-card);
        --bs-table-border-color: var(--border-color);
    }

    .table thead th {
        background: var(--bg-main);
        border-bottom: 2px solid var(--border-color);
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.025em;
    }

    /* Radio button styling */
    .icheck-info {
        margin-right: 1rem;
    }

    /* Alert styling */
    .alert {
        border-radius: var(--border-radius);
        border: 1px solid var(--border-color);
    }

    .alert-dismissible .close {
        padding: 1rem;
    }

    /* Dark mode adjustments */
    [data-bs-theme="dark"] .form-control {
        background: var(--bg-card);
        color: var(--text-main);
        border-color: var(--border-color);
    }

    [data-bs-theme="dark"] .modal-content {
        background: var(--bg-card);
    }

    [data-bs-theme="dark"] .table {
        color: var(--text-main);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="container mt-4">
      <div class="card">
        <div class="card-header bg-primary">
          <h3 class="card-title text-white"><?=isLang('daftar2')?> <?=isset($judul)?$judul:''?></h3>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <?php if(isset($aksiCreate) && $aksiCreate == 'yes'):?>
            <button class="btn btn-primary btn-sm btn-add mb-2"><?=isLang('tambah_data')?></button>
            <?php endif; ?>
            <table id="dataTable" class="table table-bordered table-striped table-sm">
              <thead class="headtable">
                <tr>
                  <th width="80px">No</th>
                  <th><?=isLang('nama')?></th>
                  <th><?=isLang('email')?></th>
                  <th><?=isLang('no_hp')?></th>
                  <th><?=isLang('Username')?></th>
                  <th><?=isLang('Peran')?></th>
                  <th><?=isLang('Status')?></th>
                  <th><?=isLang('Modul')?></th>
                  <th width="80px"><?=isLang('aksi')?></th>
                </tr>
              </thead>
              <tbody class="rowtable">
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- The Modal -->
    <div class="modal fade" id="modal_form" data-backdrop="static" data-keyboard="false" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary" id="mhead">
            <h5 class="modal-title text-white" id="modal-title"></h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="#" id="form-data" class="form-horizontal">
          <div class="modal-body">
            <input type="hidden" value="" name="data_id"/>
            <div class="form-group">
              <label for="nama" class="col-form-label"><?=isLang('nama')?>:</label>
              <input type="text" class="form-control" name="nama" id="nama" maxlength="50" autocomplete="off" placeholder="Input <?=isLang('nama')?>" required>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="email" class="col-form-label"><?=isLang('Email')?>:</label>
                  <input type="email" class="form-control" name="email" id="email" maxlength="50" autocomplete="off" placeholder="Input <?=isLang('Email')?>" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="no_hp" class="col-form-label"><?=isLang('no_hp')?>:</label>
                  <input type="text" class="form-control" name="no_hp" id="no_hp" maxlength="15" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*?)\..*/g, '$1');" autocomplete="off" placeholder="Input <?=isLang('no_hp')?>" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="username" class="col-form-label"><?=isLang('Username')?>:</label>
                  <input type="text" class="form-control" name="username" id="username" maxlength="20" autocomplete="off" placeholder="Input <?=isLang('Username')?>" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="role" class="col-form-label"><?=isLang('peran')?>:</label>
                  <select class="form-control" name="role" id="role" required>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Superadmin</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="password" class="col-form-label"><?=isLang('Password')?>: <span id="passedit" style="color: red"></span></label>
                  <input type="password" class="form-control" name="password" id="password" autocomplete="off" placeholder="Input <?=isLang('Password')?>" required>
                  <span class="passinfo"></span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="password2" class="col-form-label"><?=isLang('konfirmasi_sandi')?>:</label>
                  <input type="password" class="form-control" name="password2" id="password2" autocomplete="off" placeholder="Input <?=isLang('konfirmasi_sandi')?>" required>
                  <span class="passinfo"></span>
                </div>
              </div>
            </div>
            <div class="form-group clearfix">
              <div class="icheck-info d-inline">
                <input type="radio" id="status1" value="1" name="status" checked>
                <label for="status1">
                  <?=isLang('Aktif')?>
                </label>
              </div>
              <div class="icheck-info d-inline">
                <input type="radio" id="status0" value="0" name="status">
                <label for="status0">
                  <?=isLang('tidak_aktif')?>
                </label>
              </div>
            </div>
            <span id="msgFormInput"></span>
          </div>
          <div class="modal-footer">
            <button type="submit" id="btnSave" class="btn btn-primary"><?=isLang('simpan')?></button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?=isLang('keluar')?></button>            
          </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Module Access Modal -->
    <div class="modal fade" id="modal_module_access" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white"><?=isLang('Edit Hak Akses Modul')?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="form-module-access">
                    <input type="hidden" name="user_id" id="access_user_id">
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th><?=isLang('Modul')?></th>
                                        <th class="text-center">
                                            <input type="checkbox" class="form-check-input check-all" data-type="view"> <?=isLang('Lihat')?>
                                        </th>
                                        <th class="text-center">
                                            <input type="checkbox" class="form-check-input check-all" data-type="create"> <?=isLang('Tambah')?>
                                        </th>
                                        <th class="text-center">
                                            <input type="checkbox" class="form-check-input check-all" data-type="edit"> <?=isLang('Edit')?>
                                        </th>
                                        <th class="text-center">
                                            <input type="checkbox" class="form-check-input check-all" data-type="delete"> <?=isLang('Hapus')?>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="module_access_list">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><?=isLang('simpan')?></button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?=isLang('keluar')?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Tambahkan CDN SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
var table;
$(document).ready(function() {
  table = $("#dataTable").DataTable({
    processing: true, 
    serverSide: true, 
    pageLength: 10,
    order: [],
    ajax: {
        url: "<?= base_url('cms/user/lists') ?>",
        type: "POST",
        global: false
    },
    columnDefs: [
        { 
            targets: [0, 7], // First and last column 
            orderable: false
        },
        {
            targets: [0, 6, 7], // No, Status, and Action columns
            className: 'text-center'
        },
        {
            targets: 6, // Status column
            searchable: false // Disable search for boolean status column
        }
    ],
    language: {
        processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>'
    }
  });

  // Handle modal close button and escape key
  $('#modal_form').on('hide.bs.modal', function (e) {
      resetForm();
  });
  
  // Handle close button click
  $('.close, .btn-secondary').on('click', function() {
      $('#modal_form').modal('hide');
  });
  
  // Function to reset form
  function resetForm() {
      $('#form-data')[0].reset();
      $('#msgFormInput').html('');
      $('.passinfo').html('');
      $('#btnSave').prop('disabled', false);
      $('[name="data_id"]').val('');
      $('#mhead').removeClass('bg-primary').addClass('bg-primary');
      $('#btnSave').removeClass('btn-primary').addClass('btn-primary');
  }

  $("#form-data").submit(function(event){
    event.preventDefault();
    const formData = $(this).serialize();
    
    $.ajax({
        url: "<?= base_url('cms/user/save') ?>",
        type: "POST",
        data: formData,
        dataType: "JSON",
        beforeSend: function() {
            $("#btnSave").prop('disabled', true);
            $("#btnSave").val('Saving...');
        },
        success: function(response) {
            if (response.status) {
                $('#modal_form').modal('hide');
                reload_table();
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.msg
                });
            } else if (response.status === 'auth') {
                $('#myLogin').modal('show');
            } else {
                $('#msgFormInput').html(`
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        ${response.msg}
                    </div>
                `);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX Error:', textStatus, errorThrown);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while saving data'
            });
        },
        complete: function() {
            $("#btnSave").prop('disabled', false);
            $("#btnSave").val('<?=isLang('simpan')?>');
        }
    });
  });
});

function reload_table()
{
  table.ajax.reload(null,false); 
}

function edit_data(data)
{
  $('#msgFormInput').html('');
  $('[name="data_id"]').val(data.id);
  $('[name="nama"]').val(data.nama);
  $('[name="email"]').val(data.email);
  $('[name="no_hp"]').val(data.no_hp);
  $('[name="username"]').val(data.username);
  $('[name="role"]').val(data.role);
  $("input[name=status][value=" + data.status + "]").prop('checked', true);

  $('[name="password"]').prop('required',false);
  $('[name="password2"]').prop('required',false);
  $('#passedit').html('Input password jika ingin mengganti');
  
  // Update modal styling
  $('#mhead').removeClass('bg-primary').addClass('bg-primary');
  $('#btnSave').removeClass('btn-primary').addClass('btn-primary');
  
  $('#modal_form').modal('show');
  $('#modal-title').text('<?=isLang('edit_data')?>');
}

$(document).on('click', ".btn-add", function(event) {
  event.preventDefault();
  $('#msgFormInput').html('');
  $('#form-data')[0].reset();
  $('[name="data_id"]').val('');
  $('[name="password"]').prop('required',true);
  $('[name="password2"]').prop('required',true);
  $('#passedit').html('');
  $('#mhead').removeClass('bg-primary');
  $('#mhead').addClass('btn-primary');
  $('#btnSave').removeClass('btn-primary');
  $('#btnSave').addClass('btn-primary');
  $('#modal_form').modal('show');
  $('#modal-title').text('<?=isLang('tambah_data')?>');
});

function delete_data(id)
{
    Swal.fire({
        title: 'Hapus User',
        text: 'Apa anda yakin ingin menghapus data user ini dari database ?',
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<?=isLang('Ya, Hapus!')?>',
        cancelButtonText: '<?=isLang('Batal')?>'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "<?= base_url('cms/user/delete') ?>",
                type: "POST",
                data: { id: id },
                dataType: "JSON",
                success: function(response) {
                    reload_table();
                    Swal.fire({
                        icon: response.status ? 'success' : 'error',
                        title: response.status ? '<?=isLang('Berhasil')?>' : '<?=isLang('gagal')?>',
                        text: response.msg
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: '<?=isLang('Error')?>',
                        text: '<?=isLang('terjadi_kesalahan')?>'
                    });
                }
            });
        }
    });
}

function privileges(id)
{
  window.location.assign("<?=base_url('cms/user/privileges?q=')?>"+id);
}

$("#password, #password2").on('input', function() {
    const pass1 = $("#password").val();
    const pass2 = $("#password2").val();
    
    $(".passinfo").html(
        pass1 && pass2 && pass1 !== pass2 
            ? '<span class="text-danger">Password tidak sama</span>' 
            : ''
    );
    
    $("#btnSave").prop('disabled', pass1 && pass2 && pass1 !== pass2);
});

// Handle module access
$(document).on('click', '.btn-access', function() {
    var userId = $(this).data('id');
    $('#access_user_id').val(userId);
    
    // Fetch and display module access data
    $.ajax({
        url: "<?= base_url('cms/user/get_module_access') ?>",
        type: "POST",
        data: { user_id: userId },
        dataType: "JSON",
        success: function(response) {
            var rows = '';
            $.each(response.modules, function(index, module) {
                rows += `
                    <tr>
                        <td>${module.name}</td>
                        <td class="text-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="view_access[]" value="${module.id}" ${module.view_access ? 'checked' : ''}>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="add_access[]" value="${module.id}" ${module.add_access ? 'checked' : ''}>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="edit_access[]" value="${module.id}" ${module.edit_access ? 'checked' : ''}>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="delete_access[]" value="${module.id}" ${module.delete_access ? 'checked' : ''}>
                            </div>
                        </td>
                    </tr>
                `;
            });
            $('#module_access_list').html(rows);
        }
    });
    
    $('#modal_module_access').modal('show');
});

function editModuleAccess(userId) {
    $('#access_user_id').val(userId);
    
    // Load current module access
    $.ajax({
        url: "<?= base_url('cms/module/getUserModuleAccess') ?>",
        type: "POST",
        data: { user_id: userId },
        dataType: "JSON",
        success: function(response) {
            let html = '';
            response.modules.forEach(function(module) {
                html += `
                    <tr>
                        <td>${module.name}</td>
                        <td class="text-center">
                            <input type="checkbox" name="access[${module.id}][view]" 
                                ${module.can_view ? 'checked' : ''}>
                        </td>
                        <td class="text-center">
                            <input type="checkbox" name="access[${module.id}][create]" 
                                ${module.can_create ? 'checked' : ''}>
                        </td>
                        <td class="text-center">
                            <input type="checkbox" name="access[${module.id}][edit]" 
                                ${module.can_edit ? 'checked' : ''}>
                        </td>
                        <td class="text-center">
                            <input type="checkbox" name="access[${module.id}][delete]" 
                                ${module.can_delete ? 'checked' : ''}>
                        </td>
                    </tr>
                `;
            });
            $('#module_access_list').html(html);
            $('#modal_module_access').modal('show');
        }
    });
}

// Replace the edit_module_access function with this updated version
function edit_module_access(userId) {
    $('#access_user_id').val(userId);
    
    $.ajax({
        url: "<?= base_url('cms/user/get-module-access') ?>",
        type: "POST",
        data: { user_id: userId },
        dataType: "JSON",
        beforeSend: function() {
            $('#module_access_list').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
        },
        success: function(response) {
            if (response.status) {
                let html = '';
                response.data.forEach(function(module) {
                    const isViewOnly = module.slug === 'dashboard';
                    html += `
                        <tr>
                            <td>${module.name}</td>
                            <td class="text-center align-middle">
                                <div class="form-check d-flex justify-content-center">
                                    <input type="checkbox" class="form-check-input module-checkbox" 
                                        name="access[${module.id}][view]" 
                                        value="1"
                                        data-type="view"
                                        ${module.can_view ? 'checked' : ''}>
                                </div>
                            </td>
                            ${isViewOnly ? `
                                <td colspan="3" class="text-center align-middle text-muted">
                                    <small><i>View Only Module</i></small>
                                </td>
                            ` : `
                                <td class="text-center align-middle">
                                    <div class="form-check d-flex justify-content-center">
                                        <input type="checkbox" class="form-check-input module-checkbox" 
                                            name="access[${module.id}][create]" 
                                            value="1"
                                            data-type="create"
                                            ${module.can_create ? 'checked' : ''}>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="form-check d-flex justify-content-center">
                                        <input type="checkbox" class="form-check-input module-checkbox" 
                                            name="access[${module.id}][edit]" 
                                            value="1"
                                            data-type="edit"
                                            ${module.can_edit ? 'checked' : ''}>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="form-check d-flex justify-content-center">
                                        <input type="checkbox" class="form-check-input module-checkbox" 
                                            name="access[${module.id}][delete]" 
                                            value="1"
                                            data-type="delete"
                                            ${module.can_delete ? 'checked' : ''}>
                                    </div>
                                </td>
                            `}
                        </tr>
                    `;
                });
                $('#module_access_list').html(html);
                updateCheckAllStates(); 
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.msg || 'Failed to load module access'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load module access: ' + error
            });
        }
    });
    
    $('#modal_module_access').modal('show');
}

// Update form submit handler
$("#form-module-access").submit(function(e) {
    e.preventDefault();
    
    $.ajax({
        url: "<?= base_url('cms/user/save-module-access') ?>",
        type: "POST",
        data: $(this).serialize(),
        dataType: "JSON",
        beforeSend: function() {
            $("#form-module-access button[type=submit]").prop('disabled', true);
        },
        success: function(response) {
            if (response.status) {
                $('#modal_module_access').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.msg
                });
                reload_table();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.msg
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to save module access: ' + error
            });
        },
        complete: function() {
            $("#form-module-access button[type=submit]").prop('disabled', false);
        }
    });
});

// Handle check all functionality
$(document).on('change', '.check-all', function() {
    const type = $(this).data('type');
    const isChecked = $(this).prop('checked');
    
    // Select all checkboxes including dashboard for view permission
    if (type === 'view') {
        $(`input[name$="[${type}]"]`).prop('checked', isChecked);
    } else {
        // For other permissions, exclude view-only modules
        $('.module-checkbox[data-type="' + type + '"]').each(function() {
            const row = $(this).closest('tr');
            if (!row.find('td[colspan="3"]').length) {
                $(this).prop('checked', isChecked);
            }
        });
    }
});

// Add some CSS to ensure checkbox alignment
$('<style>')
    .text(`
        .form-check {
            margin: 0;
            padding: 0;
        }
        .align-middle {
            vertical-align: middle !important;
        }
        .form-check-input {
            margin: 0 !important;
            position: relative !important;
        }
        .d-flex.justify-content-center {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            height: 100%;
        }
    `)
    .appendTo('head');

// Update check all state when individual checkboxes change
$(document).on('change', '.module-checkbox', function() {
    const type = $(this).data('type');
    const totalBoxes = $('.module-checkbox[data-type="' + type + '"]').not(':disabled').length;
    const checkedBoxes = $('.module-checkbox[data-type="' + type + '"]:checked').not(':disabled').length;
    
    $('.check-all[data-type="' + type + '"]').prop('checked', totalBoxes === checkedBoxes);
});

// Function to update all check-all states
function updateCheckAllStates() {
    ['view', 'create', 'edit', 'delete'].forEach(function(type) {
        const totalBoxes = $('.module-checkbox[data-type="' + type + '"]').not(':disabled').length;
        const checkedBoxes = $('.module-checkbox[data-type="' + type + '"]:checked').not(':disabled').length;
        $('.check-all[data-type="' + type + '"]').prop('checked', totalBoxes === checkedBoxes);
    });
}
</script>
<?= $this->endSection() ?>
