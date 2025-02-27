<?php

namespace App\Models;

use CodeIgniter\Model;

class JvModel extends Model
{
    protected $table = 'jv';
    protected $primaryKey = 'KDJV';
    protected $allowedFields = ['KDJV', 'KETJV', 'TGLJV', 'TH', 'BL', 'STAT', 'JVTOT', 'nmgrp', 'user_id', 'TGLSYS'];

    protected $column_order = [null,'jv.KDJV', 'jv.KETJV', 'jv.TGLJV', 'jv.TH', 'jv.BL', 'jv.JVTOT'];
    protected $column_search = ['jv.KDJV', 'jv.KETJV', 'jv.TGLJV', 'jv.TH', 'jv.BL', 'jv.JVTOT'];
    protected $order = ['jv.KDJV' => 'DESC'];

    public function __construct($connectionName = null)
    {
        parent::__construct();
        if(empty($connectionName)){
            $this->validateDatabaseConfig();
        }else{
            $this->db = \Config\Database::connect($connectionName);
        }

        // $this->db = db_connect();
        $this->dt = $this->db->table($this->table);
    }

    private function validateDatabaseConfig()
    {
        // Ambil konfigurasi database dari session
        $dbConfig = session()->get('db_config');

        if ($dbConfig) {
            // Validasi koneksi database dengan konfigurasi session
            $this->db = Config::connect($dbConfig);
            try {
                // Cek apakah koneksi berhasil
                $this->db->initialize();

                // Jika tidak ada error, berarti konfigurasi valid
                log_message('info', 'Database connection validated from session.');
            } catch (\Exception $e) {
                // Jika koneksi gagal, log dan beri peringatan
                log_message('error', 'Database connection failed: ' . $e->getMessage());
                throw new \RuntimeException('Database connection validation failed.');
            }
        } else {
            // Jika tidak ada konfigurasi database di session
            // log_message('error', 'No database configuration found in session.');
            // throw new \RuntimeException('No database configuration found in session.');
            
            $this->db = db_connect();
        }
    }    

    public function getData($start, $length, $search, $orderColumn, $orderDir, $bulan = null, $tahun = null)
    {
        $builder = $this->db->table($this->table)->select('jv.*,periode.POSTING');
        $builder->join("periode","periode.TH = jv.TH AND periode.BL = jv.BL", 'LEFT');

        // Ambil daftar kolom dari tabel
        // $columns = $this->db->getFieldNames($this->table);
        // array_unshift($columns, null);

        // Pencarian dinamis
        if (!empty($search)) {
            $builder->groupStart();
            foreach ($this->column_search as $column) {
                if(!empty($column)){
                    $column = str_replace('jv.', '', $column);
                    $builder->orWhere("CAST(jv.\"$column\" AS TEXT) ILIKE '%$search%'");
                }
            }
            $builder->groupEnd();
        }

        // Filter berdasarkan bulan dan tahun
        if (!empty($bulan)) {
            $builder->where('jv.BL', $bulan);
        }

        if (!empty($tahun)) {
            $builder->where('jv.TH', $tahun);
        }

        // Sorting
        if (!empty($orderColumn) && isset($this->column_order[$orderColumn])) {
            $builder->orderBy($this->column_order[$orderColumn], $orderDir);
        } else if (isset($this->order)) {
            $order = $this->order;
            $builder->orderBy(key($order), $order[key($order)]);
        }

        // Pagination
        return $builder->limit($length, $start)->get()->getResult();
    }

    public function countFiltered($search, $bulan = null, $tahun = null)
    {
        $builder = $this->db->table($this->table);

        if (!empty($search)) {
            $builder->like('KDJV', $search)->orLike('KETJV', $search);
        }

        if (!empty($bulan)) {
            $builder->where('BL', $bulan);
        }

        if (!empty($tahun)) {
            $builder->where('TH', $tahun);
        }

        return $builder->countAllResults();
    }

    public function getJurnalWithDetails($kdjv)
    {
        $builder = $this->db->table('jv AS jv')
            ->select('jv.KDJV AS kdjv, jv.KETJV AS ketjv, jv.TGLJV AS tgljv, jv.TH AS th, jv.BL AS bl, jv.JVTOT AS jvtot, periode.POSTING AS posting')
            ->join('periode', 'periode.TH = jv.TH AND periode.BL = jv.BL', 'LEFT')
            ->where('jv.KDJV', $kdjv)
            ->get();

        $jurnal = $builder->getRowArray();

        if (!$jurnal) {
            return null;
        }

        // Query untuk rincian jurnal (jvdet)
        $rincian = $this->db->table('jvdet')
            ->select('jvdet.KDJV as kdjv, jvdet.NOU as nou, jvdet.KDCOA as kdcoa, coa.NMCOA as nmcoa, jvdet.JVDEBET as jvdebet, jvdet.JVKREDIT as jvkredit, jvdet.KET as ket')
            ->join('coa', 'coa.KDCOA = jvdet.KDCOA', 'LEFT')
            ->where('KDJV', $kdjv)
            ->orderBy('NOU','ASC')
            ->get()
            ->getResultArray();

        $jurnal['rincian'] = $rincian;

        return $jurnal;
    }
}
