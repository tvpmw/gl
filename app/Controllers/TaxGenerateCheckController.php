<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class TaxGenerateCheckController extends Controller
{
    protected $db_default;
    protected $db_crm_ars;
    protected $db_crm_wep;
    protected $db_crm_dtf;

    public function __construct()
    {
        helper(['my_helper']);
        $this->db_default = \Config\Database::connect('default');
        $this->db_crm_ars = \Config\Database::connect('crm_ars');
        $this->db_crm_wep = \Config\Database::connect('crm_wep');
        $this->db_crm_dtf = \Config\Database::connect('crm_dtf');
    }

    public function index()
    {
        $data['dbs'] = getSelDb();
        return view('tax_generate/check', $data);
    }

    public function getData()
    {
        try {
            $request = service('request');
            $startDate = $request->getPost('startDate');
            $endDate = $request->getPost('endDate');
            $dbs = $request->getPost('sumber_data');

            // Select database based on source
            switch ($dbs) {
                case 'ariston':
                    $db = $this->db_crm_ars;
                    break;
                case 'wep':
                    $db = $this->db_crm_wep;
                    break;
                case 'dtf':
                    $db = $this->db_crm_dtf;
                    break;
                default:
                    $db = $this->db_default;
            }

            $result = $db->table('crm.tax_generate tg')
                ->select('tg.*, m.gtot as current_total')
                ->join('mstr m', 'm.kdtr = tg.kode_trx', 'left')
                ->where('tg.tanggal >=', $startDate)
                ->where('tg.tanggal <=', $endDate)
                ->get()
                ->getResult();

            $formattedData = [];
            foreach ($result as $row) {
                // Check for changes in mstr and tr
                $hasChanges = $this->checkForChanges($row->kode_trx, $dbs);
                
                // Get tax core status - Fixed schema reference and added debugging
                $taxCoreQuery = $db->table('crm.data_coretax')
                    ->select('status_faktur')
                    ->where('referensi', $row->kode_trx);                    
                
                $taxCoreStatus = $taxCoreQuery->get()->getRow();
                
                // Log for debugging
                log_message('debug', 'Tax Core Query: ' . $db->getLastQuery());
                log_message('debug', 'Tax Core Result: ' . json_encode($taxCoreStatus));

                // Determine status badge
                $statusBadge = $hasChanges ? 
                    '<span class="badge bg-warning">Ada Perubahan Data</span>' : 
                    '<span class="badge bg-success">Tidak Ada Perubahan</span>';

                // Add tax core status badge
                $taxCoreBadge = '';
                if ($taxCoreStatus) {
                    if ($taxCoreStatus->status_faktur === 'APPROVED') {
                        $taxCoreBadge = '<span class="badge bg-success ms-1">APPROVED</span>';
                    } else if ($taxCoreStatus->status_faktur === 'CREATED') {
                        $taxCoreBadge = '<span class="badge bg-info ms-1">CREATED</span>';
                    }
                } else {
                    $taxCoreBadge = '<span class="badge bg-secondary ms-1">Data Tidak Ditemukan</span>';
                }

                $buttonCoretax = ($taxCoreStatus && ($taxCoreStatus->status_faktur === 'APPROVED' || $taxCoreStatus->status_faktur === 'CREATED')) 
                    ? '<button type="button" class="btn btn-sm btn-info text-white btn-coretax" data-kdtr="'.$row->kode_trx.'">
                        <i class="fas fa-eye text-white"></i>
                    </button>'
                    : '';

                $formattedData[] = [
                    $row->kode_trx,
                    format_date($row->tanggal, 'd/m/Y'),
                    $row->jam,
                    format_price($row->total_tax),
                    format_price($row->current_total),
                    $statusBadge . $taxCoreBadge,
                    '<div class="btn-group">
                        <button type="button" class="btn btn-sm btn-dark text-white btn-detail" data-kdtr="'.$row->kode_trx.'">
                            <i class="fas fa-history text-white"></i>
                        </button> &nbsp;                        
                        '.$buttonCoretax.'
                    </div>'
                ];
            }

            // Fix DataTables response format
            return $this->response->setJSON([
                'draw' => $request->getPost('draw'),
                'recordsTotal' => count($formattedData),
                'recordsFiltered' => count($formattedData), 
                'data' => $formattedData
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'draw' => $request->getPost('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    private function checkForChanges($kdtr, $dbs)
    {
        // Select database
        switch ($dbs) {
            case 'ariston':
            case 'wep':
                $db = ($dbs === 'ariston') ? $this->db_crm_ars : $this->db_crm_wep;
                
                $currentQuery = "SELECT 
                    tr.nmbrg,
                    tr.qty,
                    tr.hrg,
                    tr.disc as diskon_tr,
                    brg.nama as nama_brg,
                    mstr.disc/tr.qty/1.11 as diskon,
                    ((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))*tr.qty - ((COALESCE(mstr.disc,0)/(SELECT NULLIF(SUM(tr2.qty),0) FROM tr tr2 WHERE tr2.kdtr = tr.kdtr))*tr.qty))/1.11 as dpp,
                    (((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))*tr.qty - ((COALESCE(mstr.disc,0)/(SELECT NULLIF(SUM(tr2.qty),0) FROM tr tr2 WHERE tr2.kdtr = tr.kdtr))*tr.qty))/1.11)*11/12 as dpp_lain,
                    ((((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))*tr.qty - ((COALESCE(mstr.disc,0)/(SELECT NULLIF(SUM(tr2.qty),0) FROM tr tr2 WHERE tr2.kdtr = tr.kdtr))*tr.qty))/1.11)*11/12)*0.12 as ppn
                FROM tr
                JOIN brg ON tr.nmbrg = brg.nmbrg
                LEFT JOIN mstr ON tr.kdtr = mstr.kdtr
                WHERE tr.kdtr = ?
                ORDER BY tr.nmbrg ASC";
                break;
                
            default:
                $db = ($dbs === 'dtf') ? $this->db_crm_dtf : $this->db_default;
                
                $currentQuery = "SELECT 
                    tr.nmbrg,
                    tr.qty,
                    tr.hrg,
                    tr.disc as diskon_tr,
                    brg.nama as nama_brg,
                    0 as diskon,
                    (tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))/1.11 * tr.qty as dpp,
                    ((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))/1.11)*11/12 * tr.qty as dpp_lain,
                    (((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))/1.11)*11/12 * tr.qty)*0.12 as ppn
                FROM tr
                JOIN brg ON tr.nmbrg = brg.nmbrg
                WHERE tr.kdtr = ?
                ORDER BY tr.nmbrg ASC";
                break;
        }

        // Add query for retur
        $queryRetur = "SELECT 
            tr.kdtr,
            mstr.tgl,
            tr.nmbrg,
            tr.qty
        FROM tr
        LEFT JOIN mstr ON tr.kdtr = mstr.kdtr
        WHERE mstr.kdtr2 = ?
        ORDER BY tr.kdtr ASC, tr.nmbrg ASC";

        // Get retur data
        $getRetur = $db->query($queryRetur, [$kdtr])->getResult();  
        
        $listRetur = [];
        foreach($getRetur as $row){
            $listRetur[$row->nmbrg] = $row;
        }

        // Get current tr data
        $currentData = $db->query($currentQuery, [$kdtr])->getResultArray();

        // Adjust quantities based on retur
        foreach($currentData as $key => $row){
            $cekRetur = $listRetur[$row['nmbrg']] ?? null;
            $valRetur = $cekRetur ? $cekRetur->qty : 0;
            $qty = $row['qty'] - $valRetur;
            if($qty <= 0) {
                unset($currentData[$key]);
                continue;
            }
            $currentData[$key]['qty'] = $qty;
            $currentData[$key]['dpp'] = $qty * ($row['dpp'] / $row['qty']);
            $currentData[$key]['dpp_lain'] = $qty * ($row['dpp_lain'] / $row['qty']);
            $currentData[$key]['ppn'] = $qty * ($row['ppn'] / $row['qty']);
        }

        // Reindex array after possible removals
        $currentData = array_values($currentData);

        // Get stored tax_generate_brg data
        $storedData = $db->table('crm.tax_generate_brg')
            ->select('nmbrg, qty, hrg, diskon, diskon_tr, nama_brg, dpp, dpp_lain, nominal_ppn as ppn')
            ->where('kode_trx', $kdtr)
            ->orderBy('nmbrg', 'ASC')
            ->get()
            ->getResultArray();

        // Check if number of items changed
        if (count($currentData) !== count($storedData)) {
            return true;
        }

        // Compare each item's details with proper precision
        foreach ($currentData as $key => $current) {
            $stored = $storedData[$key];
            
            // Check basic field first
            if ($current['nmbrg'] !== $stored['nmbrg'] || 
                strtolower(trim($current['nama_brg'])) !== strtolower(trim($stored['nama_brg']))) {
                return true;
            }

            // Check numeric fields with proper precision
            $numericFields = ['qty', 'hrg', 'diskon', 'diskon_tr', 'dpp', 'dpp_lain', 'ppn'];
            foreach ($numericFields as $field) {
                // Round both values to 11 decimal places before formatting
                $currentVal = round((float)$current[$field], 11);
                $storedVal = round((float)$stored[$field], 11);
                
                // Format with fixed precision after rounding
                $currentFormatted = number_format($currentVal, 11, '.', '');
                $storedFormatted = number_format($storedVal, 11, '.', '');
                
                // Compare the formatted strings
                if ($currentFormatted !== $storedFormatted) {
                    // Double check with tolerance for floating point precision
                    if (abs($currentVal - $storedVal) > 0.00000000001) {
                        $changes[$field] = true;
                    }
                }
            }
        }

        // If we get here, no changes were found
        return false;
    }

    public function getDetail()
    {
        try {
            $request = service('request');
            $kdtr = $request->getPost('kdtr');
            $dbs = $request->getPost('sumber_data');

            // Select database based on source
            switch ($dbs) {
                case 'ariston':
                case 'wep':
                    $db = ($dbs === 'ariston') ? $this->db_crm_ars : $this->db_crm_wep;
                    
                    $currentQuery = "SELECT 
                        tr.nmbrg,
                        tr.qty,
                        tr.hrg,
                        tr.disc as diskon_tr,
                        brg.nama as nama_brg,
                        mstr.disc/tr.qty/1.11 as diskon,                        
                        ((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))*tr.qty - ((COALESCE(mstr.disc,0)/(SELECT NULLIF(SUM(tr2.qty),0) FROM tr tr2 WHERE tr2.kdtr = tr.kdtr))*tr.qty))/1.11 as dpp,
                        (((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))*tr.qty - ((COALESCE(mstr.disc,0)/(SELECT NULLIF(SUM(tr2.qty),0) FROM tr tr2 WHERE tr2.kdtr = tr.kdtr))*tr.qty))/1.11)*11/12 as dpp_lain,
                        ((((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))*tr.qty - ((COALESCE(mstr.disc,0)/(SELECT NULLIF(SUM(tr2.qty),0) FROM tr tr2 WHERE tr2.kdtr = tr.kdtr))*tr.qty))/1.11)*11/12)*0.12 as ppn
                    FROM tr
                    JOIN brg ON tr.nmbrg = brg.nmbrg
                    LEFT JOIN mstr ON tr.kdtr = mstr.kdtr
                    WHERE tr.kdtr = ?
                    ORDER BY tr.nmbrg ASC";
                    break;
                    
                default:
                    $db = ($dbs === 'dtf') ? $this->db_crm_dtf : $this->db_default;
                    
                    $currentQuery = "SELECT 
                        tr.nmbrg,
                        tr.qty,
                        tr.hrg,
                        tr.disc as diskon_tr,
                        brg.nama as nama_brg,
                        0 as diskon,
                        (tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))/1.11 * tr.qty as dpp,
                        ((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))/1.11)*11/12 * tr.qty as dpp_lain,
                        (((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))/1.11)*11/12 * tr.qty)*0.12 as ppn
                    FROM tr
                    JOIN brg ON tr.nmbrg = brg.nmbrg
                    WHERE tr.kdtr = ?
                    ORDER BY tr.nmbrg ASC";
                    break;
            }

            // Add query for retur
            $queryRetur = "SELECT 
                tr.kdtr,
                mstr.tgl,
                tr.nmbrg,
                tr.qty
            FROM tr
            LEFT JOIN mstr ON tr.kdtr = mstr.kdtr
            WHERE mstr.kdtr2 = ?
            ORDER BY tr.kdtr ASC, tr.nmbrg ASC";

            // Get retur data
            $getRetur = $db->query($queryRetur, [$kdtr])->getResult();  
            $listRetur = [];
            foreach($getRetur as $row){
                $listRetur[$row->nmbrg] = $row;
            }

            // Get stored tax_generate_brg data first
            $storedData = $db->table('crm.tax_generate_brg')
                ->select('nmbrg, qty, hrg, diskon, diskon_tr, nama_brg, dpp, dpp_lain, nominal_ppn as ppn')
                ->where('kode_trx', $kdtr)
                ->orderBy('nmbrg', 'ASC')
                ->get()
                ->getResultArray();

            // Get current tr data
            $currentData = $db->query($currentQuery, [$kdtr])->getResultArray();

            // Adjust quantities based on retur
            foreach($currentData as $key => $row){
                $cekRetur = $listRetur[$row['nmbrg']] ?? null;
                $valRetur = $cekRetur ? $cekRetur->qty : 0;
                $qty = $row['qty'] - $valRetur;
                if($qty <= 0) {
                    unset($currentData[$key]);
                    continue;
                }
                $currentData[$key]['qty'] = $qty;
                $currentData[$key]['dpp'] = $qty * ($row['dpp'] / $row['qty']);
                $currentData[$key]['dpp_lain'] = $qty * ($row['dpp_lain'] / $row['qty']);
                $currentData[$key]['ppn'] = $qty * ($row['ppn'] / $row['qty']);
                
                // Add retur information
                $currentData[$key]['qty_retur'] = $valRetur;
                $currentData[$key]['kode_retur'] = $cekRetur ? $cekRetur->kdtr : null;
                $currentData[$key]['tgl_retur'] = $cekRetur ? $cekRetur->tgl : null;
            }

            // Reindex array after possible removals
            $currentData = array_values($currentData);

            // Compare and format data
            $comparisonData = [];
            foreach ($currentData as $current) {
                $found = false;
                foreach ($storedData as $stored) {
                    if ($current['nmbrg'] === $stored['nmbrg']) {
                        $found = true;
                        $changes = [];
                        
                        // Check basic fields first including nama_brg
                        if (strtolower(trim($current['nama_brg'])) !== strtolower(trim($stored['nama_brg']))) {
                            $changes['nama_brg'] = true;
                        }

                        $numericFields = ['qty', 'hrg', 'diskon', 'diskon_tr', 'dpp', 'dpp_lain', 'ppn'];
                        foreach ($numericFields as $field) {
                            // Convert to float and round to handle precision consistently
                            $currentVal = round((float)$current[$field], 8);  // Reduced precision
                            $storedVal = round((float)$stored[$field], 8);   // Reduced precision
                            
                            // Compare with tolerance for floating point arithmetic
                            if (abs($currentVal - $storedVal) > 0.00000001) { // Smaller tolerance
                                $changes[$field] = true;
                            }
                        }

                        $comparisonData[] = [
                            'nmbrg' => $current['nmbrg'],
                            'nama_brg' => $current['nama_brg'],
                            'current' => $current,
                            'stored' => $stored,
                            'changes' => $changes,
                            'status' => !empty($changes) ? 'changed' : 'unchanged'
                        ];
                        break;
                    }
                }
                if (!$found) {
                    // Item is new
                    $comparisonData[] = [
                        'nmbrg' => $current['nmbrg'],
                        'nama_brg' => $current['nama_brg'],
                        'current' => $current,
                        'stored' => null,
                        'changes' => null,
                        'status' => 'new'
                    ];
                }
            }

            // Check for deleted items
            foreach ($storedData as $stored) {
                $found = false;
                foreach ($currentData as $current) {
                    if ($stored['nmbrg'] === $current['nmbrg']) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $comparisonData[] = [
                        'nmbrg' => $stored['nmbrg'],
                        'nama_brg' => $stored['nama_brg'],
                        'current' => null,
                        'stored' => $stored,
                        'changes' => null,
                        'status' => 'deleted'
                    ];
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $comparisonData
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function batalGenerate()
    {
        try {
            $request = service('request');
            $kode_trx = $request->getPost('kode_trx');
            $dbs = $request->getPost('sumber_data');

            // Select database based on source
            switch ($dbs) {
                case 'ariston':
                    $db = $this->db_crm_ars;
                    $db_config = 'crm_ars';
                    break;
                case 'wep':
                    $db = $this->db_crm_wep;
                    $db_config = 'crm_wep';
                    break;
                case 'dtf':
                    $db = $this->db_crm_dtf;
                    $db_config = 'crm_dtf';
                    break;
                default:
                    $db = $this->db_default;
                    $db_config = 'default';
            }

            $db->transStart();

            // Delete from tax_generate_brg
            $db->table('crm.tax_generate_brg')
                ->whereIn('kode_trx', $kode_trx)
                ->delete();

            // Delete from tax_generate
            $db->table('crm.tax_generate')
                ->whereIn('kode_trx', $kode_trx)
                ->delete();

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Gagal membatalkan tax generate');
            }

            logGL($db_config,'faktur_batalgenerate','Insert');

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Tax generate berhasil dibatalkan'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getCoretaxDetail()
    {
        try {
            $request = service('request');
            $kdtr = $request->getPost('kdtr');
            $dbs = $request->getPost('sumber_data');

            // Select database based on source
            switch ($dbs) {
                case 'ariston':
                    $db = $this->db_crm_ars;
                    break;
                case 'wep':
                    $db = $this->db_crm_wep;
                    break;
                case 'dtf':
                    $db = $this->db_crm_dtf;
                    break;
                default:
                    $db = $this->db_default;
            }

            $result = $db->table('crm.data_coretax')
                ->select('
                    npwp,
                    nama_pembeli,
                    kode_transaksi,
                    no_faktur,
                    tanggal_faktur,
                    masa_pajak,
                    tahun,
                    status_faktur,
                    harga_jual,
                    dpp,
                    ppn,
                    ppnbm,
                    referensi,
                    dilaporkan_penjual,
                    created_at'
                )
                ->where('referensi', $kdtr)
                ->get()
                ->getResult();

            return $this->response->setJSON([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function retur()
    {
        $data['dbs'] = getSelDb();
        return view('tax_generate/retur', $data);
    }

    public function getDataRetur()
    {
        try {
            $request = service('request');
            $dbs = $request->getPost('sumber_data');

            // Select database based on source
            switch ($dbs) {
                case 'ariston':
                    $db = $this->db_crm_ars;
                    break;
                case 'wep':
                    $db = $this->db_crm_wep;
                    break;
                case 'dtf':
                    $db = $this->db_crm_dtf;
                    break;
                default:
                    $db = $this->db_default;
            }

            $queryTg = $db->table('crm.tax_generate')->selectMin('tanggal')->get();
            $resultTg = $queryTg->getRow();
            $tglMin = $resultTg->tanggal ?? date('Y-m-d');

            $queryRetur = "SELECT mstr.kdtr,tr.nmbrg,tr.qty,mstr.tgl,mstr.kdtr2
                        FROM mstr
                        LEFT JOIN tr ON tr.kdtr=mstr.kdtr
                        WHERE mstr.tipe = 'KJ'
                        AND mstr.tgl > ?";

            $getRetur = $db->query($queryRetur, [$tglMin])->getResult();
            $listRetur = [];
            $listKdtr = [];
            foreach($getRetur as $row){
                $listKdtr[] = $row->kdtr2;
                $listRetur[$row->kdtr2.'|'.$row->nmbrg] = $row;
            }
            $listKdtr = array_unique($listKdtr);

            if(!empty($listKdtr)){
                $result = $db->table('crm.tax_generate_brg gb')
                    ->select('gb.*,g.tanggal,tr.kode_trx as aaa')
                    ->join('crm.tax_generate g', 'g.kode_trx = gb.kode_trx', 'left')
                    ->join('crm.tax_retur tr', 'tr.kode_trx = gb.kode_trx AND tr.nmbrg = gb.nmbrg', 'left')
                    ->whereIn('gb.kode_trx', $listKdtr)
                    ->where('tr.kode_trx IS NULL')
                    ->get()
                    ->getResult();

                $formattedData = [];
                foreach ($result as $row) {
                    $key = $row->kode_trx.'|'.$row->nmbrg;
                    $cekRetur = $listRetur[$key] ?? [];
                    if(empty($cekRetur)){
                        continue;
                    }
                    if($cekRetur->kdtr.$cekRetur->nmbrg == $row->kode_trx_retur.$row->nmbrg){
                        continue;
                    }

                    $formattedData[] = [
                        $key.'|'.$cekRetur->kdtr,
                        format_date($row->tanggal, 'd/m/Y'),
                        $row->kode_trx,
                        $row->nama_brg,
                        $row->qty,
                        $cekRetur->qty,
                        $row->qty-$cekRetur->qty,
                        $cekRetur->kdtr,
                        format_date($cekRetur->tgl, 'd/m/Y'),
                    ];
                }
            }else{
                $formattedData = [];
            }

            // Fix DataTables response format
            return $this->response->setJSON([
                'draw' => $request->getPost('draw'),
                'recordsTotal' => count($formattedData),
                'recordsFiltered' => count($formattedData), 
                'data' => $formattedData
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'draw' => $request->getPost('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function sudahLapor()
    {
        try {
            $request = service('request');
            $kode_trx = $request->getPost('kode_trx');
            $dbs = $request->getPost('sumber_data');
            $catatan = $request->getPost('catatan');
            $userId = session()->get('user_id') ?? 9999;

            // Select database based on source
            switch ($dbs) {
                case 'ariston':
                    $db = $this->db_crm_ars;
                    $db_config = 'crm_ars';
                    break;
                case 'wep':
                    $db = $this->db_crm_wep;
                    $db_config = 'crm_wep';
                    break;
                case 'dtf':
                    $db = $this->db_crm_dtf;
                    $db_config = 'crm_dtf';
                    break;
                default:
                    $db = $this->db_default;
                    $db_config = 'default';
            }

            $db->transStart();

            $dataIns = [];
            foreach ($kode_trx as $value) {
                list($kdtr,$nmbrg,$kode_trx_retur) = explode('|', $value);
                $dataIns[] = [
                    'kode_trx' => $kdtr,
                    'kode_trx_retur' => $kode_trx_retur,
                    'nmbrg' => $nmbrg,
                    'catatan' => $catatan,
                    'created_at' => date('Y-m-d H:i:s'),
                    'user_id' => $userId,
                ];
            }

            $db->table('crm.tax_retur')->insertBatch($dataIns);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Proses gagal');
            }

            logGL($db_config,'faktur_retur','Insert');
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Proses berhasil'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}