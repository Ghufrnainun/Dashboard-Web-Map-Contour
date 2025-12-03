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
    class="bg-gray-50 text-gray-800 antialiased transition-colors duration-300 dark:bg-gray-900 dark:text-gray-100"
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
    <nav class="bg-white/80 backdrop-blur-md border-b border-gray-200 sticky top-0 z-50 dark:bg-gray-800/80 dark:border-gray-700 transition-colors duration-300">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <a class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent flex items-center gap-2 hover:opacity-80 transition-opacity" href="{{ route('home') }}">
                <i class="bi bi-map-fill text-blue-600 dark:text-blue-400"></i> 
                <span>MapContour IOT</span>
            </a>
            
            <div class="flex items-center gap-4">
                <a class="text-gray-600 hover:text-blue-600 font-medium transition-colors dark:text-gray-300 dark:hover:text-blue-400 {{ request()->routeIs('home') ? '!text-blue-600 dark:!text-blue-400' : '' }}" href="{{ route('home') }}">
                    Home
                </a>
                
                <!-- Dark Mode Toggle -->
                <button 
                    @click="toggleTheme()" 
                    class="p-2 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                    aria-label="Toggle Dark Mode"
                >
                    <i class="bi" :class="darkMode ? 'bi-moon-stars-fill' : 'bi-sun-fill'"></i>
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

    <footer class="bg-white border-t border-gray-200 mt-auto py-8 dark:bg-gray-800 dark:border-gray-700 transition-colors duration-300">
        <div class="container mx-auto px-4 text-center">
            <p class="text-gray-500 text-sm dark:text-gray-400">
                &copy; {{ date('Y') }} <span class="font-semibold text-gray-700 dark:text-gray-200">MapContour IOT</span>. All rights reserved.
            </p>
        </div>
    </footer>

    @stack('scripts')
  </body>
</html>
