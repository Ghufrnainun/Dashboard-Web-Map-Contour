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
        <div class="px-6 py-2 bg-gray-50 border-b border-gray-100 flex items-center">
            <label class="inline-flex items-center cursor-pointer">
                <input type="checkbox" id="toggle-contours" class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out" checked>
                <span class="ml-2 text-sm text-gray-700">Tampilkan Kontur Warna</span>
            </label>
        </div>
        <div class="p-0">
            <div id="map-container" class="h-[500px] bg-gray-100 z-0"></div>
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
    let map;
    let markers = [];
    let contourLayer = null;
    let currentData = [];

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map
        if (window.initMap) {
            map = window.initMap('map-container');
        } else {
            console.error('Map functions not loaded properly');
        }

        // Toggle Contours Listener
        document.getElementById('toggle-contours').addEventListener('change', function(e) {
            if (e.target.checked) {
                if (!contourLayer && currentData.length > 0) {
                    updateContours(currentData);
                } else if (contourLayer) {
                    map.addLayer(contourLayer);
                }
            } else {
                if (contourLayer) {
                    map.removeLayer(contourLayer);
                }
                if (window.currentLegend) {
                    map.removeControl(window.currentLegend);
                    window.currentLegend = null;
                }
            }
        });

        loadData();
    });

    function loadData() {
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                currentData = data; // Store for toggle usage
                updateTable(data);
                updateMap(data);
                
                // Generate contours if checkbox is checked
                if (document.getElementById('toggle-contours').checked) {
                    updateContours(data);
                }
                
                console.log("Data loaded:", data);
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    function updateContours(data) {
        if (!map || !window.generateContours) return;
        
        // Remove existing layer if any
        if (contourLayer) {
            map.removeLayer(contourLayer);
            contourLayer = null;
        }
        if (window.currentLegend) {
            map.removeControl(window.currentLegend);
            window.currentLegend = null;
        }

        try {
            const result = window.generateContours(map, data);
            if (result) {
                contourLayer = result.layer;
                // Add legend
                if (window.addLegend) {
                    window.currentLegend = window.addLegend(map, result.min, result.max);
                }
            }
        } catch (e) {
            console.error("Error generating contours:", e);
        }
    }

    function updateMap(data) {
        if (!map) return;

        // Clear existing markers
        markers.forEach(marker => marker.remove());
        markers = [];

        if (data.length === 0) return;

        // Create bounds to fit all markers
        const bounds = L.latLngBounds();

        data.forEach(item => {
            const lat = parseFloat(item.latitude);
            const lng = parseFloat(item.longitude);
            
            if (!isNaN(lat) && !isNaN(lng)) {
                const popupContent = `
                    <b>Time:</b> ${new Date(item.created_at).toLocaleString()}<br>
                    <b>Alt:</b> ${item.altitude} m<br>
                    <b>Pressure:</b> ${item.pressure || '-'} hPa
                `;
                const marker = window.addMarker(map, lat, lng, popupContent);
                markers.push(marker);
                bounds.extend([lat, lng]);
            }
        });

        if (markers.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }

    function updateTable(data) {
        const tbody = document.querySelector('#data-table tbody');
        tbody.innerHTML = '';

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data pengukuran.</td></tr>';
            return;
        }

        // Ambil 10 data terakhir (atau semua, tergantung kebutuhan)
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
