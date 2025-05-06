<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BarangModel;
use Illuminate\Support\Facades\Validator;

class BarangController extends Controller
{
    public function index()
    {
        return BarangModel::all();
    }

    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'kategori_id' => 'required|exists:m_kategori,kategori_id',
            'barang_kode' => 'required|string',
            'barang_nama' => 'required|string',
            'harga_beli' => 'required|numeric',
            'harga_jual' => 'required|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Upload file
        $imageName = $request->file('image')->hashName();
        $request->file('image')->storeAs('public/posts', $imageName);

        $barang = BarangModel::create([
            'kategori_id' => $request->kategori_id,
            'barang_kode' => $request->barang_kode,
            'barang_nama' => $request->barang_nama,
            'harga_beli' => $request->harga_beli,
            'harga_jual' => $request->harga_jual,
            'image' => $imageName,
        ]);

        return response()->json($barang, 201);
    }

    public function show(BarangModel $barang)
    {
        return response()->json($barang);
    }

    public function update(Request $request, BarangModel $barang)
    {
        // Validasi
        $validator = Validator::make($request->all(), [
            'kategori_id' => 'sometimes|exists:m_kategori,kategori_id',
            'barang_kode' => 'sometimes|string',
            'barang_nama' => 'sometimes|string',
            'harga_beli' => 'sometimes|numeric',
            'harga_jual' => 'sometimes|numeric',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Cek dan update image jika ada
        if ($request->hasFile('image')) {
            $imageName = $request->file('image')->hashName();
            $request->file('image')->storeAs('public/posts', $imageName);
            $barang->image = $imageName;
        }

        // Update data lain
        $barang->fill($request->except('image'))->save();

        return response()->json($barang);
    }

    public function destroy(BarangModel $barang)
    {
        $barang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data terhapus',
        ]);
    }
}