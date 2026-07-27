<div class="modal fade" id="view-description" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                
            </div>
            <div class="modal-footer">
                <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="view-logistics" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
               
            </div>
            <div class="modal-footer">
                <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="create-event" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="{{route('full-calendar-create')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title"><i class="mdi mdi-plus"></i> Create Event</h4>
                </div>
                <div class="modal-body ">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label"><i class="mdi mdi-inbox-full mr-3"></i> Title <span
                                        class="text-danger">*</span></label>
                                <textarea name="title" id="" class="form-control" required></textarea>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label"><i class="mdi mdi-alarm-check"></i> Start Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="" class="form-control" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label"><i class="mdi mdi-clock-start"></i> Start Time</label>
                            <input type="time" name="start_time" value="00:00" class="form-control ">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="control-label"><i class="mdi mdi-alarm-check mr-3"></i> End Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="" class="form-control" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="control-label"><i class="mdi mdi-clock-start"></i> End Time</label>
                            <input type="time" name="end_time" value="00:00" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label"><i
                                        class="mdi mdi-account-multiple-outline mr-3"></i> Client <span
                                        class="text-danger">*</span></label>
                                <select name="client_id" id="" class="form-control select2 no-select2"
                                    aria-selected="true" required>
                                    <option value="">Choose Client</option>
                                    @foreach($clients as $client)
                                        <option value="{{$client->id}}"
                                                data-contract-from="{{ $client->contract_valid_from ? \Carbon\Carbon::parse($client->contract_valid_from)->format('Y-m-d') : '' }}"
                                                data-contract-to="{{ $client->contract_valid_to ? \Carbon\Carbon::parse($client->contract_valid_to)->format('Y-m-d') : '' }}">
                                            {{$client->name}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"><i class="mdi mdi-calendar-range"></i> Contract Validity From</label>
                                <input type="date" name="contract_valid_from" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"><i class="mdi mdi-calendar-range"></i> Contract Validity To</label>
                                <input type="date" name="contract_valid_to" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"><i class="mdi mdi-account-supervisor mr-3"></i> Responsible
                                    Personnel <span class="text-danger">*</span></label>
                                <select name="responsible_id[]" class="form-control ml-3 select2 no-select2"
                                    aria-placeholder="Select Personnel..." required aria-selected="true" multiple>
                                    <option value="">Choose Responsible Personnel</option>
                                    @foreach($users as $user)
                                        <option value="{{$user->id}}">{{$user->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label"><i class="mdi mdi-calendar-heart mr-3"></i> Event
                                    Status <span class="text-danger">*</span></label>
                                <select name="event_status" class="form-control ml-3 select2 no-select2" required>
                                    <option value=""></option>
                                    <option value="Upcoming">Upcoming</option>
                                    <option value="In-progress">In-progress</option>
                                    <option value="Complete">Complete</option>
                                    <option value="Delayed">Delayed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label"><i class="mdi mdi-paperclip mr-3"></i>
                                    Attachment</label>
                                <input type="file" name="attachment" id="" class="form-control ml-3">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label"
                                    style="display: flex; justify-content: space-between; align-items: center;">
                                    <span> <i class="mdi mdi-map-marker mr-3"></i> Location</span>
                                    <small class="btn btn-sm initiate-map" style="font-size:10px"><u><b>Choose From
                                                Map?</b></u></small>
                                </label>
                                <input type="text" name="location" style="width:95%" class="form-control ml-3"
                                    placeholder="Location...">
                                <input type="hidden" name="latitude" value="" id="latitude">
                                <input type="hidden" name="longitude" value="" id="longitude">

                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" class="form-control" name="is_routine"
                                    id="is-routine" />
                                <label class="form-check-label"> Event Frequency ? </label>
                            </div>
                        </div>
                        <div class="form-group col-md-4 hidden" id="frequency-field">
                            <label class="control-label">Frequency <span class="text-danger">*</span></label>
                            <select name="frequency" id="frequecy-set" class="form-control select2 no-select2">
                                <option value="" disabled>Select Frequency</option>
                                <option value="1">Daily</option>
                                <option value="7">Weekly</option>
                                <option value="30">Monthly</option>
                                <option value="90">Quarterly</option>
                                <option value="180">Semi Annually</option>
                                <option value="365">Annually</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mt-4 pb-4">
                                <input class="form-check-input" type="checkbox" class="form-control" name="notification"
                                    id="my-task" />
                                <label class="form-check-label">Send Instant Notification ? </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mt-4 pb-4">
                                <input class="form-check-input" type="checkbox" checked class="form-control"
                                    name="notify_client" id="my-task" />
                                <label class="form-check-label"> Notify Client ? </label>
                            </div>
                        </div>
                        <div class="form-group col-md-12">
                            <label class="control-label"><i class="mdi mdi-bell-ring mr-3"></i> Notification</label>
                            <div class="notification">
                                <div class="row ml-3">
                                    <div class="col-sm-4">
                                        <input type="text" value="Email" disabled class="form-control">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="number" name="duration[]" placeholder="Duration..." id="" class="form-control ">
                                    </div>
                                    <div class="col-sm-4">
                                        <select name="rate[]" id="" class="form-control select2 no-select2">
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
                            <span class="btn btn-default btn-sm mt-2" id="add-notification"
                                style="border-radius: 20px;"><u>Add Notification</u></span>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="" class="control-label"><i class="mdi mdi-view-headline mr-3"></i>
                                    Description <span class="text-danger">*</span></label>
                                <textarea class="form-control ml-3" rows="5" name="description"
                                    placeholder="Description..." required /></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="" class="control-label"><i class="mdi mdi-view-headline mr-3"></i> Logistics
                                    Detail</label>
                                <textarea name="logistics" id="" class="form-control editor"></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div id="contain-maps" class="hidden border-top pt-3 mt-3">
                                <span class="btn btn-sm btn-outline-danger float-right destroy-map mb-3">Close
                                    Map</span>
                                <div id="maps-sect" class="" style="width: 100%;height: 70vh">

                                </div>

                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-primary btn-sm mdi mdi-content-save">Save</button>
                    <span class="btn btn-outline-danger btn-sm float-right" style="float: right !important;"
                        data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="event-modal" data-clients="{{json_encode($clients)}}"
    data-responsible_personnel="{{json_encode($users)}}" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="card tab-card">
                <div class="card-header tab-card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="event-edit-tab" role="tablist">
                        <li class="nav-item">
                            <a href="#EventDetails-tab" class="nav-link" id="event-details" data-toggle="tab" role="tab"
                                aria-controls="EventDetails-tab" aria-selected="true"><i
                                    class="mdi mdi-calendar-text"></i> Event Details</a>
                        </li>
                        <li class="nav-item">
                            <a href="#EventHistory-tab" class="nav-link" id="event-history" data-toggle="tab" role="tab"
                                aria-controls="EventHistory-tab" aria-selected="true"><i class="mdi mdi-history"></i>
                                Event History</a>
                        </li>
                        <li class="nav-item d-none" id="event-occurrences-li">
                            <a href="#EventOccurrences-tab" class="nav-link" id="event-occurrences" data-toggle="tab" role="tab"
                                aria-controls="EventOccurrences-tab" aria-selected="true"><i class="mdi mdi-repeat"></i>
                                Occurrences (<span id="occurrences-count">0</span>)</a>
                        </li>
                    </ul>
                </div>
                <div class="tab-content" id="EventDetails">
                </div>

            </div>

        </div>
    </div>
</div>
<div id="delete-event" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('delete-events')}}" method="post">
                @csrf
                <div class="modal-body">

                    <div id="delete-params"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-primary btn-sm mdi mdi-content-save">Delete</button>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Cancel</button>
                </div>


            </form>
        </div>
    </div>
</div>

<script>
    window.updateFrequencyOptions = function(container) {
        var startDateVal = container.find('input[name="start_date"]').val();
        var endDateVal = container.find('input[name="end_date"]').val();
        var freqSelect = container.find('select[name="frequency"]');
        if (!freqSelect.length) return;

        if (startDateVal && endDateVal) {
            var start = new Date(startDateVal);
            var end = new Date(endDateVal);
            var diffTime = end - start;
            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            freqSelect.find('option').each(function() {
                var val = $(this).val();
                if (!val) return;
                var valInt = parseInt(val);

                if (diffDays < 7) {
                    if (valInt !== 1) {
                        $(this).attr('disabled', 'disabled').hide();
                    } else {
                        $(this).removeAttr('disabled').show();
                    }
                } else if (diffDays < 30) {
                    if (valInt !== 1 && valInt !== 7) {
                        $(this).attr('disabled', 'disabled').hide();
                    } else {
                        $(this).removeAttr('disabled').show();
                    }
                } else {
                    $(this).removeAttr('disabled').show();
                }
            });

            var selectedOpt = freqSelect.find('option:selected');
            if (selectedOpt.attr('disabled')) {
                freqSelect.val('');
                if (freqSelect.hasClass('select2-hidden-accessible') || freqSelect.data('select2')) {
                    freqSelect.trigger('change.select2');
                } else {
                    freqSelect.trigger('change');
                }
            } else {
                if (freqSelect.hasClass('select2-hidden-accessible') || freqSelect.data('select2')) {
                    freqSelect.trigger('change.select2');
                }
            }
        }
    };

    $(document).ready(function() {
        $(document).on('change', 'select[name="client_id"]', function() {
            var selectedOption = $(this).find('option:selected');
            var from = selectedOption.data('contract-from') || '';
            var to = selectedOption.data('contract-to') || '';
            var container = $(this).closest('form');
            if (!container.length) {
                container = $(this).closest('.modal-body');
            }
            container.find('input[name="contract_valid_from"]').val(from);
            container.find('input[name="contract_valid_to"]').val(to);
        });

        $(document).on('change', 'input[name="start_date"], input[name="end_date"]', function() {
            var container = $(this).closest('form');
            if (!container.length) {
                container = $(this).closest('.modal-body');
            }
            window.updateFrequencyOptions(container);
        });
    });
</script>

