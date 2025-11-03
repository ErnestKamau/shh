<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livewire Components Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.9.96/css/materialdesignicons.min.css" rel="stylesheet">
    @livewireStyles
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="mdi mdi-test-tube text-primary"></i>
                    Livewire Components Test Page
                </h1>
                <p class="text-muted mb-4">This page demonstrates the Livewire components for the laboratory management system.</p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Available Components</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <i class="mdi mdi-flask-outline text-primary" style="font-size: 2rem;"></i>
                                        <h6 class="mt-2">Sample Types Manager</h6>
                                        <p class="text-muted small">Manage sample types, analysis types, and elements</p>
                                        <a href="{{ route('livewire.sample-types') }}" class="btn btn-outline-primary btn-sm">Test Component</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <i class="mdi mdi-scale text-info" style="font-size: 2rem;"></i>
                                        <h6 class="mt-2">Standards Manager</h6>
                                        <p class="text-muted small">Manage standards, standard values, and analytes</p>
                                        <a href="{{ route('livewire.standards') }}" class="btn btn-outline-info btn-sm">Test Component</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <i class="mdi mdi-test-tube text-success" style="font-size: 2rem;"></i>
                                        <h6 class="mt-2">Analysis Types Manager</h6>
                                        <p class="text-muted small">Manage analysis types and their elements</p>
                                        <a href="{{ route('livewire.analysis-types') }}" class="btn btn-outline-success btn-sm">Test Component</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Component Features</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">✅ Completed Features:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="mdi mdi-check text-success"></i> Modern Bootstrap 5 UI with Material Design Icons</li>
                                    <li><i class="mdi mdi-check text-success"></i> Livewire-powered modals for CRUD operations</li>
                                    <li><i class="mdi mdi-check text-success"></i> Real-time search and filtering</li>
                                    <li><i class="mdi mdi-check text-success"></i> Dynamic relationship loading</li>
                                    <li><i class="mdi mdi-check text-success"></i> Form validation with error handling</li>
                                    <li><i class="mdi mdi-check text-success"></i> AJAX operations (no page reloads)</li>
                                    <li><i class="mdi mdi-check text-success"></i> Responsive table layouts</li>
                                    <li><i class="mdi mdi-check text-success"></i> Proper Eloquent relationships</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-info">🔄 Workflow Integration:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="mdi mdi-arrow-right text-primary"></i> Sample Types → Analysis Types → Elements</li>
                                    <li><i class="mdi mdi-arrow-right text-primary"></i> Standards → Standard Analytes → Standard Values</li>
                                    <li><i class="mdi mdi-arrow-right text-primary"></i> Cross-component data relationships</li>
                                    <li><i class="mdi mdi-arrow-right text-primary"></i> Consistent UI/UX across all components</li>
                                    <li><i class="mdi mdi-arrow-right text-primary"></i> Modern modal-based interactions</li>
                                    <li><i class="mdi mdi-arrow-right text-primary"></i> Real-time data updates</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-success">
                    <h6><i class="mdi mdi-check-circle"></i> Migration Complete!</h6>
                    <p class="mb-0">The entire laboratory management workflow has been successfully migrated to Livewire components with modern UI/UX. All relationships are properly maintained and the system provides a seamless user experience.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>
</html>