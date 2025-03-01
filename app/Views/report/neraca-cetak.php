<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Laba Rugi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            line-height: 1.3;
        }
        .page {
            width: 210mm;
            margin: auto;
            padding: 20px;
        }
        .header {
            margin-bottom: 15px;
        }
        .header-right {
            float: right;
            text-align: right;
        }
        .clear {
            clear: both;
        }
        .main-title {
            font-weight: bold;
            margin-bottom: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;            
        }
        .table-header {
            font-weight: bold;
            border-top: 1px solid black;
            border-bottom: 1px solid black;
        }
        .table-header td {
            padding: 3px 0;
        }
        td {
            padding: 1px 0;
            vertical-align: top;
        }
        .nilai {
            text-align: right;
            padding-left: 20px;
            padding-right: 5px;
        }
        .nilai2 {
            text-align: right;
            padding-left: 20px;     
        }
        .nilai1 {
            text-align: right;
            padding-left: 20px;
            width: 100px;
        }
        .indent-1 {
            padding-left: 10px;
        }
        .indent-2 {
            padding-left: 20px;
        }
        .indent-3 {
            padding-left: 30px;
        }
        .bold {
            font-weight: bold;
        }
        .negative {
            color: black;
        }
        a {
            color: unset !important;
            text-decoration: unset !important;
            transition: color 0.3s ease-in-out;
        }
        @media print {
            body {
                margin: 0;
            }
            thead {
                display: table-header-group;
            }
            .page-header {
                display: table-header-group;
            }
            .page-header-content {
                display: table-row;
            }
            .page-header-cell {
                display: table-cell;
                padding: 0 0 20px 0;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <table>
            <thead class="page-header">
                <tr class="page-header-content">
                    <td colspan="2" class="page-header-cell">
                        <div class="header">
                            <div class="bold" style="float: left; font-size: 12px;"><?=$nmpt?></div>
                            <div class="header-right">
                                Tgl : <?=format_date(date('Y-m-d'))?><br>
                            </div>
                        </div>
                        <div class="clear"></div>

                        <div class="main-title bold" style="text-align: center; font-size: 18px;">NERACA</div>
                        <div class="bold" style="font-size: 12px;">Level : 3</div>
                        <div class="bold" style="font-size: 12px;">Periode : <?=$periode?></div>
                    </td>
                </tr>
                <tr class="table-header">
                    <td style="text-align: center; width: 50%;">AKTIVA</td>
                    <td style="text-align: center; width: 50%; border-left: 1px solid black;">KEWAJIBAN DAN MODAL</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="width: 50%;">
                        <table>
                            <?php
                            $totalAL = 0;
                            foreach ($akuns[1] as $akun) {
                                $totalSubAL = 0;
                            ?>
                            <tr>
                                <td class="bold" colspan="2"><?=$akun['nmsub']?></td>
                            </tr>
                            <?php
                            $totalSubAL = 0;
                            if (!empty($lists[$akun['tipe']][$akun['kdsub']])):
                                foreach ($lists[$akun['tipe']][$akun['kdsub']] as $list) {
                                    $totalList = $list['nilai'];
                                    $totalRow = 0;
                                    $totalVal = 0;

                                    if (!empty($listsKe3[$list['kode_akun']])) {
                                        foreach ($listsKe3[$list['kode_akun']] as $row) {
                                            $totalRow += $row['nilai'];

                                            if (!empty($listsKe3[$row['kode_akun']])) {
                                                foreach ($listsKe3[$row['kode_akun']] as $val) {
                                                    $totalVal += $val['nilai'];
                                                }
                                            }
                                        }
                                    }

                                    $totalGabungan = $totalList + $totalRow + $totalVal;

                                    if ($totalGabungan != 0):
                                        $totalSubAL += $totalGabungan;
                            ?>

                            <tr class="hover-group">
                                <?php if(!empty($list['nilai'])){ ?>
                                <td class="indent-1"><?=$list['ket']?></td>
                                <td class="nilai"><?=formatNegatif($list['nilai'])?></td>
                                <?php }else{ ?>
                                <td class="indent-1 bold"><?=$list['ket']?></td>
                                <td class="nilai bold"><?=formatNegatif($totalGabungan)?></td>
                                <?php } ?>            
                            </tr>

                            <?php if (!empty($listsKe3[$list['kode_akun']])): ?>
                                <?php foreach ($listsKe3[$list['kode_akun']] as $row): ?>
                                    <?php if( abs($row['nilai']) >= 1 ): ?>
                                    <tr class="hover-group">
                                        <td class="indent-2"><?=$row['ket']?></td>
                                        <td class="nilai"><?=formatNegatif($row['nilai'])?></td>            
                                    </tr>
                                    <?php endif; ?>

                                    <?php if (!empty($listsKe3[$row['kode_akun']])): ?>
                                        <?php foreach ($listsKe3[$row['kode_akun']] as $val): ?>
                                            <?php if(!empty($val['nilai'])): ?>
                                            <tr class="hover-group">
                                                <td class="indent-3"><?=$val['ket']?></td>
                                                <td class="nilai"><?=formatNegatif($val['nilai'])?></td>
                                            </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php
                                    endif;
                                }
                            endif;
                            ?>

                            <tr>
                                <td class="bold">Jumlah <?=$akun['nmsub']?></td>
                                <td class="nilai bold" style="border-top: 1px solid black;">
                                    <?= formatNegatif($totalSubAL) ?>
                                </td>
                            </tr>

                            <tr><td colspan="2">&nbsp;</td></tr>

                            <?php $totalAL += $totalSubAL; } ?>
                        </table>
                    </td>
                    <td style="width: 50%; border-left: 1px solid black;">
                        <table>
                            <?php
                            $totalHL = 0;
                            foreach ($akuns[2] as $akun) {
                                $totalSubHL = 0;
                            ?>
                            <tr>
                                <td class="bold" colspan="2"><?=$akun['nmsub']?></td>
                            </tr>
                            <?php
                            $totalSubHL = 0;
                            if (!empty($lists[$akun['tipe']][$akun['kdsub']])):
                                foreach ($lists[$akun['tipe']][$akun['kdsub']] as $list) {
                                    $totalList = $list['nilai'];
                                    $totalRow = 0;
                                    $totalVal = 0;

                                    if (!empty($listsKe3[$list['kode_akun']])) {
                                        foreach ($listsKe3[$list['kode_akun']] as $row) {
                                            $totalRow += $row['nilai'];

                                            if (!empty($listsKe3[$row['kode_akun']])) {
                                                foreach ($listsKe3[$row['kode_akun']] as $val) {
                                                    $totalVal += $val['nilai'];
                                                }
                                            }
                                        }
                                    }

                                    $totalGabungan = $totalList + $totalRow + $totalVal;

                                    if ($totalGabungan != 0):
                                        $totalSubHL += $totalGabungan;
                            ?>

                            <tr class="hover-group">
                                <?php if(!empty($list['nilai'])){ ?>
                                <td class="indent-1"><?=$list['ket']?></td>
                                <td class="nilai"><?=formatNegatif($list['nilai'])?></td>
                                <?php }else{ ?>
                                <td class="indent-1 bold"><?=$list['ket']?></td>
                                <td class="nilai bold"><?=formatNegatif($totalGabungan)?></td>
                                <?php } ?>            
                            </tr>

                            <?php if (!empty($listsKe3[$list['kode_akun']])): ?>
                                <?php foreach ($listsKe3[$list['kode_akun']] as $row): ?>
                                    <?php if( abs($row['nilai']) >= 1 ): ?>
                                    <tr class="hover-group">
                                        <td class="indent-2"><?=$row['ket']?></td>
                                        <td class="nilai"><?=formatNegatif($row['nilai'])?></td>            
                                    </tr>
                                    <?php endif; ?>

                                    <?php if (!empty($listsKe3[$row['kode_akun']])): ?>
                                        <?php foreach ($listsKe3[$row['kode_akun']] as $val): ?>
                                            <?php if(!empty($val['nilai'])): ?>
                                            <tr class="hover-group">
                                                <td class="indent-3"><?=$val['ket']?></td>
                                                <td class="nilai"><?=formatNegatif($val['nilai'])?></td>
                                            </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php
                                    endif;
                                }
                            endif;
                            ?>

                            <tr>
                                <td class="bold">&nbsp; Jumlah <?=$akun['nmsub']?></td>
                                <td class="nilai bold" style="border-top: 1px solid black;">
                                    <?= formatNegatif($totalSubHL) ?>
                                </td>
                            </tr>

                            <tr><td colspan="2">&nbsp;</td></tr>

                            <?php $totalHL += $totalSubHL; } ?>


                            <?php
                            $totalMD = 0;
                            foreach ($akuns[3] as $akun) {
                                $totalSubMD = 0;
                            ?>
                            <tr>
                                <td class="bold" colspan="2"><?=$akun['nmsub']?></td>
                            </tr>
                            <?php
                            $totalSubMD = 0;
                            if (!empty($lists[$akun['tipe']][$akun['kdsub']])):
                                foreach ($lists[$akun['tipe']][$akun['kdsub']] as $list) {
                                    $totalList = $list['nilai'];
                                    $totalRow = 0;
                                    $totalVal = 0;

                                    if (!empty($listsKe3[$list['kode_akun']])) {
                                        foreach ($listsKe3[$list['kode_akun']] as $row) {
                                            $totalRow += $row['nilai'];

                                            if (!empty($listsKe3[$row['kode_akun']])) {
                                                foreach ($listsKe3[$row['kode_akun']] as $val) {
                                                    $totalVal += $val['nilai'];
                                                }
                                            }
                                        }
                                    }

                                    $totalGabungan = $totalList + $totalRow + $totalVal;

                                    if ($totalGabungan != 0):
                                        $totalSubMD += $totalGabungan;
                            ?>

                            <tr class="hover-group">
                                <?php if(!empty($list['nilai'])){ ?>
                                <td class="indent-1"><?=$list['ket']?></td>
                                <td class="nilai"><?=formatNegatif($list['nilai'])?></td>
                                <?php }else{ ?>
                                <td class="indent-1 bold"><?=$list['ket']?></td>
                                <td class="nilai bold"><?=formatNegatif($totalGabungan)?></td>
                                <?php } ?>            
                            </tr>

                            <?php if (!empty($listsKe3[$list['kode_akun']])): ?>
                                <?php foreach ($listsKe3[$list['kode_akun']] as $row): ?>
                                    <?php if( abs($row['nilai']) >= 1 ): ?>
                                    <tr class="hover-group">
                                        <td class="indent-2"><?=$row['ket']?></td>
                                        <td class="nilai"><?=formatNegatif($row['nilai'])?></td>            
                                    </tr>
                                    <?php endif; ?>

                                    <?php if (!empty($listsKe3[$row['kode_akun']])): ?>
                                        <?php foreach ($listsKe3[$row['kode_akun']] as $val): ?>
                                            <?php if(!empty($val['nilai'])): ?>
                                            <tr class="hover-group">
                                                <td class="indent-3"><?=$val['ket']?></td>
                                                <td class="nilai"><?=formatNegatif($val['nilai'])?></td>
                                            </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php
                                    endif;
                                }
                            endif;
                            ?>

                            <tr>
                                <td class="bold">&nbsp; Jumlah <?=$akun['nmsub']?></td>
                                <td class="nilai bold" style="border-top: 1px solid black;">
                                    <?= formatNegatif($totalSubMD) ?>
                                </td>
                            </tr>

                            <?php $totalMD += $totalSubMD; } ?>
                            <tr>
                                <td class="bold">&nbsp; LABA / ( RUGI ) TAHUN BERJALAN</td>
                                <td class="nilai bold" style="border-top: 1px solid black;">
                                    <?= formatNegatif($lrtb) ?>
                                </td>
                            </tr>
                            <tr><td colspan="2">&nbsp;</td></tr>
                        </table>
                    </td>
                </tr>

                <tr style="border-top: 1px solid black;border-bottom: 1px solid black;">
                    <td style="width: 50%;">
                        <table>
                            <tr>
                                <td class="bold">TOTAL AKTIVA</td>
                                <td class="nilai bold"><?= formatNegatif($totalAL) ?></td>            
                            </tr>   
                        </table>
                    </td>
                    <?php $totmodal = $totalHL+$totalMD+$lrtb; ?>
                    <td style="width: 50%; border-left: 1px solid black;">
                        <table>
                            <tr>
                                <td class="bold">TOTAL KEWAJIBAN & MODAL</td>
                                <td class="nilai2 bold"><?= formatNegatif($totmodal) ?></td>           
                            </tr>           
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>

<script>
window.onload = function () {
    setTimeout(function () {
        window.print();
    }, 500);
};

window.onafterprint = function () {
    setTimeout(function () {
        window.close();
    }, 500);
};
</script>