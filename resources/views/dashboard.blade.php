@extends('layouts.app')

@section('content')
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h2 class="text-3xl font-bold text-gray-900">{{ $project->name }}</h2>
        <p class="text-gray-500 mt-1">Surveyor: <span class="font-medium text-gray-700">{{ $project->surveyor_name }}</span> | ID: <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-sm">#{{ $project->id }}</span></p>
    </div>
    <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
        <i class="bi bi-arrow-left mr-2"></i> Kembali
    </a>
</div>

<div class="grid grid-cols-1 gap-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h5 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <i class="bi bi-map text-blue-600"></i> Peta Kontur / Sebaran Titik
            </h5>
            <button class="inline-flex items-center px-3 py-1.5 border border-blue-600 text-blue-600 rounded-md text-sm font-medium hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors" onclick="loadData()">
                <i class="bi bi-arrow-clockwise mr-1.5"></i> Refresh Data
            </button>
        </div>
        <div class="p-0">
            <div id="map-container" class="h-[500px] bg-gray-100 flex items-center justify-center text-gray-400">
                <p>Area Peta (Akan diimplementasikan dengan Library Peta seperti Leaflet/Google Maps)</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h5 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <i class="bi bi-table text-green-600"></i> Data Pengukuran Terakhir
            </h5>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="data-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Latitude</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Longitude</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Altitude (m)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pressure (hPa)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const projectId = {{ $project->id }};
    const apiUrl = "{{ route('projects.data', $project->id) }}";

    document.addEventListener('DOMContentLoaded', function() {
        loadData();
    });

    function loadData() {
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                updateTable(data);
                // Disini nanti panggil fungsi updateMap(data)
                console.log("Data loaded:", data);
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    function updateTable(data) {
        const tbody = document.querySelector('#data-table tbody');
        tbody.innerHTML = '';

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data pengukuran.</td></tr>';
            return;
        }

        // Ambil 10 data terakhir (atau semua, tergantung kebutuhan)
        // Kita balik urutannya biar yang baru diatas (kalau dari API belum sort)
        // Asumsi data dari API urut ID/created_at asc, kita reverse buat tabel
        const recentData = data.slice().reverse().slice(0, 10); 

        recentData.forEach(item => {
            const row = `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${new Date(item.created_at).toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">${item.latitude}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">${item.longitude}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">${item.altitude}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.pressure || '-'}</td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
    }
</script>
@endpush
