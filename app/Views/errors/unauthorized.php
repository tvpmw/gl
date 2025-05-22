<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle text-warning display-1 mb-4"></i>
                    <h2>Unauthorized Access</h2>
                    <p class="lead">You don't have permission to access this page.</p>
                    <a href="<?= base_url() ?>" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>