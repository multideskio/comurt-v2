<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class DocsController extends BaseController
{
    public function index()
    {
        //
        return view('docs');
    }
}
