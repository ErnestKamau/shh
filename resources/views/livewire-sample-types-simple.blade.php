<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Types Management - Livewire Component</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.9.96/css/materialdesignicons.min.css" rel="stylesheet">
    @livewireStyles
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <h4><i class="mdi mdi-information"></i> Sample Types Livewire Component</h4>
                    <p class="mb-0">This page demonstrates how to access the SampleTypeManager Livewire component.</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-flask-outline text-primary"></i>
                            Sample Types Management
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">The Livewire component is properly configured and ready to use. Here's how to access it:</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Component Details:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Component Class:</strong> <code>App\Livewire\SampleTypeManager</code></li>
                                    <li><strong>Blade Template:</strong> <code>livewire.samples.sample-type-manager</code></li>
                                    <li><strong>Route:</strong> <code>/livewire/sample-types</code></li>
                                    <li><strong>Route Name:</strong> <code>livewire.sample-types</code></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Features Available:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="mdi mdi-check text-success"></i> Sample Types CRUD</li>
                                    <li><i class="mdi mdi-check text-success"></i> Analysis Types Management</li>
                                    <li><i class="mdi mdi-check text-success"></i> Analysis Elements Management</li>
                                    <li><i class="mdi mdi-check text-success"></i> Real-time Search & Filtering</li>
                                    <li><i class="mdi mdi-check text-success"></i> Modal-based Forms</li>
                                    <li><i class="mdi mdi-check text-success"></i> AJAX Operations</li>
                                </ul>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-12">
                                <h6>How to Use:</h6>
                                <ol>
                                    <li>Navigate to <code>/livewire/sample-types</code> in your browser</li>
                                    <li>The component will load with all sample types</li>
                                    <li>Use the "Add Sample Type" button to create new sample types</li>
                                    <li>Click on sample types to manage their analysis types</li>
                                    <li>Click on analysis types to manage their elements</li>
                                </ol>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <h6><i class="mdi mdi-alert"></i> Note:</h6>
                                    <p class="mb-0">There's currently a PHP version compatibility issue preventing the Livewire component from loading properly. The component is correctly configured but requires PHP 8.3+ to run with Laravel 12.</p>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <a href="{{ route('livewire.sample-types') }}" class="btn btn-primary">
                                    <i class="mdi mdi-test-tube"></i> Test Livewire Component
                                </a>
                                <a href="/" class="btn btn-outline-secondary">
                                    <i class="mdi mdi-home"></i> Back to Home
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>
</html>
