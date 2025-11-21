<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeasurementFactory extends Factory
{
    public function definition(): array
    {
        // KITA SET SATU TITIK PUSAT (Misal: Lapangan Bola)
        // Koordinat contoh (Semarang/Jateng)
        $centerLat = -7.051;
        $centerLng = 110.435;

        return [
            'project_id' => Project::Factory(), // Default (nanti di-override di seeder)

            // Generate titik acak tapi radiusnya DEKAT (cuma geser 0.001 derajat)
            // Biar seolah-olah surveyor lagi jalan kaki di area itu
            'latitude'  => $centerLat + $this->faker->randomFloat(6, -0.002, 0.002),
            'longitude' => $centerLng + $this->faker->randomFloat(6, -0.002, 0.002),

            // Altitude pura-pura naik turun tanah berbukit (100m - 150m)
            'altitude'  => $this->faker->randomFloat(2, 100, 150),

            'pressure'  => $this->faker->randomFloat(2, 1008, 1013),
            'created_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
        ];
    }
}
