@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    {{-- Create Project Section (Left Side on Desktop) --}}
    <div class="lg:col-span-4 lg:order-last animate-fade-in-up">
        <div class="bg-card rounded-3xl shadow-sm border border-border p-8 sticky top-28 transition-all duration-300">
            <div class="flex items-center gap-4 mb-8">
                <div class="p-3 bg-primary text-primary-foreground rounded-xl shadow-md shadow-primary/20">
                    <i class="bi bi-plus-lg text-xl"></i>
                </div>
                <div>
                    <h4 class="text-xl font-bold text-card-foreground">Project Baru</h4>
                    <p class="text-sm text-muted-foreground">Mulai pemetaan area baru</p>
                </div>
            </div>
            
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                <div class="space-y-5">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-foreground mb-2">Nama Lokasi / Project</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-muted-foreground">
                                <i class="bi bi-geo-alt"></i>
                            </span>
                            <input type="text" class="w-full pl-11 pr-4 py-3 rounded-xl border-input focus:border-ring focus:ring-4 focus:ring-ring/10 transition-all bg-background focus:bg-background text-foreground" id="name" name="name" value="{{ old('name') }}" required placeholder="Masukkan nama project">
                        </div>
                        @error('name')
                            <div class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="surveyor_name" class="block text-sm font-semibold text-foreground mb-2">Nama Surveyor</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-muted-foreground">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" class="w-full pl-11 pr-4 py-3 rounded-xl border-input focus:border-ring focus:ring-4 focus:ring-ring/10 transition-all bg-background focus:bg-background text-foreground" id="surveyor_name" name="surveyor_name" value="{{ old('surveyor_name') }}" required placeholder="Masukkan nama surveyor">
                        </div>
                        @error('surveyor_name')
                            <div class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-semibold text-foreground mb-2">Deskripsi (Opsional)</label>
                        <textarea class="w-full px-4 py-3 rounded-xl border-input focus:border-ring focus:ring-4 focus:ring-ring/10 transition-all bg-background focus:bg-background text-foreground" id="description" name="description" rows="3" placeholder="Tambahkan catatan detail tentang project ini...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-primary-foreground font-bold py-3.5 px-4 rounded-xl transition-all duration-300 flex items-center justify-center gap-2 shadow-md shadow-primary/20 hover:shadow-lg hover:shadow-primary/30 transform hover:-translate-y-0.5">
                        <i class="bi bi-plus-circle"></i> Buat Project
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Project List Section --}}
    <div class="lg:col-span-8 animate-fade-in-up delay-100">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-foreground tracking-tight">Daftar Project</h2>
                <p class="text-muted-foreground mt-1">Kelola semua data pemetaan Anda</p>
            </div>
            <span class="px-4 py-2 bg-card border border-border text-muted-foreground rounded-full text-sm font-bold shadow-sm">
                {{ $projects->count() }} Projects
            </span>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($projects as $project)
            <div class="group bg-card rounded-3xl p-6 shadow-sm hover:shadow-lg border border-border transition-all duration-300 hover:-translate-y-1 hover:scale-[1.01] relative overflow-hidden">
                <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity">
                    <i class="bi bi-map text-6xl text-foreground"></i>
                </div>

                <div class="flex justify-between items-start mb-6 relative z-10">
                    <div class="p-3 bg-secondary text-secondary-foreground rounded-xl border border-border group-hover:bg-primary group-hover:text-primary-foreground group-hover:border-primary transition-colors duration-300">
                        <i class="bi bi-folder2-open text-xl"></i>
                    </div>
                    <span class="text-xs font-bold text-muted-foreground bg-secondary px-3 py-1.5 rounded-full">
                        {{ $project->created_at->diffForHumans() }}
                    </span>
                </div>
                
                <h5 class="text-xl font-bold text-card-foreground mb-2 group-hover:text-primary transition-colors relative z-10 line-clamp-1">{{ $project->name }}</h5>
                
                <div class="flex items-center gap-2 text-sm text-muted-foreground mb-4 relative z-10">
                    <i class="bi bi-person-circle text-primary"></i>
                    <span class="font-medium">{{ $project->surveyor_name }}</span>
                </div>
                
                <p class="text-muted-foreground text-sm mb-6 line-clamp-2 h-10 relative z-10">{{ $project->description ?? 'Tidak ada deskripsi tambahan untuk project ini.' }}</p>
                
                <div class="flex items-center justify-between pt-4 border-t border-border relative z-30">
                    <span class="text-xs font-mono text-muted-foreground">ID: #{{ $project->id }}</span>
                    <div class="flex items-center gap-3">
                        <form action="{{ route('projects.destroy', $project->id) }}" method="POST" class="relative z-30" onsubmit="return confirm('Apakah Anda yakin ingin menghapus project ini? Data tidak dapat dikembalikan.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 text-sm font-bold px-3 py-1 rounded border border-transparent hover:border-red-500 hover:bg-red-500 hover:text-black transition-all duration-300">
                                Hapus
                            </button>
                        </form>
                        <span class="text-primary text-sm font-bold group-hover:translate-x-1 transition-transform duration-200 flex items-center gap-1">
                            Buka Project <i class="bi bi-arrow-right"></i>
                        </span>
                    </div>
                </div>

                <a href="{{ route('projects.show', $project->id) }}" class="absolute inset-0 z-20">
                    <span class="sr-only">Lihat Detail</span>
                </a>
            </div>
            @empty
            <div class="col-span-full">
                <div class="bg-muted/50 border-2 border-dashed border-border rounded-3xl p-12 text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-card text-primary shadow-sm mb-6">
                        <i class="bi bi-journal-plus text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-foreground mb-2">Belum ada project</h3>
                    <p class="text-muted-foreground mb-0">Silakan buat project baru menggunakan form di samping untuk memulai pemetaan.</p>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
