<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Config;
use CodeIgniter\Database\ConnectionInterface;

class BrgModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'brg';
    protected $tableIns         = 'brg';
    protected $primaryKey       = 'nmbrg';
    protected $useAutoIncrement = false;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nmbrg', 'nama', 'jenis', 'hrgb', 'hrgj1', 'hrgj2', 'tipe', 'disc', 'stmin', 'stmax', 'sat', 'hpbeli', 'kat', 'status', 'user_id', 'tgsawal', 'price', 'kdbrand', 'nmbrand', 'size'];

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

    protected $column_order = [null,'TH', 'BL'];
    protected $column_search = ['TH', 'BL'];
    protected $order = ['id' => 'DESC'];

    public function __construct($connectionName = null)
    {
        parent::__construct();
        if(empty($connectionName)){
            $this->validateDatabaseConfig();
        }else{
            $this->db = \Config\Database::connect($connectionName);
        }

        // $this->db = db_connect();
        // $this->db = $this->db->table($this->table);
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

    private function getDatatablesQuery($searchValue='',$orderColumn=0,$orderDir='asc')
    {
        if ($this->useSoftDeletes === true) {
            $this->db->where($this->table . '.' . $this->deletedField, null);
        }
        $i = 0;
        foreach ($this->column_search as $item) {
            if ($searchValue) {
                if ($i === 0) {
                    $this->db->groupStart();
                    $this->db->like($item, $searchValue);
                } else {
                    $this->db->orLike($item, $searchValue);
                }
                if (count($this->column_search) - 1 == $i)
                    $this->db->groupEnd();
            }
            $i++;
        }

        if ($orderColumn !=0) {
            $this->db->orderBy($this->column_order[$orderColumn], $orderDir);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->orderBy(key($order), $order[key($order)]);
        }
    }

    public function getDatatables($length=10,$start=0,$searchValue='',$orderColumn=0,$orderDir='asc')
    {
        $this->getDatatablesQuery($searchValue,$orderColumn,$orderDir);
        if ($length != -1)
            $this->db->limit($length, $start);
        $query = $this->db->get();
        return $query->getResult();
    }

    public function countFiltered($searchValue='',$orderColumn=0,$orderDir='asc')
    {
        $this->getDatatablesQuery($searchValue,$orderColumn,$orderDir);
        return $this->db->countAllResults();
    }

    public function insert_data($data)
    {
        $this->db->table($this->tableIns)->insert($data);
        return $this->db->insertID();
    }

    public function update_data($id,$data)
    {
        return $this->db->table($this->tableIns)->where($this->primaryKey, $id)->update($data);
    }

    public function delete_data($id,$users_id=null)
    {
        if ($this->useSoftDeletes === true) {
            return $this->db->table($this->tableIns)->where($this->primaryKey, $id)->update([$this->deletedField => date('Y-m-d H:i:s'),$this->deletedFieldUser=>$users_id]);
        }else{
            return $this->db->table($this->tableIns)->where($this->primaryKey, $id)->delete();
        }
    }

    public function getBarangWithGolongan()
    {
        $sql = "
            WITH grpbrg_aggregated AS (
                SELECT 
                    nmbrg,
                    SUM(sawal) AS total_sawal,
                    SUM(qty) AS total_qty
                FROM grpbrg
                GROUP BY nmbrg
            )
            SELECT 
                brg.nmbrg,
                brg.nama,
                brg.tipe,
                brg.hpbeli,
                tipe.ket,
                COALESCE(SUM(agg.total_sawal), 0) AS sawal,
                COALESCE(SUM(agg.total_qty), 0) AS qty,
                COALESCE(SUM(agg.total_sawal), 0) + COALESCE(SUM(agg.total_qty), 0) AS stok_akhir,
                (COALESCE(SUM(agg.total_sawal), 0) + COALESCE(SUM(agg.total_qty), 0))*brg.hpbeli AS hpp
            FROM brg
            LEFT JOIN grpbrg_aggregated AS agg ON agg.nmbrg = brg.nmbrg
            LEFT JOIN tipe ON tipe.tipe = brg.tipe
            GROUP BY brg.nmbrg, brg.nama, brg.tipe, brg.hpbeli, tipe.ket
            HAVING COALESCE(SUM(agg.total_sawal), 0) + COALESCE(SUM(agg.total_qty), 0) > 0
            ORDER BY brg.nama;
        ";

        $query = $this->db->query($sql);
        return $query->getResult();
    }

    public function getDetailBarangByTipe($tipe)
    {
        $sql = "
            WITH grpbrg_aggregated AS (
                SELECT 
                    nmbrg,
                    SUM(sawal) AS total_sawal,
                    SUM(qty) AS total_qty
                FROM grpbrg
                GROUP BY nmbrg
            )
            SELECT 
                brg.nmbrg,
                brg.nama,
                brg.tipe,
                brg.hpbeli,
                tipe.ket,
                COALESCE(SUM(agg.total_sawal), 0) AS sawal,
                COALESCE(SUM(agg.total_qty), 0) AS qty,
                COALESCE(SUM(agg.total_sawal), 0) + COALESCE(SUM(agg.total_qty), 0) AS stok_akhir,
                (COALESCE(SUM(agg.total_sawal), 0) + COALESCE(SUM(agg.total_qty), 0))*brg.hpbeli AS hpp
            FROM brg
            LEFT JOIN grpbrg_aggregated AS agg ON agg.nmbrg = brg.nmbrg
            LEFT JOIN tipe ON tipe.tipe = brg.tipe
            WHERE brg.tipe = '$tipe'
            GROUP BY brg.nmbrg, brg.nama, brg.tipe, brg.hpbeli, tipe.ket
            HAVING COALESCE(SUM(agg.total_sawal), 0) + COALESCE(SUM(agg.total_qty), 0) > 0
            ORDER BY brg.nama;
        ";

        $query = $this->db->query($sql);
        return $query->getResult();
    }

    public function getStockByBrg($kdbrg, $tgl)
    {
        $sql = "
            WITH transactions AS (
                SELECT 
                    tr.nmbrg,
                    SUM(CASE WHEN mstr.tipe = 'B'  THEN tr.qty ELSE 0 END) AS pembelian,
                    SUM(CASE WHEN mstr.tipe = 'KJ' THEN tr.qty ELSE 0 END) AS retur_penjualan,
                    SUM(CASE WHEN mstr.tipe = 'KT' THEN tr.qty ELSE 0 END) AS koreksi_tambah,
                    SUM(CASE WHEN mstr.tipe = 'N'  THEN tr.qty ELSE 0 END) AS mutasi_masuk,
                    SUM(CASE WHEN mstr.tipe = 'J'  THEN tr.qty ELSE 0 END) AS penjualan,
                    SUM(CASE WHEN mstr.tipe = 'KB' THEN tr.qty ELSE 0 END) AS retur_pembelian,
                    SUM(CASE WHEN mstr.tipe = 'KK' THEN tr.qty ELSE 0 END) AS koreksi_kurang,
                    SUM(CASE WHEN mstr.tipe = 'M'  THEN tr.qty ELSE 0 END) AS mutasi_keluar,
                    SUM(CASE WHEN mstr.tipe = 'XL' THEN tr.qty ELSE 0 END) AS penjualan_konsinyasi
                FROM tr
                JOIN mstr ON mstr.kdtr = tr.kdtr 
                          AND mstr.trans = '1' 
                          AND mstr.tgl <= ?
                WHERE tr.nmbrg = ?
                GROUP BY tr.nmbrg
            ),

            saldo AS (
                SELECT nmbrg, SUM(sawal) AS sawal
                FROM grpbrg
                GROUP BY nmbrg
            )

            SELECT 
                brg.nmbrg,
                brg.nama,
                brg.tipe,
                tipe.ket,
                COALESCE(saldo.sawal, 0) AS stock_awal,
                
                COALESCE(transactions.pembelian, 0) AS pembelian,
                COALESCE(transactions.retur_penjualan, 0) AS retur_penjualan,
                COALESCE(transactions.koreksi_tambah, 0) AS koreksi_tambah,
                COALESCE(transactions.mutasi_masuk, 0) AS mutasi_masuk,
                COALESCE(transactions.penjualan, 0) AS penjualan,
                COALESCE(transactions.retur_pembelian, 0) AS retur_pembelian,
                COALESCE(transactions.koreksi_kurang, 0) AS koreksi_kurang,
                COALESCE(transactions.mutasi_keluar, 0) AS mutasi_keluar,
                COALESCE(transactions.penjualan_konsinyasi, 0) AS penjualan_konsinyasi,

                (COALESCE(transactions.pembelian, 0) + COALESCE(transactions.retur_penjualan, 0) + 
                 COALESCE(transactions.koreksi_tambah, 0) + COALESCE(transactions.mutasi_masuk, 0)) AS masuk,

                (COALESCE(transactions.penjualan, 0) + COALESCE(transactions.retur_pembelian, 0) + 
                 COALESCE(transactions.koreksi_kurang, 0) + COALESCE(transactions.mutasi_keluar, 0) + 
                 COALESCE(transactions.penjualan_konsinyasi, 0)) AS keluar,

                (COALESCE(saldo.sawal, 0) + 
                 COALESCE(transactions.pembelian, 0) + COALESCE(transactions.retur_penjualan, 0) + 
                 COALESCE(transactions.koreksi_tambah, 0) + COALESCE(transactions.mutasi_masuk, 0) - 
                 COALESCE(transactions.penjualan, 0) - COALESCE(transactions.retur_pembelian, 0) - 
                 COALESCE(transactions.koreksi_kurang, 0) - COALESCE(transactions.mutasi_keluar, 0) - 
                 COALESCE(transactions.penjualan_konsinyasi, 0)) AS stock_akhir
            FROM brg
            LEFT JOIN tipe ON tipe.tipe = brg.tipe
            LEFT JOIN saldo ON saldo.nmbrg = brg.nmbrg
            LEFT JOIN transactions ON transactions.nmbrg = brg.nmbrg
            WHERE brg.nmbrg = ?
            ORDER BY brg.tipe;
        ";

        $query = $this->db->query($sql, [$tgl, $kdbrg, $kdbrg]);
        return $query->getRow();
    }

    public function getStockByBrgAll($tgl)
    {
        $sql = "
            WITH transactions AS (
                SELECT 
                    tr.nmbrg,
                    SUM(CASE WHEN mstr.tipe = 'B'  THEN tr.qty ELSE 0 END) AS pembelian,
                    SUM(CASE WHEN mstr.tipe = 'KJ' THEN tr.qty ELSE 0 END) AS retur_penjualan,
                    SUM(CASE WHEN mstr.tipe = 'KT' THEN tr.qty ELSE 0 END) AS koreksi_tambah,
                    SUM(CASE WHEN mstr.tipe = 'N'  THEN tr.qty ELSE 0 END) AS mutasi_masuk,
                    SUM(CASE WHEN mstr.tipe = 'J'  THEN tr.qty ELSE 0 END) AS penjualan,
                    SUM(CASE WHEN mstr.tipe = 'KB' THEN tr.qty ELSE 0 END) AS retur_pembelian,
                    SUM(CASE WHEN mstr.tipe = 'KK' THEN tr.qty ELSE 0 END) AS koreksi_kurang,
                    SUM(CASE WHEN mstr.tipe = 'M'  THEN tr.qty ELSE 0 END) AS mutasi_keluar,
                    SUM(CASE WHEN mstr.tipe = 'XL' THEN tr.qty ELSE 0 END) AS penjualan_konsinyasi
                FROM tr
                JOIN mstr ON mstr.kdtr = tr.kdtr 
                          AND mstr.trans = '1'
                          AND mstr.tgl <= ?
                GROUP BY tr.nmbrg
            ),

            saldo AS (
                SELECT nmbrg, SUM(sawal) AS sawal
                FROM grpbrg
                GROUP BY nmbrg
            )

            SELECT 
                brg.nmbrg,
                brg.nama,
                brg.tipe,
                tipe.ket,
                COALESCE(saldo.sawal, 0) AS stock_awal,
                
                COALESCE(transactions.pembelian, 0) AS pembelian,
                COALESCE(transactions.retur_penjualan, 0) AS retur_penjualan,
                COALESCE(transactions.koreksi_tambah, 0) AS koreksi_tambah,
                COALESCE(transactions.mutasi_masuk, 0) AS mutasi_masuk,
                COALESCE(transactions.penjualan, 0) AS penjualan,
                COALESCE(transactions.retur_pembelian, 0) AS retur_pembelian,
                COALESCE(transactions.koreksi_kurang, 0) AS koreksi_kurang,
                COALESCE(transactions.mutasi_keluar, 0) AS mutasi_keluar,
                COALESCE(transactions.penjualan_konsinyasi, 0) AS penjualan_konsinyasi,

                (COALESCE(transactions.pembelian, 0) + COALESCE(transactions.retur_penjualan, 0) + 
                 COALESCE(transactions.koreksi_tambah, 0) + COALESCE(transactions.mutasi_masuk, 0)) AS masuk,

                (COALESCE(transactions.penjualan, 0) + COALESCE(transactions.retur_pembelian, 0) + 
                 COALESCE(transactions.koreksi_kurang, 0) + COALESCE(transactions.mutasi_keluar, 0) + 
                 COALESCE(transactions.penjualan_konsinyasi, 0)) AS keluar,

                (COALESCE(saldo.sawal, 0) + 
                 COALESCE(transactions.pembelian, 0) + COALESCE(transactions.retur_penjualan, 0) + 
                 COALESCE(transactions.koreksi_tambah, 0) + COALESCE(transactions.mutasi_masuk, 0) - 
                 COALESCE(transactions.penjualan, 0) - COALESCE(transactions.retur_pembelian, 0) - 
                 COALESCE(transactions.koreksi_kurang, 0) - COALESCE(transactions.mutasi_keluar, 0) - 
                 COALESCE(transactions.penjualan_konsinyasi, 0)) AS stock_akhir
            FROM brg
            LEFT JOIN tipe ON tipe.tipe = brg.tipe
            LEFT JOIN saldo ON saldo.nmbrg = brg.nmbrg
            LEFT JOIN transactions ON transactions.nmbrg = brg.nmbrg
            ORDER BY brg.tipe;
        ";

        $query = $this->db->query($sql, [$tgl]);
        return $query->getResult();
    }

    public function getStockSummaryPerMonth($startDate, $endDate)
    {
        $sql = "
            WITH dates AS (
                SELECT to_char(date_trunc('month', dd) + interval '1 month - 1 day', 'YYYY-MM-DD')::date AS tanggal_akhir_bulan
                FROM generate_series(?, ?, interval '1 month') dd
            )
            SELECT 
                stock_data.tipe,
                to_char(d.tanggal_akhir_bulan, 'MM-YYYY') AS bulan_tahun, -- Format MM-YYYY
                stock_data.stock_akhir
            FROM dates d
            CROSS JOIN LATERAL (
                SELECT brg.tipe, 
                    SUM(
                        COALESCE(saldo.sawal, 0) + 
                        COALESCE(transactions.pembelian, 0) + COALESCE(transactions.retur_penjualan, 0) + 
                        COALESCE(transactions.koreksi_tambah, 0) + COALESCE(transactions.mutasi_masuk, 0) - 
                        COALESCE(transactions.penjualan, 0) - COALESCE(transactions.retur_pembelian, 0) - 
                        COALESCE(transactions.koreksi_kurang, 0) - COALESCE(transactions.mutasi_keluar, 0) - 
                        COALESCE(transactions.penjualan_konsinyasi, 0)
                    ) AS stock_akhir
                FROM brg
                LEFT JOIN tipe ON tipe.tipe = brg.tipe
                LEFT JOIN (
                    SELECT brg.nmbrg, SUM(grpbrg.sawal) AS sawal
                    FROM grpbrg
                    JOIN brg ON brg.nmbrg = grpbrg.nmbrg
                    GROUP BY brg.nmbrg
                ) saldo ON saldo.nmbrg = brg.nmbrg
                LEFT JOIN (
                    SELECT 
                        tr.nmbrg,
                        SUM(CASE WHEN mstr.tipe = 'B'  THEN tr.qty ELSE 0 END) AS pembelian,
                        SUM(CASE WHEN mstr.tipe = 'KJ' THEN tr.qty ELSE 0 END) AS retur_penjualan,
                        SUM(CASE WHEN mstr.tipe = 'KT' THEN tr.qty ELSE 0 END) AS koreksi_tambah,
                        SUM(CASE WHEN mstr.tipe = 'N'  THEN tr.qty ELSE 0 END) AS mutasi_masuk,
                        SUM(CASE WHEN mstr.tipe = 'J'  THEN tr.qty ELSE 0 END) AS penjualan,
                        SUM(CASE WHEN mstr.tipe = 'KB' THEN tr.qty ELSE 0 END) AS retur_pembelian,
                        SUM(CASE WHEN mstr.tipe = 'KK' THEN tr.qty ELSE 0 END) AS koreksi_kurang,
                        SUM(CASE WHEN mstr.tipe = 'M'  THEN tr.qty ELSE 0 END) AS mutasi_keluar,
                        SUM(CASE WHEN mstr.tipe = 'XL' THEN tr.qty ELSE 0 END) AS penjualan_konsinyasi
                    FROM tr
                    JOIN mstr ON mstr.kdtr = tr.kdtr 
                              AND mstr.trans = '1'
                              AND mstr.tgl <= d.tanggal_akhir_bulan
                    GROUP BY tr.nmbrg
                ) transactions ON transactions.nmbrg = brg.nmbrg
                GROUP BY brg.tipe
            ) stock_data
            ORDER BY stock_data.stock_akhir DESC;
        ";

        $query = $this->db->query($sql, [$startDate, $endDate]);
        $getRows = $query->getResultArray();
        // $data = [];
        // foreach($getRows as $row){
        //     $data[$row['tipe']] = $row;
        // }
        return $getRows;
    }

    public function getStockSummaryPerMonthByTipe($startDate, $endDate, $tipe)
    {
        $sql = "
            WITH dates AS (
                SELECT to_char(date_trunc('month', dd) + interval '1 month - 1 day', 'YYYY-MM-DD')::date AS tanggal_akhir_bulan
                FROM generate_series(?, ?, interval '1 month') dd
            )
            SELECT 
                stock_data.tipe,
                to_char(d.tanggal_akhir_bulan, 'MM-YYYY') AS bulan_tahun, -- Format MM-YYYY
                stock_data.stock_akhir
            FROM dates d
            CROSS JOIN LATERAL (
                SELECT brg.tipe, 
                    SUM(
                        COALESCE(saldo.sawal, 0) + 
                        COALESCE(transactions.pembelian, 0) + COALESCE(transactions.retur_penjualan, 0) + 
                        COALESCE(transactions.koreksi_tambah, 0) + COALESCE(transactions.mutasi_masuk, 0) - 
                        COALESCE(transactions.penjualan, 0) - COALESCE(transactions.retur_pembelian, 0) - 
                        COALESCE(transactions.koreksi_kurang, 0) - COALESCE(transactions.mutasi_keluar, 0) - 
                        COALESCE(transactions.penjualan_konsinyasi, 0)
                    ) AS stock_akhir
                FROM brg
                LEFT JOIN tipe ON tipe.tipe = brg.tipe
                LEFT JOIN (
                    SELECT brg.nmbrg, SUM(grpbrg.sawal) AS sawal
                    FROM grpbrg
                    JOIN brg ON brg.nmbrg = grpbrg.nmbrg
                    GROUP BY brg.nmbrg
                ) saldo ON saldo.nmbrg = brg.nmbrg
                LEFT JOIN (
                    SELECT 
                        tr.nmbrg,
                        SUM(CASE WHEN mstr.tipe = 'B'  THEN tr.qty ELSE 0 END) AS pembelian,
                        SUM(CASE WHEN mstr.tipe = 'KJ' THEN tr.qty ELSE 0 END) AS retur_penjualan,
                        SUM(CASE WHEN mstr.tipe = 'KT' THEN tr.qty ELSE 0 END) AS koreksi_tambah,
                        SUM(CASE WHEN mstr.tipe = 'N'  THEN tr.qty ELSE 0 END) AS mutasi_masuk,
                        SUM(CASE WHEN mstr.tipe = 'J'  THEN tr.qty ELSE 0 END) AS penjualan,
                        SUM(CASE WHEN mstr.tipe = 'KB' THEN tr.qty ELSE 0 END) AS retur_pembelian,
                        SUM(CASE WHEN mstr.tipe = 'KK' THEN tr.qty ELSE 0 END) AS koreksi_kurang,
                        SUM(CASE WHEN mstr.tipe = 'M'  THEN tr.qty ELSE 0 END) AS mutasi_keluar,
                        SUM(CASE WHEN mstr.tipe = 'XL' THEN tr.qty ELSE 0 END) AS penjualan_konsinyasi
                    FROM tr
                    JOIN mstr ON mstr.kdtr = tr.kdtr 
                              AND mstr.trans = '1'
                              AND mstr.tgl <= d.tanggal_akhir_bulan
                    GROUP BY tr.nmbrg
                ) transactions ON transactions.nmbrg = brg.nmbrg
                WHERE brg.tipe = ?
                GROUP BY brg.tipe
            ) stock_data
            ORDER BY stock_data.tipe, d.tanggal_akhir_bulan;
        ";

        $query = $this->db->query($sql, [$startDate, $endDate, $tipe]);
        return $query->getRow();
    }
}
