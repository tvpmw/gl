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
                $id = $value['kode_akun'];

                // Tombol aksi
                $aksi = '<button class="btn btn-sm btn-light detailLR" data-id="'.$id.'" title="View">
                                <i class="fas fa-eye"></i>
                            </button>';
                if(empty($parent_akun)){
                    $lists[$tipe][$kdsub][] = [
                        'tipe' => $tipe,
                        'kdsub' => $kdsub,
                        'rekening' => $value['rekening'],
                        'Level' => $value['Level'],
                        'kode_akun' => $value['kode_akun'],
                        'parent_akun' => $value['parent_akun'],
                        'nama_akun' => $value['nama_akun'],
                        'nilai' => $value['nilai'],
                        'aksi' => $aksi,
                    ];
                }

                if(!empty($parent_akun)){
                    $listsKe3[$parent_akun][] = [
                        'tipe' => $tipe,
                        'kdsub' => $kdsub,
                        'rekening' => $value['rekening'],
                        'Level' => $value['Level'],
                        'kode_akun' => $value['kode_akun'],
                        'parent_akun' => $value['parent_akun'],
                        'nama_akun' => $value['nama_akun'],
                        'nilai' => $value['nilai'],
                        'aksi' => $aksi,
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
        // pr($data,1);

        return view('report/labarugi', $data);
    }

    public function neraca($th=null,$bl=null,$db=null)
    {
        if(empty($th) || empty($bl) || empty($db)){
            return redirect()->to('cms/dashboard/neraca');
        }

        $data['periode'] = getMonths($bl).' '.$th;
        return view('report/neraca', $data);
    }
}
