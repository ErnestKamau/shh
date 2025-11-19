<div class="modal fade" id="css-config-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">CSS Configuration</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="css-config-form">
                    <input type="hidden" id="css-config-target-id">
                    <input type="hidden" id="css-config-target-type">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Margin</h6>
                            <div class="form-group row">
                                <div class="col-3">
                                    <input type="text" class="form-control form-control-sm" id="margin-top" placeholder="Top">
                                </div>
                                <div class="col-3">
                                    <input type="text" class="form-control form-control-sm" id="margin-right" placeholder="Right">
                                </div>
                                <div class="col-3">
                                    <input type="text" class="form-control form-control-sm" id="margin-bottom" placeholder="Bottom">
                                </div>
                                <div class="col-3">
                                    <input type="text" class="form-control form-control-sm" id="margin-left" placeholder="Left">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Padding</h6>
                            <div class="form-group row">
                                <div class="col-3">
                                    <input type="text" class="form-control form-control-sm" id="padding-top" placeholder="Top">
                                </div>
                                <div class="col-3">
                                    <input type="text" class="form-control form-control-sm" id="padding-right" placeholder="Right">
                                </div>
                                <div class="col-3">
                                    <input type="text" class="form-control form-control-sm" id="padding-bottom" placeholder="Bottom">
                                </div>
                                <div class="col-3">
                                    <input type="text" class="form-control form-control-sm" id="padding-left" placeholder="Left">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="width">Width</label>
                                <input type="text" class="form-control" id="width" placeholder="e.g., 100%, 200px">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="height">Height</label>
                                <input type="text" class="form-control" id="height" placeholder="e.g., auto, 100px">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="alignment">Alignment</label>
                        <select class="form-control" id="alignment">
                            <option value="left">Left</option>
                            <option value="center">Center</option>
                            <option value="right">Right</option>
                            <option value="justify">Justify</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="background-color">Background Color</label>
                                <input type="color" class="form-control" id="background-color">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="text-color">Text Color</label>
                                <input type="color" class="form-control" id="text-color">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="border-width">Border Width</label>
                                <input type="text" class="form-control" id="border-width" placeholder="e.g., 1px">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="custom-class">Custom CSS Class</label>
                        <input type="text" class="form-control" id="custom-class" placeholder="e.g., my-custom-class">
                    </div>
                    
                    <div class="form-group">
                        <label for="custom-css">Custom CSS</label>
                        <textarea class="form-control" id="custom-css" rows="4" placeholder="e.g., font-weight: bold; border-radius: 5px;"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-css-config">Save CSS</button>
            </div>
        </div>
    </div>
</div>



