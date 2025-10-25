<div class="container-fluid">
    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-body p-4">
            <!-- Header -->
            <div class="d-flex justify-content-between pb-2 border-bottom align-items-center mb-4">
                <h3 class="mb-0">
                    <i class="mdi mdi-file-document-plus text-primary"></i>
                    Generate Sales Order
                </h3>
                <button wire:click="$dispatch('closeSalesOrderWizard')" class="btn btn-sm btn-outline-secondary">
                    <i class="mdi mdi-close"></i> Cancel
                </button>
            </div>

            <!-- Progress Indicator -->
            <div class="wizard-progress mb-4">
                <div class="step {{ $currentStep >= 1 ? 'active' : '' }} {{ $currentStep > 1 ? 'completed' : '' }}">
                    <div class="step-number">{{ $currentStep > 1 ? '✓' : '1' }}</div>
                    <div class="step-label">Batches</div>
                </div>
                <div class="step-line {{ $currentStep > 1 ? 'completed' : '' }}"></div>
                
                <div class="step {{ $currentStep >= 2 ? 'active' : '' }} {{ $currentStep > 2 ? 'completed' : '' }}">
                    <div class="step-number">{{ $currentStep > 2 ? '✓' : '2' }}</div>
                    <div class="step-label">Customer</div>
                </div>
                <div class="step-line {{ $currentStep > 2 ? 'completed' : '' }}"></div>
                
                <div class="step {{ $currentStep >= 3 ? 'active' : '' }} {{ $currentStep > 3 ? 'completed' : '' }}">
                    <div class="step-number">{{ $currentStep > 3 ? '✓' : '3' }}</div>
                    <div class="step-label">Mapping</div>
                </div>
                <div class="step-line {{ $currentStep > 3 ? 'completed' : '' }}"></div>
                
                <div class="step {{ $currentStep >= 4 ? 'active' : '' }} {{ $currentStep > 4 ? 'completed' : '' }}">
                    <div class="step-number">{{ $currentStep > 4 ? '✓' : '4' }}</div>
                    <div class="step-label">Add Items</div>
                </div>
                <div class="step-line {{ $currentStep > 4 ? 'completed' : '' }}"></div>
                
                <div class="step {{ $currentStep >= 5 ? 'active' : '' }}">
                    <div class="step-number">5</div>
                    <div class="step-label">Review</div>
                </div>
            </div>

            <!-- Error Messages -->
            @if($errorMessage)
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="mdi mdi-alert-circle"></i> {{ $errorMessage }}
                    <button type="button" class="btn-close" wire:click="$set('errorMessage', '')"></button>
                </div>
            @endif

            @if(count($validationErrors) > 0)
                <div class="alert alert-danger">
                    <h6 class="alert-heading"><i class="mdi mdi-alert"></i> Please fix the following errors:</h6>
                    <ul class="mb-0">
                        @foreach($validationErrors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($successMessage)
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="mdi mdi-check-circle"></i> {{ $successMessage }}
                    <button type="button" class="btn-close" wire:click="$set('successMessage', '')"></button>
                </div>
            @endif

            <!-- Wizard Content -->
            <div class="wizard-content">
                <!-- Step 1: Batch Review -->
                @if($currentStep === 1)
                    <div class="wizard-step">
                        <h4 class="mb-4 border-bottom">
                            <i class="mdi mdi-folder-multiple"></i> Review Selected Batches
                        </h4>
                        
                        @if(count($batchData) > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Batch Code</th>
                                            <th>Customer</th>
                                            <th>Sample Type</th>
                                            <th>Receipt Date</th>
                                            <th>Samples</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($batchData as $batch)
                                            <tr class="{{ $batch['has_invoice'] ? 'table-danger' : '' }}">
                                                <td>
                                                    <strong>{{ $batch['batch_code'] }}</strong>
                                                    @if($batch['has_invoice'])
                                                        <br><small class="text-danger">Has Invoice</small>
                                                    @endif
                                                </td>
                                                <td>{{ $batch['customer_name'] }}</td>
                                                <td>{{ $batch['sample_type_name'] }}</td>
                                                <td>{{ $batch['receipt_date'] }}</td>
                                                <td><span class="badge bg-info" style="color: white;">{{ $batch['sample_count'] }}</span></td>
                                                <td>
                                                    <button wire:click="removeBatch('{{ $batch['batch_code'] }}')" 
                                                            class="btn btn-sm btn-danger"
                                                            title="Remove">
                                                        <i class="mdi mdi-close"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="summary-box mt-3 p-3 bg-light rounded">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong><i class="mdi mdi-information"></i> Summary:</strong>
                                        <div class="mt-2">Total Batches: <strong>{{ count($batchData) }}</strong></div>
                                    </div>
                                    <div class="col-md-6">
                                        @if($customer)
                                            <strong>Customer:</strong>
                                            <div class="mt-2">{{ $customer->name }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="mdi mdi-folder-alert text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-3">No batches selected</h5>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Step 2: Customer Zoho Mapping -->
                @if($currentStep === 2)
                    <div class="wizard-step">
                        <h4 class="mb-4 border-bottom">
                            <i class="mdi mdi-account-cog"></i> Customer Integration Details
                        </h4>
                        
                        <div class="customer-info-box p-4 bg-light rounded mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5><i class="mdi mdi-account"></i> {{ $customer->name }}</h5>
                                    <p class="text-muted mb-0">{{ $customer->email }}</p>
                                </div>
                                <div class="col-md-6 text-end">
                                    <small class="text-muted">Customer Code:</small><br>
                                    <strong>{{ $customer->code }}</strong>
                                </div>
                            </div>
                        </div>
                        
                        @if(!$customer->zoho_id || !$customer->currency_id)
                            <div class="alert alert-warning">
                                <i class="mdi mdi-alert"></i> This customer needs Dynamics integration details. Please provide them below.
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="mdi mdi-check-circle"></i> Customer already has Dynamics integration. You can update if needed.
                            </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="mdi mdi-cloud text-primary"></i> Dynamics Customer ID <span class="text-danger">*</span>
                                    </label>
                                    <div class="tag-select-container" wire:click="$set('showZohoCustomerDropdown', true)">
                                        <div class="tag-select-input">
                                            @if($this->selectedZohoCustomer)
                                                <span class="tag-badge">
                                                    {{ $this->selectedZohoCustomer->name }}
                                                    <i class="mdi mdi-close-circle" wire:click.stop="clearZohoCustomer()"></i>
                                                </span>
                                            @endif
                                            
                                            @if(!$this->selectedZohoCustomer)
                                                <input type="text" 
                                                       wire:model.live="zohoCustomerSearch" 
                                                       class="tag-input" 
                                                       placeholder="Search Dynamics customers..."
                                                       autocomplete="off">
                                            @endif
                                        </div>
                                        @if($showZohoCustomerDropdown)
                                            <div class="tag-dropdown">
                                                @if(count($this->filteredZohoCustomers) > 0)
                                                    @foreach($this->filteredZohoCustomers as $zCustomer)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectZohoCustomer('{{ $zCustomer->customer_no }}')">
                                                            <strong>{{ $zCustomer->name }}</strong>
                                                            <br><small class="text-muted">{{ $zCustomer->email }}</small>
                                                            <br><small class="text-info">Customer No: {{ $zCustomer->customer_no }}</small>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="tag-dropdown-item" style="cursor: default;">
                                                        <small class="text-muted">
                                                            @if($zohoCustomerSearch)
                                                                No customers found matching "{{ $zohoCustomerSearch }}"
                                                            @else
                                                                Start typing to search customers...
                                                            @endif
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <small class="form-text text-muted">Select the Dynamics contact for this customer</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">
                                        <i class="mdi mdi-currency-usd text-success"></i> Currency <span class="text-danger">*</span>
                                    </label>
                                    
                                    @if($zohoCustomerId && !$this->selectedZohoCurrency)
                                        <!-- Manual Currency Selection when auto-populate fails -->
                                        <div class="tag-select-container" wire:click="$set('showZohoCurrencyDropdown', true)">
                                            <div class="tag-select-input" style="border-color: #ffc107;">
                                                @if($this->selectedZohoCurrency)
                                                    <span class="tag-badge">
                                                        {{ $this->selectedZohoCurrency->code }} - {{ $this->selectedZohoCurrency->description }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="$set('zohoCurrencyId', null)"></i>
                                                    </span>
                                                @endif
                                                <input type="text" 
                                                       wire:model.live="zohoCurrencySearch" 
                                                       class="tag-input" 
                                                       placeholder="{{ $this->selectedZohoCurrency ? '' : 'Search currency (required)...' }}"
                                                       autocomplete="off">
                                            </div>
                                            @if($showZohoCurrencyDropdown)
                                                <div class="tag-dropdown">
                                                    @if(count($this->filteredCurrencies) > 0)
                                                        @foreach($this->filteredCurrencies as $curr)
                                                            <div class="tag-dropdown-item" wire:click.stop="selectZohoCurrency({{ $curr->id }})">
                                                                <strong>{{ $curr->code }}</strong> - {{ $curr->description }}
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="tag-dropdown-item" style="cursor: default;">
                                                            <small class="text-muted">
                                                                @if($zohoCurrencySearch)
                                                                    No currencies found matching "{{ $zohoCurrencySearch }}"
                                                                @else
                                                                    Start typing to search currencies...
                                                                @endif
                                                            </small>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <small class="form-text text-warning">
                                            <i class="mdi mdi-alert"></i> This customer has no currency set in Dynamics. Please select one manually.
                                        </small>
                                    @else
                                        <!-- Display Selected Currency (Read-only when auto-populated) -->
                                        <div class="form-control bg-light" style="cursor: not-allowed; min-height: 42px; display: flex; align-items: center;">
                                            @if($this->selectedZohoCurrency)
                                                <span class="badge bg-success text-white" style="font-size: 0.9rem;">
                                                    <i class="mdi mdi-check-circle"></i>
                                                    {{ $this->selectedZohoCurrency->code }} - {{ $this->selectedZohoCurrency->description }}
                                                </span>
                                            @else
                                                <span class="text-muted">Auto-populated from customer</span>
                                            @endif
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="mdi mdi-information"></i> Currency is automatically set from the selected customer
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        @if($zohoCustomerId != $customer->zoho_id || $zohoCurrencyId != $customer->currency_id)
                            <div class="form-check mt-3 p-3 bg-warning bg-opacity-10 rounded">
                                <input type="checkbox" wire:model="updateCustomerZohoData" class="form-check-input" id="updateCustomer" checked>
                                <label class="form-check-label" for="updateCustomer">
                                    <strong>Update customer record with this information</strong>
                                    <br><small class="text-muted">The customer's Dynamics ID and currency will be saved for future use</small>
                                </label>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Step 3: Analysis Mapping -->
                @if($currentStep === 3)
                    <div class="wizard-step">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0">
                                <i class="mdi mdi-link-variant"></i> Analysis to Invoicable Items Mapping
                            </h4>
                            <button wire:click="autoMapAllAnalyses" class="btn btn-outline-secondary btn-sm">
                                <i class="mdi mdi-refresh"></i> Re-Map All
                            </button>
                        </div>
                        
                        @if(count($analysisMappings) > 0)
                            <div class="alert alert-success mb-3">
                                <i class="mdi mdi-check-circle"></i> <strong>{{ count($analysisMappings) }}</strong> of <strong>{{ count($analysisTypesData) }}</strong> analysis types automatically mapped. You can adjust mappings below if needed.
                            </div>
                        @else
                            <div class="alert alert-warning mb-3">
                                <i class="mdi mdi-alert"></i> No automatic mappings found. Please map items manually or ensure analysis types have linked invoicable items.
                            </div>
                        @endif
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="30%">Analysis Type</th>
                                        <th width="10%">Count</th>
                                        <th width="40%">Invoicable Item</th>
                                        <th width="10%">Unit Price</th>
                                        <th width="10%">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analysisTypesData as $analysisTypeId => $data)
                                        <tr class="{{ !isset($analysisMappings[$analysisTypeId]) ? 'table-warning' : '' }}">
                                            <td>
                                                <strong>{{ $data['name'] }}</strong>
                                                <br><small class="text-muted">Code: {{ $data['code'] }}</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary" style="color: white; font-size: 1rem;">{{ $data['count'] }}</span>
                                            </td>
                                            <td>
                                                <div class="tag-select-container" wire:click="$set('showItemDropdowns.{{ $analysisTypeId }}', true)">
                                                    <div class="tag-select-input">
                                                        @if(isset($analysisMappings[$analysisTypeId]))
                                                            @php $item = $this->getInvoicableItemById($analysisMappings[$analysisTypeId]); @endphp
                                                            @if($item)
                                                                <span class="tag-badge">
                                                                    {{ $item->item_code }} - {{ $item->item_name }}
                                                                    <i class="mdi mdi-close-circle" wire:click.stop="$set('analysisMappings.{{ $analysisTypeId }}', null)"></i>
                                                                </span>
                                                            @endif
                                                        @endif
                                                        <input type="text" 
                                                               wire:model.live="itemSearches.{{ $analysisTypeId }}" 
                                                               class="tag-input" 
                                                               placeholder="Search items..."
                                                               autocomplete="off">
                                                    </div>
                                                    @if($showItemDropdowns[$analysisTypeId] ?? false)
                                                        <div class="tag-dropdown">
                                                            @foreach($this->filteredItemsForAnalysis as $item)
                                                                <div class="tag-dropdown-item" wire:click.stop="selectInvoicableItemForAnalysis({{ $analysisTypeId }}, {{ $item->id }})">
                                                                    <strong>{{ $item->item_code }}</strong> - {{ $item->item_name }}
                                                                    <span class="badge bg-success float-end" style="color: white;">{{ number_format($item->unit_price, 2) }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if(isset($analysisMappings[$analysisTypeId]))
                                                    @php $item = $this->getInvoicableItemById($analysisMappings[$analysisTypeId]); @endphp
                                                    @if($item)
                                                        <input type="number" 
                                                               wire:model.live="analysisCustomPrices.{{ $analysisTypeId }}" 
                                                               class="form-control form-control-sm" 
                                                               placeholder="{{ number_format($item->unit_price, 2) }}"
                                                               step="0.01"
                                                               min="0">
                                                        <small class="text-muted">Default: {{ number_format($item->unit_price, 2) }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if(isset($analysisMappings[$analysisTypeId]))
                                                    @php 
                                                        $item = $this->getInvoicableItemById($analysisMappings[$analysisTypeId]); 
                                                        $unitPrice = $analysisCustomPrices[$analysisTypeId] ?? ($item ? $item->unit_price : 0);
                                                    @endphp
                                                    @if($item)
                                                        <strong>{{ number_format($unitPrice * $data['count'], 2) }}</strong>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="4" class="text-end">Analysis Items Total:</th>
                                        <th class="text-end"><strong>{{ number_format($this->analysisTotal, 2) }}</strong></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        @if($this->unmappedAnalysisCount > 0)
                            <div class="alert alert-warning mt-3">
                                <i class="mdi mdi-alert"></i> <strong>{{ $this->unmappedAnalysisCount }}</strong> analysis type(s) not yet mapped. Please map all items to continue.
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Step 4: Additional Items -->
                @if($currentStep === 4)
                    <div class="wizard-step">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0">
                                <i class="mdi mdi-plus-box"></i> Additional Items & Fees
                            </h4>
                            <button wire:click="openAddItemModal" class="btn btn-success">
                                <i class="mdi mdi-plus"></i> Add Item
                            </button>
                        </div>
                        
                        <p class="text-muted mb-3">Add sampling fees, logistics, or other charges to this sales order</p>
                        
                        @if(count($additionalItems) > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Item Code</th>
                                            <th>Item Name</th>
                                            <th width="15%">Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                            <th width="10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($additionalItems as $index => $item)
                                            <tr>
                                                <td>{{ $item['item_code'] }}</td>
                                                <td>{{ $item['item_name'] }}</td>
                                                <td>
                                                    <input type="number" 
                                                           wire:model.live="additionalItems.{{ $index }}.quantity" 
                                                           class="form-control form-control-sm" 
                                                           min="1">
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                           wire:model.live="additionalItems.{{ $index }}.unit_price" 
                                                           class="form-control form-control-sm" 
                                                           step="0.01"
                                                           min="0">
                                                </td>
                                                <td class="text-end"><strong>{{ number_format($item['unit_price'] * $item['quantity'], 2) }}</strong></td>
                                                <td class="text-center">
                                                    <button wire:click="removeAdditionalItem({{ $index }})" 
                                                            class="btn btn-sm btn-danger"
                                                            title="Remove">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="4" class="text-end">Additional Items Total:</th>
                                            <th class="text-end"><strong>{{ number_format($this->additionalItemsTotal, 2) }}</strong></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5 bg-light rounded">
                                <i class="mdi mdi-package-variant-closed text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3 mb-0">No additional items added</p>
                                <small class="text-muted">Click "Add Item" to include fees or charges</small>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Step 5: Review & Generate -->
                @if($currentStep === 5)
                    <div class="wizard-step">
                        <h4 class="mb-4 border-bottom">
                            <i class="mdi mdi-check-all"></i> Review & Generate Sales Order
                        </h4>
                        
                        <!-- Customer Info -->
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="mdi mdi-account"></i> Customer Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <p class="mb-2"><strong>Name:</strong> {{ $customer->name }}</p>
                                        <p class="mb-0"><strong>Email:</strong> {{ $customer->email }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-2"><strong>Dynamics ID:</strong> {{ $zohoCustomerId ?? 'N/A' }}</p>
                                        <p class="mb-0"><strong>Currency:</strong> {{ $this->selectedZohoCurrency ? $this->selectedZohoCurrency->code . ' - ' . $this->selectedZohoCurrency->description : 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-2"><strong>Credit Days:</strong> {{ $customer->credit_days ?? 30 }} days</p>
                                        <p class="mb-0"><strong>Invoice #:</strong> <span class="badge bg-primary" style="color: white;">FV-S-{{ $this->nextInvoiceNumber }}</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Batches -->
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="mdi mdi-folder-multiple"></i> Batches ({{ count($selectedBatches) }})</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ implode(', ', $selectedBatches) }}</p>
                            </div>
                        </div>
                        
                        <!-- Line Items -->
                        <div class="card mb-3">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="mdi mdi-format-list-bulleted"></i> Sales Order Line Items</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Unit Price</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($this->invoicePreview as $line)
                                            <tr>
                                                <td>
                                                    <strong>{{ $line['description'] }}</strong>
                                                    @if($line['type'] === 'analysis')
                                                        <br><small class="text-muted">Analysis Service</small>
                                                    @else
                                                        <br><small class="text-muted">Additional Charge</small>
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ $line['quantity'] }}</td>
                                                <td class="text-end">{{ number_format($line['unit_price'], 2) }}</td>
                                                <td class="text-end"><strong>{{ number_format($line['total'], 2) }}</strong></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="3" class="text-end">Subtotal:</th>
                                            <th class="text-end">{{ number_format($this->subtotal, 2) }}</th>
                                        </tr>
                                        <tr>
                                            <th colspan="3" class="text-end">Tax ({{ $this->taxRate }}%):</th>
                                            <th class="text-end">{{ number_format($this->taxAmount, 2) }}</th>
                                        </tr>
                                        <tr class="table-success">
                                            <th colspan="3" class="text-end">Grand Total:</th>
                                            <th class="text-end"><h5 class="mb-0">{{ number_format($this->grandTotal, 2) }}</h5></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <div class="alert alert-success">
                            <i class="mdi mdi-check-circle"></i> Ready to generate sales order <strong>FV-S-{{ $this->nextInvoiceNumber }}</strong>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Wizard Navigation -->
            <div class="wizard-actions">
                <div>
                    @if($currentStep > 1)
                        <button wire:click="previousStep" class="btn btn-outline-secondary">
                            <i class="mdi mdi-arrow-left"></i> Previous
                        </button>
                    @endif
                </div>
                <div>
                    @if($currentStep < 5)
                        <button wire:click="nextStep" class="btn btn-primary">
                            Next <i class="mdi mdi-arrow-right"></i>
                        </button>
                    @else
                        <button wire:click="generateSalesOrder" class="btn btn-success btn-lg">
                            <i class="mdi mdi-check-bold"></i> Generate Sales Order
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Add Additional Item Modal -->
    @if($showAddItemModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="mdi mdi-plus"></i> Add Additional Item
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeAddItemModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info mb-3">
                            <i class="mdi mdi-information"></i> Showing <strong>{{ count($this->nonAnalysisItems) }}</strong> available items. Use the search to filter.
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">
                                <i class="mdi mdi-magnify"></i> Search Items (Code, Name, or Description)
                            </label>
                            <input type="text" 
                                   wire:model.live="additionalItemSearch" 
                                   class="form-control form-control-lg" 
                                   placeholder="Search by item code, name, or description..."
                                   autocomplete="off"
                                   autofocus>
                            @if($additionalItemSearch)
                                <button type="button" 
                                        class="btn btn-sm btn-link text-danger" 
                                        wire:click="$set('additionalItemSearch', '')">
                                    <i class="mdi mdi-close"></i> Clear search
                                </button>
                            @endif
                        </div>
                        
                        @if(count($this->nonAnalysisItems) > 0)
                            <div class="list-group" style="max-height: 450px; overflow-y: auto;">
                                @foreach($this->nonAnalysisItems as $item)
                                    <button type="button" 
                                            wire:click="addAdditionalItem({{ $item->id }})" 
                                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                                        <div style="flex: 1;">
                                            <div class="d-flex align-items-center mb-1">
                                                <strong class="me-2">{{ $item->item_code }}</strong>
                                                @if($item->item_type)
                                                    <span class="badge bg-info" style="color: white; font-size: 0.75rem;">{{ $item->item_type }}</span>
                                                @endif
                                            </div>
                                            <div>{{ $item->item_name }}</div>
                                            @if($item->description)
                                                <small class="text-muted d-block mt-1">{{ $item->description }}</small>
                                            @endif
                                        </div>
                                        <div class="text-end ms-3">
                                            <span class="badge bg-success" style="color: white; font-size: 1rem; padding: 8px 12px;">
                                                {{ number_format($item->unit_price, 2) }}
                                            </span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="mdi mdi-package-variant-closed text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">No items found matching "{{ $additionalItemSearch }}"</p>
                                <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="$set('additionalItemSearch', '')">
                                    <i class="mdi mdi-refresh"></i> Show All Items
                                </button>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeAddItemModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
    .modal.show {
        display: block !important;
    }

    /* Wizard Progress Styling */
    .wizard-progress {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px 0;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }

    .step-number {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        border: 3px solid #e9ecef;
    }

    .step.active .step-number {
        background: #007bff;
        color: white;
        border-color: #007bff;
        box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.2);
    }

    .step.completed .step-number {
        background: #28a745;
        color: white;
        border-color: #28a745;
    }

    .step-label {
        margin-top: 8px;
        font-size: 0.85rem;
        font-weight: 500;
        color: #6c757d;
    }

    .step.active .step-label {
        color: #007bff;
        font-weight: 600;
    }

    .step.completed .step-label {
        color: #28a745;
    }

    .step-line {
        width: 60px;
        height: 3px;
        background: #e9ecef;
        margin: 0 10px;
        transition: all 0.3s ease;
    }

    .step-line.completed {
        background: #28a745;
    }

    .wizard-content {
        min-height: 450px;
        padding: 30px 20px;
    }

    .wizard-actions {
        display: flex;
        justify-content: space-between;
        padding: 20px;
        border-top: 2px solid #e9ecef;
        background: #f8f9fa;
        border-radius: 0 0 15px 15px;
    }

    /* Tag-based Dropdown Styling */
    .tag-select-container {
        position: relative;
        cursor: text;
    }

    .tag-select-input {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        min-height: 42px;
        padding: 6px 12px;
        background: #fff;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .tag-select-input:hover {
        border-color: #007bff;
    }

    .tag-select-input:focus-within {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .tag-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        background-color: #007bff;
        color: white;
        border-radius: 16px;
        font-size: 0.875rem;
        font-weight: 500;
        white-space: nowrap;
    }

    .tag-badge i {
        cursor: pointer;
        font-size: 1rem;
        opacity: 0.8;
        transition: opacity 0.2s;
    }

    .tag-badge i:hover {
        opacity: 1;
    }

    .tag-input {
        flex: 1;
        min-width: 120px;
        border: none;
        outline: none;
        padding: 4px;
        font-size: 0.9rem;
    }

    .tag-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 2px solid #007bff;
        border-top: none;
        border-radius: 0 0 8px 8px;
        max-height: 300px;
        overflow-y: auto;
        z-index: 1050;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-top: -2px;
    }

    .tag-dropdown-item {
        padding: 12px 16px;
        cursor: pointer;
        transition: background-color 0.2s;
        border-bottom: 1px solid #f0f0f0;
    }

    .tag-dropdown-item:hover {
        background-color: #f8f9fa;
    }

    .tag-dropdown-item:last-child {
        border-bottom: none;
    }

    .summary-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
    }
    </style>
    
    @script
    <script>
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.tag-select-container')) {
            $wire.set('showZohoCustomerDropdown', false);
            $wire.set('showZohoCurrencyDropdown', false);
            Object.keys($wire.get('showItemDropdowns')).forEach(key => {
                $wire.set(`showItemDropdowns.${key}`, false);
            });
        }
    });
    </script>
    @endscript
</div>
