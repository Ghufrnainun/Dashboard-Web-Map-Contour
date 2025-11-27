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
        // 1. Project: Gunung Pancar (Tipe: HILL/BUKIT)
        // Pusat tinggi, pinggir rendah
        // Real Coords: -6.59167, 106.91194. Alt: 300-800m
        $this->createProjectWithTerrain(
            'Survey Gunung Pancar', 
            'Bagas', 
            'Survey topografi area perbukitan untuk wisata alam.',
            -6.59167, 106.91194, 
            'HILL', 
            500, // Base Altitude (Mid-slope)
            50   // Jumlah Titik
        );

        // 2. Project: Lembah Anai (Tipe: VALLEY/LEMBAH)
        // Pusat rendah, pinggir tinggi
        // Real Coords: -0.483, 100.34. Alt: ~400m
        $this->createProjectWithTerrain(
            'Konstruksi Jembatan Lembah', 
            'Tim Sipil A', 
            'Pengecekan elevasi dasar lembah untuk tiang pancang.',
            -0.4830, 100.3400, 
            'VALLEY', 
            350, // Base Altitude (Dasar Lembah)
            60
        );

        // 3. Project: Lereng Merapi (Tipe: HILL/BUKIT - Puncak di Tengah)
        // Miring dari satu sisi ke sisi lain -> Ubah ke Hill biar puncaknya merah di tengah
        // Real Coords: -7.5407, 110.4457. Alt: 1000-1700m
        $this->createProjectWithTerrain(
            'Monitoring Lereng Merapi', 
            'Volcanology Team', 
            'Pemetaan jalur lahar dingin.',
            -7.5407, 110.4457, 
            'HILL', 
            1700, // Base Altitude (Puncak)
            80
        );

        // 4. Project: Perumahan Green Garden (Tipe: RANDOM/DATAR)
        // Variasi kecil
        // Real Coords: -6.1620, 106.7600. Alt: 10-20m
        $this->createProjectWithTerrain(
            'Perumahan Green Garden', 
            'Developer X', 
            'Cut and fill lahan perumahan.',
            -6.1620, 106.7600, 
            'RANDOM', 
            15, // Base Altitude
            40
        );
    }

    private function createProjectWithTerrain($name, $surveyor, $desc, $lat, $lng, $type, $baseAlt, $count)
    {
        $project = Project::create([
            'name' => $name,
            'surveyor_name' => $surveyor,
            'description' => $desc,
            'created_at' => Carbon::now()->subDays(rand(1, 5)),
        ]);

        for ($i = 0; $i < $count; $i++) {
            // Sebar titik secara acak dalam radius ~500m (0.005 derajat)
            $latOffset = (rand(-50, 50) / 10000); 
            $lngOffset = (rand(-50, 50) / 10000);
            
            $currentLat = $lat + $latOffset;
            $currentLng = $lng + $lngOffset;

            // Hitung Altitude berdasarkan Tipe Terrain
            $altitude = $baseAlt;
            $dist = sqrt(pow($latOffset, 2) + pow($lngOffset, 2)); // Jarak dari pusat
            
            // Normalisasi jarak (0 - 0.007 approx max)
            // Kita anggap max dist 0.007
            
            switch ($type) {
                case 'HILL':
                    // Makin jauh makin rendah (Gaussian bell shape)
                    // Alt = Base - (Dist * Factor)
                    $altitude = $baseAlt - ($dist * 20000); 
                    break;
                
                case 'VALLEY':
                    // Makin jauh makin tinggi (Inverse bell)
                    $altitude = $baseAlt + ($dist * 20000);
                    break;

                case 'SLOPE':
                    // Miring ke arah Utara-Timur (Lat+, Lng+)
                    // Alt = Base + (LatOffset * Factor) + (LngOffset * Factor)
                    $altitude = $baseAlt + ($latOffset * 10000) + ($lngOffset * 10000);
                    break;

                case 'RANDOM':
                default:
                    // Datar dengan noise
                    $altitude = $baseAlt + (rand(-20, 20) / 10);
                    break;
            }

            // Tambah random noise biar natural
            $altitude += (rand(-10, 10) / 10);

            Measurement::create([
                'project_id' => $project->id,
                'latitude' => $currentLat,
                'longitude' => $currentLng,
                'altitude' => $altitude,
                'pressure' => 1013 - ($altitude / 10),
                'created_at' => Carbon::now()->subDays(1)->addMinutes($i * 10),
            ]);
        }
    }
}
