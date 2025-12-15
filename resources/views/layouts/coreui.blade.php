<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CoreUI Pro CSS -->
    <link href="{{ asset('assets/css/coreui.min.css') }}" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('assets/css/custom.css') }}?v={{ time() }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    @include('layouts.partials.sidebar')

    <div class="wrapper d-flex flex-column min-vh-100">
        <div class="body flex-grow-1">
            <!-- Header -->
            @include('layouts.partials.header')

            <!-- Breadcrumb -->
            @hasSection('breadcrumb')
            <div class="container-lg px-4">
                <nav aria-label="breadcrumb" class="my-3">
                    @yield('breadcrumb')
                </nav>
            </div>
            @endif

            <!-- Main Content -->
            <main class="container-lg px-4 flex-grow-1">
                @yield('content')
            </main>
        </div>

        <!-- Footer -->
        @include('layouts.partials.footer')
    </div>

    <!-- Delete Confirmation Modal -->
    @include('components.delete-modal')

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        @if(session('success'))
        <div class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-coreui-autohide="true" data-coreui-delay="4000">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-coreui-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="toast align-items-center text-white bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-coreui-autohide="true" data-coreui-delay="5000">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    {{ session('error') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-coreui-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        @endif

        @if(session('warning'))
        <div class="toast align-items-center text-white bg-warning border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-coreui-autohide="true" data-coreui-delay="4000">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ session('warning') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-coreui-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        @endif

        @if(session('info'))
        <div class="toast align-items-center text-white bg-info border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-coreui-autohide="true" data-coreui-delay="4000">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    {{ session('info') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-coreui-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        @endif
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- CoreUI Pro JS -->
    <script src="{{ asset('assets/js/coreui.bundle.min.js') }}"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Custom JS -->
    <script src="{{ asset('assets/js/custom.js') }}?v={{ time() }}"></script>

    @stack('scripts')
</body>
</html>
