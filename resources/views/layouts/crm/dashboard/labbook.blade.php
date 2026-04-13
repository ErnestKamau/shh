@extends('layouts.crm.dashboard.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title> {{ $customer->name }} - Customer | CRM</title>
<style type="text/css">
    /* Modal Styling Only */
    .modal-content.modern-modal-content {
        border-radius: 18px;
        box-shadow: 0 8px 32px rgba(60,60,90,0.18), 0 1.5px 4px rgba(0,0,0,0.08);
        border: none;
        background: #fff;
        padding: 0;
        overflow: hidden;
    }
    .modern-modal-header {
        background: #f7f8fa;
        border-bottom: 1px solid #e5e7eb;
        border-top-left-radius: 18px;
        border-top-right-radius: 18px;
        padding: 1.25rem 1.5rem;
    }
    .modern-modal-header .modal-title {
        font-weight: 600;
        font-size: 1.15rem;
        color: #2d3748;
    }
    .modern-modal-close {
        font-size: 1.5rem;
        color: #a0aec0;
        background: transparent;
        border: none;
        outline: none;
        transition: color 0.2s;
    }
    .modern-modal-close:hover {
        color: #e53e3e;
    }
    .modern-modal-body {
        padding: 1.5rem 1.5rem 1rem 1.5rem;
        background: #fff;
    }
    .modern-modal-footer {
        background: #f7f8fa;
        border-top: 1px solid #e5e7eb;
        border-bottom-left-radius: 18px;
        border-bottom-right-radius: 18px;
        padding: 1rem 1.5rem;
    }
    .download-template-section {
        background: #f1f5f9;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .download-template-section i {
        font-size: 1.5rem;
        color: #3182ce;
    }
    .custom-file-input:focus ~ .custom-file-label {
        border-color: #3182ce;
        box-shadow: 0 0 0 0.2rem rgba(49,130,206,.25);
    }
    .custom-file-label {
        border-radius: 0.5rem;
        background: #f7fafc;
        color: #4a5568;
    }
    .form-group label.control-label {
        font-weight: 500;
        color: #374151;
    }
    .btn.modern-btn-primary {
        background: #3182ce;
        color: #fff;
        border-radius: 0.5rem;
        font-weight: 600;
        border: none;
        transition: background 0.2s;
    }
    .btn.modern-btn-primary:hover {
        background: #2563eb;
        color: #fff;
    }
    .btn.modern-btn-light {
        background: #f1f5f9;
        color: #374151;
        border-radius: 0.5rem;
        font-weight: 500;
        border: none;
        transition: background 0.2s;
    }
    .btn.modern-btn-light:hover {
        background: #e2e8f0;
        color: #2d3748;
    }
    .btn.modern-btn-danger {
        background: #e53e3e;
        color: #fff;
        border-radius: 0.5rem;
        font-weight: 600;
        border: none;
        transition: background 0.2s;
    }
    .btn.modern-btn-danger:hover {
        background: #c53030;
        color: #fff;
    }
    
    /* Responsive modal width */
    @media (min-width: 576px) {
        .modal-dialog.modal-dialog-centered {
            max-width: 480px;
        }
    }
    
    /* Validation Timeline Styles */
    .validation-timeline {
        position: relative;
        padding-left: 20px;
    }
    
    .validation-step {
        position: relative;
        display: flex;
        align-items: flex-start;
        margin-bottom: 1.5rem;
        padding-left: 2.5rem;
    }
    
    .validation-step:last-child {
        margin-bottom: 0;
    }
    
    .step-connector {
        position: absolute;
        left: 0.875rem;
        top: 2rem;
        width: 2px;
        height: calc(100% + 1rem);
        background: #e5e7eb;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .step-connector.show {
        opacity: 1;
    }
    
    .step-icon {
        position: absolute;
        left: 0;
        top: 0;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        font-weight: 600;
        transition: all 0.3s ease;
        z-index: 2;
    }
    
    .step-icon.pending {
        background: #f1f5f9;
        color: #64748b;
        border: 2px solid #cbd5e1;
    }
    
    .step-icon.passed {
        background: #10b981;
        color: white;
        border: 2px solid #10b981;
        animation: successPulse 0.6s ease-out;
    }
    
    .step-icon.failed {
        background: #ef4444;
        color: white;
        border: 2px solid #ef4444;
        animation: errorShake 0.6s ease-out;
    }
    
    .step-content {
        flex: 1;
        min-width: 0;
    }
    
    .step-title {
        font-weight: 600;
        font-size: 0.95rem;
        color: #374151;
        margin-bottom: 0.25rem;
    }
    
    .step-description {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }
    
    .step-message {
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.4;
    }
    
    .validation-container {
        background: #fafbfc;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e5e7eb;
    }
    
    @keyframes successPulse {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        50% { transform: scale(1.1); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.3); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    
    @keyframes errorShake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
        20%, 40%, 60%, 80% { transform: translateX(2px); }
    }
    
    .mdi-loading.mdi-spin {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => '/dasboard/crm/client-home',
            'name' => $customer->name,
            'icon' => null
        ),
        array(
            'link' => '/dasboard/crm/client-home',
            'name' => 'CRM',
            'icon' => null
        ),
        array(
            'link' => '#',
            'name' => 'Laboratory Booking',
            'icon' => null
        ),
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <div class="mb-3">
        <h4>
            <i class="mdi mdi-notebook-edit-outline mr-2"></i> Laboratory Booking
            <div class="btn-group" style="float:right !important">
                <button type="button" class="btn btn-sm dropdown-toggle shadow-sm" id="dropdownMenuButton"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    style="font-weight:600; border-radius:0.5rem;background-color:white !important">
                    <i class="mdi mdi-dots-vertical"></i> Actions
                </button>
                <div class="dropdown-menu dropdown-menu-right bg-light animate__animated animate__fadeIn" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#add-lab-booking">
                        <i class="mdi mdi-plus-circle-outline text-primary mr-2"></i> Add Booking
                    </a>
                    {{-- <a class="dropdown-item" href="#" data-toggle="modal" data-target="#send-enroute">
                        <i class="mdi mdi-send text-success mr-2"></i> Send Enroute
                    </a> --}}
                </div>
            </div>
        </h4>
    </div>
    <div class="card mb-4" style="clear:both">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                    <thead>
                        <tr>
                            <th style="width:90px;"></th>
                            <th>Booking No</th>
                            <th>Booking Date</th>
                            <th>Sample Type</th>
                            <th>No of Samples</th>
                            <th>Status</th>
                            <th>Stage</th>
                            <th>Created by</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                            <tr>
                                <td>
                                    @if(in_array($booking->status,[0,2]))
                                    <button class="modern-action-btn btn btn-sm btn-default" data-record="{{ json_encode($booking) }}" data-toggle="modal" data-target="#edit-lab-booking" data-toggle="tooltip" title="Edit">
                                        <i class="mdi mdi-pencil text-primary"></i>
                                    </button>
                                    <button class="modern-action-btn btn btn-sm btn-default" data-toggle="modal" data-target="#delete-lab-booking" data-record="{{ json_encode($booking) }}" data-toggle="tooltip" title="Delete">
                                        <i class="mdi mdi-delete-empty text-danger"></i>
                                    </button>
                                    @endif
                                    <a href="{{ route('crm-lab-show',['id'=>$booking->id]) }}" class="modern-action-btn btn btn-sm btn-default" data-toggle="tooltip" title="View"><i class="mdi mdi-eye text-success"></i></a>
                                </td>
                                <td> <a href="{{ route('crm-lab-show',['id'=>$booking->id]) }}" class="btn btn-sm">{{ $booking->book_no }}</a></td>
                                <td>{{ $booking->book_date }}</td>
                                <td>{{ $booking->sampletype->name }}</td>
                                <td>{{ $booking->sample->count() }}</td>
                                <td>
                                    @php
                                        $status = $bookingstatus[strval($booking->status)];
                                        $statusClass = '';
                                        switch ($booking->status) {
                                            case 0:
                                                $statusClass = 'badge badge-secondary'; // Created
                                                break;
                                            case 1:
                                                $statusClass = 'badge badge-success'; // Received And Processed
                                                break;
                                            case 2:
                                                $statusClass = 'badge badge-info'; // Submitted Not Processed
                                                break;
                                            case 3:
                                                $statusClass = 'badge badge-danger'; // Cancelled
                                                break;
                                            default:
                                                $statusClass = 'badge badge-light';
                                        }
                                    @endphp
                                    <span class="{{ $statusClass }} p-2"> <i class="mdi mdi-swap-vertical"></i> {{ $status }}</span>
                                </td>
                                <td>{{ strtoupper($booking->sampleheader ? $booking->sampleheader->status : 'EN-ROUTE')  }}</td>
                                <td>{{ $booking->creator->name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="mdi mdi-flask-outline" style="font-size:2rem;"></i>
                                    <div class="mt-2">No laboratory bookings found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection

@section('script2')

<!-- Add Lab Booking Modal -->
<div class="modal fade" id="add-lab-booking" tabindex="-1" role="dialog" aria-labelledby="addLabBookingLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modern-modal-content animate__animated animate__fadeInDown">
            <div class="modern-modal-header d-flex align-items-center justify-content-between">
                <h5 class="modal-title" id="addLabBookingLabel">
                    <i class="mdi mdi-flask-outline mr-2"></i> Add Laboratory Booking
                </h5>
                <button type="button" class="close modern-modal-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('crm-lab-book-store') }}" id="lab-booking-form" method="post" enctype="multipart/form-data" autocomplete="off">
                @csrf
                <div class="modern-modal-body">
                    <div class="form-fields-section">
                        <div class="download-template-section mb-3">
                            <i class="mdi mdi-file-download-outline"></i>
                            <div style="width:100% !important ">
                                
                                <a href="{{ url('/templates/labbookingimport.xlsx') }}" class="btn btn-primary btn-sm ml-2" style="width:100% !important ;color:white !important" download>
                                    <i class="mdi mdi-download" style="color:white !important" ></i> <b>Download Excel Template</b>
                                </a>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="sample_type_id" class="control-label">Sample Type</label>
                            <select name="sample_type_id" id="sample_type_id" class="form-control" required>
                                <option value="">Select Sample Type</option>
                                @foreach ($sample_types as $sampletype)
                                    <option value="{{ $sampletype->id }}">{{ $sampletype->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="book_date" class="control-label">Lab Booking Date</label>
                            <input type="date" name="book_date" id="book_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="booking_upload" class="control-label">Upload Booking File</label>
                            <div id="booking-upload-dropzone" class="dropzone-area mb-2" style="border: 2px dashed #b6c2d2; border-radius: 8px; padding: 32px; text-align: center; background: #f8fafc; cursor: pointer;">
                                <i class="mdi mdi-cloud-upload-outline" style="font-size: 2rem; color: #6c757d;"></i>
                                <div id="booking-upload-dropzone-text" style="margin-top: 8px; color: #6c757d;">
                                    Drag &amp; drop your file here, or <span style="color: #007bff; text-decoration: underline; cursor: pointer;">browse</span>
                                </div>
                                <input type="file" class="d-none" id="booking_upload" name="booking_upload" accept=".xlsx,.xls,.csv" required>
                                <div id="booking-upload-filename" class="mt-2 text-success" style="display:none;"></div>
                            </div>
                            <small class="form-text text-muted mt-1">Accepted formats: .xlsx, .xls, .csv</small>
                        </div>
                    </div>
                    <div class="validation-steps-section">
                        
                    </div>
                </div>
                <div class="modern-modal-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-sm modern-btn-primary">
                        <i class="mdi mdi-content-save-outline mr-1"></i> Save Booking
                    </button>
                    <button type="button" class="btn modern-btn-light mr-2" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- Edit Lab Booking Modal -->
<div class="modal fade" id="edit-lab-booking" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modern-modal-content animate__animated animate__fadeInDown">
            <form action="{{ route('crm-lab-book-store') }}" method="post" enctype="multipart/form-data">
                @csrf 
                <div class="modern-modal-body">

                </div>
                <div class="modern-modal-footer d-flex justify-content-end">
                    <button class="btn modern-btn-primary" type="submit"><i class="mdi mdi-content-save"></i> Save</button>
                    <button type="button" class="btn modern-btn-light" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Lab Booking Modal -->
<div class="modal fade" id="delete-lab-booking" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modern-modal-content animate__animated animate__fadeInDown">
            <form action="{{ route('delete-lab-booking') }}" method="post">
                @csrf 
                <div class="modern-modal-body">
                    
                </div>
                <div class="modern-modal-footer d-flex justify-content-end">
                    <button class="btn modern-btn-danger btn-sm" type="submit"><i class="mdi mdi-thumbs-up"></i> Yes, Delete</button>
                    <button type="button" class="btn modern-btn-light" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
    // Show selected file name in custom file input


    document.addEventListener('DOMContentLoaded', function () {
        var fileInput = document.getElementById('booking_upload');
        if(fileInput){
            fileInput.addEventListener('change', function(e){
                var fileName = e.target.files[0] ? e.target.files[0].name : "Choose file...";
                var label = e.target.nextElementSibling;
                if(label) label.innerText = fileName;
            });
        }
        // Animate tooltips
        $('[data-toggle="tooltip"]').tooltip({animation: true});
    });

    $(()=>{
        var editBody = (data)=>{
            var body = $(`
                <div class="mb-3 bg-light p-2" style="border-radius:10px"> 
                    <h5 class="d-flex align-items-center">
                        <i class="mdi mdi-pencil-outline mr-2"></i> Editing Lab Booking <span class="ml-2 font-weight-bold">${data.book_no ? data.book_no : ''}</span>
                    </h5>
                    
                </div>
                <div class="download-template-section mb-3">
                    <i class="mdi mdi-file-download-outline"></i>
                    <div>
                        <strong>Download Template:</strong>
                        <a href="{{ url('/templates/lab-booking-template.xlsx') }}" class="btn btn-outline-primary btn-sm ml-2" download>
                            <i class="mdi mdi-download"></i> Download Excel Template
                        </a>
                    </div>
                </div>
                <div class="form-group">
                    <label for="sample_type_id" class="control-label">Sample Type</label>
                    <select name="sample_type_id" id="sample_type_id" class="form-control" required>
                        <option value="">Select Sample Type</option>
                        @foreach ($sample_types as $sampletype)
                            <option value="{{ $sampletype->id }}">{{ $sampletype->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="book_date" class="control-label">Lab Booking Date (${data.book_date})</label>
                    <input type="date" name="book_date" id="book_date" value="${(function(d){if(!d)return '';var dt=new Date(d);if(isNaN(dt))return d;var m=(dt.getMonth()+1).toString().padStart(2,'0');var day=dt.getDate().toString().padStart(2,'0');var y=dt.getFullYear();return y+'-'+m+'-'+day;})(data.book_date)}" class="form-control" required>
                    <small class="form-text text-muted">Date format: m/d/y</small>
                </div>
                <div class="form-group">
                    <label for="booking_upload" class="control-label">Upload Booking File</label>
                    <div id="booking-upload-dropzone-edit" class="dropzone-area mb-2" style="border: 2px dashed #b6c2d2; border-radius: 8px; padding: 32px; text-align: center; background: #f8fafc; cursor: pointer;">
                        <i class="mdi mdi-cloud-upload-outline" style="font-size: 2rem; color: #6c757d;"></i>
                        <div id="booking-upload-dropzone-text-edit" style="margin-top: 8px; color: #6c757d;">
                            Drag &amp; drop your file here, or <span style="color: #007bff; text-decoration: underline; cursor: pointer;">browse</span>
                        </div>
                        <input type="file" class="d-none" id="booking_upload_edit" name="booking_upload" accept=".xlsx,.xls,.csv">
                        <div id="booking-upload-filename-edit" class="mt-2 text-success" style="display:none;"></div>
                    </div>
                    <small class="form-text text-muted mt-1">Accepted formats: .xlsx, .xls, .csv (Optional - leave empty to keep existing file)</small>
                </div>
                <input type="hidden" name="staging_header_id" value="${data.id }">
            `).clone();
            $(body).find('#sample_type_id').val(data.sample_type_id);
            
            return body;
        }
        var deleteBody = (data)=>{
            var body = $(`
                <div class="d-flex align-items-center p-3">
                    <i class="mdi mdi-delete-empty text-danger" style="font-size:2rem;"></i>
                    <span class="pl-3" style="font-size:1.1rem;">Confirm you want to delete <strong>${data.book_no}</strong> lab booking?</span>
                </div>
                <input type="hidden" name="staging_header_id" value="${data.id}">
            `).clone();
            return body;
        }
        $('#edit-lab-booking').on('show.bs.modal',(e)=>{
            var data = $(e.relatedTarget).data('record');
            var body = editBody(data);
            $('#edit-lab-booking').find('.modern-modal-body').empty();
            $('#edit-lab-booking').find('.modern-modal-body').append(body);

            // Initialize select2 for sample type
            setTimeout(function() {
                $('#edit-lab-booking').find('#sample_type_id').select2({
                    dropdownParent: $('#edit-lab-booking')
                });
            }, 50);

            // Use setTimeout to ensure DOM elements are fully rendered
            setTimeout(function() {
                const dropzone = document.getElementById('booking-upload-dropzone-edit');
                const fileInput = document.getElementById('booking_upload_edit');
                const filenameDisplay = document.getElementById('booking-upload-filename-edit');
                const dropzoneText = document.getElementById('booking-upload-dropzone-text-edit');

                if (dropzone && fileInput && filenameDisplay && dropzoneText) {
                    // Remove any existing event listeners by cloning the elements
                    const newDropzone = dropzone.cloneNode(true);
                    const newFileInput = newDropzone.querySelector('#booking_upload_edit');
                    const newFilenameDisplay = newDropzone.querySelector('#booking-upload-filename-edit');
                    const newDropzoneText = newDropzone.querySelector('#booking-upload-dropzone-text-edit');
                    
                    dropzone.parentNode.replaceChild(newDropzone, dropzone);

                    // Add click handler
                    newDropzone.addEventListener('click', function(e) {
                        if (e.target.tagName !== 'INPUT') {
                            newFileInput.click();
                        }
                    });

                    // Show file name when selected
                    newFileInput.addEventListener('change', function() {
                        if (newFileInput.files.length > 0) {
                            newFilenameDisplay.textContent = newFileInput.files[0].name;
                            newFilenameDisplay.style.display = 'block';
                            newDropzoneText.style.display = 'none';
                        } else {
                            newFilenameDisplay.style.display = 'none';
                            newDropzoneText.style.display = 'block';
                        }
                    });

                    // Drag and drop handlers
                    newDropzone.addEventListener('dragover', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        newDropzone.style.background = '#e3eaf3';
                        newDropzone.style.borderColor = '#007bff';
                    });
                    
                    newDropzone.addEventListener('dragleave', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        newDropzone.style.background = '#f8fafc';
                        newDropzone.style.borderColor = '#b6c2d2';
                    });
                    
                    newDropzone.addEventListener('drop', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        newDropzone.style.background = '#f8fafc';
                        newDropzone.style.borderColor = '#b6c2d2';
                        if (e.dataTransfer.files.length > 0) {
                            newFileInput.files = e.dataTransfer.files;
                            const event = new Event('change');
                            newFileInput.dispatchEvent(event);
                        }
                    });
                }
            }, 100);
        });
        $('#delete-lab-booking').on('show.bs.modal',(e)=>{
            var data = $(e.relatedTarget).data('record');
            var body = deleteBody(data);
            $('#delete-lab-booking').find('.modern-modal-body').empty();
            $('#delete-lab-booking').find('.modern-modal-body').append(body);
        });
        // Initialize add modal dropzone functionality once
        function initializeAddModalDropzone() {
            const dropzone = document.getElementById('booking-upload-dropzone');
            const fileInput = document.getElementById('booking_upload');
            const filenameDisplay = document.getElementById('booking-upload-filename');
            const dropzoneText = document.getElementById('booking-upload-dropzone-text');

            if (dropzone && fileInput && filenameDisplay && dropzoneText) {
                // Click to open file dialog
                dropzone.addEventListener('click', function(e) {
                    if (e.target.tagName !== 'INPUT') {
                        fileInput.click();
                    }
                });

                // Show file name when selected
                fileInput.addEventListener('change', function() {
                    if (fileInput.files.length > 0) {
                        filenameDisplay.textContent = fileInput.files[0].name;
                        filenameDisplay.style.display = 'block';
                        dropzoneText.style.display = 'none';
                    } else {
                        filenameDisplay.style.display = 'none';
                        dropzoneText.style.display = 'block';
                    }
                });

                // Drag and drop handlers
                dropzone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.background = '#e3eaf3';
                    dropzone.style.borderColor = '#007bff';
                });
                dropzone.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.background = '#f8fafc';
                    dropzone.style.borderColor = '#b6c2d2';
                });
                dropzone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.background = '#f8fafc';
                    dropzone.style.borderColor = '#b6c2d2';
                    if (e.dataTransfer.files.length > 0) {
                        fileInput.files = e.dataTransfer.files;
                        const event = new Event('change');
                        fileInput.dispatchEvent(event);
                    }
                });
            }
        }

        // Initialize dropzone on page load
        $(document).ready(function() {
            initializeAddModalDropzone();
        });

        var validateBookingFile = (formdata,callback)=>{
            $.ajax({
                url:'{{ route('validate-lab-booking-file') }}',
                type:'POST',
                data:formdata,
                contentType: false,
                processData: false,
                success:(response)=>callback(response),
                error:(res)=>{
                    console.log(res)
                }
            })
        }

        var saveBooking = (formdata,callback)=>{
            $.ajax({
                url:'{{ route('crm-lab-book-store') }}',
                type:'POST',
                data:formdata,
                contentType: false,
                processData: false,
                success:(response)=>callback(response),
                error:(res)=>{
                    console.log(res);
                    callback({status: 'error', message: 'Failed to save booking'});
                }
            })
        }

        $('#add-lab-booking').on('show.bs.modal', function(e) {
            resetForm();
            
            // Initialize select2 for sample type in add modal
            setTimeout(function() {
                $('#add-lab-booking').find('#sample_type_id').select2({
                    dropdownParent: $('#add-lab-booking')
                });
            }, 50);

            $('#lab-booking-form').on('submit', function(e){
                e.preventDefault();
                console.log('Form submitted');
                
                // Hide Save button
                $('.modern-modal-footer button[type="submit"]').hide();
                
                // Hide form fields section
                $('.form-fields-section').fadeOut(300, function() {
                    // Show validation steps section
                    $('.validation-steps-section').html(`
                        <div class="validation-container">
                            <div class="d-flex align-items-center mb-3">
                                <i class="mdi mdi-shield-check-outline text-primary" style="font-size: 1.5rem;"></i>
                                <h6 class="ml-2 mb-0 font-weight-bold">File Validation Progress</h6>
                            </div>
                            <div id="validation-timeline" class="validation-timeline">
                                <!-- Validation steps will be inserted here -->
                            </div>
                        </div>
                    `).fadeIn(300);
                    
                    // Start validation process
                    var formdata = new FormData(document.getElementById('lab-booking-form'));
                    startValidationProcess(formdata);
                });
            });
        })
        
        $('#add-lab-booking').on('hidden.bs.modal', function(e) {
            resetForm();
        })
        
        // Validation timeline functions
        function startValidationProcess(formdata) {
            validateBookingFile(formdata, function(response) {
                if (response.validation_steps) {
                    displayValidationSteps(response.validation_steps, function() {
                        if (response.status === 'success') {
                            // All validations passed, proceed to save
                            addSavingStep();
                            saveBooking(formdata, function(saveResponse) {
                                updateSavingStep(saveResponse);
                            });
                        } else {
                            // Validation failed, show error
                            showValidationError(response.message);
                        }
                    });
                } else {
                    // Fallback for older response format
                    if (response.status === 'success') {
                        saveBooking(formdata, function(saveResponse) {
                            if (saveResponse.status === 'success') {
                                $('#add-lab-booking').modal('hide');
                                location.reload();
                            }
                        });
                    } else {
                        console.log(response.message);
                        showValidationError(response.message);
                    }
                }
            });
        }
        
        function displayValidationSteps(steps, callback) {
            const timeline = $('#validation-timeline');
            let index = 0;
            
            function showNextStep() {
                if (index >= steps.length) {
                    callback();
                    return;
                }
                
                const step = steps[index];
                const stepHtml = `
                    <div class="validation-step" data-step="${index}">
                        <div class="step-connector ${index > 0 ? 'show' : ''}"></div>
                        <div class="step-icon ${step.status === 'passed' ? 'passed' : step.status === 'failed' ? 'failed' : 'pending'}">
                            ${step.status === 'passed' ? '<i class="mdi mdi-check"></i>' : 
                              step.status === 'failed' ? '<i class="mdi mdi-close"></i>' : 
                              '<i class="mdi mdi-clock-outline"></i>'}
                        </div>
                        <div class="step-content">
                            <div class="step-title">${step.step}</div>
                            <div class="step-description">${step.description}</div>
                            <div class="step-message ${step.status === 'passed' ? 'text-success' : step.status === 'failed' ? 'text-danger' : 'text-muted'}">${step.message}</div>
                        </div>
                    </div>
                `;
                
                timeline.append(stepHtml);
                
                // Animate the step appearance
                const stepElement = timeline.find(`[data-step="${index}"]`);
                stepElement.hide().fadeIn(400, function() {
                    index++;
                    setTimeout(() => showNextStep(), step.status === 'failed' ? 1000 : 800);
                });
            }
            
            showNextStep();
        }
        
        function addSavingStep() {
            const timeline = $('#validation-timeline');
            const stepHtml = `
                <div class="validation-step saving-step">
                    <div class="step-connector show"></div>
                    <div class="step-icon pending">
                        <i class="mdi mdi-loading mdi-spin"></i>
                    </div>
                    <div class="step-content">
                        <div class="step-title">Saving Lab Booking</div>
                        <div class="step-description">Processing and saving your lab booking data</div>
                        <div class="step-message text-info">Please wait while we save your booking...</div>
                    </div>
                </div>
            `;
            timeline.append(stepHtml);
            $('.saving-step').hide().fadeIn(400);
        }
        
        function updateSavingStep(response) {
            const savingStep = $('.saving-step');
            const icon = savingStep.find('.step-icon');
            const message = savingStep.find('.step-message');
            
            if (response.status === 'success') {
                icon.removeClass('pending').addClass('passed').html('<i class="mdi mdi-check"></i>');
                message.removeClass('text-info').addClass('text-success').text('Lab booking saved successfully!');
                
                setTimeout(() => {
                    $('#add-lab-booking').modal('hide');
                    location.reload();
                }, 1500);
            } else {
                icon.removeClass('pending').addClass('failed').html('<i class="mdi mdi-close"></i>');
                message.removeClass('text-info').addClass('text-danger').text(response.message || 'Failed to save lab booking');
                
                // Show retry button
                setTimeout(() => {
                    $('.modern-modal-footer').append(`
                        <button type="button" class="btn modern-btn-light mr-2" onclick="resetForm()">
                            <i class="mdi mdi-refresh"></i> Try Again
                        </button>
                    `);
                }, 1000);
            }
        }
        
        function showValidationError(message) {
            $('.validation-steps-section').append(`
                <div class="alert alert-danger mt-3" role="alert">
                    <i class="mdi mdi-alert-circle-outline mr-2"></i>
                    <strong>Validation Failed:</strong> ${message}
                </div>
            `);
            
            // Show retry button
            setTimeout(() => {
                $('.modern-modal-footer').append(`
                    <button type="button" class="btn modern-btn-light mr-2" onclick="resetForm()">
                        <i class="mdi mdi-refresh"></i> Try Again
                    </button>
                `);
            }, 1000);
        }
        
        function resetForm() {
            // Reset form visibility
            $('.form-fields-section').show();
            $('.validation-steps-section').empty();
            $('.modern-modal-footer button[type="submit"]').show();
            $('.modern-modal-footer .btn:contains("Try Again")').remove();
            
            // Reset the form
            $('#lab-booking-form')[0].reset();
            
            // Reset file input and display
            const fileInput = document.getElementById('booking_upload');
            const filenameDisplay = document.getElementById('booking-upload-filename');
            const dropzoneText = document.getElementById('booking-upload-dropzone-text');
            
            if (fileInput) fileInput.value = '';
            if (filenameDisplay) filenameDisplay.style.display = 'none';
            if (dropzoneText) dropzoneText.style.display = 'block';
            
            // Reset select2
            $('#sample_type_id').val('').trigger('change');
            
            // Clear any validation alerts
            $('.alert').remove();
        }
    })
</script>

<!-- Animate.css CDN for fadeIn/fadeInDown (if not already included) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

@endsection