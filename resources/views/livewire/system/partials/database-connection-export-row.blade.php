@php
    $driver = strtolower((string) ($connection['driver'] ?? 'unknown'));
    $driverClass = in_array($driver, ['pgsql', 'mysql', 'mariadb'], true) ? $driver : 'unknown';
    $exportFormats = $connection['export_formats'] ?? [];
    $supportsSql = in_array('sql', $exportFormats, true);
    $supportsDump = in_array('dump', $exportFormats, true);
    $supportsExport = (bool) ($connection['export_supported'] ?? false);
    $dumpHintKey = in_array($driver, ['mysql', 'mariadb'], true)
        ? 'system.export_hint_compressed_sql'
        : 'system.export_hint_custom_dump';
@endphp

<div class="sys-admin-connection-item px-3 py-2 mb-2">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
        <div class="mb-2 mb-lg-0">
            <div class="d-flex align-items-center flex-wrap mb-1">
                <div class="font-weight-bold mono mr-2">{{ $connection['name'] }}</div>
                <span class="sys-admin-driver-badge sys-admin-driver-{{ $driverClass }}">{{ strtoupper($driver ?: 'n/a') }}</span>
            </div>
            <div class="small text-muted">
                {{ $connection['database'] ?? 'n/a' }}
                @if(($connection['host'] ?? '') !== '')
                    · {{ $connection['host'] }}@if(($connection['port'] ?? '') !== ''):{{ $connection['port'] }}@endif
                @endif
            </div>
            @if(!$supportsExport)
                <div class="small text-muted">Export is unavailable for this driver.</div>
            @endif
        </div>
        @if($canExport)
            <div class="d-flex flex-wrap align-items-center">
                <div class="mr-2 mb-2 mb-lg-0">
                    @if($supportsSql)
                        <a href="{{ route('system-settings.database-export', ['format' => 'sql', 'connection' => $connection['name']]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="mdi mdi-download"></i> SQL
                        </a>
                    @else
                        <span class="btn btn-sm btn-outline-secondary sys-admin-action-disabled" aria-disabled="true">
                            <i class="mdi mdi-download"></i> SQL
                        </span>
                    @endif
                    <div class="small text-muted mt-1">{{ __('system.export_hint_plain_sql') }}</div>
                </div>

                <div class="mb-2 mb-lg-0">
                    @if($supportsDump)
                        <a href="{{ route('system-settings.database-export', ['format' => 'dump', 'connection' => $connection['name']]) }}" class="btn btn-sm btn-outline-dark">
                            <i class="mdi mdi-database-export"></i> Dump
                        </a>
                    @else
                        <span class="btn btn-sm btn-outline-secondary sys-admin-action-disabled" aria-disabled="true">
                            <i class="mdi mdi-database-export"></i> Dump
                        </span>
                    @endif
                    <div class="small text-muted mt-1">{{ __($dumpHintKey) }}</div>
                </div>
            </div>
        @endif
    </div>
</div>