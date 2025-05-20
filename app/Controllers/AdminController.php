<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersModel;

class AdminController extends BaseController
{
    public function __construct()
    {
        helper(['my_helper']);
        $this->login = 'iya';
        $cek_auth = detailUser();
        if(empty($cek_auth)){
            $this->login = 'tidak';
        }

        $this->users_id = session()->get('user_id');
        $this->userMod = new UsersModel;
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
                    if($aksiUpdate == 'yes'):
                    $aksiTable .= "<a class='btn btn-xs btn-primary' href='javascript:void(0)' title='Edit' onclick='edit_data(".json_encode($edtVal).")'><i class='fa fa-pencil text-white'></i></a> ";
                    endif;
                    if($aksiDelete == 'yes'):
                    $aksiTable .= "<a class='btn btn-xs btn-danger' href='javascript:void(0)' title='Delete' onclick='delete_data(".$list->id.")'><i class='fa fa-trash text-white'></i></a>";
                    endif;
                    $row = [];
                    $row[]  = $no++;
                    $row[]  = $list->user_nama;
                    $row[]  = $list->user_email;
                    $row[]  = $list->user_no_telp;
                    $row[]  = $list->user_username;
                    $row[]  = $list->user_role;
                    $row[]  = $akt;
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
        $data['aksiCreate'] = 'yes';
        $data['title'] = 'User Login | '.$this->CompName;
        $data['judul'] = 'User Login';
        return view('user/userLoginView', $data);
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
}
