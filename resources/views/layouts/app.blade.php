<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests"> --}}

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.favicon')

    @yield('title')

    @livewireStyles

    <!-- Scripts -->
    <link rel="stylesheet" href="/assets/css/font-awesome/all.min.css">
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css" integrity="sha256-+N4/V/SbAFiW1MPBCXnfnP9QSN3+Keu+NlB+0ev/YKQ=" crossorigin="anonymous" /> --}}
    <link rel="stylesheet" href="/material-design/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="/assets/css/bootstrap/bootstrap4.4.1.min.css">

    {{-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous"> --}}

    {{-- <script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.9.0/dist/fullcalendar.min.js"></script> --}}
    @php
        $themeVars = \App\Services\System\ThemeService::resolvedVariables();
    @endphp
    @include('layouts.partials.global-styling')
    @include('layouts.partials.typography-styles')
    @include('layouts.partials.sidebar-styles')
    @include('layouts.partials.modal-styles')
    @include('layouts.partials.page-header-styles')
    @include('layouts.partials.table-styles')
    @include('layouts.partials.form-styles')
    @include('layouts.partials.button-styles')
    @include('layouts.partials.workflow-page-styles')
    @include('layouts.lab.partials.lab-surface-theme-styles')
    @include('layouts.partials.tag-select-styles')
    <style type="text/css">
        /* legacy layout rules — tokens in global-styling partial */

        /* html,body{
      background-color: #2a2a2a;
  } */

        select,
        .select2.select2-container.select2-container--default {
            width: 100% !important;
        }

        .tag-select-container {
            position: relative;
            cursor: text;
        }

        .tag-select-input {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.35rem;
            min-height: var(--control-h, 34px);
            padding: 0.35rem 0.45rem;
            background: #fff;
            border: 1px solid var(--color-border, #e2e8f0);
            border-radius: var(--radius-sm, 6px);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
            overflow: hidden;
        }

        .tag-select-input:hover {
            border-color: var(--color-primary);
        }

        .tag-select-input:focus-within {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px var(--color-primary-focus);
            outline: none;
        }

        .tag-select-container.is-invalid .tag-select-input {
            border-color: #dc3545;
        }

        .tag-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.15rem 0.35rem 0.15rem 0.45rem;
            background-color: var(--color-primary-soft);
            color: var(--color-primary);
            border: 1px solid var(--color-primary-border-soft);
            border-radius: 999px;
            font-size: var(--text-caption, 0.75rem);
            font-weight: 600;
            line-height: 1.25;
            max-width: 100%;
            min-width: 0;
        }

        .tag-badge-label {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            min-width: 0;
        }

        .tag-badge i {
            cursor: pointer;
            font-size: 0.95rem;
            opacity: 0.75;
            transition: opacity 0.15s;
            flex-shrink: 0;
            color: inherit;
        }

        .tag-badge i:hover {
            opacity: 1;
        }

        .tag-input {
            flex: 1;
            min-width: 120px;
            border: none;
            outline: none;
            padding: 0.15rem;
            font-size: var(--text-sm, 0.8125rem);
            background: transparent;
            color: var(--color-text);
        }

        .tag-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid var(--color-border, #e2e8f0);
            border-radius: var(--radius-sm, 6px);
            max-height: 240px;
            overflow-y: auto;
            z-index: 2000;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.16);
            margin-top: 0;
        }

        .tag-dropdown-item {
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            transition: background-color 0.15s;
            border-bottom: 1px solid #f1f5f9;
            font-size: var(--text-sm, 0.8125rem);
        }

        .tag-dropdown-item:hover {
            background-color: #f8fafc;
        }

        .tag-dropdown-item:last-child {
            border-bottom: none;
        }

        .customer-tab-filters {
            align-items: flex-end;
            row-gap: 12px;
        }

        .customer-tab-filters .form-control,
        .customer-tab-filters .form-select {
            min-height: 44px;
            height: 44px;
            font-size: 1rem;
            line-height: 1.25;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        .customer-tab-filters select.form-control,
        .customer-tab-filters select.form-select {
            padding-right: 2rem;
            background-position: right 0.75rem center;
        }

        .customer-tab-filters .btn {
            min-height: 44px;
            line-height: 1.2;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        .equipment-table tbody tr,
        .equipment-table tbody td {
            background-color: #fff;
        }

        .equipment-table.table-striped tbody tr:nth-of-type(odd),
        .equipment-table.table-hover tbody tr:hover {
            background-color: #fff;
        }

        .equipment-actions-cell {
            white-space: nowrap;
        }

        .equipment-actions-group {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            flex-wrap: wrap;
        }

        .equipment-action-btn {
            width: 38px;
            height: 38px;
            padding: 0;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }

        .equipment-action-btn:hover {
            transform: translateY(-1px);
        }

        .equipment-action-btn i {
            font-size: 0.95rem;
            line-height: 1;
        }

        .equipment-add-btn,
        .rm-act-btn {
            border-radius: 12px;
        }

        .equipment-add-btn {
            padding: 0.38rem 0.8rem;
            font-weight: 600;
        }

        .rm-act-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 4px;
        }

        .rm-act-btn.equipment-action-btn {
            width: 32px;
            height: 32px;
            border: 1px solid transparent;
            box-shadow: none;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }

        .rm-act-btn.equipment-action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.08);
        }
        .rm-act-btn {
            border-radius: 7px;
            padding: 4px 8px;
            margin-right: 3px;
            font-size: 12px;
        }

        .rm-act-btn:last-child {
            margin-right: 0;
        }

        /* EDIT Button - Blue */
        .rm-act-btn--edit {
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            background: #eff6ff;
        }

        .rm-act-btn--edit:hover {
            background: #dbeafe;
            border-color: #93c5fd;
        }

        /* VIEW Button - Green */
        .rm-act-btn--view {
            border: 1px solid #bbf7d0;
            color: #15803d;
            background: #f0fdf4;
        }

        .rm-act-btn--view:hover {
            background: #dcfce7;
            border-color: #86efac;
        }

        /* DELETE Button - Red */
        .rm-act-btn--delete {
            border: 1px solid #fecdd3;
            color: #e11d48;
            background: #fff5f7;
        }

        .rm-act-btn--delete:hover {
            background: #ffe4e6;
            border-color: #fda4af;
        }

        .rm-act-btn--expand {
            border: 1px solid transparent;
            color: #0d6efd;
            background: #f8fafc;
        }

        .rm-act-btn--expand:hover {
            background: #e2e8f0;
            border-color: #cbd5e1;
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

        @-webkit-keyframes ring {
            0% {
                -webkit-transform: rotateZ(0);
            }

            1% {
                -webkit-transform: rotateZ(30deg);
            }

            3% {
                -webkit-transform: rotateZ(-28deg);
            }

            5% {
                -webkit-transform: rotateZ(34deg);
            }

            7% {
                -webkit-transform: rotateZ(-32deg);
            }

            9% {
                -webkit-transform: rotateZ(30deg);
            }

            11% {
                -webkit-transform: rotateZ(-28deg);
            }

            13% {
                -webkit-transform: rotateZ(26deg);
            }

            15% {
                -webkit-transform: rotateZ(-24deg);
            }

            17% {
                -webkit-transform: rotateZ(22deg);
            }

            19% {
                -webkit-transform: rotateZ(-20deg);
            }

            21% {
                -webkit-transform: rotateZ(18deg);
            }

            23% {
                -webkit-transform: rotateZ(-16deg);
            }

            25% {
                -webkit-transform: rotateZ(14deg);
            }

            27% {
                -webkit-transform: rotateZ(-12deg);
            }

            29% {
                -webkit-transform: rotateZ(10deg);
            }

            31% {
                -webkit-transform: rotateZ(-8deg);
            }

            33% {
                -webkit-transform: rotateZ(6deg);
            }

            35% {
                -webkit-transform: rotateZ(-4deg);
            }

            37% {
                -webkit-transform: rotateZ(2deg);
            }

            39% {
                -webkit-transform: rotateZ(-1deg);
            }

            41% {
                -webkit-transform: rotateZ(1deg);
            }

            43% {
                -webkit-transform: rotateZ(0);
            }

            100% {
                -webkit-transform: rotateZ(0);
            }
        }

        @-moz-keyframes ring {
            0% {
                -moz-transform: rotate(0);
            }

            1% {
                -moz-transform: rotate(30deg);
            }

            3% {
                -moz-transform: rotate(-28deg);
            }

            5% {
                -moz-transform: rotate(34deg);
            }

            7% {
                -moz-transform: rotate(-32deg);
            }

            9% {
                -moz-transform: rotate(30deg);
            }

            11% {
                -moz-transform: rotate(-28deg);
            }

            13% {
                -moz-transform: rotate(26deg);
            }

            15% {
                -moz-transform: rotate(-24deg);
            }

            17% {
                -moz-transform: rotate(22deg);
            }

            19% {
                -moz-transform: rotate(-20deg);
            }

            21% {
                -moz-transform: rotate(18deg);
            }

            23% {
                -moz-transform: rotate(-16deg);
            }

            25% {
                -moz-transform: rotate(14deg);
            }

            27% {
                -moz-transform: rotate(-12deg);
            }

            29% {
                -moz-transform: rotate(10deg);
            }

            31% {
                -moz-transform: rotate(-8deg);
            }

            33% {
                -moz-transform: rotate(6deg);
            }

            35% {
                -moz-transform: rotate(-4deg);
            }

            37% {
                -moz-transform: rotate(2deg);
            }

            39% {
                -moz-transform: rotate(-1deg);
            }

            41% {
                -moz-transform: rotate(1deg);
            }

            43% {
                -moz-transform: rotate(0);
            }

            100% {
                -moz-transform: rotate(0);
            }
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

        .btn-circle {
            width: 45px;
            height: 45px;
            line-height: 45px;
            text-align: center;
            padding: 0;
            border-radius: 50%;
        }

        .card-body .rotate {
            z-index: 8;
            float: right;
            height: 100%;
        }

        .no-overflow {
            overflow: hidden;
        }

        .dataTables_length {
            margin-left: 15px;
        }

        .dataTables_length label {
            white-space: nowrap !important;
        }

        table.dataTable thead th,
        table.dataTable thead td {
            padding: 10px 18px;
            border-bottom: 1px solid #868686 !important;
        }

        .btn-xs {
            font-size: 12px !important;
        }

        .card-body .rotate i {
            color: rgba(20, 20, 20, 0.15);
            position: absolute;
            left: 0;
            left: auto;
            right: -10px;
            bottom: 0;
            display: block;
            -webkit-transform: rotate(-44deg);
            -moz-transform: rotate(-44deg);
            -o-transform: rotate(-44deg);
            -ms-transform: rotate(-44deg);
            transform: rotate(-44deg);
        }

        .btn-circle i {
            position: relative;
            top: -1px;
        }

        .btn-circle-sm {
            width: 35px;
            height: 35px;
            line-height: 35px;
            font-size: 0.9rem;
        }

        .btn-circle-lg {
            width: 55px;
            height: 55px;
            line-height: 55px;
            font-size: 1.1rem;
        }

        .btn-circle-xl {
            width: 70px;
            height: 70px;
            line-height: 70px;
            font-size: 1.3rem;
        }

        :root {
            --app-header-height: 56px;
        }

        body {
            padding-top: var(--app-header-height);
        }

        body.has-password-expiry-banner {
            --app-header-height: 92px;
        }

        .password-expiry-banner {
            position: fixed;
            top: 56px;
            left: 0;
            right: 0;
            z-index: 1029;
            font-size: 13px;
        }

        .password-expiry-banner .container-fluid {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            min-height: 36px;
        }

        .password-expiry-banner__dismiss {
            flex-shrink: 0;
            color: inherit;
            opacity: 0.7;
            font-size: 1.25rem;
            line-height: 1;
            border: 0;
            background: transparent;
        }

        .password-expiry-banner__dismiss:hover {
            opacity: 1;
        }

        .sticky-offset {
            top: var(--app-header-height);
        }

        #body-row {
            margin-left: 0;
            margin-right: 0;
        }

        /* Adjust main content for fixed sidebar */
        #main-container-body {
            margin-left: 265px;
            /* Sidebar width when expanded */
            width: calc(100% - 265px);
            /* Calculate remaining width */
            transition: margin-left 0.3s ease, width 0.3s ease;
            overflow-x: hidden;
        }

        /*
         * Mobile + tablet (< Bootstrap lg / 992px): content is full-bleed.
         * Sidebar becomes an off-canvas overlay (closed by default).
         */
        @media (max-width: 991.98px) {
            #main-container-body {
                margin-left: 0 !important;
                width: 100% !important;
            }
        }

        #sidebar-container {
            position: fixed;
            top: var(--app-header-height);
            min-width: 265px;
            max-width: 265px;
            left: 0;
            height: calc(100vh - var(--app-header-height));
            max-height: calc(100vh - var(--app-header-height));
            background-color: #333;
            padding: 8px 4px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* Internet Explorer 10+ */
            z-index: 1000;
        }

        #sidebar-container::-webkit-scrollbar {
            width: 8px;
        }

        #sidebar-container::-webkit-scrollbar-track {
            background: transparent;
            border-radius: 4px;
        }

        #sidebar-container::-webkit-scrollbar-thumb {
            background: transparent;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        #sidebar-container:hover::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.35) 0%, rgba(200, 200, 200, 0.25) 100%);
        }

        #sidebar-container:hover::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.5) 0%, rgba(200, 200, 200, 0.35) 100%);
        }

        /* Sidebar sizes when expanded and expanded */
        .sidebar-expanded {
            width: 230px;
        }

        .sidebar-collapsed {
            width: 60px !important;
        }

        /* Sidebar menu interaction styles: layouts/partials/sidebar-styles.blade.php */

        .sidebar-submenu {
            font-size: var(--text-sidebar);
            margin: 4px 0;
        }

        #sidebar-container .list-group .sidebar-submenu a .badge,
        #sidebar-container .list-group .sidebar-submenu a .floating-badge {
            margin-left: auto;
            margin-right: 15px;
            flex-shrink: 0;
            font-size: 0.6875rem;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: var(--font-medium);
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 20px;
            height: 18px;
        }

        .form-control {
            font-size: var(--text-sm) !important;
        }

        /* Separators */
        .sidebar-separator-title {
            background-color: rgba(255, 255, 255, 0.06);
            height: 35px;
            margin: 4px 0;
            border-radius: 6px;
        }

        .sidebar-separator {
            background-color: rgba(255, 255, 255, 0.06);
            height: 25px;
            margin: 2px 0;
            border-radius: 4px;
        }

        .logo-separator {
            background-color: rgba(255, 255, 255, 0.06);
            height: 60px;
            margin: 4px 0;
            border-radius: 8px;
        }

        /* Closed submenu icon */
        #sidebar-container .list-group .list-group-item[aria-expanded="false"] .submenu-icon::after {
            content: "\2193";
            /* font-family: 'Font Awesome 5 Free'; */
            display: inline;
            text-align: right;
            padding-left: 10px;
        }

        /* Opened submenu icon */
        #sidebar-container .list-group .list-group-item[aria-expanded="true"] .submenu-icon::after {
            content: "\2191";
            /* font-family: 'Font Awesome 5 Free'; */
            display: inline;
            text-align: right;
            padding-left: 10px;
        }

        /*\
    * Restore Bootstrap 3 "hidden" utility classes.
    \*/

        /* Breakpoint XS */
        @media (max-width: 575px) {

            .hidden-xs-down,
            .hidden-sm-down,
            .hidden-md-down,
            .hidden-lg-down,
            .hidden-xl-down,
            .hidden-xs-up,
            .hidden-unless-sm,
            .hidden-unless-md,
            .hidden-unless-lg,
            .hidden-unless-xl {
                display: none !important;
            }
        }

        /* Breakpoint SM */
        @media (min-width: 576px) and (max-width: 767px) {

            .hidden-sm-down,
            .hidden-md-down,
            .hidden-lg-down,
            .hidden-xl-down,
            .hidden-xs-up,
            .hidden-sm-up,
            .hidden-unless-xs,
            .hidden-unless-md,
            .hidden-unless-lg,
            .hidden-unless-xl {
                display: none !important;
            }
        }

        /* Breakpoint MD */
        @media (min-width: 768px) and (max-width: 991px) {

            .hidden-md-down,
            .hidden-lg-down,
            .hidden-xl-down,
            .hidden-xs-up,
            .hidden-sm-up,
            .hidden-md-up,
            .hidden-unless-xs,
            .hidden-unless-sm,
            .hidden-unless-lg,
            .hidden-unless-xl {
                display: none !important;
            }
        }

        /* Breakpoint LG */
        @media (min-width: 992px) and (max-width: 1199px) {

            .hidden-lg-down,
            .hidden-xl-down,
            .hidden-xs-up,
            .hidden-sm-up,
            .hidden-md-up,
            .hidden-lg-up,
            .hidden-unless-xs,
            .hidden-unless-sm,
            .hidden-unless-md,
            .hidden-unless-xl {
                display: none !important;
            }
        }

        /* Breakpoint XL */
        @media (min-width: 1200px) {

            .hidden-xl-down,
            .hidden-xs-up,
            .hidden-sm-up,
            .hidden-md-up,
            .hidden-lg-up,
            .hidden-xl-up,
            .hidden-unless-xs,
            .hidden-unless-sm,
            .hidden-unless-md,
            .hidden-unless-lg {
                display: none !important;
            }
        }

        .module-name {
            text-decoration: none !important;
            color: #545454 !important;
            font-size: 24px !important;
            font-weight: 400 !important;
        }

        .table-image {
            height: 50px;
            padding: 4px;
        }

        .my-small-text {
            font-size: 13px !important;
        }

        .no-border-tab {
            border: none !important;
            background-color: none !important;
        }

        .no-border-tab.active {
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
            border-bottom-color: #fff !important;
            background-image: linear-gradient(rgba(0, 0, 0, 0.06), rgba(0, 0, 0, 0.0)) !important;
        }

        .small-badge {
            padding: 2px 6px;
            font-size: 85% !important;
            border-radius: 5% 50%;
            font-weight: 600;
        }

        .my-tab {
            float: left;
            cursor: pointer;
            font-size: 14px;
            margin: 2px 3px;
            padding: 5px 8px;
            color: #363636;
            /* background: radial-gradient(closest-side, #eeeeee, #f0f0f0, #fff); */
        }

        .my-tab-headers {
            padding: 5px 2px;
            border-bottom: 1px solid #e7e7e7;
        }

        .my-tab.selected {
            font-size: 13px;
            font-weight: 600;
            color: #4b4b4b;
            border: 1px solid rgb(197, 205, 207);
            padding: 4px 15px 0px;
            border-radius: 15px;
            box-shadow: 0px 0px 35px rgb(211, 219, 221) inset;
            /* background: radial-gradient(closest-side, #c5dee7, #cbdbe0, #fff); */
        }

        table td {
            vertical-align: middle !important;
        }

        .bg-orange {
            color: #fff;
            background-color: rgb(255, 60, 0);
        }

        .bg-red {
            color: #fff;
            background-color: rgb(214, 3, 3);
        }

        .bg-green {
            color: #fff;
            background-color: rgb(36, 155, 0);
        }

        .has-floating-badge {
            position: relative;
        }

        .floating-badge {
            font-size: 11.5px;
            position: absolute;
            top: 0px;
            left: 100%;
            z-index: 10;
            border-radius: 10px;
            padding: 0px 4px;
            background-color: rgb(196, 95, 0);
            color: #fff;
            font-weight: 600;
            box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.05);
        }

        .table-seperated {
            border-collapse: separate;
        }

        .table-seperated td,
        th {
            white-space: nowrap !important;
            margin: 0px !important;
        }

        .fixed-column {
            position: absolute;
            width: 5em;
            left: 0;
            top: auto;
            /*only relevant for first row*/
            margin-top: -3px;
            /*compensate for top border*/
        }

        .floating-sidebar {
            position: fixed !important;
            width: min(280px, 85vw) !important;
            min-width: min(280px, 85vw) !important;
            max-width: min(280px, 85vw) !important;
            left: 0;
            top: var(--app-header-height, 56px);
            height: calc(100dvh - var(--app-header-height, 56px));
            max-height: calc(100dvh - var(--app-header-height, 56px));
            z-index: 1040 !important;
            overflow-y: auto;
            overflow-x: hidden;
            box-shadow: 8px 0 28px rgba(15, 23, 42, 0.28);
            transform: translateX(0);
            transition: transform 0.22s ease;
        }

        #sidebar-backdrop {
            position: fixed;
            inset: 0;
            top: var(--app-header-height, 56px);
            z-index: 1035;
            background: rgba(15, 23, 42, 0.45);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.2s ease, visibility 0.2s ease;
            -webkit-tap-highlight-color: transparent;
        }

        #sidebar-backdrop.is-visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        body.sidebar-overlay-open {
            overflow: hidden;
        }

        @media (prefers-reduced-motion: reduce) {
            .floating-sidebar,
            #sidebar-backdrop {
                transition: none;
            }
        }

        .hidden {
            display: none !important;
        }

        /* Global modal baseline: keep content below fixed header */
        .modal {
            top: 56px !important;
            height: calc(100% - 56px) !important;
        }

        .modal-backdrop {
            top: 56px !important;
            height: calc(100vh - 56px) !important;
        }

        .modal.fade.show,
        .modal.show {
            display: block;
            z-index: 2000 !important;
        }

        .modal-backdrop.show {
            z-index: 1990 !important;
        }

        .modal-dialog {
            position: relative;
            z-index: 2001;
            margin-top: 1rem;
        }

        /* Keep dropdown/select overlays visible inside modals */
        .modal .dropdown-menu,
        .modal .select2-dropdown,
        .modal .select2-container--open,
        .modal .tag-dropdown,
        .modal .dropdown-list {
            z-index: 2100 !important;
        }

        .copyright-lims,
        #sidebar-container {
            background-color: var(--sys-sidebar-bg) !important;
        }

        .sidebar-module-div {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: inset 0 1px 3px rgba(255, 255, 255, 0.1),
                inset 0 -1px 3px rgba(0, 0, 0, 0.1),
                0 4px 15px rgba(0, 0, 0, 0.2);
            margin: 4px 0;
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            color: var(--sys-sidebar-text) !important;
        }

        .sidebar-module-div::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }

        .sidebar-module-div:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: inset 0 1px 3px rgba(255, 255, 255, 0.15),
                inset 0 -1px 3px rgba(0, 0, 0, 0.15),
                0 8px 25px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .sidebar-module-div:hover::before {
            left: 100%;
        }

        .sidebar-module-div i {
            font-size: 3.5rem !important;
            color: #ffffff !important;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.45);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: block;
            margin-bottom: 8px;
        }

        .sidebar-module-div:hover i {
            transform: scale(1.1) rotate(5deg);
            text-shadow: 0 0 30px rgba(255, 255, 255, 0.55);
        }

        .sidebar-module-div span {
            color: var(--sys-sidebar-text) !important;
            font-size: var(--text-sidebar) !important;
            font-weight: var(--font-semibold) !important;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .sidebar-module-div:hover span {
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
            transform: translateY(-1px);
        }

        .copyright-lims {
            background-color: var(--sys-sidebar-bg) !important;
            color: var(--sys-sidebar-text-muted) !important;
            margin: 4px 0;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.03);
        }

        /* Modern list group styling */
        #sidebar-container .list-group {
            border: none;
            background: transparent;
            padding: 0;
            margin-bottom: 20px;
        }

        /* Smooth scrolling for sidebar */
        #sidebar-container {
            scroll-behavior: smooth;
        }

        /* Additional scrollbar styling for better visibility */
        #sidebar-container:hover {
            scrollbar-width: thin;
            /* Firefox */
        }

        /* Custom scrollbar for better cross-browser support */

        #sidebar-container::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 8px;
            height: 100%;
            background: transparent;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        #sidebar-container:hover::after {
            background: linear-gradient(180deg,
                    rgba(255, 255, 255, 0.08) 0%,
                    rgba(200, 200, 200, 0.06) 50%,
                    rgba(255, 255, 255, 0.08) 100%);
        }

        #sidebar-container .list-group-item {
            border: none;
            background: transparent;
        }

        /* Icon styling improvements */
        #sidebar-container .mdi {
            font-size: 1.1rem;
            opacity: 0.9;
            transition: all 0.3s ease;
        }

        #sidebar-container .list-group a:hover .mdi {
            transform: scale(1.1) rotate(5deg);
            opacity: 1;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }

        #sidebar-container .list-group a.active .mdi {
            transform: scale(1.15);
            opacity: 1;
            text-shadow: 0 0 15px rgba(255, 255, 255, 0.55);
        }

        /* Text effects */
        #sidebar-container .list-group a:hover span {
            text-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
        }

        #sidebar-container .list-group a.active span {
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.45);
            font-weight: 600;
        }

        /* Smooth transitions for all sidebar elements */
        #sidebar-container * {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Modern Navigation Styles */
        #main-app-header {
            background: white !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: none;
        }

        #main-app-header .navbar-brand img {
            transition: all 0.3s ease;
        }

        #main-app-header .navbar-brand img:hover {
            transform: scale(1.05);
        }

        /* Enhanced Search Form */
        .search-form-container {
            position: relative;
            max-width: 400px;
            margin: 0 auto;
            flex: 1 1 auto;
            min-width: 0;
        }

        @media (max-width: 991.98px) {
            .search-form-container {
                max-width: 220px;
            }
        }

        @media (max-width: 575.98px) {
            .search-form-container {
                display: none;
            }
        }

        .search-input-group {
            position: relative;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1), 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            position: relative;
        }

        .search-input-group::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover), var(--color-primary-tint));
            background-size: 300% 300%;
            border-radius: 27px;
            z-index: -1;
            opacity: 0;
            animation: gradientShift 4s ease infinite;
            transition: opacity 0.3s ease;
        }

        .search-input-group:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            border-color: var(--color-primary);
        }

        .search-input-group:hover::before {
            opacity: 0.6;
        }

        .search-input-group:focus-within {
            box-shadow: 0 4px 20px var(--color-primary-shadow);
            border-color: var(--color-primary);
        }

        .search-input-group:focus-within::before {
            opacity: 0.8;
        }

        .search-input {
            border: none !important;
            background: transparent !important;
            padding: 12px 20px !important;
            font-size: 14px !important;
            color: #333 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            width: 275px;
            outline: none !important;
        }

        .search-input:focus {
            outline: none !important;
            box-shadow: none !important;
        }

        .search-input::placeholder {
            color: #999 !important;
            font-weight: 400;
        }


        .search-btn {
            background: transparent !important;
            border: none !important;
            border-radius: 0 25px 25px 0 !important;
            padding: 12px 20px !important;
            color: #6c757d !important;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .search-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, var(--color-primary-soft-10), transparent);
            transition: left 0.5s ease;
        }

        .search-btn:hover {
            background: var(--color-primary-soft-10) !important;
            color: var(--color-primary) !important;
            transform: scale(1.05);
        }

        .search-btn:hover::before {
            left: 100%;
        }

        .search-btn:active {
            transform: scale(0.98);
            background: var(--color-primary-shadow) !important;
        }

        .search-btn:focus {
            outline: none !important;
            box-shadow: none !important;
        }

        /* Subtle divider between input and button */
        .search-btn::after {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            bottom: 20%;
            width: 1px;
            background: #e9ecef;
            transition: all 0.3s ease;
        }

        .search-input-group:focus-within .search-btn::after {
            background: var(--color-primary);
        }

        /* Toggle Button Enhancement */
        #toggle-main-sidebar {
            background: transparent !important;
            border: 2px solid #e9ecef !important;
            border-radius: 8px !important;
            color: var(--color-primary) !important;
            transition: all 0.3s ease;
            min-width: 44px;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        #toggle-main-sidebar:hover {
            background: #f8f9fa !important;
            border-color: var(--color-primary) !important;
            transform: scale(1.05);
            box-shadow: 0 2px 8px var(--color-primary-shadow);
        }

        /* Navbar Toggler Enhancement */
        .navbar-toggler {
            border: 2px solid #e9ecef !important;
            border-radius: 8px !important;
            background: transparent !important;
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.2rem var(--color-primary-focus) !important;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%280, 167, 223, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
        }

        /* Right Side Navigation Enhancement */
        #main-app-header .navbar-nav .nav-link {
            color: #333 !important;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 16px !important;
            border-radius: 8px;
            margin: 0 2px;
        }

        #main-app-header .navbar-nav .nav-link:hover {
            color: var(--color-primary) !important;
            background: #f8f9fa;
            transform: translateY(-2px);
        }

        #main-app-header .navbar-nav .dropdown-menu {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05), 0 4px 20px rgba(0, 0, 0, 0.15);
            margin-top: 8px;
            max-width: 250px;
            right: 0;
            left: auto;
        }

        #main-app-header .navbar-nav .dropdown-item {
            color: #333 !important;
            padding: 10px 20px;
            transition: all 0.3s ease;
            border-radius: 6px;
            margin: 2px 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #main-app-header .navbar-nav .dropdown-item:hover {
            background: var(--color-primary);
            color: white !important;
            transform: translateX(5px);
        }

        /* Floating Badge Enhancement */
        .has-floating-badge {
            position: relative;
        }

        .floating-badge {
            font-size: 11px;
            position: absolute;
            top: -8px;
            right: -8px;
            z-index: 10;
            border-radius: 12px;
            padding: 2px 6px;
            background: #dc3545;
            color: white;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        /* Module Name Styling */
        .module-name {
            color: #333 !important;
            font-size: 20px !important;
            font-weight: 600 !important;
        }

        /* User Avatar Styling */
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 8px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .user-avatar:hover {
            border-color: var(--color-primary);
            transform: scale(1.1);
        }

        .navbar-nav .nav-link .user-avatar {
            display: inline-block;
            vertical-align: middle;
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
            border-color: var(--color-primary);
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
            background: linear-gradient(90deg, transparent, var(--color-primary-soft), transparent);
            transition: left 0.6s ease;
        }

        .breadcrumb-link:hover {
            color: var(--color-primary);
            background: linear-gradient(135deg, var(--color-primary-soft) 0%, var(--color-primary-soft-light) 100%);
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 2px 10px var(--color-primary-highlight);
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
            background: linear-gradient(135deg, var(--color-primary-soft) 0%, var(--color-primary-soft-medium) 100%);
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
            text-shadow: 0 0 8px var(--color-primary-glow);
        }

        .breadcrumb-current .breadcrumb-icon {
            text-shadow: 0 0 10px var(--color-primary-border-soft);
        }

        .breadcrumb-text {
            transition: all 0.3s ease;
        }

        .breadcrumb-link:hover .breadcrumb-text {
            text-shadow: 0 1px 3px var(--color-primary-shadow);
        }

        .breadcrumb-current .breadcrumb-text {
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Responsive breadcrumb adjustments */
        @media (max-width: 768px) {
            .breadcrumb-container {
                margin: 10px;
                padding: 10px 15px;
            }

            .breadcrumb-modern {
                font-size: 13px;
            }

            .breadcrumb-link,
            .breadcrumb-current {
                padding: 6px 12px;
            }

            .breadcrumb-item-modern:not(:last-child)::after {
                margin: 0 8px;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .breadcrumb-container {
                background: linear-gradient(135deg, rgba(248, 249, 250, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%);
                border-color: rgba(0, 0, 0, 0.1);
            }

            .breadcrumb-link {
                color: #6c757d;
            }

            .breadcrumb-current {
                color: #495057;
                background: linear-gradient(135deg, var(--color-primary-soft) 0%, var(--color-primary-soft-medium) 100%);
            }
        }

    </style>

    @if (isset($dataTable))
    {{-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.23/datatables.min.css"/> --}}
    <link rel="stylesheet" href="/assets/css/datatable/datatable.min.css">

    {{-- <link href="https://cdn.datatables.net/buttons/1.2.4/css/buttons.dataTables.min.css" rel="stylesheet"> --}}
    <link rel="stylesheet" href="/assets/css/datatable/button.datatable1.2.4.min.css">
    @endif
    @if (isset($select2))
    <link type="text/css" rel="stylesheet" href="/select2/select2.min.css" />
    <link type="text/css" rel="stylesheet" href="{{ asset('css/method-sequences.css') }}" />
    @endif
    @if (isset($datePicker))
    {{-- <link type="text/css" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.css" /> --}}
    <link rel="stylesheet" href="/assets/css/bootstrap-datepicker/bootstrap-datepicker.min.css">
    @endif
    <?php

    use Illuminate\Support\Facades\Auth;

    $thePath = request()->path();
    $PageAttachments = getPageAttachments($thePath);
    $current = Auth::user()->id;

    $__showPasswordExpiryBanner = false;
    if (Auth::check() && ! View::hasSection('hide_password_expiry_banner')) {
        $__pwDays = Auth::user()->passwordDaysRemaining();
        if ($__pwDays <= 30) {
            $__showPasswordExpiryBanner = true;
            $__pwClass = $__pwDays <= 10 ? 'danger' : ($__pwDays <= 20 ? 'warning' : 'info');
            $__pwIcon = $__pwDays <= 10 ? 'fa-lock' : 'fa-key';
            $__pwLabel = $__pwDays <= 10 ? 'URGENT' : ($__pwDays <= 20 ? 'Warning' : 'Notice');
        }
    }
    ?>
</head>

<body @if($__showPasswordExpiryBanner) class="has-password-expiry-banner" @endif>
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm fixed-top" id="main-app-header">
        <div class="container-fluid">
            <button class="btn btn-transparent btn-lg" id="toggle-main-sidebar"
                type="button"
                style="margin-left: -10px; margin-right: 5px"
                aria-label="Toggle sidebar navigation"
                aria-controls="sidebar-container"
                aria-expanded="false">
                <i class="mdi mdi-menu" aria-hidden="true"></i>
            </button>
            <a class="navbar-brand" href="{{ url('/home') }}">
                <?php 
                    $active_company = getActiveCompany(); 
                    $logoPath = ($active_company && !empty($active_company->logo)) ? $active_company->logo : '/images/logo.png';
                ?>
                <img src="{{ $logoPath }}" style="height: 40px" onerror="this.onerror=null; this.src='/images/logo.png';" />
            </a>
            <div class="search-form-container">
                <form method="post" action="{{ route('search-sample-code') }}" class="d-flex">
                    @csrf
                    <div class="search-input-group">
                        <input type="text" name="sample" class="search-input" placeholder="Search by Sample Code">
                        <button class="search-btn" type="submit">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav mr-auto">

                </ul>
                <ul class="nav navbar-nav navbar-center">
                    @yield('module-name')
                </ul>
                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ml-auto">
                    <!-- Authentication Links -->
                    @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                    </li>
                    @if (Route::has('register'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                    </li>
                    @endif
                    @else
                    @yield('alerts')
                    @if(config('localization.enable_switcher'))
                    <li class="nav-item dropdown mr-2">
                        <a id="languageDropdown" class="nav-link dropdown-toggle d-flex align-items-center text-secondary font-weight-bold" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="cursor: pointer; font-size: 0.95rem;">
                            <i class="mdi mdi-translate mr-1" style="font-size: 1.2rem;"></i>
                            <span class="text-uppercase">{{ app()->getLocale() }}</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right border-0 shadow-lg rounded-lg mt-2 p-2" aria-labelledby="languageDropdown" style="min-width: 180px;">
                            @foreach(\App\Models\System\Language::where('is_active', 1)->get() as $lang)
                                @php
                                    $flags = ['en' => '🇺🇸', 'sw' => '🇹🇿', 'fr' => '🇫🇷', 'es' => '🇪🇸', 'de' => '🇩🇪', 'pt' => '🇵🇹', 'ar' => '🇦🇪', 'zh' => '🇨🇳', 'ja' => '🇯🇵'];
                                    $flag = $flags[strtolower($lang->code)] ?? '🌍';
                                    $isActive = app()->getLocale() === strtolower($lang->code);
                                @endphp
                                <a class="dropdown-item d-flex align-items-center rounded px-3 py-2 mb-1 {{ $isActive ? 'bg-primary text-white font-weight-bold shadow-sm' : 'text-dark' }}" 
                                   href="{{ route('set-locale', strtolower($lang->code)) }}"
                                   style="transition: all 0.2s;">
                                    <span class="mr-3" style="font-size: 1.2rem;">{{ $flag }}</span>
                                    <span style="font-size: 0.95rem;">{{ $lang->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </li>
                    @endif
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=7F9CF5&background=EBF4FF"
                                alt="{{ Auth::user()->name }}" class="user-avatar">
                            {{ Auth::user()->name }} <span class="caret"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton"
                            style="width: 230px">
                            @if (Auth::user()->is_client == 0 && Auth::user()->supplier_id == 0)
                            <a class="dropdown-item" href="{{ route('user_profile') }}"><i
                                    class="mdi mdi-account-details text-primary"></i> &nbsp;&nbsp;My Profile</a>
                            @endif
                            @if (isset(Auth::user()->company_id) && Auth::user()->company_id == 0)
                            <a class="dropdown-item" href="#" data-target="#select-default-company"
                                data-toggle="modal"><i class="mdi mdi-domain text-info"></i> &nbsp;&nbsp;Select
                                Default Company</a>
                            @endif
                            <a class="dropdown-item" href="http://127.0.0.1:8000/logout"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="text-danger mdi mdi-power"></i> &nbsp;&nbsp;Sign-Out
                            </a>
                            <form id="logout-form" action="{{ route('mylogout') }}" method="POST"
                                style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                    <li class="nav-item">
                        <span data-target="#attachments-on-this-page-modal" data-toggle="modal" class="nav-link"
                            href="#page-attachments"
                            style="cursor:pointer; font-size: 22px; margin-top: -4px !important">
                            <b class="has-floating-badge">
                                <i class="mdi mdi-paperclip fa-1x"></i>
                                @if ($PageAttachments->count() > 0)
                                <small class="floating-badge">{{ $PageAttachments->count() }}</small>
                                @endif
                            </b>
                        </span>
                    </li>
                    @if (Auth::user()->is_client == 0)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('full-calendar') }}"
                            style="cursor:pointer; font-size: 22px; margin-top: -4px !important">
                            <i class="mdi mdi-calendar text-primary"></i>
                        </a>
                    </li>
                    {{-- <li class="nav-item">
							<span data-target="#chat-system" data-toggle="modal" class="nav-link" href="#chat-system" style="cursor:pointer; font-size: 22px; margin-top: -4px !important">
								<b class="has-floating-badge">
									<i class="mdi mdi-chat fa-1x text-success"></i>
										<?php
                                        $chat_count = getUserChats();
                                        ?>
										<small class="floating-badge">{{$chat_count->count()}}</small>

                    </b>
                    </span>
                    </li> --}}
                    @endif

                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    {{-- Password expiry countdown banner (fixed below navbar; hidden via @section('hide_password_expiry_banner') on full-screen pages) --}}
    @if($__showPasswordExpiryBanner)
        <div id="password-expiry-banner"
             class="password-expiry-banner alert alert-{{ $__pwClass }} mb-0 py-2 rounded-0 border-0"
             role="alert">
            <div class="container-fluid">
                <div class="password-expiry-banner__message">
                    <i class="fas {{ $__pwIcon }} mr-1"></i>
                    <strong>{{ $__pwLabel }}:</strong>
                    Your password expires in <strong>{{ $__pwDays }} day{{ $__pwDays === 1 ? '' : 's' }}</strong>.
                    <a href="{{ route('password.force-change') }}" class="alert-link ml-1">Change it now &rarr;</a>
                </div>
                <button type="button" class="password-expiry-banner__dismiss" aria-label="Dismiss">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif

    <div id="sidebar-backdrop" aria-hidden="true"></div>

    @yield('content')
    <div class="modal fade" id="chat-system" role="dialog">
        <div class="modal-dialog modal-xl" style="height: 100vh;">
            <div class="modal-content">
                <div class="row no-gutter">
                    <div class="col-sm-4 col-xl-4 col-md-4 pr-0">
                        <div class="card p-0" style="border: 0px;">
                            <div class="card-header bg-dark">
                                <h4 class="card-title">
                                    <i class="mdi mdi-message-bulleted"
                                        style="font-size: 15px;font-weight:600;color:turquoise"> Imara System</i>
                                    @if (Auth::user()->photo == '')
                                    <img src="/images/l.jpeg" class="float-right"
                                        style="border-radius: 50%; height:50px;width:50px" alt="">
                                    @else
                                    <img src="{{ Auth::user()->photo }}" class="float-right"
                                        style="border-radius: 50%; height:50px;width:50px" alt="">
                                    @endif
                                </h4>
                            </div>
                            <?php
                            $users = getCompanyUsers();
                            ?>
                            <div class="card-body bg-default ">
                                <div class="table-responsive p-0">
                                    <table class="table table-condensed my-small-text table-hover table-sm livewire-table">
                                        <tbody>
                                            @foreach ($users as $user)
                                            <tr>
                                                <td class="user-chat" id="{{ $user }}">
                                                    @if ($user->photo == '')
                                                    <img src="/images/l.jpeg"
                                                        style="border-radius: 50%;height:40px;width:40px"
                                                        alt="">
                                                    @else
                                                    <img src="{{ $user->photo }}"
                                                        style="border-radius: 50%;height:40px;width:40px"
                                                        alt="">
                                                    @endif
                                                    {{ $user->name }}
                                                    <span
                                                        class="text-small float-right mr-3 has-floating-badge">{!! $user->is_online == 1 ? '<i class="mdi mdi-circle-medium text-success"></i>' : '' !!}
                                                        @if ($user->chats > 0)
                                                        <small id="user-chat-count-{{ $user->id }}"
                                                            class="floating-badge"
                                                            style="background-color: turquoise;">{{ $user->chats }}</small>
                                                        @endif
                                                    </span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-8 col-xl-8 col-md-8 pl-0">
                        <div class="card" style="height: 100vh;">
                            <div class="card-header" style="background-color: white; height:74px">
                                <h4 class="card-title" id="card-title"></h4>
                            </div>
                            <div class="card-body bg-light" id="messages">

                            </div>
                            <div class="card-footer p-0" style="background-color: white;">
                                <form id="message-form">
                                    <div class="row no-gutter">
                                        <div class="col-sm-11 col-md-11 col-xl-11">
                                            <input type="hidden" name="to_user_id" id="to-user-id" value="">
                                            <div class="form-group">
                                                <textarea name="chat" id="chat" rows="5" class="form-control" placeholder="Type here..." required /></textarea>
                                            </div>
                                        </div>
                                        <div class="col-sm-1 col-md-1 col-xl-1">
                                            <center>

                                                <span class="btn btn-default text-primary mt-5 pr-3"
                                                    style="font-size: 25px;" id="message-save">
                                                    <i class="mdi mdi-send"></i>
                                                </span>
                                            </center>

                                        </div>
                                    </div>


                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="attachments-on-this-page-modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="mdi mdi-paperclip"></i> Attachments
                    </h4>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs card-header-tabs border-bottom" role="tablist">
                        <li class="nav-item">
                            <a data-toggle="tab" href="#modal-available-attachments"
                                class="nav-link active no-border-tab">
                                <i class="mdi mdi-paperclip"></i> Available Attachments
                                <small class="badge bg-white">{{ $PageAttachments->count() }}</small>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a data-toggle="tab" href="#modal-add-attachment-form" class="nav-link no-border-tab">
                                <i class="mdi mdi-plus"></i> Add Attachment
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content" id="analyte-tabs-content">
                        <div class="tab-pane fade pt-3 show active" id="modal-available-attachments" role="tabpanel"
                            aria-labelledby="one-tab">
                            <table
                                class="table table-sm mt-3 table-condensed table-banded table-hover table-borderless">
                                @foreach ($PageAttachments as $doc)
                                <tr data-href="{{ $doc->file }}" data-toggle="tooltip"
                                    title="{{ $doc->description }}" class="download-the-document"
                                    style="cursor: pointer">
                                    <td class="p-2 text-primary"><i class="mdi mdi-download"></i>
                                        <small class="text-muted">
                                            @if (intval($doc->size) > 1000)
                                            {{ number_format(intval($doc->size) / 1000, 2) }} KB
                                            @elseif(intval($doc->size) > 1000000)
                                            {{ number_format(intval($doc->size) / 1000000, 2) }} MB
                                            @else
                                            {{ $doc->size }} bytes
                                            @endif
                                        </small>
                                    </td>
                                    <td class="p-2">{{ $doc->title }}</td>
                                    <td class="p-2">{{ $doc->mime }}</td>
                                    <td class="p-2" style="width: 25px">
                                        <form
                                            action="{{ route('remove-page-attachment', ['docID' => $doc->id]) }}"
                                            method="POST">
                                            @csrf
                                            <button class="btn-sm btn btn-transparent text-danger">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        </div>
                        <div class="tab-pane fade p-3" id="modal-add-attachment-form" role="tabpanel"
                            aria-labelledby="one-tab">
                            <form id="add-attachment-modal-form" class="mt-2"
                                action="{{ route('add-page-attachment') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <fieldset>
                                    <legend>Attachment Details</legend>
                                    <div class="form-group">
                                        <label class="control-label">Title</label>
                                        <input type="text" name="title" class="form-control"
                                            placeholder="Attachment Title..." required />
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Description</label>
                                        <textarea name="description" class="form-control" placeholder="Attachment Description..." required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <input type="hidden" name="url" value="{{ request()->path() }}" />
                                        <label class="control-label">Attachment</label>
                                        <input type="file" name="attachment" class="form-control" required />
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="mdi mdi-content-save"></i> Save Attachment
                                        </button>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<script src="/assets/js/libs/jquery/jquery-3.5.1.min.js"></script>
<script src="/assets/js/libs/jquery/popper.min.js"></script>
<script src="/assets/js/libs/bootstrap/bootstrap-4.4.1.min.js"></script>



{{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script> --}}
{{-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script> --}}
<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed:400,300,600,700&display=swap" rel="stylesheet"
    type="text/css">
<style>
    html,
    body {
        font-family: 'Roboto', sans-serif !important;
        background-color: #f3f3f3 !important;
    }
</style>
@if (isset($dataTable) && $dataTable === true)
<script src="/assets/js/libs/DataTables/jquery.dataTables.min.js"></script>
<script src="/assets/js/libs/DataTables/data.datatables.min.js"></script>
<script src="/assets/js/libs/DataTables/datatable.buttons.min.js"></script>
<script src="/assets/js/libs/DataTables/button.flash.min.js"></script>
<script src="/assets/js/libs/DataTables/jszip.min.js"></script>
<script src="/assets/js/libs/DataTables/pdfmake.min.js"></script>
<script src="/assets/js/libs/DataTables/vsf_fonts.min.js"></script>
<script src="/assets/js/libs/DataTables/buttons.html5.min.js"></script>
<script src="/assets/js/libs/DataTables/buttons.print.min.js"></script>


{{-- <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.23/datatables.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.4/js/dataTables.buttons.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.flash.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
  <script type="text/javascript" src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
  <script type="text/javascript" src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.html5.min.js"></script>
  <script type="text/javascript" src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.2.4/js/buttons.print.min.js"></script> --}}
@endif
@if (isset($select2))
<script src="/select2/select2.min.js"></script>
<script src="/js/ls-select2.js"></script>
@endif
@if (isset($datePicker))
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script> --}}
<script src="/assets/js/libs/bootstrap-datepicker/datepicker1.9.0.min.js"></script>
{{-- <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script> --}}
@endif
<script>
    function userChats(item) {
        console.log('test2');
    }
    Date.prototype.today = function() {
        return ((this.getDate() < 10) ? "0" : "") + this.getDate() + "/" + (((this.getMonth() + 1) < 10) ? "0" :
            "") + (this.getMonth() + 1) + "/" + this.getFullYear();
    }

    // For the time now
    Date.prototype.timeNow = function() {
        return ((this.getHours() < 10) ? "0" : "") + this.getHours() + ":" + ((this.getMinutes() < 10) ? "0" : "") +
            this.getMinutes() + ":" + ((this.getSeconds() < 10) ? "0" : "") + this.getSeconds();
    }
    $(function() {

        $('#message-save').click(function(event) {
            event.preventDefault();
            var to_user_id = $('#to-user-id').val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ url('/user/chat/add') }}",
                method: 'post',
                data: {
                    to_user: to_user_id,
                    chat: $('#chat').val(),
                },
                success: function(data) {
                    console.log(data);
                    $('#chat').val('');
                    var $chat = $(`
						<div class="card p-2 mb-3 ${data.from_user_id == data.current_user_id ? 'float-right':'float-left'}"  ${data.from_user_id == data.current_user_id ? 'style="background-color:turquoise;width:60%"':''} >
							<h5 style="font-size:12px;font-weight:600">${data.from_user_id == data.current_user_id ? 'You:~': data.from_user_name}
							<small class="float-right"><i>${data.created_date}</i></small>
							</h5>
							<p class="mb-2 pl-3">${data.message}</p>
						</div>
					`);

                    $('#messages').append($chat);

                },
                error: function(data) {
                    console.log(data);
                }
            });


        });
        $('.user-chat').click(function(event) {
            var row = $(this).parent('td');
            var user = JSON.parse(this.id);


            $('#to-user-id').val(user.id);
            var $header = $(`
						${user.photo == null ? '<img src="/images/l.jpeg" style="border-radius: 50%;height:50px;width:50px" alt="">':'<img src='+user.photo+'alt="" style="border-radius:50%;height:50px;width:50px;">'}

						<span style="color:turqoise">${user.name}</span>
					`);
            $('#card-title').empty();
            $('#card-title').append($header);
            var count_id = '#user-chat-count-' + user.id;
            $(count_id).empty();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ url('/user/chat/view') }}",
                method: 'post',
                data: {
                    to_user_id: user.id,

                },
                success: function(data) {
                    console.log(data);
                    $('#messages').empty();
                    if (data.length > 1) {

                        $.each(data, function() {
                            var $chat = $(`
						<div class="card p-2 mb-3 ${this.from_user_id == this.current_user_id ? 'float-right':'float-left'}"  ${this.from_user_id == this.current_user_id ? 'style="background-color:turquoise;width:60%"':'style="width:60%"'} >
							<h5 style="font-size:12px;font-weight:600">${this.from_user_id == this.current_user_id ? 'You:~': this.from_user_name}
							<small class="float-right"><i>${this.created_date}</i></small>
							</h5>
							<p class="mb-2 pl-3">${this.message}</p>
						</div>
					`);

                            $('#messages').append($chat);
                        })
                    }
                    if (data.length == 1) {
                        var $chat = $(`
						<div class="card p-2 mb-3 ${data.from_user_id == data.current_user_id ? 'float-right':'float-left'}"  ${data.from_user_id == data.current_user_id ? 'style="background-color:turquoise;width:60%"':'style="width:60%"'} >
							<h5 style="font-size:12px;font-weight:600">${data.from_user_id == data.current_user_id ? 'You:~': data.from_user_name}
							<small class="float-right"><i>${data.created_date}</i></small>
							</h5>
							<p class="mb-2 pl-3">${data.message}</p>
						</div>
					`);

                        $('#messages').append($chat);
                    }
                },
                error: function(data) {
                    console.log(data);
                }

            });

        })
        $('.add-attachment-modal-form-btn').on('click', function() {
            $('#add-attachment-modal-form').submit();
        });

        /**
         * Overlay mode for mobile + tablet (< Bootstrap lg / 992px).
         * Desktop docks the sidebar; tablet/mobile keep content full-width
         * and open the sidebar as a temporary drawer.
         */
        var SIDEBAR_OVERLAY_MAX = 991;

        function isSidebarOverlayViewport() {
            return $(window).width() <= SIDEBAR_OVERLAY_MAX;
        }

        function setMainContentFullWidth() {
            $('#main-container-body').css({
                'margin-left': '0',
                'width': '100%'
            });
        }

        function setMainContentDockedMargin() {
            var $sidebar = $('#sidebar-container');
            if ($sidebar.hasClass('hidden')) {
                setMainContentFullWidth();
                return;
            }
            if ($sidebar.hasClass('sidebar-collapsed')) {
                $('#main-container-body').css({
                    'margin-left': '60px',
                    'width': 'calc(100% - 60px)'
                });
                return;
            }
            $('#main-container-body').css({
                'margin-left': '265px',
                'width': 'calc(100% - 265px)'
            });
        }

        function showSidebarBackdrop() {
            $('#sidebar-backdrop').addClass('is-visible').attr('aria-hidden', 'false');
            $('body').addClass('sidebar-overlay-open');
        }

        function hideSidebarBackdrop() {
            $('#sidebar-backdrop').removeClass('is-visible').attr('aria-hidden', 'true');
            $('body').removeClass('sidebar-overlay-open');
        }

        function syncSidebarToggleAria() {
            var isOpen = !$('#sidebar-container').hasClass('hidden');
            $('#toggle-main-sidebar').attr('aria-expanded', isOpen ? 'true' : 'false');
        }

        function openOverlaySidebar() {
            var $sidebar = $('#sidebar-container');
            $sidebar.removeClass('hidden d-none').addClass('floating-sidebar');
            setMainContentFullWidth();
            showSidebarBackdrop();
            syncSidebarToggleAria();
        }

        function closeOverlaySidebar() {
            var $sidebar = $('#sidebar-container');
            $sidebar.addClass('hidden').removeClass('floating-sidebar');
            setMainContentFullWidth();
            hideSidebarBackdrop();
            syncSidebarToggleAria();
        }

        function applySidebarLayoutForViewport() {
            var $sidebar = $('#sidebar-container');
            if (!$sidebar.length) {
                return;
            }

            if (isSidebarOverlayViewport()) {
                setMainContentFullWidth();
                if ($sidebar.hasClass('hidden')) {
                    $sidebar.removeClass('floating-sidebar');
                    hideSidebarBackdrop();
                } else {
                    $sidebar.addClass('floating-sidebar').removeClass('d-none');
                    showSidebarBackdrop();
                }
            } else {
                hideSidebarBackdrop();
                $sidebar.removeClass('floating-sidebar');
                setMainContentDockedMargin();
            }
            syncSidebarToggleAria();
        }

        // Tablet + mobile: start closed so content gets the full viewport.
        if (isSidebarOverlayViewport()) {
            $('#sidebar-container').addClass('hidden').removeClass('floating-sidebar');
            setMainContentFullWidth();
            hideSidebarBackdrop();
        } else {
            setMainContentDockedMargin();
        }
        syncSidebarToggleAria();

        $('#toggle-main-sidebar').on('click', function() {
            var $sidebar = $('#sidebar-container');
            var willOpen = $sidebar.hasClass('hidden');

            if (isSidebarOverlayViewport()) {
                if (willOpen) {
                    openOverlaySidebar();
                } else {
                    closeOverlaySidebar();
                }
                return;
            }

            $sidebar.toggleClass('hidden');
            if ($sidebar.hasClass('hidden')) {
                $sidebar.removeClass('floating-sidebar');
                setMainContentFullWidth();
            } else {
                $sidebar.removeClass('floating-sidebar');
                setMainContentDockedMargin();
            }
            syncSidebarToggleAria();
        });

        $('#sidebar-backdrop').on('click', function() {
            if (isSidebarOverlayViewport() && !$('#sidebar-container').hasClass('hidden')) {
                closeOverlaySidebar();
            }
        });

        $(document).on('keydown', function(event) {
            if (event.key !== 'Escape') {
                return;
            }
            if (isSidebarOverlayViewport() && !$('#sidebar-container').hasClass('hidden')) {
                closeOverlaySidebar();
            }
        });

        var sidebarResizeTimer = null;
        $(window).on('resize', function() {
            clearTimeout(sidebarResizeTimer);
            sidebarResizeTimer = setTimeout(function() {
                if (isSidebarOverlayViewport()) {
                    // Crossing into tablet/mobile: force closed so layout stays usable.
                    if (!$('#sidebar-container').hasClass('floating-sidebar')) {
                        $('#sidebar-container').addClass('hidden').removeClass('floating-sidebar');
                    }
                }
                applySidebarLayoutForViewport();
            }, 120);
        });

        $('.download-the-document').on('click', function() {
            var href = $(this).data('href');
            console.log(href);
            var a = $(`<a href="${href}" target="_blank">Download</a>`);

            $(this).parents('.modal').append(a);
            a[0].click();
            a.remove();
        });

        $('[data-toggle="tooltip"]').tooltip();

        //Page Configuration Code
        $('#body-row .collapse').collapse('hide');

        // Collapse/Expand icon
        $('#collapse-icon').addClass('fa-angle-double-left');

        // Collapse click
        $('[data-toggle=sidebar-colapse]').click(function() {
            SidebarCollapse();
        });

        function SidebarCollapse() {
            $('.menu-collapsed').toggleClass('d-none');
            $('.sidebar-submenu').toggleClass('d-none');
            $('.submenu-icon').toggleClass('d-none');
            $('#sidebar-container').toggleClass('sidebar-expanded sidebar-collapsed');

            // Adjust main content margin based on sidebar state
            if ($('#sidebar-container').hasClass('sidebar-collapsed')) {
                // Sidebar is collapsed (60px) - adjust margin and width
                $('#main-container-body').css({
                    'margin-left': '60px',
                    'width': 'calc(100% - 60px)'
                });
            } else {
                // Sidebar is expanded (265px) - add margin and adjust width
                $('#main-container-body').css({
                    'margin-left': '265px',
                    'width': 'calc(100% - 265px)'
                });
            }

            // Treating d-flex/d-none on separators with title
            var SeparatorTitle = $('.sidebar-separator-title');
            if (SeparatorTitle.hasClass('d-flex')) {
                SeparatorTitle.removeClass('d-flex');
            } else {
                SeparatorTitle.addClass('d-flex');
            }

            // Collapse/Expand icon
            $('#collapse-icon').toggleClass('fa-angle-double-left fa-angle-double-right');
        }
        //Page Configuration Code End


        $('.modal').appendTo("body");

        if ($(window).width() < 1201) {
            $("#main-sidebar").addClass('close');
        }

        @if(isset($select2))
        function initializeSelect2Elements(scope) {
            if (typeof window.initLsSelect2 === 'function') {
                window.initLsSelect2(scope);
                return;
            }

            var $scope = $(scope || document);

            $scope.find('select.ls-select2, select.livewire-select2').not('.hidden').each(function(i, e) {
                var $el = $(e);

                if ($el.hasClass('no-select2') && !$el.hasClass('ls-select2')) {
                    return;
                }

                if ($el.data('select2')) {
                    try { $el.select2('destroy'); } catch (err) {}
                }

                var $modal = $el.closest('.modal');
                var $parent = $modal.length
                    ? ($modal.find('.modal-content').first().length ? $modal.find('.modal-content').first() : $modal)
                    : $(document.body);

                $el.select2({
                    placeholder: $el.attr('placeholder') || $el.data('placeholder') || 'Select an option',
                    width: '100%',
                    allowClear: true,
                    closeOnSelect: !$el.prop('multiple'),
                    dropdownParent: $parent
                });

                $el.css('width', '100%');
            });
        }

        initializeSelect2Elements();

        $(document).on('shown.bs.modal', '.modal', function() {
            initializeSelect2Elements($(this));
        });

        document.addEventListener('livewire:navigated', function() {
            initializeSelect2Elements();
        });

        if (typeof Livewire !== 'undefined') {
            Livewire.hook('morphed', function ({ el }) {
                initializeSelect2Elements(el);
            });

            Livewire.hook('message.processed', function() {
                initializeSelect2Elements();
            });
        }
        @endif

        @if(isset($datePicker))
        $('.datepicker').each(function() {
            var dF = $(this);
            var hasMax = $.trim($(this).attr('max')) == "" ? "0" : "";
            dF.datepicker({
                clearBtn: true,
                maxDate: $.now(),
                format: "yyyy-mm-dd"
            });
        });
        @endif

        @if(\Session::has('success') || \Session::has('error'))
        setTimeout(() => {
            $('#message-section').slideUp(600);
        }, 10000);
        @endif

        $('#main-body-content').on('click', '#main-sidebar-toggler', function() {
            $("#main-sidebar").toggleClass('close');
        });

        if ($('#main-wrapper').length > 0) {
            $("#menu-toggle").click(function(e) {
                e.preventDefault();
                $("#wrapper").toggleClass("toggled");
            });
        }

        @if(isset($dataTable) && $dataTable === true)
        $('.table-responsive .table.table-condensed.table-sm').not('.server-side').not('.livewire-table').each(function(i, e) {
            var lengthMenu = $(e).data('menutext') ?? [10, 25, 50, 75, 100];
            var pageTitle = $(document).find('title').text();
            var fileName = $(e).data('filename') ?? pageTitle;

            fileName += '-D{{ getRandomHex() }}';

            buttonConfigs = ['copy', {
                extend: 'csv',
                filename: fileName
            }, {
                extend: 'excelHtml5',
                footer: true,
                filename: fileName
            }, {
                extend: 'pdf',
                filename: fileName
            }, 'print'];

            var fixedCols = $(e).data('fixedcls');
            var $fCOps = {
                dom: 'Blfrtip',
                buttons: buttonConfigs,
                "order": [],
                "language": {
                    // "lengthMenu": lengthMenu,
                    "search": '<i class="fa fa-search"></i>',
                    "paginate": {
                        "previous": '<i class="fa fa-angle-left"></i>',
                        "next": '<i class="fa fa-angle-right"></i>'
                    }
                }
            };
            if (fixedCols == "true" || fixedCols == true) {
                //   $fCOps['scrollY'] = 200;
                //   $fCOps['scrollX'] = true;
                //   $fCOps['scrollCollapse'] = true;
                //   $fCOps['scroller'] = true;
                //   $fCOps['fixedColumns'] = {
                //    left: 2
                //   }
                // console.log($fCOps);
            }
            $(e).DataTable($fCOps);
        });
        @endif

        // if($("#main-sidebar").length > 0){
        //   $('#main-body-content').append(`<button id="main-sidebar-toggler" class="btn btn-circle btn-circle-sm btn-danger"><i class="mdi mdi-menu"></i></button>`);
        // }

    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var banner = document.getElementById('password-expiry-banner');
        if (!banner) {
            return;
        }

        var storageKey = 'password-expiry-banner-dismissed';

        if (sessionStorage.getItem(storageKey)) {
            banner.remove();
            document.body.classList.remove('has-password-expiry-banner');
            return;
        }

        var dismissButton = banner.querySelector('.password-expiry-banner__dismiss');
        if (!dismissButton) {
            return;
        }

        dismissButton.addEventListener('click', function () {
            banner.remove();
            document.body.classList.remove('has-password-expiry-banner');
            sessionStorage.setItem(storageKey, '1');
        });
    });
</script>
<script src="{{ asset('js/method-sequences.js') }}"></script>
@yield('script')
@stack('scripts')
@livewireScripts
@if (isset(Auth::user()->company_id) && Auth::user()->company_id == 0)
<div id="select-default-company" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <form class="modal-content" method="POST" action="{{ route('set-default-company') }}"
            enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h4 class="modal-title"><i class="mdi mdi-domain"></i> View System As:</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Select Company</label>
                    <select class="form-control" name="company_id" required>
                        <option value="0">As Administrator</option>
                        @foreach (getCompanies() as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
@endif

@auth
<script>
(function () {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

    var vapidPublicKey = '{{ config("webpush.vapid.public_key") }}';
    if (!vapidPublicKey) return;

    function urlBase64ToUint8Array(base64String) {
        var padding = '='.repeat((4 - base64String.length % 4) % 4);
        var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        var rawData = atob(base64);
        var outputArray = new Uint8Array(rawData.length);
        for (var i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    function getBrowserName() {
        var ua = navigator.userAgent;
        if (ua.indexOf('Edg') !== -1) return 'Edge';
        if (ua.indexOf('Chrome') !== -1) return 'Chrome';
        if (ua.indexOf('Firefox') !== -1) return 'Firefox';
        if (ua.indexOf('Safari') !== -1) return 'Safari';
        return 'Unknown';
    }

    navigator.serviceWorker.register('/sw.js').then(function (reg) {
        return Notification.requestPermission().then(function (permission) {
            if (permission !== 'granted') return;

            return reg.pushManager.getSubscription().then(function (existing) {
                if (existing) return existing;

                return reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
                });
            }).then(function (sub) {
                if (!sub) return;
                var json = sub.toJSON();
                return fetch('/push-subscriptions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        endpoint: json.endpoint,
                        p256dh: json.keys.p256dh,
                        auth: json.keys.auth,
                        browser_name: getBrowserName(),
                    }),
                });
            });
        });
    }).catch(function (err) {
        console.error('[WebPush] Service worker registration failed:', err);
    });
}());
</script>
@endauth

</body>
</html>