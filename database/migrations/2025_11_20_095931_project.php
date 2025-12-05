<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Projects (Folder Survey)
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama Lokasi/Project
            $table->string('surveyor_name'); // Nama Mahasiswa
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Tabel Measurements (Data Sensor)
        Schema::create('measurements', function (Blueprint $table) {
            $table->id();
            // Relasi ke Project (Kalau project dihapus, data hilang)
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            
            // Koordinat (Presisi tinggi: 10 digit, 7 desimal)
            $table->decimal('latitude', 10, 7); 
            $table->decimal('longitude', 10, 7);
            
            // Data Sensor
            $table->float('altitude'); // Ketinggian (meter)
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('measurements');
        Schema::dropIfExists('projects');
    }
};