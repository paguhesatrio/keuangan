<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index()
    {
        return view('login', [
            "title" => 'Login',
        ]);
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'nik' => 'required',
            'password' => 'required'
        ]);

        // Daftar pengguna yang valid
        $validUsers = [
            ['nik' => 'igd', 'password' => 'igd.rspmk', 'kode_bangsal' => 'IGDK', 'role' => 'bangsal'],
            ['nik' => 'isonegatif', 'password' => 'isonegatif.rspmk', 'kode_bangsal' => 'RITN', 'role' => 'bangsal'],
            ['nik' => 'isopositif', 'password' => 'isopositif.rspmk', 'kode_bangsal' => 'RITNN', 'role' => 'bangsal'],
            ['nik' => 'icu', 'password' => 'icu.rspmk', 'kode_bangsal' => 'ICU', 'role' => 'bangsal'],
            ['nik' => 'nicu', 'password' => 'nicu.rspmk', 'kode_bangsal' => 'NICU', 'role' => 'bangsal'],
            ['nik' => 'vip', 'password' => 'vip.rspmk', 'kode_bangsal' => 'VIP', 'role' => 'bangsal'],
            ['nik' => 'nifas', 'password' => 'nifas.rspmk', 'kode_bangsal' => 'RB', 'role' => 'bangsal'],
            ['nik' => 'pd1', 'password' => 'pd1.rspmk', 'kode_bangsal' => 'PD1', 'role' => 'bangsal'],
            ['nik' => 'pd2', 'password' => 'pd2.rspmk', 'kode_bangsal' => 'PD2', 'role' => 'bangsal'],
            ['nik' => 'perina', 'password' => 'perina.rspmk', 'kode_bangsal' => 'PERI', 'role' => 'bangsal'],
            ['nik' => 'anak', 'password' => 'anak.rspmk', 'kode_bangsal' => 'ZA', 'role' => 'bangsal'],
            ['nik' => 'bedah', 'password' => 'bedah.rspmk', 'kode_bangsal' => 'ZB', 'role' => 'bangsal'],
            ['nik' => 'admin', 'password' => '12', 'kode_bangsal' => ' ', 'role' => 'admin'] // Admin tanpa kode bangsal
        ];

        foreach ($validUsers as $user) {
            if ($credentials['nik'] === $user['nik'] && $credentials['password'] === $user['password']) {
                // Simpan informasi pengguna ke dalam session
                session([
                    'authenticated_user' => $user['nik'],
                    'kode_bangsal' => $user['kode_bangsal'],
                    'role' => $user['role'], // Menyimpan peran pengguna
                ]);

                if ($user['role'] === 'admin') {
                    return redirect()->intended('/admin');
                }

                return redirect()->intended('/home');
            }
        }

        // Jika login gagal
        return back()->with('loginError', 'Login Failed!!!');
    }


    public function logout(Request $request)
    {
        session()->forget('authenticated_user');
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/');
    }
}
