<div>
    <div class="card">
        <div class="card-header">
            <h5 class="card-tile mb-0">
                <i class="mdi mdi-attachment"></i> Attachments
                <div class="float-right mb-2" wire:ignore>
                    <button type="button" 
                            class="btn btn-outline-primary btn-sm mr-1" 
                            id="merge-attachments-btn" 
                            disabled>
                        <i class="mdi mdi-file-document-box-multiple"></i> Merge Selected 
                        <span class="badge badge-primary" id="merge-count">0</span>
                    </button>
                    <span class="btn btn-outline-info btn-sm" data-target="#add-attachment-batch" data-toggle="modal">
                        <i class="mdi mdi-plus"></i> Add
                    </span>
                </div>
            </h5>
        </div>
        <div class="card-body">
            <!-- Search Input -->
            <div class="mb-3">
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       class="form-control" 
                       placeholder="Search attachments by filename, type, or uploader...">
            </div>

            <div class="table-responsive" id="attachments-container">
                <table class="table table-bordered mb-0" id="attachments-table">
                    <thead class="bg-light p-2">
                        <tr>
                            <th style="width: 30px;">
                                <input type="checkbox" id="check-all-attachments">
                            </th>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Upload Date</th>
                            <th>Uploaded By</th>
                            <th>File</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($attachments->count() > 0)
                            @if(Auth::user()->is_client == 1)
                                @foreach($attachments as $a)
                                    @if($a->is_internal == 0)
                                        <tr>
                                            <td>
                                                <input type="checkbox" 
                                                       class="attachment-checkbox" 
                                                       value="{{$a->id}}" 
                                                       data-title="{{$a->title ?? 'N/a'}}" 
                                                       data-type="{{$a->attachtypename ?? 'General'}}">
                                            </td>
                                            <td>{{ $a->attachtypename}}</td>
                                            <td>{{$a->title ?? 'N/a'}}</td>
                                            <td>{{date('Y-m-d',strtotime($a->created_at))}}</td>
                                            <td>{{$a->uploaduser}}</td>
                                            <td class="text-center">
                                                <a href="{{$a->attachment_url}}" target="_blank" data-toggle="tooltip" data-title="View Attachment" class="btn-sm btn btn-outline-dark">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                                @if(strtolower($a->file_type) == 'pdf')
                                                    <a href="{{ route('show-pdf-annotation-page', $a->id) }}" 
                                                       class="btn-sm btn btn-outline-info ml-1" 
                                                       data-toggle="tooltip" 
                                                       title="Annotate PDF">
                                                        <i class="mdi mdi-comment-text"></i>
                                                        @if($a->annotations && $a->annotations->count() > 0)
                                                            <span class="badge badge-primary">{{ $a->annotations->count() }}</span>
                                                        @endif
                                                    </a>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" 
                                                    wire:click="deleteAttachment({{$a->id}})"
                                                    onclick="return confirm('Are you sure you want to delete attachment: {{$a->title}}?')"
                                                    class="btn btn-sm btn-outline-danger" 
                                                    data-toggle="tooltip" 
                                                    title="Delete Attachment">
                                                    <i class="mdi mdi-delete-empty"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @else
                                @foreach($attachments as $a)
                                    <tr>
                                        <td><input type="checkbox" class="attachment-checkbox" value="{{$a->id}}" data-title="{{$a->title}}" data-type="{{$a->attachtypename}}"></td>
                                        <td>{{ $a->attachtypename}}</td>
                                        <td>{{$a->title ?? 'N/a'}}</td>
                                        <td>{{date('Y-m-d',strtotime($a->created_at))}}</td>
                                        <td>{{$a->uploaduser}}</td>
                                        <td class="text-center">
                                            <a href="{{$a->attachment_url}}" target="_blank" data-toggle="tooltip" data-title="View Attachment" class="btn-sm btn btn-outline-dark">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            @if(strtolower($a->file_type) == 'pdf')
                                                <a href="{{ route('show-pdf-annotation-page', $a->id) }}" 
                                                   class="btn-sm btn btn-outline-info ml-1" 
                                                   data-toggle="tooltip" 
                                                   title="Annotate PDF">
                                                    <i class="mdi mdi-comment-text"></i>
                                                    @if($a->annotations && $a->annotations->count() > 0)
                                                        <span class="badge badge-primary">{{ $a->annotations->count() }}</span>
                                                    @endif
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="btn btn-sm btn-outline-danger delete-attachment-btn" 
                                                data-id="{{$a->id}}" 
                                                data-title="{{$a->title}}" 
                                                data-toggle="tooltip" 
                                                title="Delete Attachment">
                                                <i class="mdi mdi-delete-empty"></i>
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @else
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="mdi mdi-paperclip text-muted" style="font-size: 48px;"></i>
                                    <h6 class="mt-3 text-muted">No Attachments Found</h6>
                                    <p class="text-muted mb-0"><small>
                                        @if($search)
                                            No attachments match your search criteria
                                        @else
                                            There are no batch attachments to display
                                        @endif
                                    </small></p>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Merge Attachments Modal -->
    <div class="modal fade" id="merge-attachments-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{route('merge-attachments')}}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Merge Attachments</h5>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-info p-2 mb-3">
                            <i class="mdi mdi-information"></i> Drag and drop items to reorder the report sections.
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="control-label font-weight-bold text-muted text-uppercase small">Title</label>
                            <input type="text" name="title" class="form-control form-control-lg" required placeholder="Merged Report Title" style="border-radius: 8px;">
                        </div>

                        <div class="form-group mb-4">
                            <label class="control-label font-weight-bold text-muted text-uppercase small">Attachment Type</label>
                            <select name="attachment_type" class="form-control form-control-lg" required style="border-radius: 8px;">
                                <option value="">Choose Attachment Type ...</option>
                                @foreach($attachmentTypes as $aType)
                                <option value="{{$aType->id}}">{{$aType->value}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="control-label font-weight-bold text-muted text-uppercase small">Selected Files (Order Matters)</label>
                            <ul id="sortable-attachments" class="list-group">
                                <!-- Populated by JS -->
                            </ul>
                        </div>
                        <input type="hidden" name="attachment_ids" id="ordered-attachment-ids">
                        <input type="hidden" name="batch_id" value="{{$batch->id}}">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success btn-sm"><i class="mdi mdi-check"></i> Merge & Save</button>
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Attachment Modal -->
    <div class="modal fade" id="add-attachment-batch" role="dialog">
        <div class="modal-dialog">
            <form action="{{route('add_batch_attachment')}}" method="post" enctype="multipart/form-data" class="modal-content">
                @csrf 
                <div class="modal-header">
                    <h5 class="modal-title">Add Attachment For {{$batch->batch_code}}</h5>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group mb-4">
                        <label class="control-label font-weight-bold text-muted text-uppercase small">Title</label>
                        <input type="text" name="title" id="" class="form-control form-control-lg" placeholder="e.g. Lab Report, Invoice..." required style="border-radius: 8px;">
                    </div>
                    <div class="form-group mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="control-label font-weight-bold text-muted text-uppercase small mb-2">Attachment Type</label>
                            <span class="btn btn-xs btn-info mb-2" style="cursor: pointer; padding: 2px 6px; font-size: 10px; border-radius: 4px;" data-toggle="modal" data-target="#add-attachment-type-modal" title="Add New Attachment Type">
                                <i class="mdi mdi-plus"></i> ADD NEW
                            </span>
                        </div>
                        <select name="attachment_type" id="attachment_type_select" class="form-control form-control-lg" style="border-radius: 8px;">
                            <option value="">Choose Attachment Type ...</option>
                            @foreach($attachmentTypes as $aType)
                            <option value="{{$aType->id}}">{{$aType->value}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-4">
                        <label class="control-label font-weight-bold text-muted text-uppercase small">Upload File</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="customFile" name="attachment" required>
                            <label class="custom-file-label" for="customFile" style="border-radius: 8px;">Choose file...</label>
                        </div>
                    </div>
                    
                    <div class="form-group mb-2">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="internalUse" name="is_internal">
                            <label class="custom-control-label font-weight-bold text-muted small" for="internalUse">For Internal Use Only</label>
                        </div>
                    </div>
                    <input type="hidden" name="batch_id" value="{{$batch->id}}">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-sm"><i class="mdi mdi-thumb-up"></i> Save</button>
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Attachment Type Modal -->
    <div class="modal fade" id="add-attachment-type-modal" role="dialog" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold">New Type</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input type="text" class="form-control" id="new_attachment_type_name" placeholder="Type Name..." style="border-radius: 8px; background-color: #f8f9fa; border: 1px solid #e9ecef;">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4" onclick="saveAttachmentType()">Save</button>
                </div>
            </div>
        </div>
    </div>


    <style>
        #sortable-attachments { list-style-type: none; margin: 0; padding: 0; }
        #sortable-attachments li { cursor: move; border: 1px solid #ddd; background: #fff; border-radius: 4px; }
        #sortable-attachments li:hover { background-color: #f8f9fa; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        (function() {
            'use strict';
            
            function updateMergeButtonState() {
                var checkboxes = document.querySelectorAll('.attachment-checkbox:checked');
                var count = checkboxes.length;
                var mergeBtn = document.getElementById('merge-attachments-btn');
                var mergeCount = document.getElementById('merge-count');
                
                if (mergeCount) {
                    mergeCount.textContent = count;
                }
                
                if (mergeBtn) {
                    if (count >= 2) {
                        mergeBtn.disabled = false;
                        mergeBtn.classList.remove('disabled');
                    } else {
                        mergeBtn.disabled = true;
                        mergeBtn.classList.add('disabled');
                    }
                }
            }
            
            function initializeHandlers() {
                // Use event delegation on the container (which persists through Livewire updates)
                var container = document.getElementById('attachments-container');
                if (!container) {
                    // Fallback to document if container not found
                    container = document;
                }
                
                // Remove old listener and add new one
                container.removeEventListener('change', handleCheckboxChange);
                container.addEventListener('change', handleCheckboxChange);
                
                // Check all handler
                var checkAll = document.getElementById('check-all-attachments');
                if (checkAll) {
                    checkAll.removeEventListener('change', handleCheckAllChange);
                    checkAll.addEventListener('change', handleCheckAllChange);
                }
                
                // Merge button handler
                var mergeBtn = document.getElementById('merge-attachments-btn');
                if (mergeBtn) {
                    mergeBtn.removeEventListener('click', handleMergeClick);
                    mergeBtn.addEventListener('click', handleMergeClick);
                }
                
                // Initial state
                updateMergeButtonState();
            }
            
            function handleCheckboxChange(e) {
                if (e.target.classList.contains('attachment-checkbox')) {
                    updateMergeButtonState();
                    
                    // Update check-all state
                    var table = document.getElementById('attachments-table');
                    if (table) {
                        var allCheckboxes = table.querySelectorAll('.attachment-checkbox');
                        var checkedCheckboxes = table.querySelectorAll('.attachment-checkbox:checked');
                        var checkAll = document.getElementById('check-all-attachments');
                        if (checkAll && allCheckboxes.length > 0) {
                            checkAll.checked = checkedCheckboxes.length === allCheckboxes.length;
                        }
                    }
                }
            }
            
            function handleCheckAllChange(e) {
                var table = document.getElementById('attachments-table');
                if (table) {
                    var isChecked = e.target.checked;
                    table.querySelectorAll('.attachment-checkbox').forEach(function(cb) {
                        cb.checked = isChecked;
                    });
                    updateMergeButtonState();
                }
            }
            
            function handleMergeClick(e) {
                e.preventDefault();
                
                var table = document.getElementById('attachments-table');
                if (!table) return;
                
                var checkedBoxes = table.querySelectorAll('.attachment-checkbox:checked');
                if (checkedBoxes.length < 2) {
                    alert('Please select at least 2 attachments to merge.');
                    return;
                }
                
                // Populate modal
                var sortableList = document.getElementById('sortable-attachments');
                if (sortableList) {
                    sortableList.innerHTML = '';
                    
                    checkedBoxes.forEach(function(cb) {
                        var id = cb.value;
                        var title = cb.getAttribute('data-title') || 'N/a';
                        var type = cb.getAttribute('data-type') || 'General';
                        
                        var li = document.createElement('li');
                        li.className = 'list-group-item p-2 mb-1 d-flex justify-content-between align-items-center';
                        li.setAttribute('data-id', id);
                        li.innerHTML = '<span><i class="mdi mdi-drag-vertical mr-2 text-muted"></i> ' + title + ' <small class="text-muted">(' + type + ')</small></span>';
                        sortableList.appendChild(li);
                    });
                    
                    // Show modal (using jQuery/bootstrap)
                    if (window.jQuery && window.jQuery.fn.modal) {
                        window.jQuery('#merge-attachments-modal').modal('show');
                    }
                    
                    // Initialize SortableJS
                    if (window.Sortable) {
                        // Destroy existing instance
                        if (sortableList.sortableInstance) {
                            sortableList.sortableInstance.destroy();
                        }
                        
                        sortableList.sortableInstance = window.Sortable.create(sortableList, {
                            animation: 150,
                            onEnd: function() {
                                var ids = [];
                                sortableList.querySelectorAll('li').forEach(function(li) {
                                    ids.push(li.getAttribute('data-id'));
                                });
                                var hiddenInput = document.getElementById('ordered-attachment-ids');
                                if (hiddenInput) {
                                    hiddenInput.value = ids.join(',');
                                }
                            }
                        });
                        
                        // Initial update
                        var ids = [];
                        sortableList.querySelectorAll('li').forEach(function(li) {
                            ids.push(li.getAttribute('data-id'));
                        });
                        var hiddenInput = document.getElementById('ordered-attachment-ids');
                        if (hiddenInput) {
                            hiddenInput.value = ids.join(',');
                        }
                    }
                }
            }
            
            
            // Initialize when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeHandlers);
            } else {
                initializeHandlers();
            }
            
            // Re-initialize after Livewire updates
            if (typeof Livewire !== 'undefined') {
                document.addEventListener('livewire:load', initializeHandlers);
                
                Livewire.hook('message.processed', function() {
                    setTimeout(initializeHandlers, 50);
                });
            }
            
            // Also listen for Livewire component updates
            document.addEventListener('livewire:update', function() {
                setTimeout(initializeHandlers, 50);
            });
        })();
        
        // Form Submit handler and file input handler (jQuery)
        (function($) {
            if ($) {
                $(document).on('submit', '#merge-attachments-modal form', function() {
                    var ids = [];
                    $('#sortable-attachments li').each(function() {
                        ids.push($(this).data('id'));
                    });
                    $('#ordered-attachment-ids').val(ids.join(','));
                });

                $(document).on('change', '.custom-file-input', function() {
                    var fileName = $(this).val().split("\\").pop();
                    $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
                });
            }
        })(window.jQuery);

        function saveAttachmentType() {
            var name = $('#new_attachment_type_name').val();
            if (!name) {
                alert('Please enter a name for the attachment type.');
                return;
            }
            
            $.ajax({
                url: "{{ route('store-attachment-type') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    value: name
                },
                success: function(response) {
                    if (response.success) {
                        var newOption = new Option(response.value, response.id, true, true);
                        $('#attachment_type_select').append(newOption).trigger('change');
                        $('#add-attachment-type-modal').modal('hide');
                        $('#new_attachment_type_name').val('');
                        // Reload page to refresh attachment types
                        location.reload();
                    } else {
                        alert('Error adding attachment type: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('Error adding attachment type. Please try again.');
                }
            });
        }
    </script>
</div>
