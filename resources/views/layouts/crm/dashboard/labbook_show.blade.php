@extends('layouts.crm.dashboard.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title> {{ $customer->name }} - Customer | CRM</title>
<style type="text/css">
    main {
        background: #f7f9fb;
        min-height: 100vh;
        padding-bottom: 40px;
    }
    /* --- Modal Styling (keep system colors) --- */
    .modern-modal-content {
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(60,60,60,0.18);
        border: none;
        background: #fff;
        padding: 0;
    }
    .modern-modal-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        border-radius: 12px 12px 0 0;
        padding: 1rem 1.5rem;
    }
    .modern-modal-header .modal-title {
        font-weight: 600;
        color: #333;
    }
    .modern-modal-close {
        font-size: 1.5rem;
        color: #888;
        opacity: 0.7;
        transition: color 0.2s;
    }
    .modern-modal-close:hover {
        color: #e74c3c;
        opacity: 1;
    }
    .modern-modal-body {
        padding: 1.5rem;
        background: #fff;
    }
    .modern-modal-footer {
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
        border-radius: 0 0 12px 12px;
        padding: 0.75rem 1.5rem;
    }
    .modern-btn-primary {
        background: #007bff;
        color: #fff;
        border: none;
        border-radius: 4px;
        transition: background 0.2s;
    }
    .modern-btn-primary:hover {
        background: #0056b3;
        color: #fff;
    }
    .modern-btn-light {
        background: #f4f4f4;
        color: #333;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        transition: background 0.2s;
    }
    .modern-btn-light:hover {
        background: #e0e0e0;
        color: #222;
    }
    .pull-right{
        float: right !important;
    }
    @media (min-width: 576px) {
        .modal-dialog {
            max-width: 600px;
        }
    }
</style>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => '/dasboard/crm/client-home',
            'name' => 'CRM',
            'icon' => null
        ),
        array(
            'link' => '#',
            'name' => $customer->name,
            'icon' => null
        ),
        array(
            'link' => route('crm-lab-book'),
            'name' => 'Laboratory Booking',
            'icon' => null
        ),
        array(
            'link' => route('crm-lab-show',['id'=>$booking->id]),
            'name' => $booking->book_no,
            'icon' => null
        )
    );
    ?>
    <div class="crm-breadcrumb">
        <x-bread-crumb :items="$items"></x-bread-crumb>
    </div>
    <div class="crm-section-card">
        <div class="crm-section-header d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <i class="mdi mdi-notebook-edit-outline mr-2"></i>
                Lab Booking <span class="text-primary font-weight-bold">{{ $booking->book_no }}</span>
                <span class="mx-2 text-muted">|</span>
                <span class="crm-badge-status" style="box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;">
                    <i class="mdi mdi-chevron-right"></i> {{ $status_arr[$booking->status] }}
                </span>
            </div>
            <div>
                @if(in_array($booking->status,[0,2]))
                <span class="btn btn-sm crm-btn-white crm-btn-action" style="box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;" data-toggle="modal" data-target="#edit-lab-booking">
                    <i class="mdi mdi-pencil"></i> Edit Booking
                </span>
                @endif
                @if($booking->status == 0)
                <span class="btn btn-sm crm-btn-white crm-btn-action" style="box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;" data-toggle="modal" data-target="#move-to-reception">
                    <i class="mdi mdi-swap-vertical"></i> Move to Enroute
                </span>
                @endif
                @if($booking->status == 2)
                <span class="btn btn-sm crm-btn-white crm-btn-action" style="box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;" data-toggle="modal" data-target="#move-to-portal">
                    <i class="mdi mdi-swap-vertical"></i> Return to Portal
                </span>
                @endif 


            </div>
        </div>
        <div class="crm-section-body">
            <div class="row no-gutters border-bottom pb-3 mb-3">
                <div class="col-md-12 mb-2"><b><u>Lab Booking Information:</u></b></div>
                <div class="col-md-4 mb-2">
                    <div class="crm-label">Sample Type:</div>
                    <div class="crm-value">
                        {{ $booking->sampletype ? $booking->sampletype->name : '-' }}
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="crm-label">Booking Date:</div>
                    <div class="crm-value">
                        {{ $booking->book_date ? \Carbon\Carbon::parse($booking->book_date)->format('d M Y') : '-' }}
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="crm-label">Excel Template:</div>
                    <div>
                        @if($booking->excel_url)
                            <a href="{{ asset($booking->excel_url) }}" class="btn btn-sm btn-primary" target="_blank">
                                <i class="mdi mdi-file-excel"></i> Download Uploaded Template
                            </a>
                        @else
                            <span class="text-muted my-small-text">No template uploaded.</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="crm-table-header">Sample Information</div>
            <div class="table-responsive">
                <table class="table table-condensed crm-table table-striped table-hover table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Lab No</th>
                            <th>Status</th>
                            <th>Sample Type</th>
                            <th>Site Sampled</th>
                            <th>Sampling Date</th>
                            <th>Sampling Time</th>
                            <th>Test Required</th>
                            <th>Sample Description</th>
                            <th>Comments</th>
                            <th>Temperature</th>
                            <th>PH</th>
                            <th>PPM</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($booking->sample as $sample)
                            <tr>
                                <td class="text-center">
                                    @if(in_array($booking->status,['0','2']))
                                        <span class="btn btn-sm crm-action-btn text-primary" data-record="{{ json_encode($sample) }}" data-toggle="modal" data-target="#edit-booking-sample"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span>
                                        <span class="btn btn-sm crm-action-btn text-danger" data-record="{{ json_encode($sample) }}" data-toggle="modal" data-target="#delete-booking-sample"><i class="mdi mdi-trash-empty" data-toggle="tooltip" title="Delete"></i></span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{!! $sample->detail && $sample->detail != null  ? $sample->detail->sample_code :  '<span class="text-danger">Not Assigned</span>' !!}</td>
                                <td>{!! $booking->status == 1 ? '<span class="badge p-2 badge-success badge-pill">Received And Processed</span>' : ($booking->status == 2 ? '<span class="badge p-2 badge-warning badge-pill">Submitted Not Processed</span>' : '<span class="badge p-2 badge-info badge-pill">Created</span>') !!}</td>
                                <td>{{ $sample->sample_type_name ?? '-' }}</td>
                                <td>{{ $sample->sample_point ?? '- ' }}</td>
                                <td>{{ $sample->sampling_date ?? '-' }}</td>
                                <td>{{ date('H:i',strtotime($sample->sampling_time)) ?? '' }}</td>
                                <td>{{ $sample->test_required ?? '-' }}</td>
                                <td>{{ $sample->sample_description ?? '-' }}</td>
                                <td>{{ $sample->comments ?? '-' }}</td>
                                <td>{{ $sample->temperature ?? '' }}</td>
                                <td>{{ $sample->ph ?? '-' }}</td>
                                <td>{{ $sample->ppm ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection

@section('script2')
<div class="modal fade" id="cancel-booking" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('cancel-lab-booking') }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-default p-2 d-flex border-red text-danger">
                        <i class="mdi mdi-delete-empty"></i>
                        <span class="p-2 text-bold">Confirm you want to delete lab booking <span class="text-danger">{{ $booking->book_no }}</span>?</span>
                    </div>
                    <input type="hidden" name="book_id" value="{{ $booking->id }}">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">
                        <i class="mdi mdi-delete-empty"></i> Yes, Delete
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="move-to-portal" tabindex="-1" role="dialog" aria-labelledby="moveToPortalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modern-modal-content animate__animated animate__fadeInDown">
            <div class="modern-modal-header d-flex align-items-center justify-content-between">
                <h5 class="modal-title" id="moveToPortalLabel">
                    <i class="mdi mdi-undo-variant mr-2"></i> Return Booking to Portal
                </h5>
                <button type="button" class="close modern-modal-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('move-to-portal') }}">
                @csrf
                <input type="hidden" name="book_id" value="{{ $booking->id }}">
                <div class="modern-modal-body">
                    <div class="alert alert-warning">
                        <strong>Are you sure you want to return this lab booking back to the <span class="text-primary">Portal</span>?</strong>
                        <br>
                        <small>
                            <i class="mdi mdi-information-outline"></i>
                            <b>Note:</b> This action will remove the booking from visibility from the laboratory side.
                        </small>
                    </div>
                </div>
                <div class="modern-modal-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-sm alert-warning">
                        <i class="mdi mdi-undo-variant mr-1"></i> Yes, Return to Portal
                    </button>
                    <button type="button" class="btn modern-btn-light mr-2" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="move-to-reception" tabindex="-1" role="dialog" aria-labelledby="moveToReceptionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modern-modal-content animate__animated animate__fadeInDown">
            <div class="modern-modal-header d-flex align-items-center justify-content-between">
                <h5 class="modal-title" id="moveToReceptionLabel">
                    <i class="mdi mdi-send mr-2"></i> Move Booking to Enroute
                </h5>
                <button type="button" class="close modern-modal-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('move-to-enroute') }}">
                @csrf
                <input type="hidden" name="book_id" value="{{ $booking->id }}">
                <div class="modern-modal-body">
                    <div class="alert alert-warning">
                        <strong>Are you sure you want to move this lab booking to <span class="text-primary">Enroute</span>?</strong>
                        <br>
                        <small>
                            <i class="mdi mdi-information-outline"></i>
                            <b>Note:</b> This action will make the lab booking visible to the laboratory team for further processing.
                        </small>
                    </div>
                </div>
                <div class="modern-modal-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-sm alert-warning">
                        <i class="mdi mdi-send mr-1"></i> Yes, Move to Enroute
                    </button>
                    <button type="button" class="btn modern-btn-light mr-2" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Sample Modal -->
<div class="modal fade" id="edit-booking-sample" tabindex="-1" role="dialog" aria-labelledby="editSampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modern-modal-content animate__animated animate__fadeInDown">
            <div class="modern-modal-header d-flex align-items-center justify-content-between">
                <h5 class="modal-title" id="editSampleModalLabel">
                    <i class="mdi mdi-pencil mr-2"></i> Edit Sample Details
                </h5>
               
            </div>
            <form id="edit-sample-form" method="post" action="{{ route('crm-sample-update') }}">
                @csrf
                <input type="hidden" name="sample_id" id="edit_sample_id">
                <!-- Modal body will be appended here dynamically -->
            </form>
        </div>
    </div>
</div>

<!-- Delete Sample Modal -->
<div class="modal fade" id="delete-booking-sample" tabindex="-1" role="dialog" aria-labelledby="deleteSampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modern-modal-content animate__animated animate__fadeInDown">
            <div class="modern-modal-header d-flex align-items-center justify-content-between">
                <h5 class="modal-title" id="deleteSampleModalLabel">
                    <i class="mdi mdi-trash-can-outline mr-2 text-danger"></i> Delete Sample
                </h5>
                <button type="button" class="close modern-modal-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="delete-sample-form" method="post" action="">
                @csrf
                <input type="hidden" name="sample_id" id="delete_sample_id">
                <!-- Modal body will be appended here dynamically -->
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="edit-lab-booking" tabindex="-1" role="dialog" aria-labelledby="addLabBookingLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modern-modal-content animate__animated animate__fadeInDown">
            <div class="modern-modal-header d-flex align-items-center justify-content-between">
                <h5 class="modal-title" id="addLabBookingLabel">
                    <i class="mdi mdi-flask-outline mr-2"></i> Edit Laboratory Booking - {{ $booking->book_no }}
                </h5>
               
            </div>
            <form action="{{ route('crm-lab-book-store') }}" method="post" enctype="multipart/form-data" autocomplete="off">
                @csrf
                <div class="modern-modal-body">
                    <div class="download-template-section d-flex mb-3">
                        <i class="mdi mdi-file-download-outline" style="font-size:25px"></i>
                        <div style="width:80% !important ">
                            <a href="{{ url('/templates/lab-booking-template.xlsx') }}" class="btn btn-primary btn-sm ml-2" style="width:100% !important ;color:white !important" download>
                                <i class="mdi mdi-download" style="color:white !important" ></i> <b>Download Excel Template</b>
                            </a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="sample_type_id" class="control-label">Sample Type <small class="text-danger">*</small></label>
                        <select name="sample_type_id" id="sample_type_id" class="form-control" required>
                            <option value="">Select Sample Type</option>
                            @foreach ($sample_types as $sampletype)
                                <option value="{{ $sampletype->id }}" {{ $booking->sample_type_id == $sampletype->id ? 'selected' : '' }} >{{ $sampletype->name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="staging_header_id" value="{{ $booking->id }}">
                    </div>
                    <div class="form-group">
                        <label for="book_date" class="control-label">Lab Booking Date ({{ $booking->book_date }}) <small class="text-danger">*</small></label>
                        <input type="date" name="book_date" value="{{ $booking->book_date ? \Carbon\Carbon::parse($booking->book_date)->format('Y-m-d') : '' }}" id="book_date" class="form-control" required>
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
                    <input type="hidden" name="staging_header_id" value="{{ $booking->id }}">
                </div>
                <div class="modern-modal-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="mdi mdi-content-save-outline mr-1"></i> Save Booking
                    </button>
                    <button type="button" class="btn modern-btn-light mr-2" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Modal body templates
    function getEditSampleModalBody(record) {
        return `
        <div class="modern-modal-body">
            <div class="form-group">
                <label for="edit_sample_type_name" class="control-label">Sample Type</label>
                <input type="text" class="form-control" id="edit_sample_type_name" name="sample_type_name" value="${record.sample_type_name || ''}" required>
            </div>
            <div class="form-group">
                <label for="edit_sample_point" class="control-label">Site Sampled</label>
                <input type="text" class="form-control" id="edit_sample_point" name="sample_point" value="${record.sample_point || ''}">
            </div>
            <div class="form-group">
                <label for="edit_sample_description" class="control-label">Sample Description</label>
                <input type="text" class="form-control" id="edit_sample_description" name="sample_description" value="${record.sample_description || ''}">
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="edit_sampling_date" class="control-label">Sampling Date</label>
                    <input type="date" class="form-control" id="edit_sampling_date" name="sampling_date" value="${record.sampling_date || ''}">
                </div>
                <div class="form-group col-md-6">
                    <label for="edit_sampling_time" class="control-label">Sampling Time</label>
                    <input type="time" class="form-control" id="edit_sampling_time" name="sampling_time" value="${record.sampling_time || ''}">
                </div>
            </div>
            <div class="form-group">
                <label for="edit_test_required" class="control-label">Test Required</label>
                <input type="text" class="form-control" id="edit_test_required" name="test_required" value="${record.test_required || ''}">
            </div>
            <div class="form-group">
                <label for="edit_comments" class="control-label">Comments</label>
                <textarea class="form-control" id="edit_comments" name="comments" rows="2">${record.comments || ''}</textarea>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="edit_temperature" class="control-label">Temperature</label>
                    <input type="number" step="any" class="form-control" id="edit_temperature" name="temperature" value="${record.temperature || ''}">
                </div>
                <div class="form-group col-md-4">
                    <label for="edit_ph" class="control-label">PH</label>
                    <input type="number" step="any" class="form-control" id="edit_ph" name="ph" value="${record.ph || ''}">
                </div>
                <div class="form-group col-md-4">
                    <label for="edit_ppm" class="control-label">PPM</label>
                    <input type="number" step="any" class="form-control" id="edit_ppm" name="ppm" value="${record.ppm || ''}">
                </div>
            </div>
        </div>
        <div class="modern-modal-footer d-flex justify-content-end">
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="mdi mdi-content-save-outline mr-1"></i> Save Changes
            </button>
            <button type="button" class="btn modern-btn-light mr-2" data-dismiss="modal">Cancel</button>
        </div>
        `;
    }

    function getDeleteSampleModalBody(record) {
        var details = '';
        if (record.sample_type_name) {
            details += '<b>Sample Type:</b> ' + record.sample_type_name + '<br>';
        }
        if (record.sample_point) {
            details += '<b>Site Sampled:</b> ' + record.sample_point + '<br>';
        }
        if (record.sampling_date) {
            details += '<b>Date:</b> ' + record.sampling_date + '<br>';
        }
        if (record.sampling_time) {
            details += '<b>Time:</b> ' + record.sampling_time + '<br>';
        }
        if (record.sample_description) {
            details += '<b>Description:</b> ' + record.sample_description + '<br>';
        }
        return `
        <div class="modern-modal-body">
            <div class="text-center">
                <i class="mdi mdi-alert-circle-outline text-danger" style="font-size:2.5rem;"></i>
                <h5 class="mt-3 mb-2">Are you sure you want to delete this sample?</h5>
                <div id="delete-sample-details" class="text-muted small mb-2">${details}</div>
                <div class="alert alert-warning mt-2 mb-0 py-2 px-3" style="font-size:0.95rem;">
                    This action cannot be undone.
                </div>
            </div>
        </div>
        <div class="modern-modal-footer d-flex justify-content-end">
            <button type="submit" class="btn btn-sm btn-danger">
                <i class="mdi mdi-trash-can-outline mr-1"></i> Delete
            </button>
            <button type="button" class="btn modern-btn-light mr-2" data-dismiss="modal">Cancel</button>
        </div>
        `;
    }

    // Populate modal with sample data on open (Edit)
    $('#edit-booking-sample').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var record = button.data('record');
        if (typeof record === 'string') {
            record = JSON.parse(record);
        }
        // Set hidden field and form action
        $('#edit_sample_id').val(record.id || '');
        

        // Remove any previous modal body/footer and append new
        $('#edit-sample-form .modern-modal-body, #edit-sample-form .modern-modal-footer').remove();
        $('#edit-sample-form').append(getEditSampleModalBody(record));

       
    });

    $('#edit-lab-booking').on('show.bs.modal', function (event) {
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

    // Populate modal with sample data on open (Delete)
    $('#delete-booking-sample').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var record = button.data('record');
        if (typeof record === 'string') {
            record = JSON.parse(record);
        }
        $('#delete_sample_id').val(record.id || '');

        var deleteUrl = "{{ url('/customer/lab-book/sample/delete') }}/" + (record.id || '');
        $('#delete-sample-form').attr('action', deleteUrl);

        // Remove any previous modal body/footer and append new
        $('#delete-sample-form .modern-modal-body, #delete-sample-form .modern-modal-footer').remove();
        $('#delete-sample-form').append(getDeleteSampleModalBody(record));
    });
</script>
@endsection