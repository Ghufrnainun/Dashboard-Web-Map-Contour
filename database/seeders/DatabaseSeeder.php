<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Measurement;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ProjectSeeder::class);

        // 1. Bikin SATU Project Induk
        $project = Project::factory()->create([
            'name' => 'TESTING: Lapangan Belakang Kampus',
            'surveyor_name' => 'Bot Generator',
        ]);

        // 2. Bikin 50 Data Dummy yang nempel ke Project ID di atas
        Measurement::factory(50)->create([
            'project_id' => $project->id
        ]);

        // Bikin Project kedua (buat perbandingan)
        $project2 = Project::factory()->create(['name' => 'TESTING: Area Parkir']);
        Measurement::factory(30)->create(['project_id' => $project2->id]);
    }
}
