<?php
$saldo_sebelumnya = 0;
$penambahan_modal = 0;
$pengurangan_modal = 0;
?>

<style>
    .container-lap {
        margin: auto;
        border: 1px solid #000;
        padding: 20px;
    }
    .header {
        text-align: center;
        font-weight: bold;
        margin-bottom: 20px;
    }
    .section {
        margin-bottom: 15px;
    }
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    .table td {
        padding: 5px;
    }
    .text-end {
        text-align: right;
    }
    .text-center {
        text-align: center;
    }
    .bold {
        font-weight: bold;
    }
    .border-top {
        border-top: 1px solid #000;
    }
</style>

<div class="container-lap">
    <div class="header">
        <h3><?= $nmpt; ?></h3>
        <h4>LAPORAN PERUBAHAN MODAL</h4>
        <p>Untuk Periode Yang Berakhir : <?= $periode_pilih; ?></p>
        <hr>
    </div>

    <!-- SALDO SEBELUMNYA -->
    <div class="section">
        <h4>SALDO 31 <?= $periode_lalu; ?></h4>
        <table class="table">
            <tbody>
                <?php foreach ($lists as $item): 
                    $saldo_sebelumnya += $item['total'];
                ?>
                <tr>
                    <td><?= $item['akun']; ?></td>
                    <td class="text-end"><?= formatNegatif($item['total'], 2, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="bold">
                    <td>Total Saldo 31 <?= $periode_lalu; ?></td>
                    <td class="text-end"><?= formatNegatif($saldo_sebelumnya, 2, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- PENAMBAHAN / PENGURANGAN MODAL -->
    <?php 
    if ($lrtb > 0) {
        $penambahan_modal = $lrtb;
    } else {
        $pengurangan_modal = abs($lrtb);
    }
    ?>

    <div class="section">
        <h4>PENAMBAHAN MODAL</h4>
        <table class="table">
            <?php if($penambahan_modal>0): ?>
            <tbody>
                <tr>
                    <td>Kenaikan</td>
                    <td class="text-end"><?= formatNegatif($penambahan_modal, 2, ',', '.'); ?></td>
                </tr>
            </tbody>
            <?php endif; ?>
            <tfoot>
                <tr class="bold">
                    <td>Total Penambahan Modal</td>
                    <td class="text-end"><?= formatNegatif($penambahan_modal, 2, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="section">
        <h4>PENGURANGAN MODAL</h4>
        <table class="table">
            <?php if($penambahan_modal<0): ?>
            <tbody>
                <tr>
                    <td>Penurunan</td>
                    <td class="text-end"><?= formatNegatif($pengurangan_modal, 2, ',', '.'); ?></td>
                </tr>
            </tbody>
            <?php endif; ?>
            <tfoot>
                <tr class="bold">
                    <td>Total Pengurangan Modal</td>
                    <td class="text-end"><?= formatNegatif($pengurangan_modal, 2, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- SALDO AKHIR -->
    <?php 
    $saldo_akhir = $saldo_sebelumnya + $penambahan_modal - $pengurangan_modal;
    ?>

    <div class="section border-top">
        <table class="table">
            <tfoot>
                <tr class="bold">
                    <td>SALDO 31 <?= $periode_pilih; ?></td>
                    <td class="text-end"><?= formatNegatif($saldo_akhir, 2, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
