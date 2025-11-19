// Query Builder UI for Modern Builder
class QueryBuilder {
    constructor() {
        this.tables = [];
        this.columns = {};
        this.init();
    }

    async init() {
        await this.loadTables();
        this.bindEvents();
    }

    async loadTables() {
        try {
            const response = await fetch('/certificate-templates/query-builder/tables', {
                headers: {
                    'X-CSRF-TOKEN': window.ModernBuilder?.csrfToken || ''
                }
            });
            const data = await response.json();
            if (data.success) {
                this.tables = data.tables;
                this.populateTableSelect();
            }
        } catch (error) {
            console.error('Failed to load tables:', error);
        }
    }

    populateTableSelect() {
        const select = $('#qb-table');
        select.empty().append('<option value="">Select a table...</option>');
        this.tables.forEach(table => {
            select.append(`<option value="${table.name}">${table.label}</option>`);
        });
    }

    bindEvents() {
        $('#qb-table').on('change', (e) => {
            const tableName = $(e.target).val();
            if (tableName) {
                this.loadTableColumns(tableName);
            }
        });

        $('#add-condition-btn').on('click', () => {
            this.addCondition();
        });

        $(document).on('click', '.remove-condition', function() {
            $(this).closest('.condition-row').remove();
        });

        $('#preview-query-btn').on('click', () => {
            this.previewQuery();
        });

        $('#save-query-config').on('click', () => {
            this.saveQueryConfig();
        });
    }

    async loadTableColumns(tableName) {
        try {
            const response = await fetch(`/certificate-templates/query-builder/columns/${tableName}`, {
                headers: {
                    'X-CSRF-TOKEN': window.ModernBuilder?.csrfToken || ''
                }
            });
            const data = await response.json();
            if (data.success) {
                this.columns[tableName] = data.columns;
                this.populateColumnSelects(tableName);
            }
        } catch (error) {
            console.error('Failed to load columns:', error);
        }
    }

    populateColumnSelects(tableName) {
        const columns = this.columns[tableName] || [];
        
        // Update column select
        const columnSelect = $('#qb-columns');
        columnSelect.empty().append('<option value="*">All Columns (*)</option>');
        columns.forEach(col => {
            columnSelect.append(`<option value="${col.name}">${col.label} (${col.type})</option>`);
        });

        // Update condition field selects
        $('.condition-field').each(function() {
            if ($(this).find('option').length <= 1) {
                columns.forEach(col => {
                    $(this).append(`<option value="${col.name}">${col.label}</option>`);
                });
            }
        });

        // Update order by select
        const orderSelect = $('#qb-order-by');
        orderSelect.empty().append('<option value="">No ordering</option>');
        columns.forEach(col => {
            orderSelect.append(`<option value="${col.name}">${col.label}</option>`);
        });
    }

    addCondition() {
        const tableName = $('#qb-table').val();
        if (!tableName) {
            alert('Please select a table first');
            return;
        }

        const columns = this.columns[tableName] || [];
        let options = '<option value="">Select field...</option>';
        columns.forEach(col => {
            options += `<option value="${col.name}">${col.label}</option>`;
        });

        const conditionHtml = `
            <div class="condition-row mb-2">
                <div class="row">
                    <div class="col-md-4">
                        <select class="form-control form-control-sm condition-field">
                            ${options}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control form-control-sm condition-operator">
                            <option value="=">=</option>
                            <option value="!=">!=</option>
                            <option value="<"><</option>
                            <option value="<="><=</option>
                            <option value=">">></option>
                            <option value=">=">>=</option>
                            <option value="LIKE">LIKE</option>
                            <option value="IN">IN</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm condition-value" placeholder="Value">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-condition">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#qb-conditions').append(conditionHtml);
    }

    buildQueryConfig() {
        const table = $('#qb-table').val();
        const columns = $('#qb-columns').val() || ['*'];
        const limit = $('#qb-limit').val() || null;
        const orderBy = $('#qb-order-by').val();
        const orderDirection = $('#qb-order-direction').val() || 'ASC';

        const conditions = [];
        $('.condition-row').each(function() {
            const field = $(this).find('.condition-field').val();
            const operator = $(this).find('.condition-operator').val();
            const value = $(this).find('.condition-value').val();
            
            if (field && operator && value) {
                conditions.push({ field, operator, value });
            }
        });

        const order = orderBy ? [{ field: orderBy, direction: orderDirection }] : [];

        return {
            table,
            columns: Array.isArray(columns) ? columns : [columns],
            conditions,
            order_by: order,
            limit: limit ? parseInt(limit) : null
        };
    }

    async previewQuery() {
        const config = this.buildQueryConfig();
        
        try {
            const response = await fetch('/certificate-templates/query-builder/preview', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.ModernBuilder?.csrfToken || ''
                },
                body: JSON.stringify(config)
            });
            const data = await response.json();
            
            if (data.success) {
                this.displayPreview(data.data);
            } else {
                alert('Query failed: ' + (data.error || 'Unknown error'));
            }
        } catch (error) {
            console.error('Preview error:', error);
            alert('Failed to preview query');
        }
    }

    displayPreview(data) {
        if (!data || data.length === 0) {
            $('#query-preview').hide();
            return;
        }

        const headers = Object.keys(data[0]);
        let headerHtml = '<tr>';
        headers.forEach(h => {
            headerHtml += `<th>${h}</th>`;
        });
        headerHtml += '</tr>';
        $('#preview-headers').html(headerHtml);

        let bodyHtml = '';
        data.slice(0, 10).forEach(row => { // Limit to 10 rows for preview
            bodyHtml += '<tr>';
            headers.forEach(h => {
                bodyHtml += `<td>${row[h] || ''}</td>`;
            });
            bodyHtml += '</tr>';
        });
        $('#preview-body').html(bodyHtml);
        $('#query-preview').show();
    }

    saveQueryConfig() {
        const config = this.buildQueryConfig();
        const targetId = $('#query-builder-modal').data('target-id');
        
        if (targetId) {
            // Update the data config modal with query config
            $('#data-type').val('dynamic_derived');
            $('#raw-sql').val(''); // Clear raw SQL if using visual builder
            $('#data-config-modal').data('query-config', config);
            
            // Trigger data type change to show dynamic derived config
            $('#data-type').trigger('change');
            
            // Close query builder and return to data config modal
            $('#query-builder-modal').modal('hide');
            $('#data-config-modal').modal('show');
        }
    }
    
    getQueryConfig() {
        return this.buildQueryConfig();
    }
}

window.QueryBuilder = QueryBuilder;

