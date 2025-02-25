<style>
    .table th, .table td { text-align: right; }
    .table th:nth-child(1), .table td:nth-child(1),
    .table th:nth-child(2), .table td:nth-child(2),
    .table th:nth-child(3), .table td:nth-child(3) {
        text-align: left;
    }
</style>


<div class="card shadow">
    <div class="card-header text-center">
        <h4 class="mb-0">BUKU BESAR</h4>
        <h6 class="mb-0"><?=$nmpt?></h6>
        <h6 class="mb-0 text-primary"><?=$periode?></h6>
    </div>
    <div class="card-body">
        <h6 class="mt-3"><strong>Kode Perkiraan: <?=$akun->KDCOA ?? ''?> - <?=$akun->NMCOA ?? ''?></strong></h6>
        <div class="table-responsive">
            <table class="table table-bordered mt-3">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Kode Jurnal</th>
                        <th>Keterangan</th>
                        <th>Debet</th>
                        <th>Kredit</th>
                        <th>Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $saldoAwal = ($lists[0]['jvdebet'] ?? 0) - ($lists[0]['jvkredit'] ?? 0);
                    $saldo = 0;
                    $totalDebet = 0;
                    $totalKredit = 0;

                    foreach ($lists as $key => $row) { 
                        $debet = $row['jvdebet'];
                        $kredit = $row['jvkredit'];
                        $saldo = ($saldo + $debet) - $kredit;

                        $totalDebet += $debet;
                        $totalKredit += $kredit;
                    ?>
                    <tr>
                        <td><?= format_date($row['tgl']) ?></td>
                        <td><?= $row['kdjv'] ?></td>
                        <td><?= $row['ket'] ?></td>
                        <td><?= formatNegatif($debet) ?></td>
                        <td><?= formatNegatif($kredit) ?></td>
                        <td><?= formatNegatif($saldo) ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="3" class="text-end">SALDO AKHIR</th>
                        <th class="text-end"><?= formatNegatif($totalDebet) ?></th>
                        <th class="text-end"><?= formatNegatif($totalKredit) ?></th>
                        <th class="text-end"><?= formatNegatif($saldo) ?></th>
                    </tr>
                    <tr>
                        <th colspan="3" class="text-end">MUTASI</th>
                        <th colspan="2"></th>
                        <th class="text-end"><?= formatNegatif($saldo - $saldoAwal) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="card-footer text-end">
        <small class="text-muted">24-Feb-2025 11:03</small>
    </div>
</div>
