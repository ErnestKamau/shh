<div class="qc-page qc-config-page">
    @include('livewire.qc._shared-styles')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0"><i class="mdi mdi-file-certificate-outline mr-1"></i> QC Configurations</h5>
            <small class="text-muted">Manage standards, types, schemes, and approvers in one place.</small>
        </div>
        <div class="qc-config-search-wrap">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" placeholder="Search name or code...">
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    <ul class="nav nav-pills mb-3 qc-config-tabs" role="tablist">
        <li class="nav-item"><button class="nav-link {{ $activeTab === 'standards' ? 'active' : '' }}" wire:click="setTab('standards')" type="button">Standards</button></li>
        <li class="nav-item"><button class="nav-link {{ $activeTab === 'types' ? 'active' : '' }}" wire:click="setTab('types')" type="button">QC Types</button></li>
        <li class="nav-item"><button class="nav-link {{ $activeTab === 'schemes' ? 'active' : '' }}" wire:click="setTab('schemes')" type="button">QC Schemes</button></li>
        <li class="nav-item"><button class="nav-link {{ $activeTab === 'approvals' ? 'active' : '' }}" wire:click="setTab('approvals')" type="button">Approvers</button></li>
    </ul>

    <div class="card qc-table-card">
        <div class="card-body">
            @if ($activeTab === 'standards')
                <form wire:submit.prevent="saveStandard" class="mb-4 border rounded p-3 qc-inline-form">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>{{ $editingStandardId ? 'Edit QC Standard' : 'Add QC Standard' }}</strong>
                        @if($editingStandardId)
                            <button type="button" class="btn btn-sm btn-light" wire:click="resetStandardForm">Cancel</button>
                        @endif
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Name</label>
                            <input type="text" wire:model="standardName" class="form-control form-control-sm @error('standardName') is-invalid @enderror">
                            @error('standardName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-2">
                            <label>Code</label>
                            <input type="text" wire:model="standardCode" class="form-control form-control-sm @error('standardCode') is-invalid @enderror">
                            @error('standardCode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label>QC Type</label>
                            <select wire:model="standardQcTypeId" class="form-control form-control-sm @error('standardQcTypeId') is-invalid @enderror">
                                <option value="">Select type...</option>
                                @foreach($qcTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('standardQcTypeId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label>QC Schemes</label>
                            <select wire:model="standardSchemeIds" multiple class="form-control form-control-sm @error('standardSchemeIds') is-invalid @enderror" style="min-height: 90px;">
                                @foreach($qcSchemes as $scheme)
                                    <option value="{{ $scheme->id }}">{{ $scheme->name }}</option>
                                @endforeach
                            </select>
                            @error('standardSchemeIds') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" wire:model="standardIsActive" id="standardIsActive">
                        <label class="form-check-label" for="standardIsActive">Active</label>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Save Standard</button>
                </form>

                <div class="table-responsive qc-table-wrap">
                    <table class="table table-sm table-bordered table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 160px;">Actions</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>QC Type</th>
                                <th>Schemes</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($standards as $standard)
                                <tr>
                                    <td>
                                        <button class="btn btn-sm btn-light" wire:click="editStandard('{{ $standard->id }}')">Edit</button>
                                        <a class="btn btn-sm btn-light" href="{{ route('qc_StandardShow', ['id' => $standard->id]) }}">Analytes</a>
                                        <button class="btn btn-sm btn-outline-danger" wire:click="deactivateStandard('{{ $standard->id }}')">Deactivate</button>
                                    </td>
                                    <td>{{ $standard->code }}</td>
                                    <td>{{ $standard->name }}</td>
                                    <td>{{ optional($standard->getQcType())->name ?? '-' }}</td>
                                    <td>{{ $standard->qcschemenames }}</td>
                                    <td>{!! $standard->status ? '<span class="text-success">Active</span>' : '<span class="text-muted">Inactive</span>' !!}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">No QC standards found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($activeTab === 'types')
                <form wire:submit.prevent="saveQcType" class="mb-4 border rounded p-3 qc-inline-form">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>{{ $editingQcTypeId ? 'Edit QC Type' : 'Add QC Type' }}</strong>
                        @if($editingQcTypeId)
                            <button type="button" class="btn btn-sm btn-light" wire:click="resetQcTypeForm">Cancel</button>
                        @endif
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Name</label>
                            <input type="text" wire:model="qcTypeName" class="form-control form-control-sm @error('qcTypeName') is-invalid @enderror">
                            @error('qcTypeName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label>Code</label>
                            <input type="text" wire:model="qcTypeCode" class="form-control form-control-sm @error('qcTypeCode') is-invalid @enderror">
                            @error('qcTypeCode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3"><div class="form-check"><input class="form-check-input" id="qcTypeHasStandards" type="checkbox" wire:model="qcTypeHasStandards"><label class="form-check-label" for="qcTypeHasStandards">Has Standards</label></div></div>
                        <div class="form-group col-md-3"><div class="form-check"><input class="form-check-input" id="qcTypeHasConfiguredSamples" type="checkbox" wire:model="qcTypeHasConfiguredSamples"><label class="form-check-label" for="qcTypeHasConfiguredSamples">Has Configured Samples</label></div></div>
                        <div class="form-group col-md-3"><div class="form-check"><input class="form-check-input" id="qcTypeUseExistingSample" type="checkbox" wire:model="qcTypeUseExistingSample"><label class="form-check-label" for="qcTypeUseExistingSample">Use Existing Sample</label></div></div>
                        <div class="form-group col-md-3"><div class="form-check"><input class="form-check-input" id="qcTypeIsActive" type="checkbox" wire:model="qcTypeIsActive"><label class="form-check-label" for="qcTypeIsActive">Active</label></div></div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Save QC Type</button>
                </form>

                <div class="table-responsive qc-table-wrap">
                    <table class="table table-sm table-bordered table-hover mb-0">
                        <thead class="thead-light"><tr><th style="width: 140px;">Actions</th><th>Name</th><th>Code</th><th>Has Standards</th><th>Configured Samples</th><th>Use Existing</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($qcTypes as $type)
                                <tr>
                                    <td>
                                        <button class="btn btn-sm btn-light" wire:click="editQcType('{{ $type->id }}')">Edit</button>
                                        <button class="btn btn-sm btn-outline-danger" wire:click="deactivateQcType('{{ $type->id }}')">Deactivate</button>
                                    </td>
                                    <td>{{ $type->name }}</td>
                                    <td>{{ $type->code }}</td>
                                    <td>{{ $type->has_standards ? 'Yes' : 'No' }}</td>
                                    <td>{{ $type->has_configured_samples ? 'Yes' : 'No' }}</td>
                                    <td>{{ $type->use_existing_sample ? 'Yes' : 'No' }}</td>
                                    <td>{{ $type->is_active ? 'Active' : 'Inactive' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">No QC types found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($activeTab === 'schemes')
                <form wire:submit.prevent="saveScheme" class="mb-4 border rounded p-3 qc-inline-form">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>{{ $editingSchemeId ? 'Edit QC Scheme' : 'Add QC Scheme' }}</strong>
                        @if($editingSchemeId)
                            <button type="button" class="btn btn-sm btn-light" wire:click="resetSchemeForm">Cancel</button>
                        @endif
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Name</label>
                            <input type="text" wire:model="schemeName" class="form-control form-control-sm @error('schemeName') is-invalid @enderror">
                            @error('schemeName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label>Code</label>
                            <input type="text" wire:model="schemeCode" class="form-control form-control-sm @error('schemeCode') is-invalid @enderror">
                            @error('schemeCode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group col-md-3 d-flex align-items-end">
                            <div class="form-check mb-2"><input class="form-check-input" type="checkbox" wire:model="schemeIsActive" id="schemeIsActive"><label class="form-check-label" for="schemeIsActive">Active</label></div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Save Scheme</button>
                </form>

                <div class="table-responsive qc-table-wrap">
                    <table class="table table-sm table-bordered table-hover mb-0">
                        <thead class="thead-light"><tr><th style="width: 140px;">Actions</th><th>Code</th><th>Name</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($qcSchemes as $scheme)
                                <tr>
                                    <td>
                                        <button class="btn btn-sm btn-light" wire:click="editScheme('{{ $scheme->id }}')">Edit</button>
                                        <button class="btn btn-sm btn-outline-danger" wire:click="deleteScheme('{{ $scheme->id }}')">Delete</button>
                                    </td>
                                    <td>{{ $scheme->code }}</td>
                                    <td>{{ $scheme->name }}</td>
                                    <td>{{ $scheme->is_active ? 'Active' : 'Inactive' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No QC schemes found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($activeTab === 'approvals')
                <form wire:submit.prevent="saveApprover" class="mb-4 border rounded p-3 qc-inline-form">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>{{ $editingApproverId ? 'Edit Approver' : 'Add Approver' }}</strong>
                        @if($editingApproverId)
                            <button type="button" class="btn btn-sm btn-light" wire:click="resetApproverForm">Cancel</button>
                        @endif
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Personnel</label>
                            <select wire:model="approverPersonnelId" class="form-control form-control-sm @error('approverPersonnelId') is-invalid @enderror">
                                <option value="">Select user...</option>
                                @foreach($staffs as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                @endforeach
                            </select>
                            @error('approverPersonnelId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Save Approver</button>
                </form>

                <div class="table-responsive qc-table-wrap">
                    <table class="table table-sm table-bordered table-hover mb-0">
                        <thead class="thead-light"><tr><th style="width: 140px;">Actions</th><th>Name</th><th>Created By</th></tr></thead>
                        <tbody>
                            @forelse($approvals as $approval)
                                <tr>
                                    <td>
                                        <button class="btn btn-sm btn-light" wire:click="editApprover('{{ $approval->id }}')">Edit</button>
                                        <button class="btn btn-sm btn-outline-danger" wire:click="deleteApprover('{{ $approval->id }}')">Delete</button>
                                    </td>
                                    <td>{{ $approval->name }}</td>
                                    <td>{{ $approval->creator }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">No approvers configured.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
