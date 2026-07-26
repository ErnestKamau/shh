@extends('layouts.planner.layout.app', ['dataTable' => true, 'select2' => true])

@section('title2')
<title>System Planner - Calendar</title>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => route('system-planner.dashboard'),
            'name' => 'System Planner',
            'icon' => null
        ),
        array(
            'link' => route('full-calendar'),
            'name' => 'Calendar',
            'icon' => null
        ),
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="mb-2 mt-2 d-flex justify-content-around">
        <div class="card card-widget text-white text-center no-overflow" style="height:100%;background-color:#2196f3">
            <div class="card-body">
                <div class="rotate">
                    <i class="mdi mdi-calendar-arrow-right fa-4x"></i>
                </div>
                <h6 class="text-uppercase">Upcoming Events</h6>
                <h1 class="display-4">{{$statusCounts['Upcoming'] ?? 0}}</h1>
            </div>
        </div>

        <div class="card card-widget text-white text-center no-overflow" style="height:100%;background-color:#2e7d32">
            <div class="card-body">
                <div class="rotate">
                    <i class="mdi mdi-checkbox-multiple-marked-circle fa-4x"></i>
                </div>
                <h6 class="text-uppercase">Completed Events</h6>
                <h1 class="display-4">{{$statusCounts['Complete'] ?? 0}}</h1>
            </div>
        </div>

        <div class="card card-widget text-white text-center no-overflow" style="height:100%;background-color:#e1b200">
            <div class="card-body">
                <div class="rotate">
                    <i class="mdi mdi-calendar-clock fa-4x"></i>
                </div>
                <h6 class="text-uppercase">Delayed Events</h6>
                <h1 class="display-4">{{$statusCounts['Delayed'] ?? 0}}</h1>
            </div>
        </div>

        <div class="card card-widget text-white text-center no-overflow" style="height:100%;background-color:#e65100">
            <div class="card-body">
                <div class="rotate">
                    <i class="mdi mdi-calendar-arrow-right fa-4x"></i>
                </div>
                <h6 class="text-uppercase">Expired Events</h6>
                <h1 class="display-4">{{$statusCounts['Expired'] ?? 0}}</h1>
            </div>
        </div>

        <div class="card card-widget text-white text-center no-overflow" style="height:100%;background-color:#c62828">
            <div class="card-body">
                <div class="rotate">
                    <i class="mdi mdi-calendar-remove fa-4x"></i>
                </div>
                <h6 class="text-uppercase">Cancelled Events</h6>
                <h1 class="display-4">{{$statusCounts['Cancelled'] ?? 0}}</h1>
            </div>
        </div>

        <div class="card card-widget text-white text-center no-overflow" style="height:100%;background-color:blue">
            <div class="card-body">
                <div class="rotate">
                    <i class="mdi mdi-calendar-arrow-right fa-4x"></i>
                </div>
                <h6 class="text-uppercase">Ongoing Events</h6>
                <h1 class="display-4">{{$ong}}</h1>
            </div>
        </div>
    </div>

    <div class="card tab-card" id="body-content">
        <div class="card-header tab-card-header bg-light">
            <h5 class="card-title alert alert-info mb-0">
                <i class="mdi mdi-calendar"></i> <span class="btn btn-default" id="view-text">Month View</span>
                <div class="nav-item dropdown float-right">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" style="color:black;font-size:14px" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="mdi mdi-compare-vertical"></i> Change View
                    </a>
                    <div class="dropdown-menu" style="font-size: 13px;" aria-labelledby="navbarDropdown">
                        <span class="dropdown-item btn" id="change-view"><i class="mdi mdi-subdirectory-arrow-right"></i> Week View</span>
                        <span class="dropdown-item btn" id="month-view"><i class="mdi mdi-subdirectory-arrow-right"></i> Month View</span>
                    </div>
                </div>
            </h5>
        </div>
        <div class="card-body p-2" style="background-color: white;">
            <div id='calendar' data-event="{{json_encode($events)}}" class="p-2"></div>
        </div>
    </div>
</main>

@include('layouts.planner.modals')
@endsection

@section('script2')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js" integrity="sha256-4iQZ6BVL4qNKlQ27TExEhBN1HFPvAvAMbFavKKosSWQ=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>

<script type="text/javascript">
    var current_user = {!! json_encode(Auth::user()->id) !!};
</script>

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyBqS4AEZ-gVeXjG794Rh0eTd6yvdfMKTjg&sensor=false" type="text/javascript"></script>
<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>

<script>
    $(document).ready(function () {
        tinymce.init({
            selector: 'textarea.editor'
        });

        var syncFrequencyOptions = function (formSelector) {
            var $form = $(formSelector);
            var $startDateInput = $form.find('input[name="start_date"]');
            var $endDateInput = $form.find('input[name="end_date"]');
            var $frequencySelect = $form.find('select[name="frequency"]');
            
            if (!$startDateInput.length || !$endDateInput.length || !$frequencySelect.length) {
                return;
            }
            
            var startVal = $startDateInput.val();
            var endVal = $endDateInput.val();
            
            if (!startVal || !endVal) {
                return;
            }
            
            var start = moment(startVal);
            var end = moment(endVal);
            var diffDays = end.diff(start, 'days');
            
            // Enable all options first
            $frequencySelect.find('option').prop('disabled', false);
            
            if (diffDays < 7) {
                // Less than a week: only Daily (1) is allowed. Disable others.
                $frequencySelect.find('option').each(function() {
                    var val = $(this).val();
                    if (val && val !== '1') {
                        $(this).prop('disabled', true);
                    }
                });
                var currentVal = $frequencySelect.val();
                if (currentVal && currentVal !== '1') {
                    $frequencySelect.val('1');
                }
            } else if (diffDays < 30) {
                // Less than a month: Daily (1) and Weekly (7) are allowed. Disable others.
                $frequencySelect.find('option').each(function() {
                    var val = $(this).val();
                    if (val && val !== '1' && val !== '7') {
                        $(this).prop('disabled', true);
                    }
                });
                var currentVal = $frequencySelect.val();
                if (currentVal && currentVal !== '1' && currentVal !== '7') {
                    $frequencySelect.val('1');
                }
            }
            
            // Re-initialize Select2 if it exists
            if ($frequencySelect.hasClass('select2-hidden-accessible') || $frequencySelect.data('select2')) {
                $frequencySelect.select2('destroy').select2({
                    width: '100%',
                    dropdownParent: $form.closest('.modal')
                });
            } else {
                $frequencySelect.trigger('change');
            }
        };

        $(document).on('change', 'input[name="start_date"], input[name="end_date"]', function() {
            var form = $(this).closest('form');
            syncFrequencyOptions(form);
        });

        var viewDescriptionBody = (data)=>{
            var body = $(`
                <div class="alert alert-default p-2">
                    <div class="alert-header bg-light p-2 border-bottom">
                        <b>${data.title} : Description</b>
                    </div>
                    <div class="alert-body mt-2">${data.description}</div>
                </div>
            `).clone();
            return body;
        }

        var viewLogisticsBody = (data)=>{
            var body =$(`
                <div class="alert alert-default p-2">
                    <div class="alert-header bg-light p-2 border-bottom">
                        <b>${data.title} : Logistics</b>
                    </div>
                    <div class="alert-body mt-2">
                        ${data.logistics}
                    </div>
                </div>
            `).clone();
            return body;
        }

        $('#view-description').on('show.bs.modal',(e)=>{
            var record = $(e.relatedTarget).data('record');
            var body = viewDescriptionBody(record);
            $('#view-description').find('.modal-body').empty();
            $('#view-description').find('.modal-body').append(body);
        });

        $('#view-logistics').on('show.bs.modal',(e)=>{
            var record = $(e.relatedTarget).data('record');
            var body =viewLogisticsBody(record);
            $('#view-logistics').find('.modal-body').empty();
            $('#view-logistics').find('.modal-body').append(body);
        });

        var viewEventBody = (event,personnel, clients, history, occurrences)=>{
            var occurrenceBanner = '';
            if (event.parent_id && event.parent_id != event.id) {
                occurrenceBanner = `
                    <div class="occurrence-banner">
                        <span><i class="mdi mdi-alert-circle-outline mr-1"></i> You are viewing an individual occurrence of this recurring event.</span>
                        <button type="button" class="btn btn-xs btn-dark load-parent-btn" data-parent-id="${event.parent_id}">
                            <i class="mdi mdi-arrow-left"></i> View Parent Event
                        </button>
                    </div>
                `;
            }
            var body = $(`
            <div class="tab-pane fade show active p-3" id="EventDetails-tab" role="tabpanel" aria-labelledby="one-tab">
                ${occurrenceBanner}
                <form action="{{route('editEvent')}}" method="post">
                    @csrf
                    <div class="modal-body p-0 shadow-none">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="text-secondary font-weight-bold mb-0"><i class="mdi mdi-eye mr-1"></i> View Event Details</h6>
                            <span class="btn btn-sm btn-outline-primary initiate-edit-mode"><i class="mdi mdi-pencil"></i> Edit Mode</span>
                        </div>
                        <div class="row">
                            <!-- Section: General Details -->
                            <div class="col-md-12">
                                <div class="form-section-title"><i class="mdi mdi-information-outline"></i> Event Information</div>
                            </div>
                            <div class="col-md-12 form-group">
                                <label class="control-label"><i class="mdi mdi-inbox-full"></i> Title <span class="text-danger">*</span></label>
                                <textarea name="title" class="form-control" disabled required>${event.title}</textarea>
                            </div>
                            
                            <!-- Section: Timing -->
                            <div class="col-md-12 mt-2">
                                <div class="form-section-title"><i class="mdi mdi-calendar-clock"></i> Timeline & Routine</div>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label"><i class="mdi mdi-alarm-check"></i> Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" value="${event.start_date}" disabled class="form-control" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label"><i class="mdi mdi-clock-start"></i> Start Time</label>
                                <input type="time" name="start_time" value="${event.start_time}" disabled class="form-control">
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="control-label"><i class="mdi mdi-alarm-check"></i> End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" value="${event.end_date}" disabled class="form-control" required>
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="control-label"><i class="mdi mdi-clock-start"></i> End Time</label>
                                <input type="time" name="end_time" value="${event.end_time}" disabled class="form-control">
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" name="is_routine" id="is-routine" disabled />
                                    <label class="form-check-label font-weight-bold"> Event Frequency ? </label>
                                </div>
                            </div>
                            <div class="form-group col-md-4 hidden" id="frequency-field">
                                <label class="control-label">Frequency <span class="text-danger">*</span></label>
                                <select name="frequency" id="frequecy-set" class="form-control">
                                    <option value="" disabled>Select Frequency</option>
                                    <option value="1">Daily</option>
                                    <option value="7">Weekly</option>
                                    <option value="30">Monthly</option>
                                    <option value="90">Quarterly</option>
                                    <option value="180">Semi Annually</option>
                                    <option value="365">Annually</option>
                                </select>
                            </div>

                            <!-- Section: Clients & Assignees -->
                            <div class="col-md-12 mt-2">
                                <div class="form-section-title"><i class="mdi mdi-account-group-outline"></i> Client & Assignment</div>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="control-label"><i class="mdi mdi-account-multiple-outline"></i> Client <span class="text-danger">*</span></label>
                                <select name="client_id" class="form-control select2" required>
                                    <option value="">Choose Client</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="control-label"><i class="mdi mdi-calendar-range"></i> Contract Validity From</label>
                                <input type="date" name="contract_valid_from" class="form-control" readonly>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="control-label"><i class="mdi mdi-calendar-range"></i> Contract Validity To</label>
                                <input type="date" name="contract_valid_to" class="form-control" readonly>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="control-label"><i class="mdi mdi-account-supervisor"></i> Responsible Personnel <span class="text-danger">*</span></label>
                                <select name="responsible_id[]" class="form-control select2" required multiple>
                                    <option value="">Choose Responsible Personnel</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="control-label"><i class="mdi mdi-calendar-heart"></i> Event Status <span class="text-danger">*</span></label>
                                <select name="event_status" class="form-control" required>
                                    <option value=""></option>
                                    <option value="Upcoming">Upcoming</option>
                                    <option value="In-progress">In-progress</option>
                                    <option value="Complete">Complete</option>
                                    <option value="Delayed">Delayed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>

                            <!-- Section: Details & Notes -->
                            <div class="col-md-12 mt-2">
                                <div class="form-section-title"><i class="mdi mdi-text-subject"></i> Description & Logistics</div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="control-label"><i class="mdi mdi-view-headline"></i> Description <span class="text-danger">*</span></label>
                                <div class="p-3 bg-light border rounded text-secondary" style="font-size: 13px; line-height: 1.5; min-height: 80px; overflow-y: auto;">
                                    ${event.description || '<span class="text-muted">No description provided.</span>'}
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="control-label"><i class="mdi mdi-view-headline"></i> Logistics Detail</label>
                                <div class="p-3 bg-light border rounded text-secondary" style="font-size: 13px; line-height: 1.5; min-height: 80px; overflow-y: auto;">
                                    ${event.logistics || '<span class="text-muted">No logistics details provided.</span>'}
                                </div>
                            </div>

                            <!-- Section: Location & Attachments -->
                            <div class="col-md-12 mt-2">
                                <div class="form-section-title"><i class="mdi mdi-map-marker-outline"></i> Location & Attachments</div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="control-label"><i class="mdi mdi-map-marker"></i> Location</label>
                                <div class="d-flex align-items-center">
                                    <input type="text" name="location" value="${event.location || ''}" disabled class="form-control flex-fill" placeholder="Location...">
                                    ${event.latitude ? `
                                        <a href="https://www.google.com/maps/search/?api=1&query=${event.latitude},${event.longitude}" target="_blank" class="btn btn-sm btn-outline-primary ml-2 d-flex align-items-center gap-1">
                                            <i class="mdi mdi-google-maps"></i> Map
                                        </a>
                                    ` : ''}
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="control-label"><i class="mdi mdi-paperclip"></i> Attachment</label>
                                <div class="d-flex align-items-center" style="height: 38px;">
                                    ${event.attachment ? `
                                        <a href="${event.attachment}" target="_blank" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                                            <i class="mdi mdi-download"></i> Download Attachment
                                        </a>
                                    ` : '<span class="text-muted" style="font-size: 13px;">No attachment available</span>'}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer p-0 pt-3 border-top mt-3">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
            <div class="tab-pane fade p-3" id="EventHistory-tab" role="tabpanel" aria-labelledby="one-tab">
                <div id="history-card" class="history-timeline" style="max-height: 450px; overflow-y: auto;">
                </div>
            </div>
            <div class="tab-pane fade p-3" id="EventOccurrences-tab" role="tabpanel" aria-labelledby="one-tab">
                <div style="max-height: 450px; overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="bg-light text-secondary">
                                <tr>
                                    <th class="border-0">Event Title</th>
                                    <th class="border-0">Customer Name</th>
                                    <th class="border-0">Date</th>
                                    <th class="border-0">Start Time</th>
                                    <th class="border-0">Personnel</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0 text-center" style="width: 130px;">Action</th>
                                </tr>
                            </thead>
                            <tbody class="occurrences-tbody">
                                <!-- Dynamically populated -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            `).clone();

            if (event.is_routine == 1) {
                $(body).find('#is-routine').prop('checked', true);
                $(body).find('#frequency-field').removeClass('hidden');
            }
            $(body).find('#frequecy-set').val(event.frequency);
            $(body).find('#frequecy-set').select2({ width: '100%', dropdownParent: $('#event-modal') });
            $(body).find('#frequecy-set').attr('disabled',true);
            $(body).find('select[name="event_status"]').val(event.status);
            $(body).find('select[name="event_status"]').select2({ width: '100%', dropdownParent: $('#event-modal') });
            $(body).find('select[name="event_status"]').attr('disabled',true);
            $.each(clients, function (i, e) {
                var contractFrom = e.contract_valid_from ? e.contract_valid_from.substring(0, 10) : '';
                var contractTo = e.contract_valid_to ? e.contract_valid_to.substring(0, 10) : '';
                var option_ = `<option value="${e.id}" ${e.id == event.client_id ? `selected` : ''} data-contract-from="${contractFrom}" data-contract-to="${contractTo}" >${e.name}</option>`
                $(body).find('select[name="client_id"]').append(option_);
            });
            $(body).find('select[name="client_id"]').select2({ width: '100%', dropdownParent: $('#event-modal') });
            $(body).find('select[name="client_id"]').attr('disabled',true);
            var initialClientOption = $(body).find('select[name="client_id"] option:selected');
            if (initialClientOption.length) {
                $(body).find('input[name="contract_valid_from"]').val(initialClientOption.data('contract-from') || '');
                $(body).find('input[name="contract_valid_to"]').val(initialClientOption.data('contract-to') || '');
            }
            var responsible_ = event.responsible_id.split(',');
            $.each(personnel, function (i, e) {
                var option_ = `<option value="${e.id}" ${responsible_.includes(e.id.toString()) ? `selected` : ''} >${e.name}</option>`
                $(body).find('select[name="responsible_id[]"]').append(option_);
            });
            $(body).find('select[name="responsible_id[]"]').select2({ width: '100%', dropdownParent: $('#event-modal') });
            $(body).find('select[name="responsible_id[]"]').attr('disabled',true);
            $(body).find('#history-card').empty();
            if (history && history.length > 0) {
                $.each(history, function (i, e) {
                    var formattedDate = e.created_at ? e.created_at.substring(0, 19).replace('T', ' ') : '';
                    var statusClass = (e.status || '').toLowerCase().trim();
                    var historyCard = $(`
                        <div class="history-card">
                            <div class="history-card-header">
                                <strong class="text-dark"><i class="mdi mdi-account-outline mr-1"></i> ${e.name || 'System'}</strong>
                                <span class="text-muted"><i class="mdi mdi-clock-outline mr-1"></i> ${formattedDate}</span>
                            </div>
                            <div class="history-card-body">
                                <div><strong class="text-secondary">Status:</strong> <span class="badge-status badge-status-${statusClass}">${e.status || 'N/A'}</span></div>
                                <div class="mt-1"><strong class="text-secondary">Remark:</strong> ${e.remark || 'N/A'}</div>
                            </div>
                        </div>
                    `).clone();
                    $(body).find('#history-card').append(historyCard);
                });
            } else {
                $(body).find('#history-card').append(`
                    <div class="text-center text-muted py-5" style="width: 100%;">
                        <i class="mdi mdi-history fa-2x mb-2 text-secondary"></i>
                        <p class="mb-0" style="font-size: 13px;">No history records found for this event.</p>
                    </div>
                `);
            }

            // Populate occurrences if available
            if (occurrences && occurrences.length > 0) {
                $('#event-occurrences-li').removeClass('d-none');
                $('#occurrences-count').text(occurrences.length);
                var tbody = $(body).find('.occurrences-tbody');
                tbody.empty();
                $.each(occurrences, function(index, occ) {
                    var statusOptions = ['Upcoming', 'In-progress', 'Complete', 'Delayed', 'Cancelled'];
                    var statusClass = 'select-status-' + (occ.status || 'upcoming').toLowerCase().replace(' ', '').replace('-', '');
                    var selectHtml = `<select class="form-control form-control-sm occurrence-status-select ${statusClass}" data-id="${occ.id}">`;
                    $.each(statusOptions, function(i, opt) {
                        selectHtml += `<option value="${opt}" ${occ.status == opt ? 'selected' : ''}>${opt}</option>`;
                    });
                    selectHtml += `</select>`;

                    var clientObj = clients.find(c => c.id == occ.client_id);
                    var clientName = clientObj ? clientObj.name : 'N/A';

                    var occResponsibleIds = (occ.responsible_id || '').split(',');
                    var occPersonnelNames = [];
                    $.each(occResponsibleIds, function(idx, rId) {
                        var person = personnel.find(p => p.id == rId.trim());
                        if (person) {
                            occPersonnelNames.push(person.name);
                        }
                    });
                    var personnelDisplay = occPersonnelNames.length > 0 ? occPersonnelNames.join(', ') : 'N/A';

                    var actionHtml = `
                        <div class="occurrence-actions">
                            <button type="button" class="btn btn-xs view-occurrence-btn" data-id="${occ.id}" title="View">
                                <i class="mdi mdi-eye text-primary"></i>
                            </button>
                            <button type="button" class="btn btn-xs edit-occurrence-btn" data-id="${occ.id}" title="Edit">
                                <i class="mdi mdi-pencil text-warning"></i>
                            </button>
                            <button type="button" class="btn btn-xs delete-occurrence-btn" title="Delete">
                                <i class="mdi mdi-delete text-danger"></i>
                            </button>
                        </div>
                    `;

                    var row = $(`
                        <tr>
                            <td>${occ.title || event.title}</td>
                            <td>${clientName}</td>
                            <td>${occ.start_date}</td>
                            <td>${occ.start_time || 'N/A'}</td>
                            <td>${personnelDisplay}</td>
                            <td>${selectHtml}</td>
                            <td class="text-center">${actionHtml}</td>
                        </tr>
                    `);
                    row.find('.delete-occurrence-btn').data('events', occ);
                    tbody.append(row);
                });

                // Attach change event listener
                $(body).on('change', '.occurrence-status-select', function() {
                    var occurrenceId = $(this).data('id');
                    var newStatus = $(this).val();
                    var selectEl = $(this);
                    selectEl.attr('disabled', true);
                    $.ajax({
                        url: '{{ route("updateOccurrenceStatus") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            occurrence_id: occurrenceId,
                            status: newStatus
                        },
                        success: function(response) {
                            selectEl.removeAttr('disabled');
                            selectEl.removeClass('select-status-upcoming select-status-inprogress select-status-complete select-status-delayed select-status-cancelled');
                            var cleanStatus = newStatus.toLowerCase().replace(' ', '').replace('-', '');
                            selectEl.addClass('select-status-' + cleanStatus);
                        },
                        error: function() {
                            selectEl.removeAttr('disabled');
                            alert('Failed to update occurrence status.');
                        }
                    });
                });
            } else {
                $('#event-occurrences-li').addClass('d-none');
            }

            $(body).find('.initiate-edit-mode').on('click',()=>{
                mode = 'edit';
                $('#event-modal').find('.tab-content').empty();
                getEventBody(mode,event.id,personnel,clients);
            });
            return body;
        }

        var eventBody = function (event, personnel, clients, history, notification, occurrences) {
            var occurrenceBanner = '';
            if (event.parent_id && event.parent_id != event.id) {
                occurrenceBanner = `
                    <div class="occurrence-banner">
                        <span><i class="mdi mdi-alert-circle-outline mr-1"></i> You are editing an individual occurrence of this recurring event.</span>
                        <button type="button" class="btn btn-xs btn-dark load-parent-btn" data-parent-id="${event.parent_id}">
                            <i class="mdi mdi-arrow-left"></i> Edit Parent Event
                        </button>
                    </div>
                `;
            }
            var body_ = $(`
                <div class="tab-pane fade show active p-3" id="EventDetails-tab" role="tabpanel" aria-labelledby="one-tab">
                <form action="{{route('editEvent')}}" method="post">
                @csrf
                ${occurrenceBanner}
                <div class="modal-body p-0 shadow-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-secondary font-weight-bold mb-0"><i class="mdi mdi-pencil mr-1"></i> Edit Event Details</h6>
                        <span class="btn btn-sm btn-outline-primary initiate-view-mode"><i class="mdi mdi-eye"></i> View Mode</span> 
                    </div>
                    <div class="row">
                        <!-- Section: General Details -->
                        <div class="col-md-12">
                            <div class="form-section-title"><i class="mdi mdi-information-outline"></i> Event Information</div>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="control-label"><i class="mdi mdi-inbox-full"></i> Title <span class="text-danger">*</span></label>
                            <textarea name="title" class="form-control" required>${event.title}</textarea>
                        </div>
                        <input type="hidden" name="event_id" value="${event.id}">

                        <!-- Section: Timing -->
                        <div class="col-md-12 mt-2">
                            <div class="form-section-title"><i class="mdi mdi-calendar-clock"></i> Timeline & Routine</div>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label"><i class="mdi mdi-alarm-check"></i> Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" value="${event.start_date}" class="form-control" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label"><i class="mdi mdi-clock-start"></i> Start Time</label>
                            <input type="time" name="start_time" value="${event.start_time}" class="form-control">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="control-label"><i class="mdi mdi-alarm-check"></i> End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" value="${event.end_date}" class="form-control" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="control-label"><i class="mdi mdi-clock-start"></i> End Time</label>
                            <input type="time" name="end_time" value="${event.end_time}" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="is_routine" id="is-routine" />
                                <label class="form-check-label font-weight-bold"> Event Frequency ? </label>
                            </div>
                        </div>
                        <div class="form-group col-md-4 hidden" id="frequency-field">
                            <label class="control-label">Frequency <span class="text-danger">*</span></label>
                            <select name="frequency" id="frequecy-set" class="form-control">
                                <option value="" disabled>Select Frequency</option>
                                <option value="1">Daily</option>
                                <option value="7">Weekly</option>
                                <option value="30">Monthly</option>
                                <option value="90">Quarterly</option>
                                <option value="180">Semi Annually</option>
                                <option value="365">Annually</option>
                            </select>
                        </div>

                        <!-- Section: Clients & Assignees -->
                        <div class="col-md-12 mt-2">
                            <div class="form-section-title"><i class="mdi mdi-account-group-outline"></i> Client & Assignment</div>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="control-label"><i class="mdi mdi-account-multiple-outline"></i> Client <span class="text-danger">*</span></label>
                            <select name="client_id" class="form-control select2" required>
                                <option value="">Choose Client</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="control-label"><i class="mdi mdi-calendar-range"></i> Contract Validity From</label>
                            <input type="date" name="contract_valid_from" class="form-control" readonly>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="control-label"><i class="mdi mdi-calendar-range"></i> Contract Validity To</label>
                            <input type="date" name="contract_valid_to" class="form-control" readonly>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="control-label"><i class="mdi mdi-account-supervisor"></i> Responsible Personnel <span class="text-danger">*</span></label>
                            <select name="responsible_id[]" class="form-control select2" required multiple>
                                <option value="">Choose Responsible Personnel</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="control-label"><i class="mdi mdi-calendar-heart"></i> Event Status <span class="text-danger">*</span></label>
                            <select name="event_status" class="form-control" required>
                                <option value=""></option>
                                <option value="Upcoming">Upcoming</option>
                                <option value="In-progress">In-progress</option>
                                <option value="Complete">Complete</option>
                                <option value="Delayed">Delayed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="control-label"><i class="mdi mdi-paperclip"></i> Attachment</label>
                            <input type="file" name="attachment" class="form-control">
                        </div>

                        <!-- Section: Details & Notes -->
                        <div class="col-md-12 mt-2">
                            <div class="form-section-title"><i class="mdi mdi-text-subject"></i> Description & Logistics</div>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="control-label"><i class="mdi mdi-view-headline"></i> Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" rows="4" name="description" placeholder="Description..." required>${event.description}</textarea>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="control-label"><i class="mdi mdi-view-headline"></i> Logistics Detail</label>
                            <textarea name="logistics" id="logistics" class="form-control editor">${event.logistics}</textarea>
                        </div>

                        <!-- Section: Location Details -->
                        <div class="col-md-12 mt-2">
                            <div class="form-section-title"><i class="mdi mdi-map-marker-outline"></i> Location Details</div>
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="control-label d-flex justify-content-between align-items-center">
                                <span><i class="mdi mdi-map-marker"></i> Location</span>
                                <small class="btn btn-xs btn-outline-primary initiate-map">Choose From Map?</small>
                            </label>
                            <input type="text" name="location" value="${event.location || ''}" class="form-control" placeholder="Location...">
                            <input type="hidden" name="latitude" value="${event.latitude || ''}" id="latitude_edit">
                            <input type="hidden" name="longitude" value="${event.longitude || ''}" id="longitude_edit">
                        </div>
                        <div class="col-md-12">
                            <div id="contain-maps" class="hidden border-top pt-3 mt-3">
                                <span class="btn btn-sm btn-outline-danger float-right destroy-map mb-3">Close Map</span>
                                <div id="maps-sect-" style="width: 100%; height: 70vh;"></div>
                            </div>
                        </div>

                        <!-- Section: Notifications -->
                        <div class="col-md-12 mt-2">
                            <div class="form-section-title"><i class="mdi mdi-bell-ring-outline"></i> Notifications</div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="notification" id="my-task" />
                                <label class="form-check-label font-weight-bold"> Send Instant Notification ? </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="notify_client" id="my-task" />
                                <label class="form-check-label font-weight-bold"> Notify Client ? </label>
                            </div>
                        </div>
                        
                        <div class="col-md-12 form-group mt-3">
                            <label class="control-label"><i class="mdi mdi-bell-ring"></i> Scheduled Reminders</label>
                            <div class="notification-edit">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <input type="text" value="Email" disabled class="form-control">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="number" name="duration[]" placeholder="Duration..." class="form-control">
                                    </div>
                                    <div class="col-sm-4">
                                        <select name="rate[]" class="form-control">
                                            <option value="Minutes">Minutes</option>
                                            <option value="Hours">Hours</option>
                                            <option value="Days">Days</option>
                                            <option value="Weeks">Weeks</option>
                                            <option value="Months">Months</option>
                                            <option value="Years">Years</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <span class="btn btn-outline-secondary btn-sm mt-3" id="add-notification-edit" style="border-radius: 20px;">
                                <i class="mdi mdi-plus"></i> Add Notification
                            </span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-0 pt-3 border-top mt-3">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="mdi mdi-content-save mr-1"></i> Save Changes</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                </div>
                </form>
            </div>
            <div class="tab-pane fade p-3" id="EventHistory-tab" role="tabpanel" aria-labelledby="one-tab">
                <div id="history-card" class="history-timeline" style="max-height: 450px; overflow-y: auto;">
                </div>
            </div>
            <div class="tab-pane fade p-3" id="EventOccurrences-tab" role="tabpanel" aria-labelledby="one-tab">
                <div style="max-height: 450px; overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="bg-light text-secondary">
                                <tr>
                                    <th class="border-0">Event Title</th>
                                    <th class="border-0">Customer Name</th>
                                    <th class="border-0">Date</th>
                                    <th class="border-0">Start Time</th>
                                    <th class="border-0">Personnel</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0 text-center" style="width: 130px;">Action</th>
                                </tr>
                            </thead>
                            <tbody class="occurrences-tbody">
                                <!-- Dynamically populated -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            `).clone();

            $(body_).find('.destroy-map').on('click', (e) => {
                $(body_).find('#contain-maps').addClass('hidden');
                $(body_).find('.initiate-map').removeClass('hidden');
            });
            $(body_).find('.initiate-map').on('click', (e) => {
                $(body_).find('#contain-maps').removeClass('hidden');
                $(body_).find('.initiate-map').addClass('hidden');
                mapsGraph(true,event.latitude,event.longitude);
            });
            if (event.is_routine == 1) {
                $(body_).find('#is-routine').prop('checked', true);
                $(body_).find('#frequency-field').removeClass('hidden');
            }
            $(body_).find('#frequecy-set').val(event.frequency);
            $(body_).find('#frequecy-set').select2({ width: '100%', dropdownParent: $('#event-modal') });
            $(body_).find('select[name="event_status"]').val(event.status);
            $(body_).find('select[name="event_status"]').select2({ width: '100%', dropdownParent: $('#event-modal') });
            $.each(clients, function (i, e) {
                var contractFrom = e.contract_valid_from ? e.contract_valid_from.substring(0, 10) : '';
                var contractTo = e.contract_valid_to ? e.contract_valid_to.substring(0, 10) : '';
                var option_ = `<option value="${e.id}" ${e.id == event.client_id ? `selected` : ''} data-contract-from="${contractFrom}" data-contract-to="${contractTo}" >${e.name}</option>`
                $(body_).find('select[name="client_id"]').append(option_);
            });
            $(body_).find('select[name="client_id"]').select2({ width: '100%', dropdownParent: $('#event-modal') });
            var initialClientOption = $(body_).find('select[name="client_id"] option:selected');
            if (initialClientOption.length) {
                $(body_).find('input[name="contract_valid_from"]').val(initialClientOption.data('contract-from') || '');
                $(body_).find('input[name="contract_valid_to"]').val(initialClientOption.data('contract-to') || '');
            }
            var responsible_ = event.responsible_id.split(',');
            $.each(personnel, function (i, e) {
                var option_ = `<option value="${e.id}" ${responsible_.includes(e.id.toString()) ? `selected` : ''} >${e.name}</option>`
                $(body_).find('select[name="responsible_id[]"]').append(option_);
            });
            $(body_).find('select[name="responsible_id[]"]').select2({ width: '100%', dropdownParent: $('#event-modal') });
            $(body_).find('#history-card').empty();
            if (history && history.length > 0) {
                $.each(history, function (i, e) {
                    var formattedDate = e.created_at ? e.created_at.substring(0, 19).replace('T', ' ') : '';
                    var statusClass = (e.status || '').toLowerCase().trim();
                    var historyCard = $(`
                        <div class="history-card">
                            <div class="history-card-header">
                                <strong class="text-dark"><i class="mdi mdi-account-outline mr-1"></i> ${e.name || 'System'}</strong>
                                <span class="text-muted"><i class="mdi mdi-clock-outline mr-1"></i> ${formattedDate}</span>
                            </div>
                            <div class="history-card-body">
                                <div><strong class="text-secondary">Status:</strong> <span class="badge-status badge-status-${statusClass}">${e.status || 'N/A'}</span></div>
                                <div class="mt-1"><strong class="text-secondary">Remark:</strong> ${e.remark || 'N/A'}</div>
                            </div>
                        </div>
                    `).clone();
                    $(body_).find('#history-card').append(historyCard);
                });
            } else {
                $(body_).find('#history-card').append(`
                    <div class="text-center text-muted py-5" style="width: 100%;">
                        <i class="mdi mdi-history fa-2x mb-2 text-secondary"></i>
                        <p class="mb-0" style="font-size: 13px;">No history records found for this event.</p>
                    </div>
                `);
            }
            $(body_).find('.notification-edit').empty();
            $.each(notification, function (i, e) {
                var notifcation_ = `
                    <div class="row ml-3 mt-1">
                        <div class="col-sm-4">
                            <input type="text" value="Email" disabled class="form-control">
                        </div>
                        <div class="col-sm-4">
                            <input type="number" value="${e.duration}" name="duration[]" id="" class="form-control ">
                            <input type="hidden" name="notification_id[]" value="${e.id}">
                        </div>
                        <div class="col-sm-4">
                            <select name="rate[]" id="" class="form-control">
                                <option value="Minutes" ${e.rate == 'Minutes' ? `selected` : ``}>Minutes</option>
                                <option value="Hours" ${e.rate == 'Hours' ? `selected` : ``}>Hours</option>
                                <option value="Days" ${e.rate == 'Days' ? `selected` : ``}>Days</option>
                                <option value="Weeks" ${e.rate == 'Weeks' ? `selected` : ``}>Weeks</option>
                                <option value="Months" ${e.rate == 'Months' ? `selected` : ``}>Months</option>
                                <option value="Years" ${e.rate == 'Years' ? `selected` : ``}>Years</option>
                            </select>
                        </div>
                    </div>                                        
                `;
                $(body_).find('.notification-edit').append(notifcation_);
            });
            $(body_).find('#is-routine').on('change', function () {
                if (this.checked) {
                    $(body_).find('#frequecy-set').attr('required', true);
                    $(body_).find('#frequency-field').removeClass('hidden');
                } else {
                    $(body_).find('#frequecy-set').removeAttr('required');
                    $(body_).find('#frequency-field').addClass('hidden');
                }
            });

            // Populate occurrences if available
            if (occurrences && occurrences.length > 0) {
                $('#event-occurrences-li').removeClass('d-none');
                $('#occurrences-count').text(occurrences.length);
                var tbody = $(body_).find('.occurrences-tbody');
                tbody.empty();
                $.each(occurrences, function(index, occ) {
                    var statusOptions = ['Upcoming', 'In-progress', 'Complete', 'Delayed', 'Cancelled'];
                    var statusClass = 'select-status-' + (occ.status || 'upcoming').toLowerCase().replace(' ', '').replace('-', '');
                    var selectHtml = `<select class="form-control form-control-sm occurrence-status-select ${statusClass}" data-id="${occ.id}">`;
                    $.each(statusOptions, function(i, opt) {
                        selectHtml += `<option value="${opt}" ${occ.status == opt ? 'selected' : ''}>${opt}</option>`;
                    });
                    selectHtml += `</select>`;

                    var clientObj = clients.find(c => c.id == occ.client_id);
                    var clientName = clientObj ? clientObj.name : 'N/A';

                    var occResponsibleIds = (occ.responsible_id || '').split(',');
                    var occPersonnelNames = [];
                    $.each(occResponsibleIds, function(idx, rId) {
                        var person = personnel.find(p => p.id == rId.trim());
                        if (person) {
                            occPersonnelNames.push(person.name);
                        }
                    });
                    var personnelDisplay = occPersonnelNames.length > 0 ? occPersonnelNames.join(', ') : 'N/A';

                    var actionHtml = `
                        <div class="occurrence-actions">
                            <button type="button" class="btn btn-xs view-occurrence-btn" data-id="${occ.id}" title="View">
                                <i class="mdi mdi-eye text-primary"></i>
                            </button>
                            <button type="button" class="btn btn-xs edit-occurrence-btn" data-id="${occ.id}" title="Edit">
                                <i class="mdi mdi-pencil text-warning"></i>
                            </button>
                            <button type="button" class="btn btn-xs delete-occurrence-btn" title="Delete">
                                <i class="mdi mdi-delete text-danger"></i>
                            </button>
                        </div>
                    `;

                    var row = $(`
                        <tr>
                            <td>${occ.title || event.title}</td>
                            <td>${clientName}</td>
                            <td>${occ.start_date}</td>
                            <td>${occ.start_time || 'N/A'}</td>
                            <td>${personnelDisplay}</td>
                            <td>${selectHtml}</td>
                            <td class="text-center">${actionHtml}</td>
                        </tr>
                    `);
                    row.find('.delete-occurrence-btn').data('events', occ);
                    tbody.append(row);
                });

                // Attach change event listener
                $(body_).on('change', '.occurrence-status-select', function() {
                    var occurrenceId = $(this).data('id');
                    var newStatus = $(this).val();
                    var selectEl = $(this);
                    selectEl.attr('disabled', true);
                    $.ajax({
                        url: '{{ route("updateOccurrenceStatus") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            occurrence_id: occurrenceId,
                            status: newStatus
                        },
                        success: function(response) {
                            selectEl.removeAttr('disabled');
                            selectEl.removeClass('select-status-upcoming select-status-inprogress select-status-complete select-status-delayed select-status-cancelled');
                            var cleanStatus = newStatus.toLowerCase().replace(' ', '').replace('-', '');
                            selectEl.addClass('select-status-' + cleanStatus);
                        },
                        error: function() {
                            selectEl.removeAttr('disabled');
                            alert('Failed to update occurrence status.');
                        }
                    });
                });
            } else {
                $('#event-occurrences-li').addClass('d-none');
            }
            $(body_).find('.initiate-view-mode').on('click',()=>{
                mode = 'view';
                $('#event-modal').find('.tab-content').empty();
                getEventBody(mode,event.id,personnel,clients);
            });
            window.updateFrequencyOptions(body_);
            return body_;
        }

        var getEventAjax = (eventID,callback)=>{
            $.ajax({
                url: '/get/event/id/' + eventID,
                type: 'GET',
                success: function (data) {
                    callback(data)
                } 
            })
        }

        var currentEventID = null;

        $(document).on('click', '.edit-occurrence-btn', function() {
            var occurrenceId = $(this).data('id');
            var personnel = $('#event-modal').data('responsible_personnel');
            var clients = $('#event-modal').data('clients');
            
            mode = 'edit';
            $('#event-modal').find('.tab-content').empty();
            getEventBody('edit', occurrenceId, personnel, clients);
        });

        $(document).on('click', '.view-occurrence-btn', function() {
            var occurrenceId = $(this).data('id');
            var personnel = $('#event-modal').data('responsible_personnel');
            var clients = $('#event-modal').data('clients');
            
            mode = 'view';
            $('#event-modal').find('.tab-content').empty();
            getEventBody('view', occurrenceId, personnel, clients);
        });

        $(document).on('click', '.delete-occurrence-btn', function() {
            var occ = $(this).data('events');
            $('#delete-event').modal('show', this);
        });

        $(document).on('hidden.bs.modal', '#delete-event', function() {
            if ($('#event-modal').hasClass('show')) {
                $('body').addClass('modal-open');
            }
        });

        $(document).on('click', '.load-parent-btn', function() {
            var parentId = $(this).data('parent-id');
            var personnel = $('#event-modal').data('responsible_personnel');
            var clients = $('#event-modal').data('clients');
            
            var currentMode = mode || 'view';
            $('#event-modal').find('.tab-content').empty();
            getEventBody(currentMode, parentId, personnel, clients);
        });

        var getEventBody = (mode,event_id,personnel,clients)=>{
            currentEventID = event_id;
            getEventAjax(event_id,(data)=>{
                if(mode == "edit"){
                    var event_body = eventBody(data.event, personnel, clients, data.history, data.notification, data.occurrences)
                    var header_ = `<i class="mdi mdi-calendar-text"></i> ${data.event.title} Information`;
                    $('#event-modal').find('.modal-title').empty();
                    $('#event-modal').find('.modal-title').append(header_);
                    $('#event-modal').find('.tab-content').empty();
                    $('#event-modal').find('.tab-content').append(event_body);
                    if(data.event.latitude != ""){
                        $('#event-modal').find('#contain-maps').removeClass('hidden');
                        $('#event-modal').find('.initiate-map').addClass('hidden');
                        mapsGraph(true,data.event.latitude,data.event.longitude);
                    }
                    tinymce.init({
                        selector: 'textarea#logistics'
                    });
                    syncFrequencyOptions($('#event-modal').find('form'));
                }else{
                    var event_body =viewEventBody(data.event,personnel, clients, data.history, data.occurrences)
                    var header_ = `<i class="mdi mdi-calendar-text"></i> ${data.event.title} Information`;
                    $('#event-modal').find('.modal-title').empty();
                    $('#event-modal').find('.modal-title').append(header_);
                    $('#event-modal').find('.tab-content').empty();
                    $('#event-modal').find('.tab-content').append(event_body);
                }
                return "success";
            })
        }

        $('#event-modal').on('show.bs.modal', function (e) {
            var eventID = $(e.relatedTarget).data('events') || $('#event-modal').data('events');
            currentEventID = eventID;
            var personnel = $(this).data('responsible_personnel');
            var clients = $(this).data('clients');
            $(this).data('responsible_personnel', personnel);
            $(this).data('clients', clients);
            $(this).find('.modal-title').empty();
            var mode = $(e.relatedTarget).data('mode') || 'view';
            $('#event-modal').find('.tab-content').empty();
            getEventBody(mode,eventID,personnel,clients);
            
            $('.initiate-edit-mode').off('click').on('click',()=>{
                mode = 'edit';
                $('#event-modal').find('.tab-content').empty();
                getEventBody(mode,currentEventID,personnel,clients);
            });
            $('#event-modal').find('.initiate-view-mode').off('click').on('click',(e)=>{
                mode = 'view';
                $('#event-modal').find('.tab-content').empty();
                getEventBody(mode,currentEventID,personnel,clients);
            });
        })

        $('#change-view').on('click', function () {
            $('#calendar').fullCalendar('changeView', 'listWeek');
            var text = 'Week View';
            $('#view-text').empty();
            $('#view-text').append(text);
        });

        $('#month-view').on('click', function () {
            $('#calendar').fullCalendar('changeView', 'month');
            var text = 'Month View';
            $('#view-text').empty();
            $('#view-text').append(text);
        })

        $('#delete-event').on('show.bs.modal', function (e) {
            var event = $(e.relatedTarget).data('events');
            var text = $(`
                 <div class="alert alert-danger">
                    <i class="mdi mdi-alert-decagram"></i> Confirm you want to delete event - ${event.title}
                </div>
                
                <div class="form-group">
                    <input type="checkbox" id="delete-future" name="delete_future"> Delete all future recurring events!
                    <input type="hidden" name="event_id" value="${event.id}">
                </div>
                <div id="delete-recurring"><div>
            `);
            $('#delete-event').find('#delete-params').empty();
            $('#delete-event').find('#delete-params').append(text);
            $(this).find('#delete-future').on('change', function () {
                $('#delete-event').find('#delete-recurring').empty();
                if ($('#delete-event').find('#delete-future').is(':checked')) {
                    var addtext = $(`
                        <div class="alert alert-info">
                            <i class="mdi mdi-alert-decagram"></i> All future recurring events will be deleted!   
                        </div>
                        `);
                    $('#delete-event').find('#delete-recurring').append(addtext);
                }
            });
        })

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var mapsGraph = function (gps = null,latitude = null,longitude = null,notDragable = null) {
            if (gps) {
                var mapProp = {
                    center: new google.maps.LatLng(-4.05466, 39.66359),
                    zoom: 5.5,
                };
                map = new google.maps.Map(document.getElementById('maps-sect-'), mapProp);

                var marker = new google.maps.Marker({
                    position: new google.maps.LatLng(latitude, longitude),
                    title: `Event Location`,
                    draggable: notDragable ? false : true,
                });
                marker.setMap(map);
                if(!notDragable){
                    marker.addListener('drag', function (event) {
                        document.getElementById('latitude_edit').value = event.latLng.lat();
                        document.getElementById('longitude_edit').value = event.latLng.lng();
                    });
                    marker.addListener('dragend', function (event) {
                        document.getElementById('latitude_edit').value = event.latLng.lat();
                        document.getElementById('longitude_edit').value = event.latLng.lng();
                    });
                }
            } else {
                var mapProp = {
                    center: new google.maps.LatLng(-4.05466, 39.66359),
                    zoom: 6.5,
                };
                map = new google.maps.Map(document.getElementById('maps-sect'), mapProp);

                var marker = new google.maps.Marker({
                    position: mapProp.center,
                    draggable: true,
                });

                marker.setMap(map);
                marker.addListener('drag', function (event) {
                    document.getElementById('latitude').value = event.latLng.lat();
                    document.getElementById('longitude').value = event.latLng.lng();
                });
                marker.addListener('dragend', function (event) {
                    document.getElementById('latitude').value = event.latLng.lat();
                    document.getElementById('longitude').value = event.latLng.lng();
                });
            }
        }

        $('#create-event').on('show.bs.modal', function () {
            $('#create-event').find('.intiate-map').removeClass('hidden');

            $('#create-event').find('.select2').select2({
                width: '100%',
                dropdownParent: $('#create-event')
            });

            $('#create-event').find('#is-routine').on('change', function () {
                if (this.checked) {
                    $('#create-event').find('#frequecy-set').attr('required', true);
                    $('#create-event').find('#frequency-field').removeClass('hidden');
                } else {
                    $('#create-event').find('#frequecy-set').removeAttr('required');
                    $('#create-event').find('#frequency-field').addClass('hidden');
                }
            });
            $('#create-event').find('.initiate-map').on('click', (e) => {
                $('#create-event').find('#contain-maps').removeClass('hidden');
                $('#create-event').find('.initiate-map').addClass('hidden');
                mapsGraph();
            });
            $('#create-event').find('.destroy-map').on('click', (e) => {
                $('#create-event').find('#contain-maps').addClass('hidden');
                $('#create-event').find('.initiate-map').removeClass('hidden');
            });
            syncFrequencyOptions('#create-event form');
        })

        var events_data = $('#calendar').data('event');
        fill_calendar(false, events_data);

        $('#add-notification').click(function (e) {
            var $row = $(`
                <div class="row ml-2 mt-1">
                    <div class="col-md-4"><input type="text"  value="Email"  style="margin: 8px;" disabled class="form-control"></div>
                    <div class="col-md-4"><input type="number" name="duration[]" placeholder="Duration..."  style="margin: 8px;" id="" class="form-control "></div>
                    <div class="col-md-4 d-flex">
                        <select name="rate[]" id=""  style="width:90%;margin: 8px;" class="form-control rate">
                            <option value="Minutes">Minutes</option>
                            <option value="Hours">Hours</option>
                            <option value="Days">Days</option>
                            <option value="Weeks">Weeks</option>
                            <option value="Months">Months</option>
                            <option value="Years">Years</option>
                        </select>
                        <button class="btn btn-sm btn-default" id="delete-row" style="margin: 0px;" ><i class="mdi mdi-do-not-disturb"></i></button>            
                    </div>
                </div>
            `);
            $('.notification').append($row);
        });

        $('#create-event').on('click', '#delete-row', function (e) {
            $(this).closest('.row').remove();
        });
    });

    var fill_calendar = function (personnel_selected, data, list_view) {
        var calendar = $('#calendar').fullCalendar({
            header: {
                left: 'prev,today',
                center: 'title',
                right: 'next'
            },
            displayEventTime: true,
            eventRender: function (event, element, view) {
                if (event.allDay === 'true') {
                    event.allDay = true;
                } else {
                    event.allDay = false;
                }
                return true;
            },
            events: data,
            eventClick: function (event) {
                var modal_class = '#event-modal';
                $(modal_class).data('events', event.id);
                $(modal_class).modal('show');
            },
            gotoDate: '2018-12-12',
        });
    }
</script>
@endsection
