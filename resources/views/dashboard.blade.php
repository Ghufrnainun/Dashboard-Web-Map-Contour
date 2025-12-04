@extends('layouts.app')

@section('content')
<div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 print:hidden animate-fade-in-up">
    <div class="flex flex-col items-center md:items-start w-full md:w-auto">
        <div class="flex items-center gap-3 mb-1">
            <a href="{{ route('home') }}" class="text-muted-foreground hover:text-primary transition-colors">
                <i class="bi bi-arrow-left text-xl"></i>
            </a>
            <h2 class="text-3xl font-bold text-foreground tracking-tight">{{ $project->name }}</h2>
        </div>
        <div class="flex flex-col items-center md:flex-row md:items-center gap-2 md:gap-4 ml-0 md:ml-8 text-sm mt-2 md:mt-0">
            <div class="flex items-center gap-2 text-muted-foreground">
                <i class="bi bi-person-circle text-primary"></i>
                <span class="font-semibold text-foreground">{{ $project->surveyor_name }}</span>
            </div>
            <span class="hidden md:inline text-muted-foreground/50">|</span>
            <div class="flex items-center gap-2 text-muted-foreground">
                <i class="bi bi-calendar-event"></i>
                <span>{{ $project->created_at->format('d M Y, H:i') }}</span>
            </div>
            <span class="hidden md:inline text-muted-foreground/50">|</span>
            <span class="font-mono bg-secondary px-2 py-0.5 rounded text-xs font-bold text-muted-foreground w-fit">ID: #{{ $project->id }}</span>
        </div>
    </div>
    <div class="flex gap-3 w-full md:w-auto">
        <button onclick="window.print()" class="flex-1 md:flex-none inline-flex items-center justify-center px-4 py-2 bg-card border border-border rounded-xl font-semibold text-foreground shadow-sm hover:bg-secondary transition-all">
            <i class="bi bi-printer mr-2"></i> Print
        </button>
        <button onclick="loadData()" class="flex-1 md:flex-none inline-flex items-center justify-center px-4 py-2 bg-primary text-primary-foreground rounded-xl font-semibold shadow-lg shadow-primary/20 hover:bg-primary/90 hover:shadow-primary/30 transition-all">
            <i class="bi bi-arrow-clockwise mr-2"></i> Refresh
        </button>
    </div>
</div>

{{-- Print Header --}}
<div class="hidden print:block mb-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $project->name }}</h1>
    <div class="flex gap-4 text-sm text-gray-600">
        <span>Surveyor: {{ $project->surveyor_name }}</span>
        <span>|</span>
        <span>Date: {{ $project->created_at->format('d M Y, H:i') }}</span>
        <span>|</span>
        <span>ID: #{{ $project->id }}</span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 print:block">
    <!-- Map Section (Left, 8 Columns) -->
    <div class="lg:col-span-8 flex flex-col gap-6 print:mb-6 print:break-inside-avoid print:block print:w-full animate-fade-in-up delay-100">
        <div class="bg-card rounded-3xl shadow-sm border border-border overflow-hidden transition-colors duration-300 h-full flex flex-col print:h-auto print:block print:border print:border-gray-300 print:shadow-none">
            <div class="px-6 py-4 border-b border-border flex flex-col sm:flex-row justify-between items-center gap-4 bg-card print:hidden">
                <h5 class="text-lg font-bold text-card-foreground flex items-center gap-3">
                    <div class="p-2 bg-secondary text-secondary-foreground rounded-lg">
                        <i class="bi bi-map-fill"></i>
                    </div>
                    Visualisasi Kontur
                </h5>
                
                <div class="flex items-center gap-2 bg-muted p-1 rounded-xl border border-border">
                    <label class="cursor-pointer px-3 py-1.5 rounded-lg transition-all has-[:checked]:bg-card has-[:checked]:text-primary has-[:checked]:shadow-sm">
                        <input type="checkbox" id="toggle-contours" class="sr-only" checked>
                        <span class="text-sm font-semibold flex items-center gap-2">
                            <i class="bi bi-layers"></i> Kontur
                        </span>
                    </label>
                    <div class="w-px h-4 bg-border"></div>
                    <label class="cursor-pointer px-3 py-1.5 rounded-lg transition-all has-[:checked]:bg-card has-[:checked]:text-primary has-[:checked]:shadow-sm">
                        <input type="checkbox" id="toggle-markers" class="sr-only" checked>
                        <span class="text-sm font-semibold flex items-center gap-2">
                            <i class="bi bi-geo-alt"></i> Marker
                        </span>
                    </label>
                </div>
            </div>
            
            <div class="relative flex-1 min-h-[600px] w-full bg-muted/20 print:min-h-0 print:h-[500px] print:block print:relative">
                <div id="map-container" class="absolute inset-0 z-0 print:relative print:inset-auto print:h-full print:w-full"></div>
            </div>
        </div>
    </div>

    <!-- Right Sidebar (Stats & Table) -->
    <div class="lg:col-span-4 flex flex-col gap-6 print:block animate-fade-in-up delay-200">
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 gap-4 print:mb-6">
            <div class="bg-card p-5 rounded-3xl shadow-sm border border-border relative overflow-hidden group hover:shadow-md transition-all duration-300 hover:-translate-y-1 print:border print:border-gray-200 print:shadow-none">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity print:hidden">
                    <i class="bi bi-arrow-up-circle-fill text-5xl text-red-500"></i>
                </div>
                <div class="flex items-center gap-2 mb-3 text-red-500 bg-red-50 w-fit px-3 py-1 rounded-full dark:bg-red-900/20 dark:text-red-400 print:bg-transparent print:p-0">
                    <i class="bi bi-arrow-up-circle-fill"></i>
                    <span class="text-xs font-bold uppercase tracking-wider">Tertinggi</span>
                </div>
                <div class="text-3xl font-bold text-card-foreground mb-1" id="stat-max-alt">-</div>
                <div class="text-xs font-medium text-muted-foreground">Meter Mdpl</div>
            </div>
            <div class="bg-card p-5 rounded-3xl shadow-sm border border-border relative overflow-hidden group hover:shadow-md transition-all duration-300 hover:-translate-y-1 print:border print:border-gray-200 print:shadow-none">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity print:hidden">
                    <i class="bi bi-arrow-down-circle-fill text-5xl text-teal-500"></i>
                </div>
                <div class="flex items-center gap-2 mb-3 text-teal-500 bg-teal-50 w-fit px-3 py-1 rounded-full dark:bg-teal-900/20 dark:text-teal-400 print:bg-transparent print:p-0">
                    <i class="bi bi-arrow-down-circle-fill"></i>
                    <span class="text-xs font-bold uppercase tracking-wider">Terendah</span>
                </div>
                <div class="text-3xl font-bold text-card-foreground mb-1" id="stat-min-alt">-</div>
                <div class="text-xs font-medium text-muted-foreground">Meter Mdpl</div>
            </div>
        </div>

        <!-- Data Table Section -->
        <div class="bg-card rounded-3xl shadow-sm border border-border overflow-hidden flex-1 flex flex-col transition-colors duration-300 print:shadow-none print:border-none">
            <div class="px-6 py-5 border-b border-border bg-card print:px-0 print:py-2">
                <h5 class="text-lg font-bold text-card-foreground flex items-center gap-3">
                    <div class="p-2 bg-secondary text-secondary-foreground rounded-lg print:hidden">
                        <i class="bi bi-table"></i>
                    </div>
                    Data Pengukuran
                </h5>
            </div>
            
            <div class="overflow-x-auto flex-1">
                <table class="min-w-full divide-y divide-border" id="data-table">
                    <thead class="bg-muted/50 print:bg-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-muted-foreground uppercase tracking-wider print:px-2 print:py-2">Alt (m)</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-muted-foreground uppercase tracking-wider print:px-2 print:py-2">Koordinat</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-muted-foreground uppercase tracking-wider print:px-2 print:py-2">Waktu</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-muted-foreground uppercase tracking-wider print:hidden">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-sm text-muted-foreground">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                                    <span class="font-medium">Mengambil data...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination Controls --}}
            <div class="px-6 py-4 border-t border-border flex items-center justify-between bg-muted/30 print:hidden">
                <button id="btn-prev" class="p-2.5 border border-border rounded-xl text-muted-foreground bg-card hover:bg-secondary disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <span id="page-info" class="text-sm font-bold text-foreground font-mono">Page 1</span>
                <button id="btn-next" class="p-2.5 border border-border rounded-xl text-muted-foreground bg-card hover:bg-secondary disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="delete-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-background/80 backdrop-blur-sm transition-opacity opacity-0" id="delete-modal-backdrop"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Modal Panel -->
            <div class="relative transform overflow-hidden rounded-3xl bg-card text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" id="delete-modal-panel">
                <div class="bg-card px-4 pb-4 pt-5 sm:p-6 sm:pb-4 border border-border">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10 dark:bg-red-900/20">
                            <i class="bi bi-exclamation-triangle text-red-600 dark:text-red-400 text-xl"></i>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-lg font-semibold leading-6 text-foreground" id="modal-title">Hapus Data Pengukuran?</h3>
                            <div class="mt-2">
                                <p class="text-sm text-muted-foreground">Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan dan data akan hilang permanen.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-muted/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-3">
                    <button type="button" id="confirm-delete-btn" class="inline-flex w-full justify-center rounded-xl bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto transition-colors">Hapus</button>
                    <button type="button" onclick="closeDeleteModal()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-card px-3 py-2 text-sm font-semibold text-foreground shadow-sm ring-1 ring-inset ring-border hover:bg-secondary sm:mt-0 sm:w-auto transition-colors">Batal</button>
                </div>
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
    let deleteId = null; // Store ID to delete

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

        // Handle Print Events to resize map
        window.addEventListener('beforeprint', () => {
            if (map) {
                map.invalidateSize();
                // Optional: Fit bounds again to ensure markers are visible
                if (markers.length > 0) {
                    const group = new L.featureGroup(markers);
                    map.fitBounds(group.getBounds());
                }
            }
        });

        window.addEventListener('afterprint', () => {
            if (map) {
                map.invalidateSize();
            }
        });
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
                    <div class="font-sans text-sm min-w-[200px]">
                        <div class="font-bold mb-2 text-foreground border-b border-border pb-1">Data Point</div>
                        <div class="grid grid-cols-[80px_1fr] gap-y-1">
                            <span class="text-muted-foreground text-xs uppercase tracking-wider font-semibold">Time</span>
                            <span class="font-mono text-foreground text-xs">${new Date(item.created_at).toLocaleString()}</span>
                            
                            <span class="text-muted-foreground text-xs uppercase tracking-wider font-semibold">Alt</span>
                            <span class="font-mono text-foreground text-xs font-bold">${item.altitude} m</span>
                            
                            <span class="text-muted-foreground text-xs uppercase tracking-wider font-semibold">Pressure</span>
                            <span class="font-mono text-foreground text-xs">${item.pressure || '-'} hPa</span>
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
            tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-8 text-center text-sm text-muted-foreground">Belum ada data pengukuran.</td></tr>';
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
                <tr class="hover:bg-muted/50 transition-colors">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-foreground">${item.altitude}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-xs text-muted-foreground font-mono">
                        <div>${parseFloat(item.latitude).toFixed(5)}</div>
                        <div>${parseFloat(item.longitude).toFixed(5)}</div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-xs text-right text-muted-foreground">
                        <div class="font-medium text-foreground">${timeStr}</div>
                        <div class="text-[10px]">${dateStr}</div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-center print:hidden">
                        <button onclick="openDeleteModal(${item.id})" class="p-2 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg transition-colors" title="Hapus Data">
                            <i class="bi bi-trash"></i>
                        </button>
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

    // Modal Functions
    function openDeleteModal(id) {
        deleteId = id;
        const modal = document.getElementById('delete-modal');
        const backdrop = document.getElementById('delete-modal-backdrop');
        const panel = document.getElementById('delete-modal-panel');
        
        modal.classList.remove('hidden');
        
        // Animate in
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            panel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
            panel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
        }, 10);
    }

    function closeDeleteModal() {
        deleteId = null;
        const modal = document.getElementById('delete-modal');
        const backdrop = document.getElementById('delete-modal-backdrop');
        const panel = document.getElementById('delete-modal-panel');
        
        // Animate out
        backdrop.classList.add('opacity-0');
        panel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
        panel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // Bind confirm button
    document.getElementById('confirm-delete-btn').addEventListener('click', function() {
        if (deleteId) {
            deleteMeasurement(deleteId);
            closeDeleteModal();
        }
    });

    function deleteMeasurement(id) {
        // if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) return; // Replaced by modal

        fetch(`/measurements/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Remove from local data arrays
                currentData = currentData.filter(item => item.id !== id);
                sortedData = sortedData.filter(item => item.id !== id);
                
                // Refresh UI
                updateStats(currentData);
                updateTable();
                updateMap(currentData);
                
                // Update contours if active
                if (document.getElementById('toggle-contours').checked) {
                    updateContours(currentData);
                }
            } else {
                alert('Gagal menghapus data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus data');
        });
    }
</script>
@endpush
