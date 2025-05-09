<style>
    .table-responsive {
        max-height: 500px;
        overflow-y: auto;
    }
    .table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .catatan {
        color: darkslategray;
    }
</style>

<div class="table-responsive">
    <table class="table table-bordered">
        <thead class="table-light text-center">
            <tr>
                <th>Tanggal</th>
                <th>No. Bukti</th>
                <th>Kode Rekening</th>
                <th>Nama Rekening</th>
                <th>Debet</th>
                <th>Kredit</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($lists)): foreach ($lists as $jurnal): ?>
                <?php 
                    $rowspan = count($jurnal['rincian']);
                    $totalDebet = 0;
                    $totalKredit = 0;
                ?>
                <?php foreach ($jurnal['rincian'] as $index => $rincian): ?>
                    <tr>
                        <?php if ($index === 0): ?>
                            <td rowspan="<?= $rowspan ?>"><?= format_date(($jurnal['tgljv'])) ?></td>
                            <td rowspan="<?= $rowspan ?>">
                                <?= $jurnal['kdjv'] ?>
                                <br>
                                <span class="catatan small"><?= $jurnal['ketjv'] ?></span>
                            </td>
                        <?php endif; ?>
                        <td><?= $rincian['kdcoa'] ?></td>
                        <td>
                            <?= $rincian['nmcoa'] ?>
                            <br>
                            <span class="catatan small"><?= $rincian['ket'] ?></span>
                        </td>
                        <td class="text-end"><?= number_format($rincian['jvdebet'], 2, ',', '.') ?></td>
                        <td class="text-end"><?= number_format($rincian['jvkredit'], 2, ',', '.') ?></td>
                    </tr>
                    <?php 
                        $totalDebet += $rincian['jvdebet'];
                        $totalKredit += $rincian['jvkredit'];
                    ?>
                <?php endforeach; ?>
                <tr style="border-bottom: 3px solid gray;">
                    <td colspan="4" class="fw-bold text-center">Total</td>
                    <td class="text-end fw-bold"><?= number_format($totalDebet, 2, ',', '.') ?></td>
                    <td class="text-end fw-bold"><?= number_format($totalKredit, 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; else: ?>
            <tr>
                <td colspan="6">Tidak ada data</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
