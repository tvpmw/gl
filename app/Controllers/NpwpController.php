<?php

namespace App\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class NpwpController extends BaseController
{
    private $client;
    
    public function __construct()
    {
        helper(['my_helper']);
        
        $this->client = new Client([
            'base_uri' => getenv('NPWP_API_BASE_URL')
        ]);
    }

    public function index()
    {
        return view('npwp/index');
    }

    public function checkSingle()
    {
        $npwp = $this->request->getPost('npwp');
        
        if (!preg_match('/^\d{16}$/', $npwp)) {
            return $this->response->setJSON(['error' => 'NPWP harus 16 digit angka']);
        }

        try {
            $method = 'GET';
            $path = '/v2/klikpajak/v1/npwp/inquiry';
            $queryParam = "?npwp={$npwp}";
            $headers = array_merge($this->generateHeaders($method, $path . $queryParam), [
                'X-Idempotency-Key' => uniqid()
            ]);

            $request = new Request($method, getenv('NPWP_API_BASE_URL') . $path . $queryParam, $headers);
            $response = $this->client->send($request);
            
            return $this->response->setJSON(json_decode($response->getBody()));
        } catch (\Exception $e) {
            if ($e->hasResponse()) {
                $errorBody = json_decode($e->getResponse()->getBody());
                if (isset($errorBody->message)) {
                    return $this->response->setJSON(['error' => 'NPWP Number is invalid']);
                }
            }
            
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }
    }

    public function checkBulk()
    {
        $rawInput = $this->request->getPost('npwp_list');
        
        $npwpList = array_filter(
            preg_split('/[\r\n,]+/', $rawInput),
            function($npwp) {
                return !empty(trim($npwp));
            }
        );

        $results = [];
        foreach ($npwpList as $npwp) {
            $npwp = trim($npwp);
            
            if (!preg_match('/^\d{16}$/', $npwp)) {
                $results[] = [
                    'npwp' => $npwp,
                    'error' => 'NPWP harus 16 digit angka'
                ];
                continue;
            }

            try {
                $method = 'GET';
                $path = '/v2/klikpajak/v1/npwp/inquiry';
                $queryParam = "?npwp={$npwp}";
                $headers = array_merge($this->generateHeaders($method, $path . $queryParam), [
                    'X-Idempotency-Key' => uniqid()
                ]);

                $request = new Request($method, getenv('NPWP_API_BASE_URL') . $path . $queryParam, $headers);
                $response = $this->client->send($request);
                $responseData = json_decode($response->getBody(), true);
                $responseData['npwp'] = $npwp;
                $results[] = $responseData;
            } catch (\Exception $e) {
                if ($e->hasResponse()) {
                    $errorBody = json_decode($e->getResponse()->getBody());
                    if (isset($errorBody->message)) {
                        $results[] = [
                            'npwp' => $npwp,
                            'error' => $errorBody->message
                        ];
                        continue;
                    }
                }
                
                $results[] = [
                    'npwp' => $npwp,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $this->response->setJSON($results);
    }

    public function checkNitku()
    {
        $npwp = $this->request->getPost('npwp');
        
        if (!preg_match('/^\d{16}$/', $npwp)) {
            return $this->response->setJSON(['error' => 'NPWP harus 16 digit angka']);
        }

        try {
            $method = 'GET';
            $path = '/v2/klikpajak/v1/npwp/inquiry';
            $queryParam = "?npwp={$npwp}";
            $headers = array_merge($this->generateHeaders($method, $path . $queryParam), [
                'X-Idempotency-Key' => uniqid()
            ]);

            $request = new Request($method, getenv('NPWP_API_BASE_URL') . $path . $queryParam, $headers);
            $response = $this->client->send($request);
            $inquiryResponse = json_decode($response->getBody(), true);

            if (!isset($inquiryResponse['data']['name'])) {
                return $this->response->setJSON([
                    'error' => 'Nama wajib pajak tidak ditemukan',
                    'response' => $inquiryResponse
                ]);
            }

            $taxpayer_name = $inquiryResponse['data']['name'];

            $path = '/v2/klikpajak/v1/npwp/latest';
            $queryParam = "?nik_npwp_nitku={$npwp}&taxpayer_name=" . urlencode($taxpayer_name);
            $headers = array_merge($this->generateHeaders($method, $path . $queryParam), [
                'X-Idempotency-Key' => uniqid()
            ]);

            $request = new Request($method, getenv('NPWP_API_BASE_URL') . $path . $queryParam, $headers);
            $response = $this->client->send($request);
            
            return $this->response->setJSON(json_decode($response->getBody()));
        } catch (\Exception $e) {
            if ($e->hasResponse()) {
                $errorBody = json_decode($e->getResponse()->getBody());
                if (isset($errorBody->message)) {
                    return $this->response->setJSON(['error' => $errorBody->message]);
                }
            }
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }
    }

    public function apiCheckSingle()
    {
        $npwp = $this->request->getGet('npwp');
        
        if (!preg_match('/^\d{16}$/', $npwp)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'NPWP harus 16 digit angka']);
        }

        try {
            $method = 'GET';
            $path = '/v2/klikpajak/v1/npwp/inquiry';
            $queryParam = "?npwp={$npwp}";
            $headers = array_merge($this->generateHeaders($method, $path . $queryParam), [
                'X-Idempotency-Key' => uniqid()
            ]);

            $request = new Request($method, getenv('NPWP_API_BASE_URL') . $path . $queryParam, $headers);
            $response = $this->client->send($request);
            
            return $this->response->setJSON(json_decode($response->getBody()));
        } catch (\Exception $e) {
            $errorMessage = "NPWP number is invalid";
            $errorDetails = null;
            
            if ($e->hasResponse()) {
                $responseBody = json_decode($e->getResponse()->getBody());
                if (isset($responseBody->code) && isset($responseBody->message)) {
                    $errorDetails = [
                        'code' => $responseBody->code,
                        'message' => $responseBody->message
                    ];
                }
            }
            
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'error' => $errorMessage,
                    'details' => $errorDetails
                ]);
        }
    }

    public function apiCheckBulk()
    {
        $npwpList = $this->request->getGet('npwp');
        
        $npwpArray = explode(',', $npwpList);
        
        $invalidNpwp = array_filter($npwpArray, function ($npwp) {
            return !preg_match('/^\d{16}$/', $npwp);
        });

        if (!empty($invalidNpwp)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'error' => 'Semua NPWP harus 16 digit angka', 
                    'invalid_npwp' => $invalidNpwp
                ]);
        }

        $results = [];

        foreach ($npwpArray as $npwp) {
            try {
                $method = 'GET';
                $path = '/v2/klikpajak/v1/npwp/inquiry';
                $queryParam = "?npwp={$npwp}";
                $headers = array_merge($this->generateHeaders($method, $path . $queryParam), [
                    'X-Idempotency-Key' => uniqid()
                ]);

                $request = new Request($method, getenv('NPWP_API_BASE_URL') . $path . $queryParam, $headers);
                $response = $this->client->send($request);

                $results[$npwp] = [
                    'npwp' => $npwp,
                    'response' => json_decode($response->getBody(), true)
                ];
            } catch (\Exception $e) {
                $errorMessage = "NPWP number is invalid";
                $errorDetails = null;
                
                if ($e->hasResponse()) {
                    $responseBody = json_decode($e->getResponse()->getBody());
                    if (isset($responseBody->code) && isset($responseBody->message)) {
                        $errorDetails = [
                            'code' => $responseBody->code,
                            'message' => $responseBody->message
                        ];
                    }
                }
                
                $results[$npwp] = [
                    'npwp' => $npwp,
                    'error' => $errorMessage,
                    'details' => $errorDetails
                ];
            }
        }

        return $this->response->setJSON($results);
    }

    private function generateHeaders($method, $pathWithQueryParam)
    {
        $datetime = Carbon::now()->toRfc7231String();
        $request_line = "{$method} {$pathWithQueryParam} HTTP/1.1";
        $payload = implode("\n", ["date: {$datetime}", $request_line]);
        $digest = hash_hmac('sha256', $payload, getenv('NPWP_API_CLIENT_SECRET'), true);
        $signature = base64_encode($digest);

        return [
            'Content-Type' => 'application/json',
            'Date' => $datetime,
            'Authorization' => "hmac username=\"" . getenv('NPWP_API_CLIENT_ID') . "\", algorithm=\"hmac-sha256\", headers=\"date request-line\", signature=\"{$signature}\""
        ];
    }
}