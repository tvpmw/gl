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

                $formattedData[] = [
                    $row->kode_trx,
                    format_date($row->tanggal, 'd/m/Y'),
                    $row->jam,
                    format_price($row->total_tax),
                    format_price($row->current_total),
                    $statusBadge . $taxCoreBadge,
                    '<button type="button" class="btn btn-sm btn-dark text-white btn-detail" data-kdtr="'.$row->kode_trx.'">
                        <i class="fas fa-history text-white"></i>
                    </button>' 
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
                    -- (COALESCE(mstr.disc,0)/(SELECT NULLIF(SUM(tr2.qty),0) FROM tr tr2 WHERE tr2.kdtr = tr.kdtr)) as diskon,
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

        // Get current tr data
        $currentData = $db->query($currentQuery, [$kdtr])->getResultArray();

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
                $currentVal = number_format((float)$current[$field], 11, '.', '');
                $storedVal = number_format((float)$stored[$field], 11, '.', '');
                if ($currentVal !== $storedVal) {
                    return true;
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
                        -- (COALESCE(mstr.disc,0)/(SELECT NULLIF(SUM(tr2.qty),0) FROM tr tr2 WHERE tr2.kdtr = tr.kdtr)) as diskon,
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

            // Get stored tax_generate_brg data first - use diskon as is from stored data
            $storedData = $db->table('crm.tax_generate_brg')
                ->select('nmbrg, qty, hrg, diskon, diskon_tr, nama_brg, dpp, dpp_lain, nominal_ppn as ppn')
                ->where('kode_trx', $kdtr)
                ->orderBy('nmbrg', 'ASC')
                ->get()
                ->getResultArray();

            // Log for debugging
            log_message('debug', 'Stored Data Query: ' . $db->getLastQuery());
            log_message('debug', 'Stored Data Result: ' . json_encode($storedData));

            // If no stored data found, return empty
            if (empty($storedData)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No data found for transaction ' . $kdtr
                ]);
            }

            // Get current tr data with calculations
            $currentData = $db->query($currentQuery, [$kdtr])->getResultArray();

            // Log for debugging
            log_message('debug', 'Current Data Query: ' . $currentQuery);
            log_message('debug', 'Current Data Params: ' . $kdtr);
            log_message('debug', 'Current Data Result: ' . json_encode($currentData));

            // If no current data found, return empty
            if (empty($currentData)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No current transaction data found for ' . $kdtr
                ]);
            }

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

                        // Check numeric fields
                        $numericFields = ['qty', 'hrg', 'diskon', 'diskon_tr', 'dpp', 'dpp_lain', 'ppn'];
                        foreach ($numericFields as $field) {
                            $currentVal = number_format((float)$current[$field], 11, '.', '');
                            $storedVal = number_format((float)$stored[$field], 11, '.', '');
                            if ($currentVal !== $storedVal) {
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
}