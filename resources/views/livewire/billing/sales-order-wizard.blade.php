<div class="container-fluid py-4">
    <div class="workflow-board-panel shadow-xs border" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
        <div class="workflow-board-panel-header d-flex justify-content-between align-items-center px-4 py-3" style="background: #ffffff; border-bottom: 1px solid #f1f5f9;">
            <h5 class="mb-0 font-weight-bold">
                <i class="mdi mdi-file-document-plus-outline mr-2 text-primary"></i>
                Generate Draft Invoice
            </h5>
            <div class="ml-auto">
                <button wire:click="cancel" class="wizard-cancel-btn">
                    <i class="mdi mdi-close"></i>
                    <span class="ml-1 d-none d-sm-inline">Cancel Order</span>
                </button>
            </div>
        </div>

        <div class="workflow-board-panel-body p-0">
            <!-- Progress Indicator -->
            <div class="wizard-progress-nav d-flex align-items-center justify-content-between px-4 py-3 bg-light border-bottom">
                @php
                    $steps = [
                        1 => ['label' => 'Batches', 'icon' => 'mdi-folder-multiple'],
                        2 => ['label' => 'Customer', 'icon' => 'mdi-account-cog'],
                        3 => ['label' => 'Mapping', 'icon' => 'mdi-link-variant'],
                        4 => ['label' => 'Add Items', 'icon' => 'mdi-plus-box'],
                        5 => ['label' => 'Review', 'icon' => 'mdi-check-all'],
                    ];
                @endphp

                @foreach($steps as $stepNum => $step)
                    <div class="wizard-step-item {{ $currentStep == $stepNum ? 'active' : '' }} {{ $currentStep > $stepNum ? 'completed' : '' }}">
                        <div class="step-circle">
                            @if($currentStep > $stepNum)
                                <i class="mdi mdi-check"></i>
                            @else
                                <span>{{ $stepNum }}</span>
                            @endif
                        </div>
                        <div class="step-label d-none d-md-block">{{ $step['label'] }}</div>
                    </div>
                    @if($stepNum < 5)
                        <div class="step-connector {{ $currentStep > $stepNum ? 'completed' : '' }}"></div>
                    @endif
                @endforeach
            </div>

            <!-- Main Content Area -->
            <div class="p-4" style="min-height: 500px;">
                <!-- Error/Success Messages -->
                @if($errorMessage)
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-xs mb-4" style="border-left: 4px solid #dc3545 !important;">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-alert-circle mr-3" style="font-size: 20px;"></i>
                            <div>{{ $errorMessage }}</div>
                        </div>
                        <button type="button" class="close" wire:click="$set('errorMessage', '')"><span>&times;</span></button>
                    </div>
                @endif

                @if(count($validationErrors) > 0)
                    <div class="alert alert-danger border-0 shadow-xs mb-4" style="border-left: 4px solid #dc3545 !important;">
                        <h6 class="alert-heading font-weight-bold mb-2"><i class="mdi mdi-alert mr-2"></i> Action Required:</h6>
                        <ul class="mb-0 pl-4">
                            @foreach($validationErrors as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($successMessage)
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-xs mb-4" style="border-left: 4px solid #28a745 !important;">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-check-circle mr-3" style="font-size: 20px;"></i>
                            <div>{{ $successMessage }}</div>
                        </div>
                        <button type="button" class="close" wire:click="$set('successMessage', '')"><span>&times;</span></button>
                    </div>
                @endif

                <!-- Content Steps -->
                <div class="wizard-step-content">
                    @if($currentStep === 1)
                        <div class="animate-fade-in">
                            <h6 class="text-uppercase text-muted font-weight-bold mb-4 small"><i class="mdi mdi-information-outline"></i> Batch Selection Review</h6>
                            
                            @if(count($batchData) > 0)
                                <div class="table-responsive rounded border">
                                    <table class="table table-hover mb-0 workflow-table">
                                        <thead class="bg-light text-muted small text-uppercase">
                                            <tr>
                                                <th class="border-0">Batch Code</th>
                                                <th class="border-0">Customer</th>
                                                <th class="border-0">Sample Type</th>
                                                <th class="border-0">Date</th>
                                                <th class="border-0 text-center">Samples</th>
                                                <th class="border-0 text-right">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($batchData as $batch)
                                                <tr class="{{ $batch['has_invoice'] ? 'bg-danger-light' : '' }}">
                                                    <td class="font-weight-bold">
                                                        {{ $batch['batch_code'] }}
                                                        @if($batch['has_invoice'])
                                                            <div class="badge badge-danger ml-2">Already Invoiced</div>
                                                        @endif
                                                    </td>
                                                    <td>{{ $batch['customer_name'] }}</td>
                                                    <td>{{ $batch['sample_type_name'] }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($batch['receipt_date'])->format('d M Y') }}</td>
                                                    <td class="text-center">
                                                        <span class="badge badge-pill badge-primary">{{ $batch['sample_count'] }}</span>
                                                    </td>
                                                    <td class="text-right">
                                                        <button wire:click="removeBatch('{{ $batch['batch_code'] }}')" class="btn btn-sm btn-link text-danger p-0">
                                                            <i class="mdi mdi-delete-outline" style="font-size: 18px;"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="mdi mdi-folder-outline text-muted" style="font-size: 64px; opacity: 0.3;"></i>
                                    <h5 class="text-muted mt-3">No batches selected</h5>
                                    <p class="text-muted small">Please select batches from the Lab Dashboard to proceed.</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($currentStep === 2)
                        <div class="animate-fade-in">
                            <h6 class="text-uppercase text-muted font-weight-bold mb-4 small"><i class="mdi mdi-account-card-outline"></i> Customer Billing Configuration</h6>
                            
                            <div class="card border-0 bg-light-blue mb-4" style="border-radius: 12px; border-left: 4px solid #3b82f6 !important;">
                                <div class="card-body p-3 d-flex align-items-center">
                                    <div class="bg-white rounded-circle p-2 mr-3 shadow-sm">
                                        <i class="mdi mdi-account-outline text-primary" style="font-size: 24px;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-weight-bold">{{ $customer->name }}</h6>
                                        <small class="text-muted">{{ $customer->email }} | Code: {{ $customer->code }}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-group border rounded p-3 bg-white shadow-xs">
                                        <label class="form-label font-weight-bold small text-muted text-uppercase mb-2">
                                            Dynamics Customer Reference <span class="text-danger">*</span>
                                        </label>
                                        <div class="tag-select-container" wire:click="$set('showZohoCustomerDropdown', true)">
                                            <div class="custom-search-pill-container {{ $this->selectedZohoCustomer ? 'has-selection' : '' }}">
                                                @if($this->selectedZohoCustomer)
                                                    <div class="selection-pill">
                                                        <span>{{ $this->selectedZohoCustomer->name }} ({{ $this->selectedZohoCustomer->customer_no }})</span>
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearZohoCustomer()"></i>
                                                    </div>
                                                @else
                                                    <div class="d-flex align-items-center w-100">
                                                        <i class="mdi mdi-account-search text-muted mr-2"></i>
                                                        <input type="text" wire:model.live="zohoCustomerSearch" class="border-0 w-100 outline-none small" placeholder="Search Dynamics contacts...">
                                                    </div>
                                                @endif
                                            </div>
                                            @if($showZohoCustomerDropdown)
                                                <div class="custom-search-dropdown shadow-sm animate-fade-in">
                                                    @forelse($this->filteredZohoCustomers as $zCustomer)
                                                        <div class="dropdown-item-pill" wire:click.stop="selectZohoCustomer('{{ $zCustomer->customer_no }}')">
                                                            <div class="font-weight-bold">{{ $zCustomer->name }}</div>
                                                            <div class="small text-muted">{{ $zCustomer->customer_no }} | {{ $zCustomer->email }}</div>
                                                        </div>
                                                    @empty
                                                        <div class="p-3 text-center text-muted small">No Dynamics customers found...</div>
                                                    @endforelse
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <div class="form-group border rounded p-3 bg-white shadow-xs">
                                        <label class="form-label font-weight-bold small text-muted text-uppercase mb-2">
                                            Billing Currency <span class="text-danger">*</span>
                                        </label>
                                        
                                        @if($zohoCustomerId && !$this->selectedZohoCurrency)
                                            <div class="tag-select-container" wire:click="$set('showZohoCurrencyDropdown', true)">
                                                <div class="custom-search-pill-container">
                                                    <div class="d-flex align-items-center w-100">
                                                        <i class="mdi mdi-currency-usd text-muted mr-2"></i>
                                                        <input type="text" wire:model.live="zohoCurrencySearch" class="border-0 w-100 outline-none small" placeholder="Search currency...">
                                                    </div>
                                                </div>
                                                @if($showZohoCurrencyDropdown)
                                                    <div class="custom-search-dropdown shadow-sm">
                                                        @foreach($this->filteredCurrencies as $curr)
                                                            <div class="dropdown-item-pill" wire:click.stop="selectZohoCurrency({{ $curr->id }})">
                                                                <span class="font-weight-bold">{{ $curr->code }}</span> - {{ $curr->description }}
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center p-2 bg-light rounded border-dashed">
                                                @if($this->selectedZohoCurrency)
                                                    <div class="badge badge-success-soft p-2 mr-2">
                                                        <i class="mdi mdi-cash"></i>
                                                    </div>
                                                    <span class="font-weight-bold">{{ $this->selectedZohoCurrency->code }}</span>
                                                    <span class="text-muted ml-2 small">({{ $this->selectedZohoCurrency->description }})</span>
                                                @else
                                                    <span class="text-muted small">Auto-mapping from Dynamics...</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            @if($zohoCustomerId != $customer->zoho_id || $zohoCurrencyId != $customer->currency_id)
                                <div class="custom-checkbox-banner d-flex align-items-center p-3 border rounded">
                                    <div class="custom-control custom-checkbox mr-3">
                                        <input type="checkbox" wire:model="updateCustomerZohoData" class="custom-control-input" id="updateCustomer">
                                        <label class="custom-control-label" for="updateCustomer"></label>
                                    </div>
                                    <div>
                                        <div class="font-weight-bold small">Update Customer Profile</div>
                                        <div class="text-muted tiny">Save these integration details back to the master record.</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($currentStep === 3)
                        <div class="animate-fade-in">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="text-uppercase text-muted font-weight-bold mb-0 small"><i class="mdi mdi-vector-combine"></i> Service Item Mapping</h6>
                                <button wire:click="autoMapAllAnalyses" class="btn btn-sm btn-ghost-primary">
                                    <i class="mdi mdi-auto-fix"></i> Auto-Map Services
                                </button>
                            </div>
                            
                            <div class="table-responsive rounded border">
                                <table class="table mb-0 workflow-table">
                                    <thead class="bg-light text-muted small text-uppercase">
                                        <tr>
                                            <th>Lab Service</th>
                                            <th class="text-center">Qty</th>
                                            <th>Mapped Billing Items</th>
                                            <th class="text-right">Unit Pricing</th>
                                            <th class="text-right">Row Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($analysisTypesData as $analysisTypeId => $data)
                                            <tr class="{{ empty($analysisMappings[$analysisTypeId]) ? 'bg-amber-50' : '' }}">
                                                <td style="vertical-align: top;">
                                                    <div class="font-weight-bold">{{ $data['name'] }}</div>
                                                    <div class="tiny text-muted uppercase tracking-wider">{{ $data['code'] }}</div>
                                                </td>
                                                <td class="text-center" style="vertical-align: top;">
                                                    <span class="badge badge-primary-soft font-weight-bold">{{ $data['count'] }}</span>
                                                </td>
                                                <td style="min-width: 300px;">
                                                    <div class="service-tag-multi" wire:click="$set('showItemDropdowns.{{ $analysisTypeId }}', true)">
                                                        <div class="tag-render-area">
                                                            @if(isset($analysisMappings[$analysisTypeId]) && is_array($analysisMappings[$analysisTypeId]))
                                                                @foreach($analysisMappings[$analysisTypeId] as $itemId)
                                                                    @php $item = $this->getInvoicableItemById($itemId); @endphp
                                                                    @if($item)
                                                                        <div class="item-tag-pill">
                                                                            <span>{{ $item->item_code }}</span>
                                                                            <i class="mdi mdi-close" wire:click.stop="removeInvoicableItemFromAnalysis({{ $analysisTypeId }}, {{ $item->id }})"></i>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                            <input type="text" wire:model.live="itemSearches.{{ $analysisTypeId }}" class="ghost-input" placeholder="Search billing items...">
                                                        </div>
                                                        @if($showItemDropdowns[$analysisTypeId] ?? false)
                                                            <div class="custom-search-dropdown shadow-lg">
                                                                @foreach($this->filteredItemsForAnalysis as $item)
                                                                    <div class="dropdown-item-pill d-flex justify-content-between" wire:click.stop="selectInvoicableItemForAnalysis({{ $analysisTypeId }}, {{ $item->id }})">
                                                                        <div>
                                                                            <span class="font-weight-bold">{{ $item->item_code }}</span>
                                                                            <div class="tiny text-muted">{{ $item->item_name }}</div>
                                                                        </div>
                                                                        <span class="text-success font-weight-bold">{{ number_format($item->unit_price, 2) }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    @if(!empty($analysisMappings[$analysisTypeId]))
                                                        @foreach($analysisMappings[$analysisTypeId] as $itemId)
                                                            @php $item = $this->getInvoicableItemById($itemId); @endphp
                                                            @if($item)
                                                                <div class="mb-2 pricing-input-group">
                                                                    <div class="tiny text-muted text-truncate" style="max-width: 150px;">{{ $item->item_name }}</div>
                                                                    <input type="number" wire:model.live="analysisCustomPrices.{{ $analysisTypeId }}.{{ $itemId }}" class="form-control form-control-sm text-right" placeholder="{{ number_format($item->unit_price, 2) }}" step="0.01">
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </td>
                                                <td class="text-right font-weight-bold" style="vertical-align: top;">
                                                    @php $rowTotal = 0; @endphp
                                                    @if(!empty($analysisMappings[$analysisTypeId]))
                                                        @foreach($analysisMappings[$analysisTypeId] as $itemId)
                                                            @php 
                                                                $item = $this->getInvoicableItemById($itemId); 
                                                                $unitPrice = $analysisCustomPrices[$analysisTypeId][$itemId] ?? ($item ? $item->unit_price : 0);
                                                                $rowTotal += $unitPrice * $data['count'];
                                                            @endphp
                                                        @endforeach
                                                    @endif
                                                    {{ number_format($rowTotal, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-light font-weight-bold border-top-2">
                                        <tr>
                                            <td colspan="4" class="text-right text-muted text-uppercase small">Subtotal Services:</td>
                                            <td class="text-right text-primary">{{ number_format($this->analysisTotal, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endif

                    @if($currentStep === 4)
                        <div class="animate-fade-in">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="text-uppercase text-muted font-weight-bold mb-0 small"><i class="mdi mdi-cash-plus"></i> Additional Charges & Surcharges</h6>
                                <button wire:click="openAddItemModal" class="btn btn-sm btn-primary shadow-xs">
                                    <i class="mdi mdi-plus-circle"></i> Add Custom Charge
                                </button>
                            </div>
                            
                            @if(count($additionalItems) > 0)
                                <div class="table-responsive rounded border">
                                    <table class="table mb-0 workflow-table">
                                        <thead class="bg-light text-muted small text-uppercase">
                                            <tr>
                                                <th>Code</th>
                                                <th>Description</th>
                                                <th width="120">Quantity</th>
                                                <th class="text-right" width="180">Unit Price</th>
                                                <th class="text-right" width="180">Total</th>
                                                <th class="text-center" width="80"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($additionalItems as $index => $item)
                                                <tr>
                                                    <td class="font-weight-bold text-muted small">{{ $item['item_code'] }}</td>
                                                    <td class="font-weight-bold">{{ $item['item_name'] }}</td>
                                                    <td>
                                                        <input type="number" wire:model.live="additionalItems.{{ $index }}.quantity" class="form-control form-control-sm text-center" min="1">
                                                    </td>
                                                    <td>
                                                        <input type="number" wire:model.live="additionalItems.{{ $index }}.unit_price" class="form-control form-control-sm text-right" step="0.01">
                                                    </td>
                                                    <td class="text-right font-weight-bold">
                                                        {{ number_format($item['unit_price'] * $item['quantity'], 2) }}
                                                    </td>
                                                    <td class="text-center">
                                                        <button wire:click="removeAdditionalItem({{ $index }})" class="btn btn-sm text-danger p-0">
                                                            <i class="mdi mdi-close-circle-outline" style="font-size: 18px;"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-light font-weight-bold">
                                            <tr>
                                                <td colspan="4" class="text-right text-muted text-uppercase small">Subtotal Extras:</td>
                                                <td class="text-right text-primary">{{ number_format($this->additionalItemsTotal, 2) }}</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 border-dashed rounded bg-light">
                                    <i class="mdi mdi-tray-plus text-muted mb-3" style="font-size: 48px; opacity: 0.2;"></i>
                                    <div class="text-muted font-weight-bold">No additional charges added.</div>
                                    <p class="text-muted small">Use the "Add Custom Charge" button to include logistic fees, urgent testing surcharges, etc.</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($currentStep === 5)
                        <div class="animate-fade-in">
                            <h6 class="text-uppercase text-muted font-weight-bold mb-4 small"><i class="mdi mdi-file-find"></i> Draft Invoice Final Review</h6>
                            
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="card border mb-4 shadow-xs">
                                        <div class="card-header bg-white border-bottom py-3">
                                            <h6 class="mb-0 font-weight-bold"><i class="mdi mdi-clipboard-text mr-2 text-primary"></i> Order Line Items</h6>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="bg-light small text-muted">
                                                    <tr>
                                                        <th class="pl-3 py-2">Service Description</th>
                                                        <th class="text-center py-2">Qty</th>
                                                        <th class="text-right py-2">Rate</th>
                                                        <th class="text-right pr-3 py-2">Ext Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($this->invoicePreview as $line)
                                                        <tr>
                                                            <td class="pl-3 py-3">
                                                                <div class="font-weight-bold">{{ $line['description'] }}</div>
                                                                <div class="tiny text-muted uppercase">{{ $line['type'] }} ITEM</div>
                                                            </td>
                                                            <td class="text-center py-3">{{ $line['quantity'] }}</td>
                                                            <td class="text-right py-3">{{ number_format($line['unit_price'], 2) }}</td>
                                                            <td class="text-right pr-3 py-3 font-weight-bold">{{ number_format($line['total'], 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="card border shadow-xs mb-4">
                                        <div class="card-header bg-primary text-white py-3">
                                            <h6 class="mb-0 font-weight-bold">Summary & Totals</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                                <span class="text-muted">Subtotal:</span>
                                                <span class="font-weight-bold">{{ number_format($this->subtotal, 2) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                                <span class="text-muted">VAT ({{ $this->taxRate }}%):</span>
                                                <span class="font-weight-bold">{{ number_format($this->taxAmount, 2) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-4 mt-2">
                                                <span class="h6 font-weight-bold">Grand Total:</span>
                                                <span class="h5 font-weight-bold text-primary">{{ number_format($this->grandTotal, 2) }} {{ $this->selectedZohoCurrency->code ?? '' }}</span>
                                            </div>

                                            <div class="customer-preview p-3 bg-light rounded shadow-xs mb-3">
                                                <div class="tiny text-uppercase text-muted font-weight-bold mb-1">Billing Entity</div>
                                                <div class="font-weight-bold small text-truncate">{{ $this->selectedZohoCustomer->name ?? $customer->name }}</div>
                                                <div class="tiny text-muted">{{ $this->selectedZohoCustomer->customer_no ?? 'ID Pending' }}</div>
                                            </div>

                                            <div class="alert bg-success-soft border-0 p-3">
                                                <div class="d-flex align-items-center mb-1">
                                                    <i class="mdi mdi-alert-decagram text-success mr-2"></i>
                                                    <span class="font-weight-bold text-success small">Ready for Export</span>
                                                </div>
                                                <p class="tiny text-muted mb-0">System will assign SO reference <strong>FV-S-{{ $this->nextInvoiceNumber }}</strong> upon finalization.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="workflow-board-panel-footer bg-light p-3 border-top d-flex justify-content-between">
                <div>
                    @if($currentStep > 1)
                        <button wire:click="previousStep" class="btn btn-light border px-4 shadow-xs">
                            <i class="mdi mdi-chevron-left mr-1"></i> Back
                        </button>
                    @endif
                </div>
                <div>
                    @if($currentStep < 5)
                        <button wire:click="nextStep" class="btn btn-primary px-5 shadow-xs">
                            Continue <i class="mdi mdi-chevron-right ml-1"></i>
                        </button>
                    @else
                        <button wire:click="generateSalesOrder" class="btn btn-success px-5 shadow-xs font-weight-bold">
                            <i class="mdi mdi-check-bold mr-1"></i> Confirm & Generate Order
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Item Selection Modal -->
    @if($showAddItemModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.4); backdrop-filter: blur(2px);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border shadow-xs" style="border-radius: 12px;">
                    <div class="modal-header border-bottom py-3 bg-light">
                        <h5 class="modal-title font-weight-bold"><i class="mdi mdi-magnify mr-2"></i> Find Billing Item</h5>
                        <button type="button" class="close" wire:click="closeAddItemModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="p-4 bg-light border-bottom">
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="mdi mdi-database-search text-muted"></i></span>
                                </div>
                                <input type="text" wire:model.live="additionalItemSearch" class="form-control border-left-0 pl-0" placeholder="Type item code or name..." autofocus>
                            </div>
                        </div>
                        <div class="item-list-container" style="max-height: 400px; overflow-y: auto;">
                            @forelse($this->nonAnalysisItems as $item)
                                <div class="item-selection-row d-flex align-items-center justify-content-between p-3 border-bottom" wire:click="addAdditionalItem({{ $item->id }})">
                                    <div>
                                        <div class="font-weight-bold">{{ $item->item_code }}</div>
                                        <div class="small text-muted">{{ $item->item_name }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="h6 mb-0 font-weight-bold text-success">{{ number_format($item->unit_price, 2) }}</div>
                                        <div class="tiny text-muted uppercase tracking-wider">{{ $item->item_type ?? 'SERVICE' }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="mdi mdi-magnify-close mb-2 text-muted" style="font-size: 40px; opacity: 0.3;"></i>
                                    <div class="text-muted small">No items found matching your search.</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        /* Modern Layout Tokens */
        .bg-light-blue { background-color: #f0f7ff; }
        .bg-success-soft { background-color: #ecfdf5; }
        .bg-primary-soft { background-color: #eff6ff; }
        .bg-danger-light { background-color: #fef2f2; }
        .bg-amber-50 { background-color: #fffbeb; }
        .shadow-xs { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
        .tiny { font-size: 0.7rem; }
        .border-dashed { border: 1.5px dashed #e2e8f0; }

        /* Wizard Nav Styling */
        .wizard-step-item { display: flex; flex-direction: column; align-items: center; z-index: 10; cursor: default; }
        .step-circle { width: 36px; height: 36px; border-radius: 50%; background: #ffffff; color: #9ca3af; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid #e5e7eb; transition: all 0.3s; }
        .step-label { font-size: 0.75rem; font-weight: 600; color: #9ca3af; margin-top: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .step-connector { height: 2px; background: #e5e7eb; flex: 1; margin: 0 -5px; position: relative; top: -14px; }
        
        .wizard-step-item.active .step-circle { background: #3b82f6; color: white; border-color: #3b82f6; transform: scale(1.1); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2); }
        .wizard-step-item.active .step-label { color: #3b82f6; }
        
        .wizard-step-item.completed .step-circle { background: #10b981; color: white; border-color: #10b981; }
        .wizard-step-item.completed .step-label { color: #10b981; }
        .step-connector.completed { background: #10b981; }

        /* Tag Select Custom */
        .custom-search-pill-container { min-height: 42px; padding: 6px 12px; border: 1px solid #ced4da; border-radius: 8px; transition: all 0.2s; position: relative; }
        .custom-search-pill-container:hover { border-color: #3b82f6; background-color: #f9fafb; }
        .custom-search-pill-container.has-selection { background-color: #f0f9ff; border-color: #0ea5e9; }
        
        .selection-pill { display: inline-flex; align-items: center; background: #0ea5e9; color: white; padding: 4px 12px; border-radius: 50px; font-size: 0.85rem; font-weight: 500; }
        .selection-pill i { cursor: pointer; margin-left: 8px; opacity: 0.8; }
        .selection-pill i:hover { opacity: 1; }

        .service-tag-multi { min-height: 48px; border: 1px inset #e2e8f0; border-radius: 6px; padding: 6px; background: transparent; position: relative; cursor: text; }
        .tag-render-area { display: flex; flex-wrap: wrap; gap: 4px; }
        .item-tag-pill { background: #3b82f6; color: white; padding: 2px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; display: flex; align-items: center; }
        .item-tag-pill i { margin-left: 6px; cursor: pointer; opacity: 0.8; }
        .ghost-input { border: 0; outline: none; background: transparent; font-size: 0.8rem; padding: 4px; flex-grow: 1; min-width: 100px; }

        /* Dropdowns */
        .custom-search-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: white; border-radius: 0 0 12px 12px; border: 1px solid #e2e8f0; z-index: 1000; max-height: 250px; overflow-y: auto; margin-top: 4px; }
        .dropdown-item-pill { padding: 10px 16px; cursor: pointer; transition: background 0.2s; border-bottom: 1px solid #f1f5f9; }
        .dropdown-item-pill:hover { background-color: #f0f9ff; }
        .dropdown-item-pill:last-child { border-bottom: 0; }

        /* Modal Row Selection */
        .item-selection-row { cursor: pointer; transition: all 0.1s; }
        .item-selection-row:hover { background-color: #f0fdf4; transform: scale(1.005); border-left: 3px solid #22c55e !important; }

        /* Helper Classes */
        .outline-none { outline: none !important; }
        .btn-ghost-primary { color: #3b82f6; background: transparent; border: 1px solid transparent; transition: all 0.2s; }
        .btn-ghost-primary:hover { background: #eff6ff; border-color: #3b82f6; }
        .animate-fade-in { animation: fadeIn 0.3s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        
        .pricing-input-group input { height: 32px; border-radius: 4px; font-weight: 600; color: #1e293b; }

        .wizard-cancel-btn {
            background: transparent;
            border: 1px solid #fecaca;
            color: #f87171;
            padding: 8px 16px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s ease;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            line-height: 1;
            cursor: pointer;
            white-space: nowrap;
        }

        .wizard-cancel-btn:hover {
            background: #fff1f2;
            border-color: #ef4444;
            color: #dc2626;
            text-decoration: none;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.1);
        }

        .wizard-cancel-btn i {
            font-size: 16px;
            margin-right: 4px;
        }
    </style>

    @script
    <script>
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.tag-select-container') && !e.target.closest('.service-tag-multi')) {
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
