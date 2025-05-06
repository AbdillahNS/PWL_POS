<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PenjualanModel;
use Illuminate\Support\Facades\Validator;

class PenjualanController extends Controller
{
    public function index()
    {
        return PenjualanModel::with(['user', 'penjualan_detail'])->get();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:m_user,user_id',
            'pembeli' => 'required|string',
            'penjualan_kode' => 'required|string|unique:t_penjualan,penjualan_kode',
            'tanggal_penjualan' => 'required|date',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Upload image
        $imageName = $request->file('image')->hashName();
        $request->file('image')->storeAs('public/posts', $imageName);

        // Simpan data penjualan
        $penjualan = PenjualanModel::create([
            'user_id' => $request->user_id,
            'pembeli' => $request->pembeli,
            'penjualan_kode' => $request->penjualan_kode,
            'tanggal_penjualan' => $request->tanggal_penjualan,
            'image' => $imageName,
        ]);

        return response()->json([
            'success' => true,
            'data' => $penjualan,
        ], 201);
    }

    public function show(PenjualanModel $penjualan)
    {
        return response()->json($penjualan->load(['user', 'penjualan_detail']));
    }

    public function update(Request $request, PenjualanModel $penjualan)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|exists:m_user,user_id',
            'pembeli' => 'sometimes|string',
            'penjualan_kode' => 'sometimes|string|unique:t_penjualan,penjualan_kode,' . $penjualan->penjualan_id . ',penjualan_id',
            'tanggal_penjualan' => 'sometimes|date',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Ganti image jika ada
        if ($request->hasFile('image')) {
            $imageName = $request->file('image')->hashName();
            $request->file('image')->storeAs('public/posts', $imageName);
            $penjualan->image = $imageName;
        }

        // Update data lainnya
        $penjualan->fill($request->except('image'))->save();

        return response()->json([
            'success' => true,
            'data' => $penjualan,
        ]);
    }

    public function destroy(PenjualanModel $penjualan)
    {
        $penjualan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data penjualan berhasil dihapus.',
        ]);
    }
}