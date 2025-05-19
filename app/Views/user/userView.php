<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

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
    <div class="modal" id="modal_form" data-backdrop="static">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header" id="mhead">
            <h4 class="modal-title text-white" id="modal-title">Modal Heading</h4>
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
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
            <input type="submit" id="btnSave" class="btn text-white btn_1" value="<?=isLang('simpan')?>">
            <button type="button" class="btn btn-danger" data-dismiss="modal"><?=isLang('keluar')?></button>
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
    "processing": true, 
    "serverSide": true, 
    "pageLength": 10,
    "orderable": false,
    "aaSorting": [],
    // "order": [[ 1, "asc" ]], 
          
    "ajax": {
      "url": "<?= base_url('cms/user/lists') ?>",
      "type": "POST",
      "global": false,
    },

    "columnDefs": [
      { 
        "targets": [ 0, -1 ], 
        "orderable": false, 
      },
      {
        "targets": [0, -2, -1],
        "className": 'text-center'
      },
    ],
  });

  $("#form-data").submit(function(event){
    event.preventDefault();
    var form_data = $('#form-data').serialize()
    $.ajax({
      url : "<?= base_url('cms/user/save') ?>",
      type:"post",
      data:form_data,
      dataType: "JSON",
      success: function(data)
      {
        if(data.status == true){
          $('#modal_form').modal('hide');
          reload_table();
          swal(
            'Good job!',
            data.msg,
            'success'
          )
        }else if(data.status == 'auth'){
          $('#myLogin').modal('show');
        }else{
          var a = '';
              a +='<div class="alert alert-danger alert-dismissible">';
              a +='<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
              a +=data.msg;
              a +='</div>';
          $('#msgFormInput').html(a);
        }
      },
      error: function (jqXHR, textStatus, errorThrown)
      {
        console.log(jqXHR);
        console.log(textStatus);
        console.log(errorThrown);
        // location.reload();
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
  $('#mhead').addClass('bg-warning');
  $('#mhead').removeClass('btn-primary');
  $('#btnSave').addClass('btn-warning');
  $('#btnSave').removeClass('btn-primary');
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
  $('#mhead').removeClass('bg-warning');
  $('#mhead').addClass('btn-primary');
  $('#btnSave').removeClass('btn-warning');
  $('#btnSave').addClass('btn-primary');
  $('#modal_form').modal('show');
  $('#modal-title').text('<?=isLang('tambah_data')?>');
});

function delete_data(id)
{
    Swal.fire({
        title: '<?=isLang('delete_title')?>?',
        text: `<?=isLang('delete_text')?>!`,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6a6f74',
        confirmButtonText: 'Ya, Logout!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url : "<?= base_url('cms/user/delete') ?>",
            type: "POST",
            data: {id:id},
            dataType: "JSON",
            success: function(data)
            {
              reload_table();
              if(data.status == true){
                var sts1 = '<?=isLang('Berhasil')?>';
                var sts2 = 'success';
              }else{
                var sts1 = '<?=isLang('gagal')?>';
                var sts2 = 'warning';
              }
              swal(
              sts1,
              data.msg,
              sts2
              );
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
              alert('<?=isLang('terjadi_kesalahan')?>');
            }
          });  
        }
    });
}

function privileges(id)
{
  window.location.assign("<?=base_url('cms/user/privileges?q=')?>"+id);
}

$("#password, #password2").keyup(function(){
  var pass_1 = $("#password").val();
  var pass_2 = $("#password2").val();
  if(pass_1!=pass_2){
    $(".passinfo").html("<span style='color:red'>Password tidak sama</span>");
  }else{
    $(".passinfo").html("");
  }
});
</script>
<?= $this->endSection() ?>
