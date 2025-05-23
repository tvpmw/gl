<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersModel;
use App\Models\ModuleModel;

class AdminController extends BaseController
{
    protected $moduleMod;

    public function __construct()
    {
        helper(['my_helper']);
        
        $access = checkMenuAccess('cms/user');
        if (!$access['can_view']) {            
            return redirect()->to(base_url('unauthorized'));
        }
        $this->login = 'iya';
        $cek_auth = detailUser();
        if(empty($cek_auth)){
            $this->login = 'tidak';
        }

        $this->users_id = session()->get('user_id');
        $this->userMod = new UsersModel;
        $this->moduleMod = new ModuleModel();
        $this->modul = 'user management';
        $this->CompName = "CRM";
    }

    public function index()
    {
        if(detailUser()->user_role != 'superadmin'){
            $dataNav['title'] = 'User Management | '.$this->CompName;
            $dataNav['judul'] = 'User Management';
            $dataNav['modul'] = $this->modul;
            $dataNav['checkAuth'] = $this->login;
            $dataNav['reloadLogin'] = 'ya';
            return view('NoAccessView',$dataNav);
        }

        if(detailUser()->user_role == 'superadmin'){
            $data['aksiCreate'] = 'yes';
        }

        $data['title'] = 'User Management | '.$this->CompName;
        $data['judul'] = 'User Management';
        return view('user/userView', $data);
    }

    public function lists()
    {
        if ($this->request->isAJAX()) {
            if(detailUser()->user_role != 'superadmin'){
                $output = [
                    'draw'              => $this->request->getPost('draw'),
                    'recordsTotal'      => 0,
                    'recordsFiltered'   => 0,
                    'data'              => []
                ];
                return json_encode($output);
            }

            $length         = $this->request->getPost('length');
            $start          = $this->request->getPost('start');
            $searchValue    = strtolower($this->request->getPost('search')['value']);
            $orderColumn    = !empty($this->request->getPost('order'))?$this->request->getPost('order')['0']['column']:'';
            $orderDir       = !empty($this->request->getPost('order'))?$this->request->getPost('order')['0']['dir']:'';
            $lists = $this->userMod->getDatatables($length,$start,$searchValue,$orderColumn,$orderDir);

            $no = $this->request->getPost('start')+1;
            if($lists){
                $aksiUpdate = 'yes';
                $aksiDelete = 'yes';
                $data = [];
                foreach ($lists as $list) {
                    $edtVal = [
                        'id'        => $list->id,
                        'nama'      => $list->user_nama,
                        'email'     => $list->user_email,
                        'no_hp'     => $list->user_no_telp,
                        'username'  => $list->user_username,
                        'role'      => $list->user_role,
                        'status'    => $list->user_active,
                    ];
                    $akt = '<i class="icon fa fa-close" style="color:red"></i>';
                    if($list->user_active == 1){
                        $akt = '<i class="icon fa fa-check" style="color:green"></i>';
                    }
                    $aksiTable = '';
                    $modulTable = '';
                    $userManagement = checkMenuAccess('cms/user');                    
                    if($aksiUpdate == 'yes'):
                        if($userManagement['can_edit']):                        
                        $aksiTable .= "<a class='btn btn-xs btn-primary' href='javascript:void(0)' title='Edit' onclick='edit_data(".json_encode($edtVal).")'><i class='fa fa-pencil text-white'></i></a> ";
                        endif;
                    endif;
                    if($aksiDelete == 'yes'):
                        if($userManagement['can_delete']):                        
                        $aksiTable .= "<a class='btn btn-xs btn-danger' href='javascript:void(0)' title='Delete' onclick='delete_data(".$list->id.")'><i class='fa fa-trash text-white'></i></a> ";
                        endif;
                    endif;
                    // Add module access button
                    if($userManagement['can_edit'] && $userManagement['can_create']):                    
                    $modulTable .= "<a class='btn btn-xs btn-info' href='javascript:void(0)' title='Module Access' onclick='edit_module_access(".$list->id.")'><i class='fa fa-key text-white'></i></a>";
                    endif;
                    
                    $row = [];
                    $row[]  = $no++;
                    $row[]  = $list->user_nama;
                    $row[]  = $list->user_email;
                    $row[]  = $list->user_no_telp;
                    $row[]  = $list->user_username;
                    $row[]  = $list->user_role;
                    $row[]  = $akt;
                    $row[]  = $modulTable;
                    $row[]  = $aksiTable;

                    $data[] = $row;
                }

                $output = [
                    'draw'              => $this->request->getPost('draw'),
                    'recordsTotal'      => $this->userMod->countAll(),
                    'recordsFiltered'   => $this->userMod->countFiltered($searchValue,$orderColumn,$orderDir),
                    'data'              => $data
                ];
            }else{
                $output = [
                    'draw'              => $this->request->getPost('draw'),
                    'recordsTotal'      => 0,
                    'recordsFiltered'   => 0,
                    'data'              => []
                ];
            }

            return json_encode($output);
        }
    }

    public function save()
    {
        if ($this->request->isAJAX()) {
            if(detailUser()->user_role != 'superadmin'){
                return json_encode(array('status' => false, 'msg' => 'Anda tidak memiliki akses untuk modul ini'));
            }
            $rules = [
                "nama" => "required",
                "email" => "required|valid_email",
                "no_hp" => "required|numeric",
                "username" => "required",
                "role" => "required",
                "status" => "required|numeric",
            ];

            $messages = [
                "nama" => [
                    "required" => "nama required",
                ],
                "email" => [
                    "required" => "email required",
                    "valid_email" => "email not valid",
                ],
                "no_hp" => [
                    "required" => "no hp required",
                    "numeric" => "no hp numeric",
                ],
                "username" => [
                    "required" => "username required",
                ],
                "role" => [
                    "required" => "role required",
                ],
                "status" => [
                    "required" => "status required",
                    "numeric" => "status numeric"
                ],
            ];

            if(empty($this->request->getPost('data_id')) || !empty($this->request->getPost('password'))){
                $rules['password'] = "required";
                $rules['password2'] = 'matches[password]';
                $messages['password'] = ["required" => "password required"];
                $messages['password2'] = ["matches" => "passwords are not the same"];
            }

            if (!$this->validate($rules, $messages)) {
                $err = $this->validator->getErrors();
                $msgNotif = $err;
                if(gettype($err) == 'array'){
                    $rules_array = ['nama','email','no_hp','username','role','password','password2','status'];
                    $msgNotif = '<ul>';
                    foreach ($rules_array as $key => $value) {
                        if(!empty($err[$value])){
                            $msgNotif .= '<li>'.$err[$value].'</li>';
                        }
                    }
                    $msgNotif .= '</ul>';
                }
                return json_encode(array('status' => false, 'msg' => $msgNotif));
            }

            if(empty($this->request->getPost('data_id'))){
                $cek_email = $this->userMod->where('user_email',$this->request->getVar('email'))->first();
                $cek_user = $this->userMod->where('user_username',$this->request->getVar('username'))->first();
                $cek_hp = $this->userMod->where('user_username',$this->request->getVar('user_no_telp'))->first();
            }else{
                $id_usr = $this->request->getPost('data_id');
                $cek_email = $this->userMod->where(['user_email'=>$this->request->getVar('email'),'id !='=>$id_usr])->first();
                $cek_user = $this->userMod->where(['user_username'=>$this->request->getVar('username'),'id !='=>$id_usr])->first();
                $cek_hp = $this->userMod->where(['user_username'=>$this->request->getVar('user_no_telp'),'id !='=>$id_usr])->first();
            }

            if($cek_email){
                return json_encode(array('status' => false, 'msg' => 'email already used'));
            }

            if($cek_user){
                return json_encode(array('status' => false, 'msg' => 'username already used'));
            }

            if($cek_hp){
                return json_encode(array('status' => false, 'msg' => 'no hp already used'));
            }

            $data = [
                'user_role'         => $this->request->getVar('role'),
                'user_username'     => str_replace("'","",$this->request->getVar('username')),
                'user_nama'         => str_replace("'","",$this->request->getVar('nama')),
                'user_email'        => $this->request->getVar('email'),
                'user_no_telp'      => $this->request->getVar('no_hp'),
                'user_active'       => $this->request->getVar('status'),
            ];
            if(empty($this->request->getPost('data_id'))){
                $data['user_password'] = password_hash($this->request->getVar('password'), PASSWORD_DEFAULT);
                $data['created_by'] = $this->users_id;
                $prosess = $this->userMod->insert_data($data);
                $id_usr = $prosess;
                $aksi = 'tambah';
            }else{
                if(!empty($this->request->getPost('password'))){
                    $data['user_password'] = password_hash($this->request->getVar('password'), PASSWORD_DEFAULT);
                }
                $data['updated_by'] = $this->users_id;
                $data['updated_at'] = date('Y-m-d H:i:s');
                $prosess = $this->userMod->update($this->request->getPost('data_id'), $data);
                $id_usr = $this->request->getPost('data_id');
                $aksi = 'edit';
            }

            if($prosess){
                return json_encode(array('status' => true, 'msg' => 'Data berhasil disimpan', 'user'=>$id_usr,'aksi'=>$aksi));
            }else{
                return json_encode(array('status' => false, 'msg' => 'Data gagal disimpan'));
            }
        }
    }

    public function delete()
    {
        if ($this->request->isAJAX()) {
            if(detailUser()->user_role != 'superadmin'){
                return json_encode(array('status' => false, 'msg' => 'Anda tidak memiliki akses untuk modul ini'));
            }
            $id = $this->request->getPost('id');
            if(!empty($id)){
                $prosess = $this->userMod->delete_data($id,$this->users_id);
                if($prosess){
                    return json_encode(array('status' => true, 'msg' => 'Data berhasil dihapus'));
                }else{
                    return json_encode(array('status' => false, 'msg' => 'Data gagal dihapus'));
                }
            }
        }
    }

    public function userLogin()
    {
        $userLogin = checkMenuAccess('cms/user');
        if($userLogin['can_view'] && $userLogin['can_create'] && $userLogin['can_edit'] && $userLogin['can_delete'] && detailUser()->user_role == 'superadmin'){        
        $data['aksiCreate'] = 'yes';
        $data['title'] = 'User Login | '.$this->CompName;
        $data['judul'] = 'User Login';
        return view('user/userLoginView', $data);
        }else{
            return redirect()->to(base_url('unauthorized'));
        }
    }

    public function userAktif()
    {
        $sessionModel = new \App\Models\SessionModel();
        $sessions = $sessionModel->findAll();

        $usersAktif = [];

        foreach ($sessions as $session) {
            $rawData = $session['data'];

            if (strpos($rawData, '\\x') === 0) {
                $rawData = hex2bin(substr($rawData, 2));
            }

            $sessionData = ci_session_decode($rawData); 

            if (is_array($sessionData) && isset($sessionData['user_id'])) {
                $usersAktif[] = [
                    'user_id'     => $sessionData['user_id'],
                    'ip'          => $session['ip_address'],
                    'last_active' => date('Y-m-d H:i:s', strtotime($session['timestamp'])),
                ];
            }
        }

        return $this->response->setJSON($usersAktif);
    }

    public function getOnlineUsers()
    {
        $sessionModel = new \App\Models\SessionModel();
        $sessions = $sessionModel->orderBy('timestamp','ASC')->findAll();

        $onlineUsers = [];

        foreach ($sessions as $session) {
            if (!str_starts_with($session['id'], 'gl:')) {
                continue;
            }

            $rawData = $session['data'];
            if (strpos($rawData, '\\x') === 0) {
                $rawData = hex2bin(substr($rawData, 2));
            }

            $data = ci_session_decode($rawData);
            if (isset($data['user_id'])) {
                $key = $data['user_id'] . '_' . $session['ip_address'];
                $originalId = $data['user_id'];
                $userName = detailUserById($data['user_id'])->user_nama ?? $data['user_id'];
                
                $onlineUsers[$key] = [
                    'session_id' => $session['id'],
                    'user_id'    => $originalId,
                    'user_name'  => $userName,
                    'ip'         => $session['ip_address'],
                    'last_active'=> tanggal_indo(date('Y-m-d H:i:s', strtotime($session['timestamp'])),true),
                ];
            }
        }

        $onlineUsers = array_values($onlineUsers);

        return $this->response->setJSON($onlineUsers);
    }

    public function forceLogout()
    {
        $userId = $this->request->getPost('user_id');
        $userName = detailUserById($userId)->user_nama ?? $userId;
        $sessionModel = new \App\Models\SessionModel();
        $sessions = $sessionModel->findAll();

        $count = 0;

        foreach ($sessions as $session) {
            if (!str_starts_with($session['id'], 'gl:')) {
                continue;
            }

            $rawData = $session['data'];
            if (strpos($rawData, '\\x') === 0) {
                $rawData = hex2bin(substr($rawData, 2));
            }

            $decodedData = ci_session_decode($rawData);

            if (is_array($decodedData) && isset($decodedData['user_id']) && $decodedData['user_id'] == $userId) {
                if (isset($decodedData['admin_id'])) {
                    unset($decodedData['user_id'], $decodedData['isAdminLoggedIn']);
                    $newData = ci_session_encode($decodedData);
                    $sessionModel->update($session['id'], ['data' => $newData]);
                } else {
                    $sessionModel->delete($session['id']);
                }
                $count++;
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => "$userName dengan User ID $userId telah dipaksa logout dari $count sesi GL.",
        ]);
    }

    public function moduleAccess($userId)
    {
        if(detailUser()->user_role != 'superadmin'){
            return redirect()->back()->with('error', 'Access Denied');
        }

        $data['title'] = 'Module Access | '.$this->CompName;
        $data['judul'] = 'Module Access';
        $data['user'] = $this->userMod->find($userId);
        return view('user/moduleAccessView', $data);
    }

    public function getModuleAccess()
    {
        if ($this->request->isAJAX()) {
            $userId = $this->request->getPost('user_id');
            
            // Get all active modules
            $modules = $this->moduleMod->getAllActiveModules();
            
            // Get user's current module access
            $userAccess = $this->moduleMod->getAllModuleAccess($userId);
            
            // Create access map for quick lookup
            $accessMap = [];
            foreach ($userAccess as $access) {
                $accessMap[$access->module_id] = $access; // Changed from $access->id to $access->module_id
            }
            
            // Prepare module data with access information
            $moduleData = [];
            foreach ($modules as $module) {
                $access = isset($accessMap[$module->id]) ? $accessMap[$module->id] : null;
                
                // Check if module is view-only
                $isViewOnly = in_array($module->slug, $this->moduleMod->viewOnlyModules);
                
                $moduleData[] = [
                    'id' => $module->id,
                    'name' => $module->name,
                    'slug' => $module->slug,
                    'can_view' => $access ? ($access->can_view === 't' || $access->can_view === true || $access->can_view === 1) : false,
                    'can_create' => $isViewOnly ? false : ($access ? ($access->can_create === 't' || $access->can_create === true || $access->can_create === 1) : false),
                    'can_edit' => $isViewOnly ? false : ($access ? ($access->can_edit === 't' || $access->can_edit === true || $access->can_edit === 1) : false),
                    'can_delete' => $isViewOnly ? false : ($access ? ($access->can_delete === 't' || $access->can_delete === true || $access->can_delete === 1) : false)
                ];
            }
            
            // Debug log
            log_message('debug', 'Module Access Data: ' . json_encode($moduleData));
            
            return $this->response->setJSON([
                'status' => true,
                'data' => $moduleData
            ]);
        }
        return $this->response->setJSON([
            'status' => false,
            'msg' => 'Invalid request'
        ]);
    }

    public function saveModuleAccess()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => false, 
                'msg' => 'Invalid request'
            ]);
        }

        if (detailUser()->user_role != 'superadmin') {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Access Denied'
            ]);
        }

        try {
            $userId = $this->request->getPost('user_id');
            $moduleAccess = json_decode($this->request->getPost('module_access'), true);

            if (!$userId || !is_array($moduleAccess)) {
                return $this->response->setJSON([
                    'status' => false,
                    'msg' => 'Invalid input data'
                ]);
            }

            $db = db_connect();
            $db->transBegin();

            // Delete existing access
            $db->table('user_module_access_gl')
            ->where('user_id', $userId)
            ->delete();

            // Prepare batch data for insertion
            $batchData = [];
            foreach ($moduleAccess as $access) {
                if (!isset($access['module_id'])) {
                    continue;
                }
                
                $batchData[] = [
                    'user_id' => $userId,
                    'module_id' => $access['module_id'],
                    'can_view' => $access['view'] ? 't' : 'f',
                    'can_create' => $access['create'] ? 't' : 'f',
                    'can_edit' => $access['edit'] ? 't' : 'f',
                    'can_delete' => $access['delete'] ? 't' : 'f',
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => session()->get('user_id')
                ];
            }

            // Insert new access rights if we have data
            if (!empty($batchData)) {
                $inserted = $db->table('user_module_access_gl')->insertBatch($batchData);
                
                if ($inserted === false) {
                    $db->transRollback();
                    return $this->response->setJSON([
                        'status' => false,
                        'msg' => 'Failed to insert module access data'
                    ]);
                }
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON([
                    'status' => false,
                    'msg' => 'Transaction failed'
                ]);
            }

            $db->transCommit();
            return $this->response->setJSON([
                'status' => true,
                'msg' => 'Module access updated successfully'
            ]);

        } catch (\Exception $e) {
            if (isset($db) && $db->transStatus() === false) {
                $db->transRollback();
            }
            
            log_message('error', 'Module access update error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Failed to update module access: ' . $e->getMessage()
            ]);
        }
    }
}
