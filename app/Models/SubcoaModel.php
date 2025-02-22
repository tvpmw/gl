<?php

namespace App\Models;

use CodeIgniter\Model;

class SubcoaModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'subcoa';
    protected $tableIns         = 'subcoa';
    protected $primaryKey       = 'KDSUB';
    protected $useAutoIncrement = false;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['KDSUB', 'NMSUB', 'alias', 'TIPE'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
    protected $deletedFieldUser  = 'deleted_by';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

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

    public function countAll(bool $reset = true, bool $test = false){
        $q = $this->builder( $this->table );
        if ($this->useSoftDeletes === true) {
            $q->where($this->table . '.' . $this->deletedField, null);
        }
        return $q->testMode($test)->countAllResults($reset);
    }

    public function getSubLabaRugi()
    {
        $builder = $this->db->table($this->table);
        $builder->select('KDSUB as kdsub, NMSUB as nmsub, TIPE as tipe');
        $builder->where('TIPE >=', '4');
        $builder->where('KDSUB !=', '650');

        // Ambil data dari database
        $query = $builder->get();
        $result = $query->getResultArray();

        // Tambahkan data tambahan
        // $result[] = ['kdsub' => '599', 'nmsub' => 'PERSEDIAAN AWAL', 'tipe' => 6];
        // $result[] = ['kdsub' => '650', 'nmsub' => 'PERSEDIAAN AKHIR', 'tipe' => 6];

        // Urutkan berdasarkan kdsub
        usort($result, function ($a, $b) {
            return $a['kdsub'] <=> $b['kdsub'];
        });

        return $result;
    }

    public function getSubNeraca()
    {
        $builder = $this->db->table($this->table);
        $builder->select('KDSUB as kdsub, NMSUB as nmsub, TIPE as tipe');
        $builder->where('TIPE <', '4');

        // Ambil data dari database
        $query = $builder->get();
        $result = $query->getResultArray();

        // Tambahkan data tambahan
        // $result[] = ['kdsub' => '599', 'nmsub' => 'PERSEDIAAN AWAL', 'tipe' => 6];
        // $result[] = ['kdsub' => '650', 'nmsub' => 'PERSEDIAAN AKHIR', 'tipe' => 6];

        // Urutkan berdasarkan kdsub
        usort($result, function ($a, $b) {
            return $a['kdsub'] <=> $b['kdsub'];
        });

        return $result;
    }
}
