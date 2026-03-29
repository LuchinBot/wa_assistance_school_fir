<!-- layouts/base.blade.php -->
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AUNA')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Mona+Sans:ital,wght@0,200..900;1,200..900&display=swap"
        rel="stylesheet">
    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Externos -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @stack('styles')
</head>


<body data-controller="{{ $extend['controller'] ?? request()->segment(1) }}"
    data-view="{{ $extend['view'] ?? 'index' }}" class="@yield('body-class')" id="mainContent"
    class="transition-all duration-300 opacity-100">
    @routes
    @yield('body')
    @include('partials.loading')
    {{-- <script src="{{ mix('js/app.js') }}"></script> --}}
    @stack('scripts')
</body>

</html>
