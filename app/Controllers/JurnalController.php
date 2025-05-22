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
    protected $coaModel5;
    protected $coaModel6;

    protected $JvModel;
    protected $JvModel2;
    protected $JvModel3;
    protected $JvModel4;
    protected $JvModel5;
    protected $JvModel6;

    protected $db_default;
    protected $db_crm_ars;
    protected $db_crm_wep;
    protected $db_crm_dtf;
    protected $db_crm_ars_bali;
    protected $db_crm_wep_bali;

    public function __construct()
    {
        helper(['my_helper']);

        $this->coaModel = new CoaModel('default');
        $this->coaModel2 = new CoaModel('crm_ars');
        $this->coaModel3 = new CoaModel('crm_wep');
        $this->coaModel4 = new CoaModel('crm_dtf');
        $this->coaModel5 = new CoaModel('crm_ars_bali');
        $this->coaModel6 = new CoaModel('crm_wep_bali');

        $this->jvMod = new JvModel('default');
        $this->jvMod2 = new JvModel('crm_ars');
        $this->jvMod3 = new JvModel('crm_wep');
        $this->jvMod4 = new JvModel('crm_dtf');
        $this->jvMod5 = new JvModel('crm_ars_bali');
        $this->jvMod6 = new JvModel('crm_wep_bali');

        $this->db_default = \Config\Database::connect('default');
        $this->db_crm_ars = \Config\Database::connect('crm_ars');
        $this->db_crm_wep = \Config\Database::connect('crm_wep');
        $this->db_crm_dtf = \Config\Database::connect('crm_dtf');
        $this->db_crm_ars_bali = \Config\Database::connect('crm_ars_bali');
        $this->db_crm_wep_bali = \Config\Database::connect('crm_wep_bali');
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

        $orderColumnIndex = $request->getPost('order')[0]['column'] ?? 0;
        $orderDir = $request->getPost('order')[0]['dir'] ?? 'asc';

        $dbs = $request->getPost('dbs');
        $bulan = $request->getPost('bulan');
        $tahun = $request->getPost('tahun');

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
            case 'ariston_bali':
                $mdl = $this->jvMod5;
                break;
            case 'wep_bali':
                $mdl = $this->jvMod6;
                break;
            default:
                $mdl = $this->jvMod;
        }

        $totalRecords = $mdl->countAll();
        $totalRecordsFiltered = $mdl->countFiltered($search, $bulan, $tahun);
        $data = $mdl->getData($start, $length, $search, $orderColumnIndex, $orderDir, $bulan, $tahun);

        $formattedData = [];
        $no = $start+1;
        $tampil = true;
        foreach ($data as $row) {
            $id = $row->KDJV.'|'.$dbs;
            $akt = '<i class="icon fa fa-close" style="color:red"></i>';
            if($row->POSTING == 1){
                $akt = '<i class="icon fa fa-check" style="color:green"></i>';
            }
            $aksiTable = "<a class='btn btn-sm btn-primary' href='javascript:void(0)' title='Detail' onclick='detail_data(`".$id."`)'><i class='fa fa-eye text-white'></i></a> ";
            $jurnalAccess = checkMenuAccess('cms/jurnal');            
            if($tampil):
                if($row->POSTING != 1 && $row->JVTOT != 0):
                if ($jurnalAccess['can_edit'] == false) {
                }else if ($jurnalAccess['can_edit'] == null) {
                }else{
                $aksiTable .= "<a class='btn btn-sm btn-warning' href='javascript:void(0)' title='Edit' onclick='edit_data(`".$id."`)'><i class='fa fa-pencil text-white'></i></a> ";
                }
                if ($jurnalAccess['can_delete'] == false) {
                }else if ($jurnalAccess['can_delete'] == null) {
                }else{
                $aksiTable .= "<a class='btn btn-sm btn-danger' href='javascript:void(0)' title='Delete' onclick='delete_data(`".$id."`)'><i class='fa fa-trash text-white'></i></a>";                
                }
                else:
                if ($jurnalAccess['can_edit'] == false) {
                }else if ($jurnalAccess['can_edit'] == null) {
                }else{
                $aksiTable .= "<a class='btn btn-sm btn-dark' href='javascript:void(0)' title='Edit'><i class='fa fa-pencil text-white'></i></a> ";
                }
                if ($jurnalAccess['can_delete'] == false) {
                }else if ($jurnalAccess['can_delete'] == null) {
                }else{
                $aksiTable .= "<a class='btn btn-sm btn-dark' href='javascript:void(0)' title='Delete'><i class='fa fa-trash text-white'></i></a>";
                }
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
            case 'ariston_bali':
                $detail = $this->jvMod5->getJurnalWithDetails($kdjv);
                break;
            case 'wep_bali':
                $detail = $this->jvMod6->getJurnalWithDetails($kdjv);
                break;
            default:
                $detail = $this->jvMod->getJurnalWithDetails($kdjv);
        }

        $data['detail'] = $detail;
        return view('jurnal/detail', $data);
    }

    public function getKodeJurnal()
    {
        $dbs = $this->request->getPost('database');
        
        switch ($dbs) {
            case 'ariston':
                $kode = generateKodeJurnal($this->db_crm_ars);
                break;
            case 'wep':
                $kode = generateKodeJurnal($this->db_crm_wep);
                break;
            case 'dtf':
                $kode = generateKodeJurnal($this->db_crm_dtf);
                break;
            case 'ariston_bali':
                $kode = generateKodeJurnal($this->db_crm_ars_bali);
                break;
            case 'wep_bali':
                $kode = generateKodeJurnal($this->db_crm_wep_bali);
                break;
            default:
                $kode = generateKodeJurnal($this->db_default);
        }

        return $this->response->setJSON(['kode' => $kode ?? '']);
    }

    public function getAkun()
    {
        $dbs = $this->request->getGet('database');
        
        switch ($dbs) {
            case 'ariston':
                $getAkun = $this->coaModel2->getAkun($this->db_crm_ars);
                break;
            case 'wep':
                $getAkun = $this->coaModel3->getAkun($this->db_crm_wep);
                break;
            case 'dtf':
                $getAkun = $this->coaModel4->getAkun($this->db_crm_dtf);
                break;
            case 'ariston_bali':
                $getAkun = $this->coaModel5->getAkun($this->db_crm_ars_bali);
                break;
            case 'wep_bali':
                $getAkun = $this->coaModel6->getAkun($this->db_crm_wep_bali);
                break;
            default:
                $getAkun = $this->coaModel->getAkun($this->db_default);
        }

        return $this->response->setJSON($getAkun);
    }

    public function save()
    {
        if (!$this->request->isAJAX()) return;

        $data_id = $this->request->getVar('data_id');
        $rules = [
            "tanggal"     => "required",
            "keterangan"  => "required",
            "jurnal"      => "required",
        ];

        if(empty($data_id)){
            $rules['database'] = "required";
            $rules['kode_jurnal'] = "required";
            $dbs = $this->request->getVar('database');
        }else{
            list($id, $dbs) = explode('|', $data_id);
        }

        $messages = [];
        foreach (array_keys($rules) as $row) {
            $messages[$row] = [
                "required" => isLang($row) . " required",
            ];
        }

        if (!$this->validate($rules, $messages)) {
            $errors = $this->validator->getErrors();
            $msgNotif = '<ul>';
            foreach ($errors as $message) {
                $msgNotif .= '<li>' . esc($message) . '</li>';
            }
            $msgNotif .= '</ul>';

            return $this->response->setJSON(['status' => false, 'msg' => $msgNotif]);
        }

        $jurnal      = $this->request->getVar('jurnal');
        $tanggal     = $this->request->getVar('tanggal') . " 00:00:00";
        $dbs         = $this->request->getVar('database');
        $kode_jurnal = $this->request->getVar('kode_jurnal');
        $keterangan  = $this->request->getVar('keterangan');

        $totalDebit  = 0;
        $totalKredit = 0;

        foreach ($jurnal as $row) {
            $totalDebit  += (float) str_replace(',', '', $row['debet'] ?? 0);
            $totalKredit += (float) str_replace(',', '', $row['kredit'] ?? 0);
        }

        if ($totalDebit != $totalKredit) {
            return json_encode([
                'status' => false,
                'msg'    => '⚠️ Total Debit dan Kredit tidak balance!'
            ]);
        }

        $dbMap = [
            'ariston' => $this->db_crm_ars,
            'wep'     => $this->db_crm_wep,
            'dtf'     => $this->db_crm_dtf,
            'ariston_bali' => $this->db_crm_ars_bali,
            'wep_bali' => $this->db_crm_wep_bali,
        ];

        $conn = $dbMap[$dbs] ?? $this->db_default;

        $getSetting = $conn->table('sistem')->select('th,bl')->get()->getRow();
        $newKode    = generateKodeJurnal($conn);
        if(empty($data_id)){
            $cekKode    = $conn->table('jv')->select('KDJV')->where('KDJV', $kode_jurnal)->get()->getRow();
            if ($cekKode) {
                return $this->response->setJSON([
                    'status' => false,
                    'msg' => "Kode $kode_jurnal sudah ada, silahkan ganti dengan $newKode"
                ]);
            }
        }

        $dataJv = [
            'KDJV'   => $kode_jurnal,
            'KETJV'  => $keterangan,
            'TGLJV'  => $tanggal,
            'TH'     => $getSetting->th ?? date('Y'),
            'BL'     => $getSetting->bl ?? date('m'),
            'STAT'   => 1,
            'JVTOT'  => $totalDebit,
            'TGLSYS' => date('Y-m-d H:i:s'),
        ];

        $dataJvdet = [];
        foreach ($jurnal as $i => $row) {
            $dataJvdet[] = [
                'KDJV'     => $kode_jurnal,
                'NOU'      => $i + 1,
                'KDCOA'    => $row['akun'],
                'JVDEBET'  => clearNumber($row['debet'] ?? 0),
                'JVKREDIT' => clearNumber($row['kredit'] ?? 0),
                'KET'      => $row['ket'] ?? null,
            ];
        }

        try {
            $conn->transBegin();

            if (empty($data_id)) {
                $conn->table('jv')->insert($dataJv);
            } else {
                $conn->table('jv')->where('KDJV', $id)->update($dataJv);
                $conn->table('jvdet')->where('KDJV', $id)->delete();
            }

            $conn->table('jvdet')->insertBatch($dataJvdet);

            if ($conn->transStatus() === false) {
                $conn->transRollback();
                return $this->response->setJSON(['status' => false, 'msg' => isLang('save_gagal')]);
            }

            $conn->transCommit();
            return $this->response->setJSON(['status' => true, 'msg' => isLang('save_sukses')]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function edit()
    {
        $jurnalAccess = checkMenuAccess('cms/jurnal');
        
        if ($jurnalAccess['can_edit'] == false) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Anda Tidak Punya Akses']);
          }else if ($jurnalAccess['can_edit'] == null) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Anda Tidak Punya Akses']);
          }else{
            $jurnalAccess['can_edit'] = true;
        }    
                
        $idWithDb = $this->request->getVar('id'); 

        if (!$idWithDb || !str_contains($idWithDb, '|')) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Format ID tidak valid']);
        }

        list($id, $db) = explode('|', $idWithDb); 

        $conn = match ($db) {
            'dtf' => $this->db_crm_dtf,
            'wep' => $this->db_crm_wep,
            'ariston' => $this->db_crm_ars,
            'sdkom' => $this->db_default,
            'ariston_bali' => $this->db_crm_ars_bali,
            'wep_bali' => $this->db_crm_wep_bali,
            default => null,
        };

        if (!$conn) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Database tidak dikenali']);
        }

        $jurnal = $conn->table('jv')->where('KDJV', $id)->get()->getRow();
        $detail = $conn->table('jvdet')
            ->select('KDCOA as akun, JVDEBET as debet, JVKREDIT as kredit, KET as ket')
            ->where('KDJV', $id)->orderBy('NOU','ASC')->get()->getResult();

        if ($jurnal) {
            return $this->response->setJSON([
                'status' => true,
                'data' => [
                    'kode_jurnal'   => $jurnal->KDJV,
                    'tanggal'       => date('Y-m-d', strtotime($jurnal->TGLJV)),
                    'keterangan'    => $jurnal->KETJV,
                    'database'      => $db,
                    'jurnal_detail' => $detail
                ]
            ]);
        }

        return $this->response->setJSON(['status' => false, 'msg' => 'Data tidak ditemukan']);
    }

    public function delete()
    {
        $idWithDb = $this->request->getVar('id');

        if (!$idWithDb || !str_contains($idWithDb, '|')) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Format ID tidak valid']);
        }

        list($id, $db) = explode('|', $idWithDb);

        $conn = match ($db) {
            'dtf' => $this->db_crm_dtf,
            'wep' => $this->db_crm_wep,
            'ariston' => $this->db_crm_ars,
            'sdkom' => $this->db_default,
            'ariston_bali' => $this->db_crm_ars_bali,
            'wep_bali' => $this->db_crm_wep_bali,
            default => null,
        };

        if (!$conn) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Database tidak dikenali']);
        }

        $conn->table('jvdet')->where('KDJV', $id)->delete();
        $conn->table('jv')->where('KDJV', $id)->delete();

        return $this->response->setJSON(['status' => true]);
    }
}
