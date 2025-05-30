<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FakturExcelGenerator extends Controller
{
    public function generate_excel()
    {
        try {
            $templatePath = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'template.xlsx';
            if (!file_exists($templatePath)) {
                throw new \Exception('File template.xlsx not found at: ' . $templatePath);
            }

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);

            $fakturSheet = $spreadsheet->getSheetByName('Faktur');
            if (!$fakturSheet) {
                throw new \Exception('Worksheet "Faktur" not found in template');
            }

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

            foreach ($dataFaktur as $data) {
                $fakturSheet->fromArray($data, null, 'A' . $row++);
            }

            $detailSheet = $spreadsheet->getSheetByName('DetailFaktur');
            if (!$detailSheet) {
                throw new \Exception('Worksheet "DetailFaktur" not found in template');
            }

            $detailData = [
                [1, 'A', '848180', 'SELANG AC FLEXIBLE HOSE 1/2"', 'UM.0018', 18436.7, 72, 0, 383727.6, 383727.6, 12, 46047.31, 0, 0],
                [2, 'A', '000000', 'SEAL TAPE/ONDA 1/2" 20M (5101)"', 'UM.0018', 3173.05, 24, 0, 76153.2, 76153.2, 12, 9138.38, 0, 0],
                ['END', '', '', '', '', '', '', '', '', '', '', '', '', '']
            ];

            $row = 2;
            foreach ($detailData as $data) {
                $detailSheet->fromArray($data, null, 'A' . $row++);
            }

            $spreadsheet->setActiveSheetIndexByName('Faktur');

            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $writer->setOffice2003Compatibility(false);

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