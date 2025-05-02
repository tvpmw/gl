<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<style>
    .btn-check:checked+.btn, .btn.active, .btn.show, .btn:first-child:active, :not(.btn-check)+.btn:active {
        color: var(--bs-btn-active-color);
        background-color: #4f46e5;
        border-color: #4f46e5;
    }
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    .card-header {
        background: linear-gradient(135deg, #0d6efd, #0dcaf0);
        color: white;
        border-radius: 15px 15px 0 0 !important;
        padding: 1.5rem;
    }
    .form-control {
        border-radius: 10px;
        padding: 0.75rem;
        border: 1px solid #dee2e6;
    }
    .form-control:focus {
        box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.15);
    }
    .btn-outline-primary {
        --bs-btn-color: #4f46e5;
        --bs-btn-border-color: #4f46e5;
        --bs-btn-hover-color: #fff;
        --bs-btn-hover-bg: #4f46e5;
        --bs-btn-hover-border-color: #4f46e5;
        --bs-btn-focus-shadow-rgb: 13, 110, 253;
        --bs-btn-active-color: #fff;
        --bs-btn-active-bg: #4f46e5;
        --bs-btn-active-border-color: #4f46e5;
        --bs-btn-active-shadow: inset 0 3px 5px rgba(0, 0, 0, 0.125);
        --bs-btn-disabled-color: #4f46e5;
        --bs-btn-disabled-bg: transparent;
        --bs-btn-disabled-border-color: #4f46e5;
        --bs-gradient: none;
    }
    .loading {
        display: none;
    }
    .loading.active {
        display: block;
    }
    .result-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 500;
    }
    .badge-valid {
        background-color: #198754 !important;
        color: white;
    }
    .badge-not-valid {
        background-color: #dc3545;
        color: white;
    }
    .text-muted {
    color: #6c757d !important;
    }
    .d-block {
        display: block !important;
    }
    .mt-1 {
        margin-top: 0.25rem !important;
    }
    small {
        font-size: 0.9em;
        line-height: 1.4;
    }
    .status-spt-wrapper,
    #status_spt_wrapper {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .result-badge {
        width: fit-content;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="text-center mb-0">
                        <i class="fas fa-id-card me-2"></i>NPWP Checker
                    </h4>
                </div>
                <div class="card-body p-4">
                    <!-- Mode Selection -->
                    <div class="mb-4">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="mode" id="singleMode" checked>
                            <label class="btn btn-outline-primary" for="singleMode">
                                <i class="fas fa-user me-2"></i>Single Check
                            </label>
                            <input type="radio" class="btn-check" name="mode" id="bulkMode">
                            <label class="btn btn-outline-primary" for="bulkMode">
                                <i class="fas fa-users me-2"></i>Bulk Check
                            </label>
                            <input type="radio" class="btn-check" name="mode" id="nitkuMode">
                            <label class="btn btn-outline-primary" for="nitkuMode">
                                <i class="fas fa-building me-2"></i>NITKU Check
                            </label>
                        </div>
                    </div>

                    <!-- Single Form -->
                    <form id="singleForm">
                        <div class="mb-4">
                            <label for="npwp" class="form-label">Nomor NPWP</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-hashtag"></i>
                                </span>
                                <input type="text" class="form-control" id="npwp" name="npwp" 
                                       placeholder="Contoh: 331012700960000" required>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Masukkan 16 digit NPWP tanpa tanda baca
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Cek NPWP
                        </button>
                    </form>

                    <!-- Nitku Form -->
                    <form id="nitkuForm" class="d-none">
                        <div class="mb-4">
                            <label for="npwp_nitku" class="form-label">Nomor NPWP</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-hashtag"></i>
                                </span>
                                <input type="text" class="form-control" id="npwp_nitku" name="npwp" 
                                    placeholder="Contoh: 331012700960000" required>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Masukkan 16 digit NPWP tanpa tanda baca
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Cek NITKU
                        </button>
                    </form>

                    <!-- Bulk Form -->
                    <form id="bulkForm" class="d-none">
                        <div class="mb-4">
                            <label for="npwp_list" class="form-label">Daftar NPWP</label>
                            <textarea class="form-control" id="npwp_list" name="npwp_list" rows="4" 
                                placeholder="Masukkan NPWP (pisahkan dengan koma atau baris baru)" required></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Masukkan NPWP yang dipisahkan dengan koma atau baris baru
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Cek NPWP
                        </button>
                    </form>

                    <!-- Loading Indicator -->
                    <div class="loading text-center mt-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <!-- Results Sections -->
                    <?= $this->include('npwp/partials/single_result') ?>
                    <?= $this->include('npwp/partials/bulk_result') ?>
                    <?= $this->include('npwp/partials/nitku_result') ?>

                    <!-- Error Message -->
                    <div class="alert alert-danger mt-4 d-none" id="error_message"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Add your JavaScript code here
$(document).ready(function() {
    // Mode switching logic
    $('input[name="mode"]').change(function() {
        const mode = $(this).attr('id');
        $('#singleForm, #bulkForm, #nitkuForm').addClass('d-none');
        $('#singleResult, #bulkResult, #nitkuResult').addClass('d-none');
        
        switch(mode) {
            case 'singleMode':
                $('#singleForm').removeClass('d-none');
                break;
            case 'bulkMode':
                $('#bulkForm').removeClass('d-none');
                break;
            case 'nitkuMode':
                $('#nitkuForm').removeClass('d-none');
                break;
        }
    });

    // Form submissions
    $('#singleForm').submit(function(e) {
        e.preventDefault();
        handleSingleSubmit();
    });

    $('#bulkForm').submit(function(e) {
        e.preventDefault();
        handleBulkSubmit();
    });

    $('#nitkuForm').submit(function(e) {
        e.preventDefault();
        handleNitkuSubmit();
    });

    // NPWP input formatting
    $('#npwp, #npwp_nitku').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length > 16) value = value.substr(0, 16);
        $(this).val(value);
    });
});

function parseSptStatus(fullStatus) {
    if (!fullStatus || fullStatus === '-') return { status: '-', reason: '' };
    
    // Jika mengandung kata VALID (case insensitive), anggap valid
    const isValid = fullStatus.toLowerCase().includes('valid') && 
                   !fullStatus.toLowerCase().includes('not valid') && 
                   !fullStatus.toLowerCase().includes('tidak valid');
    
    // Split status dan reason
    const parts = fullStatus.split(/\.(.+)/);
    
    return {
        status: parts[0].trim(),
        reason: parts[1] ? parts[1].trim() : '',
        isValid: isValid
    };
}

function handleSingleSubmit() {
    $('.loading').addClass('active');
    $('#error_message').addClass('d-none');
    $('#singleResult .table-responsive').addClass('d-none');

    $.ajax({
        url: '<?= base_url('cms/npwp/check-single') ?>',
        method: 'POST',
        data: $('#singleForm').serialize(),
        success: function(response) {
            if (response.error) {
                $('#error_message')
                    .removeClass('d-none')
                    .text(response.error);
                return;
            }

            // Update result table
            $('#npwp_result').text(response.data.npwp);
            $('#name').text(response.data.name);
            $('#address').text(response.data.address);
            
            // Update WP status badge
            let wpStatus = response.data.status_wp || '-';
            let wpBadgeClass = wpStatus.toLowerCase().includes('valid') || 
                            wpStatus.toLowerCase().includes('aktif') ? 
                            'badge-valid' : 'badge-not-valid';
            
            $('#status_wp')
                .text(wpStatus)
                .removeClass('badge-valid badge-not-valid')
                .addClass(wpBadgeClass);
            
           // Update SPT status badge dengan pemisahan status dan reason
            let sptFullStatus = response.data.status_spt || '-';
            let { status: sptStatus, reason: sptReason, isValid } = parseSptStatus(sptFullStatus);
            
            // Set badge class berdasarkan isValid
            let sptBadgeClass = isValid ? 'badge-valid' : 'badge-not-valid';
            
            $('#status_spt')
                .text(sptStatus)
                .removeClass('badge-valid badge-not-valid')
                .addClass(sptBadgeClass);

            // Update reason terpisah
            $('#status_spt_reason').text(sptReason || '');

            // Tampilkan alasan status jika ada
            if (response.data.status_wp_reason) {
                $('#status_wp_reason').text(response.data.status_wp_reason);
            }
            if (response.data.status_spt_reason) {
                $('#status_spt_reason').text(response.data.status_spt_reason);
            }

            $('#singleResult .table-responsive').removeClass('d-none');
        },
        error: function(xhr) {
            $('#error_message')
                .removeClass('d-none')
                .text('Terjadi kesalahan saat memproses permintaan');
        },
        complete: function() {
            $('.loading').removeClass('active');
        }
    });
}

function handleBulkSubmit() {
    $('.loading').addClass('active');
    $('#error_message').addClass('d-none');
    $('#bulkResult').addClass('d-none');

    $.ajax({
        url: '<?= base_url('cms/npwp/check-bulk') ?>',
        method: 'POST',
        data: $('#bulkForm').serialize(),
        success: function(response) {
            const tbody = $('#resultsTableBody');
            tbody.empty();

            response.forEach(function(item) {
                if (item.error) {
                    tbody.append(`
                        <tr>
                            <td>${item.npwp}</td>
                            <td colspan="5" class="text-danger">${item.error}</td>
                        </tr>
                    `);
                    return;
                }

                const wpStatus = item.data.status_wp || '-';
                const sptFullStatus = item.data.status_spt || '-';
                const { status: sptStatus, reason: sptReason, isValid } = parseSptStatus(sptFullStatus);
                
                const sptBadgeClass = isValid ? 'badge-valid' : 'badge-not-valid';
                
                // Logika yang diperbaiki untuk status WP
                const wpBadgeClass = wpStatus.toLowerCase().includes('valid') || 
                                   wpStatus.toLowerCase().includes('aktif') ? 
                                   'badge-valid' : 'badge-not-valid';                
                
                // Inside handleBulkSubmit() function, update the append section
                tbody.append(`
                <tr>
                    <td>${item.data.npwp}</td>
                    <td>${item.data.status || '-'}</td>
                    <td>${item.data.name || '-'}</td>
                    <td>${item.data.address || '-'}</td>
                    <td>
                        <span class="result-badge ${wpBadgeClass}">${wpStatus}</span>
                        ${item.data.status_wp_reason ? `<small class="text-muted d-block mt-1">${item.data.status_wp_reason}</small>` : ''}
                    </td>
                    <td>
                        <div class="status-spt-wrapper">
                            <span class="result-badge ${sptBadgeClass}">${sptStatus}</span>
                            ${sptReason ? `<small class="text-muted d-block mt-1">${sptReason}</small>` : ''}
                        </div>
                    </td>
                </tr>
            `);
        });

            $('#bulkResult').removeClass('d-none');
        },
        error: function(xhr) {
            $('#error_message')
                .removeClass('d-none')
                .text('Terjadi kesalahan saat memproses permintaan');
        },
        complete: function() {
            $('.loading').removeClass('active');
        }
    });
}

function handleNitkuSubmit() {
    $('.loading').addClass('active');
    $('#error_message').addClass('d-none');
    $('#nitkuResult .table-responsive').addClass('d-none');

    $.ajax({
        url: '<?= base_url('cms/npwp/check-nitku') ?>',
        method: 'POST',
        data: $('#nitkuForm').serialize(),
        success: function(response) {
            console.log('NITKU Response:', response); // Debug log

            if (response.error) {
                $('#error_message')
                    .removeClass('d-none')
                    .text(response.error);
                return;
            }

            // Update NITKU result table
            $('#nitku_message').text(response.message || '-');
            
            if (response.data) {
                $('#npwp15_result').text(response.data.npwp15 || '-');
                $('#npwp16_result').text(response.data.npwp16 || '-');
                $('#nitku').text(response.data.nitku || '-');
                $('#taxpayer_name').text(response.data.taxpayer_name || '-');
                $('#taxpayer_status').text(response.data.taxpayer_status || '-');
                $('#npwp_type').text(response.data.npwp_type || '-');
                
                // Add this line to ensure the result becomes visible
                $('#nitkuResult').removeClass('d-none');
                $('#nitkuResult .table-responsive').removeClass('d-none');
            }
        },
        error: function(xhr) {
            $('#error_message')
                .removeClass('d-none')
                .text('Terjadi kesalahan saat memproses permintaan');
        },
        complete: function() {
            $('.loading').removeClass('active');
        }
    });
}
</script>
<?= $this->endSection() ?>