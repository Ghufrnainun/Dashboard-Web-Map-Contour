@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    {{-- Create Project Section (Top on Mobile, Right on Desktop) --}}
    <div class="lg:col-span-1 lg:order-last">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24 dark:bg-gray-800 dark:border-gray-700 transition-colors duration-300">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 bg-blue-100 text-blue-600 rounded-lg dark:bg-blue-900/30 dark:text-blue-400">
                    <i class="bi bi-plus-lg text-xl"></i>
                </div>
                <h4 class="text-xl font-bold text-gray-800 dark:text-gray-100">Buat Project Baru</h4>
            </div>
            
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                <div class="mb-5">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-300">Nama Lokasi / Project</label>
                    <input type="text" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm py-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 transition-colors" id="name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Lahan Parkir A">
                    @error('name')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-5">
                    <label for="surveyor_name" class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-300">Nama Surveyor</label>
                    <input type="text" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm py-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 transition-colors" id="surveyor_name" name="surveyor_name" value="{{ old('surveyor_name') }}" required placeholder="Nama Anda">
                    @error('surveyor_name')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2 dark:text-gray-300">Deskripsi (Opsional)</label>
                    <textarea class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 transition-colors" id="description" name="description" rows="3" placeholder="Catatan tambahan...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 shadow-lg shadow-blue-600/20 hover:shadow-blue-600/30 transform hover:-translate-y-0.5">
                    <i class="bi bi-save"></i> Simpan Project
                </button>
            </form>
        </div>
    </div>

    {{-- Project List Section --}}
    <div class="lg:col-span-2">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Daftar Project</h2>
            <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm font-medium dark:bg-gray-800 dark:text-gray-400">{{ $projects->count() }} Projects</span>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($projects as $project)
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 overflow-hidden relative group dark:bg-gray-800 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300 dark:bg-blue-900/20 dark:text-blue-400">
                            <i class="bi bi-folder2-open text-xl"></i>
                        </div>
                        <span class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded-md dark:bg-gray-700/50 dark:text-gray-500">
                            {{ $project->created_at->diffForHumans() }}
                        </span>
                    </div>
                    
                    <h5 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors dark:text-gray-100 dark:group-hover:text-blue-400">{{ $project->name }}</h5>
                    
                    <div class="flex items-center gap-2 text-sm text-gray-500 mb-3 dark:text-gray-400">
                        <i class="bi bi-person-circle"></i>
                        <span>{{ $project->surveyor_name }}</span>
                    </div>
                    
                    <p class="text-gray-600 text-sm mb-0 line-clamp-2 dark:text-gray-400">{{ $project->description ?? 'Tidak ada deskripsi' }}</p>
                    
                    <a href="{{ route('projects.show', $project->id) }}" class="absolute inset-0 z-10">
                        <span class="sr-only">Lihat Detail</span>
                    </a>
                </div>
                <div class="bg-gray-50/50 px-6 py-3 border-t border-gray-100 flex justify-between items-center dark:bg-gray-700/30 dark:border-gray-700">
                    <span class="text-xs text-gray-400 font-mono">ID: #{{ $project->id }}</span>
                    <span class="text-blue-600 text-sm font-medium group-hover:translate-x-1 transition-transform duration-200 flex items-center gap-1 dark:text-blue-400">
                        Buka <i class="bi bi-arrow-right"></i>
                    </span>
                </div>
            </div>
            @empty
            <div class="col-span-full">
                <div class="bg-blue-50 text-blue-700 p-8 rounded-2xl text-center border border-blue-100 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-300">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 text-blue-600 mb-4 dark:bg-blue-800/50 dark:text-blue-300">
                        <i class="bi bi-journal-plus text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-bold mb-2">Belum ada project</h3>
                    <p class="text-blue-600/80 mb-0 dark:text-blue-300/80">Silakan buat project baru menggunakan form di samping untuk memulai.</p>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
