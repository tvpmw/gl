<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\MstrModel;

class CustomerController extends Controller
{
    protected $mstrModel;
    protected $mstrModel2;
    protected $mstrModel3;
    protected $mstrModel4;
    protected $db_default;
    protected $db_crm_ars;
    protected $db_crm_wep;
    protected $db_crm_dtf;

    public function __construct()
    {
        helper(['my_helper']);

        $this->mstrModel = new MstrModel('default');
        $this->mstrModel2 = new MstrModel('crm_ars');        
        $this->mstrModel3 = new MstrModel('crm_wep');
        $this->mstrModel4 = new MstrModel('crm_dtf');

        $this->db_default = \Config\Database::connect('default');
        $this->db_crm_ars = \Config\Database::connect('crm_ars');
        $this->db_crm_wep = \Config\Database::connect('crm_wep');
        $this->db_crm_dtf = \Config\Database::connect('crm_dtf');
    }

    public function index()
    {
        $data['dbs'] = getSelDb();
        return view('customer/check', $data);
    }

    public function getData()
    {
        try {
            $request = service('request');
            $dbs = $request->getPost('sumber_data');

            // Select appropriate database
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

            // Define online channels
            $onlineChannels = [
                'BHINEKA', 'BHINNEKA', 'BLIBLI.COM', 'BUKALAPAK', 'EKATALOG', 
                'ETALASE', 'IG', 'GOSHOP', 'FACEBOOK', 'JD.ID', 'LAZADA', 
                'OLX', 'OLXACD', 'ONLINE', 'SHOPEE', 'SHOPEEACD', 'SHOPEES', 
                'SHOPPOFF', 'TIKTOKSHOP', 'TOKOPEDIA', 'TOKPEDACD', 'TOKPEDDDL', 
                'TOKPEDOFFL', 'TOKPEDS', 'WEBSITE', 'SHOPEEOFF', 'TOKPEDOFF', 
                'SHOPEEBLP', 'MANGGA', 'TIKTOKMIST'
            ];

            // Convert array to PostgreSQL array string
            $channelsString = "'" . implode("','", $onlineChannels) . "'";

            // Query to get online customers with NPWP
            $query = $db->query("
                SELECT DISTINCT 
                    c.kdcust,
                    c.nmcust,
                    c.npwp,
                    c.wil
                FROM cust c               
                WHERE c.npwp IS NOT NULL 
                AND c.npwp != ''
                AND c.wil IN ($channelsString)
                ORDER BY c.kdcust
            ");

            $customers = $query->getResultArray();

            return $this->response->setJSON([
                'data' => $customers
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}