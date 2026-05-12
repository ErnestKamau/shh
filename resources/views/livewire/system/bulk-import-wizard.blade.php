<div>
<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <!-- Header -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title mb-3">
                        <i class="fas fa-download"></i> Bulk Data Import Wizard
                    </h2>
                    <p class="text-muted mb-0">Import data in bulk for multiple modules and forms. Download templates, fill with data, validate, and upload to your system.</p>
                </div>
            </div>

            <!-- Progress Steps -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col">
                            <div class="step-indicator {{ $currentStep >= 1 ? 'active' : '' }}">
                                <div class="step-number">1</div>
                                <div class="step-label">Select Module</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="step-indicator {{ $currentStep >= 2 ? 'active' : '' }}">
                                <div class="step-number">2</div>
                                <div class="step-label">Select Form</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="step-indicator {{ $currentStep >= 3 ? 'active' : '' }}">
                                <div class="step-number">3</div>
                                <div class="step-label">Download Template</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="step-indicator {{ $currentStep >= 4 ? 'active' : '' }}">
                                <div class="step-number">4</div>
                                <div class="step-label">Upload & Validate</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="step-indicator {{ $currentStep >= 5 ? 'active' : '' }}">
                                <div class="step-number">5</div>
                                <div class="step-label">Review Results</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            @if ($message)
                <div class="alert alert-{{ $messageType === 'error' ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
                    <strong>{{ $messageType === 'error' ? 'Error' : 'Success' }}:</strong> {{ $message }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Step 1: Select Module -->
            @if ($currentStep === 1)
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Step 1: Select Module</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">Choose the module you want to import data for:</p>
                        <div class="row">
                            @foreach ($availableModules as $moduleKey => $module)
                                <div class="col-md-6 mb-3">
                                    <div class="card border-0 shadow-sm hover-card" style="cursor: pointer;" wire:click="selectModule('{{ $moduleKey }}')">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">{{ $module['name'] }}</h5>
                                            <p class="text-muted small mb-0">{{ count($module['forms']) }} forms available</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Step 2: Select Form Type -->
            @if ($currentStep === 2)
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Step 2: Select Form Type</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            Choose the form you want to import for <strong>{{ $availableModules[$selectedModule]['name'] ?? $selectedModule }}</strong>:
                        </p>
                        <div class="list-group">
                            @foreach ($formTypes as $formTypeKey => $formTypeName)
                                <button type="button" class="list-group-item list-group-item-action text-start" wire:click="selectFormType('{{ $formTypeKey }}')">
                                    <h6 class="mb-1">{{ $formTypeName }}</h6>
                                    <small class="text-muted">{{ $selectedModule }}/{{ $formTypeKey }}</small>
                                </button>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            <button class="btn btn-secondary" wire:click="goBack()">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Step 3: Download Template -->
            @if ($currentStep === 3)
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Step 3: Download Template</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle"></i>
                            <strong>Instructions:</strong>
                            <ol class="mb-0">
                                <li>Download the template for <strong>{{ $formTypes[$selectedFormType] ?? $selectedFormType }}</strong></li>
                                <li>Enter your data starting from row 2 (just below the header row)</li>
                                <li>Fields marked with <strong>*</strong> are required</li>
                                <li>Save the file and come back to upload</li>
                            </ol>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-success mb-3">
                                            <i class="fas fa-file-excel fa-2x"></i>
                                        </h6>
                                        <p class="card-text mb-3">
                                            <strong>{{ $formTypes[$selectedFormType] ?? $selectedFormType }} Template</strong>
                                        </p>
                                        <button class="btn btn-success btn-sm" wire:click="downloadTemplate()">
                                            <i class="fas fa-download"></i> Download Template
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-info mb-3">
                                            <i class="fas fa-question-circle fa-2x"></i>
                                        </h6>
                                        <p class="card-text mb-3">
                                            <strong>Need Help?</strong>
                                        </p>
                                        <p class="small text-muted mb-0">The template contains only headers. Use names/codes (not IDs/UUIDs) where applicable.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <button class="btn btn-secondary" wire:click="goBack()">
                                    <i class="fas fa-arrow-left"></i> Back
                                </button>
                            </div>
                            <div class="col text-end">
                                <button class="btn btn-primary" wire:click="goToUpload()">
                                    Next: Upload File <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Step 4: Upload & Validate -->
            @if ($currentStep === 4)
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Step 4: Upload & Validate</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle"></i>
                            <strong>Upload Instructions:</strong>
                            <ul class="mb-0">
                                <li>Select your filled Excel file (.xlsx, .xls, or .csv)</li>
                                <li>Maximum file size: 5 MB</li>
                                <li>The system will validate and import your data</li>
                                <li>Invalid rows will be skipped with error messages</li>
                            </ul>
                        </div>

                        <form wire:submit.prevent="uploadFile()">
                            <div class="mb-4">
                                <label for="uploadedFile" class="form-label">
                                    <strong>Select File to Upload</strong>
                                </label>
                                <input 
                                    type="file" 
                                    id="uploadedFile"
                                    class="form-control @error('uploadedFile') is-invalid @enderror"
                                    wire:model="uploadedFile"
                                    accept=".xlsx,.xls,.csv"
                                >
                                @error('uploadedFile')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            @if ($uploadedFile)
                                <div class="alert alert-success mb-4">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>File Selected:</strong> {{ $uploadedFile->getClientOriginalName() }}
                                </div>
                            @endif

                            <div class="row">
                                <div class="col">
                                    <button type="button" class="btn btn-secondary" wire:click="goBack()">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </button>
                                </div>
                                <div class="col text-end">
                                    <button type="submit" class="btn btn-primary" {{ $uploadedFile ? '' : 'disabled' }}>
                                        <i class="fas fa-upload"></i> Upload & Process
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Step 5: Review Results -->
            @if ($currentStep === 5)
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Step 5: Import Results</h5>
                    </div>
                    <div class="card-body">
                        @if ($currentBatch)
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card text-center border-success">
                                        <div class="card-body">
                                            <h3 class="text-success mb-2">{{ $importResults['imported_rows'] ?? 0 }}</h3>
                                            <p class="text-muted mb-0">Rows Successfully Imported</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card text-center border-warning">
                                        <div class="card-body">
                                            <h3 class="text-warning mb-2">{{ $importResults['error_rows'] ?? 0 }}</h3>
                                            <p class="text-muted mb-0">Rows with Errors</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Upsert Summary -->
                            @if (isset($importResults['upserted']) && !empty($importResults['upserted']['records']))
                                <div class="alert alert-info mb-4">
                                    <strong>Data Summary:</strong>
                                    <ul class="mb-0">
                                        <li><strong>{{ $importResults['upserted']['inserted'] ?? 0 }}</strong> new records created</li>
                                        <li><strong>{{ $importResults['upserted']['updated'] ?? 0 }}</strong> existing records updated</li>
                                    </ul>
                                </div>
                            @endif

                            <!-- Error Details -->
                            @if (!empty($importResults['errors']))
                                <div class="card border-danger mb-4">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0">Error Details</h6>
                                    </div>
                                    <div class="card-body">
                                        @foreach ($importResults['errors'] as $error)
                                            <div class="mb-3 pb-3 border-bottom">
                                                <strong class="text-danger">{{ $error['message'] }}</strong>
                                                <p class="text-muted small mb-0">
                                                    Affected rows: {{ implode(', ', $error['rows']) }}
                                                </p>
                                            </div>
                                        @endforeach
                                        <button class="btn btn-danger btn-sm mt-3" wire:click="downloadErrorReport()">
                                            <i class="fas fa-download"></i> Download Error Report
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-success mb-4">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>Success!</strong> All data was imported successfully with no errors.
                                </div>
                            @endif

                            <div class="row">
                                <div class="col text-end">
                                    <button class="btn btn-primary" wire:click="resetWizard()">
                                        <i class="fas fa-plus"></i> Import More Data
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .step-indicator {
        display: flex;
        flex-direction: column;
        align-items: center;
        opacity: 0.5;
    }

    .step-indicator.active {
        opacity: 1;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .step-indicator.active .step-number {
        background-color: #007bff;
        color: white;
    }

    .step-label {
        font-size: 0.875rem;
        font-weight: 500;
    }

    .hover-card {
        transition: all 0.3s ease;
    }

    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>
</div>
