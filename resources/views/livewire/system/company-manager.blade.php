<div class="container-fluid px-0">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-domain text-primary"></i>
                                {{ __('system.registered_companies') }}
                            </h2>
                            <p class="text-muted mb-0">{{ __('system.manage_companies_profile') }}</p>
                        </div>
                        @can('system.companies.add')
                            <button wire:click="openCreateModal" class="btn btn-primary">
                                <i class="mdi mdi-plus"></i> {{ __('system.add_company') }}
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session()->has('success') || session()->has('error'))
        <div class="alert alert-{{ session()->has('success') ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ session('success') ?? session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="mb-0 text-muted">
                        <i class="mdi mdi-filter-variant"></i> {{ __('system.filter_options') }}
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('system.search') }}</label>
                                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="{{ __('system.search_by_country_or_company_id') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('system.status') }}</label>
                                <select wire:model.live="statusFilter" class="form-control">
                                    <option value="">{{ __('system.all_status') }}</option>
                                    <option value="1">{{ __('system.active') }}</option>
                                    <option value="0">{{ __('system.inactive') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('system.per_page') }}</label>
                                <select wire:model.live="perPage" class="form-control">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">&nbsp;</label>
                                <button wire:click="clearFilters" class="btn btn-outline-secondary w-100">
                                    <i class="mdi mdi-refresh"></i> {{ __('system.clear') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">{{ __('system.companies') }}</h5>
                </div>
                <div class="card-body">
                    @if($this->companies->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead style="background-color: rgba(0, 0, 0, .03);">
                                    <tr>
                                        <th>{{ __('system.actions') }}</th>
                                        <th>{{ __('system.logo') }}</th>
                                        <th>{{ __('system.name') }}</th>
                                        <th>{{ __('system.email') }}</th>
                                        <th>{{ __('system.telephone') }}</th>
                                        <th>{{ __('system.country') }}</th>
                                        <th>{{ __('system.company_location') }}</th>
                                        <th>{{ __('system.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($this->companies as $company)
                                        <tr>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @can('system.companies.edit')
                                                        <button wire:click="openEditModal('{{ $company->id }}')" class="btn btn-sm btn-outline-warning mr-1" title="{{ __('system.edit') }}">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button wire:click="openStatusModal('{{ $company->id }}')" class="btn btn-sm btn-outline-primary mr-1" title="{{ __('system.activate') }}">
                                                            <i class="mdi mdi-refresh"></i>
                                                        </button>
                                                    @endcan
                                                </div>
                                            </td>
                                            <td>
                                                @if($company->logo)
                                                    <img src="{{ $company->logo }}" alt="{{ $company->name }}" style="width:44px;height:44px;object-fit:cover;border-radius:10px;">
                                                @else
                                                    <span class="badge badge-light">{{ __('system.unavailable') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $company->name }}</strong>
                                                @if($company->show_on_reports)
                                                    <br><small><span class="badge badge-info">{{ __('system.report_logo_enabled') }}</span></small>
                                                @endif
                                            </td>
                                            <td>{{ $company->email ?: '-' }}</td>
                                            <td>{{ $company->telephone ?: '-' }}</td>
                                            <td>{{ $company->country_name ?: '-' }}</td>
                                            <td>{{ $company->location ?: '-' }}</td>
                                            <td>
                                                <span class="badge badge-{{ (int) $company->active === 1 ? 'success' : 'secondary' }}">
                                                    {{ (int) $company->active === 1 ? __('system.active') : __('system.inactive') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            {{ $this->companies->links('pagination::bootstrap-4') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-domain text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3">{{ __('system.no_companies_added_yet') }}</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($showCompanyModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-{{ $editingCompanyId ? 'pencil' : 'plus' }}"></i>
                            {{ $editingCompanyId ? __('system.edit_company') : __('system.add_company') }}
                        </h5>
                        <button type="button" class="close" wire:click="closeCompanyModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveCompany">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('system.company_name') }} <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('system.company_country') }}</label>
                                        <select wire:model="country_id" class="form-control @error('country_id') is-invalid @enderror">
                                            <option value="">{{ __('system.select_country') }}</option>
                                            @foreach($this->countries as $country)
                                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('country_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('system.company_logo') }}</label>
                                        <input type="file" wire:model="logoFile" class="form-control @error('logoFile') is-invalid @enderror">
                                        @error('logoFile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        @if($existingLogo && !$logoFile)
                                            <small class="text-muted d-block mt-1">{{ __('system.current_label') }}:</small>
                                            <img src="{{ $existingLogo }}" alt="Current logo" style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('system.report_logo') }}</label>
                                        <input type="file" wire:model="reportLogoFile" class="form-control @error('reportLogoFile') is-invalid @enderror">
                                        @error('reportLogoFile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        @if($existingReportLogo && !$reportLogoFile)
                                            <small class="text-muted d-block mt-1">{{ __('system.current_label') }}:</small>
                                            <img src="{{ $existingReportLogo }}" alt="Current report logo" style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('system.email') }}</label>
                                        <input type="text" wire:model="email" class="form-control @error('email') is-invalid @enderror">
                                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('system.company_website') }}</label>
                                        <input type="text" wire:model="website" class="form-control @error('website') is-invalid @enderror">
                                        @error('website') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('system.telephone') }}</label>
                                        <input type="text" wire:model="telephone" class="form-control @error('telephone') is-invalid @enderror">
                                        @error('telephone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('system.cell_phone') }}</label>
                                        <input type="text" wire:model="cell_phone" class="form-control @error('cell_phone') is-invalid @enderror">
                                        @error('cell_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('system.fax') }}</label>
                                        <input type="text" wire:model="fax" class="form-control @error('fax') is-invalid @enderror">
                                        @error('fax') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('system.company_location') }}</label>
                                        <input type="text" wire:model="location" class="form-control @error('location') is-invalid @enderror">
                                        @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">{{ __('system.street') }}</label>
                                        <input type="text" wire:model="street" class="form-control @error('street') is-invalid @enderror">
                                        @error('street') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">{{ __('system.company_postal_address') }}</label>
                                <textarea wire:model="address" rows="3" class="form-control @error('address') is-invalid @enderror"></textarea>
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-light mr-2" wire:click="closeCompanyModal">{{ __('system.close') }}</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="mdi mdi-content-save"></i> {{ __('system.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showStatusModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-refresh"></i>
                            {{ __('system.activate') }} {{ $statusCompanyName }}
                        </h5>
                        <button type="button" class="close" wire:click="closeStatusModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-2">
                            <label class="mb-0">
                                <input type="checkbox" wire:model="setDefault"> {{ __('system.set_company_default') }}
                            </label>
                        </div>
                        <div class="form-group mb-0">
                            <label class="mb-0">
                                <input type="checkbox" wire:model="showOnReports"> {{ __('system.show_company_logo_on_reports') }}
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="closeStatusModal">{{ __('system.close') }}</button>
                        <button type="button" class="btn btn-primary" wire:click="applyStatus">
                            <i class="mdi mdi-content-save"></i> {{ __('system.save') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>