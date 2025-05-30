<?php

namespace App\Controllers;

use App\Models\CoaModel;
use Predis\Client;
use CodeIgniter\HTTP\RedirectResponse;

class DashboardController extends BaseController
{
    protected $coaModel;
    protected $coaModel2;
    protected $coaModel3;
    protected $coaModel4;
    protected $coaModel5;
    protected $coaModel6;
    protected $redis;

    public function __construct()
    {
        helper(['my_helper']);
        
        // Initialize models if authorized
        $this->coaModel = new CoaModel('default');
        $this->coaModel2 = new CoaModel('crm_ars');        
        $this->coaModel3 = new CoaModel('crm_wep');
        $this->coaModel4 = new CoaModel('crm_dtf');
        $this->coaModel5 = new CoaModel('crm_ars_bali');
        $this->coaModel6 = new CoaModel('crm_wep_bali');

        $this->redis = new Client([
            'scheme' => 'tcp',
            'host'   => '127.0.0.1',
            'port'   => 6379,
        ]);
    }

    public function index()
    {
        $data = [
            'thnSkg' => date('Y'),
            'startYear' => 2009,
            'dbs' => getSelDb()
        ];
        
        return view('dashboard', $data);
    }

    public function getData()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $req = $input['req'] ?? 'labarugi';
        $tahun = intval($input['tahun']) ?: date('Y');
        $dbs = $input['dbs'] ?? 'sdkom';

        if($req == 'labarugi'){
            return $this->getDataLaba($tahun,$dbs);
        }

        if($req == 'neraca'){
            return $this->getDataNeraca($tahun,$dbs);
        }

        if($req == 'coa') {
            $data = $this->getDataCoa($tahun, $dbs);
            $response = [
                'status' => true,
                'message' => 'Data COA berhasil diambil',
                'data' => $data
            ];
            return $this->response->setJSON($response);
        }

        $response = [
            "tahun" => $tahun,
            "data" => []
        ];

        return $this->response->setJSON($response);
    }

    private function getDataLaba($tahun,$dbs)
    {
        switch ($dbs) {
            case 'ariston':
                $getLR = $this->coaModel2->getLaporanLabaRugi();
                break;
            case 'wep':
                $getLR = $this->coaModel3->getLaporanLabaRugi();
                break;
            case 'dtf':
                $getLR = $this->coaModel4->getLaporanLabaRugi();
                break;
            case 'ariston_bali':
                $getLR = $this->coaModel5->getLaporanLabaRugi();
                break;
            case 'wep_bali':
                $getLR = $this->coaModel6->getLaporanLabaRugi();
                break;
            default:
                $getLR = $this->coaModel->getLaporanLabaRugi();
        }

        $lists = [];
        if (!empty($getLR)) {
            foreach ($getLR as $value) {
                $key = $value['tahun'];
                $bl = $value['bulan'];
                $id = $key.'/'.$bl.'/'.$dbs;
                $aksi = '<button class="btn btn-sm btn-light detailLR" data-id="'.$id.'" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>';
                $lists[$key][] = [
                    'bln' => $bl,
                    'bulan' => getMonths($bl, true),
                    'pendapatan' => (float) $value['pendapatan'],
                    'hpp' => (float) $value['hpp'],
                    'biaya' => (float) $value['biaya'],
                    'lr' => (float) $value['lr'],
                    'posting' => $value['posting'],
                    'aksi' => $aksi,
                ];
            }
        }

        $response = [
            "tahun" => $tahun,
            "data" => $lists[$tahun] ?? []
        ];

        return $this->response->setJSON($response);
    }

    private function getDataCoa($tahun, $dbs)
    {
        switch ($dbs) {
            case 'ariston':
                $getCoa = $this->coaModel2->getCoa($tahun);
                break;
            case 'wep':
                $getCoa = $this->coaModel3->getCoa($tahun);
                break;
            case 'dtf':
                $getCoa = $this->coaModel4->getCoa($tahun);
                break;
            case 'ariston_bali':
                $getCoa = $this->coaModel5->getCoa($tahun);
                break;
            case 'wep_bali':
                $getCoa = $this->coaModel6->getCoa($tahun);
                break;
            default:
                $getCoa = $this->coaModel->getCoa($tahun);
        }

        $lists = [];
        if (!empty($getCoa)) {
            foreach ($getCoa as $value) {
                $aksi = '<button class="btn btn-sm btn-light detailCOA" data-id="'.$value['KDCOA'].'" title="View">
                            <i class="fas fa-eye"></i>
                        </button>';
                
                $lists[] = [
                    'kode_akun' => $value['KDCOA'] ?? '',
                    'nama_akun' => $value['NMCOA'] ?? '',
                    'kategori' => $value['nm_sub'] ?? '', 
                    'level' => $value['level'] ?? 0,      
                    'status' => $value['status'] ?? 1,    
                    'nilai' => floatval($value['nilai'] ?? 0),
                    'aksi' => $aksi
                ];
            }
        }

        return $lists;
    }

    public function neraca()
    {
        $data['thnSkg'] = date('Y');
        $data['startYear'] = 2009;
        $data['dbs'] = getSelDb();
        return view('dashboard-neraca', $data);
    }

    public function coa()
    {
        $data['thnSkg'] = date('Y');
        $data['startYear'] = 2009;
        $data['dbs'] = getSelDb();
        return view('dashboard-coa', $data);
    }

    private function getDataNeraca($tahun,$dbs)
    {
        switch ($dbs) {
            case 'ariston':
                $getLR = $this->coaModel2->getNeraca();
                break;
            case 'wep':
                $getLR = $this->coaModel3->getNeraca();
                break;
            case 'dtf':
                $getLR = $this->coaModel4->getNeraca();
                break;
            case 'ariston_bali':
                $getLR = $this->coaModel5->getNeraca();
                break;
            case 'wep_bali':
                $getLR = $this->coaModel6->getNeraca();
                break;
            default:
                $getLR = $this->coaModel->getNeraca();
        }

        $lists = [];
        if (!empty($getLR)) {
            foreach ($getLR as $value) {
                $key = $value['tahun'];
                $bl = $value['bulan'];
                $id = $key.'/'.$bl.'/'.$dbs;
                $aksi = '<button class="btn btn-sm btn-light detailNR" data-id="'.$id.'" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>';
                $lists[$key][] = [
                    'bln' => $bl,
                    'bulan' => getMonths($bl, true),
                    'aset' => (float) $value['aset'],
                    'liabilitas' => (float) $value['liabilitas'],
                    'labarugi_tahun' => (float) $value['labarugi_tahun'],
                    'ekuitas' => (float) $value['ekuitas'],
                    'ekuitaslaba' => (float) $value['ekuitas'] + $value['labarugi_tahun'],
                    'balance' => (float) $value['balance'],
                    'posting' => $value['posting'],
                    'aksi' => $aksi,
                ];
            }
        }

        $response = [
            "tahun" => $tahun,
            "data" => $lists[$tahun] ?? []
        ];

        return $this->response->setJSON($response);
    }
}