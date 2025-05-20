<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MappingCoretaxController extends Controller
{
    protected $db;
    protected $db_default;
    protected $db_crm_ars;
    protected $db_crm_wep;
    protected $db_crm_dtf;
    protected $db_crm_ars_bali;
    protected $db_crm_wep_bali;

    public function __construct()
    {
        $this->db_default = \Config\Database::connect('default');
        $this->db_crm_ars = \Config\Database::connect('crm_ars');
        $this->db_crm_wep = \Config\Database::connect('crm_wep');
        $this->db_crm_dtf = \Config\Database::connect('crm_dtf');
        $this->db_crm_ars_bali = \Config\Database::connect('crm_ars_bali');
        $this->db_crm_wep_bali = \Config\Database::connect('crm_wep_bali');
        helper(['my_helper']);
    }

    public function index()
    {
        return view('mapping_coretax/index');
    }

    public function getData()
    {
        try {
            $request = service('request');
            $dbs = $request->getPost('sumber_data') ?? 'default';

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
                case 'ariston_bali':
                    $db = $this->db_crm_ars_bali;
                    break;
                case 'wep_bali':
                    $db = $this->db_crm_wep_bali;
                    break;
                default:
                    $db = $this->db_default;
            }

            $sql = "SELECT mc.*, b.nama as nmbrg 
                    FROM crm.mapping_coretax mc
                    LEFT JOIN brg b ON mc.kdbrg = b.nmbrg
                    ORDER BY mc.kdbrg";

            $data = $db->query($sql)->getResult();
            
            $formattedData = [];
            foreach ($data as $row) {
                $formattedData[] = [                    
                    $row->kdbrg,
                    $row->nmbrg ?? '',
                    $row->kdtax ?? '000000',
                    ''   
                ];
            }

            return $this->response->setJSON([
                'data' => $formattedData
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    public function getKodeTax()
    {
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load(FCPATH . 'assets/template/jenis.xlsx');
            
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();
            
            array_shift($data);
            
            $options = [];
            foreach ($data as $row) {
                if (!empty($row[0])) {
                    $options[] = [
                        'value' => $row[0], 
                        'text' => $row[1]   
                    ];
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $options
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function save()
    {
        try {
            $data = $this->request->getJSON(true);
            $dbs = $data['sumber_data'] ?? 'default';

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
                case 'ariston_bali':
                    $db = $this->db_crm_ars_bali;
                    break;
                case 'wep_bali':
                    $db = $this->db_crm_wep_bali;
                    break;
                default:
                    $db = $this->db_default;
            }

            $validation = \Config\Services::validation();
            $validation->setRules([
                'kdbrg' => 'required|max_length[40]',
                'nmbrg' => 'required|max_length[100]',
                'kdtax' => 'required|max_length[10]'
            ]);

            if (!$validation->run($data)) {
                throw new \Exception(implode('\n', $validation->getErrors()));
            }

            $existing = $db->table('crm.mapping_coretax')
                            ->where('kdbrg', $data['kdbrg'])
                            ->get()
                            ->getRow();

            if ($existing) {
                $result = $db->table('crm.mapping_coretax')
                            ->where('kdbrg', $data['kdbrg'])
                            ->update([
                                'nmbrg' => $data['nmbrg'],
                                'kdtax' => $data['kdtax']
                            ]);
                $message = 'Data berhasil diupdate';
            } else {
                $result = $db->table('crm.mapping_coretax')
                            ->insert([
                                'kdbrg' => $data['kdbrg'],
                                'nmbrg' => $data['nmbrg'],
                                'kdtax' => $data['kdtax']
                            ]);
                $message = 'Data berhasil disimpan';
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function delete()
    {
        try {
            $data = $this->request->getJSON(true);
            
            if (empty($data['kdbrg'])) {
                throw new \Exception('Invalid data');
            }

            $result = $this->db->table('crm.mapping_coretax')
                              ->where('kdbrg', $data['kdbrg'])
                              ->delete();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}