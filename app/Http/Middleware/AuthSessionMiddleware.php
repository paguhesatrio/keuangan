<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;


class AuthSessionMiddleware 
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('authenticated_user')) {
            return redirect('/admin')->with('loginError', 'Silakan login terlebih dahulu.');
        }

        // Jika pengguna bukan admin dan mencoba mengakses halaman admin, redirect ke /home
        if ($request->is('admin*') && session('role') !== 'admin') {
            return redirect('/home')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
