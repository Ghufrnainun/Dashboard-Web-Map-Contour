<!doctype html>
<html lang="en" class="scroll-smooth">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MapContour IOT</title>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @stack('styles')
  </head>
  <body 
    class="bg-background text-foreground antialiased transition-colors duration-300"
    x-data="{ 
        darkMode: localStorage.getItem('darkMode') === 'true' || (localStorage.getItem('darkMode') === null && window.matchMedia('(prefers-color-scheme: dark)').matches),
        toggleTheme() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('darkMode', this.darkMode);
            if (this.darkMode) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    }"
    x-init="$watch('darkMode', val => val ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark')); if(darkMode) document.documentElement.classList.add('dark');"
  >
    <nav class="bg-background/80 backdrop-blur-md border-b border-border sticky top-0 z-50 transition-colors duration-300">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <a class="text-2xl font-bold bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent flex items-center gap-2 hover:opacity-80 transition-opacity" href="{{ route('home') }}">
                <img src="{{ asset('images/image.png') }}" alt="Logo" class="w-12 h-12">
                <span>JOHAR GEMING</span>
            </a>
            
            <div class="flex items-center gap-4">
                <a class="text-muted-foreground hover:text-primary font-medium transition-colors {{ request()->routeIs('home') ? '!text-primary' : '' }}" href="{{ route('home') }}">
                    <span class="hidden sm:inline">Home</span>
                    <i class="bi bi-house-door sm:hidden text-xl"></i>
                </a>
                
                <!-- Dark Mode Toggle -->
                <button 
                    @click="toggleTheme()" 
                    class="w-11 h-11 rounded-full bg-secondary text-secondary-foreground hover:bg-secondary/80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-300 flex items-center justify-center shadow-sm hover:shadow-md"
                    aria-label="Toggle Dark Mode"
                >
                    <i class="bi text-lg transition-transform duration-500" :class="darkMode ? 'bi-moon-stars-fill rotate-[360deg]' : 'bi-sun-fill rotate-0'"></i>
                </button>
            </div>
        </div>
      </div>
    </nav>

    <main class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 min-h-[calc(100vh-4rem-6rem)]">
        @if(session('success'))
            <div 
                x-data="{ show: true }" 
                x-show="show" 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2"
                class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative mb-6 shadow-sm flex items-center justify-between dark:bg-green-900/20 dark:border-green-800 dark:text-green-300" 
                role="alert"
            >
                <div class="flex items-center gap-2">
                    <i class="bi bi-check-circle-fill text-green-500"></i>
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-green-500 hover:text-green-700 dark:hover:text-green-200 transition-colors">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-background border-t border-border mt-auto py-8 transition-colors duration-300">
        <div class="container mx-auto px-4 text-center">
            <p class="text-muted-foreground text-sm">
                &copy; {{ date('Y') }} <span class="font-semibold text-foreground">MapContour IOT</span>. All rights reserved.
            </p>
        </div>
    </footer>

    @stack('scripts')
  </body>
</html>
