<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-table-edit text-primary"></i>
                                {{ $reportFormat->report_name }} - Report Builder
                            </h2>
                            <p class="text-muted mb-0">Configure sections and global settings for this report format</p>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('livewire.report-formats') }}" class="btn btn-secondary me-2">
                                <i class="mdi mdi-arrow-left"></i> Back to Report Formats
                            </a>
                            <button wire:click="saveConfiguration" class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i> Save Configuration
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Alert -->
    @if (session()->has('message') || $message)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-{{ $messageType == 'success' ? 'success' : 'danger' }} alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius: 15px;">
                {{ session('message') ?? $message }}
                <button type="button" class="btn-close" wire:click="dismissMessage" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Left Column: Global Config & Details -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0 text-muted"><i class="mdi mdi-cog"></i> Global Options</h5>
                </div>
                <div class="card-body p-4">
                    <div class="form-group mb-3">
                        <label for="resultsDisplayType" class="form-label fw-bold">Results Display Type</label>
                        <select wire:model="resultsDisplayType" class="form-select modern-select no-select2" id="resultsDisplayType">
                            <option value="grid">Data Grid</option>
                            <option value="list">List View</option>
                            <option value="attachment_summary">Attachment Summary (Serology)</option>
                        </select>
                        @error('resultsDisplayType') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0 text-muted"><i class="mdi mdi-text"></i> Report Details (Text Content)</h5>
                </div>
                <div class="card-body p-4">
                    @foreach($availableDetailsKeys as $key => $label)
                    <div class="form-group mb-3">
                        <label for="detail_{{ $key }}" class="form-label fw-bold">{{ $label }}</label>
                        <textarea wire:model="details.{{ $key }}" class="form-control" id="detail_{{ $key }}" rows="3" placeholder="Enter {{ strtolower($label) }}..." style="border-radius: 10px;"></textarea>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Column: Sections Configuration -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0 text-muted"><i class="mdi mdi-view-list"></i> Sections Setup</h5>
                    <small class="text-muted">Order and toggle visibility</small>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" style="border-radius: 0 0 15px 15px;">
                        @foreach($sections as $name => $data)
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-white border-bottom p-3" wire:key="section-{{ $name }}">
                            <div class="d-flex align-items-center w-100">
                                <!-- Order Controls -->
                                <div class="me-3 d-flex flex-column">
                                    <button wire:click="moveSectionUp('{{ $name }}')" class="btn btn-sm btn-link p-0 text-muted" @if($data['order']==0) disabled @endif>
                                        <i class="mdi mdi-chevron-up" style="font-size: 1.5rem;"></i>
                                    </button>
                                    <button wire:click="moveSectionDown('{{ $name }}')" class="btn btn-sm btn-link p-0 text-muted" @if($data['order']==count($sections) - 1) disabled @endif>
                                        <i class="mdi mdi-chevron-down" style="font-size: 1.5rem;"></i>
                                    </button>
                                </div>

                                <!-- Section Content -->
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong class="text-dark fs-6">{{ $availableSections[$name] }}</strong>
                                        <div class="form-check form-switch ms-2">
                                            <input class="form-check-input" type="checkbox" id="section_visibility_{{ $name }}" wire:model="sections.{{ $name }}.is_visible">
                                            <label class="form-check-label small" for="section_visibility_{{ $name }}">Visible</label>
                                        </div>
                                    </div>
                                    <div>
                                        <input type="text" wire:model="sections.{{ $name }}.custom_title" class="form-control form-control-sm" placeholder="Custom Title (Optional)" style="border-radius: 8px;">
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Add matching Select CSS -->
    <style>
        .modern-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 500;
            color: #495057;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            position: relative;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%), url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: left center, right 12px center;
            background-repeat: no-repeat, no-repeat;
            background-size: 100% 100%, 16px 16px;
            padding-right: 40px;
        }

        .modern-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            background-color: #ffffff;
            outline: none;
        }

        .modern-select:hover {
            border-color: #007bff;
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.15);
        }
    </style>
</div>