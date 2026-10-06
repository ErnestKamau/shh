<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- <meta http-equiv="Content-Security-Policy" content="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"> --}}

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.favicon')

    @yield('title')

    <style>
        :root {
            --touch-min: 44px;
            --touch-input-font: 16px;
            --page-pad-x: 0.75rem;
        }

        body {
            max-width: 100%;
            overflow-x: hidden;
        }

        .btn,
        .btn-sm {
            min-height: var(--touch-min);
        }

        .form-control,
        .custom-select,
        select.form-control {
            min-height: var(--touch-min);
            font-size: var(--touch-input-font) !important;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            max-width: 100%;
        }

        .nav-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .nav-tabs .nav-link {
            white-space: nowrap;
            min-height: var(--touch-min);
        }

        @media (max-width: 767.98px) {
            .tablet-intake-header {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 0.75rem;
            }

            .tablet-intake-header .btn {
                width: 100%;
            }
        }
    </style>
    
    @livewireStyles

    <!-- Scripts -->
    <link rel="stylesheet" href="/assets/css/font-awesome/all.min.css">
    <link rel="stylesheet" href="/material-design/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="/assets/css/bootstrap/bootstrap4.4.1.min.css">

    <style type="text/css">
        select,
        .select2.select2-container.select2-container--default {
            width: 100% !important;
        }

        button.dt-button,
        div.dt-button,
        a.dt-button {
            padding: 2px 6px !important;
        }

        .bell {
            display: block;
            -webkit-animation: ring 4s .01s ease-in-out infinite;
            -webkit-transform-origin: 50% 4px;
            -moz-animation: ring 4s .01s ease-in-out infinite;
            -moz-transform-origin: 50% 4px;
            animation: ring 4s .01s ease-in-out infinite;
            transform-origin: 50% 4px;
        }

        /* Enhanced Breadcrumb Styles */
        .breadcrumb-container {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 249, 250, 0.8) 100%);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border-radius: 12px;
            padding: 12px 20px;
            margin: 15px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05), inset 0 1px 2px rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }

        .breadcrumb-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 123, 255, 0.05), transparent);
            transition: left 0.8s ease;
        }

        .breadcrumb-container:hover::before {
            left: 100%;
        }

        .breadcrumb-modern {
            display: flex;
            align-items: center;
            list-style: none;
            margin: 0;
            padding: 0;
            font-size: 14px;
            font-weight: 500;
        }

        .breadcrumb-item-modern {
            display: flex;
            align-items: center;
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .breadcrumb-item-modern:not(:last-child)::after {
            content: '';
            width: 6px;
            height: 6px;
            border-right: 2px solid #6c757d;
            border-bottom: 2px solid #6c757d;
            transform: rotate(-45deg);
            margin: 0 12px;
            opacity: 0.6;
            transition: all 0.3s ease;
        }

        .breadcrumb-item-modern:hover:not(:last-child)::after {
            border-color: #007bff;
            opacity: 1;
            transform: rotate(-45deg) scale(1.2);
        }

        .breadcrumb-link {
            display: flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            color: #6c757d;
            background: transparent;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .breadcrumb-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 123, 255, 0.08), transparent);
            transition: left 0.6s ease;
        }

        .breadcrumb-link:hover {
            color: #007bff;
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.08) 0%, rgba(74, 144, 226, 0.03) 100%);
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 2px 10px rgba(0, 123, 255, 0.15);
            text-decoration: none;
        }

        .breadcrumb-link:hover::before {
            left: 100%;
        }

        .breadcrumb-link:active {
            transform: translateY(-1px) scale(1.01);
        }

        .breadcrumb-current {
            display: flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 8px;
            color: #495057;
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.08) 0%, rgba(74, 144, 226, 0.05) 100%);
            font-weight: 600;
            position: relative;
            overflow: hidden;
        }

        .breadcrumb-current::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% {
                left: -100%;
            }

            50% {
                left: 100%;
            }

            100% {
                left: 100%;
            }
        }

        .breadcrumb-icon {
            margin-right: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .breadcrumb-link:hover .breadcrumb-icon {
            transform: scale(1.1) rotate(5deg);
            text-shadow: 0 0 8px rgba(0, 123, 255, 0.4);
        }

        .breadcrumb-current .breadcrumb-icon {
            text-shadow: 0 0 10px rgba(0, 123, 255, 0.3);
        }

        .breadcrumb-text {
            transition: all 0.3s ease;
        }

        .breadcrumb-link:hover .breadcrumb-text {
            text-shadow: 0 1px 3px rgba(0, 123, 255, 0.2);
        }

        .breadcrumb-current .breadcrumb-text {
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        @keyframes ring {
            0% {
                transform: rotate(0);
            }

            1% {
                transform: rotate(30deg);
            }

            3% {
                transform: rotate(-28deg);
            }

            5% {
                transform: rotate(34deg);
            }

            7% {
                transform: rotate(-32deg);
            }

            9% {
                transform: rotate(30deg);
            }

            11% {
                transform: rotate(-28deg);
            }

            13% {
                transform: rotate(26deg);
            }

            15% {
                transform: rotate(-24deg);
            }

            17% {
                transform: rotate(22deg);
            }

            19% {
                transform: rotate(-20deg);
            }

            21% {
                transform: rotate(18deg);
            }

            23% {
                transform: rotate(-16deg);
            }

            25% {
                transform: rotate(14deg);
            }

            27% {
                transform: rotate(-12deg);
            }

            29% {
                transform: rotate(10deg);
            }

            31% {
                transform: rotate(-8deg);
            }

            33% {
                transform: rotate(6deg);
            }

            35% {
                transform: rotate(-4deg);
            }

            37% {
                transform: rotate(2deg);
            }

            39% {
                transform: rotate(-1deg);
            }

            41% {
                transform: rotate(1deg);
            }

            43% {
                transform: rotate(0);
            }

            100% {
                transform: rotate(0);
            }
        }
    </style>

    @if (isset($select2))
        <link type="text/css" rel="stylesheet" href="/select2/select2.min.css" />
    @endif

    @yield('css')
    @stack('styles')
</head>

<body>
    <div id="app">
        <!-- Header -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
            <div class="container-fluid">
                <?php 
                    $active_company = getActiveCompany(); 
                    $logoPath = ($active_company && !empty($active_company->logo)) ? $active_company->logo : '/images/logo.png';
                ?>
                <a class="navbar-brand" href="{{ route('home') }}">
                    <img src="{{ $logoPath }}" alt="Logo" height="40" onerror="this.onerror=null; this.src='/images/logo.png';">
                </a>
                
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            {{ Auth::user()->name ?? 'User' }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="py-4" style="margin-top: 40px;">
            <div class="container-fluid">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <!-- Scripts -->
    <script src="/assets/js/libs/jquery/jquery-3.5.1.min.js"></script>
    <script src="/assets/js/libs/bootstrap/bootstrap-4.4.1.min.js"></script>
    
    @if (isset($select2))
        <script src="/select2/select2.min.js"></script>
    @endif
    
    @yield('script')
    
    @if (isset($select2))
        <script>
            $(document).ready(function() {
                $('select').not('.hidden').each(function(i, e) {
                    if (!$(e).hasClass('no-select2')) {
                        var $select = $(e);
                        var isMultiple = $select.prop('multiple');
                        
                        var options = {
                            placeholder: $select.attr('placeholder') || $select.data('placeholder') || 'Select...'
                        };
                        
                        // Special configuration for multiple selects
                        if (isMultiple) {
                            options.allowClear = true;
                            options.closeOnSelect = false;
                        }
                        
                        $select.select2(options);
                    }
                });
            });
        </script>
    @endif
    
    @livewireScripts
    @stack('scripts')
</body>

</html>
