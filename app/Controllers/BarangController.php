<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class BarangController extends Controller 
{
    protected $db_default;
    protected $db_crm_ars;
    protected $db_crm_wep;
    protected $db_crm_dtf;

    public function __construct()
    {
        $this->db_default = \Config\Database::connect('default');
        $this->db_crm_ars = \Config\Database::connect('crm_ars');
        $this->db_crm_wep = \Config\Database::connect('crm_wep');
        $this->db_crm_dtf = \Config\Database::connect('crm_dtf');
    }

    public function search()
    {
        try {
            $q = strtoupper($this->request->getGet('q') ?? '');
            $dbs = $this->request->getGet('sumber_data') ?? 'default';

            // Select the appropriate database connection
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

            $data = $db->table('brg')
                    ->select('nmbrg as kdbrg, nama as nmbrg')
                    ->like('UPPER(nama)', "%{$q}%")
                    ->orLike('UPPER(nmbrg)', "%{$q}%")
                    ->limit(10)
                    ->get()
                    ->getResult();

            return $this->response->setJSON($data);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getTaxCodes() 
    {
        try {   
            $q = $this->request->getGet('q') ?? '';
            $q = strtoupper($q);
            $dbs = $this->request->getGet('sumber_data') ?? 'default';
            
            // Select the appropriate database connection
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
            
            $taxCodes = $db->table('crm.kode_tax')
                        ->select('kdtax as value, keterangan as text')
                        ->where('UPPER(kdtax) LIKE', "%{$q}%")
                        ->orWhere('UPPER(keterangan) LIKE', "%{$q}%")
                        ->limit(10)
                        ->get()
                        ->getResult();

            return $this->response->setJSON([
                'success' => true,
                'data' => $taxCodes
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Tax Codes Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}