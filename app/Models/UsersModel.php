<?php

namespace App\Models;

use CodeIgniter\Model;

class UsersModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'user_gl';
    protected $tableIns         = 'user_gl';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_role',
        'user_username',
        'user_password',
        'user_nama',
        'user_email',
        'user_no_telp',
        'user_kode_reset',
        'user_reset_expired',
        'user_active',
        'user_bahasa',
        'user_last_login',
        'user_fcm',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

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

    protected $column_order = [null,'user_nama', 'user_email', 'user_no_telp', 'user_username', 'user_role', 'user_active'];
    protected $column_search = ['user_nama', 'user_email', 'user_no_telp', 'user_username', 'user_role', 'user_active'];
    protected $order = ['id' => 'DESC'];

    public function __construct()
    {
        parent::__construct();
        $this->db = db_connect();
        $this->dt = $this->db->table($this->table);
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

        if (!empty($orderColumn)) {
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
}
