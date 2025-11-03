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
    position: sticky;
    top: 0;
    z-index: 1000;
    backdrop-filter: blur(10px);
}

/* === Sections Panel (Left Sidebar) === */
.sections-panel {
    position: fixed;
    top: 140px;
    left: 0;
    width: 300px;
    height: calc(100vh - 140px);
    background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
    border-right: 1px solid var(--gray-200);
    z-index: 100;
    display: flex;
    flex-direction: column;
    box-shadow: var(--shadow-md);
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
    position: fixed;
    top: 140px;
    right: 0;
    width: 240px;
    height: calc(100vh - 140px);
    background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
    border-left: 1px solid var(--gray-200);
    z-index: 100;
    display: flex;
    flex-direction: column;
    box-shadow: var(--shadow-md);
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

/* === Designer Canvas (Center) === */
.designer-canvas-wrapper {
    margin-left: 300px;
    margin-right: 240px;
    padding: 24px;
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    min-height: calc(100vh - 140px);
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
    }
    
    .designer-canvas-wrapper {
        margin-left: 260px;
    }
    
    .element-palette-panel {
        width: 200px;
    }
    
    .designer-canvas-wrapper {
        margin-right: 200px;
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
