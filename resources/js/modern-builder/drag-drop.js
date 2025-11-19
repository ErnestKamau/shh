// Drag & Drop System for Modern Builder
class DragDropManager {
    constructor() {
        this.init();
    }

    init() {
        // Initialize Interact.js for all draggable elements
        this.initSectionDragging();
        this.initRowDragging();
        this.initColumnDragging();
        this.initCellDragging();
        this.initElementDragging();
    }

    initSectionDragging() {
        interact('.canvas-section').draggable({
            inertia: true,
            modifiers: [
                interact.modifiers.restrictRect({
                    restriction: '#builder-canvas',
                    endOnly: true
                })
            ],
            autoScroll: true,
            onmove: (event) => {
                const target = event.target;
                const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

                target.style.transform = `translate(${x}px, ${y}px)`;
                target.setAttribute('data-x', x);
                target.setAttribute('data-y', y);
            },
            onend: (event) => {
                // Save new position
                this.saveSectionPosition(event.target);
            }
        });
    }

    initRowDragging() {
        interact('.canvas-row').draggable({
            inertia: true,
            autoScroll: true,
            onmove: (event) => {
                const target = event.target;
                const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

                target.style.transform = `translateY(${y}px)`;
                target.setAttribute('data-y', y);
            },
            onend: (event) => {
                this.reorderRow(event.target);
            }
        });
    }

    initColumnDragging() {
        interact('.canvas-column').draggable({
            inertia: true,
            autoScroll: true,
            onmove: (event) => {
                const target = event.target;
                const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;

                target.style.transform = `translateX(${x}px)`;
                target.setAttribute('data-x', x);
            },
            onend: (event) => {
                this.reorderColumn(event.target);
            }
        });
    }

    initCellDragging() {
        interact('.canvas-cell').draggable({
            inertia: true,
            autoScroll: true,
            onmove: (event) => {
                const target = event.target;
                const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

                target.style.transform = `translateY(${y}px)`;
                target.setAttribute('data-y', y);
            },
            onend: (event) => {
                this.reorderCell(event.target);
            }
        });
    }

    initElementDragging() {
        interact('.canvas-element').draggable({
            inertia: true,
            autoScroll: true,
            onmove: (event) => {
                const target = event.target;
                const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

                target.style.transform = `translate(${x}px, ${y}px)`;
                target.setAttribute('data-x', x);
                target.setAttribute('data-y', y);
            },
            onend: (event) => {
                this.handleElementDrop(event);
            }
        });

        // Make cells drop zones for elements
        interact('.canvas-cell').dropzone({
            accept: '.canvas-element',
            overlap: 0.5,
            ondrop: (event) => {
                const element = event.relatedTarget;
                const cell = event.target;
                this.moveElementToCell(element, cell);
            }
        });
    }

    saveSectionPosition(section) {
        // Save section position via AJAX
        const sectionId = section.dataset.sectionId;
        // Implementation for saving position
    }

    reorderRow(row) {
        // Reorder row within section
        const rowId = row.dataset.rowId;
        const sectionId = row.closest('.canvas-section').dataset.sectionId;
        // Implementation for reordering
    }

    reorderColumn(column) {
        // Reorder column within row
        const columnId = column.dataset.columnId;
        // Implementation for reordering
    }

    reorderCell(cell) {
        // Reorder cell within column
        const cellId = cell.dataset.cellId;
        // Implementation for reordering
    }

    handleElementDrop(event) {
        // Handle element drop
        const element = event.target;
        const dropzone = event.relatedTarget;
        
        if (dropzone && dropzone.classList.contains('canvas-cell')) {
            this.moveElementToCell(element, dropzone);
        }
    }

    moveElementToCell(element, cell) {
        const elementId = element.dataset.elementId;
        const cellId = cell.dataset.cellId;
        
        // Update element's parent_cell_id via AJAX
        $.ajax({
            url: `/certificate-templates/modern-elements/${elementId}/position`,
            method: 'PUT',
            data: {
                parent_cell_id: cellId,
                _method: 'PUT'
            },
            headers: {
                'X-CSRF-TOKEN': ModernBuilder.csrfToken
            },
            success: (response) => {
                if (response.success) {
                    // Move element DOM to new cell
                    cell.querySelector('.cell-content').appendChild(element);
                    element.style.transform = '';
                    element.removeAttribute('data-x');
                    element.removeAttribute('data-y');
                }
            }
        });
    }
}

// Initialize when DOM is ready
if (typeof interact !== 'undefined') {
    const dragDropManager = new DragDropManager();
}


