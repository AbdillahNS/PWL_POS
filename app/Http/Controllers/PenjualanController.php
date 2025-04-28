<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenjualanModel;
use App\Models\PenjualanDetailModel;
use App\Models\BarangModel;
use App\Models\UserModel;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PenjualanController extends Controller
{
    public function index()
    {
        $breadcrumb = (object)[ 
            'title' => 'Daftar Transaksi Penjualan',
            'list' => ['Home','Transaksi Penjualan']
        ];

        $page = (object)[
            'title' => 'Daftar Transaksi Penjualan yang terdaftar dalam sistem'
        ];

        $activeMenu = 'penjualan';

        $user = UserModel::all(); 

        return view('penjualan.index',['breadcrumb'=>$breadcrumb,'page'=>$page,'user'=>$user, 'activeMenu'=>$activeMenu]);
    }

    public function list(Request $request)
    {
        $penjualans = PenjualanModel::select('penjualan_id', 'user_id', 'pembeli', 'penjualan_kode', 'tanggal_penjualan')
            ->with('user');

        if ($request->tanggal_penjualan) {
            $penjualans->whereDate('tanggal_penjualan',($request->tanggal_penjualan));
        }

        return DataTables::of($penjualans)
            ->addIndexColumn() // Menambahkan kolom index / no urut (default nmaa kolom: DT_RowINdex)
            ->addColumn('aksi', function ($penjualan) {
                $btn  = '<button onclick="modalAction(\''.url('/penjualan/' . $penjualan->penjualan_id . '/detail_ajax').'\')" class="btn btn-info btn-sm">Detail</button> ';
                $btn .= '<button onclick="modalAction(\''.url('/penjualan/' . $penjualan->penjualan_id . '/edit_ajax').'\')" class="btn btn-warning btn-sm">Edit</button> '; 
                $btn .= '<button onclick="modalAction(\''.url('/penjualan/' . $penjualan->penjualan_id . '/delete_ajax').'\')"  class="btn btn-danger btn-sm">Hapus</button> '; 
                return $btn;
            })

            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function show(string $id)
    {
        $penjualan = PenjualanModel::with(['user'])->find($id);

        if (!$penjualan) {
            abort(404, 'Data penjualan tidak ditemukan');
        }

        $breadcrumb = (object)[
            'title' => 'Detail Penjualan',
            'list' => ['Home', 'Transaksi Penjualan', 'Detail']
        ];

        $page = (object)[
            'title' => 'Detail Transaksi Penjualan'
        ];

        $activeMenu = 'penjualan';

        return view('penjualan.show', ['breadcrumb' => $breadcrumb,'page' => $page,'penjualan' => $penjualan,'activeMenu' => $activeMenu]);
    }

    public function create_ajax(){
        $user = UserModel::select('user_id', 'username')->get();
        $barangs = BarangModel::select('barang_id', 'barang_nama', 'harga_jual')->get();

        return view('penjualan.create_ajax')->with([
            'user' => $user,
            'barangs' => $barangs
        ]);
    }

    public function store_ajax(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'pembeli'           => 'required|string|max:100',
                'penjualan_kode'    => 'required|string|unique:t_penjualan,penjualan_kode',
                'tanggal_penjualan' => 'nullable|date',
                'barang_id.*'       => 'required|exists:m_barang,barang_id', 
                'detail_jumlah.*'   => 'required|numeric|min:1',
            ];
    
            $validator = Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Validasi Gagal',
                    'msgField'  => $validator->errors(),
                ]);
            }
    
            try {
                $penjualan = PenjualanModel::create([
                    'user_id'           => auth()->id(),
                    'pembeli'           => $request->pembeli,
                    'penjualan_kode'    => $request->penjualan_kode,
                    'tanggal_penjualan' => $request->tanggal_penjualan ?: now()->toDateString(),
                ]);
    
                // Simpan data detail penjualan
                $barangIds = $request->input('barang_id', []);
                $detailJumlah = $request->input('detail_jumlah', []);
                $detailHarga = $request->input('detail_harga', []);
    
                $details = [];
                foreach ($barangIds as $index => $barangId) {
                    $details[] = [
                        'penjualan_id' => $penjualan->penjualan_id,
                        'barang_id'    => $barangId,
                        'jumlah'       => $detailJumlah[$index],
                        'harga'        => $detailHarga[$index],
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }
    
                if (!empty($details)) {
                    PenjualanDetailModel::insert($details); // Insert semua detail sekaligus
                }
    
                return response()->json([
                    'status'    => true,
                    'message'   => 'Data penjualan berhasil disimpan',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Terjadi kesalahan: ' . $e->getMessage(),
                ], 500);
            }
        }
        return redirect('/');
    }

    public function edit_ajax(string $id)
    {
        $penjualan = PenjualanModel::find($id);
        $user = UserModel::all();

        return view('penjualan.edit_ajax', compact('penjualan', 'user'));
    }

    public function update_ajax(Request $request, string $id)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'pembeli'           => 'required|string|max:100',
                'penjualan_kode'    => 'required|string|unique:t_penjualan,penjualan_kode,' . $id . ',penjualan_id',
                'tanggal_penjualan' => 'required|date',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Validasi gagal.',
                    'msgField'  => $validator->errors(),
                ]);
            }

            $penjualan = PenjualanModel::find($id);

            if (!$penjualan) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Data penjualan tidak ditemukan.',
                ]);
            }

            $penjualan->update([
                'user_id'           => auth()->id(), // Menyimpan ID pengguna yang sedang login
                'pembeli'           => $request->pembeli,
                'penjualan_kode'    => $request->penjualan_kode,
                'tanggal_penjualan' => $request->tanggal_penjualan,
            ]);

            return response()->json(['status' => true, 'message' => 'Data penjualan berhasil diperbarui.']);
        }
        return redirect('/');
    }

    public function confirm_ajax(string $id)
    {
        $penjualan = PenjualanModel::find($id);
        return view('penjualan.confirm_ajax', ['penjualan' => $penjualan]);
    }

    public function delete_ajax($id)
    {
        try {
            $penjualan = PenjualanModel::find($id);
            if ($penjualan) {
                $penjualan->penjualan_detail()->delete();
                $penjualan->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Data penjualan berhasil dihapus'
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

    public function detail_ajax(string $id)
    {
        $penjualan = PenjualanModel::with(['penjualan_detail.barang:barang_id,barang_nama,harga_jual'])
        ->select('penjualan_id', 'pembeli', 'penjualan_kode')
        ->find($id);

    if (!$penjualan) {
        return response()->json([
            'status' => false,
            'message' => 'Data penjualan tidak ditemukan'
        ]);
    }

    return view('penjualan.detail_ajax', ['penjualan' => $penjualan]);
    }

    public function import() 
    { 
        return view('penjualan.import'); 
    }

    public function import_ajax(Request $request) 
    { 
        if($request->ajax() || $request->wantsJson()){ 
            $rules = [ 
                // validasi file harus xls atau xlsx, max 1MB 
                'file_penjualan' => ['required', 'mimes:xlsx', 'max:1024'] 
            ]; 
 
            $validator = Validator::make($request->all(), $rules); 
            if($validator->fails()){ 
                return response()->json([ 
                    'status' => false, 
                    'message' => 'Validasi Gagal', 
                    'msgField' => $validator->errors() 
                ]); 
            }
 
            $file = $request->file('file_penjualan');  // ambil file dari request 
 
            $reader = IOFactory::createReader('Xlsx');  // load reader file excel 
            $reader->setReadDataOnly(true);             // hanya membaca data 
            $spreadsheet = $reader->load($file->getRealPath()); // load file excel 
            $sheet = $spreadsheet->getActiveSheet();    // ambil sheet yang aktif 
 
            $data = $sheet->toArray(null, false, true, true);   // ambil data excel 
 
            $insert = []; 
            if(count($data) > 1){ // jika data lebih dari 1 baris 
                foreach ($data as $baris => $value) { 
                    if($baris > 1){ // baris ke 1 adalah header, maka lewati 
                        $tanggalPenjualan = is_numeric($value['D']) 
                        ? Date::excelToDateTimeObject($value['D'])->format('Y-m-d H:i:s') 
                        : date('Y-m-d H:i:s', strtotime($value['D']));
                        
                        $insert[] = [ 
                            'penjualan_kode' => $value['A'], 
                            'pembeli' => $value['B'], 
                            'user_id' => $value['C'], 
                            'tanggal_penjualan' => $tanggalPenjualan, 
                            'created_at' => now(), 
                        ]; 
                    } 
                } 
 
                if(count($insert) > 0){ 
                    // insert data ke database, jika data sudah ada, maka diabaikan 
                    PenjualanModel::insertOrIgnore($insert);    
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
        // ambil data penjualan yang akan di export
        $penjualan = PenjualanModel::select('user_id', 'pembeli', 'penjualan_kode', 'tanggal_penjualan')
            ->orderBy('user_id')
            ->with('user')->get();

        // ambil data barang yang akan di export
        
        // load library excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet(); // ambil sheet yang aktif

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Kode Penjualan');
        $sheet->setCellValue('C1', 'Pembeli');
        $sheet->setCellValue('D1', 'Petugas');
        $sheet->setCellValue('E1', 'Tanggal Penjualan');
        
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        $no = 1; // nomor data dimulai dari 1
        $baris = 2; // baris data dimulai dari baris ke 2
        foreach ($penjualan as $key => $value) {
            $sheet->setCellValue('A'.$baris, $no);
            $sheet->setCellValue('B'.$baris, $value->penjualan_kode);
            $sheet->setCellValue('C'.$baris, $value->pembeli);
            $sheet->setCellValue('D'.$baris, $value->user->username);
            $sheet->setCellValue('E'.$baris, $value->tanggal_penjualan);
            $baris++;
            $no++;
        }

        foreach(range('A', 'E') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet->setTitle('Data Penjualan'); // set tittle sheet

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Data Penjualan '.date('Y-m-d H:i:s').'.xlsx';

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
        $penjualan = PenjualanModel::select('user_id', 'pembeli', 'penjualan_kode', 'tanggal_penjualan')
            ->orderBy('user_id')
            ->with('user')->get();
        
        // use Barryvd\DomPDF\Facade\PDF;
        $pdf = Pdf::loadView('penjualan.export_pdf', ['penjualan' => $penjualan]);
        $pdf->setPaper('A4', 'portrait'); // set ukuran kertas dan orientasi
        $pdf->setOption("isRemoteEnabled", true); // set true jika ada gambar dari url
        $pdf->render();

        return $pdf->stream('Data penjualan '.date('Y-m-d H:i:s').'.pdf');
    }
}