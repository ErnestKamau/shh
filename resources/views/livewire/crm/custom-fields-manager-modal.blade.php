<div>
    <!-- Modal -->
    <div wire:ignore.self class="modal fade" id="customFieldsManagerModal" tabindex="-1" role="dialog" aria-labelledby="customFieldsManagerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customFieldsManagerModalLabel">
                        {{ __('crm.manage_custom_fields') }} <br>
                        <small class="text-muted">{{ __('crm.type') }}: <strong>{{ $sampleTypeName }}</strong></small>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                @if($customerId && $sampleTypeId)
                    <div class="modal-body p-0">
                        <ul class="crm-tab-nav crm-tab-nav-justified" id="customFieldsTab" role="tablist">
                            <li class="crm-tab-item">
                                <a class="crm-tab-link {{ $activeTab === 'existing' ? 'active' : '' }}" wire:click.prevent="switchTab('existing')" href="#">
                                    <i class="mdi mdi-format-list-checks"></i> {{ __('crm.choose_existing_fields') }}
                                </a>
                            </li>
                            <li class="crm-tab-item">
                                <a class="crm-tab-link {{ $activeTab === 'create' ? 'active' : '' }}" wire:click.prevent="switchTab('create')" href="#">
                                    <i class="mdi mdi-plus-circle-outline"></i> {{ __('crm.create_new_field') }}
                                </a>
                            </li>
                        </ul>
                        
                        <div class="p-3">
                            <!-- Loading State -->
                            <div wire:loading wire:target="loadFields, attachField, detachField, createField, switchTab" class="w-100 text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">{{ __('crm.loading') }}...</span>
                                </div>
                                <p class="mt-2 text-muted">{{ __('crm.processing') }}...</p>
                            </div>

                            <div wire:loading.remove wire:target="loadFields, attachField, detachField, createField, switchTab">
                                
                                @if(!$hasCategory)
                                    <div class="alert alert-info border-info mb-4">
                                        <strong><i class="mdi mdi-information-outline"></i> {{ __('crm.no_custom_field_category_exists') }}</strong>
                                        <p class="mb-2 mt-1 small">
                                            {{ __('crm.custom_field_category_missing_help_line_1') }}
                                            {{ __('crm.custom_field_category_missing_help_line_2') }}
                                            {{ __('crm.custom_field_category_missing_help_line_3') }}
                                        </p>
                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold">{{ __('crm.category_name') }} <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('newCategoryName') is-invalid @enderror" wire:model.defer="newCategoryName" placeholder="{{ __('crm.custom_fields_category_example') }}">
                                            @error('newCategoryName') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endif

                                {{-- TAB: EXISTING FIELDS --}}
                                @if($activeTab === 'existing')
                                    <div class="mb-4">
                                        <h6 class="text-success border-bottom pb-2">{{ __('crm.currently_attached_fields') }}</h6>
                                        @if(empty($existingFields))
                                            <p class="text-muted small">{{ __('crm.no_custom_fields_attached_for_selection') }}</p>
                                        @else
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th>{{ __('crm.label') }}</th>
                                                            <th>{{ __('crm.type') }}</th>
                                                            <th width="100" class="text-center">{{ __('crm.action') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($existingFields as $field)
                                                            <tr>
                                                                <td>{{ $field['label'] }}</td>
                                                                <td>{{ ucfirst(str_replace('_', ' ', $field['type'])) }}</td>
                                                                <td class="text-center">
                                                                    <button class="btn btn-sm btn-outline-danger py-0" wire:click="detachField({{ $field['id'] }})" title="{{ __('crm.remove') }}">
                                                                        <i class="mdi mdi-minus-circle"></i> {{ __('crm.remove') }}
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>

                                    <div>
                                        <h6 class="text-primary border-bottom pb-2">{{ __('crm.available_fields_to_attach') }}</h6>
                                        @if(empty($availableFields))
                                            <p class="text-muted small">{{ __('crm.no_other_custom_fields_available') }}</p>
                                        @else
                                            <div style="max-height: 250px; overflow-y: auto;">
                                                <table class="table table-sm table-bordered table-hover">
                                                    <thead class="bg-light sticky-top">
                                                        <tr>
                                                            <th>{{ __('crm.label') }}</th>
                                                            <th>{{ __('crm.type') }}</th>
                                                            <th width="100" class="text-center">{{ __('crm.action') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($availableFields as $field)
                                                            <tr>
                                                                <td>{{ $field['label'] }}</td>
                                                                <td>{{ ucfirst(str_replace('_', ' ', $field['type'])) }}</td>
                                                                <td class="text-center">
                                                                    <button class="btn btn-sm btn-outline-primary py-0" wire:click="attachField('{{ $field['id'] }}')" title="{{ __('crm.attach_to_sample_type') }}">
                                                                        <i class="mdi mdi-plus-circle"></i> {{ __('crm.add') }}
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                {{-- TAB: CREATE NEW FIELD --}}
                                @if($activeTab === 'create')
                                    <div class="bg-light p-3 rounded border">
                                        <h6 class="text-primary mb-3"><i class="mdi mdi-plus-box"></i> {{ __('crm.define_new_custom_field') }}</h6>
                                        <p class="small text-muted mb-3">
                                            {{ __('crm.create_custom_field_help_line_1') }}
                                            {{ __('crm.create_custom_field_help_line_2') }}
                                        </p>

                                        <form wire:submit.prevent="createField">
                                            <div class="form-group">
                                                <label class="font-weight-bold">{{ __('crm.field_label') }} <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('newFieldLabel') is-invalid @enderror" wire:model.defer="newFieldLabel" placeholder="{{ __('crm.field_label_example') }}">
                                                @error('newFieldLabel') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                            </div>

                                            <div class="form-group">
                                                <label class="font-weight-bold">{{ __('crm.field_type') }} <span class="text-danger">*</span></label>
                                                <select class="form-control @error('newFieldType') is-invalid @enderror" wire:model.defer="newFieldType">
                                                    <option value="text">{{ __('crm.field_type_short_text') }}</option>
                                                    <option value="textarea">{{ __('crm.field_type_long_text') }}</option>
                                                    <option value="number">{{ __('crm.field_type_numeric') }}</option>
                                                    <option value="date_picker">{{ __('crm.field_type_date') }}</option>
                                                    <option value="checkbox">{{ __('crm.field_type_checkbox') }}</option>
                                                </select>
                                                @error('newFieldType') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div class="form-group">
                                                <label class="font-weight-bold">{{ __('crm.help_text_default_placeholder') }}</label>
                                                <input type="text" class="form-control" wire:model.defer="newFieldDefaultValue" placeholder="{{ __('crm.optional_input_hint') }}">
                                            </div>

                                            <div class="text-right mt-4">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="mdi mdi-check"></i> {{ __('crm.create_attach_field') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                @else
                    <div class="modal-body">
                        <div class="alert alert-warning mb-0">
                            <strong><i class="mdi mdi-alert"></i> {{ __('crm.missing_information') }}:</strong> {{ __('crm.select_client_before_custom_fields') }}
                        </div>
                    </div>
                @endif
                
                <div class="modal-footer bg-light p-2">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('crm.close') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
