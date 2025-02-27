<style>
    /* Agar thead tetap terlihat saat scroll */
    .table-responsive {
        max-height: 60vh; /* Sesuaikan tinggi maksimal */
        overflow-y: auto;  /* Aktifkan scroll vertikal */
        position: relative;
    }

    .table thead {
        position: sticky;
        top: 0;
        z-index: 1020;
        background-color: #343a40; /* Warna background agar tetap terlihat */
        color: white; /* Warna teks tetap putih */
    }
</style>

<div class="table-responsive">
	<table class="mb-2">
		<tr>
			<td><strong>Kode Jurnal</strong> </td>
			<td>: <?=$detail['kdjv'] ?? '' ?></td>
		</tr>
		<tr>
			<td><strong>Tanggal</strong> </td>
			<td>: <?=tanggal_indo($detail['tgljv'],true,true) ?? '' ?></td>
		</tr>
		<tr>
			<td><strong>Keterangan</strong> </td>
			<td>: <?=$detail['ketjv'] ?? '' ?></td>
		</tr>
        <tr>
            <td><strong>Status</strong> </td>
            <td>: <span id="statusBalance" class="badge bg-<?=($detail['posting']=='1')?'success':'danger' ?>"><?=($detail['posting']=='1')?'Posting':'Berjalan' ?></span></td>
        </tr>
	</table>
</div>

<div class="table-responsive">
    <table class="table table-bordered">
        <thead class="table-dark text-center">
            <tr>
                <th>Akun</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Kredit</th>
                <th class="text-start">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totDebit = 0;
            $totKredit = 0;
            foreach ($detail['rincian'] as $value) { 
            ?>
            <tr>
                <td><?=$value['kdcoa'] ?? ''?> - <?=$value['nmcoa'] ?? ''?></td>
                <td class="text-end"><?=format_angka($value['jvdebet'])?></td>
                <td class="text-end"><?=format_angka($value['jvkredit'])?></td>
                <td class="text-start"><?=$value['ket'] ?? ''?></td>
            </tr>
            <?php
                $totDebit += $value['jvdebet'];
                $totKredit += $value['jvkredit'];
            } 
            ?>
        </tbody>
        <tfoot class="table-light">
            <tr class="fw-bold text-center">
                <td>Total</td>
                <td class="text-end" id="totalDebit"><?=format_angka($totDebit) ?? '-'?></td>
                <td class="text-end" id="totalKredit"><?=format_angka($totKredit) ?? '-'?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
