<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class FakturController extends Controller
{

    public function __construct()
    {
        helper(['my_helper']);
    }

    public function index()
    {
        return view('faktur/form');
    }

    public function generate()
    {
        $bulan = $this->request->getPost('bulan');
        $tahun = $this->request->getPost('tahun');
        $sales_type = $this->request->getPost('sales_type');

        // Validasi input
        if (!$bulan || !$tahun || !$sales_type) {
            return redirect()->back()->with('error', 'Semua field harus diisi');
        }

        $data = [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'sales_type' => $sales_type
        ];

        return view('faktur/form', $data);
    }
}