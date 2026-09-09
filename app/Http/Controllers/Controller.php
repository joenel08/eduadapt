<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->check()) {
                $role = auth()->user()->role; // 'admin', 'student', 'teacher'
                view()->share('roleViewPath', $role); // e.g. 'admin'
            }
            return $next($request);
        });
    }
}
