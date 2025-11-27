@extends('layouts.app')

@section('content')
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">{{ $project->name }}</h2>
        <div class="flex items-center gap-3 mt-2 text-sm">
            <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                <i class="bi bi-person-circle"></i>
                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $project->surveyor_name }}</span>
            </div>
            <span class="text-gray-300 dark:text-gray-600">|</span>
            <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-xs font-bold dark:bg-gray-800 dark:text-gray-300">ID: #{{ $project->id }}</span>
            </div>
        </div>
    </div>
    <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-xl font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
        <i class="bi bi-arrow-left mr-2"></i> Kembali
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Map Section (Left, 2 Columns) -->
    <div class="lg:col-span-2 flex flex-col gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden dark:bg-gray-800 dark:border-gray-700 transition-colors duration-300 h-full">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4 bg-gray-50/50 dark:bg-gray-800 dark:border-gray-700">
                <h5 class="text-lg font-bold text-gray-800 flex items-center gap-2 dark:text-gray-100">
                    <div class="p-1.5 bg-blue-100 text-blue-600 rounded-lg dark:bg-blue-900/30 dark:text-blue-400">
                        <i class="bi bi-map-fill"></i>
                    </div>
                    Peta Kontur
                </h5>
                <div class="flex items-center gap-3">
                    <button class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-200 text-gray-600 rounded-lg text-sm font-semibold hover:bg-gray-50 transition-colors dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600" onclick="loadData()">
                        <i class="bi bi-arrow-clockwise mr-1.5"></i> Refresh
                    </button>
                </div>
            </div>
            
            <div class="px-6 py-3 bg-white border-b border-gray-100 flex flex-wrap items-center gap-6 dark:bg-gray-800 dark:border-gray-700">
                <label class="inline-flex items-center cursor-pointer group">
                    <div class="relative">
                        <input type="checkbox" id="toggle-contours" class="peer sr-only" checked>
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </div>
                    <span class="ml-2 text-sm font-semibold text-gray-600 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white transition-colors">Kontur</span>
                </label>
                
                <label class="inline-flex items-center cursor-pointer group">
                    <div class="relative">
                        <input type="checkbox" id="toggle-markers" class="peer sr-only" checked>
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </div>
                    <span class="ml-2 text-sm font-semibold text-gray-600 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white transition-colors">Marker</span>
                </label>
            </div>
            
            <div class="p-0 relative h-[600px]">
                <div id="map-container" class="absolute inset-0 z-0"></div>
            </div>
        </div>
    </div>

    <!-- Right Sidebar (Stats & Table) -->
    <div class="lg:col-span-1 flex flex-col gap-6">
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center gap-2 mb-2 text-red-500 bg-red-50 w-fit px-2 py-1 rounded-lg dark:bg-red-900/20 dark:text-red-400">
                    <i class="bi bi-arrow-up-circle-fill"></i>
                    <span class="text-xs font-bold uppercase tracking-wider">Tertinggi</span>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white" id="stat-max-alt">-</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Meter Mdpl</div>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                <div class="flex items-center gap-2 mb-2 text-teal-500 bg-teal-50 w-fit px-2 py-1 rounded-lg dark:bg-teal-900/20 dark:text-teal-400">
                    <i class="bi bi-arrow-down-circle-fill"></i>
                    <span class="text-xs font-bold uppercase tracking-wider">Terendah</span>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white" id="stat-min-alt">-</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Meter Mdpl</div>
            </div>
        </div>

        <!-- Data Table Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex-1 flex flex-col dark:bg-gray-800 dark:border-gray-700 transition-colors duration-300">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 dark:bg-gray-800 dark:border-gray-700">
                <h5 class="text-lg font-bold text-gray-800 flex items-center gap-2 dark:text-gray-100">
                    <div class="p-1.5 bg-green-100 text-green-600 rounded-lg dark:bg-green-900/30 dark:text-green-400">
                        <i class="bi bi-table"></i>
                    </div>
                    Data Titik
                </h5>
            </div>
            
            <div class="overflow-x-auto flex-1">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="data-table">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider dark:text-gray-400">Alt (m)</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider dark:text-gray-400">Lat, Long</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider dark:text-gray-400">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mb-2"></div>
                                    Memuat...
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination Controls --}}
            <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800 dark:border-gray-700">
                <button id="btn-prev" class="p-2 border border-gray-200 rounded-lg text-gray-600 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <span id="page-info" class="text-xs font-semibold text-gray-600 dark:text-gray-400">Page 1</span>
                <button id="btn-next" class="p-2 border border-gray-200 rounded-lg text-gray-600 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
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
    
    // Pagination Variables
    let currentPage = 1;
    const rowsPerPage = 10;
    let sortedData = [];

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

        // Toggle Markers Listener
        document.getElementById('toggle-markers').addEventListener('change', function(e) {
            if (e.target.checked) {
                markers.forEach(marker => marker.addTo(map));
            } else {
                markers.forEach(marker => marker.remove());
            }
        });

        loadData();
    });

    function loadData() {
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                currentData = data; // Store raw data
                sortedData = data.slice().reverse(); // Default sort: newest first
                
                updateStats(data);
                updateTable(); // Initial render
                updateMap(data);
                
                // Generate contours if checkbox is checked
                if (document.getElementById('toggle-contours').checked) {
                    updateContours(data);
                }
                
                console.log("Data loaded:", data);
            })
            .catch(error => console.error('Error fetching data:', error));
    }

    function updateStats(data) {
        if (!data || data.length === 0) {
            document.getElementById('stat-max-alt').textContent = '-';
            document.getElementById('stat-min-alt').textContent = '-';
            return;
        }

        const altitudes = data.map(item => parseFloat(item.altitude)).filter(val => !isNaN(val));
        
        if (altitudes.length > 0) {
            const maxAlt = Math.max(...altitudes);
            const minAlt = Math.min(...altitudes);
            
            document.getElementById('stat-max-alt').textContent = maxAlt.toFixed(1);
            document.getElementById('stat-min-alt').textContent = minAlt.toFixed(1);
        }
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
                    window.currentLegend = window.addLegend(map, result.breaks, result.palette);
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
                    <div class="font-sans text-sm">
                        <div class="font-bold mb-1 text-gray-800">Data Point</div>
                        <div class="grid grid-cols-2 gap-x-2 gap-y-1">
                            <span class="text-gray-500">Time:</span>
                            <span class="font-mono text-gray-700">${new Date(item.created_at).toLocaleString()}</span>
                            <span class="text-gray-500">Alt:</span>
                            <span class="font-mono text-gray-700">${item.altitude} m</span>
                            <span class="text-gray-500">Pressure:</span>
                            <span class="font-mono text-gray-700">${item.pressure || '-'} hPa</span>
                        </div>
                    </div>
                `;
                const marker = window.addMarker(map, lat, lng, popupContent);
                markers.push(marker);
                
                // Hide if checkbox is unchecked
                if (!document.getElementById('toggle-markers').checked) {
                    marker.remove();
                }
                
                bounds.extend([lat, lng]);
            }
        });

        if (markers.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }

    function updateTable() {
        const tbody = document.querySelector('#data-table tbody');
        tbody.innerHTML = '';

        if (sortedData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada data pengukuran.</td></tr>';
            updatePaginationControls();
            return;
        }

        // Pagination Logic
        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = startIndex + rowsPerPage;
        const pageData = sortedData.slice(startIndex, endIndex);

        pageData.forEach(item => {
            const date = new Date(item.created_at);
            const timeStr = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const dateStr = date.toLocaleDateString([], { day: 'numeric', month: 'short' });

            const row = `
                <tr class="hover:bg-gray-50 transition-colors dark:hover:bg-gray-700/50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-gray-200">${item.altitude}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500 font-mono dark:text-gray-400">
                        <div>${parseFloat(item.latitude).toFixed(5)}</div>
                        <div>${parseFloat(item.longitude).toFixed(5)}</div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-xs text-right text-gray-500 dark:text-gray-400">
                        <div class="font-medium text-gray-700 dark:text-gray-300">${timeStr}</div>
                        <div class="text-[10px]">${dateStr}</div>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });

        updatePaginationControls();
    }

    function updatePaginationControls() {
        const totalPages = Math.ceil(sortedData.length / rowsPerPage);
        const pageInfo = document.getElementById('page-info');
        const btnPrev = document.getElementById('btn-prev');
        const btnNext = document.getElementById('btn-next');

        if (pageInfo) {
            pageInfo.textContent = `Page ${currentPage} / ${totalPages || 1}`;
        }

        if (btnPrev) {
            btnPrev.disabled = currentPage === 1;
            btnPrev.onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    updateTable();
                }
            };
        }

        if (btnNext) {
            btnNext.disabled = currentPage === totalPages || totalPages === 0;
            btnNext.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    updateTable();
                }
            };
        }
    }
</script>
@endpush
