<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Traits\Loggable;
use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;

abstract class AdminBaseController extends Controller
{
    use ApiResponse, Loggable;

    public function __construct()
    {
        parent::__construct();
        $this->middleware(['sanctum.check', 'role:admin|manager']);
    }
}
