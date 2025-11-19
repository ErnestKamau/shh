<div class="modal fade" id="element-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Element Configuration</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="element-form">
                    <input type="hidden" id="element-id">
                    <input type="hidden" id="element-cell-id">
                    <input type="hidden" id="element-type">
                    
                    <div class="form-group" id="element-heading-level-group" style="display: none;">
                        <label for="heading-level">Heading Level</label>
                        <select class="form-control" id="heading-level">
                            <option value="1">H1 (Largest)</option>
                            <option value="2">H2</option>
                            <option value="3">H3</option>
                            <option value="4">H4</option>
                            <option value="5">H5</option>
                            <option value="6">H6 (Smallest)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="element-content">Content</label>
                        <textarea class="form-control" id="element-content" rows="5"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="button" class="btn btn-outline-primary" id="configure-css-btn">
                            <i class="mdi mdi-palette"></i> Configure CSS
                        </button>
                        <button type="button" class="btn btn-outline-info" id="configure-data-btn">
                            <i class="mdi mdi-database"></i> Configure Data
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-element">Save Element</button>
            </div>
        </div>
    </div>
</div>

