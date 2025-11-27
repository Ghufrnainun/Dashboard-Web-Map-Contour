<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Measurement;
use Carbon\Carbon;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Project: Lahan Kebun Raya (Data Rapat/Banyak)
        $project1 = Project::create([
            'name' => 'Lahan Kebun Raya',
            'surveyor_name' => 'Bagas',
            'description' => 'Survey kontur area taman belakang untuk perencanaan drainase.',
            'created_at' => Carbon::now()->subDays(2),
        ]);

        // Titik Tengah (Bogor)
        $baseLat = -6.597629;
        $baseLng = 106.799570;
        $baseAlt = 260.0; // mdpl

        // Generate 20 titik spiral/zig-zag
        for ($i = 0; $i < 20; $i++) {
            // Geser dikit-dikit (0.0001 derajat ~ 11 meter)
            $latOffset = ($i % 5) * 0.00015; 
            $lngOffset = floor($i / 5) * 0.00015;
            
            // Variasi random dikit biar gak kaku
            $randLat = rand(-5, 5) / 100000; 
            $randLng = rand(-5, 5) / 100000;

            Measurement::create([
                'project_id' => $project1->id,
                'latitude' => $baseLat + $latOffset + $randLat,
                'longitude' => $baseLng + $lngOffset + $randLng,
                'altitude' => $baseAlt + rand(-20, 50) / 10, // Variasi ketinggian -2m s/d +5m
                'pressure' => 1010 + rand(-5, 5),
                'created_at' => Carbon::now()->subDays(2)->addMinutes($i * 10), // Data masuk tiap 10 menit
            ]);
        }

        // 2. Project: Area Konstruksi B (Data Sedikit)
        $project2 = Project::create([
            'name' => 'Area Konstruksi B',
            'surveyor_name' => 'Tim Sipil',
            'description' => 'Cek elevasi tanah dasar.',
            'created_at' => Carbon::now()->subDay(),
        ]);

        // Titik Tengah (Jakarta)
        $baseLat2 = -6.2088;
        $baseLng2 = 106.8456;

        for ($i = 0; $i < 5; $i++) {
            Measurement::create([
                'project_id' => $project2->id,
                'latitude' => $baseLat2 + (rand(-10, 10) / 10000),
                'longitude' => $baseLng2 + (rand(-10, 10) / 10000),
                'altitude' => 15.0 + rand(-10, 10) / 10,
                'pressure' => 1012,
                'created_at' => Carbon::now()->subDay()->addMinutes($i * 30),
            ]);
        }
    }
}
