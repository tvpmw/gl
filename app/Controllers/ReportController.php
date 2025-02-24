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
        $data['nmpt'] = $nmpt;
        $data['lists'] = $getBB;
        $data['akun'] = $mdl->where('KDCOA',$kdcoa)->first();

        // pr($data,1);
        return view('report/bukubesar', $data);
    }
}
