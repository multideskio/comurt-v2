<?php

namespace App\Models;

use CodeIgniter\Model;

class MagicLinkModel extends Model
{
    protected $table = 'magiclinks';
    protected $primaryKey = 'id';
    protected $allowedFields = ['email', 'link_url', 'used', 'expiration'];
    protected $useTimestamps = false;
}