@php
    $pageHeader = $pageHeader ?? [];
@endphp
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm border-0 integrity-header-card">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap: 12px;">
                    <div>
                        <h2 class="integrity-header-title">
                            <i class="mdi mdi-shield-check-outline"></i>
                            @if(! empty($pageHeader['request_number']))
                                {{ $pageHeader['request_number'] }}
                            @else
                                Sample Integrity Check
                            @endif
                        </h2>
                        <p class="integrity-header-sub">
                            @if(! empty($pageHeader['client_name']))
                                {{ $pageHeader['client_name'] }}
                            @endif
                            @if(! empty($pageHeader['client_name']) && ! empty($pageHeader['form_name']))
                                ·
                            @endif
                            @if(! empty($pageHeader['form_name']))
                                {{ $pageHeader['form_name'] }}
                            @endif
                        </p>
                        <div class="mt-2">
                            <span class="integrity-stage-chip">
                                <i class="mdi mdi-flask-outline" aria-hidden="true"></i>
                                Sample Integrity Check
                            </span>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center" style="gap: 8px;">
                        <button type="button"
                            class="btn btn-sm btn-integrity-primary"
                            wire:click="openAcceptConfirm"
                            @disabled(! $canAccept)
                            title="Accept and create job/samples">
                            <i class="mdi mdi-check-circle-outline"></i> Accept
                        </button>
                        <div class="nav-item dropdown integrity-actions-dropdown"
                             x-data="{ open: false }"
                             @click.outside="open = false">
                            <button type="button"
                                class="btn btn-sm btn-outline-secondary integrity-actions-dropdown__toggle dropdown-toggle"
                                @click.stop="open = !open"
                                :aria-expanded="open">
                                <i class="mdi mdi-dots-vertical"></i> Actions
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow integrity-actions-dropdown__menu"
                                 :class="{ 'show': open }"
                                 @click="if ($event.target.closest('.dropdown-item')) { open = false; }">
                                <button type="button"
                                    class="dropdown-item"
                                    wire:click="openWorksheetModal"
                                    wire:loading.attr="disabled"
                                    wire:target="openWorksheetModal,issueWorksheetPdf,issueWorksheetExcel,issueAndNotifyAnalysts">
                                    <i class="mdi mdi-file-document-multiple-outline mr-2"></i>
                                    Issue lab section worksheet…
                                </button>
                                <div class="dropdown-divider"></div>
                                <button type="button"
                                    class="dropdown-item"
                                    wire:click="generateWorksheetPdf"
                                    wire:loading.attr="disabled"
                                    wire:target="generateWorksheetPdf">
                                    <span wire:loading.remove wire:target="generateWorksheetPdf">
                                        <i class="mdi mdi-file-pdf-box mr-2"></i> Print combined worksheet PDF
                                    </span>
                                    <span wire:loading wire:target="generateWorksheetPdf">
                                        <i class="mdi mdi-loading mdi-spin mr-2"></i> Generating…
                                    </span>
                                </button>
                                <button type="button"
                                    class="dropdown-item"
                                    wire:click="downloadExcel"
                                    wire:loading.attr="disabled"
                                    wire:target="downloadExcel">
                                    <span wire:loading.remove wire:target="downloadExcel">
                                        <i class="mdi mdi-file-excel-outline mr-2"></i> Print combined worksheet Excel
                                    </span>
                                    <span wire:loading wire:target="downloadExcel">
                                        <i class="mdi mdi-loading mdi-spin mr-2"></i> Downloading…
                                    </span>
                                </button>
                                <div class="dropdown-divider"></div>
                                <a href="{{ $collectionLabelUrl }}" target="_blank" class="dropdown-item">
                                    <i class="mdi mdi-tag-outline mr-2"></i> All collection labels
                                </a>
                                <a href="{{ $registrationLabelUrl }}" target="_blank" class="dropdown-item">
                                    <i class="mdi mdi-barcode mr-2"></i> All lab sample labels
                                </a>
                                <div class="dropdown-divider"></div>
                                <button type="button"
                                    class="dropdown-item"
                                    wire:click="saveAssignments"
                                    wire:loading.attr="disabled"
                                    wire:target="saveAssignments">
                                    <span wire:loading.remove wire:target="saveAssignments">
                                        <i class="mdi mdi-content-save-outline mr-2"></i> Save assignments
                                    </span>
                                    <span wire:loading wire:target="saveAssignments">
                                        <i class="mdi mdi-loading mdi-spin mr-2"></i> Saving…
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
