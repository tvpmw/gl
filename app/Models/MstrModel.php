<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Config;
use CodeIgniter\Database\ConnectionInterface;

class MstrModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'mstr';
    protected $tableIns         = 'mstr';
    protected $primaryKey       = 'kdtr';
    protected $useAutoIncrement = false;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['kdtr', 'tipe', 'nmgrp', 'tothrg', 'disc', 'dtot', 'pph', 'gtot', 'byr', 'retur', 'dn', 'cn', 'kdptg', 'reff', 'mts', 'tgl', 'jam', 'trans', 'bon', 'cnt', 'kdsupp', 'kdcust', 'due', 'islunas', 'tglunas', 'kdtr2', 'tp', 'printed', 'nmsales', 'th', 'bl', 'user_id', 'dumi', 'discp1', 'discp2', 'discp3'];

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

    protected $column_order = [null,'mstr.kdtr', 'mstr.tgl', 'mstr.gtot', 'cust.nmcust', 'n.npwp', 'n.name','n.jenis', 'n.status_wp'];
    protected $column_search = ['mstr.kdtr', 'mstr.tgl', 'mstr.gtot', 'cust.nmcust', 'n.npwp', 'n.name','n.jenis', 'n.status_wp'];
    protected $order = ['mstr.kdtr' => 'ASC'];
    protected $online = ['BHINEKA', 'BHINNEKA', 'BLIBLI.COM', 'BUKALAPAK', 'EKATALOG', 'ETALASE', 'IG', 'GOSHOP', 'FACEBOOK', 'JD.ID', 'LAZADA', 'OLX', 'OLXACD', 'ONLINE', 'SHOPEE', 'SHOPEEACD', 'SHOPEES', 'SHOPPOFF', 'TIKTOKSHOP', 'TOKOPEDIA', 'TOKPEDACD', 'TOKPEDDDL', 'TOKPEDOFFL', 'TOKPEDS', 'WEBSITE', 'SHOPEEOFF', 'TOKPEDOFF', 'SHOPEEBLP', 'MANGGA', 'TIKTOKMIST'];


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

    private function getDatatablesQuery($searchValue='',$orderColumn=0,$orderDir='asc')
    {
        if ($this->useSoftDeletes === true) {
            $this->dt->where($this->table . '.' . $this->deletedField, null);
        }
        $i = 0;
        foreach ($this->column_search as $item) {
            if ($searchValue) {
                if ($i === 0) {
                    $this->dt->groupStart();
                    $this->dt->like($item, $searchValue);
                } else {
                    $this->dt->orLike($item, $searchValue);
                }
                if (count($this->column_search) - 1 == $i)
                    $this->dt->groupEnd();
            }
            $i++;
        }

        if ($orderColumn !=0) {
            $this->dt->orderBy($this->column_order[$orderColumn], $orderDir);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->dt->orderBy(key($order), $order[key($order)]);
        }
    }

    public function getDatatables($length=10,$start=0,$searchValue='',$orderColumn=0,$orderDir='asc')
    {
        $this->getDatatablesQuery($searchValue,$orderColumn,$orderDir);
        if ($length != -1)
            $this->dt->limit($length, $start);
        $query = $this->dt->get();
        return $query->getResult();
    }

    public function countFiltered($searchValue='',$orderColumn=0,$orderDir='asc')
    {
        $this->getDatatablesQuery($searchValue,$orderColumn,$orderDir);
        return $this->dt->countAllResults();
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

    public function getDataNpwp($startDate = null, $endDate = null, $sales_type = null, $prefix = null)
    {
        $builder = $this->db->table($this->table)->select('cust.npwp');
        $builder->join("cust", "cust.kdcust = mstr.kdcust", 'LEFT');

        $builder->where('mstr.tipe', 'J');
        $builder->where('mstr.trans', '1');
        $builder->where('cust.npwp !=', '');

        // if (!empty($prefix)) {
        //     $builder->where("SUBSTRING(kdtr, 1, 1) = '{$prefix}'", null, false);
        // }

        if (!empty($startDate)) {
            $builder->where('mstr.tgl >=', $startDate);
        }

        if (!empty($endDate)) {
            $builder->where('mstr.tgl <=', $endDate);
        }

        if ($sales_type == 'ONLINE') {
            // $builder->whereIn('cust.wil', $this->online);
            $builder->where('cust.npwp', '');
        } else {
            // $builder->whereNotIn('cust.wil', $this->online);
            $builder->where('cust.npwp !=', '');
        }

        // Filter: NPWP yang belum pernah masuk ke tabel crm.cust_npwp
        $subquery = $this->db->table('crm.cust_npwp')->where('status_wp','VALID')->select('npwpcust');
        $builder->whereNotIn('cust.npwp', $subquery);

        return $builder->get()->getResult();
    }

    public function getAllData($startDate = null, $endDate = null, $sales_type = null, $prefix = null, $selectedTrx = null)
    {
        $builder = $this->db->table($this->table)
            ->select('mstr.kdtr, mstr.tgl, mstr.gtot, mstr.retur, cust.nmcust, cust.npwp, n.npwp as newnpwp, n.address as address, n.jenis, n.name, n.status_wp')
            ->join("cust", "cust.kdcust = mstr.kdcust", 'LEFT')
            ->join("crm.cust_npwp as n", "n.npwpcust = cust.npwp", 'LEFT')
            ->join("crm.tidak_dibuat td", "td.kode_trx = mstr.kdtr", 'LEFT')
            ->join("crm.tax_generate tg", "tg.kode_trx = mstr.kdtr", 'LEFT')
            ->join("crm.tax_generate_online tgo", "tgo.serial_no = mstr.kdtr", 'LEFT')
            ->where('mstr.tipe', 'J')
            ->where('mstr.trans', '1')
            ->where('td.kode_trx IS NULL')
            ->where('tg.kode_trx IS NULL')
            ->where('tgo.serial_no IS NULL')
            ->orderBy('mstr.kdtr', 'ASC'); // Add this line

        // Apply date filters
        if (!empty($startDate)) {
            $builder->where('mstr.tgl >=', $startDate);
        }

        if (!empty($endDate)) {
            $builder->where('mstr.tgl <=', $endDate);
        }

        // Apply sales type filter
        if ($sales_type == 'ONLINE') {
            // $builder->whereIn('cust.wil', $this->online);
            $builder->where('cust.npwp', '');
        } else {
            // $builder->whereNotIn('cust.wil', $this->online);
            $builder->where('cust.npwp !=', '');
        }

        // Apply prefix filter if provided
        if (!empty($prefix)) {
            $builder->where("SUBSTRING(mstr.kdtr, 1, 1) = '{$prefix}'", null, false);
        }

        // Tambahkan filter untuk kode transaksi yang dipilih
        if (!empty($selectedTrx)) {
            $selectedTrxArray = explode(',', $selectedTrx);
            $builder->whereIn('mstr.kdtr', $selectedTrxArray);
        }

        // Order by date descending for most recent first
        $builder->orderBy('mstr.tgl', 'DESC');

        // Execute query and return results
        return $builder->get()->getResult();
    }

    public function getData($start, $length, $search, $orderColumn, $orderDir, $startDate = null, $endDate = null, $sales_type = null, $prefix = null)
    {
        $builder = $this->db->table($this->table)
            ->select('mstr.*,cust.nmcust,cust.npwp,n.npwp as newnpwp,n.jenis,n.name,n.address,n.status_wp')
            ->join("cust", "cust.kdcust = mstr.kdcust", 'LEFT')
            ->join("crm.cust_npwp as n", "n.npwpcust = cust.npwp", 'LEFT')
            // Add LEFT JOIN with tidak_dibuat, tax_generate, and tax_generate_online to check existence
            ->join("crm.tidak_dibuat td", "td.kode_trx = mstr.kdtr", 'LEFT')
            ->join("crm.tax_generate tg", "tg.kode_trx = mstr.kdtr", 'LEFT')
            ->join("crm.tax_generate_online tgo", "tgo.serial_no = mstr.kdtr", 'LEFT')
            ->where('mstr.tipe', 'J')
            ->where('mstr.trans', '1')
            // Add conditions to exclude records that exist in tidak_dibuat, tax_generate, and tax_generate_online
            ->where('td.kode_trx IS NULL')
            ->where('tg.kode_trx IS NULL')
            ->where('tgo.serial_no IS NULL');

        // Rest of the method remains the same
        if (!empty($search)) {
            $builder->groupStart();
            foreach ($this->column_search as $column) {
                if(!empty($column)){
                    if (str_contains($column, 'mstr')) {
                        $column = str_replace('mstr.', '', $column);
                        $builder->orWhere("CAST(mstr.\"$column\" AS TEXT) ILIKE '%$search%'");
                    }else if (str_contains($column, 'cust')) {
                        $column = str_replace('cust.', '', $column);
                        $builder->orWhere("CAST(cust.\"$column\" AS TEXT) ILIKE '%$search%'");
                    }else{
                        $column = str_replace('n.', '', $column);
                        $builder->orWhere("CAST(n.\"$column\" AS TEXT) ILIKE '%$search%'");
                    }
                }
            }
            $builder->groupEnd();
        }

        if (!empty($startDate)) {
            $builder->where('mstr.tgl >=', $startDate);
        }

        if (!empty($endDate)) {
            $builder->where('mstr.tgl <=', $endDate);
        }

        if($sales_type == 'ONLINE'){
            $builder->whereIn('cust.wil', $this->online);
        }else{
            $builder->whereNotIn('cust.wil', $this->online);
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

    public function countFilter($search, $startDate = null, $endDate = null, $sales_type = null, $prefix = null)
    {
        $builder = $this->db->table($this->table)
            ->select('mstr.kdtr')
            ->join("cust", "cust.kdcust = mstr.kdcust", 'LEFT')
            ->join("crm.cust_npwp as n", "n.npwpcust = cust.npwp", 'LEFT')
            // Add LEFT JOIN with tidak_dibuat, tax_generate, and tax_generate_online
            ->join("crm.tidak_dibuat td", "td.kode_trx = mstr.kdtr", 'LEFT')
            ->join("crm.tax_generate tg", "tg.kode_trx = mstr.kdtr", 'LEFT')
            ->join("crm.tax_generate_online tgo", "tgo.serial_no = mstr.kdtr", 'LEFT')
            ->where('mstr.tipe', 'J')
            ->where('mstr.trans', '1')
            // Add conditions to exclude records that exist in tidak_dibuat, tax_generate, and tax_generate_online
            ->where('td.kode_trx IS NULL')
            ->where('tg.kode_trx IS NULL')
            ->where('tgo.serial_no IS NULL');

        if (!empty($search)) {
            $builder->groupStart();
            foreach ($this->column_search as $column) {
                if(!empty($column)){
                    if (str_contains($column, 'mstr')) {
                        $column = str_replace('mstr.', '', $column);
                        $builder->orWhere("CAST(mstr.\"$column\" AS TEXT) ILIKE '%$search%'");
                    }else if (str_contains($column, 'cust')) {
                        $column = str_replace('cust.', '', $column);
                        $builder->orWhere("CAST(cust.\"$column\" AS TEXT) ILIKE '%$search%'");
                    }else{
                        $column = str_replace('n.', '', $column);
                        $builder->orWhere("CAST(n.\"$column\" AS TEXT) ILIKE '%$search%'");
                    }
                }
            }
            $builder->groupEnd();
        }

        if (!empty($startDate)) {
            $builder->where('mstr.tgl >=', $startDate);
        }

        if (!empty($startDate)) {
            $builder->where('mstr.tgl <=', $endDate);
        }

        if($sales_type == 'ONLINE'){
            $builder->whereIn('cust.wil', $this->online);
        }else{
            $builder->whereNotIn('cust.wil', $this->online);
        }

        return $builder->countAllResults();
    }

    public function getPaymentReport()
    {
        $sql = "
            WITH query1 AS (
                SELECT 
                    EXTRACT(MONTH FROM byr.tgl) AS bulan,
                    EXTRACT(YEAR FROM byr.tgl) AS tahun,
                    SUM(byrdet.jmlbyr) AS total_bayar
                FROM byrdet
                JOIN byr ON byr.kdbyr = byrdet.kdbyr AND byr.tipe = 'P'
                GROUP BY EXTRACT(MONTH FROM byr.tgl), EXTRACT(YEAR FROM byr.tgl)
            ),
            query2 AS (
                SELECT 
                    EXTRACT(MONTH FROM mstr.tgl) AS bulan,
                    EXTRACT(YEAR FROM mstr.tgl) AS tahun,
                    SUM(mstr.gtot) AS total_omset
                FROM mstr
                WHERE mstr.tipe = 'J'
                GROUP BY EXTRACT(MONTH FROM mstr.tgl), EXTRACT(YEAR FROM mstr.tgl)
            )
            SELECT 
                q2.bulan,
                q2.tahun,
                COALESCE(q2.total_omset, 0) AS total_omset,
                COALESCE(q1.total_bayar, 0) AS total_bayar,
                COALESCE((q1.total_bayar::numeric / NULLIF(q2.total_omset, 0)) * 100, 0) AS persentase_pembayaran
            FROM query2 q2
            LEFT JOIN query1 q1 
                ON q1.bulan = q2.bulan 
                AND q1.tahun = q2.tahun
            ORDER BY q2.tahun DESC, q2.bulan DESC;
        ";

        return $this->db->query($sql)->getResult();
    }

    public function getPaymentReportByMonth($bulan, $tahun, $sumber)
    {
        $sql = "
            WITH pembayaran AS (
                SELECT nmsales, SUM(jmlbyr) as total_bayar
                FROM (
                    SELECT 
                        mstr.nmsales,
                        byrdet.jmlbyr
                    FROM byrdet
                    JOIN byr ON byr.kdbyr = byrdet.kdbyr AND byr.tipe = 'P'
                    LEFT JOIN mstr ON mstr.kdtr = byrdet.kdtr
                    WHERE 
                        byrdet.jmlbyr != 0
                        AND EXTRACT(MONTH FROM byr.tgl) = ?
                        AND EXTRACT(YEAR FROM byr.tgl) = ?
                ) AS r 
                GROUP BY nmsales
            ),
            penjualan AS (
                SELECT 
                    nmsales,
                    SUM(gtot) AS total_omset
                FROM mstr
                WHERE 
                    EXTRACT(MONTH FROM tgl) = ?
                    AND EXTRACT(YEAR FROM tgl) = ?
                    AND tipe = 'J'
                GROUP BY nmsales
            )
            SELECT 
                COALESCE(q1.nmsales, 'Pelunasan Piutang') AS nmsales,
                COALESCE(q1.total_omset, 0) AS total_omset,
                COALESCE(q2.total_bayar, 0) AS total_bayar,
                COALESCE((q2.total_bayar::numeric / NULLIF(q1.total_omset, 0)) * 100, 0) AS persentase_pembayaran,
                ? AS sumber
            FROM penjualan q1
            RIGHT JOIN pembayaran q2 ON q2.nmsales = q1.nmsales
            ORDER BY q1.nmsales;
        ";

        return $this->db->query($sql, [$bulan, $tahun, $bulan, $tahun, $sumber])->getResult();
    }

    public function getSummaryPiutang()
    {
        $query = "
            SELECT 
                tahun,
                bulan,
                SUM(total_piutang) OVER (PARTITION BY tahun ORDER BY bulan) AS total_piutang
            FROM (
                SELECT 
                    EXTRACT(YEAR FROM tgl) AS tahun,
                    EXTRACT(MONTH FROM tgl) AS bulan,
                    SUM((COALESCE(gtot, 0) + COALESCE(dn, 0)) - 
                        (COALESCE(byr, 0) + COALESCE(cn, 0) + COALESCE(retur, 0))) AS total_piutang
                FROM mstr
                WHERE tgl BETWEEN '2009-01-01' AND CURRENT_DATE
                AND tipe = 'J' AND trans = '1' 
                AND islunas <> 1
                AND SUBSTRING(kdtr, 1, 3) != 'BNS'
                GROUP BY tahun, bulan
            ) AS summary
            ORDER BY tahun DESC, bulan DESC;
        ";

        return $this->db->query($query)->getResult();
    }

    public function getLaporanPenjualanByTahun($tahun)
    {
        $builder = $this->db->table('mstr m');

        $subquery = $this->db->table('tr')
            ->select('kdtr, SUM(qty) AS tot_qty')
            ->groupBy('kdtr');

        $builder->select([
            'EXTRACT(YEAR FROM m.tgl) AS tahun',
            'EXTRACT(MONTH FROM m.tgl) AS bulan',
            'm.kdtr',
            'm.tgl',
            'm.gtot',
            'm.kdcust',
            'COALESCE(tr_sum.tot_qty, 0) AS tot_qty',
            'c.nmcust',
            'c.wil',
            'w.ket as wilket',
            'c.alm'
        ]);

        $builder->join("({$subquery->getCompiledSelect()}) tr_sum", 'tr_sum.kdtr = m.kdtr', 'left');
        $builder->join("cust c", 'c.kdcust = m.kdcust', 'left');
        $builder->join("wil w", 'c.wil = w.wil', 'left');

        $builder->where('m.tipe', 'J');
        $builder->where('m.trans', '1');
        $builder->where('m.tgl >=', "$tahun-01-01");
        $builder->where('m.tgl <', ($tahun + 1) . "-01-01");

        return $builder->get()->getResult();
    }
}
