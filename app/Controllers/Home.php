<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersModel;

class Home extends BaseController
{
    public function __construct()
    {
        helper(['my_helper']);
    }

    public function index()
    {
        if (!session()->get('isGlLoggedIn')) {
            $data['title'] = 'Login | GL';
            return view('welcome_message', $data);
        }

        // Redirect ke dashboard jika sudah login
        return redirect()->to('cms/dashboard');
    }

    public function loginSSO()
    {
        $urlSSO = getenv('BASE_URL_SSO');
        $cekServer = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http")."://".$_SERVER['HTTP_HOST'];
        if($cekServer){
            $urlApp = $cekServer;
        }else{
            $urlApp = "https://gl.sadarjaya.com";
        }

        $url_redirect = "{$urlSSO}/login?redirect_url={$urlApp}/login/with-sso";
        return redirect()->to($url_redirect);
    }

    public function loginWithSSO()
    {
        try {
            $token = $this->request->getVar('token');

            $url = getenv('BASE_URL_SSO').'/api/v1/login-sso';
            $header = [
                "Authorization" => "Bearer ".$token
            ];

            $userData = reqApi($url,'GET',NULL,$header);

            if($userData && $userData['status'] == 200){
                $users = new UsersModel();
                $email = $userData['data']['email'];
                $cekUser = $users->where('user_email',$email)->first();
                if($cekUser){
                    $dbConfig = [
                        'DSN'      => '',
                        'hostname' => getenv('database.default.hostname'),
                        'username' => getenv('database.default.username'),
                        'password' => getenv('database.default.password'),
                        'database' => getenv('database.default.database'),
                        'DBDriver' => getenv('database.default.DBDriver'),
                        'DBPrefix' => '',
                        'pConnect' => false,
                        'DBDebug'  => (ENVIRONMENT !== 'production'),
                        'charset'  => 'utf8',
                        'DBCollat' => 'utf8_general_ci',
                        'swapPre'  => '',
                        'encrypt'  => false,
                        'compress' => false,
                        'strictOn' => false,
                        'failover' => [],
                        'port'     => getenv('database.default.port'),
                    ];
                    // Simpan konfigurasi database ke session
                    session()->set('db_config', $dbConfig);

                    $last_login = date('Y-m-d H:i:s');
                    $users->update($cekUser->id, [
                        "user_last_login"    => $last_login
                    ]);
                    session()->set([
                        'user_id'            => $cekUser->id,
                        'isGlLoggedIn'    => TRUE
                    ]);
                    $url_redirect = base_url('cms/dashboard');
                    return redirect()->to($url_redirect);
                }else{
                    return redirect()->to('/')->with('error', 'Failed to login with SSO: Email tidak terdaftar di aplikasi ini');
                }
            }else{
                return redirect()->to('/')->with('error', 'Failed to login with SSO');
            }

        } catch (Exception $e) {
            return redirect()->to('/')->with('error', 'Failed to login with SSO: ' . $e->getMessage());
        }
    }

    function logout()
    {
        session()->destroy();
        return redirect()->to(base_url());
    }
}
