<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case File Review Form</title>
    <style>
        @page {
            margin: 35px 40px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 20px;
        }
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .company-logo {
            max-height: 55px;
            max-width: 220px;
        }
        .company-info {
            text-align: right;
            color: #475569;
            font-size: 9.5px;
            line-height: 1.3;
        }
        .company-name {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .doc-title-container {
            text-align: center;
            border-top: 1.5px solid #cbd5e1;
            border-bottom: 1.5px solid #cbd5e1;
            padding: 8px 0;
            margin-bottom: 15px;
            background-color: #f8fafc;
        }
        .doc-title {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0f172a;
        }
        .doc-subtitle {
            margin: 3px 0 0 0;
            font-size: 10px;
            color: #64748b;
            font-weight: bold;
        }
        .section-title {
            background-color: #f1f5f9;
            color: #0f172a;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border-left: 3.5px solid #2563eb;
            padding: 4px 8px;
            margin-top: 12px;
            margin-bottom: 6px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            font-size: 9.5px;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: bold;
            width: 25%;
            text-align: left;
        }
        .data-table td {
            color: #0f172a;
        }
        .checkbox-group {
            display: inline-block;
            margin-right: 12px;
        }
        .checkbox-box {
            display: inline-block;
            width: 11px;
            height: 11px;
            border: 1px solid #64748b;
            border-radius: 2px;
            margin-right: 4px;
            vertical-align: middle;
            text-align: center;
            line-height: 10px;
            font-size: 8.5px;
            font-weight: bold;
            color: #64748b;
            background-color: #ffffff;
        }
        .checkbox-box.checked {
            background-color: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-top: 25px;
        }
        .signature-table td {
            border: none;
            width: 50%;
            vertical-align: bottom;
            padding: 0 15px 0 0;
        }
        .signature-label {
            font-weight: bold;
            color: #475569;
            margin-bottom: 40px;
            font-size: 9.5px;
            text-transform: uppercase;
        }
        .signature-line {
            border-top: 1px solid #94a3b8;
            padding-top: 4px;
            font-size: 9px;
            color: #64748b;
        }
        .footer {
            position: fixed;
            bottom: -15px;
            left: 0;
            right: 0;
            height: 20px;
            text-align: center;
            font-size: 8.5px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
    </style>
</head>
<body>

@php
    $company = getActiveCompany();
@endphp

<div class="company-header" style="text-align: center; margin-bottom: 18px;">
    <div style="margin-bottom: 8px;">
        @if(!empty($logo))
            <img src="{{ $logo }}" class="company-logo" alt="Logo" style="max-height: 55px; max-width: 220px; object-fit: contain;">
        @elseif($company && !empty($company->logo))
            <img src="{{ $company->logo }}" class="company-logo" alt="Logo" style="max-height: 55px; max-width: 220px; object-fit: contain;">
        @else
            <div style="font-size: 20px; font-weight: 800; color: #2563eb; letter-spacing: -0.5px;">GCLA LABS</div>
        @endif
    </div>
    
    <div class="company-name" style="font-size: 13.5px; font-weight: bold; color: #0f172a; text-transform: uppercase; margin-bottom: 3px;">
        {{ $company->name ?? 'GCLA Laboratory' }}
    </div>
    
    <div style="color: #475569; font-size: 9.5px; line-height: 1.4;">
        @php
            $details = [];
            if ($company) {
                $addressParts = [];
                if (!empty($company->address)) $addressParts[] = e($company->address);
                if (!empty($company->street)) $addressParts[] = e($company->street);
                if (!empty($addressParts)) {
                    $details[] = implode(', ', $addressParts);
                }
                
                $contact = [];
                if (!empty($company->email)) $contact[] = 'Email: ' . e($company->email);
                if (!empty($company->cell_phone)) $contact[] = 'Tel: ' . e($company->cell_phone);
                if (!empty($company->website)) $contact[] = 'Web: ' . e($company->website);
                
                if (!empty($contact)) {
                    $details[] = implode(' | ', $contact);
                }
            }
        @endphp
        {!! implode('<br>', $details) !!}
    </div>
</div>

<div class="doc-title-container">
    <h2 class="doc-title">Case File Review Form</h2>
    <div class="doc-subtitle">BATCH REFERENCE: {{ $batch->batch_code }} &bull; DNA/F/12</div>
</div>

<!-- 1. SAMPLE INFORMATION -->
<div class="section-title">1. Sample Information</div>
<table class="data-table">
    <tr>
        <th>Lab No</th>
        <td>{{ $form->lab_no }}</td>
        <th>File No</th>
        <td>{{ $form->file_no }}</td>
    </tr>
    <tr>
        <th>Date In</th>
        <td>{{ $form->date_in ? \Carbon\Carbon::parse($form->date_in)->format('d/m/Y') : '' }}</td>
        <th>Client</th>
        <td>{{ $form->client }}</td>
    </tr>
    <tr>
        <th>No of Samples</th>
        <td>{{ $form->no_of_samples }}</td>
        <th>Sample Condition</th>
        <td>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->sample_condition_sealed ? 'checked' : '' }}">{{ $form->sample_condition_sealed ? '✓' : '' }}</span> Sealed
            </div>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->sample_condition_labelled ? 'checked' : '' }}">{{ $form->sample_condition_labelled ? '✓' : '' }}</span> Labelled
            </div>
        </td>
    </tr>
    <tr>
        <th>Condition Remarks</th>
        <td colspan="3">{{ $form->sample_condition_remark }}</td>
    </tr>
</table>

<!-- 2. SAMPLE SCREENING -->
<div class="section-title">2. Sample Screening</div>
<table class="data-table">
    <tr>
        <th>Screening Date</th>
        <td>{{ $form->screening_date ? \Carbon\Carbon::parse($form->screening_date)->format('d/m/Y') : '' }}</td>
        <th>Screening Method</th>
        <td>{{ $form->screening_method }}</td>
    </tr>
    <tr>
        <th>Sample Types</th>
        <td colspan="3">
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->screening_sample_type_blood ? 'checked' : '' }}">{{ $form->screening_sample_type_blood ? '✓' : '' }}</span> Blood
            </div>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->screening_sample_type_object_with_blood ? 'checked' : '' }}">{{ $form->screening_sample_type_object_with_blood ? '✓' : '' }}</span> Object with Blood
            </div>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->screening_sample_type_semen ? 'checked' : '' }}">{{ $form->screening_sample_type_semen ? '✓' : '' }}</span> Semen
            </div>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->screening_sample_type_object_with_semen ? 'checked' : '' }}">{{ $form->screening_sample_type_object_with_semen ? '✓' : '' }}</span> Object with Semen
            </div>
            @if(!empty($form->screening_sample_type_others))
                <div style="margin-top: 4px; font-weight: bold; color: #475569;">
                    Others: <span style="font-weight: normal; color: #0f172a;">{{ $form->screening_sample_type_others }}</span>
                </div>
            @endif
        </td>
    </tr>
    <tr>
        <th>Results</th>
        <td colspan="3">
            <strong style="color: #475569;">Result 1:</strong> {{ $form->screening_results_1 }} &nbsp;&nbsp;&bull;&nbsp;&nbsp; 
            <strong style="color: #475569;">Result 2:</strong> {{ $form->screening_results_2 }}
        </td>
    </tr>
</table>

<!-- 3. SAMPLE EXTRACTION & QUANTIFICATION -->
<div class="section-title">3. Sample Extraction & Quantification</div>
<table class="data-table">
    <tr>
        <th>Extraction Date</th>
        <td>{{ $form->extraction_date ? \Carbon\Carbon::parse($form->extraction_date)->format('d/m/Y') : '' }}</td>
        <th>Extraction Method</th>
        <td>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->extraction_method_chelex ? 'checked' : '' }}">{{ $form->extraction_method_chelex ? '✓' : '' }}</span> Chelex
            </div>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->extraction_method_prepfiler ? 'checked' : '' }}">{{ $form->extraction_method_prepfiler ? '✓' : '' }}</span> Prepfiler
            </div>
            @if(!empty($form->extraction_method_other))
                <div style="margin-top: 3px; font-weight: bold; color: #475569;">
                    Other Method: <span style="font-weight: normal; color: #0f172a;">{{ $form->extraction_method_other }}</span>
                </div>
            @endif
        </td>
    </tr>
    <tr>
        <th>Quantification Date</th>
        <td>{{ $form->quantification_date ? \Carbon\Carbon::parse($form->quantification_date)->format('d/m/Y') : '' }}</td>
        <th>Cycles & Kits</th>
        <td>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->quantification_no_of_cycles_40 ? 'checked' : '' }}">{{ $form->quantification_no_of_cycles_40 ? '✓' : '' }}</span> 40 Cycles
            </div>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->quantification_kit_used_quant_trio ? 'checked' : '' }}">{{ $form->quantification_kit_used_quant_trio ? '✓' : '' }}</span> Kit: Quant Trio
            </div>
        </td>
    </tr>
    <tr>
        <th>Quantification Remarks</th>
        <td colspan="3">{{ $form->quantification_remarks }}</td>
    </tr>
</table>

<!-- 4. PCR AMPLIFICATION & INJECTION -->
<div class="section-title">4. PCR Amplification & Injection</div>
<table class="data-table">
    <tr>
        <th>PCR Amplification Date</th>
        <td>{{ $form->pcr_amplification_date ? \Carbon\Carbon::parse($form->pcr_amplification_date)->format('d/m/Y') : '' }}</td>
        <th>No. of Cycles</th>
        <td>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->pcr_no_of_cycles_28 ? 'checked' : '' }}">{{ $form->pcr_no_of_cycles_28 ? '✓' : '' }}</span> 28
            </div>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->pcr_no_of_cycles_29 ? 'checked' : '' }}">{{ $form->pcr_no_of_cycles_29 ? '✓' : '' }}</span> 29
            </div>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->pcr_no_of_cycles_30 ? 'checked' : '' }}">{{ $form->pcr_no_of_cycles_30 ? '✓' : '' }}</span> 30
            </div>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->pcr_no_of_cycles_32 ? 'checked' : '' }}">{{ $form->pcr_no_of_cycles_32 ? '✓' : '' }}</span> 32
            </div>
        </td>
    </tr>
    <tr>
        <th>PCR Kit Used</th>
        <td colspan="3">
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->pcr_kit_used_identifiler_plus ? 'checked' : '' }}">{{ $form->pcr_kit_used_identifiler_plus ? '✓' : '' }}</span> Identifiler Plus
            </div>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->pcr_kit_used_globalfiler ? 'checked' : '' }}">{{ $form->pcr_kit_used_globalfiler ? '✓' : '' }}</span> Globalfiler
            </div>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->pcr_kit_used_yfiler_plus ? 'checked' : '' }}">{{ $form->pcr_kit_used_yfiler_plus ? '✓' : '' }}</span> Yfiler Plus
            </div>
        </td>
    </tr>
    <tr>
        <th>PCR Remarks</th>
        <td colspan="3">{{ $form->pcr_remarks }}</td>
    </tr>
    <tr>
        <th>Injection Date</th>
        <td>{{ $form->injection_date ? \Carbon\Carbon::parse($form->injection_date)->format('d/m/Y') : '' }}</td>
        <th>Run ID</th>
        <td>{{ $form->injection_run_id }}</td>
    </tr>
    <tr>
        <th>Injection Instrument</th>
        <td>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->injection_instrument_3500 ? 'checked' : '' }}">{{ $form->injection_instrument_3500 ? '✓' : '' }}</span> 3500 Genetic Analyzer
            </div>
        </td>
        <th>Controls & Ladder</th>
        <td>
            <strong style="color: #475569;">+ve:</strong> {{ $form->injection_result_positive }} &nbsp;&nbsp;|&nbsp;&nbsp; 
            <strong style="color: #475569;">-ve:</strong> {{ $form->injection_result_negative }} &nbsp;&nbsp;|&nbsp;&nbsp; 
            <strong style="color: #475569;">Ladder:</strong> {{ $form->injection_result_ladder }} &nbsp;&nbsp;|&nbsp;&nbsp; 
            <strong style="color: #475569;">Blank:</strong> {{ $form->injection_result_blank }}
        </td>
    </tr>
    <tr>
        <th>Injection Run Result</th>
        <td colspan="3">{{ $form->injection_run }}</td>
    </tr>
</table>

<!-- 5. REPORTING & REVIEW -->
<div class="section-title">5. Reporting & Review</div>
<table class="data-table">
    <tr>
        <th>Draft Report Date</th>
        <td>{{ $form->reporting_draft_report_date ? \Carbon\Carbon::parse($form->reporting_draft_report_date)->format('d/m/Y') : '' }}</td>
        <th>Review Status</th>
        <td>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->reporting_reviewed ? 'checked' : '' }}">{{ $form->reporting_reviewed ? '✓' : '' }}</span> Reviewed
            </div>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->reporting_corrected ? 'checked' : '' }}">{{ $form->reporting_corrected ? '✓' : '' }}</span> Corrected
            </div>
        </td>
    </tr>
    <tr>
        <th>Attachments Included</th>
        <td colspan="3">
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->reporting_attachment_real_time_data ? 'checked' : '' }}">{{ $form->reporting_attachment_real_time_data ? '✓' : '' }}</span> Real Time Data
            </div>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->reporting_attachment_converge ? 'checked' : '' }}">{{ $form->reporting_attachment_converge ? '✓' : '' }}</span> Converge
            </div>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->reporting_attachment_statistical_analysis ? 'checked' : '' }}">{{ $form->reporting_attachment_statistical_analysis ? '✓' : '' }}</span> Statistical Analysis
            </div>
        </td>
    </tr>
    <tr>
        <th>Reporting Remarks</th>
        <td colspan="3">{{ $form->reporting_remarks }}</td>
    </tr>
    <tr>
        <th>Manager's Review</th>
        <td>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->manager_review_technical ? 'checked' : '' }}">{{ $form->manager_review_technical ? '✓' : '' }}</span> Technical
            </div>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->manager_review_administrative ? 'checked' : '' }}">{{ $form->manager_review_administrative ? '✓' : '' }}</span> Administrative
            </div>
        </td>
        <th>Manager's Comments</th>
        <td>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->manager_comments_verified ? 'checked' : '' }}">{{ $form->manager_comments_verified ? '✓' : '' }}</span> Verified
            </div>
            <div class="checkbox-group">
                <span class="checkbox-box {{ $form->manager_comments_not_verified ? 'checked' : '' }}">{{ $form->manager_comments_not_verified ? '✓' : '' }}</span> Not Verified
            </div>
        </td>
    </tr>
    <tr>
        <th>Manager Review Date</th>
        <td>{{ $form->manager_date ? \Carbon\Carbon::parse($form->manager_date)->format('d/m/Y') : '' }}</td>
        <th>Manager Name</th>
        <td>{{ $form->manager_name }}</td>
    </tr>
</table>

<!-- SIGNATURES -->
<table class="signature-table">
    <tr>
        <td>
            <div class="signature-label">Analyst Review</div>
            <div class="signature-line">
                Analyst Signature &amp; Date
            </div>
        </td>
        <td>
            <div class="signature-label">Manager Verification</div>
            <div class="signature-line">
                {{ $form->manager_name ?: 'Manager' }} Signature &amp; Date
            </div>
        </td>
    </tr>
</table>

<div class="footer">
    Generated via GCLA System &bull; {{ now()->format('Y-m-d H:i') }} &bull; Confidential Page 1 of 1
</div>

</body>
</html>
