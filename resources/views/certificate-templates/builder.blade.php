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
    
    .section-panel-item.section-panel-active {
        border-color: var(--primary-color);
        background: rgba(79, 70, 229, 0.05);
    }
    
    .section-panel-item.section-panel-active .section-title {
        color: var(--primary-color);
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
    
    /* Holder Panel Item (Expandable) */
    .holder-panel-item {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        margin-bottom: 8px;
        overflow: hidden;
        transition: all 0.2s;
    }
    
    .holder-panel-item:hover {
        box-shadow: var(--shadow-sm);
    }
    
    .holder-panel-header {
        padding: 10px 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        background: linear-gradient(135deg, var(--gray-50) 0%, white 100%);
        transition: all 0.2s;
    }
    
    .holder-panel-header:hover {
        background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-50) 100%);
    }
    
    .holder-panel-header-left {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
    }
    
    .holder-panel-toggle {
        transition: transform 0.3s;
        color: var(--gray-400);
        font-size: 14px;
    }
    
    .holder-panel-item:not(.holder-collapsed) .holder-panel-toggle {
        transform: rotate(90deg);
    }
    
    .holder-panel-title {
        font-weight: 600;
        font-size: 12px;
        color: var(--gray-800);
    }
    
    .holder-panel-actions {
        display: flex;
        gap: 4px;
    }
    
    .holder-panel-content {
        padding: 10px 12px;
        background: var(--gray-50);
        border-top: 1px solid var(--gray-200);
    }
    
    .elements-list, .nested-holders-list {
        margin-bottom: 10px;
    }
    
    .element-badge {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 6px;
        padding: 6px 10px;
        margin-bottom: 4px;
        font-size: 11px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }
    
    .element-badge:hover {
        border-color: var(--primary-color);
        background: var(--gray-50);
    }
    
    .element-badge-actions {
        margin-left: auto;
        display: flex;
        gap: 4px;
        opacity: 0;
        transition: opacity 0.2s;
    }
    
    .element-badge:hover .element-badge-actions {
        opacity: 1;
    }
    
    .holder-actions-panel {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-top: 10px;
    }
    
    .holder-actions-panel .btn {
        font-size: 11px;
        padding: 6px 10px;
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
        box-sizing: border-box;
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
        overflow: visible;
        box-sizing: border-box;
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

    /* === Enhanced Canvas Components === */
    .canvas-section {
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        margin-bottom: 12px;
        background: white;
        transition: all 0.3s;
        box-sizing: border-box;
        overflow: visible;
        box-shadow: var(--shadow-sm);
    }
    
    .canvas-section.active {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    
    .section-label {
        position: absolute;
        top: 8px;
        left: 8px;
        background: var(--primary-color);
        color: white;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        z-index: 10;
    }
    
    .section-content {
        position: relative;
        width: 100%;
        height: 100%;
    }
    
    .section-resize-handle {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 6px;
        cursor: ns-resize;
        background: var(--gray-200);
        transition: background 0.2s;
        z-index: 20;
    }
    
    .section-resize-handle:hover {
        background: var(--primary-color);
    }

    /* === Canvas Holders (Root Only - Nested Render Flat) === */
    .canvas-holder-root {
        position: absolute;
        border: 1px dashed var(--gray-300);
        background: rgba(255, 255, 255, 0.5);
        border-radius: 6px;
        box-sizing: border-box;
        transition: all 0.2s;
    }
    
    .canvas-holder-root:hover {
        border-color: var(--primary-color);
        background: rgba(79, 70, 229, 0.05);
    }

    .holder-toolbar {
        position: absolute;
        top: -30px;
        right: 0;
        display: flex;
        gap: 4px;
        background: white;
        padding: 4px;
        border-radius: 6px;
        box-shadow: var(--shadow-md);
        opacity: 0;
        transition: opacity 0.2s;
        z-index: 100;
    }

    .canvas-holder:hover .holder-toolbar {
        opacity: 1;
    }

    .btn-toolbar {
        padding: 6px 10px;
        border: none;
        background: var(--gray-100);
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        min-height: 32px;
        color: var(--gray-700);
    }

    .btn-toolbar:hover {
        background: var(--primary-color);
        color: white;
        transform: scale(1.1);
        box-shadow: var(--shadow-md);
    }
    
    .holder-toolbar .btn-toolbar {
        width: 32px;
        height: 32px;
        padding: 0;
    }

    .holder-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px;
        background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
        border-radius: 6px;
        margin-bottom: 10px;
        font-size: 12px;
    }
    
    .holder-header-clickable {
        cursor: pointer;
        user-select: none;
        transition: all 0.2s;
    }
    
    .holder-header-clickable:hover {
        background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-200) 100%);
    }
    
    .holder-header-left {
        display: flex;
        align-items: center;
        gap: 8px;
        flex: 1;
    }
    
    .holder-toggle-icon {
        transition: transform 0.3s;
        color: var(--gray-500);
        font-size: 16px;
    }
    
    .canvas-holder:not(.holder-collapsed) .holder-toggle-icon {
        transform: rotate(90deg);
    }
    
    .holder-summary {
        font-size: 11px;
        color: var(--gray-500);
        font-weight: normal;
    }

    .holder-title {
        font-weight: 600;
        color: var(--gray-700);
    }

    .holder-capacity {
        font-size: 11px;
        color: var(--gray-500);
        background: white;
        padding: 2px 8px;
        border-radius: 10px;
        display: flex;
        align-items: center;
    }
    
    .holder-content-collapsible {
        overflow: hidden;
    }

    .holder-content {
        flex: 1;
        position: relative;
        min-height: 50px;
        padding: 8px;
        display: flex !important;
        align-items: flex-start;
        gap: 8px;
        box-sizing: border-box;
        overflow: hidden !important;
        contain: layout style paint;
        max-width: 100%;
        width: 100%;
    }
    
    /* Ensure elements fit within holder bounds - don't overflow */
    .holder-content .canvas-element {
        flex-shrink: 0;
        box-sizing: border-box;
        max-width: 100%;
        max-height: 100%;
    }
    
    .holder-content .canvas-holder.nested-holder {
        flex-shrink: 0;
        box-sizing: border-box;
        max-width: 100%;
        max-height: 100%;
    }
    
    /* Vertical layout - no wrap */
    .holder-content[style*="flex-direction: column"] {
        flex-wrap: nowrap !important;
    }
    
    /* Horizontal layout - allow wrap for responsive */
    .holder-content[style*="flex-direction: row"] {
        flex-wrap: wrap;
    }
    
    .holder-content .canvas-element {
        flex: 0 0 auto;
    }
    
    .holder-content .nested-holder {
        flex: 0 0 auto;
    }

    .holder-empty-state {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: var(--gray-400);
        font-size: 12px;
    }

    /* === Resize Handles === */
    .resize-handle {
        position: absolute;
        background: var(--primary-color);
        border: 2px solid white;
        border-radius: 50%;
        width: 12px;
        height: 12px;
        z-index: 100;
        cursor: nwse-resize;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .canvas-holder:hover .resize-handle,
    .canvas-element:hover .resize-handle {
        opacity: 1;
    }

    .resize-handle-nw { top: -6px; left: -6px; cursor: nwse-resize; }
    .resize-handle-ne { top: -6px; right: -6px; cursor: nesw-resize; }
    .resize-handle-sw { bottom: -6px; left: -6px; cursor: nesw-resize; }
    .resize-handle-se { bottom: -6px; right: -6px; cursor: nwse-resize; }
    .resize-handle-n { top: -6px; left: 50%; transform: translateX(-50%); cursor: ns-resize; }
    .resize-handle-s { bottom: -6px; left: 50%; transform: translateX(-50%); cursor: ns-resize; }
    .resize-handle-w { left: -6px; top: 50%; transform: translateY(-50%); cursor: ew-resize; }
    .resize-handle-e { right: -6px; top: 50%; transform: translateY(-50%); cursor: ew-resize; }

    /* === Canvas Elements === */
    .canvas-element {
        border: 2px solid var(--gray-200);
        background: white;
        cursor: move;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: var(--shadow-md);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .canvas-element:hover {
        border-color: var(--primary-color);
        box-shadow: 0 8px 24px rgba(79, 70, 229, 0.2);
        transform: scale(1.02);
        z-index: 100 !important;
    }

    .element-toolbar {
        position: absolute;
        top: -38px;
        right: 0;
        display: flex;
        gap: 6px;
        background: white;
        padding: 6px 8px;
        border-radius: 8px;
        box-shadow: var(--shadow-lg);
        opacity: 0;
        transition: all 0.2s;
        z-index: 150;
    }

    .canvas-element:hover .element-toolbar {
        opacity: 1;
    }
    
    .element-toolbar .btn-toolbar {
        width: 32px;
        height: 32px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .canvas-element.selected {
        border: 3px solid var(--success-color);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2), 0 12px 32px rgba(16, 185, 129, 0.3);
    }
    
    .canvas-element.element-hidden {
        opacity: 0.3;
        pointer-events: none;
        filter: blur(2px);
    }
    
    .canvas-element.element-hidden .element-toolbar {
        opacity: 1 !important;
        pointer-events: all;
    }
    
    .canvas-element.element-hidden .element-toolbar .btn-element-hide i {
        color: var(--primary-color);
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

    /* Modal Select Fields Styling */
    #holder-modal .modal-dialog {
        overflow: visible;
    }

    #holder-modal .modal-content {
        overflow: visible;
    }

    #holder-modal .modal-body {
        overflow: visible;
        max-height: none;
    }

    #holder-modal select.form-control {
        width: 100%;
        padding: 8px 12px;
        font-size: 14px;
        line-height: 1.5;
        border: 1px solid var(--gray-300);
        border-radius: 6px;
        background-color: white;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 12px;
        padding-right: 35px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    #holder-modal select.form-control:hover {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    #holder-modal select.form-control:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    }

    #holder-modal select.form-control option {
        padding: 8px 12px;
        font-size: 14px;
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
    .sections-panel .panel-body::-webkit-scrollbar {
        width: 6px;
    }

    .sections-panel .panel-body::-webkit-scrollbar-track {
        background: var(--gray-100);
    }

    .sections-panel .panel-body::-webkit-scrollbar-thumb {
        background: var(--gray-300);
        border-radius: 3px;
    }

    .sections-panel .panel-body::-webkit-scrollbar-thumb:hover {
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
                <div class="section-panel-item {{ $loop->first ? 'section-panel-active' : '' }}" data-section-id="{{ $section->id }}">
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
                        @forelse($section->elementHolders->whereNull('parent_holder_id') as $holder)
                            <div class="holder-panel-item holder-collapsed">
                                <div class="holder-panel-header" data-holder-id="{{ $holder->id }}">
                                    <div class="holder-panel-header-left">
                                        <i class="mdi mdi-chevron-right holder-panel-toggle"></i>
                                        <i class="mdi mdi-cube-outline"></i>
                                        <span class="holder-panel-title">{{ $holder->holder_type }} Holder</span>
                                        <small>({{ ($holder->elements->count() + $holder->childHolders->count()) }}/{{ $holder->max_elements ?? '∞' }})</small>
                                    </div>
                                    <div class="holder-panel-actions">
                                        <button class="btn btn-xs btn-outline-primary btn-holder-edit-panel" data-id="{{ $holder->id }}" title="Edit">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                        <button class="btn btn-xs btn-outline-danger btn-holder-delete-panel" data-id="{{ $holder->id }}" title="Delete">
                                            <i class="mdi mdi-delete-empty"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                {{-- Holder Content (Collapsible) --}}
                                <div class="holder-panel-content" style="display: none;">
                                    {{-- Elements List --}}
                                    @if($holder->elements->count() > 0)
                                        <div class="elements-list mb-2">
                                            <small class="text-muted d-block mb-1" style="font-size: 10px; font-weight: 600;">
                                                <i class="mdi mdi-format-list-bulleted"></i> Elements:
                                            </small>
                                            @foreach($holder->elements as $element)
                                                <div class="element-badge" data-element-id="{{ $element->id }}">
                                                    <i class="mdi mdi-{{ $element->element_type === 'text' ? 'text' : ($element->element_type === 'image' ? 'image' : ($element->element_type === 'data_field' ? 'database' : 'file-document')) }}"></i>
                                                    <span>{{ ucfirst(str_replace('_', ' ', $element->element_type)) }}</span>
                                                    @if($element->content)
                                                        <small class="text-muted">({{ Str::limit($element->content, 20) }})</small>
                                                    @endif
                                                    <div class="element-badge-actions">
                                                        <button class="btn btn-xs btn-outline-primary btn-element-edit-panel" data-id="{{ $element->id }}" title="Edit">
                                                            <i class="mdi mdi-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-xs btn-outline-danger btn-element-delete-panel" data-id="{{ $element->id }}" title="Delete">
                                                            <i class="mdi mdi-delete-empty"></i>
                                                        </button>
            </div>
            </div>
                                            @endforeach
            </div>
                                    @endif
                                    
                                    {{-- Nested Holders --}}
                                    @if($holder->childHolders->count() > 0)
                                        <div class="nested-holders-list mb-2">
                                            <small class="text-muted d-block mb-1" style="font-size: 10px; font-weight: 600;">
                                                <i class="mdi mdi-nested-box"></i> Nested Holders:
                                            </small>
                                            @foreach($holder->childHolders as $childHolder)
                                                <div class="holder-badge nested" style="font-size: 11px; padding: 6px 10px; margin-bottom: 4px;" data-holder-id="{{ $childHolder->id }}">
                                                    <i class="mdi mdi-folder-outline"></i>
                                                    <span>{{ $childHolder->holder_type }} Holder</span>
                                                    <small>({{ ($childHolder->elements->count() + $childHolder->childHolders->count()) }}/{{ $childHolder->max_elements ?? '∞' }})</small>
                                                    <button class="btn btn-xs btn-outline-primary btn-holder-edit-panel ml-2" data-id="{{ $childHolder->id }}" title="Edit">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-outline-danger btn-holder-delete-panel ml-1" data-id="{{ $childHolder->id }}" title="Delete">
                                                        <i class="mdi mdi-delete-empty"></i>
                                                    </button>
            </div>
                                            @endforeach
            </div>
                                    @endif
                                    
                                    {{-- Action Buttons --}}
                                    <div class="holder-actions-panel">
                                        <button class="btn btn-xs btn-block btn-outline-success btn-add-element-panel" data-holder-id="{{ $holder->id }}">
                                            <i class="mdi mdi-plus-circle"></i> Add Element
                                        </button>
                                        <button class="btn btn-xs btn-block btn-outline-info btn-add-nested-holder-panel" data-holder-id="{{ $holder->id }}">
                                            <i class="mdi mdi-folder-plus"></i> Add Nested Holder
                                        </button>
            </div>
            </div>
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
                
                {{-- Canvas Sections - Stacked Vertically --}}
                @foreach($template->sections as $section)
                    <div class="canvas-section {{ $loop->first ? 'active' : '' }}" 
                         data-section-id="{{ $section->id }}"
                         style="height: {{ $section->height ?? 400 }}px;">
                        
                        {{-- Section Label --}}
                        <div class="section-label">{{ $section->title }}</div>
                        
                        {{-- Section Content --}}
                        <div class="section-content">
                            @foreach($section->elementHolders->whereNull('parent_holder_id') as $holder)
                                @include('certificate-templates.partials.canvas-holder', ['holder' => $holder])
                            @endforeach
                            
                            {{-- Section Empty State --}}
                            @if($section->elementHolders->whereNull('parent_holder_id')->isEmpty())
                                <div class="section-empty-state">
                                    <p class="text-muted">Use the sections panel to add holders and elements.</p>
                                </div>
                            @endif
                        </div>
                        
                        {{-- Section Vertical Resize Handle --}}
                        <div class="section-resize-handle"></div>
                    </div>
                @endforeach
                
                {{-- Empty State for Canvas --}}
                @if($template->sections->count() == 0 || $template->sections->sum(function($s) { return $s->elementHolders->whereNull('parent_holder_id')->count(); }) == 0)
                    <div class="canvas-empty-state">
                        <i class="mdi mdi-file-document-outline" style="font-size: 4rem;"></i>
                        <h5>Document Preview</h5>
                        <p>Use the <strong>Sections Panel</strong> on the left to add sections, holders, and elements.</p>
                        <p class="text-muted" style="font-size: 14px;">This canvas displays your template in document format. You can resize elements and holders here.</p>
                    </div>
                @endif
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
    <div class="modal-dialog modal-lg" role="document" style="overflow: visible;">
        <div class="modal-content" style="overflow: visible;">
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
                            <select class="form-control" id="holder-type" style="width: 100%; padding: 8px 35px 8px 12px; appearance: none; -webkit-appearance: none; -moz-appearance: none; background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'%3E%3Cpath fill=\'%23333\' d=\'M6 9L1 4h10z\'/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 12px; cursor: pointer;">
                                <option value="field">Field Holder (for form data)</option>
                                <option value="text">Text Holder (for static content)</option>
                                <option value="company_header">Datasource (for database values)</option>
                </select>
            </div>
            
            <div class="form-group">
                            <label for="holder-direction">Layout Direction</label>
                            <select class="form-control" id="holder-direction" style="width: 100%; padding: 8px 35px 8px 12px; appearance: none; -webkit-appearance: none; -moz-appearance: none; background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'%3E%3Cpath fill=\'%23333\' d=\'M6 9L1 4h10z\'/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 12px; cursor: pointer;">
                                <option value="horizontal">Horizontal (Row)</option>
                                <option value="vertical">Vertical (Column)</option>
                            </select>
                            <small class="form-text text-muted">How children will be arranged inside this holder</small>
            </div>
                        
                        <input type="hidden" id="holder-parent-id">

            <!-- Dynamic Data Source Selection (shown when holder_type is company_header) -->
            <div id="data-source-section" style="display: none;">
                <h6 class="mt-3 mb-2">Data Source</h6>
            <div class="form-group">
                    <label for="data-source">Select Data Source</label>
                    <select class="form-control" id="data-source" style="width: 100%; padding: 8px 35px 8px 12px; appearance: none; -webkit-appearance: none; -moz-appearance: none; background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'%3E%3Cpath fill=\'%23333\' d=\'M6 9L1 4h10z\'/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 12px; cursor: pointer;">
                        <option value="">-- Select Data Source --</option>
                        <option value="Company">Company</option>
                        <option value="CRMCustomer">CRM Customer</option>
                        <option value="SampleHeader">Sample Header</option>
                        <option value="SampleDetails">Sample Details</option>
                        <option value="CapturedResult">Captured Result</option>
                </select>
                    <small class="form-text text-muted">Choose which model to pull data from</small>
            </div>

                <!-- Dynamic Field Mappings -->
                <div id="field-mappings-container" style="display: none;">
                    <h6 class="mt-3 mb-2">Select Fields to Include</h6>
                    <div id="field-mappings-list" class="form-group">
                        <!-- Field checkboxes will be loaded here dynamically -->
            </div>
                </div>
            </div>
            
            <!-- Legacy Company Information Fields (deprecated - shown only if data_source is not set) -->
            <div id="company-information-fields" style="display: none;">
                <h6 class="mt-3 mb-2">Company Information (Legacy)</h6>
                <div class="alert alert-warning">
                    <small>This is the legacy method. Please use Data Source selection above for better flexibility.</small>
            </div>
            <div class="form-group">
                    <label for="company-name">Company Name</label>
                    <input type="text" class="form-control" id="company-name" placeholder="Company Name">
            </div>
            <div class="form-group">
                    <label for="company-email">Company Email</label>
                    <input type="email" class="form-control" id="company-email" placeholder="company@example.com">
            </div>
            <div class="form-group">
                    <label for="company-website">Company Website</label>
                    <input type="url" class="form-control" id="company-website" placeholder="https://www.example.com">
            </div>
                        <div class="form-group">
                    <label for="company-phone">Company Phone</label>
                    <input type="text" class="form-control" id="company-phone" placeholder="+1234567890">
                        </div>
                        <div class="form-group">
                    <label for="company-logo">Company Logo URL</label>
                    <input type="text" class="form-control" id="company-logo" placeholder="https://example.com/logo.png">
                </div>
                
                <h6 class="mt-3 mb-2">Document QA Details</h6>
                <div class="form-group">
                    <label for="form-number">Form Number</label>
                    <input type="text" class="form-control" id="form-number" placeholder="Form Number">
                </div>
                <div class="form-group">
                    <label for="publish-date">Publish Date</label>
                    <input type="date" class="form-control" id="publish-date">
            </div>
                <div class="form-group">
                    <label for="qa-other-details">Other QA Details</label>
                    <textarea class="form-control" id="qa-other-details" rows="3" placeholder="Additional QA information"></textarea>
                </div>
            </div>
            
            <!-- Maximum Elements (hidden for company_header) -->
            <div id="max-elements-field">
            <div class="form-group">
                        <label for="holder-max-elements">Maximum Elements <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="holder-max-elements" min="1" max="50" value="10" required>
                        <small class="form-text text-muted">Maximum number of elements this holder can contain</small>
                </div>
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
            
<!-- Add Element Modal (Similar to Submission Builder) -->
<div class="modal fade" id="add-element-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document" style="overflow: visible;">
        <div class="modal-content" style="overflow: visible;">
            <div class="modal-header">
                <h5 class="modal-title">Add Element</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="add-element-form">
                    <input type="hidden" id="add-element-holder-id">
            <div class="form-group">
                        <label for="add-element-type">Element Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="add-element-type" style="width: 100%; padding: 8px 35px 8px 12px; appearance: none; -webkit-appearance: none; -moz-appearance: none; background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'%3E%3Cpath fill=\'%23333\' d=\'M6 9L1 4h10z\'/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 12px center; background-size: 12px; cursor: pointer;" required>
                            <option value="">-- Select Element Type --</option>
                            <option value="heading">Heading</option>
                            <option value="paragraph">Paragraph</option>
                            <option value="text">Text</option>
                            <option value="image">Image</option>
                            <option value="data_field">Data Field</option>
                            <option value="table">Table</option>
                            <option value="signature">Signature</option>
                            <option value="date">Date</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="radio">Radio Button</option>
                            <option value="link">Link</option>
                            <option value="blockquote">Blockquote</option>
                            <option value="code_block">Code Block</option>
                </select>
                        <small class="form-text text-muted">Choose the type of element to add</small>
            </div>
                    
                    <div id="add-element-extra-fields" style="display: none;">
                        <div class="form-group" id="add-element-content-field">
                            <label for="add-element-content">Content</label>
                            <textarea class="form-control" id="add-element-content" rows="3" placeholder="Enter content"></textarea>
            </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-add-element">Add Element</button>
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
