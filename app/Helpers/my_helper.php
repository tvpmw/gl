<?php
use App\Models\SettingModel;
use App\Models\UsersModel;
use App\Models\NotificationModel;
use Google\Client;

if ( ! function_exists('pr')) {
  function pr($array, $exit = FALSE) {
    if($exit) {
      echo "<!DOCTYPE html>\n<pre>\n",print_r($array,1),'</pre>';exit;
    } else {
      echo "<!DOCTYPE html>\n<pre>\n",print_r($array,1),'</pre>';
    }
  }
}

if (!function_exists('ci_session_decode')) {
  function ci_session_decode(string $sessionData): array
  {
      $return_data = [];
      $offset = 0;

      while ($offset < strlen($sessionData)) {
          if (!strstr(substr($sessionData, $offset), "|")) {
              break;
          }

          $pos = strpos($sessionData, "|", $offset);
          $num = $pos - $offset;
          $varname = substr($sessionData, $offset, $num);
          $offset += $num + 1;

          $data = @unserialize(substr($sessionData, $offset));
          if ($data !== false || $data === false && str_contains(substr($sessionData, $offset), 'b:0;')) {
              $return_data[$varname] = $data;
          }

          $serialized = serialize($data);
          $offset += strlen($serialized);
      }

      return $return_data;
  }
}

if (!function_exists('ci_session_encode')) {
    function ci_session_encode(array $data): string
    {
        $encoded = '';
        foreach ($data as $key => $value) {
            $encoded .= $key . '|' . serialize($value);
        }
        return $encoded;
    }
}

if ( ! function_exists('getAccessToken')) {
  function getAccessToken($serviceAccountPath) {
     $client = new Client();
     $client->setAuthConfig($serviceAccountPath);
     $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
     $client->useApplicationDefaultCredentials();
     $token = $client->fetchAccessTokenWithAssertion();
     // echo $token['access_token'];die();
     return $token['access_token'];
  }
}

if ( ! function_exists('reqApi')) {
  function reqApi($url, $method='GET', $data = NULL, $header = NULL, $multipart = FALSE) {

    $header_static = array(
      'Content-Type: application/json'
    );

    if($multipart) {
      unset($header_static[4]);
    }

    $header_array = array();
    foreach($header_static as $item) {
      $header_array[] = "$item";
    }

    if($header !== NULL && !empty($header)) {
      foreach($header as $key => $item) {
        $header_array[] = "$key: $item";
      }
    }

    switch ($method) {
      case 'GET':
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER =>  $header_array,
            CURLOPT_CUSTOMREQUEST => "GET"
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        if($response) {
          return json_decode($response, TRUE);
        } else {
          echo $err;
        }
        break;

      case 'POST':
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER =>  $header_array,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS     => $multipart ? $data : json_encode($data)
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        if($response) {
          return json_decode($response, TRUE);
        } else {
          echo $err;
        }
        break;
      
      default:
        return json_encode(array('code' => '405', 'message' => 'Method Not Allowed'));
        break;
    }
  }
}

if ( ! function_exists('sendMessage')) {
  function sendMessage($message, $param) {
    $serviceAccountPath = FCPATH.'hr-sjm-firebase-adminsdk-70it8-1d1e6b89e1.json';
    $projectId = 'hr-sjm';
    $accessToken = getAccessToken($serviceAccountPath);

    $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';
    $headers = [
     'Authorization: Bearer ' . $accessToken,
     'Content-Type: application/json',
     ];

    $dataString = json_encode(['message' => $message]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
    $response = curl_exec($ch);
    $err = curl_error($ch);   
    curl_close($ch);

    if ($err) {
      $resp = [
        'req' => $dataString,
        'res' => $err,
      ];
    }else{
      $resp = [
        'req' => json_decode($dataString),
        'res' => json_decode($response),
      ];
    }
    $dataLog = [
      'id_pegawai' => !empty($param['id_pegawai'])?$param['id_pegawai']:0,
      'judul' => !empty($param['notif_title'])?$param['notif_title']:null,
      'pesan' => !empty($param['notif_message'])?$param['notif_message']:null,
      'jenis' => !empty($param['notif_jenis'])?$param['notif_jenis']:null,
      'jenis_id' => !empty($param['notif_jenis_id'])?$param['notif_jenis_id']:null,
      'log_req' => $dataString,
      'log_res' => !empty($response)?$response:$err,
    ];
    model('App\Models\LogNotif')->insert($dataLog);
    return $resp;
  }
}

if ( ! function_exists('sendNotif')) {
  function sendNotif($param=array()) {
    $payload = [
      "title"             => $param['notif_title'],
      "message"           => $param['notif_message'],
      "created_at"        => date('Y-m-d H:i:s'),
      "priority"          => "high",
    ];

    if(!empty($param['notif_jenis'])){
      $payload['notif_jenis'] = $param['notif_jenis'];
    }

    if(!empty($param['notif_jenis_id'])){
      $payload['notif_jenis_id'] = (string)$param['notif_jenis_id'];
    }

    // Example message payload
    $message = [
     'topic' => 'global',
     'notification' => [
       'title' => $param['notif_title'],
       'body' => $param['notif_message'],
      ],
      // 'android' => [
      //   'notification' => [
      //      'click_action' => (!empty($param['click_action'])) ? $param['click_action'] : "android.intent.action.MAIN",
      //   ]
      // ],
      'data' => $payload
    ];
    $response = sendMessage($message, $param);
    return $response;
  }
}

if ( ! function_exists('sendNotif')) {
  function sendNotif_Old($param=array()) {
    $authUser = new PegawaiLoginModel;
    $firebaseToken = [];
    $user_token = $authUser->where('fcm_token !=',null)->findAll();
    foreach ($user_token as $key) {
      array_push($firebaseToken ,$key->fcm_token);
    }

    $SERVER_API_KEY = getenv('FIREBASE_SERVER_KEY');

    $payload = [
      "title"             => $param['notif_title'],
      "message"           => $param['notif_message'],
      "created_at"        => date('Y-m-d H:i:s'),
      "content_available" => true,
      "priority"          => "high",
    ];

    if(!empty($param['notif_jenis'])){
      $payload['notif_jenis'] = $param['notif_jenis'];
    }

    if(!empty($param['notif_jenis_id'])){
      $payload['notif_jenis_id'] = $param['notif_jenis_id'];
    }

    // $data = [
    //   "registration_ids" => $firebaseToken,
    //   "notification" => $payload
    // ];

    $data = [
      "registration_ids"  => $firebaseToken,
      "data"              => $payload,
      "priority"          => 'high'
    ];

    $dataString = json_encode($data);

    $headers = [
        'Authorization: key=' . $SERVER_API_KEY,
        'Content-Type: application/json',
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

    $response = curl_exec($ch); 
    $err = curl_error($ch);   
    curl_close($ch);  

    if ($err) {
      $resp = [
        'req' => $dataString,
        'res' => $err,
      ];
    }else{
      $resp = [
        'req' => json_decode($dataString),
        'res' => json_decode($response),
      ];
    }
    $dataLog = [
      'id_pegawai' => !empty($param['id_pegawai'])?$param['id_pegawai']:0,
      'judul' => !empty($param['notif_title'])?$param['notif_title']:null,
      'pesan' => !empty($param['notif_message'])?$param['notif_message']:null,
      'jenis' => !empty($param['notif_jenis'])?$param['notif_jenis']:null,
      'jenis_id' => !empty($param['notif_jenis_id'])?$param['notif_jenis_id']:null,
      'log_req' => $dataString,
      'log_res' => !empty($response)?$response:$err,
    ];
    model('App\Models\LogNotif')->insert($dataLog);
    return $resp;
  }
}

if ( ! function_exists('sendNotifAtasan')) {
  function sendNotifAtasan($param=array()) {
    $authUser = new PegawaiLoginModel;
    $user_cek = $authUser->where(['id_pegawai'=>$param['user_id']])->first();
    if(empty($user_cek->perent_id)){
      return false;
    }

    $user_atasan = $authUser->where(['id_pegawai'=>$user_cek->perent_id])->first();

    $firebaseToken = $user_atasan->fcm_token;

    $payload = [
      "title"             => $param['notif_title'],
      "message"           => potong_kalimat($param['notif_message']),
      "created_at"        => date('Y-m-d H:i:s'),
      "priority"          => "high",
    ];

    if(!empty($param['notif_jenis'])){
      $payload['notif_jenis'] = $param['notif_jenis'];
    }

    if(!empty($param['notif_jenis_id'])){
      $payload['notif_jenis_id'] = (string)$param['notif_jenis_id'];
    }

    $dataLog = [
      'id_pegawai' => !empty($user_cek->perent_id)?$user_cek->perent_id:0,
      'judul' => !empty($param['notif_title'])?$param['notif_title']:null,
      'pesan' => !empty($param['notif_message'])?$param['notif_message']:null,
      'jenis' => !empty($param['notif_jenis'])?$param['notif_jenis']:null,
      'jenis_id' => !empty($param['notif_jenis_id'])?$param['notif_jenis_id']:null,
    ];

    $insert_log = model('App\Models\Cms\NotifikasiModel')->insert($dataLog);
    if($insert_log){
      $payload['id'] = (string)$insert_log;
    }

    // Example message payload
    $message = [
     'token' => $firebaseToken,
     'notification' => [
       'title' => $param['notif_title'],
       'body' => potong_kalimat($param['notif_message']),
      ],
      'data' => $payload
    ];
    $response = sendMessage($message, $param);
    return $response;
  }
}

if ( ! function_exists('sendNotifPerson')) {
  function sendNotifPerson($param=array()) {
    $authUser = new PegawaiLoginModel;
    $user_cek = $authUser->where(['id_pegawai'=>$param['user_id']])->first();
    if(empty($user_cek)){
      return false;
    }

    $firebaseToken = $user_cek->fcm_token;

    $payload = [
      "title"             => $param['notif_title'],
      "message"           => $param['notif_message'],
      "created_at"        => date('Y-m-d H:i:s'),
      "priority"          => "high",
    ];

    if(!empty($param['notif_jenis'])){
      $payload['notif_jenis'] = $param['notif_jenis'];
    }

    if(!empty($param['notif_jenis_id'])){
      $payload['notif_jenis_id'] = (string)$param['notif_jenis_id'];
    }

    if(isset($param['nosave']) && $param['nosave']){
      //skip
    }else{
      $dataLog = [
        'id_pegawai' => !empty($user_cek->id_pegawai)?$user_cek->id_pegawai:0,
        'judul' => !empty($param['notif_title'])?$param['notif_title']:null,
        'pesan' => !empty($param['notif_message'])?$param['notif_message']:null,
        'jenis' => !empty($param['notif_jenis'])?$param['notif_jenis']:null,
        'jenis_id' => !empty($param['notif_jenis_id'])?$param['notif_jenis_id']:null,
      ];

      $insert_log = model('App\Models\Cms\NotifikasiModel')->insert($dataLog);
      if($insert_log){
        $payload['id'] = (string)$insert_log;
      }
    }

    // Example message payload
    $message = [
     'token' => $firebaseToken,
     'notification' => [
       'title' => $param['notif_title'],
       'body' => potong_kalimat($param['notif_message']),
      ],
      'data' => $payload
    ];
    $response = sendMessage($message, $param);
    return $response;
  }
}

if ( ! function_exists('sendNotifIT')) {
  function sendNotifIT($param=array()) {
    $authUser = new PegawaiLoginModel;

    $payload = [
      "title"             => $param['notif_title'],
      "message"           => $param['notif_message'],
      "created_at"        => date('Y-m-d H:i:s'),
      "priority"          => "high",
    ];

    if(!empty($param['notif_jenis'])){
      $payload['notif_jenis'] = $param['notif_jenis'];
    }

    if(!empty($param['notif_jenis_id'])){
      $payload['notif_jenis_id'] = (string)$param['notif_jenis_id'];
    }

    $user_cek = $authUser->where(['divisi'=>4,'status_pegawai !='=>2,'fcm_token !='=>null])->findAll();
    if(empty($user_cek)){
      return false;
    }

    foreach ($user_cek as $key => $value) {
      // Example message payload
      $message = [
       'token' => $value->fcm_token,
       'notification' => [
         'title' => $param['notif_title'],
         'body' => potong_kalimat($param['notif_message']),
        ],
        'data' => $payload
      ];
      $response = sendMessage($message, $param);
    }

    return true;
  }
}

if ( ! function_exists('sendNotifHrd')) {
  function sendNotifHrd($param=array()) {
    $authUser = new PegawaiLoginModel;

    $payload = [
      "title"             => $param['notif_title'],
      "message"           => $param['notif_message'],
      "created_at"        => date('Y-m-d H:i:s'),
      "priority"          => "high",
    ];

    if(!empty($param['notif_jenis'])){
      $payload['notif_jenis'] = $param['notif_jenis'];
    }

    if(!empty($param['notif_jenis_id'])){
      $payload['notif_jenis_id'] = (string)$param['notif_jenis_id'];
    }

    $user_cek = $authUser->where(['divisi'=>3,'status_pegawai !='=>2,'fcm_token !='=>null])->findAll();
    if(empty($user_cek)){
      return false;
    }

    foreach ($user_cek as $key => $value) {
      // Example message payload
      $message = [
       'token' => $value->fcm_token,
       'notification' => [
         'title' => $param['notif_title'],
         'body' => potong_kalimat($param['notif_message']),
        ],
        'data' => $payload
      ];
      $response = sendMessage($message, $param);
    }

    return true;
  }
}

if ( ! function_exists('sendNotifKeu')) {
  function sendNotifKeu($param=array()) {
    $authUser = new PegawaiLoginModel;

    $payload = [
      "title"             => $param['notif_title'],
      "message"           => $param['notif_message'],
      "created_at"        => date('Y-m-d H:i:s'),
      "priority"          => "high",
    ];

    if(!empty($param['notif_jenis'])){
      $payload['notif_jenis'] = $param['notif_jenis'];
    }

    if(!empty($param['notif_jenis_id'])){
      $payload['notif_jenis_id'] = (string)$param['notif_jenis_id'];
    }

    $user_cek = $authUser->where(['divisi'=>5,'status_pegawai !='=>2,'fcm_token !='=>null])->findAll();
    if(empty($user_cek)){
      return false;
    }

    foreach ($user_cek as $key => $value) {
      // Example message payload
      $message = [
       'token' => $value->fcm_token,
       'notification' => [
         'title' => $param['notif_title'],
         'body' => potong_kalimat($param['notif_message']),
        ],
        'data' => $payload
      ];
      $response = sendMessage($message, $param);
    }

    return true;
  }
}

if (!function_exists('resBody')) {
  function resBody($param=[]) {
    $response = [
        'status'    => $param['status'],
        'messages'  => $param['messages'],
    ];

    if(isset($param['jarak'])){
      $response['jarak'] = $param['jarak'];
    }

    if(isset($param['batas'])){
      $response['batas'] = $param['batas'];
    }

    if(isset($param['data'])){
      $response['data'] = $param['data'];
    }

    if(!empty($param['token'])){
      $response['token'] = $param['token'];
    }

    return $response;
  }
}

if ( ! function_exists('forgotPassword')) {
  function forgotPassword(int $id): bool
  {
    $authUser = new PegawaiLoginModel;
    $baseURL = getenv('app.baseURL');
    $forgotToken = generateRandomString(9);
    $expired_at = date("Y-m-d H:i:s", strtotime('+1 hours'));

    $edit = $authUser->where('id',$id)->first();
    if(!$edit) return false;

    $isUpdated = $authUser->update_data($id, [ 'reset_token' => $forgotToken,'reset_token_ex' => $expired_at ]);
    if (!$isUpdated) return false;

    $mailer = service('email');
    $config['SMTPHost'] = getenv('EMAIL_HOST');
    $config['SMTPUser'] = getenv('EMAIL_AKUN');
    $config['SMTPPass'] = getenv('EMAIL_PASS');
    $config['SMTPPort'] = getenv('EMAIL_PORT');
    $config['wordWrap'] = true;
    $config['protocol']  = 'smtp';
    $config['mailType']  = 'html';
    $config['SMTPCrypto']  = 'ssl';
    $mailer->initialize($config);
    $mailer->setFrom(getenv('EMAIL_AKUN'), 'Sistem HRD');
    $mailer->setTo($edit->email);
    // $mailer->setBCC('mail.bcc@yopmail.com');
    $mailer->setSubject('Lupa Kata Sandi');
    $mailer->setMessage("Berikut kode untuk mengatur ulang kata sandi Anda <b style='font-size:16px'>".$forgotToken.'</b>');
    $isSent = $mailer->send(false);
    // pr($mailer->printDebugger(),1);
    if (!$isSent) log_message('error', $mailer->printDebugger());

    return $isSent;
  }
}

if ( ! function_exists('getNotifications')){
  function getNotifications($userId)
  {
      $notificationModel = new NotificationModel();
      return $notificationModel->where('user_id', $userId)->orderBy('created_at', 'DESC')->asObject()->findAll();
  }
}

if ( ! function_exists('resetPassword')) {
  function resetPassword(string $forgotToken, string $password): bool
  {
    $authUser = new PegawaiLoginModel;
    $edit = $authUser->where('reset_token',$forgotToken)->first();
    if(!$edit) return false;

    $isUpdated = $authUser->update_data($edit->id, ['reset_token' => null, 'reset_token_ex'=> null, 'password' => password_hash($password, PASSWORD_DEFAULT)]);
    
    return $isUpdated;
  }
}

if ( ! function_exists('generateRandomString')) {
  function generateRandomString($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[Rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }
}

if ( ! function_exists('sendMail')) {
  function sendMail($param=array()) {
    $mailer = service('email');
    $config['SMTPHost'] = getenv('EMAIL_HOST');
    $config['SMTPUser'] = getenv('EMAIL_AKUN');
    $config['SMTPPass'] = getenv('EMAIL_PASS');
    $config['SMTPPort'] = getenv('EMAIL_PORT');
    $config['wordWrap'] = true;
    $config['protocol']  = 'smtp';
    $config['mailType']  = 'html';
    $config['SMTPCrypto']  = 'ssl';
    $mailer->initialize($config);
    $mailer->setFrom(getenv('EMAIL_AKUN'), 'Sistem HRD');
    $mailer->setTo($param['email']);
    // $mailer->setBCC('abc.bcc@yopmail.com');
    $mailer->setSubject($param['subject']);
    $mailer->setMessage($param['massage']);
    $isSent = $mailer->send(false);

    if (!$isSent) log_message('error', $mailer->printDebugger());

    return $isSent;
  }
}

if ( ! function_exists('GetJarak')) {
  function GetJarak(
    $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
  {
    // convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $lonDelta = $lonTo - $lonFrom;
    $a = pow(cos($latTo) * sin($lonDelta), 2) +
      pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
    $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

    $angle = atan2(sqrt($a), $b);
    return ceil($angle * $earthRadius);
  }
}

if ( ! function_exists('time_elapsed_string')) {
  function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
      'y' => 'year',
      'm' => 'month',
      'w' => 'week',
      'd' => 'day',
      'h' => 'hour',
      'i' => 'minute',
      's' => 'second',
    );
    foreach ($string as $k => &$v) {
      if ($diff->$k) {
        $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
      } else {
        unset($string[$k]);
      }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
  }
}

if ( ! function_exists('AddPlayTime')) {
  function AddPlayTime($times=array())
  {
    $all_seconds = 0;
    $minutes = 0;
    // loop throught all the times
    foreach ($times as $time) {
        list($hour, $minute, $second) = explode(':', $time);
        $all_seconds += $hour * 3600;
        $all_seconds += $minute * 60; $all_seconds += $second;
        $minutes += $hour * 60;
        $minutes += $minute;

    }
  
    $hours = floor($minutes / 60);
    $minutes -= $hours * 60;
    $seconds = $all_seconds % 60;

    // returns the time already formatted
    return sprintf('%02d:%02d:%02d', $hours, $minutes,$seconds);
  }
}

if (!function_exists('timeToMinute')) {
  function timeToMinute($time=null){
    if(!empty($time)){
      $time = explode(':', $time);
      $a = ($time[0]*60) + ($time[1]) + ($time[2]/60);
      $b = $a/60;
      return $b;
    }else{
      return 0;
    }
  }
}

if (!function_exists('format_price')) {
  function format_price($price = NULL,$koma = 0) {
    if(!empty($price)){
      return "Rp".number_format($price, $koma, ',', '.');
    }else{
      return "Rp0";
    }
  }
}

if (!function_exists('format_price2')) {
  function format_price2($price = NULL) {
    if(!empty($price)){
      return "Rp ".number_format($price, 2, ',', '.');
    }else{
      return "Rp0";
    }
  }
}

if (!function_exists('format_angka')) {
  function format_angka($angka = NULL) {
    if(!empty($angka)){
      return number_format($angka, 2, ',', '.');
    }else{
      return 0;
    }
  }
}

if (!function_exists('formatNegatif')) {
    function formatNegatif($angka) {
        $angka = trim($angka);
        $angka = floatval($angka); // Konversi ke float

        if ($angka < 0) {
            return '(' . number_format(abs($angka), 2, ',', '.') . ')';
        }
        return number_format($angka, 2, ',', '.');
    }
}

if (!function_exists('getSelDb')) {
  function getSelDb($db=null) {
    $dbs = [
      'sdkom' => 'SDKOM',
      'ariston' => 'Ariston', 
      'wep' => 'WEP',
      'dtf' => 'DTF',
      'ariston_bali' => 'Ariston Bali',
      'wep_bali' => 'WEP Bali'
    ];
    if (empty($db)) {
      return $dbs;
    }

    return $dbs[$db];
  }
}


if ( ! function_exists('tanggal_indo')) {
  function tanggal_indo($tgl, $cetak_hari = false, $pendek= false)
  {
    if($tgl == "0000-00-00" OR $tgl == null){
      return '-';
      exit();
    }

    $cek = explode(' ', $tgl);
    if(count($cek)>1){
      $tanggal = $cek[0];
      $jam = $cek[1];
    }else{
      $tanggal = $tgl;
      $jam = '';
    }

    if($jam == "00:00:00"){
      $jam = '';
    }

    $hari = array ( 1 =>    'Senin',
                'Selasa',
                'Rabu',
                'Kamis',
                'Jumat',
                'Sabtu',
                'Minggu'
            );

    if($pendek){
      $bulan = array (1 =>   'Jan',
                  'Feb',
                  'Mar',
                  'Apr',
                  'Mei',
                  'Jun',
                  'Jul',
                  'Agt',
                  'Sep',
                  'Okt',
                  'Nov',
                  'Des'
              );
    }else{
      $bulan = array (1 =>   'Januari',
                  'Februari',
                  'Maret',
                  'April',
                  'Mei',
                  'Juni',
                  'Juli',
                  'Agustus',
                  'September',
                  'Oktober',
                  'November',
                  'Desember'
              );
    }
    $split    = explode('-', $tanggal);
    $tgl_indo = $split[2] . ' ' . $bulan[ (int)$split[1] ] . ' ' . $split[0];
   
    if ($cetak_hari) {
      $num = date('N', strtotime($tanggal));
      return $hari[$num] . ', ' . $tgl_indo.' '.$jam;
    }
    return $tgl_indo.' '.$jam;
  }
}

if ( ! function_exists('format_date')) {
  function format_date($date = NULL, $format = "d/m/Y") {
    if(!empty($date) && $date != "0000-00-00"){
      $timestamp = strtotime($date);
      return date($format, $timestamp);
    }else{
      return '';
    }
  }
}

if ( ! function_exists('formatTitle')) {
  function formatTitle($string) {
    return ucwords(strtolower($string));
  }
}

if ( ! function_exists('getMonths_old')) {
  function getMonths_old($id = NULL) {
    $months = array(
        1   => 'Januari',
        2   => 'Februari',
        3   => 'Maret',
        4   => 'April',
        5   => 'Mei',
        6   => 'Juni',
        7   => 'Juli',
        8   => 'Agustus',
        9   => 'September',
        10  => 'Oktober',
        11  => 'November',
        12  => 'Desember'
    );
    if(empty($id)){
      return $months;
    }else{
      return $months[$id] ?? '';
    }
  }
}

if ( ! function_exists('getMonths')) {
  function getMonths($id = NULL,$pendek = false) {
    if($pendek){
      $months = array(
          1   => 'Jan',
          2   => 'Feb',
          3   => 'Mar',
          4   => 'Apr',
          5   => 'Mei',
          6   => 'Jun',
          7   => 'Jul',
          8   => 'Agt',
          9   => 'Sep',
          10  => 'Okt',
          11  => 'Nov',
          12  => 'Des'
      );
    }else{
      $months = array(
          1   => 'Januari',
          2   => 'Februari',
          3   => 'Maret',
          4   => 'April',
          5   => 'Mei',
          6   => 'Juni',
          7   => 'Juli',
          8   => 'Agustus',
          9   => 'September',
          10  => 'Oktober',
          11  => 'November',
          12  => 'Desember'
      );
    }
    if(empty($id)){
      return $months;
    }else{
      return $months[$id] ?? '';
    }
  }
}

if (!function_exists('checkMenuAccess')) {
    function checkMenuAccess($moduleLink) {
        $session = session();
        $userId = $session->get('user_id');
        
        // If no user is logged in, return no access
        if (!$userId) {
            return [
                'can_view' => false,
                'can_create' => false,
                'can_edit' => false,
                'can_delete' => false
            ];
        }
        
        $db = db_connect();
        $query = $db->query("SELECT can_view, can_create, can_edit, can_delete 
                            FROM user_module_access_detail 
                            WHERE user_id = ? AND module_link = ? 
                            AND deleted_at IS NULL", [$userId, $moduleLink]);
        
        $result = $query->getRowArray();
        
        if (!$result) {
            return [
                'can_view' => false,
                'can_create' => false,
                'can_edit' => false,
                'can_delete' => false
            ];
        }

        // Normalize PostgreSQL boolean values
        return [
            'can_view' => in_array($result['can_view'], ['t', '1', 1, true], true),
            'can_create' => in_array($result['can_create'], ['t', '1', 1, true], true),
            'can_edit' => in_array($result['can_edit'], ['t', '1', 1, true], true),
            'can_delete' => in_array($result['can_delete'], ['t', '1', 1, true], true)
        ];
    }
}

function checkModul($modul, $aksi = 'view') 
{
    $db = \Config\Database::connect();
    $userId = session()->get('user_id');
    
    if (!$userId) return false;
    
    // Super admin has all access
    if (detailUser()->user_role == 'superadmin') return true;
    
    $builder = $db->table('modules_gl')
        ->join('user_module_access_gl', 'modules_gl.id = user_module_access_gl.module_id')
        ->where('modules_gl.slug', $modul)
        ->where('user_module_access_gl.user_id', $userId)
        ->where('modules_gl.deleted_at', null)
        ->where('user_module_access_gl.deleted_at', null);
        
    $access = $builder->get()->getRow();
    
    if (!$access) return false;
    
    switch($aksi) {
        case 'view':
            return (bool)$access->can_view;
        case 'create':
            return (bool)$access->can_create;
        case 'edit':
            return (bool)$access->can_edit;
        case 'delete':
            return (bool)$access->can_delete;
        default:
            return false;
    }
}

if ( ! function_exists('generateKodeJurnal')) {
  function generateKodeJurnal($db)
  {
      // Ambil bulan singkat dari helper (misalnya 'Mar') dan ubah ke kapital semua
      $bulan = strtoupper(getMonths(date('n'), true)); // Misalnya: MAR

      // Ambil 2 digit tahun
      $tahun = date('y');

      // Gabung jadi prefix
      $prefix = $bulan . $tahun;

      // Query PostgreSQL: ambil angka terakhir dari KDJV yang diawali dengan prefix
      $query = $db->query("
          SELECT MAX(RIGHT(\"KDJV\", 4)) AS max_kode 
          FROM jv 
          WHERE \"KDJV\" LIKE '{$prefix}%'
      ");

      $row = $query->getRow();
      $lastNumber = $row && $row->max_kode ? (int)$row->max_kode : 0;
      $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

      return $prefix . '-' . $newNumber;
  }
}

if ( ! function_exists('clearNumber')) {
  function clearNumber($num) {
      return (float) str_replace([',', '.'], '', $num);
  }
}


if ( ! function_exists('diff_date')) {
  function diff_date($tgl1='date("Y-m-d")',$tgl2='date("Y-m-d")') {
    $tgl2 = new \DateTime($tgl2);
    $tgl1 = new \DateTime($tgl1);
    if ($tgl2 > $tgl1) { 
      return "0 tahun 0 bulan 0 hari";
    }
    $y = $tgl1->diff($tgl2)->y;
    $m = $tgl1->diff($tgl2)->m;
    $d = $tgl1->diff($tgl2)->d;
    return $y." tahun ".$m." bulan ".$d." hari";
  }
}

if ( ! function_exists('diff_date2')) {
  function diff_date2($tgl1='date("Y-m-d")',$tgl2='date("Y-m-d")') {
    $tgl2 = new \DateTime($tgl2);
    $tgl1 = new \DateTime($tgl1);
    if ($tgl2 > $tgl1) { 
      return "0 tahun 0 bulan 0 hari";
    }
    $y = $tgl1->diff($tgl2)->y;
    $m = $tgl1->diff($tgl2)->m;
    $d = $tgl1->diff($tgl2)->d;
    $hsl = [
      'tahun' => $y,
      'bulan' => $m,
      'hari' => $d,
    ];
    return $hsl;
  }
}

if ( ! function_exists('diff_time')) {
  function diff_time($jam1='date("H:i:s")',$jam2='date("H:i:s")') {
    $jam2 = new \DateTime($jam2);
    $jam1 = new \DateTime($jam1);
    // if ($jam2 > $jam1) { 
    //   return NULL;
    // }
    $h = $jam1->diff($jam2)->h;
    $i = $jam1->diff($jam2)->i;
    $s = $jam1->diff($jam2)->s;
    return $h.":".$i.":".$s;
  }
}

if ( ! function_exists('diff_datetime')) {
  function diff_datetime($tgl=null,$tgl2=null) {
    if(empty($tgl) || empty($tgl2)){
      return "00:00:00";
    }
    $tgl2 = new \DateTime($tgl2);
    $tgl = new \DateTime($tgl);
    // if ($tgl2 > $tgl) { 
    //   return NULL;
    // }
    $h = $tgl->diff($tgl2)->h;
    $i = $tgl->diff($tgl2)->i;
    $s = $tgl->diff($tgl2)->s;
    return $h.":".$i.":".$s;
  }
}

if ( ! function_exists('diff_days')) {
  function diff_days($tanggal_mulai='date("Y-m-d")',$tanggal_selesai='date("Y-m-d")') {
    $tgl1 = new \DateTime($tanggal_mulai);
    $tgl2 = new \DateTime($tanggal_selesai);
    $selisih = $tgl2->diff($tgl1)->d;

    $hitung  = date_diff( $tgl1, $tgl2 );
    $selisih  = $hitung->days;

    if ($tanggal_selesai > $tanggal_mulai) { 
      return -$selisih;
    }
    return $selisih;
  }
}

if ( ! function_exists('stringBulan')) {
  function stringBulan($bln=null) {
    if(empty($bln)){
      return null;
    }
    $months = array(
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    );
    return $months[(int)$bln];
  }
}

if (!function_exists('potong_kalimat')) {
  function potong_kalimat($kalimat, $num_char = 200)
  {
    $kontn = strip_tags($kalimat);
    if (strlen($kontn) > $num_char) {
      $desk = substr($kontn, 0, $num_char);
      $char     = $desk[$num_char - 1];
      while ($char != ' ') {
        $char = $desk[--$num_char]; // Cari spasi
      }
      $deskr = substr($desk, 0, $num_char) . '...';
    } else {
      $deskr = $kontn;
    }
    return $deskr;
  }
}

function encode_img_base64( $img_path = false, $img_type = 'png' ){
  if( $img_path ){
      //convert image into Binary data
      $img_data = fopen ( $img_path, 'rb' );
      $img_size = filesize ( $img_path );
      $binary_image = fread ( $img_data, $img_size );
      fclose ( $img_data );

      //Build the src string to place inside your img tag
      $img_src = "data:image/".$img_type.";base64,".str_replace ("\n", "", base64_encode($binary_image));

      return $img_src;
  }

  return false;
}

if ( ! function_exists('get_setting')) {
  function get_setting() {
    $setting =  new SettingModel();
    $data = $setting->first();
    if($data){
      return $data;  
    }else{
      return [];
    }
  }
}

if ( ! function_exists('generate_webp_image')) {
  function generate_webp_image($file, $compression_quality = 80)
  {
    ini_set('memory_limit', '-1');
    // check if file exists
    if (!file_exists($file)) {
      return false;
    }

    // If output file already exists return path
    $output_file = $file . '.webp';
    if (file_exists($output_file)) {
      return $output_file;
    }

    $file_type = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if (function_exists('imagewebp')) {
      switch ($file_type) {
        case 'jpeg':
        case 'jpg':
          $image = imagecreatefromjpeg($file);
          break;
        case 'png':
          $image = imagecreatefrompng($file);
          imagepalettetotruecolor($image);
          imagealphablending($image, true);
          imagesavealpha($image, true);
          break;
        case 'gif':
          $image = imagecreatefromgif($file);
          break;
        default:
          return false;
      }

      // Save the image
      $result = imagewebp($image, $output_file, $compression_quality);
      if (false === $result) {
        return false;
      }
      // Free up memory
      imagedestroy($image);
      return $output_file;
    } elseif (class_exists('Imagick')) {
      $image = new Imagick();
      $image->readImage($file);
      if ($file_type === 'png') {
        $image->setImageFormat('webp');
        $image->setImageCompressionQuality($compression_quality);
        $image->setOption('webp:lossless', 'true');
      }
      $image->writeImage($output_file);
      return $output_file;
    }
    return false;
  }
}

if ( ! function_exists('get_youtube_embed')) {
  function get_youtube_embed($data) {
    return preg_replace('/(?:https?:\/\/)?(?:www\.|m\.)?youtu(?:\.be\/|be.com\/\S*(?:watch|embed)(?:(?:(?=\/[^&\s\?]+(?!\S))\/)|(?:\S*v=|v\/)))([^&\s\?]+)/m',"https://www.youtube.com/embed/$1",$data);
  }
}

if ( ! function_exists('cleanString')) {
  function cleanString($str) {
      // Ubah ke string, hapus semua karakter non-angka
      $str = preg_replace('/[^0-9]/', '', (string) $str);

      $result = str_pad($str, 16, '0', STR_PAD_LEFT);
      $result = substr($result, 0, 16);
      return $result;
  }
}

if ( ! function_exists('potong_huruf')) {
  function potong_huruf($kalimat, $lenth) {
    $num_char = $lenth;
    $kontn = strip_tags($kalimat);
    if(strlen($kontn) > $num_char){
        $desk = substr($kontn, 0,$num_char);
        $char     = $desk[$num_char - 1];
    while($char != ' ') {
        $char = $desk[--$num_char]; // Cari spasi
    }
        $deskr = substr($desk, 0, $num_char).'...';
    }else{
        $deskr = $kontn;
    }
    return $deskr;
  }
}

if ( ! function_exists('selisih_bulan')) {
  function selisih_bulan($date, $date1 = null) { 
    if($date1 == null){
      $set = explode('-', $date);
      $diff  = date_diff( date_create($set[0]), date_create($set[1]) );
    }else{
      $diff  = date_diff( $date, $date1 );
    }

    $result = '';
    if($diff->m != 0){
      $result = $result.$diff->m.' Bulan ';
    }
    if($diff->d != 0){
      $result = $result.$diff->d.' Hari ';
    }


    return $result;

  } 

}

if (!function_exists('logGL')) {
    function logGL(string $config_db,string $module, string $aksi, int $userId = null): bool
    {
        $db = \Config\Database::connect($config_db);

        if ($userId === null) {
            $userId = session()->get('user_id');
        }

        $data = [
            'module'     => $module,
            'aksi'       => $aksi,
            'user_id'    => $userId,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        return $db->table('crm.log_gl')->insert($data);
    }
}

if ( ! function_exists('listModul')) {
  function listModul(){
    return [];

    $UserAkses =  new UserAksesModel();
    $user_id = session()->get('user_id');
    if($user_id){
      $cekFitur = $UserAkses->get_fitur($user_id);
      if ($cekFitur) {
        $fiturData = [];
        foreach ($cekFitur as $key => $value) {
          array_push($fiturData, $value->module_nama);
        }
        return $fiturData;
      }else{
        return [];
      }
    }else{
      return [];
    }
  }
}

if ( ! function_exists('checkModul')) {
  function checkModul($modul, $aksi = 'view') 
{
    $db = \Config\Database::connect();
    $userId = session()->get('user_id');
    
    if (!$userId) return false;
    
    // Super admin has all access
    if (detailUser()->user_role == 'superadmin') return true;
    
    $builder = $db->table('modules_gl')
        ->join('user_module_access_gl', 'modules_gl.id = user_module_access_gl.module_id')
        ->where('modules_gl.slug', $modul)
        ->where('user_module_access_gl.user_id', $userId)
        ->where('modules_gl.deleted_at', null)
        ->where('user_module_access_gl.deleted_at', null);
        
    $access = $builder->get()->getRow();
    
    if (!$access) return false;
    
    switch($aksi) {
        case 'view':
            return (bool)$access->can_view;
        case 'create':
            return (bool)$access->can_create;
        case 'edit':
            return (bool)$access->can_edit;
        case 'delete':
            return (bool)$access->can_delete;
        default:
            return false;
    }
}
}

if (!function_exists('detailUser')) {
  function detailUser(){
      $user_id = session()->get('user_id');
      if($user_id){
          $User =  new \App\Models\UsersModel(); // Ensure the correct namespace
          return $User->find($user_id);
      } else {
          return [];
      }
  }
}

if ( ! function_exists('detailUserById')) {
  function detailUserById($user_id){
    if($user_id){
      $User =  new UsersModel();
      return $User->find($user_id);
    }else{
      return [];
    }
  }
}

if ( ! function_exists('get_gapok')) {
  function get_gapok($id_pegawai=null) {
    $pgw =  model('App\Models\Cms\PegawaiModel');
    $data = $pgw->where('id',$id_pegawai)->first();
    if($data){
      return ['gapok' => $data->gapok, 'sisa_gapok' => $data->sisa_gapok];  
    }else{
      return ['gapok' => 0, 'sisa_gapok' => 0];
    }
  }
}

if ( ! function_exists('getAngsuran')) {
  function getAngsuran($totaltarif,$jumlahangsuran) {
    $min = 5000;
    $totalPem = 0;
    $DaftarAngsuran = [];

    for($i = 0; $i < $jumlahangsuran; $i++){
      $angsuran = 0;
      if($totaltarif >= $min){
        $angsuran = ceil($totaltarif/$jumlahangsuran);
        if($angsuran%$min > 0){
          $angsuran = $min * ceil($angsuran/$min);
        }
      }else{
        $angsuran = $min;
      }
      $totalPem += $angsuran;
    }

    $totalCount=0;
    $sbulan=1;
    $no = 1;

    for($j = 0; $j < $jumlahangsuran; $j++){
      $angsuran = 0;
      $nilai = 0;
      if($totaltarif >= $min){
        $angsuran = ceil($totaltarif/$jumlahangsuran);
        if($angsuran%$min > 0){
          $angsuran = $min * ceil($angsuran/$min);
        }
        $nilai = (($totaltarif-$totalPem)<0&&$j==($jumlahangsuran-1)?$angsuran-($totalPem-$totaltarif):$angsuran);
        $totalCount+=$nilai;
      }else{
        $nilai = ($i==0?$totaltarif:0);
        $totalCount+=$nilai;
      }
      
      $set = [
        'no'=>$no,
        'nilai'=>$nilai,
      ];
      array_push($DaftarAngsuran,$set);
      $no++;
    }

    return $DaftarAngsuran;
  }
}

if ( ! function_exists('checkStatus')) {
  function checkStatus($status,$style=false){
    $sts = 'Active';
    if($status == 1){
      if($style == false){
        $sts = 'Tetap';
      }else{
        $sts = '<span class="badge bg-success">Tetap</span>';
      }
    }else if($status == 2){
      if($style == false){
        $sts = 'Resign';
      }else{
        $sts = '<span class="badge bg-danger">Resign</span>';
      }
    }else if($status == 3){
      if($style == false){
        $sts = 'Kontrak';
      }else{
        $sts = '<span class="badge bg-success">Kontrak</span>';
      }
    }else if($status == 4){
      if($style == false){
        $sts = 'Training';
      }else{
        $sts = '<span class="badge bg-success">Training</span>';
      }
    }else if($status == 5){
      if($style == false){
        $sts = 'Uji Kompetensi';
      }else{
        $sts = '<span class="badge bg-success">Uji Kompetensi</span>';
      }
    }else if($status == 6){
      if($style == false){
        $sts = 'Freelance';
      }else{
        $sts = '<span class="badge bg-success">Freelance</span>';
      }
    }else if($status == 7){
      if($style == false){
        $sts = 'PKL';
      }else{
        $sts = '<span class="badge bg-success">PKL</span>';
      }
    }
    return $sts;
  }
}

if ( ! function_exists('checkStatusUser')) {
  function checkStatusUser($status,$style=false){
    $sts = 'Active';
    if($status == 1){
      if($style == false){
        $sts = 'Active';
      }else{
        $sts = '<span class="badge bg-success">Active</span>';
      }
    }else if($status == 0){
      if($style == false){
        $sts = 'Banned';
      }else{
        $sts = '<span class="badge bg-danger">Banned</span>';
      }
    }
    return $sts;
  }
}

if ( ! function_exists('checkStatusBug')) {
  function checkStatusBug($status,$style=false){
    $sts = 'Open';
    if($status == 0){
      if($style == false){
        $sts = 'Open';
      }else{
        $sts = '<span class="badge bg-danger">Open</span>';
      }
    }else if($status == 1){
      if($style == false){
        $sts = 'Close';
      }else{
        $sts = '<span class="badge bg-success">Close</span>';
      }
    }else if($status == 2){
      if($style == false){
        $sts = 'On Progress';
      }else{
        $sts = '<span class="badge bg-info">On Progress</span>';
      }
    }else if($status == 3){
      if($style == false){
        $sts = 'Panding';
      }else{
        $sts = '<span class="badge bg-warning">Panding</span>';
      }
    }
    return $sts;
  }
}

if ( ! function_exists('checkStatusActive')) {
  function checkStatusActive($status,$style=false){
    $sts = 'Active';
    if($status == 1){
      if($style == false){
        $sts = 'Active';
      }else{
        $sts = '<span class="badge bg-success">Active</span>';
      }
    }else if($status == 0){
      if($style == false){
        $sts = 'Draft';
      }else{
        $sts = '<span class="badge bg-danger">Draft</span>';
      }
    }
    return $sts;
  }
}

if ( ! function_exists('checkStatusPayroll')) {
  function checkStatusPayroll($status,$style=false){
    $sts = 'Draft';
    if($status == 2){
      if($style == false){
        $sts = 'Publish';
      }else{
        $sts = '<span class="badge bg-success">Publish</span>';
      }
    }else if($status == 1){
      if($style == false){
        $sts = 'Draft';
      }else{
        $sts = '<span class="badge bg-danger">Draft</span>';
      }
    }
    return $sts;
  }
}

if ( ! function_exists('checkStatusKendaraan')) {
  function checkStatusKendaraan($status,$style=false){
    $sts = 'Diterima';
    if($status == 1){
      if($style == false){
        $sts = 'Diterima';
      }else{
        $sts = '<span class="badge bg-success">Diterima</span>';
      }
    }else if($status == 0){
      if($style == false){
        $sts = 'Mengajukan';
      }else{
        $sts = '<span class="badge bg-danger">Mengajukan</span>';
      }
    }
    return $sts;
  }
}

if ( ! function_exists('checkStatusResign')) {
  function checkStatusResign($status,$style=false){
    $sts = 'Mengajukan';
    if($status == 0){
      if($style == false){
        $sts = 'Mengajukan';
      }else{
        $sts = '<span class="badge bg-warning">Mengajukan</span>';
      }
    }else if($status == 1){
      if($style == false){
        $sts = 'Atasan Acc';
      }else{
        $sts = '<span class="badge bg-success">Atasan Acc</span>';
      }
    }else if($status == 2){
      if($style == false){
        $sts = 'HRD Acc';
      }else{
        $sts = '<span class="badge bg-success">HRD Acc</span>';
      }
    }else if($status == 3){
      if($style == false){
        $sts = 'Atasan Menolak';
      }else{
        $sts = '<span class="badge bg-danger">Atasan Menolak</span>';
      }
    }else if($status == 4){
      if($style == false){
        $sts = 'HRD Menolak';
      }else{
        $sts = '<span class="badge bg-danger">HRD Menolak</span>';
      }
    }
    return $sts;
  }
}

if ( ! function_exists('checkStatusKehadiran')) {
  function checkStatusKehadiran($status,$style=false){
    $sts = 'Mengajukan';
    if($status == 0){
      if($style == false){
        $sts = 'Mengajukan';
      }else{
        $sts = '<span class="badge bg-warning">Mengajukan</span>';
      }
    }else if($status == 1){
      if($style == false){
        $sts = 'Atasan Acc';
      }else{
        $sts = '<span class="badge bg-success">Atasan Acc</span>';
      }
    }else if($status == 2){
      if($style == false){
        $sts = 'HRD Acc';
      }else{
        $sts = '<span class="badge bg-success">HRD Acc</span>';
      }
    }else if($status == 3){
      if($style == false){
        $sts = 'Atasan Menolak';
      }else{
        $sts = '<span class="badge bg-danger">Atasan Menolak</span>';
      }
    }else if($status == 4){
      if($style == false){
        $sts = 'HRD Menolak';
      }else{
        $sts = '<span class="badge bg-danger">HRD Menolak</span>';
      }
    }
    return $sts;
  }
}

if ( ! function_exists('checkStatusPengajuan')) {
  function checkStatusPengajuan($status,$style=false){
    $sts = 'Mengajukan';
    if($status == 0){
      if($style == false){
        $sts = 'Mengajukan';
      }else{
        $sts = '<span class="badge bg-warning">Mengajukan</span>';
      }
    }else if($status == 1){
      if($style == false){
        $sts = 'Atasan Acc';
      }else{
        $sts = '<span class="badge bg-success">Atasan Acc</span>';
      }
    }else if($status == 2){
      if($style == false){
        $sts = 'HRD/Keu Acc';
      }else{
        $sts = '<span class="badge bg-success">HRD Acc</span>';
      }
    }else if($status == 3){
      if($style == false){
        $sts = 'Atasan Menolak';
      }else{
        $sts = '<span class="badge bg-danger">Atasan Menolak</span>';
      }
    }else if($status == 4){
      if($style == false){
        $sts = 'HRD/Keu Menolak';
      }else{
        $sts = '<span class="badge bg-danger">HRD Menolak</span>';
      }
    }
    return $sts;
  }
}

if ( ! function_exists('checkStatusPengembalian')) {
  function checkStatusPengembalian($status,$style=false){
    $sts = 'Mengajukan';
    if($status == 0){
      if($style == false){
        $sts = 'Mengajukan';
      }else{
        $sts = '<span class="badge bg-warning">Mengajukan</span>';
      }
    }else if($status == 1){
      if($style == false){
        $sts = 'Approve';
      }else{
        $sts = '<span class="badge bg-success">Approve</span>';
      }
    }else if($status == 2){
      if($style == false){
        $sts = 'Rejected';
      }else{
        $sts = '<span class="badge bg-danger">Rejected</span>';
      }
    }else if($status == 3){
      if($style == false){
        $sts = 'User Deleted';
      }else{
        $sts = '<span class="badge bg-danger">User Deleted</span>';
      }
    }
    return $sts;
  }
}

if ( ! function_exists('checkStatusPinjaman')) {
  function checkStatusPinjaman($status,$style=false){
    $sts = 'Mengajukan';
    if($status == 0){
      if($style == false){
        $sts = 'Mengajukan';
      }else{
        $sts = '<span class="badge bg-warning">Mengajukan</span>';
      }
    }else if($status == 1){
      if($style == false){
        $sts = 'Atasan Acc';
      }else{
        $sts = '<span class="badge bg-success">Atasan Acc</span>';
      }
    }else if($status == 2){
      if($style == false){
        $sts = 'Diterima';
      }else{
        $sts = '<span class="badge bg-success">Diterima</span>';
      }
    }else if($status == 5){
      if($style == false){
        $sts = 'Ditolak';
      }else{
        $sts = '<span class="badge bg-danger">Ditolak</span>';
      }
    }else if($status == 3){
      if($style == false){
        $sts = 'Atasan Menolak';
      }else{
        $sts = '<span class="badge bg-danger">Atasan Menolak</span>';
      }
    }
    return $sts;
  }
}

if ( ! function_exists('checkStatusTask')) {
  function checkStatusTask($status,$style=false){
    $sts = 'Open';
    if($status == 1){
      if($style == false){
        $sts = 'Open';
      }else{
        $sts = '<span class="badge bg-info">Open</span>';
      }
    }else if($status == 2){
      if($style == false){
        $sts = 'In Progress';
      }else{
        $sts = '<span class="badge bg-warning">In Progress</span>';
      }
    }else if($status == 3){
      if($style == false){
        $sts = 'Done';
      }else{
        $sts = '<span class="badge bg-success">Done</span>';
      }
    }
    return $sts;
  }
}

if ( ! function_exists('subModule')) {
  function subModule($id){
    $userMdlMod = new UserModulModel();
    $child = $userMdlMod->where('module_perent', $id)->findAll();
    if($child){
      return $child;
    }else{
      return [];
    }
  }
}

if ( ! function_exists('tipeIjin')) {
  function tipeIjin($id=null,$all=false){
    $lists[0]['id'] = 'sehari';
    $lists[0]['nama'] = 'Sehari';
    $lists[1]['id'] = 'stghr';
    $lists[1]['nama'] = 'Setengah Hari';
    $lists[2]['id'] = 'plgawl';
    $lists[2]['nama'] = 'Pulang Lebih Awal';
    $lists[3]['id'] = 'klrsbntr';
    $lists[3]['nama'] = 'Keluar Sebentar';
    if(empty($id)){
      if(!$all){
        unset($lists[0]);
      }
      return $lists;
    }else{
        $row = [];
        foreach ($lists as $key => $value) {
            if($value['id'] == $id){
                $row = $value;
                // array_push($row, $value);
            }
        }
        return $row;
    }
  }
}

if ( ! function_exists('getTipeSakit')) {
  function getTipeSakit($id=null){
    $tipe[0]['id'] = 'sd';
    $tipe[0]['nama'] = 'Surat Dokter';
    $tipe[1]['id'] = 'cuti';
    $tipe[1]['nama'] = 'Mengurangi Cuti';
    $tipe[2]['id'] = 'hk';
    $tipe[2]['nama'] = 'Mengurangi Hari Kerja';
    if(empty($id)){
        return $tipe;
    }else{
        foreach ($tipe as $key => $value) {
            if($value['id'] == $id){
                return $value;
            }
        }
    }

    return [];
  }
}

if ( ! function_exists('getSundays')) {
  function getSundays($startYear=2024, $endYear=2024) {
      $sundays = [];

      // Loop melalui setiap tahun
      for ($year = $startYear; $year <= $endYear; $year++) {
          // Loop melalui setiap bulan dalam satu tahun
          for ($month = 1; $month <= 12; $month++) {
              // Jumlah hari dalam bulan tertentu
              $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

              // Loop melalui setiap hari dalam bulan
              for ($day = 1; $day <= $daysInMonth; $day++) {
                  // Membuat objek DateTime untuk tanggal tertentu
                  $date = new DateTime("$year-$month-$day");

                  // Periksa apakah hari adalah Minggu (0 = Minggu)
                  if ($date->format('w') == 0) {
                      $sundays[] = $date->format('Y-m-d');
                  }
              }
          }
      }

      return $sundays;
  }
}

if ( ! function_exists('getMonthsInRange')) {
  function getMonthsInRange($startDate, $endDate)
  {
      $months = array();

      list($athn,$abln,$atgl) = explode('-', $startDate);
      $startDate = $athn.'-'.$abln.'-01';

      while (strtotime($startDate) <= strtotime($endDate)) {
          $months[] = array(
              'year' => date('Y', strtotime($startDate)),
              'month' => date('m', strtotime($startDate)),
          );

          // Set date to 1 so that new month is returned as the month changes.
          $startDate = date('01 M Y', strtotime($startDate . '+ 1 month'));
      }

      return $months;
  }
}

if ( ! function_exists('validateDate')) {
  function validateDate($date, $format = 'Y-m-d')
  {
      $d = DateTime::createFromFormat($format, $date);
      // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
      return $d && $d->format($format) === $date;
  }
}

if ( ! function_exists('checkModulUser')) {
  function checkModulUser($user_id=null,$modul=null,$aksi='lihat'){
    $UserAkses =  new UserAksesModel();
    if($user_id){
      $cekFitur = $UserAkses->check_fitur($user_id,$modul);
      $akses = 'no';
      if($cekFitur){
        if($aksi == 'buat' && $cekFitur->ua_create == 1){
          $akses = 'yes';
        }else if($aksi == 'lihat' && $cekFitur->ua_read == 1){
          $akses = 'yes';
        }else if($aksi == 'ubah' && $cekFitur->ua_update == 1){
          $akses = 'yes';
        }else if($aksi == 'hapus' && $cekFitur->ua_delete == 1){
          $akses = 'yes';
        }
      }
    }else{
      $akses = 'auth';
    }
    return $akses;
  }
}

if ( ! function_exists('number_to_alphabet')) {
  function number_to_alphabet($number = 0){
      $number = intval($number);
      if ($number <= 0) {
         return '';
      }
      $alphabet = '';
      while($number != 0) {
         $p = ($number - 1) % 26;
         $number = intval(($number - $p) / 26);
         $alphabet = chr(65 + $p) . $alphabet;
      }
      return $alphabet;
  }
}

if ( ! function_exists('alphabet_to_number')) {
  function alphabet_to_number($string = 'A'){
      $string = strtoupper($string);
      $length = strlen($string);
      $number = 0;
      $level = 1;
      while ($length >= $level ) {
          $char = $string[$length - $level];
          $c = ord($char) - 64;        
          $number += $c * (26 ** ($level-1));
         $level++;
      }
      return $number;
  }
}

if ( ! function_exists('getJarakGoogle')) {
  function getJarakGoogle($origin=null,$destination=null,$id_pegawai=null,$tanggal=null,$origin_id=null,$destination_id=null,$user_id=null){
    $totJarakTempuh = 0;
    $duration = 0;
    $destination_addresses = '';
    $origin_addresses = '';
    if(!empty($origin) && !empty($destination)){
        $pgwKel = new PegawaiKeliling;
        $pgwKelRek = new PegawaiKelilingRekap;

        $apiKey = 'AIzaSyCYx_sM8WAXW6BaYDwjMZzUmTYPvk8gIhw';
        $url = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins='.$origin.'&destinations='.$destination.'&key='.$apiKey;
        // pr($url,1);
        $getJarak = reqApi($url,'GET');
        if(!empty($getJarak['rows'])){
            $destination_addresses .= $getJarak['destination_addresses'][0] ?? '';
            $origin_addresses .= $getJarak['origin_addresses'][0] ?? '';
            foreach ($getJarak['rows'] as $k => $row) {
                foreach ($row['elements'] as $v) {
                    $totJarakTempuh += $v['distance']['value'] ?? 0;
                    $duration += $v['duration']['value'] ?? 0;
                }
            }

        }
        $cekOrigin = $pgwKel->where(['id'=>$origin_id,])->first();
        $cekDestination = $pgwKel->where(['id'=>$destination_id,])->first();

        if($cekOrigin->tanggal == $cekDestination->tanggal){
          if(!empty($origin_id)){
            $distance = $totJarakTempuh ?? 0;
            $distance_km = $totJarakTempuh/1000;
            $nominal_bbm = get_setting()->nominal_bbm ?? 0;
            $total = $distance_km*$nominal_bbm;
            $dataSave = [
              'id_pegawai' => $id_pegawai,
              'tanggal' => $tanggal,
              'origin_id' => $origin_id,
              'origin' => $origin,
              'destination_id' => $destination_id,
              'destination_id' => $destination_id,
              'destination' => $destination,
              'distance' => $distance,
              'distance_km' => $distance_km,
              'duration' => $duration,
              'destination_addresses' => $destination_addresses,
              'origin_addresses' => $origin_addresses,
              'nominal_bbm' => $nominal_bbm,
              'total' => $total,
            ];
            $cekData = $pgwKelRek->where($dataSave)->first();
            if(empty($cekData)){
              $dataSave['req'] = $url;
              $dataSave['res'] = json_encode($getJarak) ?? '';
              $dataSave['created_by'] = $user_id;
              $pgwKelRek->insert_data($dataSave);
            }
          }
        }
    }

    return $totJarakTempuh;
  }
}

if ( ! function_exists('isLang')) {
  function isLang($title=null){
    $user_id = session()->get('user_id');
    $a = $title;
    if($user_id){
      $User =  new UsersModel();
      $getDetail = $User->find($user_id);
      if($getDetail->user_bahasa == 'us'){
        if(strtolower($title) == 'dashboard'){
          $a = 'dashboard';
        }
        if(strtolower($title) == 'masterdata'){
          $a = 'Master Data';
        }
        if(strtolower($title) == 'agama'){
          $a = 'Religion';
        }
        if(strtolower($title) == 'jabatan'){
          $a = 'Position';
        }
        if(strtolower($title) == 'pendidikan'){
          $a = 'Education';
        }
        if(strtolower($title) == 'pendidikan_terakhir'){
          $a = 'last education';
        }
        if(strtolower($title) == 'tambah_pendidikan'){
          $a = 'add education';
        }
        if(strtolower($title) == 'status_nikah'){
          $a = 'Marital Status';
        }
        if(strtolower($title) == 'ukuran_seragam'){
          $a = 'Uniform Size';
        }
        if(strtolower($title) == 'ukuran'){
          $a = 'Size';
        }
        if(strtolower($title) == 'wilayah'){
          $a = 'Region';
        }
        if(strtolower($title) == 'pegawai'){
          $a = 'employee';
        }
        if(strtolower($title) == 'pegawai_new'){
          $a = 'new employee';
        }
        if(strtolower($title) == 'daftar_pegawai'){
          $a = 'employee list';
        }
        if(strtolower($title) == 'pegawai_daftar'){
          $a = 'employee list';
        }
        if(strtolower($title) == 'daftar'){
          $a = 'lists';
        }
        if(strtolower($title) == 'tambah'){
          $a = 'Add';
        }
        if(strtolower($title) == 'pegawai_resign'){
          $a = 'resign';
        }
        if(strtolower($title) == 'pengunduran_diri'){
          $a = 'resign';
        }
        if(strtolower($title) == 'tambah_pegawai'){
          $a = 'add employee';
        }
        if(strtolower($title) == 'edit_pegawai'){
          $a = 'employee updates';
        }
        if(strtolower($title) == 'rincian_pegawai'){
          $a = 'employee details';
        }
        if(strtolower($title) == 'formulir'){
          $a = 'Form';
        }
        if(strtolower($title) == 'ijin'){
          $a = 'permission leave';
        }
        if(strtolower($title) == 'izin'){
          $a = 'permission leave';
        }
        if(strtolower($title) == 'cuti'){
          $a = 'leave';
        }
        if(strtolower($title) == 'sakit'){
          $a = 'sick leave';
        }
        if(strtolower($title) == 'pesan'){
          $a = 'message';
        }
        if(strtolower($title) == 'pengaturan'){
          $a = 'Settings';
        }
        if(strtolower($title) == 'umum'){
          $a = 'general';
        }
        if(strtolower($title) == 'pengguna'){
          $a = 'User';
        }
        if(strtolower($title) == 'profil'){
          $a = 'Profile';
        }

        if(strtolower($title) == 'tambah_data'){
          $a = 'new data';
        }
        if(strtolower($title) == 'edit_data'){
          $a = 'update data';
        }
        if(strtolower($title) == 'nama'){
          $a = 'name';
        }
        if(strtolower($title) == 'status'){
          $a = 'status';
        }
        if(strtolower($title) == 'aksi'){
          $a = 'action';
        }
        if(strtolower($title) == 'simpan'){
          $a = 'save';
        }
        if(strtolower($title) == 'keluar'){
          $a = 'Close';
        }
        if(strtolower($title) == 'keluar2'){
          $a = 'sign out';
        }
        if(strtolower($title) == 'aktif'){
          $a = 'active';
        }
        if(strtolower($title) == 'tidak_aktif'){
          $a = 'not active';
        }
        if(strtolower($title) == 'tambah_data'){
          $a = 'new data';
        }
        if(strtolower($title) == 'delete_title'){
          $a = 'Are you sure you want to delete data';
        }
        if(strtolower($title) == 'delete_text'){
          $a = 'You will delete data from database';
        }
        if(strtolower($title) == 'signout_title'){
          $a = 'are you sure you will sign out';
        }
        if(strtolower($title) == 'hapus'){
          $a = 'delete';
        }
        if(strtolower($title) == 'berhasil'){
          $a = 'Successfully';
        }
        if(strtolower($title) == 'gagal'){
          $a = 'failed';
        }
        if(strtolower($title) == 'terjadi_kesalahan'){
          $a = 'terjadi_kesalahan';
        }
        if(strtolower($title) == 'daftar2'){
          $a = 'list of';
        }
        if(strtolower($title) == 'no_notif'){
          $a = 'notification not found';
        }
        if(strtolower($title) == 'all_notif'){
          $a = 'see all notification';
        }
        if(strtolower($title) == 'kelompok'){
          $a = 'Group';
        }
        if(strtolower($title) == 'alamat'){
          $a = 'address';
        }
        if(strtolower($title) == 'nama_lengkap'){
          $a = 'full name';
        }
        if(strtolower($title) == 'tempat_lahir'){
          $a = 'place of birth';
        }
        if(strtolower($title) == 'tanggal_lahir'){
          $a = 'date of birth';
        }
        if(strtolower($title) == 'jenis_kelamin'){
          $a = 'gender';
        }
        if(strtolower($title) == 'pria'){
          $a = 'male';
        }
        if(strtolower($title) == 'laki laki'){
          $a = 'male';
        }
        if(strtolower($title) == 'perempuan'){
          $a = 'female';
        }
        if(strtolower($title) == 'sel_pil'){
          $a = '--Select--';
        }
        if(strtolower($title) == 'jumlah_anak'){
          $a = 'number of children';
        }
        if(strtolower($title) == 'jurusan'){
          $a = 'major';
        }
        if(strtolower($title) == 'keluarga'){
          $a = 'family';
        }
        if(strtolower($title) == 'nm_issu'){
          $a = 'Husband / Wife Name';
        }
        if(strtolower($title) == 'nama_ayah'){
          $a = "father's name";
        }
        if(strtolower($title) == 'nama_ibu'){
          $a = "mother's name";
        }
        if(strtolower($title) == 'jumlah_kakak'){
          $a = "number of brothers";
        }
        if(strtolower($title) == 'jumlah_adik'){
          $a = "number of siblings";
        }
        if(strtolower($title) == 'keluarga_hub'){
          $a = "Contact Family";
        }
        if(strtolower($title) == 'nama_keluarga'){
          $a = "family name";
        }
        if(strtolower($title) == 'hubungan_keluarga'){
          $a = "family relationship";
        }
        if(strtolower($title) == 'hubungan_keluarga'){
          $a = "family relationship";
        }
        if(strtolower($title) == 'no_hp_keluarga'){
          $a = "family phone number";
        }
        if(strtolower($title) == 'data_pekerjaan'){
          $a = "employment data";
        }
        if(strtolower($title) == 'tanggal_masuk'){
          $a = "Date of entry";
        }
        if(strtolower($title) == 'no_hp'){
          $a = "Phone number";
        }
        if(strtolower($title) == 'no_telp'){
          $a = "Phone number";
        }
        if(strtolower($title) == 'pengalaman_kerja'){
          $a = "experience";
        }
        if(strtolower($title) == 'riwayat_pekerjaan'){
          $a = "Job Experiences";
        }
        if(strtolower($title) == 'tambah_pengalaman'){
          $a = "add experience";
        }
        if(strtolower($title) == 'perusahaan'){
          $a = "Company";
        }
        if(strtolower($title) == 'lama_bekerja'){
          $a = "length of work";
        }
        if(strtolower($title) == 'uraian_tugas'){
          $a = "Job description";
        }
        if(strtolower($title) == 'gaji_terakhir'){
          $a = "Last salary";
        }
        if(strtolower($title) == 'data_kosong'){
          $a = "Empty data";
        }
        if(strtolower($title) == 'unggah_dokumen'){
          $a = "Upload Document";
        }
        if(strtolower($title) == 'pas_foto'){
          $a = "Passport photo";
        }
        if(strtolower($title) == 'file_saat_ini'){
          $a = "Current files";
        }
        if(strtolower($title) == 'ijasah'){
          $a = "Diploma/Certificate";
        }
        if(strtolower($title) == 'ijazah'){
          $a = "Diploma/Certificate";
        }
        if(strtolower($title) == 'transkrip_nilai'){
          $a = "transcripts";
        }
        if(strtolower($title) == 'nilai'){
          $a = "transcripts";
        }
        if(strtolower($title) == 'masukan_lagi'){
          $a = "input again";
        }
        if(strtolower($title) == 'perbarui_lagi'){
          $a = "update Again";
        }
        if(strtolower($title) == 'lihat_daftar_data'){
          $a = "View Data List";
        }
        if(strtolower($title) == 'ttl'){
          $a = "date of birth";
        }
        if(strtolower($title) == 'alasan'){
          $a = "reason";
        }
        if(strtolower($title) == 'tanggal'){
          $a = "date";
        }
        if(strtolower($title) == 'tanggal_mengundurkan_diri'){
          $a = "resign date";
        }
        if(strtolower($title) == 'menyetujui'){
          $a = "Approve";
        }
        if(strtolower($title) == 'tolak'){
          $a = "reject";
        }
        if(strtolower($title) == 'catatan'){
          $a = "note";
        }
        if(strtolower($title) == 'proses_resign'){
          $a = "resign Process";
        }
        if(strtolower($title) == 'peran'){
          $a = "role";
        }
        if(strtolower($title) == 'kata_sandi'){
          $a = "password";
        }
        if(strtolower($title) == 'konfirmasi_sandi'){
          $a = "confirm password";
        }
        if(strtolower($title) == 'modul'){
          $a = "module";
        }
        if(strtolower($title) == 'membaca'){
          $a = "read";
        }
        if(strtolower($title) == 'membuat'){
          $a = "create";
        }
        if(strtolower($title) == 'memperbarui'){
          $a = "update";
        }
        if(strtolower($title) == 'tx_pwd_edit'){
          $a = "input the password if you want to change";
        }
        if(strtolower($title) == 'kata_sandi_lama'){
          $a = "old password";
        }
        if(strtolower($title) == 'kata_sandi_baru'){
          $a = "new password";
        }
        if(strtolower($title) == 'save_sukses'){
          $a = "Data saved successfully";
        }
        if(strtolower($title) == 'save_gagal'){
          $a = "Data failed to save";
        }
        if(strtolower($title) == 'modul_akses'){
          $a = "You do not have access to this module";
        }
        if(strtolower($title) == 'hapus_sukses'){
          $a = "Data deleted successfully";
        }
        if(strtolower($title) == 'hapus_gagal'){
          $a = "Data failed to delete";
        }
        if(strtolower($title) == 'pengaturan_profil'){
          $a = "profile settings";
        }
        if(strtolower($title) == 'pengaturan_umum'){
          $a = "general settings";
        }
        if(strtolower($title) == 'formulir_cuti'){
          $a = 'leave form';
        }
        if(strtolower($title) == 'form_cuti'){
          $a = 'leave form';
        }
        if(strtolower($title) == 'jenis_cuti'){
          $a = 'type of leave';
        }
        if(strtolower($title) == 'nilai_durasi'){
          $a = 'duration value';
        }
        if(strtolower($title) == 'satuan_durasi'){
          $a = 'duration unit';
        }
        if(strtolower($title) == 'tipe'){
          $a = 'type';
        }
        if(strtolower($title) == 'sekali'){
          $a = 'once';
        }
        if(strtolower($title) == 'berulang'){
          $a = 'repeated';
        }
        if(strtolower($title) == 'hari'){
          $a = 'day';
        }
        if(strtolower($title) == 'bulan'){
          $a = 'month';
        }
        if(strtolower($title) == 'mulai_cuti'){
          $a = 'start leave';
        }
        if(strtolower($title) == 'selesai_cuti'){
          $a = 'finished leave';
        }
        if(strtolower($title) == 'mengajukan'){
          $a = 'submit';
        }
        if(strtolower($title) == 'kehadiran'){
          $a = 'attendance';
        }
        if(strtolower($title) == 'manfaat_pegawai'){
          $a = 'employee benefits';
        }
        if(strtolower($title) == 'perjalanan_dinas'){
          $a = 'official travel';
        }
        if(strtolower($title) == 'dinas'){
          $a = 'official travel';
        }
        if(strtolower($title) == 'notifikasi'){
          $a = 'notification';
        }
        if(strtolower($title) == 'direktori'){
          $a = 'directory';
        }
        if(strtolower($title) == 'rekam_jejak'){
          $a = 'track record';
        }
        if(strtolower($title) == 'promosi'){
          $a = 'promotion';
        }
        if(strtolower($title) == 'demosi'){
          $a = 'demotion';
        }
        if(strtolower($title) == 'mutasi'){
          $a = 'mutation';
        }
        if(strtolower($title) == 'pelanggaran'){
          $a = 'infringement';
        }
        if(strtolower($title) == 'tambah_pelanggaran'){
          $a = 'add infringement';
        }
        if(strtolower($title) == 'edit_pelanggaran'){
          $a = 'infringement updates';
        }
        if(strtolower($title) == 'gaji'){
          $a = 'salary';
        }
        if(strtolower($title) == 'pengembalian'){
          $a = 'reimbursement';
        }
        if(strtolower($title) == 'pinjaman'){
          $a = 'loan';
        }
        if(strtolower($title) == 'tambah_pinjaman'){
          $a = 'add loan';
        }
        if(strtolower($title) == 'kasbon'){
          $a = 'cash advances';
        }
        if(strtolower($title) == 'laporan'){
          $a = 'report';
        }
        if(strtolower($title) == 'sebelum'){
          $a = 'before';
        }
        if(strtolower($title) == 'sesudah'){
          $a = 'after';
        }
        if(strtolower($title) == 'tambah_promosi'){
          $a = 'add promotion';
        }
        if(strtolower($title) == 'tambah_demosi'){
          $a = 'add demotion';
        }
        if(strtolower($title) == 'tambah_mutasi'){
          $a = 'add mutation';
        }
        if(strtolower($title) == 'is_jabatan'){
          $a = 'position must be different from before';
        }
        if(strtolower($title) == 'is_wilayah'){
          $a = 'the region must be different from before';
        }
        if(strtolower($title) == 'dokumen'){
          $a = 'Document';
        }
        if(strtolower($title) == 'masa_kerja'){
          $a = 'years of service';
        }
        if(strtolower($title) == 'usia'){
          $a = 'age';
        }
        if(strtolower($title) == 'posisi'){
          $a = 'position';
        }
        if(strtolower($title) == 'sanksi'){
          $a = 'penalty';
        }
        if(strtolower($title) == 'deskripsi'){
          $a = 'description';
        }
        if(strtolower($title) == 'kategori'){
          $a = 'category';
        }
        if(strtolower($title) == 'no_data'){
          $a = 'Data not found';
        }
        if(strtolower($title) == 'nomor'){
          $a = 'number';
        }
        if(strtolower($title) == 'tambah_bpjs'){
          $a = 'Add BPJS';
        }
        if(strtolower($title) == 'edit_bpjs'){
          $a = 'BPJS updates';
        }
        if(strtolower($title) == 'aset'){
          $a = 'asset';
        }
        if(strtolower($title) == 'daftar_aset'){
          $a = 'asset list';
        }
        if(strtolower($title) == 'alokasi'){
          $a = 'allocation';
        }
        if(strtolower($title) == 'alokasi_aset'){
          $a = 'asset allocation';
        }
        if(strtolower($title) == 'tambah_alokasi'){
          $a = 'Add allocation';
        }
        if(strtolower($title) == 'edit_alokasi'){
          $a = 'allocation updates';
        }
        if(strtolower($title) == 'pelanggaran_sanksi'){
          $a = 'infringement penalty';
        }
        if(strtolower($title) == 'seragam'){
          $a = 'uniform';
        }
        if(strtolower($title) == 'seragam_pegawai'){
          $a = 'employee uniform';
        }
        if(strtolower($title) == 'tambah_seragam'){
          $a = 'Add uniform';
        }
        if(strtolower($title) == 'edit_seragam'){
          $a = 'uniform updates';
        }
        if(strtolower($title) == 'tanggal_mulai'){
          $a = 'start date';
        }
        if(strtolower($title) == 'tanggal_selesai'){
          $a = 'end date';
        }
        if(strtolower($title) == 'tambah_cuti'){
          $a = 'Add leave';
        }
        if(strtolower($title) == 'edit_cuti'){
          $a = 'leave updates';
        }
        if(strtolower($title) == 'proses_cuti'){
          $a = "leave Process";
        }
        if(strtolower($title) == 'tambah_izin'){
          $a = 'add permission leave';
        }
        if(strtolower($title) == 'edit_izin'){
          $a = 'permission leave updates';
        }
        if(strtolower($title) == 'proses_izin'){
          $a = "permission leave Process";
        }
        if(strtolower($title) == 'tgl_salah'){
          $a = 'Invalid date';
        }
        if(strtolower($title) == 'sekolah'){
          $a = 'school';
        }
        if(strtolower($title) == 'tahun_lulus'){
          $a = 'graduation year';
        }
        if(strtolower($title) == 'ya'){
          $a = 'yes';
        }
        if(strtolower($title) == 'tidak'){
          $a = 'no';
        }
        if(strtolower($title) == 'batal'){
          $a = 'cencel';
        }
        if(strtolower($title) == 'tanggal_bergabung'){
          $a = 'join date';
        }
        if(strtolower($title) == 'jenis_kelamin'){
          $a = 'Gender';
        }
        if(strtolower($title) == 'keuntungan'){
          $a = 'benefit';
        }
        if(strtolower($title) == 'presensi'){
          $a = 'presence';
        }
        if(strtolower($title) == 'navigasi'){
          $a = 'Navigation';
        }
        if(strtolower($title) == 'keuangan'){
          $a = 'finance';
        }
        if(strtolower($title) == 'daftar_gaji'){
          $a = 'payroll';
        }
        if(strtolower($title) == 'berkas'){
          $a = 'file';
        }
        if(strtolower($title) == 'terbitkan'){
          $a = 'publish';
        }
        if(strtolower($title) == 'draf'){
          $a = 'draft';
        }
        if(strtolower($title) == 'dilihat'){
          $a = 'seen';
        }
        if(strtolower($title) == 'diunduh'){
          $a = 'downloaded';
        }
        if(strtolower($title) == 'judul'){
          $a = 'title';
        }
        if(strtolower($title) == 'lihat'){
          $a = 'view';
        }
        if(strtolower($title) == 'unduh'){
          $a = 'download';
        }
        if(strtolower($title) == 'jam_datang'){
          $a = 'coming time';
        }
        if(strtolower($title) == 'jam_pulang'){
          $a = 'go home time';
        }
        if(strtolower($title) == 'terlambat'){
          $a = 'late';
        }
        if(strtolower($title) == 'tepat_waktu'){
          $a = 'on time';
        }
        if(strtolower($title) == 'lokasi_datang'){
          $a = 'coming location';
        }
        if(strtolower($title) == 'lokasi_pulang'){
          $a = 'go home location';
        }
        if(strtolower($title) == 'status_datang'){
          $a = 'status coming';
        }
        if(strtolower($title) == 'status_pulang'){ 
          $a = 'go home status';
        }
        if(strtolower($title) == 'waktu_terlambat'){
          $a = 'time late';
        }
        if(strtolower($title) == 'titik_lokasi'){
          $a = 'location point';
        }
        if(strtolower($title) == 'jarak_datang'){
          $a = 'distance come';
        }
        if(strtolower($title) == 'jarak_pulang'){
          $a = 'go home distance';
        }
        if(strtolower($title) == 'jam_mulai_kerja'){
          $a = 'hours start work';
        }
        if(strtolower($title) == 'jam_selesai_kerja'){
          $a = 'hours finished work';
        }
        if(strtolower($title) == 'radius_maks'){
          $a = 'max radius';
        }
        if(strtolower($title) == 'info_presensi_lama'){
          $a = 'working hours from '.get_setting()->jam_mulai_kerja.' - '.get_setting()->jam_selesai_kerja.' and a max radius of '.get_setting()->max_radius.' meters';
        }
        if(strtolower($title) == 'info_presensi'){
          $a = 'Max radius of '.get_setting()->max_radius.' meters';
        }
        if(strtolower($title) == 'info_dinas'){
          $a = 'fuel price '.format_price(get_setting()->nominal_bbm);
        }
        if(strtolower($title) == 'info_hitung'){
          $a = 'legalitas dibuat oleh '.get_setting()->payroll_buat.' dan disetujui oleh '.get_setting()->payroll_setuju;
        }
        if(strtolower($title) == 'tanggal_keluar'){
          $a = 'resign date';
        }
        if(strtolower($title) == 'jabatan_terakhir'){
          $a = 'last position';
        }
        if(strtolower($title) == 'tambah_pengembalian'){
          $a = 'add reimbursement';
        }
        if(strtolower($title) == 'dari'){
          $a = 'from';
        }
        if(strtolower($title) == 'ke'){
          $a = 'to';
        }
        if(strtolower($title) == 'proses_pengembalian'){
          $a = "reimbursement process";
        }
        if(strtolower($title) == 'keperluan'){
          $a = "necessity";
        }
        if(strtolower($title) == 'asal'){
          $a = "origin";
        }
        if(strtolower($title) == 'tujuan'){
          $a = "destination";
        }
        if(strtolower($title) == 'berangkat'){
          $a = "leave";
        }
        if(strtolower($title) == 'sampai'){
          $a = "arrive";
        }
        if(strtolower($title) == 'lama_perjalanan'){
          $a = "length of journey";
        }
        if(strtolower($title) == 'jarak'){
          $a = "distance";
        }
        if(strtolower($title) == 'tanggal_pengajuan'){
          $a = "date of filing";
        }
        if(strtolower($title) == 'tanggal_acc'){
          $a = "approved date";
        }
        if(strtolower($title) == 'nominal_pinjaman'){
          $a = "loan nominal";
        }
        if(strtolower($title) == 'tanggal_angsuran'){
          $a = "installment date";
        }
        if(strtolower($title) == 'besaran_angsuran'){
          $a = "installment amount";
        }
        if(strtolower($title) == 'plafon'){
          $a = "ceiling";
        }
        if(strtolower($title) == 'nominal_acc'){
          $a = "approved nominal";
        }
        if(strtolower($title) == 'tanggal_pengembalian'){
          $a = "date of return";
        }
        if(strtolower($title) == 'akun'){
          $a = "account";
        }
        if(strtolower($title) == 'kirim_ke_email'){
          $a = "send to email";
        }
        if(strtolower($title) == 'buat_kata_sandi'){
          $a = "Generate Password";
        }
        if(strtolower($title) == 'pimpinan'){
          $a = "leader";
        }
        if(strtolower($title) == 'staf'){
          $a = "staff";
        }
        if(strtolower($title) == 'pegawai_baru'){
          $a = "new employee";
        }
        if(strtolower($title) == 'kehadiran_hari_ini'){
          $a = "attendance today";
        }
        if(strtolower($title) == 'pengembalian_hari_ini'){
          $a = 'reimbursement today';
        }
        if(strtolower($title) == 'pengembalian_tertunda'){
          $a = 'pending reimbursement';
        }
        if(strtolower($title) == 'menyetujui_penggantian'){
          $a = 'approve reimbursement';
        }
        if(strtolower($title) == 'email_use'){
          $a = 'email is used by another account';
        }
        if(strtolower($title) == 'ubah_level'){
          $a = 'change level';
        }
        if(strtolower($title) == 'aplikasi'){
          $a = 'application';
        }
        if(strtolower($title) == 'notifikasi'){
          $a = 'notification';
        }
        if(strtolower($title) == 'kirim_notifikasi'){
          $a = 'send notification';
        }
        if(strtolower($title) == 'kirim_notif_sekarang'){
          $a = 'send notification now';
        }
        if(strtolower($title) == 'kirim'){
          $a = 'send';
        }
        if(strtolower($title) == 'kirim_ulang_notif'){
          $a = 'resend notification';
        }
        if(strtolower($title) == 'notif_sukses'){
          $a = 'notification sent successfully';
        }
        if(strtolower($title) == 'notif_gagal'){
          $a = 'notification failed to send';
        }
        if(strtolower($title) == 'tanggal_angsuran_berakhir'){
          $a = 'installment end date';
        }
        if(strtolower($title) == 'gapok'){
          $a = 'basic salary';
        }
        if(strtolower($title) == 'hitung'){
          $a = 'calculate';
        }
        if(strtolower($title) == 'tunjangan'){
          $a = 'allowance';
        }
        if(strtolower($title) == 'tunjangan_jabatan'){
          $a = 'positional allowance';
        }
        if(strtolower($title) == 'jumlah_data'){
          $a = 'amount of data';
        }
        if(strtolower($title) == 'tahun'){
          $a = 'year';
        }
        if(strtolower($title) == 'nominal'){
          $a = 'amount';
        }
        if(strtolower($title) == 'harian'){
          $a = 'daily';
        }
        if(strtolower($title) == 'bulanan'){
          $a = 'monthly';
        }
        if(strtolower($title) == 'lembur'){
          $a = 'overtime';
        }
        if(strtolower($title) == 'kebijakan_direktur'){
          $a = 'director policy';
        }
        if(strtolower($title) == 'kebijakan'){
          $a = 'policy';
        }
        if(strtolower($title) == 'keterangan'){
          $a = 'description';
        }
        if(strtolower($title) == 'periode'){
          $a = 'period';
        }
        if(strtolower($title) == 'warning_calculate'){
          $a = 'are you sure to calculate the salary of this period?';
        }
        if(strtolower($title) == 'kembali'){
          $a = 'back';
        }
        if(strtolower($title) == 'detail_gaji'){
          $a = 'Salary Details';
        }
        if(strtolower($title) == 'slip_gaji'){
          $a = 'salary slip';
        }
        if(strtolower($title) == 'tugas'){
          $a = 'task';
        }
        if(strtolower($title) == 'tugas_dari'){
          $a = 'task from';
        }
        if(strtolower($title) == 'tugas_untuk'){
          $a = 'task for';
        }
        if(strtolower($title) == 'jam_kerja'){
          $a = 'working hours';
        }
        if(strtolower($title) == 'jam'){
          $a = 'hour';
        }
        if(strtolower($title) == 'mulai'){
          $a = 'start';
        }
        if(strtolower($title) == 'berakhir'){
          $a = 'end';
        }
        if(strtolower($title) == 'bonus_tahunan'){
          $a = 'yearly bonus';
        }
        if(strtolower($title) == 'tambah_pekerjaan'){
          $a = 'add work';
        }
        if(strtolower($title) == 'pekerjaan'){
          $a = 'work';
        }
        if(strtolower($title) == 'tanggal_selesai'){
          $a = 'date of completion';
        }
        if(strtolower($title) == 'ultah'){
          $a = 'birthday employee';
        }
        if(strtolower($title) == 'datang'){
          $a = 'come';
        }
        if(strtolower($title) == 'pulang'){
          $a = 'go home';
        }
      }else if($getDetail->user_bahasa == 'id'){
        if(strtolower($title) == 'dashboard'){
          $a = 'Beranda';
        }
        if(strtolower($title) == 'masterdata'){
          $a = 'Data master';
        }
        if(strtolower($title) == 'pegawai_daftar'){
          $a = 'Daftar Pegawai';
        }
        if(strtolower($title) == 'pegawai_resign'){
          $a = 'pengunduran diri';
        }
        if(strtolower($title) == 'resign'){
          $a = 'berhenti';
        }
        if(strtolower($title) == 'ijin'){
          $a = 'izin';
        }
        if(strtolower($title) == 'delete_title'){
          $a = 'Apakah yakin akan hapus data';
        }
        if(strtolower($title) == 'delete_text'){
          $a = 'Anda akan menghapus data dari database';
        }
        if(strtolower($title) == 'daftar2'){
          $a = 'daftar';
        }
        if(strtolower($title) == 'keluar2'){
          $a = 'keluar';
        }
        if(strtolower($title) == 'signout_title'){
          $a = 'Yakin anda akan keluar';
        }
        if(strtolower($title) == 'no_notif'){
          $a = 'Tidak Ada Notifikasi';
        }
        if(strtolower($title) == 'all_notif'){
          $a = 'Lihat Semua Notifikasi';
        }
        if(strtolower($title) == 'sel_pil'){
          $a = '--Pilih--';
        }
        if(strtolower($title) == 'nm_issu'){
          $a = 'Nama Suami / Istri';
        }
        if(strtolower($title) == 'keluarga_hub'){
          $a = 'Keluarga Yang Bisa Dihubungi';
        }
        if(strtolower($title) == 'ttl'){
          $a = "TTL";
        }
        if(strtolower($title) == 'setting'){
          $a = "pengaturan";
        }
        if(strtolower($title) == 'tx_pwd_edit'){
          $a = "masukkan kata sandi jika Anda ingin mengubah";
        }
        if(strtolower($title) == 'save_sukses'){
          $a = "Data berhasil disimpan";
        }
        if(strtolower($title) == 'save_gagal'){
          $a = "Data gagal disimpan";
        }
        if(strtolower($title) == 'modul_akses'){
          $a = "Anda tidak memiliki akses untuk modul ini";
        }
        if(strtolower($title) == 'hapus_sukses'){
          $a = "Data berhasil dihapus";
        }
        if(strtolower($title) == 'hapus_gagal'){
          $a = "Data gagal dihapus";
        }
        if(strtolower($title) == 'form_cuti'){
          $a = 'formulir cuti';
        }
        if(strtolower($title) == 'dinas'){
          $a = 'perjalanan dinas';
        }
        if(strtolower($title) == 'is_jabatan'){
          $a = 'jabatan harus berbeda dari sebelumnya';
        }
        if(strtolower($title) == 'is_wilayah'){
          $a = 'wilayah harus berbeda dari sebelumnya';
        }
        if(strtolower($title) == 'nilai'){
          $a = "transkrip nilai";
        }
        if(strtolower($title) == 'no_data'){
          $a = 'Data tidak ditemukan';
        }
        if(strtolower($title) == 'tambah_bpjs'){
          $a = 'Tambah BPJS';
        }
        if(strtolower($title) == 'tgl_salah'){
          $a = 'Tanggal tidak benar';
        }
        if(strtolower($title) == 'ijasah'){
          $a = "ijazah";
        }
        if(strtolower($title) == 'info_presensi_lama'){
          $a = 'jam kerja dari jam '.get_setting()->jam_mulai_kerja.' - '.get_setting()->jam_selesai_kerja.' dan maks radius '.get_setting()->max_radius.' meter';
        }
        if(strtolower($title) == 'info_presensi'){
          $a = 'Maks radius '.get_setting()->max_radius.' meter';
        }
        if(strtolower($title) == 'info_dinas'){
          $a = 'harga BBM '.format_price(get_setting()->nominal_bbm);
        }
        if(strtolower($title) == 'info_hitung'){
          $a = 'legalitas dibuat oleh '.get_setting()->payroll_buat.' dan disetujui oleh '.get_setting()->payroll_setuju;
        }
        if(strtolower($title) == 'app_version'){
          $a = "Versi Aplikasi";
        }
        if(strtolower($title) == 'os_version'){
          $a = "Versi Os";
        }
        if(strtolower($title) == 'device_model'){
          $a = "model perangkat";
        }
        if(strtolower($title) == 'device_manufacturer'){
          $a = "produsen perangkat";
        }
        if(strtolower($title) == 'last_login_at'){
          $a = "login terakhir";
        }
        if(strtolower($title) == 'email_use'){
          $a = 'email digunakan akun lain';
        }
        if(strtolower($title) == 'over_time'){
          $a = 'melebihi waktu';
        }
        if(strtolower($title) == 'notif_sukses'){
          $a = 'notifikasi berhasil dikirim';
        }
        if(strtolower($title) == 'notif_gagal'){
          $a = 'notifikasi gagal dikirim';
        }
        if(strtolower($title) == 'gapok'){
          $a = 'gaji pokok';
        }
        if(strtolower($title) == 'warning_calculate'){
          $a = 'apakah anda yakin akan menghitung gaji periode ini?';
        }
        if(strtolower($title) == 'ultah'){
          $a = 'pegawai ulang tahun';
        }
        if(strtolower($title) == 'pegawai_new'){
          $a = 'pegawai baru';
        }
      }
    }

    return ucwords(str_replace('_', ' ', $a));
  }
}
?>