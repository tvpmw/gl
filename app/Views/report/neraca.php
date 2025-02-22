<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            line-height: 1.3;
        }
        .page {
        	background-color: white;
            width: 297mm;
            margin: 0 auto;
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
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
	<div class="page">
	    <div class="header">
	        <div class="bold" style="float: left; font-size: 12px;"><?=$nmpt?></div>
	        <div class="header-right">
	            Tgl : <?=format_date(date('Y-m-d'))?><br>
	        </div>
	    </div>
	    <div class="clear"></div>

	    <div class="main-title bold" style="text-align: center; font-size: 14px;">NERACA</div>
	    <div class="bold" style="font-size: 12px;">Level : 3</div>
	    <div class="bold" style="font-size: 12px;">Periode : <?=$periode?></div>
	    <table>
			<tr class="table-header">
				<td style="text-align: center; width: 50%;">AKTIVA</td>
				<td style="text-align: center; width: 50%; border-left: 1px solid black;">KEWAJIBAN DAN MODAL</td>
			</tr>
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
						    if (!empty($lists[$akun['tipe']][$akun['kdsub']])):
						        foreach ($lists[$akun['tipe']][$akun['kdsub']] as $list) {
						            $totalSubAL += $list['nilai'];
						?>
						<tr>
						    <td class="indent-1 <?=(empty($list['nilai']))?'bold':''?>"><?=$list['kode_akun'].' '.$list['nama_akun']?></td>
						    <td class="nilai <?=(empty($list['nilai']))?'bold':''?>"><?=(!empty($list['nilai']))?formatNegatif($list['nilai']):''?></td>            
						</tr>
						<?php
			            if (!empty($listsKe3[$list['kode_akun']])):
			                foreach ($listsKe3[$list['kode_akun']] as $row) {
			                    $totalSubAL += $row['nilai'];
						?>
						<tr>
						    <td class="indent-2"><?=$row['kode_akun'].' '.$row['nama_akun']?></td>
						    <td class="nilai"><?=(!empty($row['nilai']))?formatNegatif($row['nilai']):'0,00'?></td>            
						</tr>
						<?php
			            if (!empty($listsKe3[$row['kode_akun']])):
			                foreach ($listsKe3[$row['kode_akun']] as $val) {
			                    $totalSubAL += $val['nilai'];
						?>
						<tr>
						    <td class="indent-3"><?=$val['kode_akun'].' '.$val['nama_akun']?></td>
						    <td class="nilai"><?=(!empty($val['nilai']))?formatNegatif($val['nilai']):0?></td>
						</tr>
						<?php } endif; ?>
						<?php } endif; ?>
						<?php } endif; ?>

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
							<td class="bold" colspan="2">&nbsp; <?=$akun['nmsub']?></td>
						</tr>
						<?php
						    if (!empty($lists[$akun['tipe']][$akun['kdsub']])):
						        foreach ($lists[$akun['tipe']][$akun['kdsub']] as $list) {
						            $totalSubHL += $list['nilai'];
						?>
						<tr>
						    <td class="indent-1 <?=(empty($list['nilai']))?'bold':''?>"><?=$list['kode_akun'].' '.$list['nama_akun']?></td>
						    <td class="nilai <?=(empty($list['nilai']))?'bold':''?>"><?=(!empty($list['nilai']))?formatNegatif($list['nilai']):''?></td>            
						</tr>
						<?php
			            if (!empty($listsKe3[$list['kode_akun']])):
			                foreach ($listsKe3[$list['kode_akun']] as $row) {
			                    $totalSubHL += $row['nilai'];
						?>
						<tr>
						    <td class="indent-2"><?=$row['kode_akun'].' '.$row['nama_akun']?></td>
						    <td class="nilai"><?=(!empty($row['nilai']))?formatNegatif($row['nilai']):'0,00'?></td>            
						</tr>
						<?php
			            if (!empty($listsKe3[$row['kode_akun']])):
			                foreach ($listsKe3[$row['kode_akun']] as $val) {
			                    $totalSubHL += $val['nilai'];
						?>
						<tr>
						    <td class="indent-3"><?=$val['kode_akun'].' '.$val['nama_akun']?></td>
						    <td class="nilai"><?=(!empty($val['nilai']))?formatNegatif($val['nilai']):0?></td>
						</tr>
						<?php } endif; ?>
						<?php } endif; ?>
						<?php } endif; ?>

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
							<td class="bold" colspan="2">&nbsp; <?=$akun['nmsub']?></td>
						</tr>
						<?php
						    if (!empty($lists[$akun['tipe']][$akun['kdsub']])):
						        foreach ($lists[$akun['tipe']][$akun['kdsub']] as $list) {
						            $totalSubMD += $list['nilai'];
						?>
						<tr>
						    <td class="indent-1 <?=(empty($list['nilai']))?'bold':''?>"><?=$list['kode_akun'].' '.$list['nama_akun']?></td>
						    <td class="nilai <?=(empty($list['nilai']))?'bold':''?>"><?=(!empty($list['nilai']))?formatNegatif($list['nilai']):''?></td>            
						</tr>
						<?php
			            if (!empty($listsKe3[$list['kode_akun']])):
			                foreach ($listsKe3[$list['kode_akun']] as $row) {
			                    $totalSubMD += $row['nilai'];
						?>
						<tr>
						    <td class="indent-2"><?=$row['kode_akun'].' '.$row['nama_akun']?></td>
						    <td class="nilai"><?=(!empty($row['nilai']))?formatNegatif($row['nilai']):'0,00'?></td>            
						</tr>
						<?php
			            if (!empty($listsKe3[$row['kode_akun']])):
			                foreach ($listsKe3[$row['kode_akun']] as $val) {
			                    $totalSubMD += $val['nilai'];
						?>
						<tr>
						    <td class="indent-3"><?=$val['kode_akun'].' '.$val['nama_akun']?></td>
						    <td class="nilai"><?=(!empty($val['nilai']))?formatNegatif($val['nilai']):0?></td>
						</tr>
						<?php } endif; ?>
						<?php } endif; ?>
						<?php } endif; ?>

						<tr>
						    <td class="bold">&nbsp; Jumlah <?=$akun['nmsub']?></td>
						    <td class="nilai bold" style="border-top: 1px solid black;">
						        <?= formatNegatif($totalSubMD) ?>
						    </td>
						</tr>

						<tr><td colspan="2">&nbsp;</td></tr>

						<?php $totalMD += $totalSubMD; } ?>
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
				<?php $totmodal = $totalHL+$totalMD; ?>
				<td style="width: 50%; border-left: 1px solid black;">
					<table>
						<tr>
							<td class="bold">TOTAL KEWAJIBAN & MODAL</td>
							<td class="nilai2 bold"><?= formatNegatif($totmodal) ?></td>           
						</tr>			
					</table>
				</td>
			</tr>
	    </table>
	</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?= $this->endSection() ?>