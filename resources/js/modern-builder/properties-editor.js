// Properties Editor for Modern Builder
class PropertiesEditor {
    constructor() {
        this.selectedElement = null;
        this.selectedSection = null;
    }

    selectElement(elementId) {
        this.selectedElement = elementId;
        this.selectedSection = null;
        this.loadElementProperties(elementId);
    }

    selectSection(sectionId) {
        this.selectedSection = sectionId;
        this.selectedElement = null;
        this.loadSectionProperties(sectionId);
    }

    loadElementProperties(elementId) {
        $.ajax({
            url: `/certificate-templates/modern-elements/${elementId}`,
            method: 'GET',
            success: (response) => {
                if (response.success) {
                    this.displayElementProperties(response.element);
                }
            }
        });
    }

    loadSectionProperties(sectionId) {
        $.ajax({
            url: `/certificate-templates/modern-sections/${sectionId}`,
            method: 'GET',
            success: (response) => {
                if (response.success) {
                    this.displaySectionProperties(response.section);
                }
            }
        });
    }

    displayElementProperties(element) {
        const html = `
            <div class="properties-panel">
                <h6>Element: ${element.element_type}</h6>
                <div class="form-group">
                    <label>Content</label>
                    <textarea class="form-control element-content" rows="3">${element.content || ''}</textarea>
                </div>
                <button class="btn btn-sm btn-outline-primary configure-css" data-id="${element.id}">
                    Configure CSS
                </button>
                <button class="btn btn-sm btn-outline-info configure-data" data-id="${element.id}">
                    Configure Data
                </button>
            </div>
        `;
        $('#properties-content').html(html);
    }

    displaySectionProperties(section) {
        const html = `
            <div class="properties-panel">
                <h6>Section: ${section.title}</h6>
                <div class="form-group">
                    <label>Description</label>
                    <textarea class="form-control section-description" rows="2">${section.description || ''}</textarea>
                </div>
                <button class="btn btn-sm btn-outline-primary configure-section-css" data-id="${section.id}">
                    Configure CSS
                </button>
            </div>
        `;
        $('#properties-content').html(html);
    }

    saveCssConfig(targetId, targetType, cssConfig) {
        const url = targetType === 'element' 
            ? `/certificate-templates/modern-elements/${targetId}/css-config`
            : `/certificate-templates/modern-sections/${targetId}/css-config`;
        
        $.ajax({
            url: url,
            method: 'PUT',
            data: {
                css_config: cssConfig,
                _method: 'PUT'
            },
            headers: { 'X-CSRF-TOKEN': window.ModernBuilder?.csrfToken || '' },
            success: (response) => {
                if (response.success) {
                    this.showMessage('CSS configuration saved');
                    // Update preview
                    this.updatePreview();
                }
            }
        });
    }

    saveDataConfig(targetId, dataConfig) {
        $.ajax({
            url: `/certificate-templates/modern-elements/${targetId}/data-config`,
            method: 'PUT',
            data: {
                data_config: dataConfig,
                _method: 'PUT'
            },
            headers: { 'X-CSRF-TOKEN': window.ModernBuilder?.csrfToken || '' },
            success: (response) => {
                if (response.success) {
                    this.showMessage('Data configuration saved');
                    this.updatePreview();
                }
            }
        });
    }

    updatePreview() {
        // Trigger preview update
        if (window.PreviewEngine) {
            window.PreviewEngine.update();
        }
    }

    showMessage(message) {
        // Show success message
        const alert = `<div class="alert alert-success alert-dismissible fade show">
            ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>`;
        $('main').prepend(alert);
        setTimeout(() => $('.alert').fadeOut(), 3000);
    }
}

window.PropertiesEditor = PropertiesEditor;


