<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Config;
use CodeIgniter\Database\ConnectionInterface;

class TrModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'tr';
    protected $tableIns         = 'tr';
    protected $primaryKey       = 'kdtr';
    protected $useAutoIncrement = false;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['kdtr', 'nou', 'nmbrg', 'qty', 'xqty', 'sat', 'vhrg', 'valas', 'disc', 'hrg', 'tot', 'hrgb', 'tpjual'];

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

    protected $column_order = [null,'kdtr', 'nou'];
    protected $column_search = ['kdtr', 'nou'];
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

    public function getDataKomisi($bulan=null, $tahun=null, $upd=false)
    {
        if($upd){
            // Ubah TUNAI 30/2 jadi 30/12
            $sql1 = "
                UPDATE byr
                SET ket = 'TUNAI 30/12'
                WHERE kdbyr='P221200545'
            ";
            $this->db->query($sql1);

            // Ubah TUNAI 31/11 jadi 30/11
            $sql2 = "
                UPDATE byr
                SET ket = 'TUNAI 30/11'
                WHERE kdbyr='P241100589'
            ";
            $this->db->query($sql2);
        }

        $startDate = "{$tahun}-{$bulan}-01";
        $endDate   = date("Y-m-t", strtotime($startDate));

        $sql = "
            WITH pembayaran_tunai AS (
                SELECT DISTINCT ON (bd.kdtr)
                    bd.kdtr,
                    (regexp_match(b.ket, '\\d{1,2}[-/]\\d{1,2}'))[1] || '/' || b.th AS tanggal_bayar_tunai2,
                    TO_CHAR(
                      TO_DATE(
                        b.th || '-' ||
                        LPAD(split_part((regexp_match(b.ket, '\\d{1,2}[-/]\\d{1,2}'))[1], '/', 2), 2, '0') || '-' ||
                        LPAD(split_part((regexp_match(b.ket, '\\d{1,2}[-/]\\d{1,2}'))[1], '/', 1), 2, '0'),
                        'YYYY-MM-DD'
                      ),
                      'YYYY-MM-DD'
                    ) AS tanggal_bayar_tunai
                FROM byr b
                JOIN byrdet bd ON bd.kdbyr = b.kdbyr
                WHERE b.ket ~ '\\d{1,2}[-/]\\d{1,2}' 
                  AND b.tunai > 0
                  AND b.ket ~* '(^| )TUNAI($| )' 
                  AND b.ket !~* 'TTP TUNAI' 
                  AND b.ket !~* 'TITIP'
                  AND split_part((regexp_match(b.ket, '\\d{1,2}[-/]\\d{1,2}'))[1], '/', 1)::int BETWEEN 1 AND 31
                  AND split_part((regexp_match(b.ket, '\\d{1,2}[-/]\\d{1,2}'))[1], '/', 2)::int BETWEEN 1 AND 12
                ORDER BY bd.kdtr, b.tgl DESC
            ),
            data_transaksi AS (
                SELECT 
                    tr.kdtr,
                    CASE mstr.tipe
                        WHEN 'J' THEN 'Penjualan'
                        WHEN 'KJ' THEN 'Retur'
                        ELSE 'Batal'
                    END AS tipe_transaksi,

                    COALESCE(kbrg.komisi, 0) * 
                    CASE WHEN mstr.tipe = 'KJ' THEN -1 ELSE 1 END AS komisi,

                    tr.qty * CASE WHEN mstr.tipe = 'KJ' THEN -1 ELSE 1 END AS qty,

                    CASE 
                        WHEN kbrg.tipe = 'R' THEN 
                            (COALESCE(kbrg.komisi, 0) * tr.qty) * 
                            CASE WHEN mstr.tipe = 'KJ' THEN -1 ELSE 1 END
                        WHEN kbrg.tipe = 'P' THEN 
                            kbrg.komisi * tr.tot / 100
                        ELSE 0
                    END AS total_komisi_hitung,

                    CASE 
                        WHEN mstr.tipe = 'KJ' THEN -1 * ktr.tkomisi
                        ELSE ktr.tkomisi
                    END AS total_komisi,

                    CASE CAST(kmstr.trans AS INTEGER)
                        WHEN 0 THEN 'Proses'
                        WHEN 1 THEN 'Dibayarkan'
                        ELSE 'Dihanguskan'
                    END AS status_komisi,
                    
                    tr.nmbrg, 
                    tr.hrg, 
                    CAST(tr.disc AS NUMERIC) AS disc_numeric,
                    tr.hrgb, 
                    (tr.hrg * tr.qty) AS total_harga,

                    ROUND(
                        CAST((tr.hrg * tr.qty) AS NUMERIC) - 
                        (CAST(tr.disc AS NUMERIC) / 100.0 * CAST((tr.hrg * tr.qty) AS NUMERIC)), 
                        0
                    ) * 
                    CASE 
                        WHEN brg.tipe = 'ONDA' THEN 1
                        WHEN mstr.tipe = 'KJ' THEN -1
                        ELSE 1
                    END AS pendapatan,

                    (tr.hrgb * tr.qty) * 
                    CASE WHEN mstr.tipe = 'KJ' THEN -1 ELSE 1 END AS harga_pokok,

                    mstr.tgl,
                    mstr.tglunas,       
                    mstr.islunas,       
                    brg.tipe,
                    mstr.nmsales,       
                    mstr.kdcust,
                    cust.nmcust, 
                    EXTRACT(YEAR FROM mstr.tgl) AS tahun,
                    EXTRACT(MONTH FROM mstr.tgl) AS bulan,
                    kmstr.totkomisi, 
                    kmstr.rtotkomisi, 
                    kmstr.kdkom, 
                    komisi.tgl AS tgl_komisi,
                    kmstr.bayar,
                    kmstr.trans,
                    mstr.user_id,

                    pt.tanggal_bayar_tunai

                FROM tr
                INNER JOIN brg ON tr.nmbrg = brg.nmbrg
                LEFT JOIN kbrg ON brg.nmbrg = kbrg.nmbrg
                INNER JOIN mstr ON tr.kdtr = mstr.kdtr 
                INNER JOIN cust ON mstr.kdcust = cust.kdcust
                LEFT JOIN kmstr ON kmstr.kdtr = mstr.kdtr
                LEFT JOIN komisi ON komisi.kdkom = kmstr.kdkom
                LEFT JOIN ktr ON ktr.kdtr = tr.kdtr AND ktr.nou = tr.nou
                LEFT JOIN pembayaran_tunai pt ON pt.kdtr = mstr.kdtr
                WHERE mstr.tipe IN ('J', 'KJ') AND mstr.trans != '2'
                  AND mstr.tgl BETWEEN ? AND ?
            )
            SELECT *, 
                (pendapatan - harga_pokok) AS laba_kotor
            FROM data_transaksi
            WHERE pendapatan <> 0
            ORDER BY kdtr DESC, tgl DESC
        ";

        $query = $this->db->query($sql, [$startDate, $endDate]);
        return $query->getResultArray();
    }

    public function getTransaksiTigaBulanTerakhir($sumber = '', $tipe = '')
    {
        $query = "
            SELECT 
                mstr.tgl,
                tr.kdtr,
                tr.nmbrg,
                tr.qty * CASE WHEN mstr.tipe = 'KJ' THEN -1 ELSE 1 END AS qty_tr,
                tr.hrg,
                (tr.hrg * (tr.qty * CASE WHEN mstr.tipe = 'KJ' THEN -1 ELSE 1 END)) AS total_harga, 
                CASE mstr.tipe
                    WHEN 'J' THEN 'Penjualan'
                    WHEN 'KJ' THEN 'Retur'
                    ELSE 'Batal'
                END AS tipe_transaksi,
                brg.tipe,
                :sumber: AS sumber -- Menambahkan nilai statis sebagai sumber
            FROM tr
            INNER JOIN brg ON tr.nmbrg = brg.nmbrg
            INNER JOIN mstr ON tr.kdtr = mstr.kdtr
            WHERE mstr.tipe IN ('J', 'KJ')
            AND mstr.tgl >= DATE_TRUNC('month', CURRENT_DATE) - INTERVAL '3 months'
            AND mstr.tgl < DATE_TRUNC('month', CURRENT_DATE)
        ";

        if (!empty($tipe)) {
            $query .= " AND brg.tipe = :tipe:";
        }

        $query .= " ORDER BY mstr.tgl DESC";

        $params = ["sumber" => $sumber];
        if (!empty($tipe)) {
            $params["tipe"] = $tipe;
        }

        return $this->db->query($query, $params)->getResult();
    }

    public function getPembayaranTunaiByKdtr($kdtr)
    {
        $sql = "
            SELECT 
                byr.kdbyr,
                byrdet.kdtr,
                byr.ket,
                (regexp_match(byr.ket, '\d{1,2}[-/]\d{1,2}'))[1] || '/' || byr.th AS tanggal_bayar
            FROM 
                byr
            JOIN 
                byrdet ON byr.kdbyr = byrdet.kdbyr
            WHERE 
                byrdet.kdtr = ?
                AND byr.ket ~ '\\d{1,2}[-/]\\d{1,2}' 
                AND byr.tunai > 0
                AND byr.ket ~* '(^| )TUNAI($| )' 
                AND byr.ket !~* 'TTP TUNAI' 
                AND byr.ket !~* 'TITIP'
            ORDER BY 
                byr.th DESC,
                substring((regexp_match(byr.ket, '\\d{1,2}[-/]\\d{1,2}'))[1], '\\d{1,2}')::int DESC
            LIMIT 1
        ";

        return $this->db->query($sql, [$kdtr])->getResultArray();
    }

}
