<style>
/* === Visual Builder Layout === */
.builder-toolbar {
    background: #fff;
    border-bottom: 2px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    position: sticky;
    top: 0;
    z-index: 1000;
}

/* === Sections Panel (Left Sidebar) === */
.sections-panel {
    position: fixed;
    top: 140px;
    left: 0;
    width: 280px;
    height: calc(100vh - 140px);
    background: #f8f9fa;
    border-right: 2px solid #dee2e6;
    z-index: 100;
    display: flex;
    flex-direction: column;
}

.sections-panel .panel-header {
    padding: 15px;
    background: #fff;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sections-panel .panel-body {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
}

.section-panel-item {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    margin-bottom: 10px;
    overflow: hidden;
}

.section-panel-header {
    padding: 10px 12px;
    background: #ffffff;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.section-panel-header:hover {
    background: #f8f9fa;
}

.section-panel-header i.mdi-chevron-down {
    transition: transform 0.2s;
}

.section-panel-header[aria-expanded="false"] i.mdi-chevron-down {
    transform: rotate(-90deg);
}

.section-title {
    flex: 1;
    font-weight: 500;
    font-size: 14px;
}

.section-panel-actions {
    display: flex;
    gap: 4px;
}

.section-panel-actions .btn {
    padding: 2px 6px;
    font-size: 12px;
}

.section-holders {
    padding: 8px;
    background: #f8f9fa;
}

.holder-badge {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 6px 10px;
    margin-bottom: 6px;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.holder-badge:hover {
    border-color: #007bff;
    background: #f0f8ff;
}

.holder-badge i {
    color: #6c757d;
}

.holder-badge small {
    margin-left: auto;
    color: #6c757d;
}

.btn-holder-delete {
    background: none;
    border: none;
    color: #dc3545;
    padding: 0 4px;
    cursor: pointer;
    opacity: 0;
    transition: opacity 0.2s;
}

.holder-badge:hover .btn-holder-delete {
    opacity: 1;
}

.add-holder-btn {
    font-size: 12px;
}

/* === Element Palette (Right Sidebar) === */
.element-palette-panel {
    position: fixed;
    top: 140px;
    right: 0;
    width: 200px;
    height: calc(100vh - 140px);
    background: #f8f9fa;
    border-left: 2px solid #dee2e6;
    z-index: 100;
    display: flex;
    flex-direction: column;
}

.element-palette-panel .panel-header {
    padding: 15px;
    background: #fff;
    border-bottom: 1px solid #dee2e6;
}

.element-palette-panel .panel-body {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
}

.element-types {
    display: grid;
    gap: 8px;
}

.element-type-btn {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 12px 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    text-align: center;
}

.element-type-btn:hover {
    border-color: #007bff;
    background: #f0f8ff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,123,255,0.15);
}

.element-type-btn i {
    font-size: 20px;
    color: #007bff;
}

.palette-help {
    border-radius: 4px;
}

/* === Designer Canvas (Center) === */
.designer-canvas-wrapper {
    margin-left: 280px;
    margin-right: 200px;
    padding: 20px;
    background: #e9ecef;
    min-height: calc(100vh - 140px);
}

.canvas-toolbar {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px 8px 0 0;
    padding: 12px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: -1px;
}

.canvas-info {
    display: flex;
    gap: 8px;
}

.canvas-controls {
    display: flex;
    gap: 8px;
    align-items: center;
}

.zoom-level {
    padding: 0 10px;
    font-weight: 500;
    min-width: 50px;
    text-align: center;
}

.divider-v {
    width: 1px;
    height: 20px;
    background: #dee2e6;
}

.canvas-container {
    background: #e9ecef;
    overflow: auto;
    border: 1px solid #dee2e6;
    border-radius: 0 0 8px 8px;
    padding: 40px;
}

.designer-canvas {
    background: #ffffff;
    box-shadow: 0 0 30px rgba(0,0,0,0.15);
    position: relative;
    margin: 0 auto;
    background-image: 
        linear-gradient(rgba(0,0,0,0.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,0,0,0.05) 1px, transparent 1px);
    background-size: 20px 20px;
    background-position: -1px -1px;
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
    color: #6c757d;
}

/* === Element Holders === */
.element-holder-container {
    border: 2px dashed #007bff;
    background: rgba(0,123,255,0.03);
    border-radius: 6px;
    overflow: hidden;
    transition: all 0.2s;
}

.element-holder-container:hover {
    background: rgba(0,123,255,0.08);
    border-color: #0056b3;
    box-shadow: 0 4px 12px rgba(0,123,255,0.2);
}

.element-holder-container.selected {
    border-color: #28a745;
    background: rgba(40,167,69,0.05);
}

.holder-header {
    background: rgba(0,123,255,0.1);
    padding: 6px 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    font-weight: 500;
    color: #0056b3;
    cursor: move;
}

.holder-title {
    flex: 1;
}

.holder-capacity {
    font-size: 10px;
    background: rgba(0,123,255,0.2);
    padding: 2px 6px;
    border-radius: 3px;
}

.holder-actions button {
    background: none;
    border: none;
    color: #0056b3;
    padding: 0 4px;
    cursor: pointer;
}

.holder-content {
    position: relative;
    height: calc(100% - 28px);
}

.holder-empty-state {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: #6c757d;
    font-size: 12px;
}

.holder-empty-state i {
    font-size: 2rem;
    opacity: 0.5;
}

.holder-empty-state p {
    margin: 8px 0 0 0;
    line-height: 1.3;
}

/* === Canvas Elements === */
.canvas-element {
    border: 1px solid #dee2e6;
    background: #ffffff;
    cursor: move;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    transition: all 0.2s;
}

.canvas-element:hover {
    border-color: #007bff;
    box-shadow: 0 4px 12px rgba(0,123,255,0.25);
    z-index: 10 !important;
}

.canvas-element.selected {
    border: 2px solid #28a745;
    box-shadow: 0 0 0 3px rgba(40,167,69,0.2);
}

.element-header {
    background: #f8f9fa;
    padding: 4px 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 10px;
    border-bottom: 1px solid #dee2e6;
}

.element-type-icon {
    color: #6c757d;
}

.element-actions {
    display: flex;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.2s;
}

.canvas-element:hover .element-actions {
    opacity: 1;
}

.element-actions button {
    background: none;
    border: none;
    color: #6c757d;
    padding: 2px;
    cursor: pointer;
    font-size: 12px;
}

.element-actions button:hover {
    color: #007bff;
}

.btn-element-delete:hover {
    color: #dc3545 !important;
}

.element-preview {
    padding: 8px;
    height: calc(100% - 25px);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.preview-heading {
    font-weight: bold;
    font-size: 14px;
}

.preview-paragraph {
    line-height: 1.4;
    color: #495057;
}

.preview-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.preview-placeholder {
    font-size: 2rem;
    color: #dee2e6;
}

.preview-table,
.preview-data,
.preview-signature,
.preview-date {
    text-align: center;
    color: #6c757d;
}

/* === Resize Handles === */
.canvas-element.interact-dragging,
.element-holder-container.interact-dragging {
    opacity: 0.8;
    z-index: 1000;
}

/* === Modals === */
.modal-header {
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.modal-header .modal-title {
    font-weight: 600;
    color: #495057;
}

.form-group label {
    font-weight: 500;
    color: #495057;
    font-size: 14px;
}

.text-danger {
    color: #dc3545 !important;
}

/* === Auto-save Indicator === */
.auto-save-indicator {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 10px 16px;
    border-radius: 25px;
    box-shadow: 0 4px 12px rgba(40,167,69,0.4);
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s;
    z-index: 2000;
    font-size: 13px;
    font-weight: 500;
}

.auto-save-indicator.show {
    opacity: 1;
    transform: translateY(0);
}

.auto-save-indicator i {
    margin-right: 6px;
}

/* === Responsive Adjustments === */
@media (max-width: 1400px) {
    .sections-panel {
        width: 240px;
    }
    
    .designer-canvas-wrapper {
        margin-left: 240px;
    }
}

/* === Utility Classes === */
.btn-xs {
    padding: 2px 6px;
    font-size: 11px;
}
</style>

