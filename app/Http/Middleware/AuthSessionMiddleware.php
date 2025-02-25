<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthSessionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Periksa apakah pengguna sudah login
        if (!session()->has('authenticated_user')) {
            return redirect('/')->with('loginError', 'Silakan login terlebih dahulu.');
        }
       
        // Periksa role pengguna
        $role = session()->get('role', '');

        // Admin hanya bisa mengakses '/admin'
        if ($role === 'admin' && !$request->is('admin')) {
            return redirect('/admin')->with('error', 'Admin hanya bisa mengakses halaman admin.');
        }

        // Bangsal hanya bisa mengakses '/home'
        if ($role === 'bangsal' && !$request->is('home')) {
            return redirect('/home')->with('error', 'Bangsal hanya bisa mengakses halaman home.');
        }

        return $next($request);
    }
}
