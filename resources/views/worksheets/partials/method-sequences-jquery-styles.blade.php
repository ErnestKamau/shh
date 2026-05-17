<style>
    .sequence-info-card {
        background: #f8f9fa;
        border-left: 4px solid #28a745;
        padding: 15px;
        margin-bottom: 20px;
    }
    /* Compact run items for many steps */
    .run-item {
        margin-bottom: 12px;
    }

    .run-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 10px 14px;
        cursor: pointer;
        border-radius: 8px;
        margin-bottom: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        border-bottom: 2px solid #e2e8f0 !important;
    }
    .run-header:hover {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }

    /* Custom isolated table styling to avoid DataTable auto-init */
    .polucon-custom-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
        background-color: transparent;
        font-size: 13px;
    }
    .polucon-custom-table th, .polucon-custom-table td {
        padding: 0.75rem;
        vertical-align: middle;
        border: 1px solid #dee2e6;
    }
    .polucon-custom-table thead th {
        vertical-align: bottom;
        border-bottom: 2px solid #dee2e6;
        background-color: #f8f9fa;
        font-weight: 600;
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
        font-size: 14px;
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
        font-size: 14px;
        padding: 5px 10px;
    }
    .sample-badge {
        background: #17a2b8;
        color: white;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 12px;
        margin-left: 10px;
    }
    
    /* Modern Sequence Info Card - Updated to White Theme */
    .sequence-info-card-modern {
        background: #ffffff;
        color: #334155;
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 25px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
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
    }

    .info-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 0 15px;
        text-align: center;
        min-width: 110px;
    }

    .info-item:first-child {
        padding-left: 0;
    }

    .info-item i {
        font-size: 28px;
        margin-bottom: 8px;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.05));
    }

    .info-label {
        font-size: 10px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 2px;
    }

    .info-value {
        font-size: 10px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .sequence-info-card-modern .mdi-chevron-right {
        font-size: 24px;
        color: #cbd5e1;
        display: flex;
        align-items: center;
        margin: 0 5px;
    }

    .create-run-btn {
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 13px;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
        transition: all 0.3s ease;
    }

    .create-run-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(40, 167, 69, 0.3);
    }

    #sequence-tabs {
        border-bottom: 2px solid #f1f5f9;
        margin-bottom: 20px;
    }

    #sequence-tabs .nav-link {
        border: none;
        color: #64748b;
        font-weight: 600;
        padding: 12px 20px;
        border-bottom: 2px solid transparent;
        transition: all 0.2s ease;
    }

    #sequence-tabs .nav-link:hover {
        color: #28a745;
        background: transparent;
    }

    #sequence-tabs .nav-link.active {
        color: #28a745;
        background: transparent;
        border-bottom: 2px solid #28a745;
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
    
    /* Stage Details Stepper Form Styles */
    .stage-details-stepper-form {
        background: #fff;
        padding: 20px;
    }

    /* Stepper Header Styling */
    .stepper-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        max-width: 600px;
        margin: 0 auto 30px;
    }

    .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        z-index: 2;
        cursor: pointer;
        opacity: 0.5;
        transition: all 0.3s ease;
    }

    .step-item.active {
        opacity: 1;
    }

    .step-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #e9ecef;
        color: #495057;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-bottom: 8px;
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .step-item.active .step-circle {
        background: #28a745;
        color: #fff;
        box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
    }

    .step-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: #6c757d;
    }

    .step-item.active .step-label {
        color: #28a745;
    }

    .step-line {
        flex: 1;
        height: 2px;
        background: #e9ecef;
        margin-top: -24px;
        margin-left: 10px;
        margin-right: 10px;
        z-index: 1;
    }

    /* Pane visibility */
    .step-pane {
        display: none;
        animation: fadeIn 0.4s ease;
    }

    .step-pane.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .equipment-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .equipment-card .equipment-name {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .equipment-card .equipment-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
    }

    .equipment-card .equipment-meta-block {
        min-width: 160px;
    }

    .info-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .info-card:hover {
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08) !important;
    }

    .info-card textarea[readonly] {
        cursor: default;
        border: 1px solid #d0d7e7;
    }

    .info-card input[readonly] {
        background-color: #f1f5f9;
        border: 1px solid #d0d7e7;
    }
    
    .form-label {
        font-size: 13px;
        font-weight: 500;
        color: #495057;
        margin-bottom: 5px;
        display: block;
    }
    
    .form-control {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 8px 12px;
        font-size: 14px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
    
    .form-control[readonly] {
        background-color: #f8f9fa;
        cursor: not-allowed;
    }
    
    .solution-group {
        margin-bottom: 20px;
        padding: 15px;
        background: #ffffff;
        border-radius: 6px;
        border: 1px solid #e9ecef;
    }
    
    .group-label {
        font-size: 13px;
        font-weight: 600;
        color: #495057;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .group-label i {
        color: #28a745;
        font-size: 16px;
    }
    
    .select2-multiple {
        width: 100% !important;
    }
    
    .form-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        padding-top: 20px;
        border-top: 1px solid #e9ecef;
        margin-top: 20px;
    }
    
    .btn {
        border-radius: 6px;
        padding: 8px 16px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .btn-primary {
        background: #28a745;
        border-color: #28a745;
        color: white;
    }
    
    .btn-primary:hover {
        background: #0056b3;
        border-color: #0056b3;
        transform: translateY(-1px);
    }
    
    .btn-secondary {
        background: #6c757d;
        border-color: #6c757d;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #545b62;
        border-color: #545b62;
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
        padding: 12px;
        font-size: 13px;
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #e9ecef;
    }
    
    .results-table td {
        padding: 10px 12px;
        font-size: 14px;
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
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
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
        border-radius: 6px;
        padding: 12px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }

    .stage-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }

    .stage-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 2px solid #e2e8f0 !important;
    }
        gap: 15px;
        flex-wrap: wrap;
    }

    .stage-info {
        flex: 1;
        min-width: 200px;
    }

    .stage-title {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: #2c3e50;
    }

    .stage-status {
        display: flex;
        align-items: center;
    }

    .stage-actions {
        display: flex;
        gap: 5px;
    }
    
    /* Compact Stage Details */
    .stage-details-modern {
        margin-top: 10px;
        padding: 12px;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 6px;
        border: 1px solid #e9ecef;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
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

    .info-label {
        font-weight: 600;
        color: #495057;
        min-width: 70px;
        font-size: 12px;
    }

    .info-value {
        color: #6c757d;
        flex: 1;
        font-size: 12px;
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
        font-size: 11px;
        font-weight: 600;
        padding: 3px 7px;
        border-radius: 4px;
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
        font-size: 12px;
        padding: 6px 16px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 2px 4px rgba(34, 197, 94, 0.1);
    }

    .status-badge-validated i {
        font-size: 8px;
        margin-right: 8px;
    }

    .section-title-with-icon {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 1.5rem;
    }

    .section-title-with-icon i {
        color: #28a745;
        font-size: 20px;
    }

    .control-card-premium {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        position: relative;
        transition: all 0.3s ease;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }

    .control-card-premium:hover {
        box-shadow: 0 8px 24px rgba(149, 157, 165, 0.1);
        transform: translateY(-2px);
    }

    .control-type-label {
        font-size: 11px;
        font-weight: 800;
        color: #28a745;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .control-name-title {
        font-size: 18px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 15px;
    }

    .control-meta-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 15px;
    }

    .control-meta-grid > div:last-child {
        text-align: right;
    }

    .meta-label {
        font-size: 10px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }

    .meta-value {
        font-size: 14px;
        font-weight: 700;
        color: #334155;
    }

    .verified-badge {
        color: #15803d;
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .ref-number {
        font-size: 11px;
        color: #94a3b8;
        font-weight: 500;
    }

    .add-more-dashed-box {
        border: 2px dashed #e2e8f0;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 180px;
        color: #94a3b8;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #fcfdfe;
    }

    .add-more-dashed-box:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        color: #64748b;
    }

    .add-more-dashed-box i {
        font-size: 32px;
        margin-bottom: 12px;
        color: #cbd5e1;
    }

    .registration-panel-premium {
        background: #f8faff;
        border: 1px solid #eef2ff;
        border-radius: 16px;
        padding: 30px;
    }

    .premium-input-group label {
        font-weight: 700;
        color: #475569;
        font-size: 13px;
        margin-bottom: 8px;
    }

    .premium-input {
        background: #edeff5 !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 12px 16px !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        color: #334155 !important;
        height: auto !important;
        box-shadow: none !important;
    }

    .premium-input::placeholder {
        color: #94a3b8 !important;
    }

    .register-btn-premium {
        background: #0061e0;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 16px 24px;
        font-weight: 700;
        font-size: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        box-shadow: 0 4px 12px rgba(0, 97, 224, 0.2);
        transition: all 0.3s ease;
    }

    .register-btn-premium:hover {
        background: #0056c7;
        box-shadow: 0 6px 16px rgba(0, 97, 224, 0.3);
        transform: translateY(-1px);
    }

    .registration-helper-text {
        font-size: 11px;
        color: #94a3b8;
        line-height: 1.5;
        margin-top: 15px;
        text-align: center;
    }

    /* Navigation Buttons Redesign */
    .btn-nav-back {
        background: #ffffff !important;
        color: #1e293b !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        padding: 12px 24px !important;
        font-weight: 700 !important;
        font-size: 14px !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
    }

    .btn-nav-back:hover {
        background: #f8faff !important;
        border-color: #cbd5e1 !important;
    }

    .btn-nav-continue {
        background: #0066ff !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 12px 30px !important;
        font-weight: 700 !important;
        font-size: 14px !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        box-shadow: 0 4px 12px rgba(0, 102, 255, 0.2) !important;
    }

    .btn-nav-continue:hover {
        background: #0052cc !important;
        box-shadow: 0 6px 16px rgba(0, 102, 255, 0.3) !important;
    }

    /* Media Card Styles */
    .media-card-premium {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        position: relative;
        transition: all 0.3s ease;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        min-height: 200px;
        display: flex;
        flex-direction: column;
    }

    .media-card-premium:hover {
        box-shadow: 0 8px 24px rgba(149, 157, 165, 0.1);
        transform: translateY(-2px);
    }

    .status-badge-verified {
        background: #dcfce7;
        color: #15803d;
        font-weight: 700;
        font-size: 10px;
        padding: 4px 12px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .verified-badge-footer {
        background: #f8fafc;
        color: #64748b;
        font-weight: 700;
        font-size: 11px;
        padding: 6px 12px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1px solid #e2e8f0;
    }

    .media-name-title {
        font-size: 18px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 20px;
        line-height: 1.2;
    }

    .media-meta-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
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
        gap: 10px;
        align-items: center;
        border-top: 1px solid #f1f5f9;
        padding-top: 15px;
    }

    .btn-edit-entry {
        flex: 1;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #0061e0;
        font-weight: 700;
        font-size: 12px;
        padding: 8px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .btn-edit-entry:hover {
        background: #f8faff;
        border-color: #0061e0;
    }

    .media-registration-panel {
        background: #f8faff;
        border: 1px solid #eef2ff;
        border-radius: 16px;
        padding: 25px;
        margin-top: 30px;
    }

    .media-registration-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 800;
        color: #1e293b;
        font-size: 16px;
        margin-bottom: 20px;
    }

    .media-registration-title::before {
        content: '';
        width: 4px;
        height: 18px;
        background: #0061e0;
        border-radius: 2px;
    }

    .placeholder-card-dashed {
        border: 2px dashed #e2e8f0;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 200px;
        color: #94a3b8;
        background: #fcfdfe;
        text-align: center;
        padding: 20px;
    }

    .placeholder-icon-container {
        width: 48px;
        height: 48px;
        background: #f1f5f9;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
    }

    .placeholder-icon-container i {
        font-size: 24px;
        color: #94a3b8;
    }

    .placeholder-text-main {
        font-weight: 700;
        color: #64748b;
        font-size: 14px;
        margin-bottom: 4px;
    }

    .placeholder-text-sub {
        font-size: 12px;
        color: #94a3b8;
    }

    /* Post Results modal — match formula worksheets modal theme */
    #post-results-modal .post-results-modal-dialog {
        max-width: 96%;
        width: 96%;
        margin: 1.75rem auto;
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
        margin-bottom: 0.35rem;
    }

    #post-results-modal #post-results-table thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        vertical-align: middle;
        border-color: #dee2e6;
    }

    #post-results-modal #post-results-table tbody td {
        vertical-align: middle;
        border-color: #dee2e6;
    }

    #post-results-modal #post-results-table tbody tr.post-result-row td:nth-child(2) {
        font-weight: 600;
    }

    #post-results-modal #post-results-table .form-control-sm {
        font-size: 0.875rem;
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
