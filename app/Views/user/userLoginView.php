<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-teal">
        <h3 class="card-title text-white"><?=isLang('daftar2')?> <?=isset($judul)?$judul:''?></h3>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="userTable" class="table table-bordered table-striped table-sm" style="width:100%">
            <thead>
              <tr>
                <th>User ID</th>
                <th>IP Address</th>
                <th>Last Active</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
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
      $(document).ready(function () {
          const table = $('#userTable').DataTable({
              ajax: {
                  url: '<?= base_url('cms/user/getOnlineUsers') ?>',
                  dataSrc: '',
                  global:false
              },
              columns: [
                  { 
                      data: null,
                      render: function(data) {
                          return data.user_name; // Tampilkan nama user
                      }
                  },
                  { data: 'ip' },
                  { data: 'last_active' },
                  {
                      data: null,
                      render: function (data, type, row) {
                          return `<button class="btn btn-danger btn-sm" onclick="forceLogout('${row.user_id}', '${row.user_name}')">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                  </button>`;
                      }
                  }
              ]
          });

          // Refresh otomatis setiap 10 detik
          // setInterval(() => table.ajax.reload(null, false), 10000);
      });
      
      function forceLogout(userId, userName) {
          Swal.fire({
              title: 'Konfirmasi',
              text: `Apa anda yakin ingin memaksa logout user ${userName}?`,
              icon: 'error',
              showCancelButton: true,
              confirmButtonColor: '#dc3545',
              cancelButtonColor: '#6a6f74',
              confirmButtonText: 'Ya, Logout!',
              cancelButtonText: 'Batal'
          }).then((result) => {
              if (result.isConfirmed) {
                  $.ajax({
                      url: '<?= base_url('cms/user/forceLogout') ?>',
                      type: 'POST',
                      data: {
                          user_id: userId
                      },
                        success: function(response) {
                          Swal.fire({
                            title: 'Berhasil!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#20c997' // Warna bg-teal
                          }).then(() => {
                            // $('#userTable').DataTable().ajax.reload(null, false);
                            location.reload();
                          });
                        },
                      error: function(xhr) {
                          Swal.fire({
                              title: 'Error!',
                              text: xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan',
                              icon: 'error'
                          });
                      }
                  });
              }
          });
      }
  </script>
<?= $this->endSection() ?>
