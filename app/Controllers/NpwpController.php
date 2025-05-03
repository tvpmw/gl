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
        
        // Initialize Guzzle client
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
        
        // Validate NPWP
        if (!preg_match('/^\d{16}$/', $npwp)) {
            return $this->response->setJSON(['error' => 'NPWP harus 16 digit angka']);
        }

        try {
            // Set method, path, and query parameters
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
            return $this->response->setJSON([
                'error' => $e->getMessage(),
                'details' => $e->hasResponse() ? json_decode($e->getResponse()->getBody()) : null
            ]);
        }
    }

    public function checkBulk()
    {
        $rawInput = $this->request->getPost('npwp_list');
        
        // Clean and split input
        $npwpList = array_filter(
            preg_split('/[\r\n,]+/', $rawInput),
            function($npwp) {
                return !empty(trim($npwp));
            }
        );

        $results = [];
        foreach ($npwpList as $npwp) {
            $npwp = trim($npwp);
            
            // Validate NPWP
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
                $results[] = [
                    'npwp' => $npwp,
                    'error' => $e->getMessage(),
                    'details' => $e->hasResponse() ? json_decode($e->getResponse()->getBody()) : null
                ];
            }
        }

        return $this->response->setJSON($results);
    }

    public function checkNitku()
    {
        $npwp = $this->request->getPost('npwp');
        
        // Validate NPWP
        if (!preg_match('/^\d{16}$/', $npwp)) {
            return $this->response->setJSON(['error' => 'NPWP harus 16 digit angka']);
        }

        try {
            // Step 1: Inquiry to get taxpayer name
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

            // Step 2: Check NITKU
            $path = '/v2/klikpajak/v1/npwp/latest';
            $queryParam = "?nik_npwp_nitku={$npwp}&taxpayer_name=" . urlencode($taxpayer_name);
            $headers = array_merge($this->generateHeaders($method, $path . $queryParam), [
                'X-Idempotency-Key' => uniqid()
            ]);

            $request = new Request($method, getenv('NPWP_API_BASE_URL') . $path . $queryParam, $headers);
            $response = $this->client->send($request);
            
            return $this->response->setJSON(json_decode($response->getBody()));
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage(),
                'details' => $e->hasResponse() ? json_decode($e->getResponse()->getBody()) : null
            ]);
        }
    }

    public function apiCheckSingle()
    {
        // Get NPWP from query string
        $npwp = $this->request->getGet('npwp');
        
        // Validate NPWP
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
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'error' => $e->getMessage(),
                    'details' => $e->hasResponse() ? json_decode($e->getResponse()->getBody()) : null
                ]);
        }
    }

    public function apiCheckBulk()
    {
        // Get NPWP list from query string
        $npwpList = $this->request->getGet('npwp');
        
        // Split NPWPs if more than one
        $npwpArray = explode(',', $npwpList);
        
        // Validate each NPWP
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
                $results[$npwp] = [
                    'npwp' => $npwp,
                    'error' => $e->getMessage(),
                    'details' => $e->hasResponse() ? json_decode($e->getResponse()->getBody(), true) : null
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