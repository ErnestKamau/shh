<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle }} - GCLA Print Layout</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 15mm 20mm 15mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        /* Standard GCLA Double-Header Layout */
        .header-container {
            width: 100%;
            border-bottom: 2px double #000;
            padding-bottom: 6px;
            margin-bottom: 15px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        .header-table td {
            border: none;
            vertical-align: top;
            padding: 0;
        }
        .ref-block {
            width: 28%;
            font-size: 9px;
            line-height: 1.3;
            text-align: left;
        }
        .emblem-block {
            width: 44%;
            text-align: center;
        }
        .emblem-image {
            height: 60px;
            width: auto;
            margin-bottom: 2px;
        }
        .emblem-block h2 {
            font-size: 10px;
            font-weight: bold;
            margin: 1px 0;
            text-transform: uppercase;
        }
        .address-block {
            width: 28%;
            font-size: 9px;
            line-height: 1.3;
            text-align: right;
        }

        /* Document Title */
        .doc-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            text-decoration: underline;
            margin: 15px 0;
            text-transform: uppercase;
        }

        /* Section Headings */
        .section-title {
            font-size: 11px;
            font-weight: bold;
            margin: 12px 0 4px 0;
            text-transform: uppercase;
        }
        .section-content {
            margin-bottom: 12px;
            text-align: justify;
            font-size: 10px;
        }

        /* Results Table Grid */
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 15px 0;
        }
        .results-table th {
            border: 1px solid #000;
            background-color: #f2f2f2;
            padding: 5px 6px;
            font-weight: bold;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }
        .results-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            font-size: 9px;
            vertical-align: top;
        }
        .results-table tr:nth-child(even) td {
            background-color: #fafafa;
        }

        /* Signature block: Tri-Party Grid */
        .signature-section {
            margin-top: 40px;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        .signature-table td {
            width: 33%;
            border: none;
            text-align: center;
            vertical-align: top;
            padding: 8px;
        }
        .sig-line {
            width: 80%;
            margin: 30px auto 4px auto;
            border-bottom: 1px solid #000;
        }
        .sig-label {
            font-size: 9px;
            font-weight: bold;
            margin: 0;
        }
        .sig-desc {
            font-size: 8px;
            color: #333;
            margin: 1px 0 0 0;
        }

        /* Print Specifics */
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
                padding: 0;
            }
        }

        .print-btn-bar {
            background-color: #f1f5f9;
            padding: 10px 20px;
            text-align: right;
            border-bottom: 1px solid #e2e8f0;
        }
        .btn-print {
            background-color: #10b981;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 11px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-print:hover {
            background-color: #059669;
        }
    </style>
</head>
<body>

    @if(!$isPdf)
        <div class="print-btn-bar no-print">
            <a href="javascript:window.print()" class="btn-print">
                Print Report
            </a>
            <a href="{{ route('module-reports.index') }}" class="btn-print" style="background-color: #475569; margin-left: 8px;">
                Back to Dashboard
            </a>
        </div>
    @endif

    <!-- Standardized Double-Header Block -->
    <div class="header-container">
        <table class="header-table">
            <tr>
                <td class="ref-block">
                    <strong>Ref No:</strong> LAB CZ025-{{ date('Y') }}<br>
                    <strong>Form:</strong> GCLA/LIMS/{{ strtoupper($reportType) }}<br>
                    <strong>Date:</strong> {{ date('d/m/Y') }}<br>
                    <strong>Page:</strong> 1 of 1
                </td>
                
                <td class="emblem-block">
                    @if(isset($logos['tanzania']) && $logos['tanzania'])
                        <img class="emblem-image" src="{{ $logos['tanzania'] }}" alt="Tanzania National Emblem">
                    @else
                        <div style="height: 50px; font-weight: bold; font-size: 9px;">[COAT OF ARMS]</div>
                    @endif
                    <h2>THE UNITED REPUBLIC OF TANZANIA</h2>
                    <h2 style="font-size: 9px;">MINISTRY OF HEALTH</h2>
                    <h2 style="color: #15803d; font-size: 10px;">GOVERNMENT CHEMIST LABORATORY AUTHORITY</h2>
                </td>

                <td class="address-block">
                    <strong>GCLA Head Office:</strong><br>
                    Baraka Obama Road<br>
                    P.O. Box 164, Dar es Salaam<br>
                    <strong>Tel:</strong> +255 22 2113383<br>
                    <strong>Email:</strong> gcla@gcla.go.tz<br>
                    <strong>Website:</strong> www.gcla.go.tz
                </td>
            </tr>
        </table>
    </div>

    <!-- Official Document Title -->
    <div class="doc-title">
        {{ strtoupper($reportTitle) }}
    </div>

    <!-- SECTION 1.0: UTANGULIZI -->
    <div class="section-title">1.0 UTANGULIZI (INTRODUCTION)</div>
    <div class="section-content">
        {{ $introduction }} Uchunguzi huu umechakatwa kwa kutumia data zilizopo kwenye mfumo wa LIMS wa mamlaka ya GCLA na umechujwa kulingana na vigezo vilivyochaguliwa na mtaalamu wa usimamizi wa ubora.
    </div>

    <!-- SECTION 2.0: MATOKEO YA UCHUNGUZI -->
    <div class="section-title">2.0 MATOKEO YA UCHUNGUZI / TAARIFA ZA RIPOTI (REPORT DATA)</div>
    <table class="results-table">
        <thead>
            <tr>
                @foreach($headers as $h)
                    <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @if(count($results) === 0)
                <tr>
                    <td colspan="{{ count($headers) }}" style="text-align: center; font-style: italic;">
                        Hakuna kumbukumbu zilizopatikana kulingana na vigezo vilivyochaguliwa. / No records found matching the chosen criteria.
                    </td>
                </tr>
            @else
                @foreach($results as $row)
                    <tr>
                        @if($reportType === 'sample_register')
                            <td>{{ $row->receipt_date ?? optional($row->created_at)->format('Y-m-d') }}</td>
                            <td><strong>{{ $row->batch_code }}</strong></td>
                            <td>{{ optional($row->client)->name ?? 'Walk-in Customer' }}</td>
                            <td>{{ optional($row->sample_type)->name ?? 'N/A' }}</td>
                            <td>{{ $row->priority ?? 'Medium' }}</td>
                            <td>{{ $row->workflow_stage ?? 'Staging' }}</td>
                        @elseif($reportType === 'chain_of_custody')
                            <td>{{ optional($row->created_at)->format('Y-m-d H:i') }}</td>
                            <td><strong>{{ optional($row->sampleHeader)->batch_code ?? 'N/A' }}</strong></td>
                            <td>{{ optional($row->started_by)->name ?? 'N/A' }}</td>
                            <td>{{ optional($row->completed_by)->name ?? 'Pending' }}</td>
                            <td>{{ optional($row->tracking_stage)->name ?? 'N/A' }}</td>
                            <td>{{ $row->comments ?? 'N/A' }}</td>
                        @elseif($reportType === 'rejection')
                            <td>{{ optional($row->submitted_at)->format('Y-m-d') }}</td>
                            <td><strong>{{ $row->batch_code ?? $row->request_reference }}</strong></td>
                            <td>{{ $row->payload['name_of_client'] ?? 'N/A' }}</td>
                            <td>{{ $row->payload['laboratory_staff'] ?? 'N/A' }}</td>
                            <td>
                                @if(isset($row->payload['reasons']))
                                    {{ implode(', ', (array)$row->payload['reasons']) }}
                                @else
                                    {{ $row->payload['explanation'] ?? 'N/A' }}
                                @endif
                            </td>
                            <td>{{ $row->payload['explanation'] ?? 'N/A' }}</td>
                        @elseif($reportType === 'disposal')
                            <td>{{ $row->disposal_date }}</td>
                            <td><strong>{{ $row->sample_code }}</strong></td>
                            <td>{{ optional($row->getSampleHeader())->batch_code ?? 'N/A' }}</td>
                            <td>{{ optional($row->lab)->name ?? 'N/A' }}</td>
                            <td>Biosafety incinerator / Standard Method</td>
                        @elseif($reportType === 'amendment')
                            <td>{{ optional($row->created_at)->format('Y-m-d') }}</td>
                            <td><strong>{{ optional($row->sampleHeader)->batch_code ?? 'N/A' }}</strong></td>
                            <td>{{ $row->creator }}</td>
                            <td>{{ $row->ammendment_no ?? '1' }}</td>
                            <td>{{ $row->reason }}</td>
                        @elseif($reportType === 'workbook')
                            <td>{{ optional($row->created_at)->format('Y-m-d H:i') }}</td>
                            <td><strong>{{ $row->sample_detail_code }}</strong></td>
                            <td>{{ $row->analyte_code }}</td>
                            <td>{{ $row->result }}</td>
                            <td>{{ optional($row->operator)->name ?? 'N/A' }}</td>
                            <td>Verified</td>
                        @elseif($reportType === 'tat')
                            <td>{{ $row->receipt_date }}</td>
                            <td><strong>{{ $row->sample_code }}</strong></td>
                            <td>{{ $row->analyst_name }}</td>
                            <td>{{ $row->sample_type_name }}</td>
                            <td>Complete</td>
                        @elseif($reportType === 'standards')
                            <td>{{ $row->name }}</td>
                            <td><strong>{{ $row->standard_code }}</strong></td>
                            <td>{{ $row->batch_number }}</td>
                            <td>{{ $row->expiry_date }}</td>
                            <td>{{ $row->active ? 'Active' : 'Expired/Inactive' }}</td>
                        @elseif($reportType === 'risk_register')
                            <td><strong>{{ $row->risk_number }}</strong></td>
                            <td>{{ $row->title }}</td>
                            <td>{{ optional($row->category)->name ?? 'General' }}</td>
                            <td>{{ $row->risk_level ?? 'Medium' }}</td>
                            <td>{{ $row->riskOwner }}</td>
                            <td>{{ $row->status_name }}</td>
                        @elseif($reportType === 'non_conformance')
                            <td><strong>{{ $row->nc_number }}</strong></td>
                            <td>{{ $row->title }}</td>
                            <td>{{ optional($row->date_identified)->format('Y-m-d') }}</td>
                            <td>{{ optional($row->identifiedByUser)->name ?? 'System' }}</td>
                            <td>{{ $row->severity ?? 'Medium' }}</td>
                            <td>{{ $row->status_name }}</td>
                        @elseif($reportType === 'system_audit')
                            <td>{{ optional($row->created_at)->format('Y-m-d H:i:s') }}</td>
                            <td>{{ optional($row->user)->name ?? 'Guest/Console' }}</td>
                            <td>{{ $row->event }}</td>
                            <td>{{ $row->ip_address }}</td>
                            <td>Updated record metadata logs.</td>

                        {{-- Mapped Custom SRS Reports --}}
                        @elseif($reportType === 'sample_return')
                            <td><strong>{{ $row->sample_code }}</strong></td>
                            <td>{{ $row->exhibit_name }}</td>
                            <td>{{ $row->returned_to }}</td>
                            <td>{{ $row->returned_date }}</td>
                            <td>{{ $row->officer_name }}</td>
                            <td>{{ $row->authorized_by }}</td>
                        @elseif($reportType === 'retained_samples')
                            <td><strong>{{ $row->sample_code }}</strong></td>
                            <td>{{ $row->batch_code }}</td>
                            <td>{{ $row->retained_date }}</td>
                            <td>{{ $row->retention_period }}</td>
                            <td>{{ $row->shelf_location }}</td>
                            <td>{{ $row->officer_name }}</td>
                        @elseif($reportType === 'resampling')
                            <td><strong>{{ $row->batch_code }}</strong></td>
                            <td>{{ $row->original_code }}</td>
                            <td>{{ $row->resampled_date }}</td>
                            <td>{{ $row->reason }}</td>
                            <td>{{ $row->officer_name }}</td>
                        @elseif($reportType === 'proficiency_testing')
                            <td><strong>{{ $row->pt_scheme }}</strong></td>
                            <td>{{ $row->analyte }}</td>
                            <td>{{ $row->score }}</td>
                            <td>{{ $row->status }}</td>
                            <td>{{ $row->date }}</td>
                            <td>{{ $row->analyst }}</td>
                        @elseif($reportType === 'coa_report')
                            <td><strong>{{ $row->coa_reference }}</strong></td>
                            <td>{{ $row->batch_code }}</td>
                            <td>{{ $row->customer_name }}</td>
                            <td>{{ $row->released_date }}</td>
                            <td>{{ $row->status }}</td>
                            <td>{{ $row->approved_by }}</td>
                        @elseif($reportType === 'method_validation')
                            <td><strong>{{ $row->method_code }}</strong></td>
                            <td>{{ $row->method_title }}</td>
                            <td>{{ $row->validation_date }}</td>
                            <td>{{ $row->parameters_checked }}</td>
                            <td>{{ $row->approved_status }}</td>
                        @elseif($reportType === 'instrument_log')
                            <td><strong>{{ $row->instrument_name }}</strong></td>
                            <td>{{ $row->date_checked }}</td>
                            <td>{{ $row->operator_name }}</td>
                            <td>{{ $row->hours_utilised }}</td>
                            <td>{{ $row->log_comments }}</td>
                        @elseif($reportType === 'intermediate_check')
                            <td><strong>{{ $row->equipment_name }}</strong></td>
                            <td>{{ $row->check_date }}</td>
                            <td>{{ $row->reference_standard }}</td>
                            <td>{{ $row->deviation_value }}</td>
                            <td>{{ $row->status }}</td>
                        @elseif($reportType === 'instrument_calibration')
                            <td><strong>{{ $row->instrument_name }}</strong></td>
                            <td>{{ $row->calibration_date }}</td>
                            <td>{{ $row->due_date }}</td>
                            <td>{{ $row->calibrated_by }}</td>
                            <td>{{ $row->status }}</td>
                            <td>{{ $row->deviation }}</td>
                        @elseif($reportType === 'preventive_maintenance')
                            <td><strong>{{ $row->equipment_name }}</strong></td>
                            <td>{{ $row->maintenance_date }}</td>
                            <td>{{ $row->contractor_name }}</td>
                            <td>{{ $row->actions_taken }}</td>
                            <td>{{ $row->status }}</td>
                        @elseif($reportType === 'environmental_monitoring')
                            <td>{{ $row->timestamp }}</td>
                            <td>{{ $row->temperature }}</td>
                            <td>{{ $row->humidity }}</td>
                            <td>{{ $row->recorded_by }}</td>
                            <td>{{ $row->status }}</td>
                        @elseif($reportType === 'decontamination_register')
                            <td><strong>{{ $row->area_room }}</strong></td>
                            <td>{{ $row->decontaminated_date }}</td>
                            <td>{{ $row->chemical_used }}</td>
                            <td>{{ $row->staff_officer }}</td>
                            <td>{{ $row->status }}</td>
                        @elseif($reportType === 'backlog_analysis')
                            <td><strong>{{ $row->lab_section }}</strong></td>
                            <td>{{ $row->backlog_count }}</td>
                            <td>{{ $row->oldest_pending }}</td>
                            <td>{{ $row->target_sla }}</td>
                            <td>{{ $row->risk_level }}</td>
                        @elseif($reportType === 'performance_report')
                            <td><strong>{{ $row->lab_section }}</strong></td>
                            <td>{{ $row->samples_completed }}</td>
                            <td>{{ $row->target_compliance }}</td>
                            <td>{{ $row->analyst_count }}</td>
                            <td>{{ $row->rating }}</td>
                        @elseif($reportType === 'trend_analysis')
                            <td><strong>{{ $row->matrix_type }}</strong></td>
                            <td>{{ $row->analyte_code }}</td>
                            <td>{{ $row->min_value }}</td>
                            <td>{{ $row->max_value }}</td>
                            <td>{{ $row->trend_direction }}</td>
                        @elseif($reportType === 'zonal_performance')
                            <td><strong>{{ $row->zone_name }}</strong></td>
                            <td>{{ $row->target_sla }}</td>
                            <td>{{ $row->sla_met }}</td>
                            <td>{{ $row->volume_handled }}</td>
                            <td>{{ $row->zonal_rating }}</td>
                        @elseif($reportType === 'proforma_invoice')
                            <td><strong>{{ $row->proforma_ref }}</strong></td>
                            <td>{{ $row->client_name }}</td>
                            <td>{{ $row->sample_count }}</td>
                            <td>{{ $row->total_fee }}</td>
                            <td>{{ $row->status }}</td>
                        @elseif($reportType === 'payment_receipt')
                            <td><strong>{{ $row->receipt_ref }}</strong></td>
                            <td>{{ $row->invoice_ref }}</td>
                            <td>{{ $row->client_name }}</td>
                            <td>{{ $row->amount_paid }}</td>
                            <td>{{ $row->payment_date }}</td>
                        @elseif($reportType === 'aging_receivables')
                            <td><strong>{{ $row->client_name }}</strong></td>
                            <td>{{ $row->current }}</td>
                            <td>{{ $row->days_30_60 }}</td>
                            <td>{{ $row->days_61_90 }}</td>
                            <td>{{ $row->days_over_90 }}</td>
                            <td><strong>{{ $row->total_due }}</strong></td>
                        @elseif($reportType === 'revenue_summary')
                            <td><strong>{{ $row->lab_section }}</strong></td>
                            <td>{{ $row->quarterly_revenue }}</td>
                            <td>{{ $row->target_achievement }}</td>
                            <td>{{ $row->invoiced_batches }}</td>
                            <td>{{ $row->status }}</td>
                        @elseif($reportType === 'reconciliation_report')
                            <td><strong>{{ $row->month_period }}</strong></td>
                            <td>{{ $row->expected }}</td>
                            <td>{{ $row->collected }}</td>
                            <td>{{ $row->discrepancy }}</td>
                            <td>{{ $row->status }}</td>
                        @elseif($reportType === 'annual_procurement_plan')
                            <td><strong>{{ $row->reagent_name }}</strong></td>
                            <td>{{ $row->fiscal_year }}</td>
                            <td>{{ $row->quarterly_planned_qty }}</td>
                            <td>{{ $row->unit_cost }}</td>
                            <td>{{ $row->allocated_budget }}</td>
                            <td>{{ $row->status }}</td>
                        @elseif($reportType === 'inspection_report')
                            <td><strong>{{ $row->delivery_ref }}</strong></td>
                            <td>{{ $row->supplier_name }}</td>
                            <td>{{ $row->inspection_date }}</td>
                            <td>{{ $row->conformity_status }}</td>
                            <td>{{ $row->inspected_by }}</td>
                        @elseif($reportType === 'grn_note')
                            <td><strong>{{ $row->grn_number }}</strong></td>
                            <td>{{ $row->delivery_date }}</td>
                            <td>{{ $row->supplier_name }}</td>
                            <td>{{ $row->items_accepted }}</td>
                            <td>{{ $row->items_returned }}</td>
                        @elseif($reportType === 'inventory_status')
                            <td><strong>{{ $row->reagent_name }}</strong></td>
                            <td>{{ $row->current_qty }}</td>
                            <td>{{ $row->unit_measure }}</td>
                            <td>{{ $row->storage_temp }}</td>
                            <td>{{ $row->safety_rating }}</td>
                        @elseif($reportType === 'stock_disposal')
                            <td><strong>{{ $row->reagent_name }}</strong></td>
                            <td>{{ $row->batch_lot }}</td>
                            <td>{{ $row->disposed_date }}</td>
                            <td>{{ $row->disposal_method }}</td>
                            <td>{{ $row->officer }}</td>
                        @elseif($reportType === 'audit_report')
                            <td><strong>{{ $row->audit_reference }}</strong></td>
                            <td>{{ $row->audited_section }}</td>
                            <td>{{ $row->date_conducted }}</td>
                            <td>{{ $row->ncs_found }}</td>
                            <td>{{ $row->lead_auditor }}</td>
                        @elseif($reportType === 'risk_register')
                            <td><strong>{{ $row->risk_number }}</strong></td>
                            <td>{{ $row->title }}</td>
                            <td>{{ optional($row->category)->name ?? 'General' }}</td>
                            <td>{{ $row->risk_level ?? 'Medium' }}</td>
                            <td>{{ $row->riskOwner }}</td>
                            <td>{{ $row->status_name }}</td>
                        @elseif($reportType === 'non_conformance')
                            <td><strong>{{ $row->nc_number }}</strong></td>
                            <td>{{ $row->title }}</td>
                            <td>{{ optional($row->date_identified)->format('Y-m-d') }}</td>
                            <td>{{ optional($row->identifiedByUser)->name ?? 'System' }}</td>
                            <td>{{ $row->severity ?? 'Medium' }}</td>
                            <td>{{ $row->status_name }}</td>
                        @elseif($reportType === 'mgmt_review')
                            <td><strong>{{ $row->meeting_date }}</strong></td>
                            <td>{{ $row->attendees }}</td>
                            <td>{{ $row->agenda_summary }}</td>
                            <td>{{ $row->actions_count }}</td>
                            <td>{{ $row->chairperson }}</td>
                        @elseif($reportType === 'complaints_report')
                            <td><strong>{{ $row->complaint_code }}</strong></td>
                            <td>{{ $row->client_name }}</td>
                            <td>{{ $row->logged_date }}</td>
                            <td>{{ $row->feedback_type }}</td>
                            <td>{{ $row->status }}</td>
                        @elseif($reportType === 'exception_report')
                            <td><strong>{{ $row->exception_code }}</strong></td>
                            <td>{{ $row->severity }}</td>
                            <td>{{ $row->incident_date }}</td>
                            <td>{{ $row->description }}</td>
                            <td>{{ $row->investigated_by }}</td>
                        @elseif($reportType === 'system_audit')
                            <td>{{ optional($row->created_at)->format('Y-m-d H:i:s') }}</td>
                            <td>{{ optional($row->user)->name ?? 'Guest/Console' }}</td>
                            <td>{{ $row->event }}</td>
                            <td>{{ $row->ip_address }}</td>
                            <td>Updated record metadata logs.</td>
                        @elseif($reportType === 'sample_audit')
                            <td><strong>{{ $row->sample_code }}</strong></td>
                            <td>{{ $row->audit_date }}</td>
                            <td>{{ $row->discrepancies }}</td>
                            <td>{{ $row->verified_by }}</td>
                            <td>{{ $row->status }}</td>
                        @elseif($reportType === 'reagent_audit')
                            <td><strong>{{ $row->reagent_name }}</strong></td>
                            <td>{{ $row->lot_number }}</td>
                            <td>{{ $row->actual_stock }}</td>
                            <td>{{ $row->system_stock }}</td>
                            <td>{{ $row->discrepancy }}</td>
                        @elseif($reportType === 'ceo_performance')
                            <td><strong>{{ $row->period }}</strong></td>
                            <td>{{ $row->total_revenue }}</td>
                            <td>{{ $row->overall_tat }}</td>
                            <td>{{ $row->customer_satisfaction }}</td>
                            <td>{{ $row->performance_rating }}</td>
                        @elseif($reportType === 'equipment_breakdown')
                            <td><strong>{{ $row->instrument_name }}</strong></td>
                            <td>{{ $row->breakdown_date }}</td>
                            <td>{{ $row->repair_completion }}</td>
                            <td>{{ $row->downtime_hours }}</td>
                            <td>{{ $row->repair_cost }}</td>
                        @elseif($reportType === 'clients_served')
                            <td><strong>{{ $row->month }}</strong></td>
                            <td>{{ $row->corporate_clients }}</td>
                            <td>{{ $row->individual_clients }}</td>
                            <td>{{ $row->govt_bodies }}</td>
                            <td>{{ $row->total_served }}</td>
                        @elseif($reportType === 'interzone_transfers')
                            <td><strong>{{ $row->batch_code }}</strong></td>
                            <td>{{ $row->origin }}</td>
                            <td>{{ $row->destination }}</td>
                            <td>{{ $row->courier }}</td>
                            <td>{{ $row->dispatch_date }}</td>
                            <td>{{ $row->status }}</td>
                        @elseif($reportType === 'zonal_dispatch')
                            <td><strong>{{ $row->batch_code }}</strong></td>
                            <td>{{ $row->dispatch_no }}</td>
                            <td>{{ $row->courier_name }}</td>
                            <td>{{ $row->dispatch_date }}</td>
                            <td>{{ $row->delivery_status }}</td>
                        @elseif($reportType === 'regional_comparison')
                            <td><strong>{{ $row->zone_name }}</strong></td>
                            <td>{{ $row->samples_processed }}</td>
                            <td>{{ $row->average_tat }}</td>
                            <td>{{ $row->rejections_count }}</td>
                            <td>{{ $row->efficiency_rating }}</td>
                        @elseif($reportType === 'annual_procurement_plan')
                            <td><strong>{{ $row->reagent_name }}</strong></td>
                            <td>{{ $row->fiscal_year }}</td>
                            <td>{{ $row->quarterly_planned_qty }}</td>
                            <td>{{ $row->unit_cost }}</td>
                            <td>{{ $row->allocated_budget }}</td>
                            <td>{{ $row->status }}</td>
                        @elseif($reportType === 'supplier_scorecard')
                            <td><strong>{{ $row->supplier_name }}</strong></td>
                            <td>{{ $row->rating }}</td>
                            <td>{{ $row->delivery_reliability }}</td>
                            <td>{{ $row->active_contracts }}</td>
                            <td>{{ $row->quality_conformity }}</td>
                        @elseif($reportType === 'stock_reorder')
                            <td><strong>{{ $row->reagent_name }}</strong></td>
                            <td>{{ $row->current_stock }}</td>
                            <td>{{ $row->reorder_level }}</td>
                            <td>{{ $row->supplier }}</td>
                            <td>{{ $row->status }}</td>
                        @endif
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <!-- SECTION 3.0: HITIMISHO -->
    <div class="section-title">3.0 HITIMISHO (CONCLUSION)</div>
    <div class="section-content">
        Ripoti hii imethibitishwa rasmi na mfumo wa uendeshaji na usimamizi wa maabara (LIMS) wa Mamlaka ya Maabara ya Mkemia Mkuu wa Serikali (GCLA) kwa ajili ya matumizi rasmi ya kiutendaji na usimamizi wa ubora.
    </div>

</body>
</html>
