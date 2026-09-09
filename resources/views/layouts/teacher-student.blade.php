<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EduAdapt · @yield('page_title', 'Dashboard')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/student_teacher.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo_icon.png') }}">
    @stack('styles')
</head>

<body data-page="@yield('page')" class="role-{{ $roleViewPath ?? 'teacher' }}">
    <div class="container">

        @include($roleViewPath . '.partials.sidebar')
        <main class="main-content" id="mainContent">
            @include($roleViewPath . '.partials.topbar')
            <section class="content">
                @yield('content')
            </section>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/student_teacher.js') }}"></script>
    <script>
    window.user = {
        fullName: '{{ auth()->user()->full_name ?? auth()->user()->name ?? '' }}'
    };
</script>
    @stack('scripts')
</body>

</html>