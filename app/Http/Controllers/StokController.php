<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarangModel;
use App\Models\StokModel;
use App\Models\UserModel;
use App\Models\SupplierModel;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StokController extends Controller
{
    public function index()
    {
        $breadcrumb = (object)[ 
            'title' => 'Daftar Stok Barang',
            'list' => ['Home','Stok Barang']
        ];

        $page = (object)[
            'title' => 'Daftar Stok Barang yang terdaftar dalam sistem'
        ];

        $activeMenu = 'stok'; // Set menu yang sedang aktif

        $user = UserModel::all(); // ambil data level untuk filter level
        $barang = BarangModel::all();
        $supplier = SupplierModel::all();
        return view('stok.index',['breadcrumb'=>$breadcrumb,'page'=>$page,'user'=>$user, 'barang'=>$barang, 'supplier'=>$supplier, 'activeMenu'=>$activeMenu]);
    }

    public function list(Request $request)
    {
        $stoks = StokModel::select('stok_id', 'supplier_id', 'barang_id', 'user_id', 'stok_tanggal', 'stok_jumlah')
            ->with('supplier')
            ->with('barang')
            ->with('user');
        
        if ($request->supplier_id) {
            $stoks->where('supplier_id', $request->supplier_id);
        } 
        
        if ($request->barang_id) {
            $stoks->where('barang_id', $request->barang_id);
        }

        return DataTables::of($stoks)
            ->addIndexColumn() // Menambahkan kolom index / no urut (default nmaa kolom: DT_RowINdex)
            ->addColumn('aksi', function ($stok) {
                $btn  = '<a href="' . url('/stok/' . $stok->stok_id) . '" class="btn btn-info btn-sm">Detail</a> ';
                $btn .= '<button onclick="modalAction(\''.url('/stok/' . $stok->stok_id . '/edit_ajax').'\')" class="btn btn-warning btn-sm">Edit</button> '; 
                $btn .= '<button onclick="modalAction(\''.url('/stok/' . $stok->stok_id . '/delete_ajax').'\')"  class="btn btn-danger btn-sm">Hapus</button> '; 
                return $btn;
            })

            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function show(string $id)
    {
        $stok = StokModel::with(['user','barang'])->find($id);

        $breadcrumb = (object)[
            'title' => 'Detail Stok',
            'list' => ['Home','Stok','Detail']
        ];

        $page = (object)[
            'title' => 'Detail Stok'
        ];

        $activeMenu = 'stok';
        return view('stok.show',['breadcrumb' => $breadcrumb, 'page'=> $page, 'stok'=>$stok, 'activeMenu' => $activeMenu]);
    }

    public function create_ajax(){
        $supplier = SupplierModel::select('supplier_id', 'supplier_nama')->get();
        $barang = BarangModel::select('barang_id', 'barang_nama')->get();
        $user = UserModel::select('user_id', 'username')->get();
        
        return view('stok.create_ajax', compact('supplier', 'barang','user'));
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'supplier_id' => 'required|integer',
                'barang_id' => 'required|integer',
                'stok_tanggal' => 'nullable|date',
                'stok_jumlah' => 'required|integer|min:1',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal',
                    'msgField' => $validator->errors()
                ]);
            }

            try {
                $data = $request->all();
                $data['user_id'] = auth()->id(); // Pastikan user sudah login
                $data['stok_tanggal'] = $data['stok_tanggal'] ?: now()->toDateString(); // Default ke tanggal hari ini

                // Menyimpan data
                StokModel::create($data); 

                return response()->json([
                    'status' => true, 
                    'message' => 'Data stok berhasil ditambahkan.'
                ]);
            } catch (\Exception $e) {
                // Log error untuk debugging
                Log::error('Error saat menambahkan stok: ' . $e->getMessage());

                return response()->json([
                    'status' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
        }
        return redirect('/');
    }


    public function edit_ajax($id)
    {
        $stok = StokModel::find($id);
        $barangList = BarangModel::all();
        $supplierList = SupplierModel::all();
        if (!$stok) {
            return view('stok.edit_ajax')->with('stok', null);
        }
        return view('stok.edit_ajax', compact('stok', 'barangList', 'supplierList'));
    }   

    public function update_ajax(Request $request, $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = ([
                'supplier_id' => 'required|integer',
                'barang_id' => 'required|integer',
                'stok_tanggal' => 'required|date',
                'stok_jumlah' => 'required|integer|min:1',
            ]);

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi Gagal', 
                    'msgField' => $validator->errors()
                ]);
            }

            $stok = StokModel::find($id);

            if (!$stok) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Data stok tidak ditemukan.',
                ]);
            }

            $stok->update($request->only([
                'barang_id',
                'supplier_id',
                'stok_tanggal',
                'stok_jumlah'
            ]));

            return response()->json(['status' => true, 'message' => 'Data stok berhasil diperbarui.']);
        }
        return redirect('/');
    }

    public function confirm_ajax(string $id)
    {
        $stok = StokModel::find($id);
        return view('stok.confirm_ajax', compact('stok'));
    }

    public function delete_ajax($id)
    {
        try {
            $stok = StokModel::find($id);
            if ($stok) {
                $stok->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Data stok berhasil dihapus'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal dihapus karena masih terdapat tabel lain yang terkait dengan data ini'
            ]);
        }
    }

    public function import() 
    { 
        return view('stok.import'); 
    }

    public function import_ajax(Request $request) 
    { 
        if($request->ajax() || $request->wantsJson()){ 
            $rules = [ 
                // validasi file harus xls atau xlsx, max 1MB 
                'file_stok' => ['required', 'mimes:xlsx', 'max:1024'] 
            ]; 
 
            $validator = Validator::make($request->all(), $rules); 
            if($validator->fails()){ 
                return response()->json([ 
                    'status' => false, 
                    'message' => 'Validasi Gagal', 
                    'msgField' => $validator->errors() 
                ]); 
            }
 
            $file = $request->file('file_stok');  // ambil file dari request 
 
            $reader = IOFactory::createReader('Xlsx');  // load reader file excel 
            $reader->setReadDataOnly(true);             // hanya membaca data 
            $spreadsheet = $reader->load($file->getRealPath()); // load file excel 
            $sheet = $spreadsheet->getActiveSheet();    // ambil sheet yang aktif 
 
            $data = $sheet->toArray(null, false, true, true);   // ambil data excel 
 
            $insert = []; 
            if(count($data) > 1){ // jika data lebih dari 1 baris 
                foreach ($data as $baris => $value) { 
                    if($baris > 1){ // baris ke 1 adalah header, maka lewati 
                        $stokTanggal = is_numeric($value['D']) 
                        ? Date::excelToDateTimeObject($value['D'])->format('Y-m-d H:i:s') 
                        : date('Y-m-d H:i:s', strtotime($value['D']));
                        
                        $insert[] = [ 
                            'supplier_id' => $value['A'], 
                            'barang_id' => $value['B'], 
                            'stok_jumlah' => $value['C'], 
                            'stok_tanggal' => $stokTanggal, 
                            'user_id' => $value['E'],
                            'created_at' => now(), 
                        ]; 
                    } 
                } 
 
                if(count($insert) > 0){ 
                    // insert data ke database, jika data sudah ada, maka diabaikan 
                    StokModel::insertOrIgnore($insert);    
                } 
 
                return response()->json([ 
                    'status' => true, 
                    'message' => 'Data berhasil diimport' 
                ]); 
            }else{ 
                return response()->json([ 
                    'status' => false, 
                    'message' => 'Tidak ada data yang diimport' 
                ]); 
            } 
        } 
        return redirect('/'); 
    } 

    public function export_excel()
    {
        // ambil data barang yang akan di export
        $stok = StokModel::select('supplier_id', 'barang_id', 'user_id', 'stok_tanggal', 'stok_jumlah')
            ->orderBy('supplier_id')
            ->orderBy('barang_id')
            ->orderBy('user_id')
            ->with('supplier', 'barang', 'user')->get();
        
        // load library excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet(); // ambil sheet yang aktif

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nama Supplier');
        $sheet->setCellValue('C1', 'Nama Barang');
        $sheet->setCellValue('D1', 'Jumlah');
        $sheet->setCellValue('E1', 'Tanggal');
        $sheet->setCellValue('F1', 'Petugas');
        
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        $no = 1; // nomor data dimulai dari 1
        $baris = 2; // baris data dimulai dari baris ke 2
        foreach ($stok as $key => $value) {
            $sheet->setCellValue('A'.$baris, $no);
            $sheet->setCellValue('B'.$baris, $value->supplier->supplier_nama);
            $sheet->setCellValue('C'.$baris, $value->barang->barang_nama);
            $sheet->setCellValue('D'.$baris, $value->stok_jumlah);
            $sheet->setCellValue('E'.$baris, $value->stok_tanggal);
            $sheet->setCellValue('F'.$baris, $value->user->username);
            $baris++;
            $no++;
        }

        foreach(range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet->setTitle('Data Stok'); // set tittle sheet

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data Stok '.date('Y-m-d H:i:s').'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: no-cache');
        
        $writer->save('php://output');
        exit;
    }

    public function export_pdf()
    {
        $stok = StokModel::select('supplier_id', 'barang_id', 'user_id', 'stok_tanggal', 'stok_jumlah')
            ->orderBy('supplier_id')
            ->orderBy('barang_id')
            ->orderBy('user_id')
            ->with('supplier', 'barang', 'user')->get();
        
        // use Barryvd\DomPDF\Facade\PDF;
        $pdf = Pdf::loadView('stok.export_pdf', ['stok' => $stok]);
        $pdf->setPaper('A4', 'portrait'); // set ukuran kertas dan orientasi
        $pdf->setOption("isRemoteEnabled", true); // set true jika ada gambar dari url
        $pdf->render();

        return $pdf->stream('Data Stok '.date('Y-m-d H:i:s').'.pdf');
    }
}