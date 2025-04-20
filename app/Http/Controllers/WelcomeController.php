<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class WelcomeController extends Controller
{
    public function index()
    {
        $breadcrumb = (object) [
            'title' => 'Selamat Datang',
            'list' => ['Home', 'Welcome']
        ];

        $activeMenu = 'dashboard';

        return view('welcome', ['breadcrumb' => $breadcrumb, 'activeMenu' => $activeMenu]);
    }

    // Ubah foto profil
    public function update_profil(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'foto_profil' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal: ' . $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('foto_profil')) {
            $file = $request->file('foto_profil');

            // Membuat nama unik untuk file
            $nama_file = 'avatar_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/avatars', $nama_file);

            // Menghasilkan URL file
            $foto_url = asset('storage/avatars/' . $nama_file);

            // Mengupdate foto profil di session
            session(['avatar_temp' => $foto_url]);

            return response()->json([
                'status' => true,
                'message' => 'Foto berhasil diperbarui.',
                'foto_url' => $foto_url
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'File foto tidak ditemukan.'
        ], 400);
    }
}