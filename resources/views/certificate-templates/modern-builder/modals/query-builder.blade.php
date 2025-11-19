<div class="modal fade" id="query-builder-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Visual Query Builder</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="query-builder-form">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="qb-table">Table <span class="text-danger">*</span></label>
                                <select class="form-control" id="qb-table" required>
                                    <option value="">Select a table...</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="qb-columns">Columns</label>
                                <select class="form-control" id="qb-columns" multiple>
                                    <option value="*">All Columns (*)</option>
                                </select>
                                <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Conditions (WHERE)</label>
                        <div id="qb-conditions">
                            <div class="condition-row mb-2">
                                <div class="row">
                                    <div class="col-md-4">
                                        <select class="form-control form-control-sm condition-field">
                                            <option value="">Select field...</option>
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
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-condition-btn">
                            <i class="mdi mdi-plus"></i> Add Condition
                        </button>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="qb-order-by">Order By</label>
                                <select class="form-control" id="qb-order-by">
                                    <option value="">No ordering</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="qb-order-direction">Direction</label>
                                <select class="form-control" id="qb-order-direction">
                                    <option value="ASC">ASC</option>
                                    <option value="DESC">DESC</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="qb-limit">Limit</label>
                                <input type="number" class="form-control" id="qb-limit" min="1" placeholder="e.g., 10">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="button" class="btn btn-outline-info" id="preview-query-btn">
                            <i class="mdi mdi-eye"></i> Preview Query Results
                        </button>
                    </div>
                    
                    <div id="query-preview" style="display: none;">
                        <h6>Query Preview</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead id="preview-headers"></thead>
                                <tbody id="preview-body"></tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-query-config">Save Query</button>
            </div>
        </div>
    </div>
</div>


