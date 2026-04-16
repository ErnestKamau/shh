<div>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">{{ __('mas/personnel.title') }}</h1>
            <p class="text-muted small mb-0">{{ __('mas/personnel.subtitle') }}</p>
        </div>
        <div class="col-auto d-flex align-items-center">
            <a href="{{ route('mas.export', 'personnel') }}" class="btn btn-success btn-sm mr-2">
                <i class="mdi mdi-download"></i> {{ __('mas/common.download_report') }}
            </a>
            <span class="badge badge-primary p-2">
                <i class="mdi mdi-account-group mr-1"></i> {{ __('mas/personnel.total_users') }}: {{ $stats['total_staff'] }}
            </span>
        </div>
    </div>

    <!-- KPI Row -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 border-left-primary h-100">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">{{ __('mas/personnel.active_staff') }}</h6>
                            <h2 class="font-weight-bold text-dark mb-0">{{ $stats['active_users'] }}</h2>
                        </div>
                        <div class="icon-circle bg-primary-light text-primary">
                            <i class="mdi mdi-account-check mdi-36px"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 border-left-info h-100">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">{{ __('mas/personnel.departments') }}</h6>
                            <h2 class="font-weight-bold text-dark mb-0">{{ $stats['departments'] }}</h2>
                        </div>
                        <div class="icon-circle bg-info-light text-info">
                            <i class="mdi mdi-office-building mdi-36px"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 border-left-success h-100">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">{{ __('mas/personnel.certifications') }}</h6>
                            <h2 class="font-weight-bold text-dark mb-0">{{ $stats['certifications'] }}</h2>
                        </div>
                        <div class="icon-circle bg-success-light text-success">
                            <i class="mdi mdi-certificate mdi-36px"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="glass-panel p-5 text-center shadow-sm">
                <i class="mdi mdi-chart-bubble mdi-48px text-primary mb-3"></i>
                <h4 class="font-weight-bold">{{ __('mas/personnel.org_deep_dive') }}</h4>
                <p class="text-muted">{{ __('mas/personnel.org_description') }}</p>
                <div class="mt-4">
                    <a href="/personnel" class="btn btn-primary px-4 py-2 shadow-sm" style="border-radius: 20px;">
                        {{ __('mas/personnel.manage_all') }} <i class="mdi mdi-open-in-new ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .border-left-primary { border-left: 4px solid #007bff !important; }
    .border-left-info { border-left: 4px solid #17a2b8 !important; }
    .border-left-success { border-left: 4px solid #28a745 !important; }
    .icon-circle { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .bg-primary-light { background-color: rgba(0, 123, 255, 0.1); }
    .bg-info-light { background-color: rgba(23, 162, 184, 0.1); }
    .bg-success-light { background-color: rgba(40, 167, 69, 0.1); }
</style>
</div>