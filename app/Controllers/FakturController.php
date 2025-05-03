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
    protected $db_default;

    public function __construct()
    {
        helper(['my_helper']);

        $this->mstrModel = new MstrModel('default');
        $this->mstrModel2 = new MstrModel('crm_ars');        
        $this->mstrModel3 = new MstrModel('crm_wep');
        $this->mstrModel4 = new MstrModel('crm_dtf');

        $this->db_default = \Config\Database::connect('default');
    }

    public function index()
    {
        $data['dbs'] = getSelDb();
        return view('faktur/form',$data);
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
        $startDate = $request->getPost('startDate');
        $endDate = $request->getPost('endDate');
        $sales_type = $request->getPost('sales_type');
        $dbs = $request->getPost('sumber_data');

        if(empty($startDate)){
            $startDate = date('Y-m-d');
        }

        if(empty($endDate)){
            $endDate = $startDate;
        }

        if($startDate > $endDate){
            $endDate = $startDate;
        }

        // Validasi database yang dipilih
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
            default:
                $mdl = $this->mstrModel;
                $prefix = 'K';
        }

        $getNpwp = $mdl->getDataNpwp($startDate, $endDate, $sales_type,$prefix);
        if(!empty($getNpwp)){
            $listNpwp = [];
            foreach ($getNpwp as $value) {
                $npwp = cleanString($value->npwp);
                $listNpwp[$npwp] = $value->npwp;
            }

            $this->prosesNpwp($listNpwp);
        }

        // Query Data dengan filter
        $totalRecords = $mdl->countAll();
        $totalRecordsFiltered = $mdl->countFilter($search, $startDate, $endDate, $sales_type, $prefix);
        $data = $mdl->getData($start, $length, $search, $orderColumnIndex, $orderDir, $startDate, $endDate, $sales_type, $prefix);

        $formattedData = [];
        $no = $start+1;
        $tampil = true;
        foreach ($data as $row) {
            $akt = '<span class="badge text-bg-danger"> INVALID</span>';
            if($row->status_wp == 'VALID'){
                $akt = '<span class="badge text-bg-success"> VALID</span>';
            }

            $aksiTable = '';
            $lists = [];
            $lists[]  = $no++;
            $lists[]  = $row->kdtr;
            $lists[]  = format_date($row->tgl,'m/d/Y');
            $lists[]  = format_price($row->gtot);
            $lists[]  = $row->nmcust;
            $lists[]  = $row->newnpwp;
            $lists[]  = $row->name;
            $lists[]  = $row->jenis;
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

    private function prosesNpwp($listNpwp)
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
            $set['npwpcust'] = $npwpOld;
            $set['resp'] = (isset($hitData[$npwp]))?json_encode($hitData[$npwp]):null;

            $cekData = $this->db_default->table('crm.cust_npwp')->where('npwpcust',$npwpOld)->get()->getRow();
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
            $this->db_default->table('crm.cust_npwp')->insertBatch($insertData);
        }

        if(!empty($updateData)){
            $this->db_default->table('crm.cust_npwp')->updateBatch($updateData, 'npwpcust');
        }

        return true;
    }

    public function generate()
    {
        $bulan = $this->request->getPost('bulan');
        $tahun = $this->request->getPost('tahun');
        $sales_type = $this->request->getPost('sales_type');
        $sumber_data = $this->request->getPost('sumber_data');

        // Validasi input
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
            // Load template first
            $templatePath = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'template.xlsx';
            if (!file_exists($templatePath)) {
                throw new \Exception('File template.xlsx not found at: ' . $templatePath);
            }

            // Load existing template as our base
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);

            // Get Faktur sheet and populate data
            $fakturSheet = $spreadsheet->getSheetByName('Faktur');
            if (!$fakturSheet) {
                throw new \Exception('Worksheet "Faktur" not found in template');
            }

            // Data for Faktur starting at A4
            $row = 4;
            $today = date('Y-m-d');
            $dataFaktur = [
                [
                    1,
                    $today,
                    'Normal',
                    '04',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '0316396407526000000000',
                    '123456789012345',
                    'TIN',
                    'IDN',
                    'DOC123',
                    'Nama Pembeli',
                    'Alamat Pembeli',
                    'email@pembeli.com',
                    'IDTKUPMBL123'
                ],
                ['END', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']
            ];

            // Fill Faktur data
            foreach ($dataFaktur as $data) {
                $fakturSheet->fromArray($data, null, 'A' . $row++);
            }

            // Get DetailFaktur sheet and populate data
            $detailSheet = $spreadsheet->getSheetByName('DetailFaktur');
            if (!$detailSheet) {
                throw new \Exception('Worksheet "DetailFaktur" not found in template');
            }

            // Data for DetailFaktur starting at A2
            $detailData = [
                [1, 'A', '848180', 'SELANG AC FLEXIBLE HOSE 1/2"', 'UM.0018', 18436.7, 72, 0, 383727.6, 383727.6, 12, 46047.31, 0, 0],
                [2, 'A', '000000', 'SEAL TAPE/ONDA 1/2" 20M (5101)"', 'UM.0018', 3173.05, 24, 0, 76153.2, 76153.2, 12, 9138.38, 0, 0],
                ['END', '', '', '', '', '', '', '', '', '', '', '', '', '']
            ];

            // Fill DetailFaktur data
            $row = 2;
            foreach ($detailData as $data) {
                $detailSheet->fromArray($data, null, 'A' . $row++);
            }

            // Set active sheet to Faktur
            $spreadsheet->setActiveSheetIndexByName('Faktur');

            // Create writer
            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $writer->setOffice2003Compatibility(false);

            // Output the file
            if (ob_get_length()) ob_end_clean();
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="tax_'.date('Ymd').'.xlsx"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');

        } catch (\Exception $e) {
            log_message('error', 'Error generating Excel file: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            die('Error generating Excel file: ' . $e->getMessage());
        }

        exit();
    }
}