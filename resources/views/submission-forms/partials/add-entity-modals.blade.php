{{-- Modals for adding new entities (rendered once, outside main form to avoid nested forms) --}}

<!-- Add Sample Point Modal -->
<div class="modal fade" id="addSamplePointModal" tabindex="-1" role="dialog" aria-labelledby="addSamplePointModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSamplePointModalLabel">Add New Sample Point</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addSamplePointForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="samplePointName">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="samplePointName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="samplePointUnit">Client Unit <span class="text-danger">*</span></label>
                        <select class="form-control" id="samplePointUnit" name="crm_company_unit_id" required>
                            <option value="">Select a client unit...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="samplePointGps">GPS Coordinates</label>
                        <input type="text" class="form-control" id="samplePointGps" name="gps" placeholder="e.g., -1.2921, 36.8219">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Sample Point</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Sample Condition Modal -->
<div class="modal fade" id="addSampleConditionModal" tabindex="-1" role="dialog" aria-labelledby="addSampleConditionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSampleConditionModalLabel">Add New Sample Condition</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addSampleConditionForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="sampleConditionName">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sampleConditionName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="sampleConditionType">Sample Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="sampleConditionType" name="sample_type_id" required>
                            <option value="">Select a sample type...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sampleConditionShortName">Short Name</label>
                        <input type="text" class="form-control" id="sampleConditionShortName" name="short_name">
                    </div>
                    <div class="form-group">
                        <label for="sampleConditionReportingTime">Reporting Time (days)</label>
                        <input type="number" class="form-control" id="sampleConditionReportingTime" name="reporting_time" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Sample Condition</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Client Modal -->
<div class="modal fade" id="addClientModal" tabindex="-1" role="dialog" aria-labelledby="addClientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addClientModalLabel">Add New Client</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addClientForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="clientName">Company Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="clientName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="clientCode">Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="clientCode" name="code" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="clientEmail">Email</label>
                                <input type="email" class="form-control" id="clientEmail" name="email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="clientTelephone">Telephone</label>
                                <input type="text" class="form-control" id="clientTelephone" name="telephone1">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="clientPostalAddress">Postal Address</label>
                        <textarea class="form-control" id="clientPostalAddress" name="postal_address" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="clientPhysicalAddress">Physical Address</label>
                        <textarea class="form-control" id="clientPhysicalAddress" name="physical_address" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Client Unit Modal -->
<div class="modal fade" id="addClientUnitModal" tabindex="-1" role="dialog" aria-labelledby="addClientUnitModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addClientUnitModalLabel">Add New Client Unit</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addClientUnitForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="clientUnitName">Unit Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="clientUnitName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="clientUnitClient">Client <span class="text-danger">*</span></label>
                        <select class="form-control" id="clientUnitClient" name="crm_customer_id" required>
                            <option value="">Select a client...</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Client Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Client Contact Modal -->
<div class="modal fade" id="addClientContactModal" tabindex="-1" role="dialog" aria-labelledby="addClientContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addClientContactModalLabel">Add New Client Contact</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addClientContactForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="contactFirstName">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="contactFirstName" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="contactMiddleName">Middle Name</label>
                                <input type="text" class="form-control" id="contactMiddleName" name="middle_name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="contactLastName">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="contactLastName" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contactEmail">Email</label>
                                <input type="email" class="form-control" id="contactEmail" name="email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contactTelephone">Telephone</label>
                                <input type="text" class="form-control" id="contactTelephone" name="telephone">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contactMobile">Mobile</label>
                                <input type="text" class="form-control" id="contactMobile" name="mobile">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contactJobOccupation">Job Occupation</label>
                                <input type="text" class="form-control" id="contactJobOccupation" name="job_occupation">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="contactClient">Client <span class="text-danger">*</span></label>
                        <select class="form-control" id="contactClient" name="crm_customer_id" required>
                            <option value="">Select a client...</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Client Contact</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Notification function to replace toastr
function showNotification(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="mdi ${iconClass}"></i> ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    // Remove existing notifications
    $('.alert[style*="position: fixed"]').remove();
    
    // Add new notification
    $('body').append(alertHtml);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        $('.alert[style*="position: fixed"]').fadeOut();
    }, 5000);
}

$(document).ready(function() {
    // Prevent duplicate event handlers
    if (window.customElementModalsInitialized) {
        return;
    }
    window.customElementModalsInitialized = true;

    // Load clients for dependent dropdowns
    function loadClients() {
        $.get('/api/clients', function(data) {
            $('#clientUnitClient, #contactClient').empty().append('<option value="">Select a client...</option>');
            $.each(data, function(index, client) {
                $('#clientUnitClient, #contactClient').append('<option value="' + client.id + '">' + client.name + '</option>');
            });
        });
    }

    // Load sample types for sample condition
    function loadSampleTypes() {
        $.get('/api/sample-types', function(data) {
            $('#sampleConditionType').empty().append('<option value="">Select a sample type...</option>');
            $.each(data, function(index, type) {
                $('#sampleConditionType').append('<option value="' + type.id + '">' + type.name + '</option>');
            });
        });
    }

    // Load client units for sample point from existing page data
    function loadClientUnits() {
        // Get client units from existing client_unit_select on the page
        var clientUnitSelect = $('select[data-element-type="client_unit_select"]');
        if (clientUnitSelect.length > 0) {
            $('#samplePointUnit').empty().append('<option value="">Select a client unit...</option>');
            clientUnitSelect.find('option').each(function() {
                var value = $(this).val();
                var text = $(this).text();
                if (value && value !== '') {
                    $('#samplePointUnit').append('<option value="' + value + '">' + text + '</option>');
                }
            });
        } else {
            // Fallback to API if no client_unit_select found on page
            $.get('/api/client-units', function(data) {
                $('#samplePointUnit').empty().append('<option value="">Select a client unit...</option>');
                $.each(data, function(index, unit) {
                    $('#samplePointUnit').append('<option value="' + unit.id + '">' + unit.name + '</option>');
                });
            });
        }
    }

    // Initialize dropdowns
    loadClients();
    loadSampleTypes();
    loadClientUnits();

    // Refresh sample point modal client units when modal is shown
    $('#addSamplePointModal').on('show.bs.modal', function() {
        loadClientUnits();
    });

    // Remove existing event handlers to prevent duplicates
    $('#addSamplePointForm').off('submit');
    $('#addSampleConditionForm').off('submit');
    $('#addClientForm').off('submit');
    $('#addClientUnitForm').off('submit');
    $('#addClientContactForm').off('submit');

    // Handle form submissions
    $('#addSamplePointForm').on('submit', function(e) {
        e.preventDefault();
        
        // Prevent double submission
        if ($(this).data('submitting')) {
            return false;
        }
        $(this).data('submitting', true);
        
        $.ajax({
            url: '/api/sample-points',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // Add new option to sample point select
                var newOption = '<option value="' + response.id + '">' + response.name + '</option>';
                $('select[data-element-type="sample_point_select"]').append(newOption);
                $('#addSamplePointModal').modal('hide');
                $('#addSamplePointForm')[0].reset();
                showNotification('success', 'Sample point added successfully!');
            },
            error: function(xhr) {
                showNotification('error', 'Error adding sample point: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'));
            },
            complete: function() {
                // Reset submission flag
                $('#addSamplePointForm').data('submitting', false);
            }
        });
    });

    $('#addSampleConditionForm').on('submit', function(e) {
        e.preventDefault();
        
        // Prevent double submission
        if ($(this).data('submitting')) {
            return false;
        }
        $(this).data('submitting', true);
        
        $.ajax({
            url: '/api/sample-conditions',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // Add new option to sample condition select
                var newOption = '<option value="' + response.id + '">' + response.name + '</option>';
                $('select[data-element-type="sample_condition_select"]').append(newOption);
                $('#addSampleConditionModal').modal('hide');
                $('#addSampleConditionForm')[0].reset();
                showNotification('success', 'Sample condition added successfully!');
            },
            error: function(xhr) {
                showNotification('error', 'Error adding sample condition: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'));
            },
            complete: function() {
                // Reset submission flag
                $('#addSampleConditionForm').data('submitting', false);
            }
        });
    });

    $('#addClientForm').on('submit', function(e) {
        e.preventDefault();
        
        // Prevent double submission
        if ($(this).data('submitting')) {
            return false;
        }
        $(this).data('submitting', true);
        
        $.ajax({
            url: '/api/clients',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // Add new option to client select
                var newOption = '<option value="' + response.id + '">' + response.name + '</option>';
                $('select[data-element-type="client_select"]').append(newOption);
                $('#addClientModal').modal('hide');
                $('#addClientForm')[0].reset();
                showNotification('success', 'Client added successfully!');
                // Reload dependent dropdowns
                loadClientUnits();
            },
            error: function(xhr) {
                showNotification('error', 'Error adding client: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'));
            },
            complete: function() {
                // Reset submission flag
                $('#addClientForm').data('submitting', false);
            }
        });
    });

    $('#addClientUnitForm').on('submit', function(e) {
        e.preventDefault();
        
        // Prevent double submission
        if ($(this).data('submitting')) {
            return false;
        }
        $(this).data('submitting', true);
        
        $.ajax({
            url: '/api/client-units',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // Add new option to client unit select
                var newOption = '<option value="' + response.id + '">' + response.name + '</option>';
                $('select[data-element-type="client_unit_select"]').append(newOption);
                $('#addClientUnitModal').modal('hide');
                $('#addClientUnitForm')[0].reset();
                showNotification('success', 'Client unit added successfully!');
                // Reload sample points and refresh sample point modal dropdown
                loadClientUnits();
                // Also refresh the sample point modal's client unit dropdown
                var clientUnitSelect = $('select[data-element-type="client_unit_select"]');
                if (clientUnitSelect.length > 0) {
                    $('#samplePointUnit').empty().append('<option value="">Select a client unit...</option>');
                    clientUnitSelect.find('option').each(function() {
                        var value = $(this).val();
                        var text = $(this).text();
                        if (value && value !== '') {
                            $('#samplePointUnit').append('<option value="' + value + '">' + text + '</option>');
                        }
                    });
                }
            },
            error: function(xhr) {
                showNotification('error', 'Error adding client unit: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'));
            },
            complete: function() {
                // Reset submission flag
                $('#addClientUnitForm').data('submitting', false);
            }
        });
    });

    $('#addClientContactForm').on('submit', function(e) {
        e.preventDefault();
        
        // Prevent double submission
        if ($(this).data('submitting')) {
            return false;
        }
        $(this).data('submitting', true);
        
        $.ajax({
            url: '/api/client-contacts',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                // Add new option to client contact select
                var newOption = '<option value="' + response.id + '">' + response.first_name + ' ' + response.last_name + '</option>';
                $('select[data-element-type="client_contact_select"]').append(newOption);
                $('#addClientContactModal').modal('hide');
                $('#addClientContactForm')[0].reset();
                showNotification('success', 'Client contact added successfully!');
            },
            error: function(xhr) {
                showNotification('error', 'Error adding client contact: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'));
            },
            complete: function() {
                // Reset submission flag
                $('#addClientContactForm').data('submitting', false);
            }
        });
    });
});
</script>
@endpush
