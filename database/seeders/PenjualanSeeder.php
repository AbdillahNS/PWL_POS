<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenjualanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'penjualan_id' => 1,
                'user_id' => 1,
                'pembeli' => 'Andi',
                'penjualan_kode' => 'PJL-20240201',
                'tanggal_penjualan' => now()->subDays(10),
            ],
            [
                'penjualan_id' => 2,
                'user_id' => 2,
                'pembeli' => 'Budi',
                'penjualan_kode' => 'PJL-20240202',
                'tanggal_penjualan' => now()->subDays(9),
            ],
            [
                'penjualan_id' => 3,
                'user_id' => 3,
                'pembeli' => 'Citra',
                'penjualan_kode' => 'PJL-20240203',
                'tanggal_penjualan' => now()->subDays(8),
            ],
            [
                'penjualan_id' => 4,
                'user_id' => 1,
                'pembeli' => 'Dewi',
                'penjualan_kode' => 'PJL-20240204',
                'tanggal_penjualan' => now()->subDays(7),
            ],
            [
                'penjualan_id' => 5,
                'user_id' => 2,
                'pembeli' => 'Eka',
                'penjualan_kode' => 'PJL-20240205',
                'tanggal_penjualan' => now()->subDays(6),
            ],
            [
                'penjualan_id' => 6,
                'user_id' => 3,
                'pembeli' => 'Fajar',
                'penjualan_kode' => 'PJL-20240206',
                'tanggal_penjualan' => now()->subDays(5),
            ],
            [
                'penjualan_id' => 7,
                'user_id' => 1,
                'pembeli' => 'Gina',
                'penjualan_kode' => 'PJL-20240207',
                'tanggal_penjualan' => now()->subDays(4),
            ],
            [
                'penjualan_id' => 8,
                'user_id' => 2,
                'pembeli' => 'Hadi',
                'penjualan_kode' => 'PJL-20240208',
                'tanggal_penjualan' => now()->subDays(3),
            ],
            [
                'penjualan_id' => 9,
                'user_id' => 3,
                'pembeli' => 'Indah',
                'penjualan_kode' => 'PJL-20240209',
                'tanggal_penjualan' => now()->subDays(2),
            ],
            [
                'penjualan_id' => 10,
                'user_id' => 1,
                'pembeli' => 'Joko',
                'penjualan_kode' => 'PJL-20240210',
                'tanggal_penjualan' => now()->subDays(1),
            ],
        ];
        DB::table('t_penjualan')->insert($data);
    }
}
