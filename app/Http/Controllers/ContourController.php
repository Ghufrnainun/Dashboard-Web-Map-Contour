<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Measurement;
use Illuminate\Http\Request;

class ContourController extends Controller
{
    // =========================================================
    // BAGIAN 1: WEB DASHBOARD (Interaksi dengan Manusia)
    // =========================================================

    /**
     * 1. Halaman Awal (Daftar Project)
     * Disini user bikin "Folder" dulu sebelum survey.
     */
    public function index()
    {
        // Ambil project urut dari yang terbaru
        $projects = Project::latest()->get();
        return view('home', compact('projects'));
    }

    /**
     * 2. Proses Simpan Project Baru
     * Flow: User isi form nama lokasi -> Klik Submit -> Masuk sini.
     */
    public function storeProject(Request $request)
    {
        // Validasi input biar gak kosong
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surveyor_name' => 'required|string|max:100',
            'description' => 'nullable|string'
        ]);

        // Simpan ke tabel 'projects'
        Project::create($validated);

        // Balik ke halaman awal
        return redirect()->route('home')->with('success', 'Project berhasil dibuat! Silakan catat ID-nya.');
    }

    /**
     * 3. Halaman Detail Project (Dashboard Peta)
     * Flow: User klik salah satu project -> Masuk sini.
     */
    public function show($id)
    {
        // Cari project berdasarkan ID, kalau gak ada error 404
        $project = Project::findOrFail($id);

        // Kirim data project ke View dashboard.blade.php
        return view('dashboard', compact('project'));
    }

    /**
     * 4. API Internal untuk Frontend (AJAX/Fetch)
     * Flow: Javascript di dashboard minta data titik-titik koordinat.
     */
    public function getMeasurements($id)
    {
        // Ambil semua data measurement milik project ID tersebut
        $measurements = Measurement::where('project_id', $id)->get();

        // Return dalam bentuk JSON biar bisa dibaca Javascript
        return response()->json($measurements);
    }

    // =========================================================
    // BAGIAN 2: API HARDWARE (Interaksi dengan ESP32)
    // =========================================================

    /**
     * 5. Endpoint Penerima Data Sensor
     * Flow: ESP32 nembak ke sini pakai method POST.
     */
    public function storeMeasurement(Request $request)
    {
        // VALIDASI PENTING!
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'latitude'   => 'required|numeric',
            'longitude'  => 'required|numeric',
            'altitude'   => 'required|numeric',
            'speed'      => 'nullable|numeric', // Tambahan untuk tracking
            'pressure'   => 'nullable|numeric',
        ]);

        // Kalau validasi lolos, simpan ke tabel 'measurements'
        // Opsional: jika speed kosong, default ke 0
        if (!isset($validated['speed'])) {
            $validated['speed'] = 0;
        }

        $data = Measurement::create($validated);

        // Balas ke ESP32 (Penting biar alat tau datanya masuk)
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil disimpan',
            'data_id' => $data->id
        ], 201);
    }

    /**
     * 6. Hapus Data Pengukuran (Dashboard)
     */
    public function destroy($id)
    {
        $measurement = Measurement::findOrFail($id);
        $measurement->delete();

        return response()->json(['status' => 'success']);
    }
}
