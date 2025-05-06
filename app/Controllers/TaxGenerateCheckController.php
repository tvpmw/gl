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
                
                $formattedData[] = [
                    $row->kode_trx,
                    format_date($row->tanggal, 'd/m/Y'),
                    $row->jam,
                    format_price($row->total_tax),
                    format_price($row->current_total),
                    $hasChanges ? 
                        '<span class="badge bg-warning">Ada Perubahan Data</span>' : 
                        '<span class="badge bg-success">Tidak Ada Perubahan</span>',
                    '<button type="button" class="btn btn-sm btn-dark text-white btn-detail" data-kdtr="'.$row->kode_trx.'">
                        <i class="fas fa-eye text-white"></i>
                    </button>'
                ];
            }

            return $this->response->setJSON([
                'data' => $formattedData
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function checkForChanges($kdtr, $dbs)
    {
        // Select database
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

        // Get current tr data with nama_brg from brg table
        $currentTrData = $db->table('tr')
            ->select('tr.nmbrg, tr.qty, tr.hrg, tr.disc, brg.nama as nama_brg')
            ->join('brg', 'tr.nmbrg = brg.nmbrg', 'left')
            ->where('tr.kdtr', $kdtr)
            ->orderBy('tr.nmbrg', 'ASC')
            ->get()
            ->getResultArray();

        // Get stored tax_generate_brg data
        $storedData = $db->table('crm.tax_generate_brg')
            ->select('nmbrg, qty, hrg, diskon as disc, nama_brg')
            ->where('kode_trx', $kdtr)
            ->orderBy('nmbrg', 'ASC')
            ->get()
            ->getResultArray();

        // Check if number of items changed
        if (count($currentTrData) !== count($storedData)) {
            return true;
        }

        // Compare each item's details
        foreach ($currentTrData as $key => $current) {
            $stored = $storedData[$key];
            
            // Check if any of these values don't match
            if ($current['nmbrg'] !== $stored['nmbrg'] || 
                strtolower(trim($current['nama_brg'])) !== strtolower(trim($stored['nama_brg'])) ||
                (float)$current['qty'] !== (float)$stored['qty'] ||
                (float)$current['hrg'] !== (float)$stored['hrg'] ||
                (float)$current['disc'] !== (float)$stored['disc']) {
                return true;
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

            // Get current tr data
            $currentData = $db->table('tr')
                ->select('tr.nmbrg, tr.qty, tr.hrg, tr.disc, brg.nama as nama_brg')
                ->join('brg', 'tr.nmbrg = brg.nmbrg', 'left')
                ->where('tr.kdtr', $kdtr)
                ->orderBy('tr.nmbrg', 'ASC')
                ->get()
                ->getResultArray();

            // Get stored tax_generate_brg data
            $storedData = $db->table('crm.tax_generate_brg')
                ->select('nmbrg, qty, hrg, diskon as disc, nama_brg')
                ->where('kode_trx', $kdtr)
                ->orderBy('nmbrg', 'ASC')
                ->get()
                ->getResultArray();

            // Compare and format data
            $comparisonData = [];
            foreach ($currentData as $current) {
                $found = false;
                foreach ($storedData as $stored) {
                    if ($current['nmbrg'] === $stored['nmbrg']) {
                        $found = true;
                        $changes = [];
                        
                        // Check each field for changes
                        if (strtolower(trim($current['nama_brg'])) !== strtolower(trim($stored['nama_brg']))) {
                            $changes['nama_brg'] = [
                                'old' => $stored['nama_brg'],
                                'new' => $current['nama_brg']
                            ];
                        }
                        if ((float)$current['qty'] !== (float)$stored['qty']) {
                            $changes['qty'] = [
                                'old' => $stored['qty'],
                                'new' => $current['qty']
                            ];
                        }
                        if ((float)$current['hrg'] !== (float)$stored['hrg']) {
                            $changes['hrg'] = [
                                'old' => $stored['hrg'],
                                'new' => $current['hrg']
                            ];
                        }
                        if ((float)$current['disc'] !== (float)$stored['disc']) {
                            $changes['disc'] = [
                                'old' => $stored['disc'],
                                'new' => $current['disc']
                            ];
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
}