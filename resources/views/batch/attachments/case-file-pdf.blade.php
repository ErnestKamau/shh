<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case File Review Form</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 18px; text-transform: uppercase; letter-spacing: 1px; }
        .section-title { background: #f4f4f4; padding: 5px 10px; font-weight: bold; border: 1px solid #ddd; margin-top: 15px; margin-bottom: 5px; font-size: 13px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #fafafa; width: 30%; font-weight: normal; color: #555; }
        .checkbox-group { display: inline-block; margin-right: 15px; }
        .checkbox-box { display: inline-block; width: 12px; height: 12px; border: 1px solid #000; margin-right: 5px; vertical-align: middle; text-align: center; line-height: 12px; font-size: 10px; font-weight: bold;}
        .signature-line { border-bottom: 1px solid #000; display: inline-block; width: 200px; margin-left: 10px; }
        .footer { position: fixed; bottom: -20px; left: 0px; right: 0px; height: 50px; text-align: center; font-size: 10px; color: #777; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>

<div class="header">
    <h2>Case File Review Form</h2>
    <p style="margin: 5px 0 0 0;">Batch Code: <strong>{{ $batch->batch_code }}</strong></p>
</div>

<!-- 1. SAMPLE INFORMATION -->
<div class="section-title">1. Sample Information</div>
<table>
    <tr>
        <th>Lab No</th><td>{{ $form->lab_no }}</td>
        <th>File No</th><td>{{ $form->file_no }}</td>
    </tr>
    <tr>
        <th>Date In</th><td>{{ $form->date_in ? \Carbon\Carbon::parse($form->date_in)->format('d/m/Y') : '' }}</td>
        <th>Client</th><td>{{ $form->client }}</td>
    </tr>
    <tr>
        <th>No of Samples</th><td>{{ $form->no_of_samples }}</td>
        <th>Sample Condition</th>
        <td>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->sample_condition_sealed ? 'X' : '' }}</span> Sealed</div>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->sample_condition_labelled ? 'X' : '' }}</span> Labelled</div>
        </td>
    </tr>
    <tr>
        <th>Condition Remarks</th><td colspan="3">{{ $form->sample_condition_remark }}</td>
    </tr>
</table>

<!-- 2. SAMPLE SCREENING -->
<div class="section-title">2. Sample Screening</div>
<table>
    <tr>
        <th>Screening Date</th><td>{{ $form->screening_date ? \Carbon\Carbon::parse($form->screening_date)->format('d/m/Y') : '' }}</td>
        <th>Screening Method</th><td>{{ $form->screening_method }}</td>
    </tr>
    <tr>
        <th>Sample Types</th>
        <td colspan="3">
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->screening_sample_type_blood ? 'X' : '' }}</span> Blood</div>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->screening_sample_type_object_with_blood ? 'X' : '' }}</span> Object with Blood</div>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->screening_sample_type_semen ? 'X' : '' }}</span> Semen</div>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->screening_sample_type_object_with_semen ? 'X' : '' }}</span> Object with Semen</div><br><br>
            <strong>Others:</strong> {{ $form->screening_sample_type_others }}
        </td>
    </tr>
    <tr>
        <th>Results</th>
        <td colspan="3">Result 1: {{ $form->screening_results_1 }} &nbsp;&nbsp;&nbsp; Result 2: {{ $form->screening_results_2 }}</td>
    </tr>
</table>

<!-- 3. SAMPLE EXTRACTION -->
<div class="section-title">3. Sample Extraction</div>
<table>
    <tr>
        <th>Extraction Date</th><td>{{ $form->extraction_date ? \Carbon\Carbon::parse($form->extraction_date)->format('d/m/Y') : '' }}</td>
        <th>Method Used</th>
        <td>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->extraction_method_chelex ? 'X' : '' }}</span> Chelex</div>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->extraction_method_prepfiler ? 'X' : '' }}</span> Prepfiler</div><br>
            <strong>Others:</strong> {{ $form->extraction_method_other }}
        </td>
    </tr>
</table>

<!-- 4. QUANTIFICATION -->
<div class="section-title">4. Quantification</div>
<table>
    <tr>
        <th>Quantification Date</th><td>{{ $form->quantification_date ? \Carbon\Carbon::parse($form->quantification_date)->format('d/m/Y') : '' }}</td>
        <th>Cycles & Kits</th>
        <td>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->quantification_no_of_cycles_40 ? 'X' : '' }}</span> 40 Cycles</div>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->quantification_kit_used_quant_trio ? 'X' : '' }}</span> Kit: Quant Trio</div>
        </td>
    </tr>
    <tr>
        <th>Remarks</th><td colspan="3">{{ $form->quantification_remarks }}</td>
    </tr>
</table>

<!-- 5. POLYMERASE CHAIN REACTION (PCR) -->
<div class="section-title">5. Polymerase Chain Reaction (PCR)</div>
<table>
    <tr>
        <th>Amplification Date</th><td>{{ $form->pcr_amplification_date ? \Carbon\Carbon::parse($form->pcr_amplification_date)->format('d/m/Y') : '' }}</td>
        <th>No. of Cycles</th>
        <td>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->pcr_no_of_cycles_28 ? 'X' : '' }}</span> 28</div>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->pcr_no_of_cycles_29 ? 'X' : '' }}</span> 29</div>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->pcr_no_of_cycles_30 ? 'X' : '' }}</span> 30</div>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->pcr_no_of_cycles_32 ? 'X' : '' }}</span> 32</div>
        </td>
    </tr>
    <tr>
        <th>Kit Used</th>
        <td colspan="3">
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->pcr_kit_used_identifiler_plus ? 'X' : '' }}</span> Identifiler Plus</div>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->pcr_kit_used_globalfiler ? 'X' : '' }}</span> Globalfiler</div>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->pcr_kit_used_yfiler_plus ? 'X' : '' }}</span> Yfiler Plus</div>
        </td>
    </tr>
    <tr>
        <th>Remarks</th><td colspan="3">{{ $form->pcr_remarks }}</td>
    </tr>
</table>

<!-- 6. INJECTION & INTERPRETATION -->
<div class="section-title">6. Injection & Interpretation</div>
<table>
    <tr>
        <th>Injection Date</th><td>{{ $form->injection_date ? \Carbon\Carbon::parse($form->injection_date)->format('d/m/Y') : '' }}</td>
        <th>Run ID</th><td>{{ $form->injection_run_id }}</td>
    </tr>
    <tr>
        <th>Instrument</th>
        <td colspan="3"><div class="checkbox-group"><span class="checkbox-box">{{ $form->injection_instrument_3500 ? 'X' : '' }}</span> 3500</div></td>
    </tr>
    <tr>
        <th>Results (Pass/Fail)</th>
        <td colspan="3">
            +ve Ctrl: {{ $form->injection_result_positive }} &nbsp;&nbsp;|&nbsp;&nbsp;
            -ve Ctrl: {{ $form->injection_result_negative }} &nbsp;&nbsp;|&nbsp;&nbsp;
            Ladder: {{ $form->injection_result_ladder }} &nbsp;&nbsp;|&nbsp;&nbsp;
            Blank: {{ $form->injection_result_blank }}
        </td>
    </tr>
    <tr>
        <th>Overall Run Result</th><td colspan="3">{{ $form->injection_run }}</td>
    </tr>
</table>

<!-- 7. REPORTING -->
<div class="section-title">7. Reporting</div>
<table>
    <tr>
        <th>Draft Report Date</th><td>{{ $form->reporting_draft_report_date ? \Carbon\Carbon::parse($form->reporting_draft_report_date)->format('d/m/Y') : '' }}</td>
        <th>Review Status</th>
        <td>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->reporting_reviewed ? 'X' : '' }}</span> Reviewed</div>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->reporting_corrected ? 'X' : '' }}</span> Corrected</div>
        </td>
    </tr>
    <tr>
        <th>Attachments</th>
        <td colspan="3">
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->reporting_attachment_real_time_data ? 'X' : '' }}</span> Real Time Data</div>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->reporting_attachment_converge ? 'X' : '' }}</span> Converge</div>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->reporting_attachment_statistical_analysis ? 'X' : '' }}</span> Statistical Analysis</div>
        </td>
    </tr>
    <tr>
        <th>Reporting Remarks</th><td colspan="3">{{ $form->reporting_remarks }}</td>
    </tr>
</table>

<!-- 8. MANAGER'S REVIEW & VERIFICATION -->
<div class="section-title">8. Manager's Review & Verification</div>
<table>
    <tr>
        <th>Review Type</th>
        <td>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->manager_review_technical ? 'X' : '' }}</span> Technical Review</div>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->manager_review_administrative ? 'X' : '' }}</span> Administrative Review</div>
        </td>
        <th>Comments</th>
        <td>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->manager_comments_verified ? 'X' : '' }}</span> Verified</div>
            <div class="checkbox-group"><span class="checkbox-box">{{ $form->manager_comments_not_verified ? 'X' : '' }}</span> Not Verified</div>
        </td>
    </tr>
    <tr>
        <th>Manager Date</th><td>{{ $form->manager_date ? \Carbon\Carbon::parse($form->manager_date)->format('d/m/Y') : '' }}</td>
        <th>Manager Name</th><td>{{ $form->manager_name }}</td>
    </tr>
</table>

<div style="margin-top: 40px; width: 100%;">
    <table style="border: none;">
        <tr>
            <td style="border: none; width: 50%;">
                <strong>Analyst Signature:</strong><br><br><br>
                ______________________________
            </td>
            <td style="border: none; width: 50%; text-align: right;">
                <strong>Manager Signature:</strong><br><br><br>
                ______________________________
            </td>
        </tr>
    </table>
</div>

<div class="footer">
    Generated via GCLA System &bull; {{ now()->format('Y-m-d H:i') }}
</div>

</body>
</html>
