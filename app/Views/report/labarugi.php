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
        }
        .nilai1 {
            text-align: right;
            padding-left: 20px;
            width: 100px;
        }
        .indent-1 {
            padding-left: 15px;
        }
        .indent-2 {
            padding-left: 30px;
        }
        .indent-3 {
            padding-left: 45px;
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

		.hover-group {
		    align-items: center;
		}

		.hover-group a {
		    color: unset !important;
		    text-decoration: unset !important;
		    transition: color 0.3s ease-in-out, font-weight 0.3s ease-in-out;
		}

		.hover-group .nilai {
		    transition: color 0.3s ease-in-out, font-weight 0.3s ease-in-out;
		}

		.hover-group:hover a,
		.hover-group:hover .nilai {
		    color: blue !important;
		    font-weight: bold;
		}
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row mb-3 no-print page">
      <div class="col">
        <div class="card card-body">
          <form action="#" id="form-filter" class="form-horizontal">
            <div class="row">
              <!-- Dropdown Sumber -->
              <div class="col-2">
                <div class="form-group">
                  <select class="form-control" name="dbs" id="dbs" style="width: 100%">
                    <?php foreach ($dbs as $key => $row): ?>
                      <option value="<?=$row?>" <?=($row == $dbSel) ? "selected" : ""?>><?=$row?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <!-- Dropdown Bulan -->
              <div class="col-3">
                <div class="form-group">
                  <select class="form-control select2" name="bulan" id="bulan" style="width: 100%">
                    <?php foreach ($bln as $key => $value): ?>
                      <option value="<?=$key?>" <?=($key == $blnSel) ? "selected" : ""?>><?=$value?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <!-- Dropdown Tahun -->
              <div class="col-2">
                <div class="form-group">
                  <select class="form-control select2" name="tahun" id="tahun" style="width: 100%">
                    <?php for ($i = date('Y'); $i >= $startYear; $i--): ?>
                      <option value="<?=$i?>" <?=($i == $thnSel) ? "selected" : ""?>><?=$i?></option>
                    <?php endfor; ?>
                  </select>
                </div>
              </div>
              <div class="col">
                <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-eye"></i> <?=isLang('tampilkan')?></button>
                <button type="button" class="btn btn-light mb-2 btn-back"><i class="fa fa-rotate-left"></i> <?=isLang('dashboard')?></button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>     

	<div class="page">
	    <div class="header">
	        <div class="bold" style="float: left; font-size: 12px;"><?=$nmpt?></div>
	        <div class="header-right">
	            Tgl : <?=format_date(date('Y-m-d'))?><br>
	        </div>
	    </div>
	    <div class="clear"></div>

	    <div class="main-title bold" style="text-align: center; font-size: 14px;">LABA / RUGI</div>
	    <div class="bold" style="font-size: 12px;">Level : 3</div>
	    <div class="bold" style="font-size: 12px;">Periode : <?=$periode?></div>
	    <table>
	        <tr class="table-header">
	            <td style="text-align: center; border-right: 1px solid black;">R E K E N I N G</td>          
	            <td style="text-align: right; border-left: 1px solid black;">N I&nbsp;</td>
	            <td style="text-align: left;">L A I</td>
	        </tr>
	        <?php
	        $totalPend = 0;
			foreach ($akuns[4] as $akun) {
		    	$totalKdsub = 0;
			?>
			<tr>
				<td class="bold" colspan="2"><?=$akun['nmsub']?></td>
			</tr>
			<?php
			$totalKdsub = 0;
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
			            $totalKdsub += $totalGabungan;
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
			    <td class="nilai1 bold" style="border-top: 1px solid black;">
			        <?= formatNegatif($totalKdsub) ?>
			    </td>
			</tr>

			<?php $totalPend += $totalKdsub; } ?>

	        <tr>
	            <td class="bold">JUMLAH PENDAPATAN</td>
	            <td></td>
	            <td class="nilai1 bold" style="border-top: 1px solid black;"><?= formatNegatif($totalPend) ?></td>
	        </tr>

	        <tr><td colspan="2">&nbsp;</td></tr>

	        <tr>
	            <td class="bold">H P P</td>        
	        </tr>
	        <?php
	        $persdAwl = $lists['6']['650'][0]['nilai'] ?? 0;
	        ?>
	        <tr>
	            <td class="indent-1 bold">PERSEDIAAN AWAL</td>
	            <td class="nilai bold"><?= formatNegatif($persdAwl) ?></td>
		    </tr>
	        <tr>
	            <td class="indent-1 bold">PEMBELIAN</td>
		    </tr>

	        <?php
			foreach ($akuns[6] as $akun) {
		    	$totalKdsubBli = 0;
			?>
			<tr>
				<td class="bold" colspan="2"><?=$akun['nmsub']?></td>
			</tr>
			<?php
			$totalKdsubBli = 0;
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
			            $totalKdsubBli += $totalGabungan;
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
			    <td class="bold indent-1">TOTAL PEMBELIAN</td>
			    <td class="nilai1 bold" style="border-top: 1px solid black;">
			        <?= formatNegatif($totalKdsubBli) ?>
			    </td>
			</tr>
			<?php } ?>

	        <?php 
	        $persdAhr = $lists['6']['650'][1]['nilai'] ?? 0;
	        ?>
	        <tr>
	            <td class="indent-1 bold">PERSEDIAAN AKHIR</td>
	            <td class="nilai bold"><?= formatNegatif($persdAhr) ?></td>
	            <td>&nbsp; (-)</td>
		    </tr>

		    <?php
		    $hpp = ($persdAwl+$totalKdsubBli)-$persdAhr;
		    ?>
		    <tr>
	            <td class="bold">HPP PERIODE INI</td>
	            <td></td>
	            <td class="nilai1 bold" style="border-top: 1px solid black;"><?= formatNegatif($hpp) ?></td>
	            <td>(-)</td>
		    </tr>

	        <tr><td colspan="2">&nbsp;</td></tr>

		    <?php
		    $lbk = $totalPend-$hpp;
		    ?>
		    <tr style="border-top: 1px solid black;border-bottom: 1px solid black;">
	            <td class="bold">LABA / (RUGI) KOTOR</td>            
	            <td></td>
	            <td class="nilai1 bold"><?= formatNegatif($lbk) ?></td>
		    </tr>

	        <tr><td colspan="2">&nbsp;</td></tr>

	        <?php
	        $totalBya = 0;
			foreach ($akuns[5] as $akun) {
		    	$totalKdsubBy = 0;
			?>
			<tr>
				<td class="bold" colspan="2"><?=$akun['nmsub']?></td>
			</tr>
			<?php
			$totalKdsubBy = 0;
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
			            $totalKdsubBy += $totalGabungan;
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
			    <td class="nilai1 bold" style="border-top: 1px solid black;">
			        <?= formatNegatif($totalKdsubBy) ?>
			    </td>
			</tr>

			<?php $totalBya += $totalKdsubBy; } ?>

	        <tr>
	            <td class="bold">TOTAL BEBAN / BIAYA</td>
	            <td></td>
	            <td class="nilai1 bold" style="border-top: 1px solid black;"><?= formatNegatif($totalBya) ?></td>
	        </tr>

	        <tr><td colspan="2">&nbsp;</td></tr>

		    <?php
		    $lr = $lbk-$totalBya;
		    ?>
		    <tr style="border-top: 1px solid black;border-bottom: 1px solid black;">
	            <td class="bold">LABA / (RUGI) BERSIH</td>            
	            <td></td>
	            <td class="nilai1 bold"><?= formatNegatif($lr) ?></td>
		    </tr>

	    </table>
	</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$("#form-filter").submit(function(event) {
    event.preventDefault();  // Mencegah form dari reload halaman

    const formData = $("#form-filter").serializeArray();
    const params = {};

    formData.forEach(({ name, value }) => {
        if (value.trim() !== "") { // Pastikan input tidak kosong
            params[name] = value;
        }
    });

	let id = params['tahun']+'/'+params['bulan']+'/'+params['dbs'];      
	window.location.replace('<?=base_url('cms/report/labarugi')?>/'+id);
});

$(document).on('click', ".btn-back", function(event) {
  event.preventDefault();
  window.location.replace('<?=base_url('cms/dashboard')?>');
});
</script>

<?= $this->endSection() ?>