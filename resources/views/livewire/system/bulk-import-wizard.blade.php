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
                        @if ($selectedModule === 'lab')
                            <div class="alert alert-warning mb-4">
                                <i class="fas fa-sitemap"></i>
                                <strong>Tip:</strong>
                                Use <strong>Amspec Parameters</strong> to create and map Sample Type → Analysis Type → Analyte → Parameters in one spreadsheet.
                                On upload, check <em>Clear previous…</em> to replace existing hierarchy data cleanly before import.
                                <strong>Sample Types &amp; Analysis Types</strong> can create Sample Types and nest Analysis Types under them (Labs must already exist).
                                When clearing that form, only Analysis Parameters under existing Analysis Types are removed.
                            </div>
                        @endif
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
                                @if ($selectedFormType === 'analysis_method')
                                    <li>Or upload your AmSpec Parameters workbook directly (Reference Method + Test Method SOP columns are extracted)</li>
                                @endif
                                @if ($selectedFormType === 'sample_condition')
                                    <li>
                                        This form depends on parents already existing —
                                        <strong>Sample Types</strong> must already exist
                                    </li>
                                @endif
                                @if ($selectedFormType === 'analysis_type')
                                    <li>
                                        Creates <strong>Sample Types</strong> and <strong>Analysis Types</strong> under each sample type.
                                        <strong>Labs</strong> must already exist.
                                    </li>
                                @endif
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

                        @if ($selectedFormType === 'amspec_parameters')
                            <div class="alert alert-danger mb-4">
                                <strong>Replace existing lab hierarchy</strong>
                                <p class="mb-2 small">
                                    When enabled, all <strong>Sample Types</strong>, <strong>Analysis Types</strong>,
                                    <strong>Analytes</strong>, and <strong>Analysis Parameters</strong> for your company
                                    are permanently deleted before import — including dependent mappings
                                    (lab links, sample conditions, pricelist lines tied to those records),
                                    plus related workflow batches/samples/results so foreign keys stay clean.
                                    Standards catalog rows are not deleted. After import, only the uploaded hierarchy remains.
                                </p>
                                <div class="form-check mb-3">
                                    <input type="checkbox" wire:model.live="replaceExisting" class="form-check-input" id="replace_existing_amspec_parameters">
                                    <label class="form-check-label" for="replace_existing_amspec_parameters">
                                        Clear previous Sample Types / Analysis Types / Parameters before import
                                    </label>
                                </div>
                                @if ($replaceExisting)
                                    <div class="mb-0">
                                        <label class="form-label" for="purge_confirmation_amspec">Type <code>DELETE ALL LAB DATA</code> or your company name to confirm</label>
                                        <input type="text" id="purge_confirmation_amspec" wire:model="purgeConfirmation" class="form-control" placeholder="DELETE ALL LAB DATA">
                                        @error('purgeConfirmation') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if ($selectedFormType === 'analysis_type')
                            <div class="alert alert-danger mb-4">
                                <strong>Clear previous Analysis Types</strong>
                                <p class="mb-2 small">
                                    When enabled, all Analysis Types for your company are deleted before import,
                                    along with Analysis Parameters under them. Sample Types, Analytes, and Labs are kept.
                                </p>
                                <div class="form-check mb-3">
                                    <input type="checkbox" wire:model.live="replaceExisting" class="form-check-input" id="replace_existing_analysis_types">
                                    <label class="form-check-label" for="replace_existing_analysis_types">
                                        Clear previous Analysis Types before import
                                    </label>
                                </div>
                                @if ($replaceExisting)
                                    <div class="mb-0">
                                        <label class="form-label" for="purge_confirmation_analysis_types">Type <code>DELETE ALL ANALYSIS TYPES</code> or your company name to confirm</label>
                                        <input type="text" id="purge_confirmation_analysis_types" wire:model="purgeConfirmation" class="form-control" placeholder="DELETE ALL ANALYSIS TYPES">
                                        @error('purgeConfirmation') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if ($selectedFormType === 'analyte')
                            <div class="alert alert-danger mb-4">
                                <strong>Replace existing analytes</strong>
                                <p class="mb-2 small">
                                    When enabled, all Analytes for your company are deleted before import,
                                    along with Analysis Parameters and standard/analyte mappings that reference them,
                                    so no orphan parameter rows remain. Sample Types and Analysis Types are kept.
                                </p>
                                <div class="form-check mb-3">
                                    <input type="checkbox" wire:model.live="replaceExisting" class="form-check-input" id="replace_existing_analytes">
                                    <label class="form-check-label" for="replace_existing_analytes">
                                        Clear previous Analytes before import
                                    </label>
                                </div>
                                @if ($replaceExisting)
                                    <div class="mb-0">
                                        <label class="form-label" for="purge_confirmation_analytes">Type <code>DELETE ALL ANALYTES</code> or your company name to confirm</label>
                                        <input type="text" id="purge_confirmation_analytes" wire:model="purgeConfirmation" class="form-control" placeholder="DELETE ALL ANALYTES">
                                        @error('purgeConfirmation') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if ($selectedFormType === 'analysis_method')
                            <div class="alert alert-danger mb-4">
                                <strong>Replace existing analysis methods</strong>
                                <p class="mb-2 small">When enabled, all analysis methods for your company will be permanently deleted before import. Other data (stage headers, samples, analysis elements, results) is left unchanged. You can upload the dedicated template or your AmSpec Parameters workbook (methods-only extraction).</p>
                                <div class="form-check mb-3">
                                    <input type="checkbox" wire:model.live="replaceExisting" class="form-check-input" id="replace_existing_analysis_methods" checked>
                                    <label class="form-check-label" for="replace_existing_analysis_methods">
                                        Replace all existing analysis methods for this company before import
                                    </label>
                                </div>
                                @if ($replaceExisting)
                                    <div class="mb-0">
                                        <label class="form-label" for="purge_confirmation_methods">Type <code>DELETE ALL METHODS</code> or your company name to confirm</label>
                                        <input type="text" id="purge_confirmation_methods" wire:model="purgeConfirmation" class="form-control" placeholder="DELETE ALL METHODS">
                                        @error('purgeConfirmation') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                        @endif

                        <form wire:submit.prevent="uploadFile()">
                            @if ($selectedFormType === 'user' || $selectedFormType === 'equipment' || $selectedFormType === 'inventory')
                                <div class="mb-4 text-start">
                                    <label class="form-label">
                                        <strong>Associate with Zone (Optional)</strong>
                                    </label>
                                    
                                    <!-- Premium Custom Zone Selector -->
                                    <div class="custom-zone-selector-wrapper" 
                                         x-data="{ 
                                            open: false, 
                                            search: '',
                                            zones: @js($zones),
                                            selectedId: @entangle('selectedZoneId'),
                                            get selectedZone() {
                                                return this.zones.find(z => z.id == this.selectedId) || null;
                                            },
                                            get filteredZones() {
                                                if (!this.search.trim()) return this.zones;
                                                return this.zones.filter(z => 
                                                    (z.value && z.value.toLowerCase().includes(this.search.toLowerCase())) ||
                                                    (z.key && z.key.toLowerCase().includes(this.search.toLowerCase()))
                                                );
                                            },
                                            selectZone(id) {
                                                this.selectedId = id;
                                                this.open = false;
                                                this.search = '';
                                            },
                                            clearSelection() {
                                                this.selectedId = null;
                                                this.open = false;
                                                this.search = '';
                                            }
                                         }"
                                         @click.outside="open = false"
                                    >
                                        <!-- Trigger Button -->
                                        <div class="zone-select-trigger" 
                                             :class="{ 'active': open, 'has-value': selectedZone }" 
                                             @click="open = !open"
                                        >
                                            <div class="d-flex align-items-center justify-content-between w-100">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="zone-trigger-icon-circle">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                    </div>
                                                    <div class="text-start">
                                                        <div class="zone-trigger-label" x-text="selectedZone ? selectedZone.value : 'No Zone Associated'"></div>
                                                        <div class="zone-trigger-subtext" x-text="selectedZone ? 'Code: ' + selectedZone.key : 'All imported records will be general/global'"></div>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <template x-if="selectedZone">
                                                        <button type="button" class="btn-clear-zone" @click.stop="clearSelection()">
                                                            <i class="fas fa-times-circle"></i>
                                                        </button>
                                                    </template>
                                                    <i class="fas fa-chevron-down zone-chevron" :class="{ 'rotated': open }"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Dropdown Panel -->
                                        <div class="zone-dropdown-panel" 
                                             x-show="open" 
                                             x-transition:enter="transition ease-out duration-150"
                                             x-transition:enter-start="opacity-0 transform scale-95"
                                             x-transition:enter-end="opacity-100 transform scale-100"
                                             x-transition:leave="transition ease-in duration-100"
                                             x-transition:leave-start="opacity-100 transform scale-100"
                                             x-transition:leave-end="opacity-0 transform scale-95"
                                             style="display: none;"
                                        >
                                            <!-- Search Box -->
                                            <div class="zone-search-wrapper">
                                                <i class="fas fa-search zone-search-icon"></i>
                                                <input type="text" 
                                                       class="form-control zone-search-input" 
                                                       placeholder="Search zones by name or code..." 
                                                       x-model="search"
                                                       @click.prevent.stop
                                                >
                                                <template x-if="search">
                                                    <button type="button" class="zone-search-clear" @click.stop="search = ''">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </template>
                                            </div>

                                            <!-- Zones List -->
                                            <div class="zone-options-list">
                                                <!-- Global / No Zone option -->
                                                <div class="zone-option-item" 
                                                     :class="{ 'selected': !selectedId }" 
                                                     @click="clearSelection()"
                                                >
                                                    <div class="d-flex align-items-center justify-content-between w-100">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="zone-option-icon-circle bg-light-gray">
                                                                <i class="fas fa-globe text-muted"></i>
                                                            </div>
                                                            <div class="text-start">
                                                                <span class="zone-option-name fw-bold">No Zone Association</span>
                                                                <span class="zone-option-description d-block text-muted small">Import as global records (general)</span>
                                                            </div>
                                                        </div>
                                                        <template x-if="!selectedId">
                                                            <i class="fas fa-check text-primary"></i>
                                                        </template>
                                                    </div>
                                                </div>

                                                <!-- Loop options -->
                                                <template x-for="zone in filteredZones" :key="zone.id">
                                                    <div class="zone-option-item" 
                                                         :class="{ 'selected': selectedId == zone.id }" 
                                                         @click="selectZone(zone.id)"
                                                    >
                                                        <div class="d-flex align-items-center justify-content-between w-100">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="zone-option-icon-circle">
                                                                    <i class="fas fa-map-marked-alt text-primary"></i>
                                                                </div>
                                                                <div class="text-start">
                                                                    <span class="zone-option-name fw-bold" x-text="zone.value"></span>
                                                                    <span class="zone-option-code-pill" x-text="zone.key"></span>
                                                                </div>
                                                            </div>
                                                            <template x-if="selectedId == zone.id">
                                                                <i class="fas fa-check text-primary"></i>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>

                                                <!-- No results -->
                                                <template x-if="filteredZones.length === 0">
                                                    <div class="zone-no-results text-center py-3 text-muted">
                                                        <i class="fas fa-exclamation-circle mb-2 d-block" style="font-size: 1.25rem;"></i>
                                                        No zones found matching "<span x-text="search"></span>"
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-text text-muted">
                                        If selected, all records in this import batch will be tied to this Zone.
                                    </div>
                                    @error('selectedZoneId')
                                        <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif

                            <div class="mb-4 text-start">
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

                            @if (!empty($importResults['purge_summary']))
                                <div class="alert alert-warning mb-4">
                                    <strong>Pre-import purge summary:</strong>
                                    <ul class="mb-0 small">
                                        @foreach ($importResults['purge_summary'] as $label => $count)
                                            <li><strong>{{ str_replace('_', ' ', $label) }}:</strong> {{ $count }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Warning Details -->
                            @if (!empty($importResults['warnings']))
                                <div class="card border-warning mb-4">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0">Import Warnings</h6>
                                    </div>
                                    <div class="card-body">
                                        @foreach ($importResults['warnings'] as $warning)
                                            <div class="mb-3 pb-3 border-bottom">
                                                <strong class="text-warning">{{ $warning['message'] }}</strong>
                                                <p class="text-muted small mb-0">
                                                    Affected rows: {{ implode(', ', $warning['rows']) }}
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
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
                            @elseif (($importResults['imported_rows'] ?? 0) > 0)
                                <div class="alert alert-success mb-4">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>Success!</strong> All data was imported successfully with no errors.
                                </div>
                            @else
                                <div class="alert alert-warning mb-4">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>No rows imported.</strong>
                                    The file was processed but no matching data rows were found.
                                    Check that column headers and required values match the selected form
                                    (e.g. Sample Type, Analysis Type, Lab), and that Labs already exist.
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
        background-color: var(--color-primary);
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

    /* ── Premium Custom Zone Selector ────────────────────────── */
    .custom-zone-selector-wrapper {
        position: relative;
        width: 100%;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .zone-select-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 10px 14px;
        background: #ffffff;
        border: 1.5px solid #d1d7e0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .zone-select-trigger:hover {
        border-color: var(--color-primary);
        background: #f8fafc;
     }
 
     .zone-select-trigger.active {
        border-color: var(--color-primary);
        background: #ffffff;
        box-shadow: 0 0 0 0.2rem var(--color-primary-highlight);
     }

    .zone-select-trigger.has-value {
        border-color: #28a745;
        background: #f4fbf7;
    }

    .zone-select-trigger.has-value:hover {
        background: #eafcf1;
        border-color: #218838;
    }

    .zone-trigger-icon-circle {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: #f1f5f9;
        color: #64748b;
        border-radius: 6px;
        font-size: 1rem;
        transition: all 0.2s ease;
    }

    .zone-select-trigger.has-value .zone-trigger-icon-circle {
        background: #d4edda;
        color: #28a745;
    }

    .zone-trigger-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #212529;
        line-height: 1.2;
    }

    .zone-trigger-subtext {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 1px;
        line-height: 1.2;
    }

    .zone-select-trigger.has-value .zone-trigger-subtext {
        color: #28a745;
        font-weight: 500;
    }

    .btn-clear-zone {
        background: transparent;
        border: none;
        color: #6c757d;
        padding: 2px;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: color 0.15s, transform 0.15s;
    }

    .btn-clear-zone:hover {
        color: #dc3545;
        transform: scale(1.15);
    }

    .zone-chevron {
        color: #6c757d;
        font-size: 0.8rem;
        transition: transform 0.2s ease;
    }

    .zone-chevron.rotated {
        transform: rotate(180deg);
        color: var(--color-primary);
    }

    /* Dropdown Panel */
    .zone-dropdown-panel {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        background: #ffffff;
        border: 1.5px solid #d1d7e0;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        z-index: 1050;
        overflow: hidden;
        padding: 6px;
    }

    /* Search Wrapper */
    .zone-search-wrapper {
        position: relative;
        margin-bottom: 6px;
    }

    .zone-search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        font-size: 0.85rem;
    }

    .zone-search-input {
        width: 100% !important;
        height: 36px !important;
        padding: 8px 30px 8px 32px !important;
        border: 1.5px solid #d1d7e0 !important;
        border-radius: 6px !important;
        font-size: 0.85rem !important;
        background: #f8fafc !important;
        color: #212529 !important;
        transition: all 0.15s ease !important;
    }

    .zone-search-input:focus {
        border-color: var(--color-primary) !important;
        background: #ffffff !important;
        box-shadow: none !important;
        outline: none !important;
    }

    .zone-search-clear {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: #6c757d;
        cursor: pointer;
        font-size: 0.85rem;
    }

    .zone-search-clear:hover {
        color: #212529;
    }

    /* Options List */
    .zone-options-list {
        max-height: 200px;
        overflow-y: auto;
        padding-right: 2px;
    }

    .zone-options-list::-webkit-scrollbar {
        width: 5px;
    }
    .zone-options-list::-webkit-scrollbar-track {
        background: transparent;
    }
    .zone-options-list::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 99px;
    }
    .zone-options-list::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .zone-option-item {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 6px;
        cursor: pointer;
        margin-bottom: 2px;
        transition: all 0.15s ease;
    }

    .zone-option-item:last-child {
        margin-bottom: 0;
    }

    .zone-option-item:hover {
        background: #f1f5f9;
    }

    .zone-option-item.selected {
        background: #e7f1ff;
    }

    .zone-option-icon-circle {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        background: #e7f1ff;
        border-radius: 6px;
        font-size: 0.85rem;
    }

    .zone-option-icon-circle.bg-light-gray {
        background: #f1f5f9;
    }

    .zone-option-name {
        font-size: 0.85rem;
        color: #212529;
    }

    .zone-option-code-pill {
        display: inline-block;
        padding: 1px 6px;
        background: #e2e8f0;
        color: #475569;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-left: 6px;
        text-transform: uppercase;
    }

    .zone-option-item.selected .zone-option-code-pill {
        background: #cbd5e1;
        color: #1e293b;
    }

    .zone-no-results {
        padding: 15px 10px;
        font-size: 0.8rem;
    }
</style>
</div>
