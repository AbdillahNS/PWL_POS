<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LevelModel;

class LevelController extends Controller
{
    public function index() {
        return LevelModel::all();
    }
    public function store(Request $request) {
        $level = LevelModel::create($request->all());
        return response()->json($level, 201);
    }
    public function show(levelModel $level) {
        return LevelModel::find($level);
    }
    public function update(Request $request, levelModel $level) {
        $level->update($request->all());
        return LevelModel::find($level);
    }
    public function destroy(levelModel $level) {
        $level->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data terhapus'
        ]);
    }
}