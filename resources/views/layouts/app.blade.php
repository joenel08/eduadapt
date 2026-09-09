<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EduAdapt · @yield('page_title', 'Dashboard')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo_icon.png') }}">
</head>
<body data-page="@yield('page')">

    <div class="container">
        {{-- Sidebar overlay --}}
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        {{-- SIDEBAR (partial) --}}
        @include($roleViewPath . '.partials.sidebar')

        <main class="main-content" id="mainContent">
            {{-- TOP BAR (partial) --}}
            @include($roleViewPath . '.partials.topbar')

            {{-- MAIN CONTENT (yielded from child views) --}}
            <section class="content">
                @yield('content')
            </section>
        </main>
    </div>

    {{-- Global modals (if any) --}}
   

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="{{ asset('js/style.js') }}"></script>
    @stack('scripts')
</body>
</html>