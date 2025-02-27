<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CoaModel;
use App\Models\SubcoaModel;
use App\Models\JvModel;

class JurnalController extends BaseController
{
    protected $coaModel;
    protected $coaModel2;
    protected $coaModel3;
    protected $coaModel4;
    protected $subcoaModel;
    protected $subcoaModel2;
    protected $subcoaModel3;
    protected $subcoaModel4;

    protected $JvModel;
    protected $JvModel2;
    protected $JvModel3;
    protected $JvModel4;

    public function __construct()
    {
        helper(['my_helper']);

        $this->coaModel = new CoaModel('default');
        $this->coaModel2 = new CoaModel('crm_ars');
        $this->coaModel3 = new CoaModel('crm_wep');
        $this->coaModel4 = new CoaModel('crm_dtf');

        $this->subcoaModel = new SubcoaModel('default');
        $this->subcoaModel2 = new SubcoaModel('crm_ars');
        $this->subcoaModel3 = new SubcoaModel('crm_wep');
        $this->subcoaModel4 = new SubcoaModel('crm_dtf');

        $this->jvMod = new JvModel('default');
        $this->jvMod2 = new JvModel('crm_ars');
        $this->jvMod3 = new JvModel('crm_wep');
        $this->jvMod4 = new JvModel('crm_dtf');
    }

    public function index()
    {
        $data['blnSel'] = date('m');
        $data['bln'] = getMonths();
        $data['thnSel'] = date('Y');
        $data['startYear'] = 2009;
        $data['dbs'] = getSelDb();
        return view('jurnal/list', $data);
    }

    public function getData()
    {
        $request = service('request');

        $draw = $request->getPost('draw');
        $start = $request->getPost('start');
        $length = $request->getPost('length');
        $search = $request->getPost('search')['value'];

        // Sorting
        $orderColumnIndex = $request->getPost('order')[0]['column'] ?? 0;
        $orderDir = $request->getPost('order')[0]['dir'] ?? 'asc';

        // Filter dari frontend
        $dbs = $request->getPost('dbs');
        $bulan = $request->getPost('bulan');
        $tahun = $request->getPost('tahun');

        // Validasi database yang dipilih
        switch ($dbs) {
            case 'ariston':
                $mdl = $this->jvMod2;
                break;
            case 'wep':
                $mdl = $this->jvMod3;
                break;
            case 'dtf':
                $mdl = $this->jvMod4;
                break;
            default:
                $mdl = $this->jvMod;
        }

        // Query Data dengan filter
        $totalRecords = $mdl->countAll();
        $totalRecordsFiltered = $mdl->countFiltered($search, $bulan, $tahun);
        $data = $mdl->getData($start, $length, $search, $orderColumnIndex, $orderDir, $bulan, $tahun);

        $formattedData = [];
        $no = $start+1;
        $tampil = false;
        foreach ($data as $row) {
            $id = $row->KDJV.'|'.$dbs;
            $akt = '<i class="icon fa fa-close" style="color:red"></i>';
            if($row->POSTING == 1){
                $akt = '<i class="icon fa fa-check" style="color:green"></i>';
            }
            $aksiTable = "<a class='btn btn-sm btn-primary' href='javascript:void(0)' title='Detail' onclick='detail_data(`".$id."`)'><i class='fa fa-eye text-white'></i></a> ";
            if($tampil):
                if($row->POSTING != 1):
                $aksiTable .= "<a class='btn btn-sm btn-warning' href='javascript:void(0)' title='Edit' onclick='edit_data(`".$id."`)'><i class='fa fa-pencil text-white'></i></a> ";
                $aksiTable .= "<a class='btn btn-sm btn-danger' href='javascript:void(0)' title='Delete' onclick='delete_data(`".$id."`)'><i class='fa fa-trash text-white'></i></a>";
                else:
                $aksiTable .= "<a class='btn btn-sm btn-dark' href='javascript:void(0)' title='Edit'><i class='fa fa-pencil text-white'></i></a> ";
                $aksiTable .= "<a class='btn btn-sm btn-dark' href='javascript:void(0)' title='Delete'><i class='fa fa-trash text-white'></i></a>";
                endif;
            endif;

            $lists = [];
            $lists[]  = $no++;
            $lists[]  = '<a href="javascript:void(0)" title="Detail" onclick="detail_data(`'.$id.'`)">'.$row->KDJV.'</a>';
            $lists[]  = $row->KETJV;
            $lists[]  = tanggal_indo($row->TGLJV,true,true);
            $lists[]  = $row->TH;
            $lists[]  = $row->BL;
            $lists[]  = format_angka($row->JVTOT);
            $lists[]  = $akt;
            $lists[]  = $aksiTable;

            $formattedData[] = $lists;
        }

        $response = [
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordsFiltered,
            "data" => $formattedData,
        ];

        return $this->response->setJSON($response);
    }

    public function getDetail()
    {
        $id = $this->request->getPost('id');
        list($kdjv,$dbs) = explode('|', $id);

        // Validasi database yang dipilih
        switch ($dbs) {
            case 'ariston':
                $detail = $this->jvMod2->getJurnalWithDetails($kdjv);
                break;
            case 'wep':
                $detail = $this->jvMod3->getJurnalWithDetails($kdjv);
                break;
            case 'dtf':
                $detail = $this->jvMod4->getJurnalWithDetails($kdjv);
                break;
            default:
                $detail = $this->jvMod->getJurnalWithDetails($kdjv);
        }

        $data['detail'] = $detail;
        // pr($data,1);
        return view('jurnal/detail', $data);
    }
}
