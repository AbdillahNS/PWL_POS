<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LevelModel;
use App\Models\UserModel;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login()
    {
        if(Auth::check()) { //jika sudah login, maka redirect ke halaman home
            return redirect('/');
        }
        return view('auth.login');
    }

    public function postlogin(Request $request)
    {
        if($request->ajax() || $request->wantsJson()) {
            $credentials = $request->only('username', 'password');

            if (Auth::attempt($credentials)) {
                return response()->json([
                   'status'    => true,
                   'message'   => 'Login Berhasil',
                   'redirect'  => url('/')
                ]);
            }
            return response()->json([
                'status'    => false,
                'message'   => 'Login Gagal'
            ]);
        }
        return redirect('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('login');
    }

    public function register()
    {
        $level = LevelModel::all();
        return view('auth.register', compact('level'));
    }

    public function postRegister(Request $request)
    {
    $valid_levels = LevelModel::whereIn('level_nama', ['Manager', 'Staff/Kasir']) // Kusu untuk manager dan staff
                              ->pluck('level_id')
                              ->toArray();

    $validator = Validator::make($request->all(), [
        'level_id' => ['required', 'in:'.implode(',', $valid_levels)], // Validasi level_id
        'username' => 'required|unique:m_user,username|min:3|max:20',
        'nama' => 'required|min:3|max:100',
        'password' => 'required|min:6|max:20',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validasi gagal',
            'errors' => $validator->errors()
        ], 422);
    }

    UserModel::create([
        'level_id' => $request->level_id,
        'username' => $request->username,
        'nama' => $request->nama,
        'password' => bcrypt($request->password),
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Registrasi berhasil! Silakan login.'
    ]);
    }
}