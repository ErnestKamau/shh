<div class="modal fade" id="section-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Section Configuration</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="section-form">
                    <input type="hidden" id="section-id">
                    <div class="form-group">
                        <label for="section-title">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="section-title" required>
                    </div>
                    <div class="form-group">
                        <label for="section-description">Description</label>
                        <textarea class="form-control" id="section-description" rows="3"></textarea>
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




