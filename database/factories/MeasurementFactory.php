<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeasurementFactory extends Factory
{
    public function definition(): array
{
    // Koordinat Pusat (Gedung Sekolah Vokasi Undip)
    $centerLat = -7.0556; 
    $centerLng = 110.4348;
    
    // Titik acak di sekitar pusat
    $lat = $centerLat + $this->faker->randomFloat(6, -0.003, 0.003);
    $lng = $centerLng + $this->faker->randomFloat(6, -0.003, 0.003);

    // LOGIKA MATEMATIKA: Semakin dekat ke pusat, semakin tinggi (Bikin Gunung)
    // Hitung jarak ke pusat pake Pythagoras sederhana
    $distance = sqrt(pow(($lat - $centerLat), 2) + pow(($lng - $centerLng), 2));
    
    // Kalo deket pusat (jarak < 0.001) tingginya 180-200m (Puncak)
    // Kalo jauh, tingginya turun sampe 100m
    // Rumus: Base 200m dikurangi jarak * faktor
    $altitude = 200 - ($distance * 40000); 
    
    // Tambah noise dikit biar gak terlalu mulus kayak mangkok
    $altitude += $this->faker->randomFloat(2, -5, 5);

    return [
        'project_id' => \App\Models\Project::factory(),
        'latitude'  => $lat,
        'longitude' => $lng,
        'altitude'  => $altitude, // Pakai altitude hasil hitungan bukit
        'created_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
    ];
}
}
