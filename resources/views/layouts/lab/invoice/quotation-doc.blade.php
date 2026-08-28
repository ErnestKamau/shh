@extends('layouts.lab.layout.app', ['dataTable' => true, 'select2' => true])



@section('title2')
    <title> Lab-Invoice </title>

    @if(isset($branding))
        @include('billing.quotations.amspec.partials.fonts')
        @include('billing.quotations.amspec.partials.styles')
    @endif

    <style type="text/css">
        .tab-card {
            border: 1px solid #eee;
        }

        .tab-card-header {
            background: none;
        }

        /* Default mode */
        .tab-card-header>.nav-tabs {
            border: none;
            margin: 0px;
        }

        .tab-card-header>.nav-tabs>li {
            margin-right: 2px;
        }

        .tab-card-header>.nav-tabs>li>a {
            border: 0;
            border-bottom: 2px solid transparent;
            margin-right: 0;
            color: #737373;
            padding: 2px 15px;
        }

        .tab-card-header>.nav-tabs>li>a.show {
            border-bottom: 2px solid #007bff;
            color: #007bff;
        }

        .tab-card-header>.nav-tabs>li>a:hover {
            color: #007bff;
        }

        .tab-card .nav-link.active {
            background-color: #dadccd !important;
            border: 1px solid #cccebf !important;
        }

        .tab-card-header>.tab-content {
            padding-bottom: 0;
        }

        .my-small-text {
            font-size: 13px !important;
        }

        .removeThis {
            z-index: 12;
            position: absolute;
            cursor: pointer;
            top: 0px;
            right: 2px;
            padding: 1px 4px;
            font-size: 12px;
            background-color: red;
            border-radius: 50%;
            color: #fff;
            box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.08);
        }

        .btn-white {
            background-color: white !important;
            box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;
        }
    </style>
@endsection
@section('content2')
    <main class="container-fluid workflow-board-page lab-panel-theme workflow-theme lab-surface-theme ls-quotation-shell ls-ui-kit" data-ls-type="plex">
        @include('layouts.lab.partials.lab-panel-theme-styles')
        @include('layouts.lab.partials.lab-surface-theme-styles')
        @include('layouts.lab.partials.ls-ui.ls-ui-tokens-and-styles')
        @include('layouts.lab.partials.ls-ui.quotation.ls-quotation-overview-styles')
        <?php
        $crumbHeader = $quotationHeader ?? $reportHeader ?? null;
        $crumbStatus = (string) ($crumbHeader->status ?? $header[0]->status ?? '');
        if (! in_array($crumbStatus, ['Quote In Preparation', 'Quote In Approval', 'Quote Complete'], true)) {
            $crumbIsComplete = (int) ($header[0]->is_complete ?? 0) > 0
                || (int) ($crumbHeader->is_approved ?? $header[0]->is_approved ?? 0) === 1;
            $crumbStatus = $crumbIsComplete ? 'Quote Complete' : 'Quote In Preparation';
        }
        $crumbStageLabel = match ($crumbStatus) {
            'Quote In Approval' => 'Quotes in Approval',
            'Quote Complete' => 'Completed Quotes',
            'Quote In Preparation' => 'Quotes in Preparation',
            default => 'Quotations',
        };
        $items = array(
            array(
                'link' => '/lab-dashboard',
                'name' => 'Dashboard',
                'icon' => null
            ),
            array(
                'link' => route('quotation-index', [
                    'stage_filter' => $crumbStatus,
                    'page_tab' => 'quotations',
                ]),
                'name' => $crumbStageLabel,
                'icon' => null
            ),

            array(
                'link' => null,
                'name' => $header[0]->quote_number,
                'icon' => null
            ),

        );
        $header_id = $header[0]->id;
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>
        @include('layouts.lab.invoice.partials.quotation-preview-hover-styles')

        <div class="ls-quotation-doc-chrome quotation-preview-hover-parent">
            <div class="d-flex flex-wrap align-items-start" style="gap: 12px;">
                <div>
                    @php
                        $docHeader = $quotationHeader ?? $reportHeader ?? null;
                        $docIsApproved = (int) ($docHeader->is_approved ?? $header[0]->is_approved ?? 0) === 1;
                        $docIsComplete = (int) ($header[0]->is_complete ?? 0) > 0;
                        $docStatus = (string) ($docHeader->status ?? $header[0]->status ?? '');
                        $docApproverId = $docHeader->approved_by ?? $header[0]->approved_by ?? null;
                        $docApproverName = $docApproverId ? (getUserById($docApproverId)->name ?? null) : null;
                    @endphp
                    <h1 class="ls-quotation-doc-chrome__title">
                        <i class="mdi mdi-file-cad"></i>
                        <span>{{ $header[0]->quote_number }}</span>
                        @if(($reportHeader->revision_number ?? 1) > 1)
                            <span class="quotation-status-chip quotation-status-chip--approval">Rev. {{ $reportHeader->revision_number }}</span>
                        @endif
                    </h1>
                    <div class="ls-quotation-doc-chrome__meta">
                        @if($docIsApproved)
                            <span class="quotation-status-chip quotation-status-chip--complete">
                                <i class="mdi mdi-thumb-up"></i> Approved
                            </span>
                        @else
                            <span class="quotation-status-chip quotation-status-chip--approval">
                                <i class="mdi mdi-alert-decagram"></i> Awaiting Approval
                            </span>
                            @if($docApproverId && (string) $docApproverId !== (string) auth()->id() && filled($docApproverName))
                                <span class="quotation-status-chip quotation-status-chip--danger">
                                    <i class="mdi mdi-account-alert"></i> Approver — {{ $docApproverName }}
                                </span>
                            @endif
                        @endif
                        @if($docIsComplete)
                            <span class="quotation-status-chip quotation-status-chip--complete">
                                <i class="mdi mdi-check-circle"></i> Complete
                            </span>
                        @elseif($docStatus !== 'Quote In Approval')
                            <span class="quotation-status-chip quotation-status-chip--approval">
                                <i class="mdi mdi-progress-clock"></i> Not Complete
                            </span>
                        @endif
                        @if(($header[0]->is_batch_generate ?? 0) == 1)
                            <span class="quotation-status-chip quotation-status-chip--complete">
                                <i class="mdi mdi-thumb-up"></i> Batch Generated
                            </span>
                        @endif
                    </div>
                </div>

                <div class="ls-quotation-doc-chrome__actions">
                    @php
                        $docNeedsApproval = $docStatus === 'Quote In Approval' && ! $docIsApproved;
                    @endphp
                    @if($docNeedsApproval)
                        @livewire('billing.quotation-approve-from-detail', ['quotationHeaderId' => (string) $header[0]->id], key('billing-approve-detail-'.$header[0]->id))
                    @endif
                    <div class="dropdown">
                        <button type="button" class="ls-btn-ghost dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="mdi mdi-compare-vertical"></i> Move to workflow
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" style="font-size: 13px;">
                            <a class="dropdown-item"
                               href="{{ route('change_quotation_workflow', ['id' => $header[0]->id, 'stage' => 'Quote In Preparation']) }}">
                                <i class="mdi mdi-subdirectory-arrow-right"></i> Quotation In Preparation
                            </a>
                            <a class="dropdown-item"
                               href="{{ route('change_quotation_workflow', ['id' => $header[0]->id, 'stage' => 'Quote In Approval']) }}">
                                <i class="mdi mdi-subdirectory-arrow-right"></i> Quotation In Approval
                            </a>
                            <a class="dropdown-item"
                               href="{{ route('change_quotation_workflow', ['id' => $header[0]->id, 'stage' => 'Quote Complete']) }}">
                                <i class="mdi mdi-subdirectory-arrow-right"></i> Quotation Complete
                            </a>
                        </div>
                    </div>

                    <div class="dropdown">
                        <button type="button" class="ls-btn-ghost dropdown-toggle" id="quotationDocActionsDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="mdi mdi-dots-vertical"></i> Actions
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" style="font-size: 13px;" aria-labelledby="quotationDocActionsDropdown">
                            <a class="dropdown-item quotation-preview-quote-btn" href="{{ route('quotation.preview', ['id' => $header[0]->id]) }}" target="_blank" title="Preview quotation document">
                                <i class="mdi mdi-file-eye"></i> Preview Quote
                            </a>
                            @if($header[0]->is_complete == 1)
                                <span class="dropdown-item" style="cursor: pointer;" data-target="#print-quotation" data-header="{{ $header[0]->id }}" data-toggle="modal">
                                    <i class="mdi mdi-printer"></i> Process PDF
                                </span>
                                @if($header[0]->is_print == 1)
                                    <span class="dropdown-item" style="cursor: pointer;" data-toggle="modal" data-target="#quotation-upload">
                                        <i class="mdi mdi-share-all"></i> Send Quotation
                                    </span>
                                @endif
                                @can('laboratory.components.quotation.add')
                                    @php
                                        $docExpiringDate = $header[0]->expiring_date
                                            ?? ($quotationHeader->expiring_date ?? null);
                                        $docCanCreateEnquiry = $header[0]->quotation_type == 'Analysis'
                                            && $docExpiringDate
                                            && \Carbon\Carbon::parse($docExpiringDate)->startOfDay()->gte(now()->startOfDay());
                                    @endphp
                                    @if($docCanCreateEnquiry)
                                        <span class="dropdown-item" style="cursor: pointer;"
                                              onclick="window.Livewire && window.Livewire.dispatch('open-create-enquiry-from-quotation', { quotationId: @js((string) $header[0]->id) })">
                                            <i class="mdi mdi-flask-outline"></i> Create Enquiry From Quotation
                                        </span>
                                    @endif
                                @endcan
                            @endif
                            @if($docNeedsApproval && ($canApproveQuotation ?? false))
                                <div class="dropdown-divider"></div>
                                <span class="dropdown-item text-success" style="cursor: pointer;"
                                      onclick="window.Livewire && window.Livewire.dispatch('billing-quotation-open-approve')">
                                    <i class="mdi mdi-check-decagram"></i> Approve quotation
                                </span>
                                <span class="dropdown-item text-danger" style="cursor: pointer;"
                                      onclick="window.Livewire && window.Livewire.dispatch('billing-quotation-open-reject')">
                                    <i class="mdi mdi-close-octagon-outline"></i> Reject quotation
                                </span>
                            @endif
                            <div class="dropdown-divider"></div>
                            <form action="{{ route('create_quotation_revision', ['id' => $header[0]->id]) }}" method="POST" class="px-0 m-0">
                                @csrf
                                <button type="submit" class="dropdown-item" style="cursor: pointer;">
                                    <i class="mdi mdi-source-branch"></i> Create Revision
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @if(($revisionFamily ?? collect())->count() > 1)
                <div class="mt-3 pt-3" style="border-top: 1px solid #e2e8f0;">
                    <small class="text-muted d-block mb-1"><strong>Revision history</strong></small>
                    <ul class="mb-0 pl-3" style="font-size: 12px;">
                        @foreach($revisionFamily as $revision)
                            <li>
                                @if($revision->id === $header[0]->id)
                                    <strong>Rev. {{ $revision->revision_number }} — {{ $revision->quote_number }} (current)</strong>
                                @else
                                    <a href="{{ route('view_quotation_final', ['id' => $revision->id]) }}">Rev. {{ $revision->revision_number }} — {{ $revision->quote_number }}</a>
                                    <span class="text-muted">({{ $revision->quote_date }})</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(($linkedEnquiryEngagements ?? collect())->isNotEmpty())
                <div class="mt-3 pt-3" style="border-top: 1px solid #e2e8f0;">
                    <small class="text-muted d-block mb-1"><strong><i class="mdi mdi-link-variant"></i> Linked enquiries</strong></small>
                    <ul class="mb-0 pl-3" style="font-size: 12px;">
                        @foreach($linkedEnquiryEngagements as $engagement)
                            <li>
                                @if($engagement->enquiry)
                                    @php
                                        $linkedEnquiryLabel = trim((string) (
                                            $engagement->enquiry->unique_identification
                                            ?: $engagement->enquiry->reference_number
                                            ?: $engagement->enquiry->formatted_number
                                        ));
                                    @endphp
                                    <a href="{{ $engagement->enquiry->staffViewUrl() }}">{{ $linkedEnquiryLabel !== '' ? $linkedEnquiryLabel : 'Enquiry' }}</a>
                                    <span class="text-muted">— {{ $engagement->enquiry->status }}</span>
                                    @if($engagement->sent_to_customer_at)
                                        <span class="text-muted">· sent {{ $engagement->sent_to_customer_at->format('Y-m-d') }}</span>
                                    @endif
                                    @if($engagement->accepted_at)
                                        <span class="text-muted">· accepted {{ $engagement->accepted_at->format('Y-m-d') }}</span>
                                    @endif
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="mt-3" id="quotation-document">
            @php
                $shellMode = 'embedded';
                $forPdf = false;
                $stylesLoaded = true;
            @endphp
            @include('billing.quotations.amspec.shell')
        </div>
    </main>

    <livewire:billing.create-enquiry-from-quotation-wizard />
@endsection
@section('script2')
    <div class="modal fade" id="quotation-upload" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('upload_quotation', ['id' => $header[0]->id]) }}" method="post"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="modal-body">
                        <div class="alert alert-success">
                            <i class="mdi mdi-alert-decagram"></i> Confirm you want to send quote
                            {{$header[0]->quote_number}} to client.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success btn-sm"><i class="mdi mdi-content-save"></i>
                            Confirm</button>
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="print-quotation" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="mdi mdi-printer"></i> Quote {{$header[0]->quote_number}} PDF
                        Proccessing </h5>
                </div>
                <div class="modal-body text-center">
                    <div class="loading">

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script>

        function printquoatation() {
            var mybody = document.getElementById('quotation-document');
            var print_area = window.open();
            print_area.document.write(mybody.innerHTML);
            print_area.document.close();
            print_area.focus();
            print_area.print();
            print_area.close()
            // console.log(mybody);
        }

        $(function () {
            $('#print-quotation').on('show.bs.modal', function (e) {
                var header_id = $(e.relatedTarget).data('header');
                var load_text = $(`<img src="/images/loading.gif" width="70%" height="70%" alt="">
                        <div class="alert alert-primary">
                            <i class="mdi mdi-alert-octagon"></i> Kindly wait as the quotation pdf is being proccessed!
                        </div>`);
                $(this).find('.loading').empty();
                $(this).find('.loading').append(load_text);
                $.ajax({
                    url: '/billing/print_quotation/' + header_id,
                    success: function (data) {
                        console.log(data);
                    },
                    complete: function (data) {
                        $('#print-quotation').find('.loading').empty();
                        var complete_text = $(`
                        <img src="/images/suc.gif" width="50%" height="50%" alt="">
                        <div class="alert alert-success">
                            <i class="mdi mdi-alert-octagon"></i> Quote PDF generated successfully!
                        </div>
                        `);
                        $('#print-quotation').find('.loading').append(complete_text);
                    },
                    error: function (data) {
                        console.log(data);
                    }
                })
            })
        })
    </script>

@endsection