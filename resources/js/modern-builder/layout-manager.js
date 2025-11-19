// Layout Manager for Modern Builder
class LayoutManager {
    constructor() {
        this.templateId = window.ModernBuilder?.templateId || null;
        this.csrfToken = window.ModernBuilder?.csrfToken || '';
    }

    addRow(sectionId) {
        $.ajax({
            url: `/certificate-templates/modern-sections/${sectionId}/add-row`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': this.csrfToken },
            success: (response) => {
                if (response.success) {
                    location.reload(); // Reload to show new row
                }
            }
        });
    }

    addColumn(sectionId, rowId) {
        $.ajax({
            url: `/certificate-templates/modern-sections/${sectionId}/add-column`,
            method: 'POST',
            data: { row_id: rowId },
            headers: { 'X-CSRF-TOKEN': this.csrfToken },
            success: (response) => {
                if (response.success) {
                    location.reload();
                }
            }
        });
    }

    addCell(sectionId, columnId) {
        $.ajax({
            url: `/certificate-templates/modern-sections/${sectionId}/add-cell`,
            method: 'POST',
            data: { column_id: columnId },
            headers: { 'X-CSRF-TOKEN': this.csrfToken },
            success: (response) => {
                if (response.success) {
                    location.reload();
                }
            }
        });
    }

    removeRow(sectionId, rowId) {
        if (!confirm('Are you sure you want to remove this row?')) return;
        
        // Remove from layout structure via section update
        this.updateSectionLayout(sectionId, (layout) => {
            layout.rows = layout.rows.filter(r => r.id !== rowId);
            return layout;
        });
    }

    removeColumn(sectionId, columnId) {
        if (!confirm('Are you sure you want to remove this column?')) return;
        
        this.updateSectionLayout(sectionId, (layout) => {
            layout.rows.forEach(row => {
                row.columns = row.columns.filter(c => c.id !== columnId);
            });
            return layout;
        });
    }

    removeCell(sectionId, cellId) {
        if (!confirm('Are you sure you want to remove this cell?')) return;
        
        this.updateSectionLayout(sectionId, (layout) => {
            layout.rows.forEach(row => {
                row.columns.forEach(column => {
                    column.cells = column.cells.filter(c => c.id !== cellId);
                });
            });
            return layout;
        });
    }

    updateSectionLayout(sectionId, updateFn) {
        // Get current layout
        $.ajax({
            url: `/certificate-templates/modern-sections/${sectionId}`,
            method: 'GET',
            success: (response) => {
                if (response.success) {
                    const section = response.section;
                    const layout = section.layout_structure || { rows: [] };
                    const updatedLayout = updateFn(layout);
                    
                    // Save updated layout
                    $.ajax({
                        url: `/certificate-templates/modern-sections/${sectionId}`,
                        method: 'PUT',
                        data: {
                            layout_structure: updatedLayout,
                            _method: 'PUT'
                        },
                        headers: { 'X-CSRF-TOKEN': this.csrfToken },
                        success: () => {
                            location.reload();
                        }
                    });
                }
            }
        });
    }

    addSubSection(sectionId, cellId, subSectionId) {
        $.ajax({
            url: `/certificate-templates/modern-sections/${sectionId}/add-sub-section`,
            method: 'POST',
            data: {
                cell_id: cellId,
                sub_section_id: subSectionId
            },
            headers: { 'X-CSRF-TOKEN': this.csrfToken },
            success: (response) => {
                if (response.success) {
                    location.reload();
                }
            }
        });
    }
}

// Export for use
window.LayoutManager = LayoutManager;


