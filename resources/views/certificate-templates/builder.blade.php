@extends('layouts.lab.layout.app')

@section('title2')
<title>Visual Template Builder - {{ $template->name }} | Lab Management</title>
@endsection

@section('content2')
<style>
    /* === Modern Color Palette === */
    :root {
        --primary-color: #4f46e5;
        --primary-light: #818cf8;
        --primary-dark: #3730a3;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

     /* === Visual Builder Layout === */
     .builder-toolbar {
         background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
         border-bottom: 1px solid var(--gray-200);
         box-shadow: var(--shadow-md);
         margin: 0;
         width: 100%;
         z-index: 100;
     }

     /* === Sections Panel (Left Sidebar) === */
     .sections-panel {
         width: 300px;
         min-width: 300px;
         height: calc(100vh - 200px);
         background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
         border-right: 1px solid var(--gray-200);
         z-index: 100;
         display: flex;
         flex-direction: column;
         box-shadow: var(--shadow-md);
         flex-shrink: 0;
     }

    .sections-panel .panel-header {
        padding: 20px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        border-bottom: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
    }

    .sections-panel .panel-header h6 {
        color: white;
        font-weight: 600;
        font-size: 15px;
        margin: 0;
    }

    .sections-panel .panel-header .btn {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        font-size: 12px;
        font-weight: 500;
        padding: 6px 12px;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .sections-panel .panel-header .btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .sections-panel .panel-body {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
    }

    .section-panel-item {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 12px;
        margin-bottom: 12px;
        overflow: hidden;
        transition: all 0.3s;
        box-shadow: var(--shadow-sm);
    }

    .section-panel-item:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .section-panel-header {
        padding: 14px 16px;
        background: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s;
        border-bottom: 1px solid transparent;
    }

    .section-panel-header:hover {
        background: var(--gray-50);
        border-bottom-color: var(--gray-100);
    }

    .section-panel-header i.mdi-chevron-down {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        color: var(--gray-400);
        font-size: 18px;
    }

    .section-panel-header[aria-expanded="false"] i.mdi-chevron-down {
        transform: rotate(-90deg);
    }

    .section-title {
        flex: 1;
        font-weight: 600;
        font-size: 14px;
        color: var(--gray-800);
    }

    .section-panel-actions {
        display: flex;
        gap: 6px;
    }

    .section-panel-actions .btn {
        padding: 4px 8px;
        font-size: 12px;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .section-panel-actions .btn:hover {
        transform: scale(1.1);
    }

    .section-holders {
        padding: 12px;
        background: var(--gray-50);
    }

    .holder-badge {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 8px;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: var(--shadow-sm);
    }

    .holder-badge:hover {
        border-color: var(--primary-color);
        background: var(--gray-50);
        transform: translateX(4px);
        box-shadow: var(--shadow-md);
    }

    .holder-badge i {
        color: var(--primary-color);
        font-size: 16px;
    }

    .holder-badge small {
        margin-left: auto;
        color: var(--gray-500);
        font-weight: 500;
    }

    .btn-holder-delete {
        background: none;
        border: none;
        color: var(--danger-color);
        padding: 0 6px;
        cursor: pointer;
        opacity: 0;
        transition: all 0.2s;
        border-radius: 4px;
    }

    .btn-holder-delete:hover {
        background: rgba(239, 68, 68, 0.1);
    }

    .holder-badge:hover .btn-holder-delete {
        opacity: 1;
    }

    .add-holder-btn {
        font-size: 12px;
        font-weight: 500;
        border-radius: 8px;
        padding: 8px;
        transition: all 0.2s;
        background: white;
        color: var(--success-color);
        border: 1px dashed var(--success-color);
    }

    .add-holder-btn:hover {
        background: var(--success-color);
        color: white;
        border-style: solid;
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

     /* === Element Palette (Right Sidebar) === */
     .element-palette-panel {
         width: 240px;
         min-width: 240px;
         height: calc(100vh - 200px);
         background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
         border-left: 1px solid var(--gray-200);
         z-index: 100;
         display: flex;
         flex-direction: column;
         box-shadow: var(--shadow-md);
         flex-shrink: 0;
     }

    .element-palette-panel .panel-header {
        padding: 20px;
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        border-bottom: none;
        color: white;
    }

    .element-palette-panel .panel-header h6 {
        color: white;
        font-weight: 600;
        font-size: 15px;
        margin: 0;
    }

    .element-palette-panel .panel-body {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
    }

    .element-types {
        display: grid;
        gap: 10px;
    }

    .element-type-btn {
        background: white;
        border: 2px solid var(--gray-200);
        border-radius: 10px;
        padding: 14px 10px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        font-weight: 600;
        text-align: center;
        color: var(--gray-700);
        position: relative;
        overflow: hidden;
    }

    .element-type-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(79, 70, 229, 0.1), transparent);
        transition: left 0.5s;
    }

    .element-type-btn:hover::before {
        left: 100%;
    }

    .element-type-btn:hover {
        border-color: var(--primary-color);
        background: linear-gradient(135deg, #ffffff 0%, #f0f1ff 100%);
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 8px 16px rgba(79, 70, 229, 0.2);
    }

    .element-type-btn.active {
        border-color: var(--primary-color);
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        color: white;
        box-shadow: 0 8px 16px rgba(79, 70, 229, 0.3);
    }

    .element-type-btn i {
        font-size: 24px;
        color: var(--primary-color);
        transition: all 0.2s;
    }

    .element-type-btn:hover i {
        transform: scale(1.1);
    }

    .element-type-btn.active i {
        color: white;
    }

    .palette-help {
        border-radius: 10px;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 1px solid #fbbf24;
        padding: 12px;
    }

    .palette-help strong {
        color: var(--gray-800);
    }

    .palette-help ol {
        font-size: 11px;
        color: var(--gray-700);
    }

     /* === Builder Container === */
     .builder-container {
         display: flex;
         width: 100%;
         min-height: calc(100vh - 200px);
         position: relative;
     }

     /* === Designer Canvas (Center) === */
     .designer-canvas-wrapper {
         flex: 1;
         padding: 24px;
         background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
         min-height: calc(100vh - 200px);
         overflow: auto;
     }

    .canvas-toolbar {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 12px 12px 0 0;
        padding: 14px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: -1px;
        box-shadow: var(--shadow-sm);
    }

    .canvas-info {
        display: flex;
        gap: 10px;
    }

    .canvas-info .badge {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-secondary {
        background: linear-gradient(135deg, var(--gray-600) 0%, var(--gray-700) 100%);
        color: white;
    }

    .badge-light {
        background: var(--gray-100);
        color: var(--gray-700);
        border: 1px solid var(--gray-300);
    }

    .canvas-controls {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .canvas-controls .btn {
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s;
        border: 1px solid var(--gray-300);
    }

    .canvas-controls .btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .canvas-controls .btn.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .zoom-level {
        padding: 6px 14px;
        font-weight: 600;
        min-width: 60px;
        text-align: center;
        background: var(--gray-100);
        border-radius: 6px;
        font-size: 13px;
        color: var(--gray-700);
    }

    .divider-v {
        width: 1px;
        height: 24px;
        background: var(--gray-300);
    }

    .canvas-container {
        background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
        overflow: auto;
        border: 1px solid var(--gray-200);
        border-radius: 0 0 12px 12px;
        padding: 48px;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
    }

    .designer-canvas {
        background: #ffffff;
        box-shadow: var(--shadow-xl);
        position: relative;
        margin: 0 auto;
        background-image: 
            linear-gradient(var(--gray-100) 1px, transparent 1px),
            linear-gradient(90deg, var(--gray-100) 1px, transparent 1px);
        background-size: 20px 20px;
        background-position: -1px -1px;
        border-radius: 4px;
        overflow: hidden;
    }

    .designer-canvas.grid-hidden {
        background-image: none;
    }

    .canvas-empty-state {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        color: var(--gray-500);
    }

    .canvas-empty-state i {
        color: var(--gray-300);
        margin-bottom: 16px;
    }

    .canvas-empty-state h5 {
        color: var(--gray-700);
        font-weight: 600;
        margin-bottom: 8px;
    }

    .canvas-empty-state p {
        color: var(--gray-500);
        margin-bottom: 20px;
    }

    .canvas-empty-state .btn {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
        border: none;
        color: white;
        padding: 10px 20px;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    }

    .canvas-empty-state .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
    }

    /* === Element Holders === */
    .element-holder-container {
        border: 2px dashed var(--primary-color);
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.02) 0%, rgba(79, 70, 229, 0.05) 100%);
        border-radius: 10px;
        overflow: visible;
        transition: all 0.3s;
        box-shadow: var(--shadow-sm);
    }

    .element-holder-container:hover {
        background: rgba(79, 70, 229, 0.08);
        border-color: var(--primary-dark);
        box-shadow: 0 8px 24px rgba(79, 70, 229, 0.15);
        transform: translateY(-1px);
    }

    .element-holder-container.selected {
        border-color: var(--success-color);
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(16, 185, 129, 0.1) 100%);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
    }

    .holder-header {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.1) 0%, rgba(79, 70, 229, 0.15) 100%);
        padding: 8px 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 11px;
        font-weight: 600;
        color: var(--primary-dark);
        cursor: move;
        border-bottom: 1px solid rgba(79, 70, 229, 0.1);
    }

    .holder-title {
        flex: 1;
    }

    .holder-capacity {
        font-size: 10px;
        background: rgba(79, 70, 229, 0.15);
        padding: 3px 8px;
        border-radius: 6px;
        font-weight: 700;
    }

    .holder-actions button {
        background: rgba(255, 255, 255, 0.8);
        border: none;
        color: var(--primary-color);
        padding: 4px 8px;
        cursor: pointer;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .holder-actions button:hover {
        background: white;
        transform: scale(1.1);
    }

    .holder-content {
        position: relative;
        height: calc(100% - 33px);
    }

    .holder-empty-state {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        color: var(--gray-400);
        font-size: 12px;
    }

    .holder-empty-state i {
        font-size: 3rem;
        opacity: 0.3;
        color: var(--primary-color);
    }

    .holder-empty-state p {
        margin: 12px 0 0 0;
        line-height: 1.5;
        font-weight: 500;
    }

    /* === Canvas Elements === */
    .canvas-element {
        border: 2px solid var(--gray-200);
        background: white;
        cursor: move;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: var(--shadow-md);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .canvas-element:hover {
        border-color: var(--primary-color);
        box-shadow: 0 8px 24px rgba(79, 70, 229, 0.2);
        transform: scale(1.02);
        z-index: 100 !important;
    }

    .canvas-element.selected {
        border: 3px solid var(--success-color);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2), 0 12px 32px rgba(16, 185, 129, 0.3);
    }

    .element-header {
        background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
        padding: 6px 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 10px;
        border-bottom: 1px solid var(--gray-200);
    }

    .element-type-icon {
        color: var(--gray-600);
        font-size: 14px;
    }

    .element-actions {
        display: flex;
        gap: 4px;
        opacity: 0;
        transition: all 0.2s;
    }

    .canvas-element:hover .element-actions {
        opacity: 1;
    }

    .element-actions button {
        background: white;
        border: 1px solid var(--gray-300);
        color: var(--gray-600);
        padding: 3px 6px;
        cursor: pointer;
        font-size: 12px;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .element-actions button:hover {
        color: var(--primary-color);
        border-color: var(--primary-color);
        transform: scale(1.1);
        box-shadow: var(--shadow-sm);
    }

    .btn-element-delete:hover {
        color: var(--danger-color) !important;
        border-color: var(--danger-color) !important;
    }

    .element-preview {
        padding: 12px;
        height: calc(100% - 29px);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        background: white;
    }

    .preview-heading {
        font-weight: 700;
        font-size: 16px;
        color: var(--gray-800);
    }

    .preview-paragraph {
        line-height: 1.6;
        color: var(--gray-600);
    }

    .preview-image {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        border-radius: 4px;
    }

    .preview-placeholder {
        font-size: 3rem;
        color: var(--gray-200);
    }

    .preview-table,
    .preview-data,
    .preview-signature,
    .preview-date {
        text-align: center;
        color: var(--gray-500);
        font-weight: 500;
    }

    /* === Resize Handles === */
    .canvas-element.interact-dragging {
        opacity: 0.9;
        z-index: 1000;
        cursor: grabbing;
    }

    .element-holder-container.interact-dragging {
        opacity: 0.85;
        z-index: 1000;
        box-shadow: 0 16px 48px rgba(79, 70, 229, 0.35);
    }

    /* === Modals === */
    .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: var(--shadow-xl);
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        border-bottom: none;
        padding: 20px 24px;
    }

    .modal-header .modal-title {
        font-weight: 700;
        color: white;
        font-size: 18px;
    }

    .modal-header .close {
        color: white;
        opacity: 0.9;
        text-shadow: none;
        font-size: 28px;
    }

    .modal-header .close:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 24px;
    }

    .modal-footer {
        background: var(--gray-50);
        border-top: 1px solid var(--gray-200);
        padding: 16px 24px;
    }

    .form-group label {
        font-weight: 600;
        color: var(--gray-700);
        font-size: 13px;
        margin-bottom: 8px;
    }

    .form-control {
        border-radius: 8px;
        border: 2px solid var(--gray-200);
        padding: 10px 14px;
        transition: all 0.2s;
        font-size: 14px;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .text-danger {
        color: var(--danger-color) !important;
        font-weight: 600;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        border: none;
        color: white;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 8px;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
    }

    .btn-secondary {
        background: var(--gray-100);
        border: 1px solid var(--gray-300);
        color: var(--gray-700);
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .btn-secondary:hover {
        background: var(--gray-200);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    /* === Auto-save Indicator === */
    .auto-save-indicator {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
        color: white;
        padding: 12px 20px;
        border-radius: 50px;
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
        opacity: 0;
        transform: translateY(20px) scale(0.9);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 2000;
        font-size: 14px;
        font-weight: 600;
    }

    .auto-save-indicator.show {
        opacity: 1;
        transform: translateY(0) scale(1);
    }

    .auto-save-indicator i {
        margin-right: 8px;
        font-size: 16px;
    }

    /* === Toolbar Buttons === */
    .builder-toolbar .btn-group .btn {
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 8px;
        transition: all 0.2s;
        font-size: 14px;
    }

    .builder-toolbar .btn-success {
        background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
        border: none;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .builder-toolbar .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
    }

    .builder-toolbar .btn-info {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        border: none;
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
    }

    .builder-toolbar .btn-info:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(14, 165, 233, 0.4);
    }

     /* === Responsive Adjustments === */
     @media (max-width: 1400px) {
         .sections-panel {
             width: 260px;
             min-width: 260px;
         }
         
         .element-palette-panel {
             width: 200px;
             min-width: 200px;
         }
     }

    /* === Animations === */
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .section-panel-item {
        animation: slideInLeft 0.3s ease-out;
    }

    .element-type-btn {
        animation: slideInRight 0.3s ease-out;
    }

    /* === Empty States === */
    .empty-sections-state {
        text-align: center;
        padding: 32px 20px;
        color: var(--gray-400);
    }

    .empty-sections-state i {
        font-size: 3.5rem;
        color: var(--gray-300);
        margin-bottom: 12px;
        display: block;
    }

    .empty-sections-state p {
        font-weight: 600;
        color: var(--gray-600);
        margin-bottom: 6px;
        font-size: 14px;
    }

    .empty-sections-state small {
        color: var(--gray-500);
        font-size: 11px;
        display: block;
        line-height: 1.4;
    }

    .empty-holders-state {
        text-align: center;
        padding: 20px;
        color: var(--gray-400);
        background: rgba(0, 0, 0, 0.02);
        border-radius: 6px;
        margin: 8px;
    }

    .empty-holders-state i {
        font-size: 2rem;
        opacity: 0.4;
        display: block;
        margin-bottom: 8px;
    }

    .empty-holders-state p {
        margin: 0;
        font-size: 12px;
        font-weight: 500;
    }

    /* === Utility Classes === */
    .btn-xs {
        padding: 4px 8px;
        font-size: 11px;
        border-radius: 6px;
    }

    /* === Scrollbar Styling === */
    .sections-panel .panel-body::-webkit-scrollbar,
    .element-palette-panel .panel-body::-webkit-scrollbar {
        width: 6px;
    }

    .sections-panel .panel-body::-webkit-scrollbar-track,
    .element-palette-panel .panel-body::-webkit-scrollbar-track {
        background: var(--gray-100);
    }

    .sections-panel .panel-body::-webkit-scrollbar-thumb,
    .element-palette-panel .panel-body::-webkit-scrollbar-thumb {
        background: var(--gray-300);
        border-radius: 3px;
    }

    .sections-panel .panel-body::-webkit-scrollbar-thumb:hover,
    .element-palette-panel .panel-body::-webkit-scrollbar-thumb:hover {
        background: var(--gray-400);
    }
</style>

<main>
    <?php
    $items = array(
        array(
            'link' => route('lab-home'),
            'name' => 'Lab Management',
            'icon' => null
        ),
        array(
            'link' => route('certificate-templates.index'),
            'name' => 'Certificate Templates',
            'icon' => null
        ),
        array(
            'link' => route('certificate-templates.show', $template),
            'name' => $template->name,
            'icon' => null
        ),
        array(
            'link' => '#',
            'name' => 'Visual Builder',
            'icon' => null
        )
    );

    // Calculate canvas dimensions based on page settings
    $pageSettings = $template->page_settings ?? ['size' => 'A4', 'orientation' => 'portrait'];
    $canvasDimensions = [
        'A4' => ['width' => 794, 'height' => 1123], // pixels at 96dpi
        'A3' => ['width' => 1123, 'height' => 1587],
        'Letter' => ['width' => 816, 'height' => 1056],
        'Legal' => ['width' => 816, 'height' => 1344],
        'A5' => ['width' => 559, 'height' => 794],
    ];
    
    $size = $pageSettings['size'] ?? 'A4';
    $orientation = $pageSettings['orientation'] ?? 'portrait';
    
    if ($orientation === 'landscape') {
        $canvasWidth = $canvasDimensions[$size]['height'];
        $canvasHeight = $canvasDimensions[$size]['width'];
    } else {
        $canvasWidth = $canvasDimensions[$size]['width'];
        $canvasHeight = $canvasDimensions[$size]['height'];
    }
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <!-- Top Toolbar -->
    <div class="builder-toolbar">
        <div class="container-fluid">
            <div class="row align-items-center py-3">
                <div class="col-md-6">
                    <h5 class="mb-0">
                        <i class="mdi mdi-pencil"></i> Visual Template Builder
                        <small class="text-muted">{{ $template->name }}</small>
                    </h5>
                </div>
                <div class="col-md-6 text-right">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-success" id="save-template">
                            <i class="mdi mdi-content-save"></i> Save
                        </button>
                        <button type="button" class="btn btn-info" id="preview-template">
                            <i class="mdi mdi-eye"></i> Preview
                        </button>
                        <a href="{{ route('certificate-templates.show', $template) }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Builder Container: Sections | Canvas | Elements -->
    <div class="builder-container">
    <!-- Sections Panel (Left Sidebar) -->
    <div class="sections-panel" id="sections-panel">
        <div class="panel-header">
            <h6 class="mb-0"><i class="mdi mdi-folder-multiple"></i> Sections</h6>
            <button class="btn btn-sm btn-primary" id="add-section-btn">
                                        <i class="mdi mdi-plus"></i> Add Section
                                    </button>
                                </div>
        <div class="panel-body">
            @forelse($template->sections as $section)
                <div class="section-panel-item" data-section-id="{{ $section->id }}">
                    <div class="section-panel-header" data-toggle="collapse" data-target="#section-{{ $section->id }}-holders">
                        <i class="mdi mdi-chevron-down"></i>
                        <span class="section-title">{{ $section->title }}</span>
                        <div class="section-panel-actions">
                            <button class="btn btn-xs btn-outline-primary edit-section" data-id="{{ $section->id }}" title="Edit">
                                <i class="mdi mdi-pencil"></i>
                            </button>
                            <button class="btn btn-xs btn-outline-danger delete-section" data-id="{{ $section->id }}" title="Delete">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </div>
                    </div>
                    <div class="section-holders collapse show" id="section-{{ $section->id }}-holders">
                        @forelse($section->elementHolders as $holder)
                            <div class="holder-badge" data-holder-id="{{ $holder->id }}" data-section-id="{{ $section->id }}">
                                <i class="mdi mdi-cube-outline"></i>
                                Holder {{ $loop->iteration }}
                                <small>({{ $holder->elements->count() }}/{{ $holder->max_elements }})</small>
                                <button class="btn-holder-delete" data-id="{{ $holder->id }}" title="Delete Holder">
                                    <i class="mdi mdi-close"></i>
                                </button>
                            </div>
                        @empty
                            <div class="empty-holders-state">
                                <i class="mdi mdi-cube-outline"></i>
                                <p>No holders yet</p>
                            </div>
                        @endforelse
                        <button class="btn btn-sm btn-block btn-outline-success add-holder-btn mt-2" data-section-id="{{ $section->id }}">
                            <i class="mdi mdi-plus"></i> Add Holder
                        </button>
                    </div>
                </div>
            @empty
                <div class="empty-sections-state">
                    <i class="mdi mdi-folder-multiple-outline"></i>
                    <p>No sections yet</p>
                    <small>Click "+ Add Section" above to get started</small>
                </div>
            @endforelse
    </div>
    </div>
    
    <!-- Visual Designer Canvas (Center) -->
    <div class="designer-canvas-wrapper">
        <div class="canvas-toolbar">
            <div class="canvas-info">
                <span class="badge badge-secondary">{{ $size }} - {{ ucfirst($orientation) }}</span>
                <span class="badge badge-light">{{ $canvasWidth }}x{{ $canvasHeight }}px</span>
            </div>
            <div class="canvas-controls">
                <button class="btn btn-sm btn-outline-secondary" id="zoom-out" title="Zoom Out">
                    <i class="mdi mdi-minus"></i>
                    </button>
                <span class="zoom-level">100%</span>
                <button class="btn btn-sm btn-outline-secondary" id="zoom-in" title="Zoom In">
                    <i class="mdi mdi-plus"></i>
                    </button>
                <button class="btn btn-sm btn-outline-secondary" id="reset-zoom" title="Reset Zoom">
                    <i class="mdi mdi-backup-restore"></i>
                </button>
                <div class="divider-v"></div>
                <button class="btn btn-sm btn-outline-secondary" id="toggle-grid" title="Toggle Grid">
                    <i class="mdi mdi-grid"></i> Grid
                </button>
                <button class="btn btn-sm btn-outline-secondary active" id="toggle-snap" title="Toggle Snap">
                    <i class="mdi mdi-magnet-on"></i> Snap
                            </button>
                        </div>
                            </div>

        <div class="canvas-container" id="canvas-container">
            <div class="designer-canvas" id="designer-canvas" 
                 data-width="{{ $canvasWidth }}" 
                 data-height="{{ $canvasHeight }}"
                 style="width: {{ $canvasWidth }}px; height: {{ $canvasHeight }}px;">
                
                @foreach($template->sections as $section)
                    @foreach($section->elementHolders as $holder)
                        <div class="element-holder-container" 
                             data-holder-id="{{ $holder->id }}"
                             data-section-id="{{ $section->id }}"
                             data-max-elements="{{ $holder->max_elements }}"
                             style="position: absolute; 
                                    left: {{ $holder->position_x ?? 50 }}px; 
                                    top: {{ $holder->position_y ?? 50 }}px;
                                    width: {{ $holder->width ?? 300 }}px;
                                    height: {{ $holder->height ?? 200 }}px;">
                            
                            <div class="holder-header">
                                <span class="holder-title">{{ $section->title }} - Holder {{ $loop->parent->iteration }}</span>
                                <span class="holder-capacity">{{ $holder->elements->count() }}/{{ $holder->max_elements }}</span>
                                <div class="holder-actions">
                                    <button class="btn-holder-edit" data-id="{{ $holder->id }}" title="Edit Holder">
                                        <i class="mdi mdi-cog"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="holder-content">
                                @foreach($holder->elements as $element)
                                    <div class="canvas-element" 
                                         data-element-id="{{ $element->id }}"
                                         data-holder-id="{{ $holder->id }}"
                                         data-type="{{ $element->element_type }}"
                                         style="position: absolute; 
                                                left: {{ $element->position_x ?? 10 }}px; 
                                                top: {{ $element->position_y ?? 10 }}px;
                                                width: {{ $element->width ?? 200 }}px;
                                                height: {{ $element->height ?? 100 }}px;
                                                z-index: {{ $element->z_index ?? 1 }};">
                                        
                                        <div class="element-header">
                                            <span class="element-type-icon">
                                                @switch($element->element_type)
                                                    @case('heading')
                                                        <i class="mdi mdi-format-header-1"></i>
                                                        @break
                                                    @case('paragraph')
                                                        <i class="mdi mdi-format-paragraph"></i>
                                                        @break
                                                    @case('image')
                                                        <i class="mdi mdi-image"></i>
                                                        @break
                                                    @case('table')
                                                        <i class="mdi mdi-table"></i>
                                                        @break
                                                    @case('data_field')
                                                        <i class="mdi mdi-database"></i>
                                                        @break
                                                    @case('signature')
                                                        <i class="mdi mdi-pen"></i>
                                                        @break
                                                    @case('date')
                                                        <i class="mdi mdi-calendar"></i>
                                                        @break
                                                    @default
                                                        <i class="mdi mdi-square"></i>
                                                @endswitch
                                            </span>
                                            <span class="element-actions">
                                                <button class="btn-element-edit" data-id="{{ $element->id }}">
                                                    <i class="mdi mdi-pencil"></i>
                                                </button>
                                                <button class="btn-element-delete" data-id="{{ $element->id }}">
                                                    <i class="mdi mdi-delete"></i>
                                                </button>
                                            </span>
                                        </div>
                                        
                                        <div class="element-preview">
                                            @switch($element->element_type)
                                                @case('heading')
                                                    <div class="preview-heading">{{ $element->content ?: 'Heading' }}</div>
                                                    @break
                                                @case('paragraph')
                                                    <div class="preview-paragraph">{{ Str::limit($element->content ?: 'Paragraph text...', 50) }}</div>
                                                    @break
                                                @case('image')
                                                    @if($element->content)
                                                        <img src="{{ $element->content }}" alt="Preview" class="preview-image">
                                                    @else
                                                        <div class="preview-placeholder"><i class="mdi mdi-image"></i></div>
                                                    @endif
                                                    @break
                                                @case('table')
                                                    <div class="preview-table"><i class="mdi mdi-table"></i> Table</div>
                                                    @break
                                                @case('data_field')
                                                    <div class="preview-data">{{ '{' . ($element->content ?: 'field') . '}' }}</div>
                                                    @break
                                                @case('signature')
                                                    <div class="preview-signature"><i class="mdi mdi-pen"></i> Signature</div>
                                                    @break
                                                @case('date')
                                                    <div class="preview-date"><i class="mdi mdi-calendar"></i> {{ date('Y-m-d') }}</div>
                                                    @break
                                            @endswitch
                                        </div>
                                    </div>
                                @endforeach
                                
                                @if($holder->elements->count() == 0)
                                    <div class="holder-empty-state">
                                        <i class="mdi mdi-cube-outline"></i>
                                        <p>Click an element type<br>then click here to add</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endforeach
                
                @if($template->sections->count() == 0 || $template->sections->sum(function($s) { return $s->elementHolders->count(); }) == 0)
                    <div class="canvas-empty-state">
                        <i class="mdi mdi-file-document-edit" style="font-size: 4rem;"></i>
                        <h5>Start Building Your Template</h5>
                        <p>Add a section first, then add holders to it</p>
                        <button class="btn btn-primary" id="add-first-section">
                            <i class="mdi mdi-plus"></i> Add First Section
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Element Palette (Right Sidebar) -->
    <div class="element-palette-panel" id="element-palette">
        <div class="panel-header">
            <h6 class="mb-0"><i class="mdi mdi-palette"></i> Elements</h6>
        </div>
        <div class="panel-body">
            <div class="element-types">
                <button class="element-type-btn" data-type="heading">
                    <i class="mdi mdi-format-header-1"></i>
                    <span>Heading</span>
                </button>
                <button class="element-type-btn" data-type="paragraph">
                    <i class="mdi mdi-format-paragraph"></i>
                    <span>Paragraph</span>
                </button>
                <button class="element-type-btn" data-type="image">
                    <i class="mdi mdi-image"></i>
                    <span>Image</span>
                </button>
                <button class="element-type-btn" data-type="table">
                    <i class="mdi mdi-table"></i>
                    <span>Table</span>
                </button>
                <button class="element-type-btn" data-type="data_field">
                    <i class="mdi mdi-database"></i>
                    <span>Data Field</span>
                </button>
                <button class="element-type-btn" data-type="signature">
                    <i class="mdi mdi-pen"></i>
                    <span>Signature</span>
                </button>
                <button class="element-type-btn" data-type="date">
                    <i class="mdi mdi-calendar"></i>
                    <span>Date</span>
                </button>
                <button class="element-type-btn" data-type="page_break">
                    <i class="mdi mdi-page-layout-body"></i>
                    <span>Page Break</span>
                </button>
                </div>
            <div class="palette-help mt-3 p-2 bg-light small">
                <strong>How to add elements:</strong>
                <ol class="mb-0 pl-3">
                    <li>Click an element type</li>
                    <li>Click on a holder in the canvas</li>
                    <li>Drag & resize as needed</li>
                </ol>
            </div>
        </div>
    </div>
    <!-- End Builder Container -->
    </div>
</main>


@endsection


@section('script2')
<!-- Section Modal -->
<div class="modal fade" id="section-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Section Properties</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                </div>
            <div class="modal-body">
                <form id="section-form">
                    <input type="hidden" id="section-id">
                    <div class="form-group">
                        <label for="section-title">Section Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="section-title" required>
                    </div>
                    <div class="form-group">
                        <label for="section-description">Description</label>
                        <textarea class="form-control" id="section-description" rows="3"></textarea>
                    </div>
                <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="section-collapsible">
                        <label class="form-check-label" for="section-collapsible">
                            Collapsible Section
                    </label>
                </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-section">Save Section</button>
            </div>
        </div>
    </div>
</div>

<!-- Element Holder Modal -->
<div class="modal fade" id="holder-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Element Holder Properties</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="holder-form">
                    <input type="hidden" id="holder-id">
                    <input type="hidden" id="holder-section-id">
            <div class="form-group">
                        <label for="holder-type">Holder Type</label>
                        <select class="form-control" id="holder-type">
                            <option value="field">Field Holder (for form data)</option>
                            <option value="text">Text Holder (for static content)</option>
                </select>
            </div>
                        <div class="form-group">
                        <label for="holder-max-elements">Maximum Elements <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="holder-max-elements" min="1" max="50" value="10" required>
                        <small class="form-text text-muted">Maximum number of elements this holder can contain</small>
                        </div>
                </form>
                    </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-holder">Save Holder</button>
            </div>
        </div>
    </div>
</div>
                
<!-- Element Properties Modal -->
<div class="modal fade" id="element-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Element Properties</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                    </button>
                </div>
            <div class="modal-body">
                <div id="element-properties-content">
                    <!-- Properties content will be loaded here dynamically -->
            </div>
                </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-element-properties">Save Changes</button>
            </div>
        </div>
    </div>
</div>
            
<!-- Auto-save Indicator -->
<div class="auto-save-indicator" id="auto-save-indicator">
    <i class="mdi mdi-check-circle"></i> Saved
</div>
<script src="https://cdn.jsdelivr.net/npm/interactjs@1.10.19/dist/interact.min.js"></script>
@include('certificate-templates.partials.builder-scripts')
@endsection
