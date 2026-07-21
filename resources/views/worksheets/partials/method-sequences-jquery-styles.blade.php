<style>
    .sequence-info-card {
        background: #f8f9fa;
        border-left: 4px solid var(--color-primary, #28a745);
        padding: 10px 12px;
        margin-bottom: 0.85rem;
        border-radius: 8px;
    }
    /* Compact run items for many steps */
    .run-item {
        margin-bottom: 10px;
    }

    .run-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 8px 12px;
        cursor: pointer;
        border-radius: 8px;
        margin-bottom: 8px;
        transition: background 0.15s ease, box-shadow 0.15s ease;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    .run-header:hover {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        transform: none;
    }

    /* Custom isolated table styling to avoid DataTable auto-init */
    .polucon-custom-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
        background-color: transparent;
        font-size: 0.8125rem;
    }
    .polucon-custom-table th, .polucon-custom-table td {
        padding: 0.55rem 0.7rem;
        vertical-align: middle;
        border: 1px solid #dee2e6;
    }
    .polucon-custom-table thead th {
        vertical-align: bottom;
        border-bottom: 2px solid #dee2e6;
        background-color: #f8f9fa;
        font-weight: 600;
        font-size: 0.6875rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #64748b;
        text-align: left;
    }
    .polucon-custom-table tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.05);
    }
    .polucon-custom-table tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.075);
    }

    .run-title {
        color: #1f2937;
        font-size: 0.8125rem;
        letter-spacing: 0.2px;
    }

    .run-samples-inline {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        margin-left: 4px;
    }

    .run-samples-wrapper {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        cursor: default;
    }

    .run-sample-chip {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #334155;
        border-radius: 999px;
        padding: 2px 8px;
        font-size: 12px;
        font-weight: 600;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .run-sample-more {
        color: #475569;
        font-size: 12px;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.15);
        border: 1px dashed rgba(148, 163, 184, 0.65);
        cursor: help;
    }

    .run-created-meta {
        font-size: 12px;
        line-height: 1.2;
    }

    .run-status-meta {
        font-size: 12px;
        line-height: 1.2;
        margin-top: 2px;
    }

    .run-status-pill,
    .run-result-pill {
        font-size: 12px;
        font-weight: 700;
        padding: 6px 10px;
        border-radius: 999px;
        letter-spacing: 0.2px;
    }

    .run-result-pill {
        background: rgba(14, 165, 233, 0.12);
        border: 1px solid rgba(14, 165, 233, 0.35);
        color: #0369a1;
    }
    .run-body {
        padding: 14px;
        border: 1px solid #dee2e6;
        border-top: none;
        margin-bottom: 12px;
        background: white;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .stage-row {
        padding: 10px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .stage-row:hover {
        background: #f8f9fa;
    }
    .stage-details {
        padding: 15px;
        background: #f8f9fa;
        margin-top: 10px;
        border-radius: 4px;
    }
    .timer-badge {
        font-size: 0.75rem;
        padding: 3px 8px;
    }
    .sample-badge {
        background: #17a2b8;
        color: white;
        padding: 2px 6px;
        border-radius: 999px;
        font-size: 0.6875rem;
        margin-left: 8px;
    }
    
    /* Modern Sequence Info Card - Updated to White Theme */
    .sequence-info-card-modern {
        background: #ffffff;
        color: #334155;
        border-radius: 10px;
        padding: 10px 12px;
        margin-bottom: 0.85rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
        position: relative;
        overflow: hidden;
    }
    
    .sequence-info-card-modern::before {
        display: none; /* Remove overlay */
    }
    
    .sequence-info-card-modern > * {
        position: relative;
        z-index: 1;
    }

    .info-items {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        flex: 1;
        flex-wrap: wrap;
        gap: 4px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        text-align: center;
        min-width: 90px;
    }

    .info-item:first-child {
        padding-left: 0;
    }

    .info-item i {
        font-size: 1.25rem;
        margin-bottom: 4px;
        filter: none;
    }

    .info-label {
        font-size: 0.625rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
    }

    .info-value {
        font-size: 0.6875rem;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .sequence-info-card-modern .mdi-chevron-right {
        font-size: 1rem;
        color: #cbd5e1;
        display: flex;
        align-items: center;
        margin: 0 4px;
    }

    .create-run-btn {
        border-radius: 6px;
        padding: 0 14px;
        height: 34px;
        font-weight: 600;
        text-transform: none;
        letter-spacing: 0;
        font-size: 0.75rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
        transition: background 0.15s ease, box-shadow 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .create-run-btn:hover {
        transform: none;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    #sequence-tabs {
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 0.85rem;
    }

    #sequence-tabs .nav-link {
        border: none;
        color: #64748b;
        font-weight: 600;
        font-size: 0.8125rem;
        padding: 8px 12px;
        border-bottom: 2px solid transparent;
        transition: color 0.15s ease, border-color 0.15s ease;
    }

    #sequence-tabs .nav-link:hover {
        color: var(--color-primary, #28a745);
        background: transparent;
    }

    #sequence-tabs .nav-link.active {
        color: var(--color-primary, #28a745);
        background: transparent;
        border-bottom: 2px solid var(--color-primary, #28a745);
    }

    @media (max-width: 768px) {
        .info-items {
            flex-direction: column;
        }
        .sequence-info-card-modern .mdi-chevron-right {
            transform: rotate(90deg);
            margin: 15px 0;
        }
        .info-item {
            padding: 10px 0;
        }
    }
    
    /* Stage Details Stepper Form Styles — compact density */
    .stage-details-stepper-form {
        background: #fff;
        padding: 10px 12px;
        font-size: 0.8125rem;
    }

    /* Stepper Header Styling */
    .stepper-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        max-width: 560px;
        margin: 0 auto 1rem;
    }

    .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        z-index: 2;
        cursor: pointer;
        opacity: 0.5;
        transition: opacity 0.15s ease, color 0.15s ease;
    }

    .step-item.active {
        opacity: 1;
    }

    .step-circle {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #e9ecef;
        color: #495057;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.75rem;
        margin-bottom: 4px;
        border: 2px solid transparent;
        transition: background 0.15s ease, box-shadow 0.15s ease;
    }

    .step-item.active .step-circle {
        background: #28a745;
        color: #fff;
        box-shadow: 0 2px 6px rgba(40, 167, 69, 0.25);
    }

    .step-label {
        font-size: 0.625rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        color: #6c757d;
        text-transform: uppercase;
    }

    .step-item.active .step-label {
        color: #28a745;
    }

    .step-line {
        flex: 1;
        height: 2px;
        background: #e9ecef;
        margin-top: -18px;
        margin-left: 6px;
        margin-right: 6px;
        z-index: 1;
    }

    /* Pane visibility */
    .step-pane {
        display: none;
        animation: fadeIn 0.25s ease;
    }

    .step-pane.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(3px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .equipment-card {
        transition: box-shadow 0.15s ease;
        padding: 10px 12px !important;
        border-radius: 8px !important;
    }

    .equipment-card .equipment-name {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.8125rem !important;
        margin-bottom: 0.35rem !important;
    }

    .equipment-card .equipment-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .equipment-card .equipment-meta-block {
        min-width: 120px;
    }

    .info-card {
        transition: box-shadow 0.15s ease;
        padding: 10px 12px !important;
        border-radius: 8px !important;
    }

    .info-card:hover {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08) !important;
    }

    .info-card textarea[readonly] {
        cursor: default;
        border: 1px solid #d0d7e7;
        font-size: 0.8125rem;
        min-height: 64px;
        padding: 0.4rem 0.65rem;
    }

    .info-card input[readonly] {
        background-color: #f1f5f9;
        border: 1px solid #d0d7e7;
        min-height: 34px;
        font-size: 0.8125rem;
        padding: 0.35rem 0.65rem;
    }
    
    .stage-details-stepper-form .form-label,
    .registration-panel-premium .form-label,
    .media-registration-panel .form-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.3rem;
        display: block;
    }
    
    .stage-details-stepper-form .form-control,
    .registration-panel-premium .form-control,
    .media-registration-panel .form-control {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 0.35rem 0.65rem;
        font-size: 0.8125rem;
        min-height: 34px;
        height: auto;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .stage-details-stepper-form .form-control:focus,
    .registration-panel-premium .form-control:focus,
    .media-registration-panel .form-control:focus {
        border-color: var(--color-primary, #28a745);
        box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.18);
    }
    
    .stage-details-stepper-form .form-control[readonly],
    .registration-panel-premium .form-control[readonly] {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }
    
    .solution-group {
        margin-bottom: 0.75rem;
        padding: 10px 12px;
        background: #ffffff;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .group-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .group-label i {
        color: #28a745;
        font-size: 0.875rem;
    }
    
    .select2-multiple {
        width: 100% !important;
    }
    
    .form-actions {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        padding-top: 0.75rem;
        border-top: 1px solid #e9ecef;
        margin-top: 0.75rem;
    }
    
    .stage-details-stepper-form .btn:not(.btn-sm),
    .registration-panel-premium .btn:not(.btn-sm),
    .media-registration-panel .btn:not(.btn-sm) {
        border-radius: 6px;
        padding: 0.3rem 0.75rem !important;
        font-size: 0.75rem !important;
        font-weight: 600;
        min-height: 32px !important;
        height: auto !important;
        line-height: 1.2;
        transition: background 0.15s ease, box-shadow 0.15s ease;
    }

    .stage-details-stepper-form .btn-sm,
    .stage-card .btn-sm,
    .stage-actions .btn-sm {
        min-height: 28px !important;
        height: 28px !important;
        padding: 0.2rem 0.55rem !important;
        font-size: 0.6875rem !important;
        font-weight: 600;
        border-radius: 6px;
        line-height: 1.2;
    }

    .stage-actions .btn-sm i {
        font-size: 0.8125rem;
    }
    
    /* Step titles / descriptions inside stage steppers */
    .stage-details-stepper-form .step-title,
    .stage-details-stepper-form h4.step-title {
        font-size: 0.875rem !important;
        font-weight: 700 !important;
        color: #1e293b;
        line-height: 1.3;
        margin-bottom: 0.15rem !important;
    }

    .stage-details-stepper-form .step-description {
        font-size: 0.75rem !important;
        color: #64748b !important;
        line-height: 1.4;
        margin-bottom: 0.75rem !important;
    }

    .stage-details-stepper-form .stepper-header.mb-4 {
        margin-bottom: 0.75rem !important;
    }

    .stage-details-stepper-form .mb-4 {
        margin-bottom: 0.75rem !important;
    }

    .stage-details-stepper-form .mt-4 {
        margin-top: 0.75rem !important;
    }

    .stage-details-stepper-form .p-4,
    .stage-details-stepper-form .diluent-entry-header.p-4 {
        padding: 0.65rem 0.75rem !important;
    }

    .stage-details-stepper-form .px-4 {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }

    .stage-details-stepper-form .pb-4 {
        padding-bottom: 0.75rem !important;
    }

    .stage-details-stepper-form .py-2 {
        padding-top: 0.3rem !important;
        padding-bottom: 0.3rem !important;
    }

    .stage-details-stepper-form .px-5,
    .stage-details-stepper-form .btn.px-5 {
        padding-left: 0.85rem !important;
        padding-right: 0.85rem !important;
    }

    .stage-details-stepper-form .form-group {
        margin-bottom: 0.65rem;
    }

    .stage-details-stepper-form .form-group label,
    .stage-details-stepper-form .form-group .form-label {
        font-size: 0.75rem !important;
        margin-bottom: 0.3rem !important;
    }

    .stage-details-stepper-form .stepper-actions {
        gap: 8px;
        padding-top: 0.65rem;
        border-top: 1px solid #e2e8f0;
    }

    .stage-details-stepper-form .registration-panel-premium .rounded-circle {
        width: 22px !important;
        height: 22px !important;
        font-size: 0.75rem;
    }

    .stage-details-stepper-form .badge.p-2 {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.6875rem !important;
        font-weight: 600;
    }

    .stage-details-stepper-form small,
    .stage-details-stepper-form .text-muted,
    .stage-details-stepper-form .text-info {
        font-size: 0.6875rem !important;
    }

    /* Results capture cards / tables */
    .stage-details-stepper-form .card,
    #solution-results-container .card,
    #sample-results-container .card {
        padding: 0.65rem 0.75rem !important;
        border-radius: 8px !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
        margin-bottom: 0.75rem !important;
    }

    .stage-details-stepper-form .card h5,
    #solution-results-container .card h5,
    #sample-results-container .card h5 {
        font-size: 0.8125rem !important;
        font-weight: 700 !important;
        margin-bottom: 0.5rem !important;
    }

    .stage-details-stepper-form .table th,
    #solution-results-container .table th,
    #sample-results-container .table th {
        padding: 0.4rem 0.55rem !important;
        font-size: 0.6875rem !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #64748b !important;
    }

    .stage-details-stepper-form .table td,
    #solution-results-container .table td,
    #sample-results-container .table td {
        padding: 0.4rem 0.55rem !important;
        font-size: 0.8125rem !important;
        vertical-align: middle !important;
    }

    .stage-details-stepper-form .table .form-control-sm,
    #solution-results-container .table .form-control-sm,
    #sample-results-container .table .form-control-sm {
        min-height: 30px;
        height: 30px;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem !important;
    }

    /* Compact Select2 inside steppers */
    .stage-details-stepper-form .select2-container .select2-selection--single {
        height: 34px !important;
        min-height: 34px !important;
        border-radius: 6px !important;
        border-color: #ced4da !important;
    }

    .stage-details-stepper-form .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 32px !important;
        font-size: 0.8125rem !important;
        padding-left: 0.65rem !important;
    }

    .stage-details-stepper-form .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 32px !important;
    }

    .stage-details-stepper-form .input-group-text {
        font-size: 0.75rem;
        padding: 0.3rem 0.55rem;
        min-height: 34px;
    }

    .step-info-badge.completed {
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }

    .step-info-badge.overdue {
        background: rgba(239, 68, 68, 0.1);
        color: #b91c1c;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    /* Results Table Styles */
    .results-table {
        background: white;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid #e9ecef;
    }
    
    .results-table table {
        margin: 0;
        width: 100%;
    }
    
    .results-table th {
        background: #f8f9fa;
        padding: 0.4rem 0.55rem;
        font-size: 0.6875rem;
        font-weight: 600;
        color: #64748b;
        border-bottom: 1px solid #e9ecef;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    
    .results-table td {
        padding: 0.4rem 0.55rem;
        font-size: 0.8125rem;
        border-bottom: 1px solid #f1f3f4;
    }
    
    .results-table tr:hover td {
        background: #f8f9fa;
    }
    
    .remark-select {
        min-width: 120px;
    }
    
    .result-input {
        min-width: 150px;
    }
    
    .media-controls-section {
        margin-top: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 6px;
        border: 1px solid #e9ecef;
    }
    
    .media-controls-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-top: 10px;
    }
    
    .media-control-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        background: white;
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }
    
    .media-control-label {
        font-size: 13px;
        font-weight: 500;
        color: #495057;
        min-width: 120px;
    }
    
    .media-control-select {
        flex: 1;
    }
    
    /* Compact timeline for many steps */
    .stages-timeline {
        position: relative;
        padding: 12px 0;
    }

    .timeline-item {
        display: flex;
        margin-bottom: 16px;
        position: relative;
    }

    .timeline-marker {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-right: 12px;
    }

    .timeline-circle {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.75rem;
        color: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        z-index: 2;
    }

    .timeline-circle.pending {
        background: linear-gradient(135deg, #6c757d, #495057);
    }

    .timeline-circle.running {
        background: linear-gradient(135deg, #28a745, #20c997);
        animation: pulse 2s infinite;
    }

    .timeline-circle.completed {
        background: linear-gradient(135deg, #28a745, #0056b3);
    }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 4px 10px rgba(40, 167, 69, 0.4); }
        50% { box-shadow: 0 4px 20px rgba(40, 167, 69, 0.8); }
    }

    .timeline-line {
        width: 2px;
        flex-grow: 1;
        background: linear-gradient(to bottom, #dee2e6, #adb5bd);
        margin-top: 8px;
        min-height: 32px;
    }

    .timeline-content {
        flex: 1;
        padding-top: 2px;
    }

    .stage-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 10px 12px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        transition: box-shadow 0.15s ease;
    }

    .stage-card:hover {
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        transform: none;
    }

    .stage-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        padding: 4px 0 8px;
        border-bottom: 1px solid #e2e8f0 !important;
    }

    .stage-info {
        flex: 1;
        min-width: 180px;
    }

    .stage-title {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.3;
    }

    .stage-info > .text-muted,
    .stage-info > small.text-muted {
        font-size: 0.7rem !important;
    }

    .stage-status {
        display: flex;
        align-items: center;
    }

    .stage-actions {
        display: flex;
        gap: 4px;
        align-items: center;
        flex-wrap: wrap;
    }
    
    /* Compact Stage Details */
    .stage-details-modern {
        margin-top: 8px;
        padding: 10px 12px;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        box-shadow: none;
    }

    .info-section {
        padding: 8px 0;
        border-bottom: 1px solid #e9ecef;
        position: relative;
    }

    .info-section:last-child {
        border-bottom: none;
    }

    .info-section::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 2px;
        height: 16px;
        background: linear-gradient(to bottom, #28a745, #0056b3);
        border-radius: 2px;
    }

    .info-row {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        font-size: 13px;
    }

    .info-row i {
        font-size: 14px;
        width: 18px;
        text-align: center;
    }

    .info-row .info-label {
        font-weight: 600;
        color: #495057;
        min-width: 70px;
        font-size: 0.75rem;
    }

    .info-row .info-value {
        color: #6c757d;
        flex: 1;
        font-size: 0.8125rem;
    }

    .info-row.remarks {
        margin-top: 8px;
        padding-left: 20px;
        font-style: italic;
    }

    /* Compact step info badges */
    .step-info-badges {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 6px;
    }

    .step-info-badge {
        font-size: 0.6875rem;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        line-height: 1.2;
    }

    .step-info-badge.result-step {
        background: rgba(59, 130, 246, 0.12);
        color: #1e40af;
        border: 1px solid rgba(59, 130, 246, 0.3);
    }

    .step-info-badge.started {
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }

    .step-info-badge.not-started {
        background: rgba(156, 163, 175, 0.12);
        color: #4b5563;
        border: 1px solid rgba(156, 163, 175, 0.3);
    }

    .step-info-badge.time {
        background: rgba(251, 146, 60, 0.12);
        color: #c2410c;
        border: 1px solid rgba(251, 146, 60, 0.3);
    }

    .step-info-badge.user {
        background: rgba(163, 230, 53, 0.12);
        color: #365314;
        border: 1px solid rgba(163, 230, 53, 0.3);
    }
    
    /* Post Results Modal Styles */
    .limit-group {
        margin-bottom: 8px;
    }
    
    .limit-group label {
        display: block;
        margin-bottom: 2px;
        font-weight: 600;
    }
    
    .limit-group input {
        display: inline-block;
        width: 48%;
        margin-right: 2%;
    }
    
    .limit-group input:last-child {
        margin-right: 0;
    }


    
    .remark-cell {
        text-align: center;
    }
    
    .standard-limit-input {
        font-size: 12px;
    }

    /* Redesigned Control Step Styles */
    .step-header-container {
        border-bottom: 2px solid #f1f5f9;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
    }

    .status-badge-validated {
        background: #dcfce7;
        color: #15803d;
        font-weight: 600;
        font-size: 0.6875rem;
        padding: 2px 8px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        box-shadow: none;
    }

    .status-badge-validated i {
        font-size: 0.5rem;
        margin-right: 4px;
    }

    .timer-badge {
        font-size: 0.6875rem !important;
        padding: 2px 8px !important;
        font-weight: 600;
        border-radius: 999px;
        line-height: 1.25;
    }

    .section-title-with-icon {
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 700;
        font-size: 0.875rem;
        color: #334155;
        margin-bottom: 0.75rem;
    }

    .section-title-with-icon i {
        color: #28a745;
        font-size: 1rem;
    }

    .control-card-premium {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 12px;
        position: relative;
        transition: box-shadow 0.15s ease;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }

    .control-card-premium:hover {
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        transform: none;
    }

    .control-type-label {
        font-size: 0.625rem;
        font-weight: 700;
        color: #28a745;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
    }

    .control-name-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }

    .control-meta-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px 12px;
        margin-bottom: 0.5rem;
    }

    .control-meta-grid > div:last-child {
        text-align: right;
    }

    .meta-label {
        font-size: 0.625rem;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 1px;
    }

    .meta-value {
        font-size: 0.75rem;
        font-weight: 600;
        color: #334155;
    }

    .verified-badge {
        color: #15803d;
        font-size: 0.6875rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .ref-number {
        font-size: 0.6875rem;
        color: #94a3b8;
        font-weight: 500;
    }

    .add-more-dashed-box {
        border: 1px dashed #e2e8f0;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 110px;
        color: #94a3b8;
        cursor: pointer;
        transition: border-color 0.15s ease, background 0.15s ease;
        background: #fcfdfe;
        font-size: 0.75rem;
        padding: 10px;
    }

    .add-more-dashed-box:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        color: #64748b;
    }

    .add-more-dashed-box i {
        font-size: 1.25rem;
        margin-bottom: 6px;
        color: #cbd5e1;
    }

    .registration-panel-premium {
        background: #f8faff;
        border: 1px solid #eef2ff;
        border-radius: 10px;
        padding: 12px 14px;
    }

    .registration-panel-premium h5 {
        font-size: 0.875rem !important;
        font-weight: 700 !important;
        margin-bottom: 0 !important;
    }

    .premium-input-group {
        margin-bottom: 0.65rem;
    }

    .premium-input-group label {
        font-weight: 600;
        color: #475569;
        font-size: 0.75rem;
        margin-bottom: 0.3rem;
    }

    .premium-input {
        background: #edeff5 !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 6px !important;
        padding: 0.35rem 0.65rem !important;
        font-size: 0.8125rem !important;
        font-weight: 500 !important;
        color: #334155 !important;
        min-height: 34px !important;
        height: 34px !important;
        box-shadow: none !important;
    }

    .premium-input::placeholder {
        color: #94a3b8 !important;
        font-size: 0.8125rem !important;
    }

    .register-btn-premium {
        background: var(--color-primary, #0061e0);
        color: white;
        border: none;
        border-radius: 6px;
        padding: 0.4rem 0.85rem;
        font-weight: 600;
        font-size: 0.8125rem;
        min-height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
        transition: background 0.15s ease, box-shadow 0.15s ease;
    }

    .register-btn-premium:hover {
        background: var(--color-primary-hover, #0056c7);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transform: none;
        color: #fff;
    }

    .registration-helper-text {
        font-size: 0.6875rem;
        color: #94a3b8;
        line-height: 1.4;
        margin-top: 0.65rem;
        text-align: center;
    }

    /* Navigation Buttons Redesign — compact */
    .btn-nav-back {
        background: #ffffff !important;
        color: #1e293b !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 6px !important;
        padding: 0.3rem 0.75rem !important;
        font-weight: 600 !important;
        font-size: 0.75rem !important;
        min-height: 30px !important;
        height: 30px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        box-shadow: none !important;
        line-height: 1.2 !important;
    }

    .btn-nav-back:hover {
        background: #f8faff !important;
        border-color: #cbd5e1 !important;
    }

    .btn-nav-continue {
        background: var(--color-primary, #0066ff) !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 6px !important;
        padding: 0.3rem 0.85rem !important;
        font-weight: 600 !important;
        font-size: 0.75rem !important;
        min-height: 30px !important;
        height: 30px !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08) !important;
        line-height: 1.2 !important;
    }

    .btn-nav-continue:hover {
        background: var(--color-primary-hover, #0052cc) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12) !important;
    }

    /* Media Card Styles */
    .media-card-premium {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 12px;
        position: relative;
        transition: box-shadow 0.15s ease;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        min-height: 0;
        display: flex;
        flex-direction: column;
    }

    .media-card-premium:hover {
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        transform: none;
    }

    .status-badge-verified {
        background: #dcfce7;
        color: #15803d;
        font-weight: 700;
        font-size: 0.625rem;
        padding: 2px 8px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 6px;
    }

    .verified-badge-footer {
        background: #f8fafc;
        color: #64748b;
        font-weight: 700;
        font-size: 0.625rem;
        padding: 4px 8px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border: 1px solid #e2e8f0;
    }

    .media-name-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.65rem;
        line-height: 1.3;
    }

    .media-meta-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 0.65rem;
        align-items: center;
    }

    .media-meta-item {
        display: flex;
        align-items: center;
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
    }

    .media-meta-label {
        color: #94a3b8;
        text-transform: uppercase;
        font-size: 9px;
        margin-right: 4px;
    }

    .media-meta-value {
        color: #334155;
    }

    .media-meta-divider {
        color: #e2e8f0;
        margin: 0 4px;
        font-weight: 300;
    }

    .media-card-actions {
        display: flex;
        gap: 6px;
        align-items: center;
        border-top: 1px solid #f1f5f9;
        padding-top: 8px;
    }

    .btn-edit-entry {
        flex: 1;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: var(--color-primary, #0061e0);
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.3rem 0.5rem;
        min-height: 30px;
        border-radius: 6px;
        transition: background 0.15s ease, border-color 0.15s ease;
    }

    .btn-edit-entry:hover {
        background: #f8faff;
        border-color: var(--color-primary, #0061e0);
    }

    .media-registration-panel {
        background: #f8faff;
        border: 1px solid #eef2ff;
        border-radius: 10px;
        padding: 12px 14px;
        margin-top: 0.85rem;
    }

    .media-registration-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        color: #1e293b;
        font-size: 0.875rem;
        margin-bottom: 0.75rem;
    }

    .media-registration-title::before {
        content: '';
        width: 3px;
        height: 14px;
        background: var(--color-primary, #0061e0);
        border-radius: 2px;
    }

    .placeholder-card-dashed {
        border: 1px dashed #e2e8f0;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 120px;
        color: #94a3b8;
        background: #fcfdfe;
        text-align: center;
        padding: 12px;
    }

    .placeholder-icon-container {
        width: 36px;
        height: 36px;
        background: #f1f5f9;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 8px;
    }

    .placeholder-icon-container i {
        font-size: 1.1rem;
        color: #94a3b8;
    }

    .placeholder-text-main {
        font-weight: 600;
        color: #64748b;
        font-size: 0.8125rem;
        margin-bottom: 2px;
    }

    .placeholder-text-sub {
        font-size: 0.6875rem;
        color: #94a3b8;
    }

    /* Post Results modal — compact density */
    #post-results-modal .post-results-modal-dialog {
        max-width: 96%;
        width: 96%;
        margin: 1.25rem auto;
    }

    @media (min-width: 1400px) {
        #post-results-modal .post-results-modal-dialog {
            max-width: 1320px;
            width: 96%;
        }
    }

    #post-results-modal .modal-header .close {
        opacity: 1;
        text-shadow: none;
    }

    #post-results-modal .modal-body .form-label {
        margin-bottom: 0.3rem;
        font-size: 0.75rem;
    }

    #post-results-modal #post-results-table thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        font-size: 0.6875rem;
        padding: 0.4rem 0.5rem;
        vertical-align: middle;
        border-color: #dee2e6;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #64748b;
    }

    #post-results-modal #post-results-table tbody td {
        padding: 0.4rem 0.5rem;
        font-size: 0.8125rem;
        vertical-align: middle;
        border-color: #dee2e6;
    }

    #post-results-modal #post-results-table tbody tr.post-result-row td:nth-child(2) {
        font-weight: 600;
    }

    #post-results-modal #post-results-table .form-control-sm {
        font-size: 0.75rem;
        min-height: 30px;
        height: 30px;
        padding: 0.25rem 0.5rem;
    }

    #post-results-modal .modal-footer .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }

    #post-results-modal .modal-footer .btn-success:hover:not(:disabled) {
        background-color: #218838;
        border-color: #1e7e34;
    }

    #post-results-modal .modal-footer .btn-success:disabled {
        opacity: 0.65;
    }

    .stage-locked .save-stage-details-btn,
    .stage-locked .save-results-btn,
    .stage-locked .add-solution-item,
    .stage-locked .delete-solution-btn,
    .stage-locked .remove-solution-item,
    .stage-locked .remove-equipment-btn,
    .stage-locked .next-step-btn[data-next="6"] {
        display: none !important;
        pointer-events: none;
    }

    .stage-locked input:not([readonly]),
    .stage-locked select,
    .stage-locked textarea {
        pointer-events: none;
        background-color: #f8f9fa;
    }
</style>
