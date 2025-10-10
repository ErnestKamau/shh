@extends('layouts.sample-submissions', ['select2' => true])

@section('module-name')
    <li class="nav-item">
        <a class="nav-link module-name" style="font-size:12px !important" href="{{ route('home') }}">
            <i class="mdi mdi-file-document-multiple"></i> Sample Submissions
        </a>
    </li>
@endsection

@section('title')
    <style type="text/css">
        /* Livewire styles */
        [wire\:loading] {
            display: none;
        }

        [wire\:loading].flex {
            display: flex;
        }

        .form-select:focus,
        .form-control:focus {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
    </style>
@endsection

@section('content')
    <div id="body-row" class="p-3" style="width:100%;height:100vh">
        <div class="" id="main-container-body">
            <!-- Error Messages Section -->
            <div id="message-section" style="padding: 10px 10px 0px 10px !important">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li><i class="fas fa-exclamation-triangle"></i> {{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if (\Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-thumbs-up"></i> {{ Session::get('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if (\Session::has('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle"></i> {{ Session::get('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>

            <!-- Breadcrumb -->
            <div class="">
                <?php
                $items = [
                    [
                        'link' => '/',
                        'name' => 'Home',
                        'icon' => null,
                    ],
                    [
                        'link' => 'null',
                        'name' => 'Sample Submissions',
                        'icon' => null,
                    ],
                ];
                ?>
                <x-bread-crumb :items="$items"></x-bread-crumb>
            </div>

            <!-- Livewire Component -->
            @livewire('submissions.submission-forms-manager')
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert-dismissible');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
@endsection
