<div x-data x-on:open-preview-modal.window="$('#certificate-preview-modal').modal('show')">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:28px;height:28px;background:#e8f4fd;">
                <i class="mdi mdi-file-certificate-outline text-info" style="font-size:1rem;"></i>
            </span>
            <div>
                <small class="font-weight-bold text-dark" style="font-size:0.82rem;">{{ __('crm.certification_compliance_register') }}</small>
                <small class="text-muted d-block" style="font-size:0.67rem;">{{ __('crm.certification_compliance_subtitle') }}</small>
            </div>
        </div>
        <div class="d-flex justify-content-end align-items-center">
            <div class="crm-search-wrapper mr-2">
                <i class="mdi mdi-magnify crm-search-icon"></i>
                <input type="text" class="form-control" placeholder="{{ __('crm.search_certifications') }}"
                    wire:model.live.debounce.300ms="search">
            </div>
            <!-- Show Entries -->
            <div class="d-flex align-items-center mb-2 mb-md-0 mr-3 flex-shrink-0">
                <label class="mb-0 mr-2 crm-filter-label text-nowrap">{{ __('crm.show') }}</label>
                <select wire:model.live="perPage" wire:key="per-page-select" class="custom-select custom-select-sm no-select2" style="width: 70px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <label class="mb-0 ml-2 crm-filter-label text-nowrap">{{ __('crm.entries') }}</label>
            </div>
            <button class="btn btn-outline-success btn-sm mr-2 text-nowrap" wire:click="exportToExcel">
                <i class="mdi mdi-file-excel"></i> {{ __('crm.export_to_excel') }}
            </button>
            <button class="btn btn-add btn-sm" wire:click="openCertificationForm">
                <i class="mdi mdi-plus"></i> {{ __('crm.add') }}
            </button>
        </div>
    </div>

    <div wire:loading wire:target="search,perPage" class="crm-loading-indicator"><i
            class="mdi mdi-loading mdi-spin"></i> {{ __('crm.loading') }}...</div>
    <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
        <x-slot:header>
            <tr>
                <th style="min-width: 140px;">{{ __('crm.actions') }}</th>
                <th nowrap>{{ __('crm.certification_name') }}</th>
                <th nowrap>{{ __('crm.document') }}</th>
                <th>{{ __('crm.accreditation_date') }}</th>
                <th>{{ __('crm.expiry_date') }}</th>
                <th nowrap>{{ __('crm.issuing_body') }}</th>
                <th nowrap>{{ __('crm.validity') }}</th>
                <th nowrap>{{ __('crm.last_updated_by') }}</th>
            </tr>
        </x-slot:header>
                    @forelse($certifications as $item)
                        <tr>
                            <td>
                                <x-crm.action-buttons>
                                    <button type="button" class="btn crm-btn crm-btn-edit btn-sm"
                                        wire:click="openCertificationForm({{ $item->id }})">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                    <button type="button" class="btn crm-btn crm-btn-delete btn-sm"
                                        wire:click="deleteCertification({{ $item->id }})"
                                        wire:confirm="{{ __('crm.delete_certification_confirm') }}">
                                        <i class="mdi mdi-trash-can-outline"></i>
                                    </button>
                                    <button type="button" class="btn crm-btn crm-btn-view btn-sm"
                                        wire:click.prevent="previewCertificate({{ $item->id }})">
                                        <i class="mdi mdi-eye-outline"></i>
                                    </button>
                                </x-crm.action-buttons>
                            </td>
                            <td>{{ $item->name }}</td>
                            <td style="text-align: center;"><a href="{{ asset($item->certificate) }}"
                                    class="btn btn-sm btn-transparent" target="_blank" download><i
                                        class="mdi mdi-download text-success"></i></a></td>
                            <td><small>{{$item->certification_date}}</small></td>
                            <td><small>{{$item->expire_date}}</small></td>
                            <td>{{$item->certification_body}}</td>
                            <td class="text-small">
                                @php
                                    $expiry = $item->expire_date ? \Carbon\Carbon::parse($item->expire_date) : null;
                                    $daysLeft = $expiry ? now()->diffInDays($expiry, false) : null;
                                @endphp
                                @if($item->status == 0)
                                    @if($daysLeft !== null && $daysLeft <= 30 && $daysLeft > 0)
                                        <span class="crm-badge crm-badge-warning">{{ __('crm.expiring_soon') }}</span>
                                    @elseif($daysLeft !== null && $daysLeft <= 0)
                                        <span class="crm-badge crm-badge-danger">{{ __('crm.expired') }}</span>
                                    @else
                                        <span class="crm-badge crm-badge-success">{{ __('crm.valid') }}</span>
                                    @endif
                                @else
                                    <span class="crm-badge crm-badge-neutral">{{ __('crm.inactive') }}</span>
                                @endif
                            </td>
                            <td>{!! $item->edited == '' ? 'N/a' : $item->edited !!}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-crm.empty-state
                                    icon="mdi-file-certificate-outline"
                                    :message="__('crm.no_certifications_for_client')"
                                />
                            </td>
                        </tr>
                    @endforelse
    </x-crm.data-table>

    <x-crm.pagination :summary="'Showing ' . ($certifications->firstItem() ?? 0) . ' to ' . ($certifications->lastItem() ?? 0) . ' of ' . $certifications->total() . ' results'">
        {{ $certifications->links() }}
    </x-crm.pagination>

    @if($showForm)
        @livewire(\App\Livewire\CRM\Customer\CertificationForm::class, [
            'customerId' => $customer->id,
            'certificationId' => $editingCertification ? $editingCertification->id : null
        ], 'certification-form-' . ($editingCertification ? $editingCertification->id : 'new'))
      @endif
 


     
    <!-- Document Preview Modal -->
    <template x-teleport="body">
        <div id="certificate-preview-modal" class="modal fade" x-on:click.self=" $wire.closePreview(); $('#certificate-preview-modal').modal('hide')"
            tabindex="-1" role="dialog" wire:ignore.self>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-file-document-outline mr-1"></i> {{ __('crm.preview') }}: {{ $previewTitle }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" wire:click="closePreview">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0" style="background-color: #f8f9fa;">
                        @if($previewUrl)
                            <iframe src="{{ $previewUrl }}" width="100%" height="600px" style="border:none;" title="{{ __('crm.certificate_preview') }}"></iframe>
                        @else
                            <div class="text-center p-5">
                                <i class="mdi mdi-loading mdi-spin text-primary" style="font-size: 2rem;"></i>
                                <p class="mt-2 text-muted">{{ __('crm.loading_document_preview') }}...</p>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" wire:click="closePreview">{{ __('crm.close_preview') }}</button>
                        @if($previewUrl)
                            <a href="{{ $previewUrl }}" class="btn btn-primary" target="_blank" download>
                                <i class="mdi mdi-download mr-1"></i> {{ __('crm.download_file') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>