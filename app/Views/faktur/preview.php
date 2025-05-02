<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Preview Data Faktur</h1>
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Data Faktur: <?= $bulan_array[$bulan] ?? '' ?> <?= $tahun ?> - <?= $sales_type ?>
                </div>
                <form action="<?= base_url('cms/faktur/generate_excel') ?>" method="post">
                    <input type="hidden" name="bulan" value="<?= $bulan ?>">
                    <input type="hidden" name="tahun" value="<?= $tahun ?>">
                    <input type="hidden" name="sales_type" value="<?= $sales_type ?>">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-file-excel me-2"></i>Generate Excel
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Faktur</th>
                            <th>Tanggal</th>
                            <th>Customer</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">PPN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($faktur_data)) : ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data yang ditemukan</td>
                            </tr>
                        <?php else : ?>
                            <?php $no = 1; foreach ($faktur_data as $row) : ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= esc($row['nomor_faktur']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_faktur'])) ?></td>
                                    <td><?= esc($row['nama_customer']) ?></td>
                                    <td class="text-end"><?= number_format($row['total_amount'], 2) ?></td>
                                    <td class="text-end"><?= number_format($row['ppn_amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>