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
            width: auto;
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
	        <div class="bold" style="float: left; font-size: 12px;">nmpt</div>
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
			<td colspan="2" style="text-align: center; width: 50%; border-right: 1px solid black;">AKTIVA</td>
			<td colspan="2" style="text-align: center; width: 50%; border-left: 1px solid black;">KEWAJIBAN DAN MODAL</td>
		</tr>
			<tr>
			    <td class="bold">AKTIVA LANCAR</td>
			    <td style="border-right: 1px solid black;"></td>
				<td class="bold">HUTANG LANCAR</td>
			</tr>					
			<tr>
			    <td class="indent-1 bold">101.000 Kas</td>
			    <td class="nilai bold" style="border-right: 1px solid black;">24.672.452,00</td>            
				<td class="indent-1">301.000 Hutang Dagang</td>
				<td class="nilai">3.873.906.050,14</td>           
			</tr>			
			<tr>
			    <td class="indent-2">101.001 KAS KELUAR</td>
			    <td class="nilai" style="border-right: 1px solid black;">3.262.894,00</td>
				<td class="indent-1 bold">302.000 Hutang Lain-lain</td>
				<td class="nilai bold">18.762.830.861,43</td> 
			</tr>			
			<tr>
			    <td class="indent-1 bold">102.000 Bank</td>
			    <td class="nilai bold" style="border-right: 1px solid black;">2.802.282.195,76</td>
				<td class="indent-2">302.001 Hutang Sadar Manunggal</td>
				<td class="nilai">8.800.000.000,00</td> 
			</tr>
			<tr>
			    <td class="indent-2">102.001 BII Dollar (2.105.015.038)</td>
			    <td class="nilai" style="border-right: 1px solid black;">32.805.370,00</td>
				<td class="indent-2 bold">302.030 HUTANG BII $</td>
				<td class="nilai bold">0,00</td> 				
			</tr>

	        <tr>
	            <td class="indent-1 bold">104.000 Piutang</td>
	            <td class="nilai bold" style="border-right: 1px solid black;">6.214.213.967,82</td>	            
				<td class="indent-3">302.030.001 HUTANG BII $45000*8800</td>
				<td class="nilai">0,00</td> 			
	        </tr>
			
			<tr>
	            <td class="indent-1 bold">104.090 PIUTANG SALES</td>
	            <td class="nilai bold" style="border-right: 1px solid black;">263.386.791,00</td>	
				<td class="indent-1 bold">Jumlah HUTANG LANCAR</td>
				<td class="nilai bold" style="border-top: 1px solid black;">22.833.220.662,47</td> 				 	
			</tr>

			<tr>
	            <td class="indent-2 bold">104.126 PIUTANG RATIH</td>
	            <td class="nilai bold" style="border-right: 1px solid black;">0,00</td>		
				<td class="bold">PRIVE</td>				
			</tr>

			<tr>
	            <td class="indent-3">104.127 PIUTANG RISKA</td>
	            <td class="nilai" style="border-right: 1px solid black;">0,00</td>
				<td class="indent-1">900.001 Prive</td>
				<td class="nilai">(65.000.000,00)</td>				
			</tr>

			<tr>
	            <td class="indent-3">104.128 PIUTANG RISKA</td>
	            <td class="nilai" style="border-right: 1px solid black;">0,00</td>
				<td class="bold">Jumlah PRIVE</td>
				<td class="nilai bold" style="border-top: 1px solid black;">(65.000.000,00)</td>				
			</tr>

			<tr>
	            <td class="indent-2">126.000 INVESTASI SAHAM WAHANA EKA PERKASA</td>
	            <td class="nilai" style="border-right: 1px solid black;">200.000.000,00</td>	
				<td class="bold">LABA / ( RUGI ) TAHUN BERJALAN</td>
				<td class="nilai bold">31.460.257,76</td>				
			</tr>
			 
			<tr>
	            <td class="indent-1 bold">Jumlah AKTIVA LANCAR</td>
	            <td class="nilai bold" style="border-top: 1px solid black; border-right: 1px solid black;">16.444.137.748,99</td>				
	        </tr>

			<tr>
	            <td class="bold"></td>
	            <td class="nilai bold" style="border-right: 1px solid black;"></td>
	        </tr>		
		    
		    <tr style="border-top: 1px solid black;border-bottom: 1px solid black;">
	            <td class="bold">TOTAL AKTIVA</td>            	            
	            <td class="nilai1 bold" style="border-right: 1px solid black;">24.439.421.356,99</td>
				<td class="bold">TOTAL KEWAJIBAN & MODAL</td> 
				<td class="nilai bold">24.439.421.357,00</td>
		    </tr>

	    </table>
	</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?= $this->endSection() ?>