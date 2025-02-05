<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;


class AuthSessionMiddleware 
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('authenticated_user')) {
            return redirect('/')->with('loginError', 'Silakan login terlebih dahulu.');
        }

        return $next($request);
    }
}
