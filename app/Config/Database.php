<?php
namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;
    public string $defaultGroup = 'default';

    // Default database connection (placeholder)
    public $default = [];

    // CRM_ARS connection group (placeholder)
    public $crm_ars = [];

    // CRM_WEP connection group (placeholder)
    public $crm_wep = [];

    public function __construct()
    {
        parent::__construct();

        // Initialize default connection dynamically
        $this->default = [
            'DSN'      => '',
            'hostname' => env('database.default.hostname', 'localhost'),
            'username' => env('database.default.username', 'postgres'),
            'password' => env('database.default.password', ''),
            'database' => env('database.default.database', ''),
            'DBDriver' => env('database.default.DBDriver', 'Postgre'),
            'DBPrefix' => env('database.default.DBPrefix', ''),
            'pConnect' => false,
            'DBDebug'  => (ENVIRONMENT !== 'production'),
            'charset'  => 'utf8',
            'DBCollat' => 'utf8_general_ci',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => false,
            'failover' => [],
            'port'     => env('database.default.port', 5432),
        ];

        // Initialize crm_ars dynamically
        $this->crm_ars = [
            'DSN'      => '',
            'hostname' => env('database.crm_ars.hostname', 'localhost'),
            'username' => env('database.crm_ars.username', 'postgres'),
            'password' => env('database.crm_ars.password', ''),
            'database' => env('database.crm_ars.database', ''),
            'DBDriver' => env('database.crm_ars.DBDriver', 'Postgre'),
            'DBPrefix' => env('database.crm_ars.DBPrefix', ''),
            'pConnect' => false,
            'DBDebug'  => (ENVIRONMENT !== 'production'),
            'charset'  => 'utf8',
            'DBCollat' => 'utf8_general_ci',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => false,
            'failover' => [],
            'port'     => env('database.crm_ars.port', 5432),
        ];

        // Initialize crm_wep dynamically
        $this->crm_wep = [
            'DSN'      => '',
            'hostname' => env('database.crm_wep.hostname', 'localhost'),
            'username' => env('database.crm_wep.username', 'postgres'),
            'password' => env('database.crm_wep.password', ''),
            'database' => env('database.crm_wep.database', ''),
            'DBDriver' => env('database.crm_wep.DBDriver', 'Postgre'),
            'DBPrefix' => env('database.crm_wep.DBPrefix', ''),
            'pConnect' => false,
            'DBDebug'  => (ENVIRONMENT !== 'production'),
            'charset'  => 'utf8',
            'DBCollat' => 'utf8_general_ci',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => false,
            'failover' => [],
            'port'     => env('database.crm_wep.port', 5432),
        ];
    }
}