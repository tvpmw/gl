<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\MstrModel;

class FakturController extends Controller
{
    protected $mstrModel;
    protected $mstrModel2;
    protected $mstrModel3;
    protected $mstrModel4;
    protected $mstrModel5;
    protected $mstrModel6;
    protected $db_default;
    protected $db_crm_ars;
    protected $db_crm_wep;
    protected $db_crm_dtf;
    protected $db_crm_ars_bali;
    protected $db_crm_wep_bali;

    public function __construct()
    {
        helper(['my_helper']);

        $this->mstrModel = new MstrModel('default');
        $this->mstrModel2 = new MstrModel('crm_ars');        
        $this->mstrModel3 = new MstrModel('crm_wep');
        $this->mstrModel4 = new MstrModel('crm_dtf');
        $this->mstrModel5 = new MstrModel('crm_ars_bali');
        $this->mstrModel6 = new MstrModel('crm_wep_bali');

        $this->db_default = \Config\Database::connect('default');
        $this->db_crm_ars = \Config\Database::connect('crm_ars');
        $this->db_crm_wep = \Config\Database::connect('crm_wep');
        $this->db_crm_dtf = \Config\Database::connect('crm_dtf');
        $this->db_crm_ars_bali = \Config\Database::connect('crm_ars_bali');
        $this->db_crm_wep_bali = \Config\Database::connect('crm_wep_bali');
    }

    public function index()
    {
        $data['dbs'] = getSelDb();
        return view('faktur/form',$data);
    }

    public function import()
    {        
        $data['dbs'] = getSelDb();
        return view('faktur/import',$data);
    }

    public function getData()
    {
        $request = service('request');
        
        $startDate = $request->getPost('startDate') ?? date('Y-m-d');
        $endDate = $request->getPost('endDate') ?? $startDate;
        $sales_type = $request->getPost('sales_type');
        $dbs = $request->getPost('sumber_data');

        if($startDate > $endDate){
            $endDate = $startDate;
        }

        switch ($dbs) {
            case 'ariston':
                $mdl = $this->mstrModel2;
                $prefix = 'A';
                break;
            case 'wep':
                $mdl = $this->mstrModel3;
                $prefix = 'W';
                break;
            case 'dtf':
                $mdl = $this->mstrModel4;
                $prefix = 'B';
                break;
            case 'ariston_bali':
                $mdl = $this->mstrModel5;
                $prefix = 'AB';
                break;
            case 'wep_bali':
                $mdl = $this->mstrModel6;
                $prefix = 'MB';
                break;
            default:
                $mdl = $this->mstrModel;
                $prefix = 'K';
        }

        $getNpwp = $mdl->getDataNpwp($startDate, $endDate, $sales_type, $prefix);
        if(!empty($getNpwp)){
            $listNpwp = [];
            foreach ($getNpwp as $value) {
                $npwp = cleanString($value->npwp);
                $listNpwp[$npwp] = $value->npwp;
            }
            $this->prosesNpwp($listNpwp,$dbs);
        }

        $data = $mdl->getAllData($startDate, $endDate, $sales_type, $prefix);
        
        $formattedData = [];
        foreach ($data as $row) {
            $akt = '<span class="badge text-bg-danger">INVALID</span>';
            if($row->status_wp == 'VALID'){
                $akt = '<span class="badge text-bg-success">VALID</span>';
            }

            $formattedData[] = [
                '',  
                $row->kdtr,
                format_date($row->tgl,'d/m/Y'),
                format_price($row->gtot),
                $row->nmcust,
                $row->newnpwp,
                $row->name,
                $row->jenis,
                $akt,
                ''  
            ];
        }

        return $this->response->setJSON([
            'data' => $formattedData
        ]);
    }

    public function tidakDibuat()
    {
        try {
            $rawInput = $this->request->getBody();
            $request = json_decode($rawInput, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON data received');
            }

            if (empty($request['data'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tidak ada data yang dipilih'
                ]);
            }

            $insertData = [];
            $updateData = [];
            $now = date('Y-m-d H:i:s');

            foreach ($request['data'] as $row) {            
                $dateParts = explode('/', $row['tanggal']);
                if (count($dateParts) === 3) {
                    $month = $dateParts[1];
                    $day = $dateParts[0];
                    $year = $dateParts[2];
                    $tanggal = sprintf('%s-%02d-%02d', $year, $month, $day);
                } else {
                    throw new \Exception('Invalid date format received: ' . $row['tanggal']);
                }

                $grand_total = preg_replace('/[^0-9]/', '', $row['grand_total']);
                
                $data = [
                    'kode_trx' => trim($row['kode_trx']),
                    'tanggal' => $tanggal,
                    'grand_total' => (float) $grand_total,
                    'nama_customer' => $row['nama_customer'],
                    'sumber_data' => $row['sumber_data']
                ];

                switch ($row['sumber_data']) {
                    case 'ariston':
                        $mdl = $this->db_crm_ars;
                        $db_config = 'crm_ars';
                        break;
                    case 'wep':
                        $mdl = $this->db_crm_wep;
                        $db_config = 'crm_wep';
                        break;
                    case 'dtf':
                        $mdl = $this->db_crm_dtf;
                        $db_config = 'crm_dtf';
                        break;
                    case 'ariston_bali':
                        $mdl = $this->db_crm_ars_bali;
                        $db_config = 'crm_ars_bali';
                        break;
                    case 'wep_bali':
                        $mdl = $this->db_crm_wep_bali;
                        $db_config = 'crm_wep_bali';
                        break;
                    default:
                        $mdl = $this->db_default;
                        $db_config = 'default';
                }

                $existing = $mdl->table('crm.tidak_dibuat')
                    ->where('kode_trx', $data['kode_trx'])
                    ->get()
                    ->getRow();

                if ($existing) {
                    $data['updated_at'] = $now;
                    $updateData[] = $data;
                } else {
                    $data['created_at'] = $now;
                    $data['updated_at'] = null;
                    $insertData[] = $data;
                }
            }

            $success = true;
            $message = [];

            if (!empty($insertData)) {
                $insertResult = $mdl->table('crm.tidak_dibuat')
                    ->insertBatch($insertData);
                
                if (!$insertResult) {
                    $success = false;
                    $message[] = 'Gagal menyimpan data baru';
                } else {
                    $message[] = count($insertData) . ' data baru berhasil disimpan';
                }
            }

            if (!empty($updateData)) {
                $mdl->query('CREATE TEMPORARY TABLE tmp_update AS SELECT * FROM crm.tidak_dibuat WITH NO DATA');
                
                foreach ($updateData as $row) {
                    $mdl->table('tmp_update')->insert($row);
                }

                $sql = "UPDATE crm.tidak_dibuat t 
                        SET tanggal = CAST(u.tanggal AS date),
                            grand_total = u.grand_total,
                            nama_customer = u.nama_customer,
                            sumber_data = u.sumber_data,
                            updated_at = u.updated_at
                        FROM tmp_update u 
                        WHERE t.kode_trx = u.kode_trx";

                $updateResult = $mdl->query($sql);
                
                if (!$updateResult) {
                    $success = false;
                    $message[] = 'Gagal mengupdate data';
                } else {
                    $message[] = count($updateData) . ' data berhasil diupdate';
                }

                $mdl->query('DROP TABLE IF EXISTS tmp_update');
            }

            logGL($db_config,'faktur_tidakDibuat','Insert');
            return $this->response->setJSON([
                'success' => $success,
                'message' => implode(', ', $message)
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error saving tidak dibuat: ' . $e->getMessage());
            log_message('error', 'Raw input: ' . $rawInput);
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function prosesNpwp($listNpwp,$dbs)
    {
        $header = NULL;
        $lists = array_keys($listNpwp);
        $url = 'https://gl.sadarjaya.com/api/npwp/check-bulk?npwp=' . implode(',', $lists);

        $hitData = reqApi($url, 'GET', NULL, $header);

        if (!is_array($hitData)) {
            return ['error' => 'Gagal mendapatkan response dari API'];
        }

        $invalidNpwp = array_values($hitData['invalid_npwp'] ?? []);
        if ($invalidNpwp) {
            $validNpwp = array_filter($lists, function ($npwp) use ($invalidNpwp) {
                return !in_array($npwp, $invalidNpwp);
            });

            if (empty($validNpwp)) {
                return ['error' => 'Semua NPWP tidak valid'];
            }

            $cleanedRequest = implode(',', $validNpwp);
            $url = 'https://gl.sadarjaya.com/api/npwp/check-bulk?npwp=' . $cleanedRequest;
            $hitData = reqApi($url, 'GET', NULL, $header);
        }

        switch ($dbs) {
            case 'ariston':
                $mdl = $this->db_crm_ars;
                break;
            case 'wep':
                $mdl = $this->db_crm_wep;
                break;
            case 'dtf':
                $mdl = $this->db_crm_dtf;
                break;
            case 'ariston_bali':
                $mdl = $this->db_crm_ars_bali;
                break;
            case 'wep_bali':
                $mdl = $this->db_crm_wep_bali;
                break;
            default:
                $mdl = $this->db_default;
        }

        $insertData = [];
        $updateData = [];

        foreach($listNpwp as $npwp => $npwpOld){
            $jenis = 'National ID';
            $name = null;
            $address = null;
            $status_wp = 'INVALID';
            $npwpOld = (string) $npwpOld;
            if(isset($hitData[$npwp]['response'])){
                $dtResp = $hitData[$npwp]['response'];
                if(isset($dtResp['data']['status_wp']) && $dtResp['data']['status_wp'] == 'VALID'){
                    $jenis = 'TIN';
                }

                $name = $dtResp['data']['name'] ?? null;
                $address = $dtResp['data']['address'] ?? null;
                $status_wp = $dtResp['data']['status_wp'] ?? 'INVALID';
            }
            $set['npwp'] = (string)$npwp;
            $set['jenis'] = $jenis;
            $set['name'] = $name;
            $set['address'] = $address;
            $set['status_wp'] = $status_wp;
            $set['sumber_data'] = $dbs;
            $set['npwpcust'] = $npwpOld;
            $set['resp'] = (isset($hitData[$npwp]))?json_encode($hitData[$npwp]):null;

            $cekData = $mdl->table('crm.cust_npwp')->where('npwpcust',$npwpOld)->get()->getRow();
            if($cekData){
                $set['updated_at'] = date('Y-m-d H:i:s');
                $updateData[] = $set;
            }else{
                $set['created_at'] = date('Y-m-d H:i:s');
                $set['updated_at'] = null;
                $insertData[] = $set;
            }
        }

        if(!empty($insertData)){
            $mdl->table('crm.cust_npwp')->insertBatch($insertData);
        }

        if(!empty($updateData)){
            foreach ($updateData as $row) {
                $row['updated_at'] = date('Y-m-d H:i:s'); 
                $mdl->table('crm.cust_npwp')
                    ->where('npwpcust', $row['npwpcust'])
                    ->update($row);
            }
        }

        return true;
    }

    public function generate()
    {
        $bulan = $this->request->getPost('bulan');
        $tahun = $this->request->getPost('tahun');
        $sales_type = $this->request->getPost('sales_type');
        $sumber_data = $this->request->getPost('sumber_data');

        if (!$bulan || !$tahun || !$sales_type || !$sumber_data) {
            return redirect()->back()->with('error', 'Semua field harus diisi');
        }

        $data = [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'sales_type' => $sales_type,
            'sumber_data' => $sumber_data
        ];

        return view('faktur/form', $data);
    }

    public function generate_excel()
    {
        try {
            $request = service('request');
            
            $startDate = $request->getGet('startDate') ?? date('Y-m-d');
            $endDate = $request->getGet('endDate') ?? $startDate;
            $sales_type = $request->getGet('sales_type');
            $dbs = $request->getGet('sumber_data');
            $selectedTrx = $request->getGet('selected_trx');

            switch ($dbs) {
                case 'ariston':
                    $mdl = $this->mstrModel2;
                    $prefix = 'A';
                    $tku = '0210642716526000000000';
                    $db = $this->db_crm_ars;
                    $db_config = 'crm_ars';
                    break;
                case 'wep':
                    $mdl = $this->mstrModel3;
                    $prefix = 'W';
                    $tku = '0137755021526000000000';
                    $db = $this->db_crm_wep;
                    $db_config = 'crm_wep';
                    break;
                case 'dtf':
                    $mdl = $this->mstrModel4;
                    $prefix = 'B';
                    $tku = '0316396407526000000000';
                    $db = $this->db_crm_dtf;
                    $db_config = 'crm_dtf';
                    break;
                case 'ariston_bali':
                    $mdl = $this->mstrModel5;
                    $prefix = 'AB';
                    $tku = '0316396407526000000000';
                    $db = $this->db_crm_ars_bali;
                    $db_config = 'crm_ars_bali';
                    break;
                case 'wep_bali':
                    $mdl = $this->mstrModel6;
                    $prefix = 'MB';
                    $tku = '0137755021526000000000';
                    $db = $this->db_crm_wep_bali;
                    $db_config = 'crm_wep_bali';
                    break;
                default:
                    $mdl = $this->mstrModel;
                    $prefix = 'K';
                    $tku = '0316396407526000000000';
                    $db = $this->db_default;
                    $db_config = 'default';
            }

            $rawData = $mdl->getAllData($startDate, $endDate, $sales_type, $prefix);            
            usort($rawData, function ($a, $b) {
                return strcmp($a->kdtr, $b->kdtr);
            });

            if (!empty($selectedTrx)) {
                $selectedTrxArray = explode(',', $selectedTrx);
                $rawData = array_filter($rawData, function($row) use ($selectedTrxArray) {
                    return in_array($row->kdtr, $selectedTrxArray);
                });
            }

            $existingTrx = $db->table('crm.tax_generate')
                ->select('kode_trx')
                ->get()
                ->getResultArray();

            $existingTrxList = array_column($existingTrx, 'kode_trx');

            $rawData = array_filter($rawData, function($row) use ($existingTrxList) {
                return !in_array($row->kdtr, $existingTrxList);
            });

            if (empty($rawData)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Semua transaksi sudah ada di database'
                ]);
            }

            if($sales_type == 'DISTRI'){
                return $this->generateDistri($dbs,$rawData,$tku);
            }else if($sales_type == 'ONLINE'){
                return $this->generateOnline($dbs,$rawData,$tku,$startDate);
            }else{
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tipe sales tidak ditemukan'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function generateDistri($dbs,$rawData,$tku)
    {
        $userId = session()->get('user_id') ?? 9999; 
        $now = date('Y-m-d H:i:s');
        $templatePath = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'template.xlsx';
        if (!file_exists($templatePath)) {
            throw new \Exception('File template.xlsx not found at: ' . $templatePath);
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);

        $fakturSheet = $spreadsheet->getSheetByName('Faktur');
        if (!$fakturSheet) {
            throw new \Exception('Worksheet "Faktur" not found in template');
        }

        $detailSheet = $spreadsheet->getSheetByName('DetailFaktur');
        if (!$detailSheet) {
            throw new \Exception('Worksheet "DetailFaktur" not found in template');
        }

        try {
            $rowDetail = 2; 
            $fakturCounter = 1;
            $processedItems = [];
            
            $baseQuery = "SELECT 
                tr.kdtr,
                tr.nmbrg,
                tr.qty,
                tr.hrg,
                tr.disc,
                (tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))/1.11 as dpp_unit,
                ((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))/1.11)*11/12 as dpp_nl,
                tr.tot,
                brg.nama as nama_brg,
                mc.kdtax
            FROM tr
            JOIN brg ON tr.nmbrg = brg.nmbrg 
            LEFT JOIN crm.mapping_coretax mc ON tr.nmbrg = mc.kdbrg
            WHERE tr.kdtr = ?
            ORDER BY tr.kdtr ASC, tr.nmbrg ASC";
        
            $aristonWepQuery = "SELECT 
                tr.kdtr,
                tr.nmbrg,
                tr.qty,
                tr.hrg,
                tr.disc,                    
                ((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))*tr.qty - (mstr.disc/(SELECT SUM(tr2.qty) FROM tr tr2 WHERE tr2.kdtr = tr.kdtr)*tr.qty))/1.11/tr.qty as dpp_unit,                    
                (((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))*tr.qty - (mstr.disc/(SELECT SUM(tr2.qty) FROM tr tr2 WHERE tr2.kdtr = tr.kdtr)*tr.qty))/1.11)*11/12 as dpp_nl,
                ((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))*tr.qty - (mstr.disc/(SELECT SUM(tr2.qty) FROM tr tr2 WHERE tr2.kdtr = tr.kdtr)*tr.qty))/1.11 as dpp,
                tr.tot,
                brg.nama as nama_brg,
                mc.kdtax,
                mstr.disc as disc_faktur
            FROM tr
            JOIN brg ON tr.nmbrg = brg.nmbrg 
            LEFT JOIN crm.mapping_coretax mc ON tr.nmbrg = mc.kdbrg
            LEFT JOIN mstr ON tr.kdtr = mstr.kdtr
            WHERE tr.kdtr = ?
            ORDER BY tr.kdtr ASC, tr.nmbrg ASC";


            $queryRetur = "SELECT 
                tr.kdtr,
                mstr.tgl,
                tr.nmbrg,
                tr.qty
            FROM tr
            LEFT JOIN mstr ON tr.kdtr = mstr.kdtr
            WHERE mstr.kdtr2 = ?
            ORDER BY tr.kdtr ASC, tr.nmbrg ASC";

            switch ($dbs) {
                case 'ariston':
                    $sql = $aristonWepQuery;
                    $db = $this->db_crm_ars;
                    $db_config = 'crm_ars';
                    break;
                case 'wep':
                    $sql = $aristonWepQuery;
                    $db = $this->db_crm_wep;
                    $db_config = 'crm_wep';
                    break;
                case 'dtf':
                    $sql = $baseQuery;
                    $db = $this->db_crm_dtf;
                    $db_config = 'crm_dtf';
                    break;
                case 'ariston_bali':
                    $sql = $aristonWepQuery;
                    $db = $this->db_crm_ars_bali;
                    $db_config = 'crm_ars_bali';
                    break;
                case 'wep_bali':
                    $sql = $aristonWepQuery;
                    $db = $this->db_crm_wep_bali;
                    $db_config = 'crm_wep_bali';
                    break;
                default:
                    $sql = $baseQuery;
                    $db = $this->db_default;
                    $db_config = 'default';
            }


            $db->transStart();
            $listTrx = [];
            foreach ($rawData as $trx) {
                $getRetur = $db->query($queryRetur, [$trx->kdtr])->getResult();  
                $listRetur = [];
                foreach($getRetur as $row){
                    $listRetur[$row->nmbrg] = $row;
                }
                $details = $db->query($sql, [$trx->kdtr])->getResult();
                $processedDetailsForTrx = [];
                foreach ($details as $detail) {
                    // Buat unique key untuk setiap item
                    $uniqueKey = $trx->kdtr . '_' . $detail->nmbrg;
                    
                    // Skip jika item sudah diproses untuk transaksi ini
                    if (in_array($uniqueKey, $processedDetailsForTrx)) {
                        continue;
                    }
                    $cekRetur = $listRetur[$detail->nmbrg] ?? [];
                    $valRetur = $cekRetur->qty ?? 0;
                    $qty = $detail->qty;
                    $tot_qty = $qty-$valRetur;
                    
                    if($tot_qty<=0){
                        continue;
                    }

                    $processedDetailsForTrx[] = $uniqueKey; // Tandai item sebagai sudah diproses
                    $listTrx[] = $detail->kdtr;
                    if (in_array($dbs, ['ariston', 'wep', 'ariston_bali', 'wep_bali'])) {
                        $dpp = $detail->dpp;
                        $dpp_lain = $detail->dpp_nl;
                        $total_disc = $detail->disc_faktur / $detail->qty / 1.11;
                    } else {
                        $dpp = $detail->dpp_unit * $tot_qty;
                        $dpp_lain = $detail->dpp_nl * $tot_qty; 
                        $total_disc = '0.00';
                    }
                    
                    $nominal_ppn = $dpp_lain * 0.12;

                    $taxGenerateDetail = [
                        'faktur_counter' => $fakturCounter,
                        'jenis_barang' => 'A',
                        'kode_brg' => $detail->kdtax ?? '000000',
                        'nama_brg' => $detail->nama_brg,
                        'satuan' => 'UM.0018',
                        'qty' => $tot_qty,
                        'diskon' => number_format($total_disc, 11, '.', ''), 
                        'dpp' => number_format($dpp, 11, '.', ''),
                        'dpp_lain' => number_format($dpp_lain, 11, '.', ''),
                        'tarif_ppn' => 12.00,
                        'nominal_ppn' => number_format($nominal_ppn, 11, '.', ''),
                        'tarif_ppnbm' => 0,
                        'nominal_ppnbm' => number_format(0, 2, '.', ''),
                        'created_at' => $now,
                        'user_id' => $userId,
                        'hrg' => $detail->hrg,
                        'kode_trx' => $trx->kdtr,
                        'nmbrg' => $detail->nmbrg,
                        'diskon_tr' => $detail->disc,
                        'kode_trx_retur' => $cekRetur->kdtr ?? null,
                        'qty_retur' => $valRetur
                    ];

                    $existing = $db->table('crm.tax_generate_brg')
                                  ->where('kode_trx', $trx->kdtr)
                                  ->where('nmbrg', $detail->nmbrg)
                                  ->get()
                                  ->getRow();

                    if ($existing) {
                        $taxGenerateDetail['updated_at'] = $now;
                        $db->table('crm.tax_generate_brg')
                           ->where('kode_trx', $trx->kdtr)
                           ->where('nmbrg', $detail->nmbrg)
                           ->update($taxGenerateDetail);
                    } else {
                        $db->table('crm.tax_generate_brg')
                           ->insert($taxGenerateDetail);
                    }

                    $detailFaktur = [
                        $fakturCounter, 
                        'A', 
                        $detail->kdtax ?? '000000', 
                        $detail->nama_brg, 
                        'UM.0018', 
                        $detail->dpp_unit,
                        $detail->qty, 
                        number_format($total_disc, 2, '.', ''), 
                        $dpp, 
                        $dpp_lain, 
                        12, 
                        $nominal_ppn, 
                        '0',
                        '0.00' 
                    ];
            
                    $detailSheet->fromArray($detailFaktur, null, 'A' . $rowDetail++);
                }
                
                $fakturCounter++; 
            }

            $db->transComplete();
            
            if ($db->transStatus() === false) {
                throw new \Exception('Error saving tax_generate_brg data');
            }

        } catch (\Exception $e) {
            log_message('error', 'Error saving tax_generate_brg: ' . $e->getMessage());
            throw $e; 
        }

        
        $detailSheet->fromArray(['END', '', '', '', '', '', '', '', '', '', '', '', '', ''], null, 'A' . $rowDetail);

        $listTrx = array_unique($listTrx);



        try {
            $db->transStart();
                            
            foreach ($rawData as $row) {
                $returData = $db->query($queryRetur, [$row->kdtr])->getResult();
                $listRetur = [];
                foreach($returData as $retur){
                    $listRetur[$retur->nmbrg] = $retur;
                }
                
                $sql = "SELECT 
                    tr.nmbrg,
                    tr.qty,
                    (tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))/1.11 * tr.qty * 0.11 as tax_per_item
                FROM tr
                WHERE tr.kdtr = ?";
                
                $taxDetails = $db->query($sql, [$row->kdtr])->getResult();
                
                $totalTax = 0;
                foreach($taxDetails as $detail) {
                    $cekRetur = $listRetur[$detail->nmbrg] ?? null;
                    $valRetur = $cekRetur ? $cekRetur->qty : 0;
                    $qty = $detail->qty - $valRetur;
                    if($qty > 0) {
                        $totalTax += ($qty / $detail->qty) * $detail->tax_per_item;
                    }
                }

                $taxGenerateData = [
                    'kode_trx' => $row->kdtr,
                    'tanggal' => $row->tgl,
                    'jam' => date('H:i:s'),
                    'total_tax' => $totalTax,
                    'created_at' => $now,
                    'user_id' => $userId
                ];
                
                $existing = $db->table('crm.tax_generate')
                              ->where('kode_trx', $row->kdtr)
                              ->get()
                              ->getRow();
                
                if ($existing) {
                    $taxGenerateData['updated_at'] = $now;
                    $db->table('crm.tax_generate')
                       ->where('kode_trx', $row->kdtr)
                       ->update($taxGenerateData);
                } else {
                    $db->table('crm.tax_generate')
                       ->insert($taxGenerateData);
                }
            }
            
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                throw new \Exception('Error saving tax_generate data');
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error saving tax_generate: ' . $e->getMessage());
            throw $e; 
        }
            
        $dataFaktur = [];
        $counter = 1;
        foreach ($rawData as $row) {
            if (!in_array($row->kdtr, $listTrx)){
                continue;
            }
            $npwp = $row->status_wp == "VALID" ? str_pad($row->newnpwp, 16, '0', STR_PAD_RIGHT) : "000000000000000";
            $npwpInvalid = $row->status_wp == "INVALID" ? str_pad($row->newnpwp, 16, '0', STR_PAD_RIGHT) : "-";

            $aa = ($row->status_wp == "VALID" ? "TIN" : "National ID");
            
            $dataFaktur[] = [
                $counter++,
                format_date($row->tgl, 'd/m/Y'),
                'Normal',
                "04",
                '',
                '',
                '',                            
                $row->kdtr,                                                            
                '',
                $tku,
                '="' . $npwp . '"', 
                $aa,
                'IDN',
                '="' . $npwpInvalid . '"', 
                $row->name,
                $row->address,
                '',
                ($row->status_wp == "VALID" ? '="'.str_pad($row->newnpwp . "000000", 21, '0', STR_PAD_RIGHT).'"' : "-")
            ];
        }
        $dataFaktur[] = ['END', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];

        $row = 4; 
        foreach ($dataFaktur as $data) {
            $fakturSheet->fromArray($data, null, 'A' . $row++);
        }

        $fakturSheet->getColumnDimension('K')->setWidth(20);
        $fakturSheet->getColumnDimension('N')->setWidth(20);
        $fakturSheet->getColumnDimension('R')->setWidth(25);
        
        $fakturSheet->getStyle('J4:J' . ($row-1))->getNumberFormat()->setFormatCode('@');
        $fakturSheet->getStyle('K4:K' . ($row-1))->getNumberFormat()->setFormatCode('@');
        $fakturSheet->getStyle('N4:N' . ($row-1))->getNumberFormat()->setFormatCode('@');
        $fakturSheet->getStyle('R4:R' . ($row-1))->getNumberFormat()->setFormatCode('@');

        $spreadsheet->setActiveSheetIndexByName('Faktur');

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->setOffice2003Compatibility(false);

        if (ob_get_length()) ob_end_clean();
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="tax_'.date('Ymd').'.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        logGL($db_config,'faktur_generate','Insert');
        exit();
    }

    private function generateOnline($dbs, $rawData, $tku, $startDate)
    {
        $userId = session()->get('user_id') ?? 9999;
        $now = date('Y-m-d H:i:s');

        $templatePath = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'online.xlsx';
        if (!file_exists($templatePath)) {
            throw new \Exception('File online.xlsx not found at: ' . $templatePath);
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
        $fakturSheet = $spreadsheet->getSheetByName('DATA');
        if (!$fakturSheet) {
            throw new \Exception('Worksheet "DATA" not found in template');
        }

        try {
            $bln = format_date($startDate, 'm');
            $bln = str_pad($bln, 2, '0', STR_PAD_LEFT);
            $thn = format_date($startDate, 'Y');
            $fakturSheet->setCellValue('B2', "'".$bln);
            $fakturSheet->setCellValue('B3', $thn);

            switch ($dbs) {
                case 'ariston':
                    $db = $this->db_crm_ars;
                    $db_config = 'crm_ars';
                    break;
                case 'wep':
                    $db = $this->db_crm_wep;
                    $db_config = 'crm_wep';
                    break;
                case 'dtf':
                    $db = $this->db_crm_dtf;
                    $db_config = 'crm_dtf';
                    break;
                case 'ariston_bali':
                    $db = $this->db_crm_ars_bali;
                    $db_config = 'crm_ars_bali';
                    break;
                case 'wep_bali':
                    $db = $this->db_crm_wep_bali;
                    $db_config = 'crm_wep_bali';
                    break;
                default:
                    $db = $this->db_default;
                    $db_config = 'default';
            }

            $rowDetail = 5;

            $whiteStyle = [];
            $grayStyle = [];

            foreach (range('B', 'M') as $col) {
                $whiteStyle[$col] = $fakturSheet->getStyle($col . '5');
                $grayStyle[$col] = $fakturSheet->getStyle($col . '6');
            }

            $db->transStart();
            foreach ($rawData as $index => $trx) {
                $nmcust = $trx->nmcust ?? '';
                $valRetur = $trx->retur ?? 0;
                $gtot = $trx->gtot ?? 0;
                $tot = $gtot - $valRetur;
                if ($tot <= 0) continue;

                $dpp = $tot/1.11;
                $dpp_lain = $dpp*11/12; 
                $nominal_ppn = $dpp_lain * 0.12;

                $taxGenerateDetail = [
                    'trx_code' => 'Normal',
                    'buyer_name' => $nmcust,
                    'buyer_id_opt' => 'NIK',
                    'buyer_id_number' => "0000000000000000",
                    'good_service_opt' => 'A',
                    'serial_no' => $trx->kdtr,
                    'transaction_date' => $trx->tgl,
                    'tax_base_selling_price' => $dpp,
                    'other_tax_base_selling_price' => $dpp_lain,
                    'vat' => $nominal_ppn,
                    'sltg' => 0,
                    'info' => null,
                ];

                $queryCek = $db->table('crm.tax_generate_online')
                              ->where('serial_no', $trx->kdtr)
                              ->get();
                $existing = $queryCek ? $queryCek->getRow() : null;

                if ($existing) {
                    $taxGenerateDetail['updated_at'] = $now;
                    $db->table('crm.tax_generate_online')
                       ->where('serial_no', $trx->kdtr)
                       ->update($taxGenerateDetail);
                } else {
                    $taxGenerateDetail['created_at'] = $now;
                    $taxGenerateDetail['user_id'] = $userId;
                    $db->table('crm.tax_generate_online')
                       ->insert($taxGenerateDetail);
                }

                $detailFaktur = [
                    'Normal', 
                    $nmcust, 
                    'NIK', 
                    "0000000000000000", 
                    'A', 
                    $trx->kdtr,
                    format_date($trx->tgl, 'd/m/Y'), 
                    number_format($dpp, 11, '.', ''), 
                    number_format($dpp_lain, 11, '.', ''), 
                    number_format($nominal_ppn, 11, '.', ''), 
                    '0',
                    '' 
                ];

                $fakturSheet->fromArray($detailFaktur, null, 'B' . $rowDetail);

                $currentStyle = ($index % 2 == 0) ? $whiteStyle : $grayStyle;
                foreach ($currentStyle as $col => $style) {
                    $fakturSheet->duplicateStyle($style, "$col" . $rowDetail);
                }

                $rowDetail++;
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error saving tax_generate_online data');
            }

        } catch (\Exception $e) {
            log_message('error', 'Error saving tax_generate_online: ' . $e->getMessage());
            throw $e;
        }




        $fakturSheet->getStyle('H5:H'.($rowDetail - 1))
            ->getNumberFormat()
            ->setFormatCode('DD/MM/YYYY');

        $fakturSheet->getStyle('I5:K'.($rowDetail - 1))
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        $highestColumn = $fakturSheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        for ($col = 2; $col <= $highestColumnIndex; $col++) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $fakturSheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndexByName('DATA');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->setOffice2003Compatibility(false);

        if (ob_get_length()) ob_end_clean();
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="tax_online_' . date('Ymd') . '.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        logGL($db_config, 'faktur_generate_online', 'Insert');
        exit();
    }

    public function checkExisting()
    {
        try {
            $request = $this->request->getJSON(true);
            
            if (empty($request['data'])) {
                throw new \Exception('No data provided');
            }

            $noFakturList = $request['data'];
            $dbs = $request['sumber_data'] ?? 'default';

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

            $exists = $db->table('crm.data_coretax')
                        ->whereIn('no_faktur', $noFakturList)
                        ->get()
                        ->getResult();

            return $this->response->setJSON([
                'success' => true,
                'exists' => !empty($exists),
                'count' => count($exists)
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function previewImport()
    {
        try {
            $file = $this->request->getFile('file_import');
            
            if (!$file->isValid()) {
                throw new \Exception('File tidak valid');
            }

            if ($file->getSize() > 5242880) { 
                throw new \Exception('Ukuran file melebihi batas maksimal (5MB)');
            }

            $ext = $file->getExtension();
            if (!in_array($ext, ['xls', 'xlsx'])) {
                throw new \Exception('Format file tidak didukung');
            }

            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader(ucfirst($ext));
            $spreadsheet = $reader->load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();

            $expectedHeaders = [
                'NPWP Pembeli / Identitas lainnya',
                'Nama Pembeli',
                'Kode Transaksi',
                'Nomor Faktur Pajak',
                'Tanggal Faktur Pajak', 
                'Masa Pajak',
                'Tahun',
                'Status Faktur',
                'ESignStatus',
                'Harga Jual/Penggantian/DPP',
                'DPP Nilai Lain/DPP',
                'PPN',
                'PPnBM',
                'Penandatangan',
                'Referensi',
                'Dilaporkan oleh Penjual',
                'Dilaporkan oleh Pemungut PPN'
            ];

            $headers = $data[0];

            $missingColumns = array_diff($expectedHeaders, $headers);
            if (!empty($missingColumns)) {
                throw new \Exception('Format file tidak sesuai template.');
            }

            array_shift($data);

            $formattedData = [];
            foreach ($data as $row) {
                if (empty($row[0])) continue; 
                
                $formattedData[] = [
                    'npwp' => $row[0],
                    'nama_pembeli' => $row[1], 
                    'kode_transaksi' => $row[2],
                    'no_faktur' => $row[3],
                    'tanggal_faktur' => $row[4],
                    'masa_pajak' => $row[5],
                    'tahun' => $row[6],
                    'status_faktur' => $row[7],
                    'harga_jual' => (float)$row[9],
                    'dpp' => (float)$row[10],
                    'ppn' => (float)$row[11],
                    'ppnbm' => (float)$row[12],
                    'referensi' => $row[14],
                    'dilaporkan_penjual' => $row[15]
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $formattedData
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function saveImport()
    {
        try {
            $json = $this->request->getJSON(true);
            
            if (empty($json['data'])) {
                throw new \Exception('Tidak ada data yang akan disimpan');
            }

            $dbs = $json['sumber_data'] ?? 'default';

            switch ($dbs) {
                case 'ariston':
                    $db = $this->db_crm_ars;
                    $db_config = 'crm_ars';
                    break;
                case 'wep':
                    $db = $this->db_crm_wep;
                    $db_config = 'crm_wep';
                    break;
                case 'dtf':
                    $db = $this->db_crm_dtf;
                    $db_config = 'crm_dtf';
                    break;
                case 'ariston_bali':
                    $db = $this->db_crm_ars_bali;
                    $db_config = 'crm_ars_bali';
                    break;
                case 'wep_bali':
                    $db = $this->db_crm_wep_bali;
                    $db_config = 'crm_wep_bali';
                    break;
                default:
                    $db = $this->db_default;
                    $db_config = 'default';
            }

            $insertData = [];
            $updateData = [];
            $now = date('Y-m-d H:i:s');
            $referensiInserted = [];

            $db->transStart();

            try {
                foreach ($json['data'] as $row) {
                    $kodeTransaksi = preg_replace('/^(\d+).*$/', '$1', $row['kode_transaksi']);
                    
                    $tanggal = null;
                    if (!empty($row['tanggal_faktur'])) {
                        $dateObj = new \DateTime($row['tanggal_faktur']);
                        $tanggal = $dateObj->format('Y-m-d');
                    }

                    $data = [
                        'npwp' => $row['npwp'],
                        'nama_pembeli' => $row['nama_pembeli'],
                        'kode_transaksi' => $kodeTransaksi,
                        'no_faktur' => $row['no_faktur'],
                        'tanggal_faktur' => $tanggal,
                        'masa_pajak' => $row['masa_pajak'],
                        'tahun' => $row['tahun'],
                        'status_faktur' => $row['status_faktur'],
                        'harga_jual' => $row['harga_jual'],
                        'dpp' => $row['dpp'],
                        'ppn' => $row['ppn'],
                        'ppnbm' => $row['ppnbm'],
                        'referensi' => $row['referensi'],
                        'dilaporkan_penjual' => $row['dilaporkan_penjual']
                    ];

                    // Search only by referensi for updates
                    $existing = $db->table('crm.data_coretax')
                                ->where('referensi', $data['referensi'])
                                ->get()
                                ->getRow();

                    if ($existing) {
                        // Update based on referensi
                        $data['updated_at'] = $now;
                        $result = $db->table('crm.data_coretax')
                                ->where('referensi', $data['referensi'])
                                ->update($data);
                        if ($result) {
                            $updateData[] = $data['referensi'];
                        }
                    } else {
                        // Cek apakah referensi sudah pernah dimasukkan ke $insertData
                        if (!in_array($data['referensi'], $referensiInserted)) {
                            $data['created_at'] = $now;
                            $data['updated_at'] = null;
                            $insertData[] = $data;
                            $referensiInserted[] = $data['referensi'];
                        }
                    }
                }

                if (!empty($insertData)) {
                    $result = $db->table('crm.data_coretax')->insertBatch($insertData);
                    if (!$result) {
                        throw new \Exception('Gagal menyimpan data baru');
                    }
                }

                $db->transComplete();

                $message = [];
                if (!empty($insertData)) {
                    $message[] = count($insertData) . ' data baru berhasil disimpan';
                }
                if (!empty($updateData)) {
                    $message[] = count($updateData) . ' data berhasil diupdate';
                }

                logGL($db_config, 'faktur_import', 'Insert');

                return $this->response->setJSON([
                    'success' => true,
                    'message' => implode(', ', $message)
                ]);

            } catch (\Exception $e) {
                $db->transRollback();
                throw $e;
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getDetail()
    {
        try {
            $request = service('request');
            $kdtr = $request->getPost('kdtr');
            $dbs = $request->getPost('sumber_data');

            $baseQuery = "SELECT 
                tr.nmbrg,
                tr.qty,
                tr.hrg,
                tr.disc as diskon,
                (tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))/1.11 as dpp_unit,
                ((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))/1.11)*11/12 as dpp_nl,
                brg.nama as nama_brg,
                mc.kdtax as kode_brg,
                'UM.0018' as satuan,
                (tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))/1.11 * tr.qty as dpp,
                ((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))/1.11)*11/12 * tr.qty as dpp_lain,
                ((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))/1.11)*11/12 * tr.qty * 0.12 as nominal_ppn
            FROM tr
            JOIN brg ON tr.nmbrg = brg.nmbrg 
            LEFT JOIN crm.mapping_coretax mc ON tr.nmbrg = mc.kdbrg
            WHERE tr.kdtr = ?
            ORDER BY tr.kdtr ASC, tr.nmbrg ASC";

            $aristonWepQuery = "SELECT 
                tr.nmbrg,
                tr.qty,
                tr.hrg,
                tr.disc as diskon,
                mstr.disc/(SELECT SUM(tr2.qty) FROM tr tr2 WHERE tr2.kdtr = tr.kdtr)*tr.qty as disc_per_unit,
                (tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))/1.11 as dpp_unit,
                ((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))/1.11)*11/12 as dpp_nl,
                brg.nama as nama_brg,
                mc.kdtax as kode_brg,
                'UM.0018' as satuan,                
                ((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))*tr.qty - (mstr.disc/(SELECT SUM(tr2.qty) FROM tr tr2 WHERE tr2.kdtr = tr.kdtr)*tr.qty))/1.11 as dpp,
                (((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))*tr.qty - (mstr.disc/(SELECT SUM(tr2.qty) FROM tr tr2 WHERE tr2.kdtr = tr.kdtr)*tr.qty))/1.11)*11/12 as dpp_lain,
                (((tr.hrg - (tr.hrg * CAST(tr.disc AS numeric)/100))*tr.qty - (mstr.disc/(SELECT SUM(tr2.qty) FROM tr tr2 WHERE tr2.kdtr = tr.kdtr)*tr.qty))/1.11)*11/12*0.12 as nominal_ppn
            FROM tr
            JOIN brg ON tr.nmbrg = brg.nmbrg 
            LEFT JOIN crm.mapping_coretax mc ON tr.nmbrg = mc.kdbrg
            LEFT JOIN mstr ON tr.kdtr = mstr.kdtr
            WHERE tr.kdtr = ?
            ORDER BY tr.kdtr ASC, tr.nmbrg ASC";

            $queryRetur = "SELECT 
                tr.kdtr,
                mstr.tgl,
                tr.nmbrg,
                tr.qty
            FROM tr
            LEFT JOIN mstr ON tr.kdtr = mstr.kdtr
            WHERE mstr.kdtr2 = ?
            ORDER BY tr.kdtr ASC, tr.nmbrg ASC";

            switch ($dbs) {
                case 'ariston':
                    $db = $this->db_crm_ars;
                    $sql = $aristonWepQuery;
                    break;
                case 'wep':
                    $db = $this->db_crm_wep;
                    $sql = $aristonWepQuery;
                    break;
                case 'dtf':
                    $db = $this->db_crm_dtf;
                    $sql = $baseQuery;
                    break;
                case 'ariston_bali':
                    $db = $this->db_crm_ars_bali;
                    $sql = $aristonWepQuery;
                    break;
                case 'wep_bali':
                    $db = $this->db_crm_wep_bali;
                    $sql = $aristonWepQuery;
                    break;
                default:
                    $db = $this->db_default;
                    $sql = $baseQuery;
            }

            $getRetur = $db->query($queryRetur, [$kdtr])->getResult();  
            $listRetur = [];
            foreach($getRetur as $row){
                $listRetur[$row->nmbrg] = $row;
            }

            $details = $db->query($sql, [$kdtr])->getResult();  
            foreach($details as $key => $dt){
                $cekRetur = $listRetur[$dt->nmbrg] ?? [];
                $valRetur = $cekRetur->qty ?? 0;
                $valStlhRetur = $dt->qty - $valRetur;
                $details[$key]->retur = $valRetur;
                $details[$key]->tot_qty = $dt->qty;
                $details[$key]->qty = $valStlhRetur;
                $details[$key]->dpp = $valStlhRetur * $dt->dpp_unit;
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $details
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function batalGenerate()
    {
        try {
            $request = service('request');
            
            $startDate = $request->getPost('startDate');
            $endDate = $request->getPost('endDate');
            $dbs = $request->getPost('sumber_data');

            if (!$startDate || !$endDate || !$dbs) {
                throw new \Exception('Parameter tidak lengkap');
            }

            switch ($dbs) {
                case 'ariston':
                    $db = $this->db_crm_ars;
                    $db_config = 'crm_ars';
                    break;
                case 'wep':
                    $db = $this->db_crm_wep;
                    $db_config = 'crm_wep';
                    break;
                case 'dtf':
                    $db = $this->db_crm_dtf;
                    $db_config = 'crm_dtf';
                    break;
                case 'ariston_bali':
                    $db = $this->db_crm_ars_bali;
                    $db_config = 'crm_ars_bali';
                    break;
                case 'wep_bali':
                    $db = $this->db_crm_wep_bali;
                    $db_config = 'crm_wep_bali';
                    break;
                default:
                    $db = $this->db_default;
                    $db_config = 'default';
            }

            $db->transStart();

            $kodeTransaksi = $db->table('crm.tax_generate')
                ->select('kode_trx')
                ->where('tanggal >=', $startDate)
                ->where('tanggal <=', $endDate)
                ->get()
                ->getResultArray();

            if (empty($kodeTransaksi)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Tidak ada data yang perlu dibatalkan untuk periode tersebut'
                ]);
            }

            $kodeList = array_column($kodeTransaksi, 'kode_trx');

            $db->table('crm.tax_generate_brg')
               ->whereIn('kode_trx', $kodeList)
               ->delete();

            $db->table('crm.tax_generate')
               ->where('tanggal >=', $startDate)
               ->where('tanggal <=', $endDate)
               ->delete();

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Gagal membatalkan generate data');
            }

            logGL($db_config,'faktur_batal_generate','delete');

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Berhasil membatalkan generate data untuk periode ' . 
                            format_date($startDate, 'd/m/Y') . ' sampai ' . 
                            format_date($endDate, 'd/m/Y')
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}