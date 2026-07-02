@extends('layouts.configuration.layout.app')

@section('title2')
    <title>System Backups</title>
@endsection

@section('content2')
    <main>
        @php
            $items = [
                [
                    'link' => route('system-settings'),
                    'name' => 'System Settings',
                    'icon' => null,
                ],
                [
                    'link' => route('system-settings.backups'),
                    'name' => 'System Backups',
                    'icon' => null,
                ],
            ];
        @endphp

        <x-bread-crumb :items="$items"></x-bread-crumb>

        <div class="p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
                <div>
                    <h3 class="mb-1"><i class="mdi mdi-backup-restore"></i> System Backups</h3>
                    <p class="text-muted mb-0">Export the full PostgreSQL database or selected system data sets from one place.</p>
                </div>
            </div>

            @if(session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-5 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><i class="mdi mdi-database-export"></i> Full Database Backup</h5>
                            <p class="text-muted mb-3">Use the existing PostgreSQL dump pipeline to download the complete database in one click.</p>

                            @if($canExport)
                                <div class="mb-2">
                                    <a href="{{ route('system-settings.database-export', ['format' => 'sql']) }}" class="btn btn-outline-primary mr-2 mb-2">
                                        <i class="mdi mdi-download"></i> SQL dump
                                    </a>
                                    <a href="{{ route('system-settings.database-export', ['format' => 'dump']) }}" class="btn btn-outline-dark mb-2">
                                        <i class="mdi mdi-database-export"></i> Custom dump
                                    </a>
                                </div>
                                <small class="text-muted d-block">These downloads use the default PostgreSQL connection configured for the system.</small>
                            @else
                                <div class="alert alert-warning mb-0">You do not have permission to download system backups.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-7 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="card-title mb-1"><i class="mdi mdi-folder-download"></i> Selected Module Data</h5>
                                    <p class="text-muted mb-0">Pick one or more user-friendly data groups. CSV exports become a ZIP bundle when multiple tables are included.</p>
                                </div>
                                <span class="badge badge-light border">CSV / SQL / Dump</span>
                            </div>

                            @if($canExport)
                                <form action="{{ route('system-settings.backups.export') }}" method="POST">
                                    @csrf

                                    <div class="form-group">
                                        <label class="control-label">Backup format</label>
                                        <select name="format" class="form-control" required>
                                            <option value="csv" {{ old('format') === 'csv' ? 'selected' : '' }}>CSV</option>
                                            <option value="sql" {{ old('format') === 'sql' ? 'selected' : '' }}>PostgreSQL SQL</option>
                                            <option value="dump" {{ old('format') === 'dump' ? 'selected' : '' }}>PostgreSQL custom dump</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label">Select backup targets</label>
                                        <div class="row">
                                            @foreach($backupTargets as $target)
                                                <div class="col-md-6 mb-3">
                                                    <div class="border rounded p-3 h-100">
                                                        <div class="custom-control custom-checkbox">
                                                            <input
                                                                type="checkbox"
                                                                class="custom-control-input"
                                                                id="backup-target-{{ $target['key'] }}"
                                                                name="targets[]"
                                                                value="{{ $target['key'] }}"
                                                                {{ in_array($target['key'], old('targets', []), true) ? 'checked' : '' }}
                                                            />
                                                            <label class="custom-control-label font-weight-bold" for="backup-target-{{ $target['key'] }}">
                                                                {{ $target['label'] }}
                                                            </label>
                                                        </div>
                                                        <small class="text-muted d-block mt-2">{{ $target['description'] }}</small>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="mdi mdi-cloud-download"></i> Download selected data
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-warning mb-0">You do not have permission to export selected backup targets.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="mdi mdi-information-outline"></i> Backup Notes</h5>
                    <ul class="mb-0 pl-3">
                        <li>SQL and custom dump exports use the default PostgreSQL connection.</li>
                        <li>Module exports are limited to the approved data groups listed above.</li>
                        <li>CSV export creates a ZIP file automatically when more than one table is included.</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>
@endsection