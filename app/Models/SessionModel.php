<?php

namespace App\Models;

use CodeIgniter\Model;

class SessionModel extends Model
{
    protected $table = 'ci_sessions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'ip_address', 'timestamp', 'data'];
    protected $returnType = 'array';
}
