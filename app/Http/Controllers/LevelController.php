<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\LevelModel;
use Yajra\DataTables\Facades\DataTables;

class LevelController extends Controller
{
    public function index()
    {
        $breadcrumb = (object)[
            'title' => 'Daftar Level',
            'list' => ['Home', 'Level']
        ];

        $page = (object)[
            'title' => 'Daftar Level yang ada'
        ];

        $activeMenu = 'level'; // Set menu yang sedang aktif

        $level = LevelModel::all(); // ambil data level untuk filter level
        return view('level.index', ['breadcrumb' => $breadcrumb, 'page' => $page, 'level' => $level, 'activeMenu' => $activeMenu]);
    }

    public function list(Request $request)
    {
        $levels = LevelModel::select('level_id', 'level_kode', 'level_nama');

        // 
        if ($request->level_id) {
            $levels->where('level_id', $request->level_id);
        }



        return DataTables::of($levels)
            ->addIndexColumn() // Menambahkan kolom index / no urut (default nmaa kolom: DT_RowINdex)
            ->addColumn('aksi', function ($level) {
                $btn = '<a href="' . url('/level/' . $level->level_id) . '" class="btn btn-info btn-sm">Detail</a>';
                $btn .= '<a href="' . url('/level/' . $level->level_id . '/edit') . '" class="btn btn-warning btn-sm">Edit</a> ';
                $btn .= '<form class="d-inline-block" method="POST" action="' . url('/level/' . $level->level_id) . '">' . csrf_field() . method_field('DELETE')
                    . '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Apakah Anda yakit menghapus data 
                ini?\');">Hapus</button></form>';
                return $btn;
            })

            ->rawColumns(['aksi'])
            ->make(true);
    }
    
    // public function index()
    // {
    //     //DB::insert('insert into m_level(level_kode, level_nama, created_at) values(?, ?, ?)', ['CUS', 'Pelanggan', now()]);
    //     //return 'insert data baru berhasil';

    //     //$row = DB::update('update m_level set level_nama = ? where level_kode = ?', ['Customers', 'CUS']);
    //     //return 'update data berhasil. Jumlah data yang diupdate : ' . $row. ' baris';

    //     //$row = DB::delete('delete from m_level where level_kode = ?', ['CUS']);
    //     //return 'delete data berhasil. Jumlah data yang dihapus : ' . $row. ' baris';

    //     $data = DB::select('select * from m_level');
    //     return view('level', ['data' => $data]);
    //}
}
