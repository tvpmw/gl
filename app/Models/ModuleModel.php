<?php

namespace App\Models;

use CodeIgniter\Model;

class ModuleModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'modules_gl';
    protected $tableIns         = 'modules_gl';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'slug',
        'description', 
        'parent_id',
        'sort_order',
        'is_active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
    protected $deletedFieldUser = 'deleted_by';

    // Validation
    protected $validationRules      = [
        'name' => 'required|min_length[3]',
        'slug' => 'required|is_unique[modules_gl.slug,id,{id}]',
    ];
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

    protected $column_order = [null, 'name', 'slug', 'description', 'parent_id', 'order', 'is_active'];
    protected $column_search = ['name', 'slug', 'description'];
    protected $order = ['order' => 'ASC'];

    public function __construct()
    {
        parent::__construct();
        $this->db = db_connect();
        $this->dt = $this->db->table($this->table);
    }

    public function getModuleWithAccess($userId) 
    {
        return $this->select('modules_gl.*, user_module_access_gl.*')
                    ->join('user_module_access_gl', 'modules_gl.id = user_module_access_gl.module_id', 'left')
                    ->where('user_module_access_gl.user_id', $userId)
                    ->where('modules_gl.is_active', true) // Change from 1 to true
                    ->where('modules_gl.deleted_at', null)
                    ->where('user_module_access_gl.deleted_at', null)
                    ->orderBy('modules_gl.sort_order', 'ASC')
                    ->findAll();
    }

    public function setUserAccess($userId, $moduleId, $permissions) 
    {
        $data = [
            'user_id' => $userId,
            'module_id' => $moduleId,
            'can_view' => isset($permissions['view']) ? $permissions['view'] : false,
            'can_create' => isset($permissions['create']) ? $permissions['create'] : false,
            'can_edit' => isset($permissions['edit']) ? $permissions['edit'] : false,
            'can_delete' => isset($permissions['delete']) ? $permissions['delete'] : false,
            'created_by' => session()->get('user_id'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->table('user_module_access_gl')->insert($data);
    }

    public function updateUserAccess($userId, $moduleId, $permissions) 
    {
        $data = [
            'can_view' => isset($permissions['view']) ? $permissions['view'] : false,
            'can_create' => isset($permissions['create']) ? $permissions['create'] : false,
            'can_edit' => isset($permissions['edit']) ? $permissions['edit'] : false,
            'can_delete' => isset($permissions['delete']) ? $permissions['delete'] : false,
            'updated_by' => session()->get('user_id'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->table('user_module_access_gl')
                    ->where('user_id', $userId)
                    ->where('module_id', $moduleId)
                    ->update($data);
    }

    public function deleteUserAccess($userId, $moduleId) 
    {
        if ($this->useSoftDeletes) {
            return $this->db->table('user_module_access_gl')
                        ->where('user_id', $userId)
                        ->where('module_id', $moduleId)
                        ->update([
                            'deleted_at' => date('Y-m-d H:i:s'),
                            'deleted_by' => session()->get('user_id')
                        ]);
        }
        
        return $this->db->table('user_module_access_gl')
                    ->where('user_id', $userId)
                    ->where('module_id', $moduleId)
                    ->delete();
    }

    public function getUserModuleAccess($userId) 
    {
        $modules = $this->select('modules_gl.*, user_module_access_gl.can_view, 
                                user_module_access_gl.can_create, 
                                user_module_access_gl.can_edit, 
                                user_module_access_gl.can_delete')
                       ->join('user_module_access_gl', 
                             'modules_gl.id = user_module_access_gl.module_id', 'left')
                       ->where('modules_gl.is_active', true) // Change from 1 to true
                       ->where('modules_gl.deleted_at', null)
                       ->orderBy('modules_gl.sort_order', 'ASC')
                       ->findAll();

        // Set default values if no access exists
        foreach ($modules as $module) {
            $module->can_view = (bool)$module->can_view;
            $module->can_create = (bool)$module->can_create;
            $module->can_edit = (bool)$module->can_edit;
            $module->can_delete = (bool)$module->can_delete;
        }

        return $modules;
    }
    
    private function getDatatablesQuery($searchValue='', $orderColumn=0, $orderDir='asc')
    {
        if ($this->useSoftDeletes === true) {
            $this->dt->where($this->table . '.' . $this->deletedField, null);
        }
        
        if ($searchValue) {
            $this->dt->groupStart();
            foreach ($this->column_search as $i => $item) {
                if ($i === 0) {
                    $this->dt->like($item, $searchValue);
                } else {
                    $this->dt->orLike($item, $searchValue);
                }
            }
            $this->dt->groupEnd();
        }

        if (!empty($orderColumn)) {
            $this->dt->orderBy($this->column_order[$orderColumn], $orderDir);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->dt->orderBy(key($order), $order[key($order)]);
        }
    }

    public function getDatatables($length=10, $start=0, $searchValue='', $orderColumn=0, $orderDir='asc')
    {
        $this->getDatatablesQuery($searchValue, $orderColumn, $orderDir);
        if ($length != -1)
            $this->dt->limit($length, $start);
    
        $query = $this->dt->get();
        $result = $query->getResult();
        
        // Add access button to each row
        foreach ($result as $row) {
            $row->access_button = '<button class="btn btn-info btn-xs" onclick="editAccess('.$row->id.')" title="Edit Access"><i class="fa fa-key"></i></button>';
        }
        
        return $result;
    }

    public function countFiltered($searchValue='', $orderColumn=0, $orderDir='asc')
    {
        $this->getDatatablesQuery($searchValue, $orderColumn, $orderDir);
        return $this->dt->countAllResults();
    }

    public function countAll(bool $reset = true, bool $test = false)
    {
        $q = $this->builder($this->table);
        if ($this->useSoftDeletes === true) {
            $q->where($this->table . '.' . $this->deletedField, null);
        }
        return $q->testMode($test)->countAllResults($reset);
    }

    public function insert_data($data)
    {
        $this->db->table($this->tableIns)->insert($data);
        return $this->db->insertID();
    }

    public function update_data($id, $data)
    {
        return $this->db->table($this->tableIns)->where($this->primaryKey, $id)->update($data);
    }

    public function delete_data($id, $users_id=null)
    {
        if ($this->useSoftDeletes === true) {
            return $this->db->table($this->tableIns)
                           ->where($this->primaryKey, $id)
                           ->update([
                               $this->deletedField => date('Y-m-d H:i:s'),
                               $this->deletedFieldUser => $users_id
                           ]);
        } else {
            return $this->db->table($this->tableIns)->where($this->primaryKey, $id)->delete();
        }
    }

    protected $viewOnlyModules = ['dashboard']; // Daftar slug modul yang hanya memiliki akses view

    public function getAllActiveModules()
    {
        return $this->select('modules_gl.*')
                    ->where('is_active', true)
                    ->where('deleted_at', null)
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }

    public function getAllModuleAccess($userId) 
    {
        return $this->select('modules_gl.*, modules_gl.name, modules_gl.slug,
                         user_module_access_gl.can_view, 
                         user_module_access_gl.can_create, 
                         user_module_access_gl.can_edit, 
                         user_module_access_gl.can_delete')
                ->join('user_module_access_gl', 
                      'modules_gl.id = user_module_access_gl.module_id', 'left')
                ->where('user_module_access_gl.user_id', $userId)
                ->where('modules_gl.is_active', true)
                ->where('modules_gl.deleted_at', null)
                ->orderBy('modules_gl.sort_order', 'ASC')
                ->findAll();
    }

    public function updateUserModuleAccess($userId, $moduleAccess) 
    {
        $this->db->transStart();

        // Delete existing access
        $this->db->table('user_module_access_gl')
                 ->where('user_id', $userId)
                 ->delete();

        // Insert new access
        if ($moduleAccess) {
            foreach ($moduleAccess as $moduleId => $access) {
                // Get module info
                $module = $this->find($moduleId);
                
                // Set permissions based on module type
                $isViewOnly = in_array($module->slug, $this->viewOnlyModules);
                
                $this->db->table('user_module_access_gl')->insert([
                    'user_id' => $userId,
                    'module_id' => $moduleId,
                    'can_view' => isset($access['view']) ? true : false,
                    'can_create' => $isViewOnly ? false : (isset($access['create']) ? true : false),
                    'can_edit' => $isViewOnly ? false : (isset($access['edit']) ? true : false),
                    'can_delete' => $isViewOnly ? false : (isset($access['delete']) ? true : false),
                    'created_by' => session()->get('user_id'),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    public function getMenuModules()
    {
        $modules = $this->where('parent_id', null)
                        ->where('is_active', true)
                        ->where('deleted_at', null)
                        ->orderBy('sort_order', 'ASC')
                        ->findAll();

        foreach ($modules as $module) {
            $module->children = $this->where('parent_id', $module->id)
                                    ->where('is_active', true)
                                    ->where('deleted_at', null)
                                    ->orderBy('sort_order', 'ASC')
                                    ->findAll();
        }

        return $modules;
    }
}