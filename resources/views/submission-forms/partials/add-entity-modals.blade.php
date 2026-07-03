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
                    <div class="alert alert-primary p-2">
                        <i class="mdi mdi-plus" style="font-size:30px"></i>
                        <span class="p-2">Add Sample Condition by providing the information below</span>
                    </div>
                    <div class="form-group">
                        <label for="sampleConditionName" class="control-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sampleConditionName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="sampleConditionActive" class="control-label">
                            <input type="checkbox" name="active" value="1" checked id="sampleConditionActive"> Is Active ?
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="mdi mdi-content-save"></i> Save
                    </button>
                    <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
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
                <h5 class="modal-title" id="addClientModalLabel">
                    <i class="mdi mdi-plus"></i> Add New Customer
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addClientForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="clientName" name="name" placeholder="Customer name..." required>
                                <span class="text-danger" id="error-name"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="clientEmail" name="email" placeholder="Email address..." required>
                                <span class="text-danger" id="error-email"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Phone 1 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="clientTelephone1" name="telephone1" placeholder="Primary phone..." required>
                                <span class="text-danger" id="error-telephone1"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Phone 2</label>
                                <input type="text" class="form-control" id="clientTelephone2" name="telephone2" placeholder="Secondary phone...">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold"><i class="mdi mdi-earth text-primary"></i> Country <span class="text-danger">*</span></label>
                                <div class="searchable-dropdown-wrapper dropdown-wrapper-country">
                                    <div class="single-select-container" onclick="toggleClientCountryDropdown()">
                                        <input 
                                            type="text" 
                                            id="countrySearch"
                                            placeholder="Search countries..."
                                            class="form-control searchable-input-single"
                                            autocomplete="off"
                                            onclick="toggleClientCountryDropdown()"
                                        >
                                        <i class="mdi mdi-chevron-down dropdown-arrow"></i>
                                        <input type="hidden" id="clientCountryId" name="country_id" required>
                    </div>

                                    <div class="dropdown-list dropdown-list-country" id="countryDropdown" style="display: none;">
                                        <div class="options-list" id="countryOptions">
                                            <!-- Countries will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                                <span class="text-danger" id="error-country_id"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold"><i class="mdi mdi-cog text-info"></i> Account Settings <span class="text-danger">*</span></label>
                                <div class="searchable-dropdown-wrapper dropdown-wrapper-account">
                                    <div class="single-select-container" onclick="toggleClientAccountDropdown()">
                                        <input 
                                            type="text" 
                                            id="accountSearch"
                                            placeholder="Search account settings..."
                                            class="form-control searchable-input-single"
                                            autocomplete="off"
                                            onclick="toggleClientAccountDropdown()"
                                        >
                                        <i class="mdi mdi-chevron-down dropdown-arrow"></i>
                                        <input type="hidden" id="clientAccountStatus" name="account_status" required>
                                    </div>

                                    <div class="dropdown-list dropdown-list-account" id="accountDropdown" style="display: none;">
                                        <div class="options-list" id="accountOptions">
                                            <!-- Account settings will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                                <span class="text-danger" id="error-account_status"></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Postal Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="clientPostalAddress" name="postal_address" rows="3" placeholder="Postal address..." required></textarea>
                        <span class="text-danger" id="error-postal_address"></span>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Physical Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="clientPhysicalAddress" name="physical_address" placeholder="Physical address..." required>
                        <span class="text-danger" id="error-physical_address"></span>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Website</label>
                                <input type="text" class="form-control" id="clientWebsite" name="website" placeholder="Website URL...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Fax</label>
                                <input type="text" class="form-control" id="clientFax" name="fax" placeholder="Fax number...">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">VAT Number</label>
                                <input type="text" class="form-control" id="clientVatNo" name="vat_no" placeholder="VAT number...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Credit Days</label>
                                <input type="number" class="form-control" id="clientCreditDays" name="credit_days" placeholder="Credit days...">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold"><i class="mdi mdi-link-variant text-success"></i> Dynamics Customer Mapping <small class="text-muted">(Optional)</small></label>
                                <div class="searchable-dropdown-wrapper dropdown-wrapper-zoho">
                                    <div class="single-select-container" onclick="toggleClientZohoDropdown()">
                                        <input 
                                            type="text" 
                                            id="zohoCustomerSearch"
                                            placeholder="Search Dynamics customers..."
                                            class="form-control searchable-input-single"
                                            autocomplete="off"
                                            onclick="toggleClientZohoDropdown()"
                                        >
                                        <i class="mdi mdi-chevron-down dropdown-arrow"></i>
                                        <button 
                                            type="button"
                                            onclick="clearClientZohoCustomer(event)"
                                            class="clear-selection-btn"
                                            id="clearZohoBtn"
                                            style="display: none;"
                                            title="Clear selection">
                                            <i class="mdi mdi-close-circle"></i>
                                        </button>
                                        <input type="hidden" id="clientZohoCustomerId" name="zoho_customer_id">
                                    </div>

                                    <div class="dropdown-list dropdown-list-zoho" id="zohoDropdown" style="display: none;">
                                        <div class="options-list" id="zohoOptions">
                                            <!-- Zoho customers will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    <i class="mdi mdi-information-outline"></i> Link this customer to a Dynamics 365 customer for billing integration.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="clientActive" name="active" checked>
                                <label class="form-check-label" for="clientActive">
                                    Is Active?
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="clientLposRequired" name="lpos_required">
                                <label class="form-check-label" for="clientLposRequired">
                                    LPO Required?
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitClientBtn">
                        <i class="mdi mdi-content-save"></i> Create Customer
                    </button>
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

<style>
    /* Make modal body scrollable */
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    /* Custom scrollbar for better UX */
    .modal-body::-webkit-scrollbar {
        width: 8px;
    }
    
    .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .modal-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Single-Select Searchable Dropdown Styling */
    .searchable-input-single {
        border: none;
        outline: none;
        box-shadow: none !important;
        padding: 4px 0;
        width: 100%;
    }
    
    .searchable-input-single:focus {
        border: none !important;
        box-shadow: none !important;
    }
    
    .single-select-container {
        position: relative;
        min-height: 45px;
        border: 1px solid #ced4da;
        border-radius: 12px;
        padding: 8px 40px 8px 12px;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
    }
    
    .single-select-container:hover {
        border-color: var(--color-primary);
        box-shadow: 0 2px 8px var(--color-primary-soft-10);
    }
    
    .single-select-container:has(.searchable-input-single:focus) {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 0.2rem var(--color-primary-focus);
    }
    
    .options-list {
        padding: 8px;
        max-height: 300px;
        overflow-y: auto;
        
        /* Hide scrollbar but keep scrolling functionality */
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE and Edge */
    }
    
    /* Hide scrollbar for Chrome, Safari and Opera */
    .options-list::-webkit-scrollbar {
        display: none;
    }
    
    .option-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
    }
    
    .option-item:hover {
        background: #f8f9fa;
    }
    
    .option-item.selected {
        background: var(--color-primary-soft);
        font-weight: 500;
    }
    
    .option-item i {
        font-size: 18px;
    }
    
    .dropdown-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ced4da;
        border-radius: 12px;
        margin-top: 4px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 9999 !important;
        max-height: 300px;
        overflow-y: auto;
        
        /* Hide scrollbar but keep scrolling functionality */
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE and Edge */
    }
    
    /* Hide scrollbar for Chrome, Safari and Opera */
    .dropdown-list::-webkit-scrollbar {
        display: none;
    }
    
    .searchable-dropdown-wrapper {
        position: relative;
        z-index: 1;
    }
    
    /* Increase z-index when dropdown is open */
    .searchable-dropdown-wrapper.dropdown-open {
        z-index: 9998 !important;
        position: relative;
    }
    
    /* Ensure dropdowns in modal appear above modal content and other form elements */
    .modal-content .dropdown-list {
        z-index: 9999 !important;
    }
    
    /* Prevent form groups from affecting dropdown positioning */
    #addClientModal .form-group {
        position: relative;
        z-index: 1;
        overflow: visible !important;
    }
    
    /* Elevate form group containing open dropdown (class added via JS) */
    #addClientModal .form-group.has-open-dropdown {
        z-index: 9998 !important;
        position: relative;
    }
    
    /* Prevent rows from increasing height when dropdown opens */
    #addClientModal .row {
        overflow: visible !important;
    }
    
    /* Prevent col divs from creating stacking context */
    #addClientModal .col-md-6,
    #addClientModal .col-md-12 {
        position: relative;
        z-index: auto;
    }
    
    /* Ensure modal body doesn't create new stacking context */
    #addClientModal .modal-body {
        overflow-x: hidden;
        overflow-y: auto;
        position: relative;
        z-index: auto;
    }
    
    /* Ensure form doesn't create stacking context */
    #addClientForm {
        position: relative;
        z-index: auto;
    }
    
    /* Clear selection button */
    .clear-selection-btn {
        position: absolute;
        right: 35px;
        top: 50%;
        transform: translateY(-50%);
        padding: 0 !important;
        color: #dc3545;
        z-index: 5;
        background: none !important;
        border: none !important;
        line-height: 1;
    }
    
    .clear-selection-btn:hover {
        color: #a71d2a !important;
    }
    
    .clear-selection-btn:focus {
        outline: none;
        box-shadow: none !important;
    }
    
    .no-results {
        padding: 20px;
        text-align: center;
        color: #6c757d;
    }
    
    .no-results i {
        font-size: 24px;
        display: block;
        margin-bottom: 8px;
    }
    
    .dropdown-arrow {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        transition: transform 0.3s ease;
        pointer-events: none;
        font-size: 20px;
        color: #6c757d;
    }
    
    .dropdown-arrow.rotated {
        transform: translateY(-50%) rotate(180deg);
    }
</style>

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

    // Load clients for dependent dropdowns (handles paginated response)
    function loadClients() {
        $.get('/api/clients?per_page=1000', function(response) {
            // Handle both old format (array) and new format (paginated)
            const clients = response.data || response;
            
            $('#clientUnitClient, #contactClient').empty().append('<option value="">Select a client...</option>');
            $.each(clients, function(index, client) {
                $('#clientUnitClient, #contactClient').append('<option value="' + client.id + '">' + client.name + '</option>');
            });
            
            // If there are more clients, log a notice (could upgrade to Select2 AJAX in future)
            if (response.pagination && response.pagination.has_more) {
                console.log('Note: Showing first ' + clients.length + ' of ' + response.pagination.total + ' clients. Consider using search to find specific clients.');
            }
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
    loadClientUnits();

    // Refresh sample point modal client units when modal is shown
    $('#addSamplePointModal').on('show.bs.modal', function() {
        loadClientUnits();
    });
    
    // Auto-select client in Client Unit modal based on main form selection
    $('#addClientUnitModal').on('show.bs.modal', function() {
        // Get the currently selected client from the main form
        var $clientSelect = $('select[data-element-type="client_select"]');
        var selectedClientId = $clientSelect.val();
        
        console.log('Client Unit modal opened, auto-selecting client:', selectedClientId);
        
        // Pre-select the client in the modal
        if (selectedClientId) {
            var $clientUnitClient = $('#clientUnitClient');
            
            // Check if the option already exists in the dropdown
            var optionExists = $clientUnitClient.find('option[value="' + selectedClientId + '"]').length > 0;
            
            if (optionExists) {
                // Option exists, set the value immediately
                $clientUnitClient.val(selectedClientId);
                console.log('Auto-selected client:', selectedClientId);
            } else {
                // Option doesn't exist, fetch it and add it
                console.log('Client option not found, fetching client details...');
                $.get('/api/clients/' + selectedClientId, function(client) {
                    // Add the option if it doesn't exist
                    if ($clientUnitClient.find('option[value="' + client.id + '"]').length === 0) {
                        $clientUnitClient.append('<option value="' + client.id + '">' + client.name + '</option>');
                    }
                    $clientUnitClient.val(client.id);
                    console.log('Added and auto-selected client:', client.name);
                }).fail(function() {
                    console.warn('Could not load client details for ID:', selectedClientId);
                });
            }
        }
    });
    
    // Auto-select client in Client Contact modal based on main form selection
    $('#addClientContactModal').on('show.bs.modal', function() {
        // Get the currently selected client from the main form
        var $clientSelect = $('select[data-element-type="client_select"]');
        var selectedClientId = $clientSelect.val();
        
        console.log('Client Contact modal opened, auto-selecting client:', selectedClientId);
        
        // Pre-select the client in the modal
        if (selectedClientId) {
            $('#contactClient').val(selectedClientId);
        }
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
                console.log('Sample point created successfully:', response);
                
                // Show success notification first
                showNotification('success', 'Sample point added successfully!');
                
                // Close modal and clean up
                $('#addSamplePointModal').modal('hide');
                
                // Force remove modal backdrop and restore page interactivity
                setTimeout(function() {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css('overflow', '');
                    $('body').css('padding-right', '');
                }, 300);
                
                // Reset form
                $('#addSamplePointForm')[0].reset();
                
                // Add new option to sample point select (non-blocking)
                setTimeout(function() {
                var newOption = '<option value="' + response.id + '">' + response.name + '</option>';
                var $select = $('select[data-element-type="sample_point_select"]');
                    
                    if ($select.length > 0) {
                $select.append(newOption);
                
                // Set the value and trigger change events
                $select.val(response.id);
                $select.trigger('change');
                        
                // Trigger Select2 events if Select2 is initialized
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.trigger('select2:select');
                }
                
                // Update form validation and progress
                if (typeof FormFill !== 'undefined') {
                    FormFill.updateProgress();
                    FormFill.updateSubmitButtonState();
                }
                    }
                }, 500);
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
        
        // Build form data with proper checkbox handling
        const formData = {
            name: $('#sampleConditionName').val(),
            active: $('#sampleConditionActive').is(':checked') ? 1 : 0
        };
        
        console.log('Submitting sample condition:', formData);
        
        $.ajax({
            url: '/api/sample-conditions',
            method: 'POST',
            data: formData,
            success: function(response) {
                console.log('Sample condition created successfully:', response);
                
                // Show success notification first
                showNotification('success', 'Sample condition added successfully!');
                
                // Close modal and clean up
                $('#addSampleConditionModal').modal('hide');
                
                // Force remove modal backdrop and restore page interactivity
                setTimeout(function() {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css('overflow', '');
                    $('body').css('padding-right', '');
                }, 300);
                
                // Reset form
                $('#addSampleConditionForm')[0].reset();
                
                // Add new option to sample condition select (non-blocking)
                setTimeout(function() {
                var newOption = '<option value="' + response.id + '">' + response.name + '</option>';
                var $select = $('select[data-element-type="sample_condition_select"]');
                    
                    if ($select.length > 0) {
                $select.append(newOption);
                
                // Set the value and trigger change events
                $select.val(response.id);
                $select.trigger('change');
                        
                // Trigger Select2 events if Select2 is initialized
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.trigger('select2:select');
                }
                
                // Update form validation and progress
                if (typeof FormFill !== 'undefined') {
                    FormFill.updateProgress();
                    FormFill.updateSubmitButtonState();
                }
                    }
                }, 500);
            },
            error: function(xhr) {
                console.error('Sample condition submission error:', xhr);
                showNotification('error', 'Error adding sample condition: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'));
            },
            complete: function() {
                // Reset submission flag
                $('#addSampleConditionForm').data('submitting', false);
            }
        });
    });

    // Client modal data
    let clientModalData = {
        countries: [],
        accounts: [],
        zohoCustomers: [],
        selectedCountryId: null,
        selectedAccountId: null,
        selectedZohoId: null,
        filteredCountries: [],
        filteredAccounts: [],
        filteredZohoCustomers: [],
        zohoCurrentPage: 1,
        zohoLastPage: 1,
        zohoHasMore: false,
        zohoLoading: false,
        zohoSearchTerm: ''
    };

    // Load countries for client modal
    function loadCountriesForClient() {
        $.get('/api/countries', function(data) {
            console.log('Countries loaded:', data);
            if (data && Array.isArray(data)) {
                clientModalData.countries = data;
                clientModalData.filteredCountries = data;
                // Render options if dropdown should be visible
                if ($('#countryDropdown').is(':visible')) {
                    renderCountryOptions();
                }
            } else {
                console.error('Invalid countries data format:', data);
                clientModalData.countries = [];
                clientModalData.filteredCountries = [];
            }
        }).fail(function(xhr, status, error) {
            console.error('Error loading countries:', error, xhr.responseText);
            clientModalData.countries = [];
            clientModalData.filteredCountries = [];
        });
    }

    // Load account settings for client modal
    function loadAccountSettingsForClient() {
        $.get('/api/account-settings', function(data) {
            console.log('Account settings loaded:', data);
            if (data && Array.isArray(data)) {
                clientModalData.accounts = data;
                clientModalData.filteredAccounts = data;
                // Render options if dropdown should be visible
                if ($('#accountDropdown').is(':visible')) {
                    renderAccountOptions();
                }
            } else {
                console.error('Invalid account settings data format:', data);
                clientModalData.accounts = [];
                clientModalData.filteredAccounts = [];
            }
        }).fail(function(xhr, status, error) {
            console.error('Error loading account settings:', error, xhr.responseText);
            clientModalData.accounts = [];
            clientModalData.filteredAccounts = [];
        });
    }

    // Load Zoho customers for client modal with pagination
    function loadZohoCustomersForClient(page = 1, search = '', append = false) {
        if (clientModalData.zohoLoading) {
            return; // Prevent multiple simultaneous requests
        }
        
        clientModalData.zohoLoading = true;
        const params = {
            page: page,
            per_page: 50,
            search: search
        };
        
        $.get('/api/zoho-customers', params, function(response) {
            console.log('Zoho customers loaded:', response);
            
            if (response && response.data && Array.isArray(response.data)) {
                if (append) {
                    // Append to existing data (infinite scroll)
                    clientModalData.zohoCustomers = clientModalData.zohoCustomers.concat(response.data);
                } else {
                    // Replace data (new search or initial load)
                    clientModalData.zohoCustomers = response.data;
                }
                
                clientModalData.filteredZohoCustomers = clientModalData.zohoCustomers;
                clientModalData.zohoCurrentPage = response.current_page;
                clientModalData.zohoLastPage = response.last_page;
                clientModalData.zohoHasMore = response.has_more;
                clientModalData.zohoSearchTerm = search;
                
                // Render options if dropdown should be visible
                if ($('#zohoDropdown').is(':visible')) {
                    renderZohoOptions();
                }
            } else {
                console.error('Invalid zoho customers data format:', response);
                if (!append) {
                    clientModalData.zohoCustomers = [];
                    clientModalData.filteredZohoCustomers = [];
                }
            }
            
            clientModalData.zohoLoading = false;
        }).fail(function(xhr, status, error) {
            console.error('Error loading zoho customers:', error, xhr.responseText);
            if (!append) {
                clientModalData.zohoCustomers = [];
                clientModalData.filteredZohoCustomers = [];
            }
            clientModalData.zohoLoading = false;
        });
    }

    // Toggle country dropdown
    window.toggleClientCountryDropdown = function() {
        const dropdown = $('#countryDropdown');
        const wrapper = $('#countrySearch').closest('.searchable-dropdown-wrapper');
        const formGroup = wrapper.closest('.form-group');
        const isVisible = dropdown.is(':visible');
        
        // Close all other dropdowns and reset parent form groups
        $('.dropdown-list').not(dropdown).hide();
        $('.dropdown-arrow').removeClass('rotated');
        $('.searchable-dropdown-wrapper').removeClass('dropdown-open');
        $('.form-group').removeClass('has-open-dropdown');
        
        if (isVisible) {
            dropdown.hide();
            wrapper.removeClass('dropdown-open');
            formGroup.removeClass('has-open-dropdown');
            $('#countrySearch').closest('.single-select-container').find('.dropdown-arrow').removeClass('rotated');
        } else {
            dropdown.show();
            wrapper.addClass('dropdown-open');
            formGroup.addClass('has-open-dropdown');
            $('#countrySearch').closest('.single-select-container').find('.dropdown-arrow').addClass('rotated');
            renderCountryOptions();
        }
    };

    // Toggle account dropdown
    window.toggleClientAccountDropdown = function() {
        const dropdown = $('#accountDropdown');
        const wrapper = $('#accountSearch').closest('.searchable-dropdown-wrapper');
        const formGroup = wrapper.closest('.form-group');
        const isVisible = dropdown.is(':visible');
        
        // Close all other dropdowns and reset parent form groups
        $('.dropdown-list').not(dropdown).hide();
        $('.dropdown-arrow').removeClass('rotated');
        $('.searchable-dropdown-wrapper').removeClass('dropdown-open');
        $('.form-group').removeClass('has-open-dropdown');
        
        if (isVisible) {
            dropdown.hide();
            wrapper.removeClass('dropdown-open');
            formGroup.removeClass('has-open-dropdown');
            $('#accountSearch').closest('.single-select-container').find('.dropdown-arrow').removeClass('rotated');
        } else {
            dropdown.show();
            wrapper.addClass('dropdown-open');
            formGroup.addClass('has-open-dropdown');
            $('#accountSearch').closest('.single-select-container').find('.dropdown-arrow').addClass('rotated');
            renderAccountOptions();
        }
    };

    // Toggle Zoho dropdown
    window.toggleClientZohoDropdown = function() {
        const dropdown = $('#zohoDropdown');
        const wrapper = $('#zohoCustomerSearch').closest('.searchable-dropdown-wrapper');
        const formGroup = wrapper.closest('.form-group');
        const isVisible = dropdown.is(':visible');
        
        // Close all other dropdowns and reset parent form groups
        $('.dropdown-list').not(dropdown).hide();
        $('.dropdown-arrow').removeClass('rotated');
        $('.searchable-dropdown-wrapper').removeClass('dropdown-open');
        $('.form-group').removeClass('has-open-dropdown');
        
        if (isVisible) {
            dropdown.hide();
            wrapper.removeClass('dropdown-open');
            formGroup.removeClass('has-open-dropdown');
            $('#zohoCustomerSearch').closest('.single-select-container').find('.dropdown-arrow').removeClass('rotated');
        } else {
            dropdown.show();
            wrapper.addClass('dropdown-open');
            formGroup.addClass('has-open-dropdown');
            $('#zohoCustomerSearch').closest('.single-select-container').find('.dropdown-arrow').addClass('rotated');
            
            // Lazy load Zoho customers when first opened
            if (clientModalData.zohoCustomers.length === 0 && !clientModalData.zohoLoading) {
                loadZohoCustomersForClient(1, '', false);
            } else {
                renderZohoOptions();
            }
        }
    };

    // Select country
    window.selectClientCountry = function(countryId, countryName) {
        clientModalData.selectedCountryId = countryId;
        $('#clientCountryId').val(countryId);
        $('#countrySearch').val(countryName);
        $('#countryDropdown').hide();
        const wrapper = $('#countrySearch').closest('.searchable-dropdown-wrapper');
        wrapper.removeClass('dropdown-open');
        wrapper.closest('.form-group').removeClass('has-open-dropdown');
        $('#countrySearch').closest('.single-select-container').find('.dropdown-arrow').removeClass('rotated');
    };

    // Select account
    window.selectClientAccount = function(accountId, accountName) {
        clientModalData.selectedAccountId = accountId;
        $('#clientAccountStatus').val(accountId);
        $('#accountSearch').val(accountName);
        $('#accountDropdown').hide();
        const wrapper = $('#accountSearch').closest('.searchable-dropdown-wrapper');
        wrapper.removeClass('dropdown-open');
        wrapper.closest('.form-group').removeClass('has-open-dropdown');
        $('#accountSearch').closest('.single-select-container').find('.dropdown-arrow').removeClass('rotated');
    };

    // Select Zoho customer
    window.selectClientZoho = function(zohoId, zohoName) {
        clientModalData.selectedZohoId = zohoId;
        $('#clientZohoCustomerId').val(zohoId);
        $('#zohoCustomerSearch').val(zohoName);
        $('#zohoDropdown').hide();
        const wrapper = $('#zohoCustomerSearch').closest('.searchable-dropdown-wrapper');
        wrapper.removeClass('dropdown-open');
        wrapper.closest('.form-group').removeClass('has-open-dropdown');
        $('#zohoCustomerSearch').closest('.single-select-container').find('.dropdown-arrow').removeClass('rotated');
        $('#clearZohoBtn').show();
    };

    // Clear Zoho customer
    window.clearClientZohoCustomer = function(event) {
        if (event) event.stopPropagation();
        clientModalData.selectedZohoId = null;
        $('#clientZohoCustomerId').val('');
        $('#zohoCustomerSearch').val('');
        $('#clearZohoBtn').hide();
    };

    // Render country options
    function renderCountryOptions() {
        if (!clientModalData.countries || clientModalData.countries.length === 0) {
            $('#countryOptions').html(`
                <div class="no-results">
                    <i class="mdi mdi-loading mdi-spin"></i>
                    <span>Loading countries...</span>
                </div>
            `);
            return;
        }

        const searchTerm = ($('#countrySearch').val() || '').toLowerCase();
        clientModalData.filteredCountries = clientModalData.countries.filter(c => {
            const name = (c.name || '').toLowerCase();
            return name.includes(searchTerm);
        });

        let html = '';
        if (clientModalData.filteredCountries.length > 0) {
            clientModalData.filteredCountries.forEach(country => {
                const isSelected = clientModalData.selectedCountryId == country.id;
                const countryName = (country.name || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                html += `
                    <div class="option-item ${isSelected ? 'selected' : ''}" onclick="selectClientCountry(${country.id}, '${countryName}')">
                        ${isSelected ? '<i class="mdi mdi-check-circle text-primary"></i>' : ''}
                        <span>${country.name || ''}</span>
                    </div>
                `;
            });
        } else {
            html = `
                <div class="no-results">
                    <i class="mdi mdi-alert-circle-outline"></i>
                    <span>No countries found${searchTerm ? ' matching "' + searchTerm + '"' : ''}</span>
                </div>
            `;
        }
        $('#countryOptions').html(html);
    }

    // Render account options
    function renderAccountOptions() {
        if (!clientModalData.accounts || clientModalData.accounts.length === 0) {
            $('#accountOptions').html(`
                <div class="no-results">
                    <i class="mdi mdi-loading mdi-spin"></i>
                    <span>Loading account settings...</span>
                </div>
            `);
            return;
        }

        const searchTerm = ($('#accountSearch').val() || '').toLowerCase();
        clientModalData.filteredAccounts = clientModalData.accounts.filter(a => {
            // Handle both object and array formats
            const key = (typeof a === 'object' && a !== null && !Array.isArray(a)) ? (a.key || '') : (a['key'] || '');
            return key.toLowerCase().includes(searchTerm);
        });

        let html = '';
        if (clientModalData.filteredAccounts.length > 0) {
            clientModalData.filteredAccounts.forEach(account => {
                const isSelected = clientModalData.selectedAccountId == account.id;
                // Handle both object and array formats
                const accountKey = (typeof account === 'object' && account !== null && !Array.isArray(account)) ? (account.key || '') : (account['key'] || '');
                const accountKeyEscaped = accountKey.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                html += `
                    <div class="option-item ${isSelected ? 'selected' : ''}" onclick="selectClientAccount(${account.id}, '${accountKeyEscaped}')">
                        ${isSelected ? '<i class="mdi mdi-check-circle text-primary"></i>' : ''}
                        <span>${accountKey}</span>
                    </div>
                `;
            });
        } else {
            html = `
                <div class="no-results">
                    <i class="mdi mdi-alert-circle-outline"></i>
                    <span>No account settings found${searchTerm ? ' matching "' + searchTerm + '"' : ''}</span>
                </div>
            `;
        }
        $('#accountOptions').html(html);
    }

    // Render Zoho options
    function renderZohoOptions() {
        // Don't filter locally - filtering is done server-side
        clientModalData.filteredZohoCustomers = clientModalData.zohoCustomers;

        let html = '';
        if (clientModalData.filteredZohoCustomers.length > 0) {
            clientModalData.filteredZohoCustomers.forEach(zoho => {
                const isSelected = clientModalData.selectedZohoId == zoho.id;
                const zohoName = (zoho.name || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                html += `
                    <div class="option-item ${isSelected ? 'selected' : ''}" onclick="selectClientZoho(${zoho.id}, '${zohoName}')">
                        ${isSelected ? '<i class="mdi mdi-check-circle text-primary"></i>' : ''}
                        <div class="d-flex flex-column">
                            <span class="fw-bold">${zoho.name || ''}</span>
                            <small class="text-muted">
                                <span>${zoho.customer_no || ''}</span>
                                ${zoho.currency_code ? `<span class="ms-2 badge bg-info">${zoho.currency_code}</span>` : ''}
                            </small>
                        </div>
                    </div>
                `;
            });
            
            // Show loading indicator at bottom if loading more
            if (clientModalData.zohoLoading) {
                html += `
                    <div class="option-item" style="justify-content: center; opacity: 0.7;">
                        <i class="mdi mdi-loading mdi-spin"></i>
                        <span>Loading more...</span>
                    </div>
                `;
            }
            // Show "scroll for more" message if has more pages
            else if (clientModalData.zohoHasMore) {
                html += `
                    <div class="option-item" style="justify-content: center; opacity: 0.5; font-size: 12px;">
                        <i class="mdi mdi-arrow-down"></i>
                        <span>Scroll for more</span>
                    </div>
                `;
            }
        } else if (clientModalData.zohoLoading) {
            html = `
                <div class="no-results">
                    <i class="mdi mdi-loading mdi-spin"></i>
                    <span>Loading Dynamics customers...</span>
                </div>
            `;
        } else {
            const searchTerm = $('#zohoCustomerSearch').val();
            html = `
                <div class="no-results">
                    <i class="mdi mdi-alert-circle-outline"></i>
                    <span>No Dynamics customers found${searchTerm ? ' matching "' + searchTerm + '"' : ''}</span>
                </div>
            `;
        }
        $('#zohoOptions').html(html);
    }

    // Search handlers - open dropdown on typing
    $('#countrySearch').on('input', function() {
        const dropdown = $('#countryDropdown');
        const wrapper = $(this).closest('.searchable-dropdown-wrapper');
        const formGroup = wrapper.closest('.form-group');
        
        if (!dropdown.is(':visible')) {
            // Close other dropdowns
            $('.dropdown-list').not(dropdown).hide();
            $('.dropdown-arrow').removeClass('rotated');
            $('.searchable-dropdown-wrapper').removeClass('dropdown-open');
            $('.form-group').removeClass('has-open-dropdown');
            
            // Open this dropdown
            dropdown.show();
            wrapper.addClass('dropdown-open');
            formGroup.addClass('has-open-dropdown');
            $(this).closest('.single-select-container').find('.dropdown-arrow').addClass('rotated');
        }
        renderCountryOptions();
    });

    $('#accountSearch').on('input', function() {
        const dropdown = $('#accountDropdown');
        const wrapper = $(this).closest('.searchable-dropdown-wrapper');
        const formGroup = wrapper.closest('.form-group');
        
        if (!dropdown.is(':visible')) {
            // Close other dropdowns
            $('.dropdown-list').not(dropdown).hide();
            $('.dropdown-arrow').removeClass('rotated');
            $('.searchable-dropdown-wrapper').removeClass('dropdown-open');
            $('.form-group').removeClass('has-open-dropdown');
            
            // Open this dropdown
            dropdown.show();
            wrapper.addClass('dropdown-open');
            formGroup.addClass('has-open-dropdown');
            $(this).closest('.single-select-container').find('.dropdown-arrow').addClass('rotated');
        }
        renderAccountOptions();
    });

    // Debounce timer for Zoho search
    let zohoSearchTimeout = null;
    
    $('#zohoCustomerSearch').on('input', function() {
        const dropdown = $('#zohoDropdown');
        const wrapper = $(this).closest('.searchable-dropdown-wrapper');
        const formGroup = wrapper.closest('.form-group');
        const searchTerm = $(this).val();
        
        if (!dropdown.is(':visible')) {
            // Close other dropdowns
            $('.dropdown-list').not(dropdown).hide();
            $('.dropdown-arrow').removeClass('rotated');
            $('.searchable-dropdown-wrapper').removeClass('dropdown-open');
            $('.form-group').removeClass('has-open-dropdown');
            
            // Open this dropdown
            dropdown.show();
            wrapper.addClass('dropdown-open');
            formGroup.addClass('has-open-dropdown');
            $(this).closest('.single-select-container').find('.dropdown-arrow').addClass('rotated');
        }
        
        // Clear previous timeout
        if (zohoSearchTimeout) {
            clearTimeout(zohoSearchTimeout);
        }
        
        // Show loading state immediately
        $('#zohoOptions').html(`
            <div class="no-results">
                <i class="mdi mdi-loading mdi-spin"></i>
                <span>Searching...</span>
            </div>
        `);
        
        // Debounce search - wait 500ms after user stops typing
        zohoSearchTimeout = setTimeout(function() {
            // Reset pagination and load first page with search
            clientModalData.zohoCurrentPage = 1;
            loadZohoCustomersForClient(1, searchTerm, false);
        }, 500);
    });
    
    // Infinite scroll for Zoho customers dropdown
    $('#zohoDropdown').on('scroll', function() {
        const dropdown = $(this);
        const scrollTop = dropdown.scrollTop();
        const scrollHeight = dropdown.prop('scrollHeight');
        const clientHeight = dropdown.prop('clientHeight');
        
        // Check if scrolled near bottom (within 50px)
        if (scrollTop + clientHeight >= scrollHeight - 50) {
            // Load more if available and not currently loading
            if (clientModalData.zohoHasMore && !clientModalData.zohoLoading) {
                const nextPage = clientModalData.zohoCurrentPage + 1;
                console.log('Loading more Zoho customers, page:', nextPage);
                loadZohoCustomersForClient(nextPage, clientModalData.zohoSearchTerm, true);
            }
        }
    });

    // Close dropdowns when clicking outside
    $(document).on('click', function(event) {
        if (!$(event.target).closest('.searchable-dropdown-wrapper').length) {
            $('.dropdown-list').hide();
            $('.dropdown-arrow').removeClass('rotated');
            $('.searchable-dropdown-wrapper').removeClass('dropdown-open');
            $('.form-group').removeClass('has-open-dropdown');
        }
    });

    // Load data when modal is shown
    $('#addClientModal').on('show.bs.modal', function() {
        loadCountriesForClient();
        loadAccountSettingsForClient();
        // Don't auto-load Zoho customers - only load when dropdown is opened
        
        // Reset form and selections
        clientModalData.selectedCountryId = null;
        clientModalData.selectedAccountId = null;
        clientModalData.selectedZohoId = null;
        $('#countrySearch').val('');
        $('#accountSearch').val('');
        $('#zohoCustomerSearch').val('');
        $('#clearZohoBtn').hide();
        
        // Reset Zoho pagination state
        clientModalData.zohoCustomers = [];
        clientModalData.filteredZohoCustomers = [];
        clientModalData.zohoCurrentPage = 1;
        clientModalData.zohoLastPage = 1;
        clientModalData.zohoHasMore = false;
        clientModalData.zohoLoading = false;
        clientModalData.zohoSearchTerm = '';
        
        // Close all dropdowns and reset states
        $('.dropdown-list').hide();
        $('.dropdown-arrow').removeClass('rotated');
        $('.searchable-dropdown-wrapper').removeClass('dropdown-open');
        $('.form-group').removeClass('has-open-dropdown');
        
        // Clear all error messages
        $('[id^="error-"]').text('');
    });
    
    // Clean up when modal is hidden
    $('#addClientModal').on('hidden.bs.modal', function() {
        $('.dropdown-list').hide();
        $('.dropdown-arrow').removeClass('rotated');
        $('.searchable-dropdown-wrapper').removeClass('dropdown-open');
        $('.form-group').removeClass('has-open-dropdown');
    });

    $('#addClientForm').on('submit', function(e) {
        e.preventDefault();
        
        // Prevent double submission
        if ($(this).data('submitting')) {
            return false;
        }
        $(this).data('submitting', true);
        
        // Clear previous errors
        $('[id^="error-"]').text('');
        
        // Show loading state
        $('#submitClientBtn').html('<i class="mdi mdi-loading mdi-spin"></i> Saving...').prop('disabled', true);
        
        // Build form data with proper checkbox handling
        const formData = {
            name: $('#clientName').val(),
            email: $('#clientEmail').val(),
            telephone1: $('#clientTelephone1').val(),
            telephone2: $('#clientTelephone2').val(),
            country_id: $('#clientCountryId').val(),
            account_status: $('#clientAccountStatus').val(),
            postal_address: $('#clientPostalAddress').val(),
            physical_address: $('#clientPhysicalAddress').val(),
            website: $('#clientWebsite').val(),
            fax: $('#clientFax').val(),
            vat_no: $('#clientVatNo').val(),
            credit_days: $('#clientCreditDays').val(),
            zoho_customer_id: $('#clientZohoCustomerId').val(),
            active: $('#clientActive').is(':checked') ? 1 : 0,
            lpos_required: $('#clientLposRequired').is(':checked') ? 1 : 0
        };
        
        console.log('Submitting client form data:', formData);
        
        $.ajax({
            url: '/api/clients',
            method: 'POST',
            data: formData,
            success: function(response) {
                console.log('Customer created successfully:', response);
                
                // Show success notification first
                showNotification('success', 'Customer added successfully!');
                
                // Close modal and clean up
                $('#addClientModal').modal('hide');
                
                // Force remove modal backdrop and restore page interactivity
                setTimeout(function() {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css('overflow', '');
                    $('body').css('padding-right', '');
                }, 300);
                
                // Reset form
                $('#addClientForm')[0].reset();
                
                // Reset modal state
                clientModalData.selectedCountryId = null;
                clientModalData.selectedAccountId = null;
                clientModalData.selectedZohoId = null;
                $('#countrySearch').val('');
                $('#accountSearch').val('');
                $('#zohoCustomerSearch').val('');
                $('#clearZohoBtn').hide();
                
                // Add new option to client select (non-blocking)
                setTimeout(function() {
                var newOption = '<option value="' + response.id + '">' + response.name + '</option>';
                var $select = $('select[data-element-type="client_select"]');
                    
                    if ($select.length > 0) {
                $select.append(newOption);
                
                // Set the value and trigger change events
                $select.val(response.id);
                $select.trigger('change');
                        
                // Trigger Select2 events if Select2 is initialized
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.trigger('select2:select');
                }
                
                // Update form validation and progress
                if (typeof FormFill !== 'undefined') {
                    FormFill.updateProgress();
                    FormFill.updateSubmitButtonState();
                }
                    }
                }, 500);
            },
            error: function(xhr) {
                console.error('Client submission error:', xhr);
                console.error('Status:', xhr.status);
                console.error('Response:', xhr.responseJSON);
                console.error('Response Text:', xhr.responseText);
                
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    // Display validation errors
                    console.error('Validation errors:', xhr.responseJSON.errors);
                    $.each(xhr.responseJSON.errors, function(field, messages) {
                        console.error('Field:', field, 'Errors:', messages);
                        $('#error-' + field).text(messages[0]);
                    });
                    showNotification('error', 'Please correct the errors in the form.');
                } else {
                    showNotification('error', 'Error adding customer: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'));
                }
            },
            complete: function() {
                // Reset submission flag and button
                $('#addClientForm').data('submitting', false);
                $('#submitClientBtn').html('<i class="mdi mdi-content-save"></i> Create Customer').prop('disabled', false);
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
                console.log('Client unit created successfully:', response);
                
                // Show success notification first
                showNotification('success', 'Client unit added successfully!');
                
                // Close modal and clean up
                $('#addClientUnitModal').modal('hide');
                
                // Force remove modal backdrop and restore page interactivity
                setTimeout(function() {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css('overflow', '');
                    $('body').css('padding-right', '');
                }, 300);
                
                // Reset form
                $('#addClientUnitForm')[0].reset();
                
                // Add new option to client unit select (non-blocking)
                setTimeout(function() {
                var newOption = '<option value="' + response.id + '">' + response.name + '</option>';
                var $select = $('select[data-element-type="client_unit_select"]');
                    
                    if ($select.length > 0) {
                $select.append(newOption);
                
                // Set the value and trigger change events
                $select.val(response.id);
                $select.trigger('change');
                        
                // Trigger Select2 events if Select2 is initialized
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.trigger('select2:select');
                }
                
                        // Update form validation and progress
                        if (typeof FormFill !== 'undefined') {
                            FormFill.updateProgress();
                            FormFill.updateSubmitButtonState();
                        }
                        
                        // Also update the sample point modal's client unit dropdown
                        if ($('#samplePointUnit').length > 0) {
                            $('#samplePointUnit').append('<option value="' + response.id + '">' + response.name + '</option>');
                        }
                        
                    }
                }, 500);
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
                console.log('Client contact created successfully:', response);
                
                // Show success notification first
                showNotification('success', 'Client contact added successfully!');
                
                // Close modal and clean up
                $('#addClientContactModal').modal('hide');
                
                // Force remove modal backdrop and restore page interactivity
                setTimeout(function() {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css('overflow', '');
                    $('body').css('padding-right', '');
                }, 300);
                
                // Reset form
                $('#addClientContactForm')[0].reset();
                
                // Add new option to client contact select (non-blocking)
                setTimeout(function() {
                var newOption = '<option value="' + response.id + '">' + response.first_name + ' ' + response.last_name + '</option>';
                var $select = $('select[data-element-type="client_contact_select"]');
                    
                    if ($select.length > 0) {
                $select.append(newOption);
                
                // Set the value and trigger change events
                $select.val(response.id);
                $select.trigger('change');
                        
                // Trigger Select2 events if Select2 is initialized
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.trigger('select2:select');
                }
                
                // Update form validation and progress
                if (typeof FormFill !== 'undefined') {
                    FormFill.updateProgress();
                    FormFill.updateSubmitButtonState();
                }
                    }
                }, 500);
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
