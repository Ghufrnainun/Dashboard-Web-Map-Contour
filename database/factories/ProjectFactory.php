<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Survey Lahan ' . $this->faker->city(),
            'surveyor_name' => $this->faker->name(),
            'description' => 'Simulasi data dummy untuk testing visualisasi.',
            'created_at' => now(),
        ];
    }
}
