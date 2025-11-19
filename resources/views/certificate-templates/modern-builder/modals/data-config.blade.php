<div class="modal fade" id="data-config-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Data Configuration</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="data-config-form">
                    <input type="hidden" id="data-config-target-id">
                    <input type="hidden" id="data-config-target-type">
                    
                    <div class="form-group">
                        <label for="data-type">Data Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="data-type" required>
                            <option value="static">Static Data (Manual Entry)</option>
                            <option value="dynamic_model">Dynamic Model Data</option>
                            <option value="dynamic_derived">Dynamic Derived Data (Query Builder)</option>
                        </select>
                    </div>
                    
                    <!-- Static Data -->
                    <div id="static-data-config" class="data-config-section">
                        <div class="form-group">
                            <label for="static-value">Value</label>
                            <textarea class="form-control" id="static-value" rows="5"></textarea>
                        </div>
                    </div>
                    
                    <!-- Dynamic Model Data -->
                    <div id="dynamic-model-config" class="data-config-section" style="display: none;">
                        <div class="form-group">
                            <label for="model-class">Model Class</label>
                            <input type="text" class="form-control" id="model-class" placeholder="e.g., App\Models\User">
                        </div>
                        <div class="form-group">
                            <label for="model-field">Field Name</label>
                            <input type="text" class="form-control" id="model-field" placeholder="e.g., name, email">
                        </div>
                        <div class="form-group">
                            <label for="model-row-id">Row/ID Reference (Optional)</label>
                            <input type="text" class="form-control" id="model-row-id" placeholder="Leave empty for first record">
                        </div>
                    </div>
                    
                    <!-- Dynamic Derived Data -->
                    <div id="dynamic-derived-config" class="data-config-section" style="display: none;">
                        <div class="form-group">
                            <button type="button" class="btn btn-outline-primary" id="open-query-builder-btn">
                                <i class="mdi mdi-database-search"></i> Open Query Builder
                            </button>
                        </div>
                        <div class="form-group">
                            <label for="raw-sql">Or Enter Raw SQL (Sandboxed)</label>
                            <textarea class="form-control" id="raw-sql" rows="5" placeholder="SELECT column FROM table WHERE condition LIMIT 1"></textarea>
                            <small class="form-text text-muted">Only SELECT queries are allowed. Dangerous keywords are blocked.</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-data-config">Save Data Config</button>
            </div>
        </div>
    </div>
</div>


