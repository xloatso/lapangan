<?php

namespace Database\Seeders;

use App\Models\Courts;
use Illuminate\Database\Seeder;

class courts_seeder extends Seeder
{
    
    public function run(): void
    {
          foreach ([
            ['name' => 'Futsal Arena 01', 'type' => 'futsal', 'price_per_hour' => 150000, 'description' => 'Lapangan indoor dengan rumput sintetis. Tersedia area tunggu dan ruang ganti.'],
            ['name' => 'Futsal Arena 02', 'type' => 'futsal', 'price_per_hour' => 120000, 'description' => 'Lapangan vinyl untuk latihan dan pertandingan bersama tim kamu.'],
            ['name' => 'Badminton Court 01', 'type' => 'badminton', 'price_per_hour' => 50000, 'description' => 'Lapangan indoor dengan karpet olahraga dan pencahayaan yang nyaman.'],
            ['name' => 'Basket Court 01', 'type' => 'basket', 'price_per_hour' => 175000, 'description' => 'Lapangan basket untuk latihan tim, sparring, dan permainan bersama.'],
        ] as $court) {
            Courts::firstOrCreate(['name' => $court['name']], $court);
        }

    }
}
