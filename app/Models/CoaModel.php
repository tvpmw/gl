<?php

namespace App\Models;

use CodeIgniter\Model;

class CoaModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'coa';
    protected $tableIns         = 'coa';
    protected $primaryKey       = 'KDCOA';
    protected $useAutoIncrement = false;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['KDCOA', 'NMCOA', 'STAT', 'KDSUB', 'sistem', 'kdparent', 'root', 'leaf', 'tree', 'kasbank', 'trans'];

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

    public function getLaporanLabaRugi()
    {
        $sql = "
            WITH RekeningData AS (
                SELECT 
                    coadet.\"TH\" AS tahun,
                    coadet.\"BL\" AS bulan,
                    subcoa.\"TIPE\" AS tipe,
                    COALESCE(
                        SUM(
                            CASE 
                                WHEN subcoa.\"TIPE\" = 4 THEN (coadet.\"MKREDIT\" - coadet.\"MDEBET\")
                                WHEN subcoa.\"TIPE\" = 5 THEN (coadet.\"MDEBET\" - coadet.\"MKREDIT\")
                                WHEN subcoa.\"TIPE\" IN (2, 3) THEN (coadet.\"SKREDIT\" + coadet.\"MKREDIT\") - (coadet.\"SDEBET\" + coadet.\"MDEBET\")
                                ELSE (coadet.\"SDEBET\" - coadet.\"SKREDIT\") + (coadet.\"MDEBET\" - coadet.\"MKREDIT\")
                            END
                        ), 
                        0
                    ) AS nilai
                FROM coa
                LEFT JOIN subcoa ON coa.\"KDSUB\" = subcoa.\"KDSUB\"
                LEFT JOIN coadet ON coa.\"KDCOA\" = coadet.\"KDCOA\"
                WHERE subcoa.\"KDSUB\" != '650' AND subcoa.\"TIPE\" >= 4
                GROUP BY coadet.\"TH\", coadet.\"BL\", subcoa.\"TIPE\"

                UNION ALL

                SELECT 
                    periode.\"TH\" AS tahun,
                    periode.\"BL\" AS bulan,
                    6 AS tipe,
                    (sawal - sakhir) AS nilai
                FROM periode
            )

            SELECT 
                rd.tahun,
                rd.bulan,
                COALESCE(SUM(CASE WHEN rd.tipe = 4 THEN rd.nilai END), 0) AS pendapatan,
                COALESCE(SUM(CASE WHEN rd.tipe = 6 THEN rd.nilai END), 0) AS hpp,
                COALESCE(SUM(CASE WHEN rd.tipe = 5 THEN rd.nilai END), 0) AS biaya,
                (COALESCE(SUM(CASE WHEN rd.tipe = 4 THEN rd.nilai END), 0) 
                 - COALESCE(SUM(CASE WHEN rd.tipe = 6 THEN rd.nilai END), 0) 
                 - COALESCE(SUM(CASE WHEN rd.tipe = 5 THEN rd.nilai END), 0)) AS lr,
                p.\"POSTING\" AS posting
            FROM RekeningData rd
            JOIN periode p ON rd.tahun = p.\"TH\" AND rd.bulan = p.\"BL\" AND p.\"POSTING\"=1
            GROUP BY rd.tahun, rd.bulan, p.\"POSTING\"
            ORDER BY rd.tahun DESC, rd.bulan DESC;
        ";

        return $this->db->query($sql)->getResultArray();
    }

    public function getLaporanRekening($tahun, $bulan)
    {
        $sql = "
            WITH RekeningData AS (
                SELECT 
                    coa.\"KDCOA\", 
                    coa.\"NMCOA\", 
                    coa.\"kdparent\", 
                    coa.\"KDSUB\",
                    subcoa.\"NMSUB\" AS kategori,
                    subcoa.\"TIPE\" AS tipe,
                    COALESCE(
                        SUM(
                            CASE 
                                WHEN subcoa.\"TIPE\" = 4 THEN (coadet.\"MKREDIT\" - coadet.\"MDEBET\")
                                WHEN subcoa.\"TIPE\" = 5 THEN (coadet.\"MDEBET\" - coadet.\"MKREDIT\")
                                WHEN subcoa.\"TIPE\" IN (2, 3) THEN (coadet.\"SKREDIT\" + coadet.\"MKREDIT\") - (coadet.\"SDEBET\" + coadet.\"MDEBET\")
                                ELSE (coadet.\"SDEBET\" - coadet.\"SKREDIT\") + (coadet.\"MDEBET\" - coadet.\"MKREDIT\")
                            END
                        ), 
                        0
                    ) AS nilai,
                    coa.root AS level
                FROM coa
                LEFT JOIN subcoa ON coa.\"KDSUB\" = subcoa.\"KDSUB\"
                LEFT JOIN coadet ON coa.\"KDCOA\" = coadet.\"KDCOA\" 
                    AND coadet.\"TH\" = ? 
                    AND coadet.\"BL\" = ?
                WHERE subcoa.\"KDSUB\" != '650' AND subcoa.\"TIPE\" >= '4'
                GROUP BY coa.\"KDCOA\", coa.\"NMCOA\", coa.\"kdparent\", coa.\"KDSUB\", subcoa.\"NMSUB\", subcoa.\"TIPE\", coa.root
            )

            SELECT 
                tipe,
                \"KDSUB\" AS \"kdsub\",
                kategori AS \"rekening\",
                level AS \"Level\",
                \"KDCOA\" AS \"kode_akun\",
                COALESCE(\"kdparent\", '-') AS \"parent_akun\",
                \"NMCOA\" AS \"nama_akun\",
                nilai
            FROM RekeningData

            UNION ALL

            SELECT 
                6 AS tipe,
                '650' AS \"kdsub\",
                'Persediaan' AS \"rekening\",
                2 AS \"Level\",
                '5555' AS \"kode_akun\",
                '' AS \"parent_akun\",
                'Persediaan Awal' AS \"nama_akun\",
                sawal AS nilai
            FROM periode
            WHERE \"TH\" = ? AND \"BL\" = ?

            UNION ALL

            SELECT 
                6 AS tipe,
                '650' AS \"kdsub\",
                'Persediaan' AS \"rekening\",
                2 AS \"Level\",
                '6666' AS \"kode_akun\",
                '' AS \"parent_akun\",
                'Persediaan Akhir' AS \"nama_akun\",
                sakhir AS nilai
            FROM periode
            WHERE \"TH\" = ? AND \"BL\" = ?

            ORDER BY \"kdsub\", \"Level\", \"kode_akun\", \"rekening\", \"parent_akun\" NULLS FIRST;
        ";

        return $this->db->query($sql, [$tahun, $bulan, $tahun, $bulan, $tahun, $bulan])->getResultArray();
    }

    public function getNeraca($tahun = null, $bulan = null)
    {
        $sql = "
            WITH RekeningData AS ( 
                SELECT 
                    coadet.\"TH\" AS tahun,
                    coadet.\"BL\" AS bulan,
                    subcoa.\"TIPE\" AS tipe,
                    COALESCE(
                        SUM(
                            CASE 
                                WHEN subcoa.\"TIPE\" = 4 THEN (coadet.\"MKREDIT\" - coadet.\"MDEBET\")
                                WHEN subcoa.\"TIPE\" = 5 THEN (coadet.\"MDEBET\" - coadet.\"MKREDIT\")
                                WHEN subcoa.\"TIPE\" IN (2, 3) THEN (coadet.\"SKREDIT\" + coadet.\"MKREDIT\") - (coadet.\"SDEBET\" + coadet.\"MDEBET\")
                                ELSE (coadet.\"SDEBET\" - coadet.\"SKREDIT\") + (coadet.\"MDEBET\" - coadet.\"MKREDIT\")
                            END
                        ), 
                        0
                    ) AS nilai
                FROM coa
                LEFT JOIN subcoa ON coa.\"KDSUB\" = subcoa.\"KDSUB\"
                LEFT JOIN coadet ON coa.\"KDCOA\" = coadet.\"KDCOA\"
                WHERE subcoa.\"KDSUB\" != '650'
                GROUP BY coadet.\"TH\", coadet.\"BL\", subcoa.\"TIPE\"

                UNION ALL

                SELECT 
                    periode.\"TH\" AS tahun,
                    periode.\"BL\" AS bulan,
                    6 AS tipe,
                    (sawal - sakhir) AS nilai
                FROM periode
            ),

            LabaRugiAkumulasi AS (
                SELECT 
                    rd.tahun,
                    rd.bulan,
                    SUM(
                        COALESCE(SUM(CASE WHEN rd.tipe = 4 THEN rd.nilai END), 0) 
                        - COALESCE(SUM(CASE WHEN rd.tipe = 6 THEN rd.nilai END), 0) 
                        - COALESCE(SUM(CASE WHEN rd.tipe = 5 THEN rd.nilai END), 0)
                    ) OVER (PARTITION BY rd.tahun ORDER BY rd.bulan ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) 
                    AS labarugi_tahun
                FROM RekeningData rd
                GROUP BY rd.tahun, rd.bulan
            )

            SELECT 
                rd.tahun,
                rd.bulan,
                COALESCE(SUM(CASE WHEN rd.tipe = 1 THEN rd.nilai END), 0) AS aset,
                COALESCE(SUM(CASE WHEN rd.tipe = 2 THEN rd.nilai END), 0) AS liabilitas,
                MAX(lr.labarugi_tahun) AS labarugi_tahun,
                (COALESCE(SUM(CASE WHEN rd.tipe = 3 THEN rd.nilai END), 0) + MAX(lr.labarugi_tahun)) AS ekuitas,
                (COALESCE(SUM(CASE WHEN rd.tipe = 1 THEN rd.nilai END), 0) 
                 - COALESCE(SUM(CASE WHEN rd.tipe = 2 THEN rd.nilai END), 0) 
                 - (COALESCE(SUM(CASE WHEN rd.tipe = 3 THEN rd.nilai END), 0) + MAX(lr.labarugi_tahun))) AS balance,
                p.\"POSTING\" AS posting
            FROM RekeningData rd
            JOIN LabaRugiAkumulasi lr 
                ON rd.tahun = lr.tahun 
                AND rd.bulan = lr.bulan
            JOIN periode p 
                ON rd.tahun = p.\"TH\" 
                AND rd.bulan = p.\"BL\" 
                AND p.\"POSTING\" = 1
        ";

        $conditions = [];
        $params = [];

        if (!is_null($tahun)) {
            $conditions[] = "rd.tahun = :tahun:";
            $params['tahun'] = $tahun;
        }
        if (!is_null($bulan)) {
            $conditions[] = "rd.bulan = :bulan:";
            $params['bulan'] = $bulan;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " GROUP BY rd.tahun, rd.bulan, p.\"POSTING\" ORDER BY rd.tahun DESC, rd.bulan DESC;";

        // Jalankan query dengan binding parameter
        return $this->db->query($sql, $params)->getResultArray();
    }
}
