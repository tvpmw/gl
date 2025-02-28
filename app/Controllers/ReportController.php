<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CoaModel;
use App\Models\SubcoaModel;

class ReportController extends BaseController
{
    protected $coaModel;
    protected $coaModel2;
    protected $coaModel3;
    protected $coaModel4;
    protected $subcoaModel;
    protected $subcoaModel2;
    protected $subcoaModel3;
    protected $subcoaModel4;

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
    }

    public function index()
    {
        //
    }

    public function labaRugi($th=null,$bl=null,$db=null)
    {
        if(empty($th) || empty($bl) || empty($db)){
            return redirect()->to('cms/dashboard');
        }

        // Ambil data berdasarkan pilihan database
        switch ($db) {
            case 'ariston':
                $getLR = $this->coaModel2->getLaporanRekening($th,$bl);
                $getAkun = $this->subcoaModel2->getSubLabaRugi();
                $nmpt = 'Ariston';
                break;
            case 'wep':
                $getLR = $this->coaModel3->getLaporanRekening($th,$bl);
                $getAkun = $this->subcoaModel3->getSubLabaRugi();
                $nmpt = 'Wahana Eka Pekasa';
                break;
            case 'dtf':
                $getLR = $this->coaModel4->getLaporanRekening($th,$bl);
                $getAkun = $this->subcoaModel4->getSubLabaRugi();
                $nmpt = 'DTF';
                break;
            default:
                $getLR = $this->coaModel->getLaporanRekening($th,$bl);
                $getAkun = $this->subcoaModel->getSubLabaRugi();
                $nmpt = 'PT Sadar Jaya Mandiri';
        }

        $lists = [];
        $listsKe3 = [];
        if (!empty($getLR)) {
            foreach ($getLR as $value) {
                $tipe = $value['tipe'];
                $kdsub = $value['kdsub'];
                $parent_akun = $value['parent_akun'];
                $level = $value['level'];
                $id = $th.'/'.$bl.'/'.$db.'/'.$value['kode_akun'];
                $kode_akun = $value['kode_akun'];
                $nama_akun = $value['nama_akun'];
                $setKet = $kode_akun.' '.$nama_akun;

                if(empty($value['nilai'])){
                    $ket = $setKet;
                }else{
                    $ket = '<a href="'.base_url('cms/report/bukubesar/'.$id).'" >'.$setKet.'</a>';
                }
                if($level == 1){
                    $lists[$tipe][$kdsub][] = [
                        'tipe' => $tipe,
                        'kdsub' => $kdsub,
                        'rekening' => $value['rekening'],
                        'level' => $value['level'],
                        'kode_akun' => $kode_akun,
                        'parent_akun' => $value['parent_akun'],
                        'nama_akun' => $nama_akun,
                        'nilai' => $value['nilai'],
                        'ket' => $ket,
                    ];
                }

                if($level != 1){
                    $listsKe3[$parent_akun][] = [
                        'tipe' => $tipe,
                        'kdsub' => $kdsub,
                        'rekening' => $value['rekening'],
                        'level' => $value['level'],
                        'kode_akun' => $kode_akun,
                        'parent_akun' => $value['parent_akun'],
                        'nama_akun' => $nama_akun,
                        'nilai' => $value['nilai'],
                        'ket' => $ket,
                    ];
                }
            }
        }

        $listAkun = [];
        foreach($getAkun as $akun){
            $key = $akun['tipe'];
            $listAkun[$key][] = $akun;
        }

        $data['periode'] = getMonths($bl).' '.$th;
        $data['nmpt'] = $nmpt;
        $data['akuns'] = $listAkun;
        $data['lists'] = $lists;
        $data['listsKe3'] = $listsKe3;
        $data['startYear'] = 2009;
        $data['thnSel'] = $th;
        $data['bln'] = getMonths();
        $data['blnSel'] = $bl;
        $data['dbs'] = getSelDb();
        $data['dbSel'] = $db;
        // pr($data,1);

        return view('report/labarugi', $data);
    }

    public function filterLabaRugi()
    {
        $data['startYear'] = 2009;
        $data['thnSel'] = date('Y');
        $data['bln'] = getMonths();
        $data['blnSel'] = date('m');
        $data['dbs'] = getSelDb();
        $data['dbSel'] = 'sdkom';
        return view('report/labarugi-filter', $data);
    }

    public function neraca($th=null,$bl=null,$db=null)
    {
        if(empty($th) || empty($bl) || empty($db)){
            return redirect()->to('cms/dashboard/neraca');
        }

        // Ambil data berdasarkan pilihan database
        switch ($db) {
            case 'ariston':
                $getNR = $this->coaModel2->getLaporanNeraca($th,$bl);
                $getLbt = $this->coaModel2->getLabaRugiTahunBerjalan($th,$bl);
                $getAkun = $this->subcoaModel2->getSubNeraca();
                $mdl = $this->coaModel2;
                $nmpt = 'Ariston';
                break;
            case 'wep':
                $getNR = $this->coaModel3->getLaporanNeraca($th,$bl);
                $getLbt = $this->coaModel3->getLabaRugiTahunBerjalan($th,$bl);
                $getAkun = $this->subcoaModel3->getSubNeraca();
                $mdl = $this->coaModel3;
                $nmpt = 'Wahana Eka Pekasa';
                break;
            case 'dtf':
                $getNR = $this->coaModel4->getLaporanNeraca($th,$bl);
                $getLbt = $this->coaModel4->getLabaRugiTahunBerjalan($th,$bl);
                $getAkun = $this->subcoaModel4->getSubNeraca();
                $mdl = $this->coaModel4;
                $nmpt = 'DTF';
                break;
            default:
                $getNR = $this->coaModel->getLaporanNeraca($th,$bl);
                $getLbt = $this->coaModel->getLabaRugiTahunBerjalan($th,$bl);
                $getAkun = $this->subcoaModel->getSubNeraca();
                $mdl = $this->coaModel;
                $nmpt = 'PT Sadar Jaya Mandiri';
        }

        $lists = [];
        $listsKe3 = [];
        if (!empty($getNR)) {
            foreach ($getNR as $value) {
                $tipe = $value['tipe'];
                $kdsub = $value['kdsub'];
                $parent_akun = $value['parent_akun'];
                $level = $value['level'];
                $id = $th.'/'.$bl.'/'.$db.'/'.$value['kode_akun'];
                $kode_akun = $value['kode_akun'];
                $nama_akun = $value['nama_akun'];
                $setKet = $kode_akun.' '.$nama_akun;

                if(empty($value['nilai'])){
                    $ket = $setKet;
                }else{
                    $ket = '<a href="'.base_url('cms/report/bukubesar/'.$id).'" >'.$setKet.'</a>';
                }
                if($level == 1){
                    $lists[$tipe][$kdsub][] = [
                        'tipe' => $tipe,
                        'kdsub' => $kdsub,
                        'rekening' => $value['rekening'],
                        'level' => $value['level'],
                        'kode_akun' => $kode_akun,
                        'parent_akun' => $value['parent_akun'],
                        'nama_akun' => $nama_akun,
                        'nilai' => $value['nilai'],
                        'ket' => $ket
                    ];
                }

                if($level != 1){
                    $listsKe3[$parent_akun][] = [
                        'tipe' => $tipe,
                        'kdsub' => $kdsub,
                        'rekening' => $value['rekening'],
                        'level' => $value['level'],
                        'kode_akun' => $kode_akun,
                        'parent_akun' => $value['parent_akun'],
                        'nama_akun' => $nama_akun,
                        'nilai' => $value['nilai'],
                        'ket' => $ket
                    ];
                }
            }
        }

        $listAkun = [];
        foreach($getAkun as $akun){
            $cek = $mdl->where(['KDSUB'=>$akun['kdsub']])->first();
            if(!empty($cek)){
                $key = $akun['tipe'];
                $listAkun[$key][] = $akun;
            }
        }

        $data['periode'] = getMonths($bl).' '.$th;
        $data['nmpt'] = $nmpt;
        $data['akuns'] = $listAkun;
        $data['lists'] = $lists;
        $data['listsKe3'] = $listsKe3;
        $data['lrtb'] = $getLbt;
        $data['startYear'] = 2009;
        $data['thnSel'] = $th;
        $data['bln'] = getMonths();
        $data['blnSel'] = $bl;
        $data['dbs'] = getSelDb();
        $data['dbSel'] = $db;
        // pr($data,1);

        return view('report/neraca', $data);
    }

    public function filterNeraca()
    {
        $data['startYear'] = 2009;
        $data['thnSel'] = date('Y');
        $data['bln'] = getMonths();
        $data['blnSel'] = date('m');
        $data['dbs'] = getSelDb();
        $data['dbSel'] = 'sdkom';
        return view('report/neraca-filter', $data);
    }

    public function bukuBesar($th=null,$bl=null,$db=null,$kdcoa=null)
    {
        if(empty($th) || empty($bl) || empty($db) || empty($kdcoa)){
            return redirect()->back();
        }

        // Ambil data berdasarkan pilihan database
        switch ($db) {
            case 'ariston':
                $getBB = $this->coaModel2->getJurnalData($kdcoa,$th,$bl);
                $mdl = $this->coaModel2;
                $nmpt = 'Ariston';
                break;
            case 'wep':
                $getBB = $this->coaModel3->getJurnalData($kdcoa,$th,$bl);
                $mdl = $this->coaModel3;
                $nmpt = 'Wahana Eka Pekasa';
                break;
            case 'dtf':
                $getBB = $this->coaModel4->getJurnalData($kdcoa,$th,$bl);
                $mdl = $this->coaModel4;
                $nmpt = 'DTF';
                break;
            default:
                $getBB = $this->coaModel->getJurnalData($kdcoa,$th,$bl);
                $mdl = $this->coaModel;
                $nmpt = 'PT Sadar Jaya Mandiri';
        }

        $data['periode'] = getMonths($bl).' '.$th;
        $data['dbs'] = $db;
        $data['nmpt'] = $nmpt;
        $data['lists'] = $getBB;
        $data['akun'] = $mdl->where('KDCOA',$kdcoa)->first();

        // pr($data,1);
        return view('report/bukubesar', $data);
    }

    public function filterBukuBesar()
    {
        $data['dbs'] = getSelDb();
        return view('report/bukubesar-filter', $data);
    }

    public function searchRekening()
    {
        $search = $this->request->getGet('search');
        $db = $this->request->getGet('db');

        // Ambil data berdasarkan pilihan database
        switch ($db) {
            case 'ariston':
                $mdl = $this->coaModel2;
                $nmpt = 'Ariston';
                break;
            case 'wep':
                $mdl = $this->coaModel3;
                $nmpt = 'Wahana Eka Pekasa';
                break;
            case 'dtf':
                $mdl = $this->coaModel4;
                $nmpt = 'DTF';
                break;
            default:
                $mdl = $this->coaModel;
                $nmpt = 'PT Sadar Jaya Mandiri';
        }

        $data = $mdl->select('KDCOA as kdcoa, NMCOA as nmcoa')
            ->like('"KDCOA"', $search, 'both', null, true)
            ->orLike('"NMCOA"', $search, 'both', null, true)
            ->findAll(10);

        return $this->response->setJSON(array_map(function($item) {
            return [
                'id' => $item->kdcoa,
                'nama_rekening' => $item->kdcoa.' - '.$item->nmcoa
            ];
        }, $data));
    }

    public function resultBukuBesar()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $dbs = $input['dbs'] ?? 'sdkom';
        $tanggal_awal = $input['tanggal_awal'];
        $tanggal_akhir = $input['tanggal_akhir'];
        $rekening_id = $input['rekening_id'];

        if(empty($tanggal_awal) || empty($tanggal_akhir) || empty($rekening_id)){
            return "Form wajib diisi";
        }

        // Ambil data berdasarkan pilihan database
        switch ($dbs) {
            case 'ariston':
                $getBB = $this->coaModel2->getJurnalDataByDate($rekening_id,$tanggal_awal,$tanggal_akhir);
                $mdl = $this->coaModel2;
                $nmpt = 'Ariston';
                break;
            case 'wep':
                $getBB = $this->coaModel3->getJurnalDataByDate($rekening_id,$tanggal_awal,$tanggal_akhir);
                $mdl = $this->coaModel3;
                $nmpt = 'Wahana Eka Pekasa';
                break;
            case 'dtf':
                $getBB = $this->coaModel4->getJurnalDataByDate($rekening_id,$tanggal_awal,$tanggal_akhir);
                $mdl = $this->coaModel4;
                $nmpt = 'DTF';
                break;
            default:
                $getBB = $this->coaModel->getJurnalDataByDate($rekening_id,$tanggal_awal,$tanggal_akhir);
                $mdl = $this->coaModel;
                $nmpt = 'PT Sadar Jaya Mandiri';
        }

        $data['periode'] = format_date($tanggal_awal).' s/d '.format_date($tanggal_akhir);
        $data['dbs'] = $dbs;
        $data['nmpt'] = $nmpt;
        $data['lists'] = $getBB;
        $data['akun'] = $mdl->where('KDCOA',$rekening_id)->first();

        // pr($data,1);
        return view('report/bukubesar-result', $data);
    }

    public function filterBukuBesarKet()
    {
        $data['dbs'] = getSelDb();
        $data['thnSkg'] = date('Y');
        $data['startYear'] = 2009;
        return view('report/bukubesarket-filter', $data);
    }

    public function resultBukuBesarKet()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $dbs = $input['dbs'] ?? 'sdkom';
        $tahun = $input['tahun'];
        $keterangan = $input['keterangan'];

        if(empty($tahun) || empty($keterangan)){
            return "Form wajib diisi";
        }

        // Ambil data berdasarkan pilihan database
        switch ($dbs) {
            case 'ariston':
                $getBB = $this->coaModel2->getJurnalDataByKet($keterangan,$tahun);
                $mdl = $this->coaModel2;
                $nmpt = 'Ariston';
                break;
            case 'wep':
                $getBB = $this->coaModel3->getJurnalDataByKet($keterangan,$tahun);
                $mdl = $this->coaModel3;
                $nmpt = 'Wahana Eka Pekasa';
                break;
            case 'dtf':
                $getBB = $this->coaModel4->getJurnalDataByKet($keterangan,$tahun);
                $mdl = $this->coaModel4;
                $nmpt = 'DTF';
                break;
            default:
                $getBB = $this->coaModel->getJurnalDataByKet($keterangan,$tahun);
                $mdl = $this->coaModel;
                $nmpt = 'PT Sadar Jaya Mandiri';
        }

        $data['periode'] = $tahun;
        $data['dbs'] = $dbs;
        $data['nmpt'] = $nmpt;
        $data['lists'] = $getBB;
        $data['mdl'] = $mdl;

        // pr($data,1);
        return view('report/bukubesarket-result', $data);
    }

    public function filterPerubahanModal()
    {
        $data['dbs'] = getSelDb();
        $data['thnSkg'] = date('Y');
        $data['startYear'] = 2009;
        $data['blnSel'] = date('m');
        $data['bln'] = getMonths();
        return view('report/perubahanmodal-filter', $data);
    }

    public function resultPerubahanModal()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $db = $input['dbs'] ?? 'sdkom';
        $bl = $input['bulan'];
        $th = $input['tahun'];
        $thSblm = $th-1;
        $blSblm = 12;
        $tipe = 3;

        if(empty($th) || empty($bl)){
            return "Form wajib diisi";
        }

        // Ambil data berdasarkan pilihan database
        switch ($db) {
            case 'ariston':
                $getNR = $this->coaModel2->getLaporanNeraca($thSblm,$blSblm,$tipe);
                $getLbt = $this->coaModel2->getLabaRugiTahunBerjalan($th,$bl);
                $getAkun = $this->subcoaModel2->getSubNeraca();
                $mdl = $this->coaModel2;
                $nmpt = 'Ariston';
                break;
            case 'wep':
                $getNR = $this->coaModel3->getLaporanNeraca($thSblm,$blSblm,$tipe);
                $getLbt = $this->coaModel3->getLabaRugiTahunBerjalan($th,$bl);
                $getAkun = $this->subcoaModel3->getSubNeraca();
                $mdl = $this->coaModel3;
                $nmpt = 'Wahana Eka Pekasa';
                break;
            case 'dtf':
                $getNR = $this->coaModel4->getLaporanNeraca($thSblm,$blSblm,$tipe);
                $getLbt = $this->coaModel4->getLabaRugiTahunBerjalan($th,$bl);
                $getAkun = $this->subcoaModel4->getSubNeraca();
                $mdl = $this->coaModel4;
                $nmpt = 'DTF';
                break;
            default:
                $getNR = $this->coaModel->getLaporanNeraca($th,$bl,$tipe);
                $getLbt = $this->coaModel->getLabaRugiTahunBerjalan($th,$bl);
                $getAkun = $this->subcoaModel->getSubNeraca();
                $mdl = $this->coaModel;
                $nmpt = 'PT Sadar Jaya Mandiri';
        }

        $lists = [];
        $listsKe3 = [];
        if (!empty($getNR)) {
            foreach ($getNR as $value) {
                $tipe = $value['tipe'];
                $kdsub = $value['kdsub'];
                $parent_akun = $value['parent_akun'];
                $level = $value['level'];
                $id = $th.'/'.$bl.'/'.$db.'/'.$value['kode_akun'];
                $kode_akun = $value['kode_akun'];
                $nama_akun = $value['nama_akun'];
                $setKet = $kode_akun.' '.$nama_akun;

                if($level == 1){
                    $lists[] = [
                        'tipe' => $tipe,
                        'kdsub' => $kdsub,
                        'rekening' => $value['rekening'],
                        'level' => $value['level'],
                        'kode_akun' => $kode_akun,
                        'parent_akun' => $value['parent_akun'],
                        'nama_akun' => $nama_akun,
                        'nilai' => $value['nilai'],
                        'ket' => $setKet
                    ];
                }

                if($level != 1){
                    $listsKe3[$parent_akun][] = [
                        'tipe' => $tipe,
                        'kdsub' => $kdsub,
                        'rekening' => $value['rekening'],
                        'level' => $value['level'],
                        'kode_akun' => $kode_akun,
                        'parent_akun' => $value['parent_akun'],
                        'nama_akun' => $nama_akun,
                        'nilai' => $value['nilai'],
                        'ket' => $setKet
                    ];
                }
            }
        }

        $listModal = [];
        foreach ($lists as $k => $row) {
            $total = (isset($listsKe3[$row['kode_akun']]))?array_sum(array_column($listsKe3[$row['kode_akun']],'nilai')):$row['nilai'];
            $listModal[] = [
                'akun' => $row['nama_akun'],
                'total' => $total,
            ];
        }

        $data['periode_pilih'] = getMonths($bl).' '.$th;
        $data['periode_lalu'] = getMonths($blSblm).' '.$thSblm;
        $data['nmpt'] = $nmpt;
        $data['lists'] = $listModal;
        $data['lrtb'] = $getLbt;
        // pr($data,1);
        return view('report/perubahanmodal-result', $data);
    }
}
