@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    {{-- Create Project Section (Left Side on Desktop) --}}
    <div class="lg:col-span-4 lg:order-last">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-8 sticky top-28 dark:bg-gray-800 dark:border-gray-700 transition-all duration-300">
            <div class="flex items-center gap-4 mb-8">
                <div class="p-3 bg-blue-600 text-white rounded-xl shadow-md shadow-blue-600/20">
                    <i class="bi bi-plus-lg text-xl"></i>
                </div>
                <div>
                    <h4 class="text-xl font-bold text-gray-900 dark:text-white">Project Baru</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Mulai pemetaan area baru</p>
                </div>
            </div>
            
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                <div class="space-y-5">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2 dark:text-gray-300">Nama Lokasi / Project</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-gray-400">
                                <i class="bi bi-geo-alt"></i>
                            </span>
                            <input type="text" class="w-full pl-11 pr-4 py-3 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all bg-gray-50 focus:bg-white dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:focus:bg-gray-700" id="name" name="name" value="{{ old('name') }}" required placeholder="Masukkan nama project">
                        </div>
                        @error('name')
                            <div class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="surveyor_name" class="block text-sm font-semibold text-gray-700 mb-2 dark:text-gray-300">Nama Surveyor</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-gray-400">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" class="w-full pl-11 pr-4 py-3 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all bg-gray-50 focus:bg-white dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:focus:bg-gray-700" id="surveyor_name" name="surveyor_name" value="{{ old('surveyor_name') }}" required placeholder="Masukkan nama surveyor">
                        </div>
                        @error('surveyor_name')
                            <div class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2 dark:text-gray-300">Deskripsi (Opsional)</label>
                        <textarea class="w-full px-4 py-3 rounded-xl border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all bg-gray-50 focus:bg-white dark:bg-gray-700/50 dark:border-gray-600 dark:text-white dark:focus:bg-gray-700" id="description" name="description" rows="3" placeholder="Tambahkan catatan detail tentang project ini...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-4 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 shadow-md shadow-blue-600/20 hover:shadow-lg hover:shadow-blue-600/30 transform hover:-translate-y-0.5">
                        <i class="bi bi-plus-circle"></i> Buat Project
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Project List Section --}}
    <div class="lg:col-span-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Daftar Project</h2>
                <p class="text-gray-500 mt-1 dark:text-gray-400">Kelola semua data pemetaan Anda</p>
            </div>
            <span class="px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-full text-sm font-bold shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300">
                {{ $projects->count() }} Projects
            </span>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($projects as $project)
            <div class="group bg-white rounded-3xl p-6 shadow-sm hover:shadow-md border border-gray-200 transition-all duration-300 hover:-translate-y-1 relative overflow-hidden dark:bg-gray-800 dark:border-gray-700">
                <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity">
                    <i class="bi bi-map text-6xl text-gray-900 dark:text-white"></i>
                </div>

                <div class="flex justify-between items-start mb-6 relative z-10">
                    <div class="p-3 bg-gray-50 text-gray-900 rounded-xl border border-gray-100 group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-600 transition-colors duration-300 dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600">
                        <i class="bi bi-folder2-open text-xl"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-400 bg-gray-50 px-3 py-1.5 rounded-full dark:bg-gray-700/50 dark:text-gray-500">
                        {{ $project->created_at->diffForHumans() }}
                    </span>
                </div>
                
                <h5 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors relative z-10 dark:text-white dark:group-hover:text-blue-400 line-clamp-1">{{ $project->name }}</h5>
                
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-4 dark:text-gray-400 relative z-10">
                    <i class="bi bi-person-circle text-blue-500"></i>
                    <span class="font-medium">{{ $project->surveyor_name }}</span>
                </div>
                
                <p class="text-gray-600 text-sm mb-6 line-clamp-2 h-10 dark:text-gray-400 relative z-10">{{ $project->description ?? 'Tidak ada deskripsi tambahan untuk project ini.' }}</p>
                
                <div class="flex items-center justify-between pt-4 border-t border-gray-50 dark:border-gray-700/50 relative z-10">
                    <span class="text-xs font-mono text-gray-400">ID: #{{ $project->id }}</span>
                    <span class="text-blue-600 text-sm font-bold group-hover:translate-x-1 transition-transform duration-200 flex items-center gap-1 dark:text-blue-400">
                        Buka Project <i class="bi bi-arrow-right"></i>
                    </span>
                </div>

                <a href="{{ route('projects.show', $project->id) }}" class="absolute inset-0 z-20">
                    <span class="sr-only">Lihat Detail</span>
                </a>
            </div>
            @empty
            <div class="col-span-full">
                <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-3xl p-12 text-center dark:bg-gray-800/50 dark:border-gray-700">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white text-blue-600 shadow-sm mb-6 dark:bg-gray-800 dark:text-blue-400">
                        <i class="bi bi-journal-plus text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2 dark:text-white">Belum ada project</h3>
                    <p class="text-gray-500 mb-0 dark:text-gray-400">Silakan buat project baru menggunakan form di samping untuk memulai pemetaan.</p>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
