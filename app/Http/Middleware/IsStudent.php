<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsStudent
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role === 'student') {
            return $next($request);
        }
        abort(403, 'Access denied – student only.');
    }
}