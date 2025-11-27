<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MapContour IOT</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    @stack('styles')
  </head>
  <body class="bg-gray-50 text-gray-800 font-sans antialiased">
    <nav class="bg-white shadow-sm mb-8">
      <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <a class="text-xl font-bold text-blue-600 flex items-center gap-2" href="{{ route('home') }}">
                <i class="bi bi-map-fill"></i> MapContour IOT
            </a>
            
            <div class="hidden md:flex space-x-8">
                <a class="text-gray-600 hover:text-blue-600 {{ request()->routeIs('home') ? 'font-bold text-blue-600' : '' }}" href="{{ route('home') }}">Home</a>
            </div>
        </div>
      </div>
    </nav>

    <div class="container mx-auto px-4 mb-12">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @yield('content')
    </div>

    <footer class="text-center py-8 text-gray-500 text-sm">
        <small>&copy; {{ date('Y') }} MapContour IOT. All rights reserved.</small>
    </footer>

    @stack('scripts')
  </body>
</html>
