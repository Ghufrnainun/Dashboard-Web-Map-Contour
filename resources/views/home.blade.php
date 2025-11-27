@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    {{-- Create Project Section (Top on Mobile, Right on Desktop) --}}
    <div class="md:col-span-1 md:order-last">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-8">
            <h4 class="text-xl font-bold mb-4 flex items-center gap-2 text-gray-800">
                <i class="bi bi-plus-circle text-blue-600"></i> Buat Project Baru
            </h4>
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lokasi / Project</label>
                    <input type="text" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm" id="name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Lahan Parkir A">
                    @error('name')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="surveyor_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Surveyor</label>
                    <input type="text" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm" id="surveyor_name" name="surveyor_name" value="{{ old('surveyor_name') }}" required placeholder="Nama Anda">
                    @error('surveyor_name')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi (Opsional)</label>
                    <textarea class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm" id="description" name="description" rows="3" placeholder="Catatan tambahan...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2">
                    <i class="bi bi-save"></i> Simpan Project
                </button>
            </form>
        </div>
    </div>

    {{-- Project List Section --}}
    <div class="md:col-span-2">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Daftar Project</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($projects as $project)
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 border border-gray-100 overflow-hidden relative group">
                <div class="p-6">
                    <h5 class="text-lg font-semibold text-blue-600 mb-2">{{ $project->name }}</h5>
                    <h6 class="text-sm text-gray-500 mb-3 flex items-center gap-2">
                        <i class="bi bi-person"></i> {{ $project->surveyor_name }}
                    </h6>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $project->description ?? 'Tidak ada deskripsi' }}</p>
                    <p class="text-xs text-gray-400 mb-0">Dibuat: {{ $project->created_at->format('d M Y H:i') }}</p>
                    <a href="{{ route('projects.show', $project->id) }}" class="absolute inset-0 z-10">
                        <span class="sr-only">Lihat Detail</span>
                    </a>
                </div>
                <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 flex justify-end">
                    <span class="text-blue-600 text-sm font-medium group-hover:translate-x-1 transition-transform duration-200 flex items-center gap-1">
                        Lihat Detail <i class="bi bi-arrow-right"></i>
                    </span>
                </div>
            </div>
            @empty
            <div class="col-span-full">
                <div class="bg-blue-50 text-blue-700 p-4 rounded-lg text-center border border-blue-200">
                    Belum ada project. Silakan buat project baru di samping.
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
