<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0 font-weight-bold"><i class="fab fa-whatsapp text-success mr-2"></i> WhatsApp Cloud API Configuration</h4>
                <div class="page-title-right">
                    <span class="badge badge-soft-success p-2"><i class="mdi mdi-shield-check mr-1"></i> Multi-Tenant Isolated Channel</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if($successMessage)
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-left: 4px solid #25D366 !important;">
            <i class="mdi mdi-check-circle mr-2"></i> {{ $successMessage }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" wire:click="$set('successMessage', '')">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errorMessage)
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-left: 4px solid #f46a6a !important;">
            <i class="mdi mdi-alert-circle mr-2"></i> {{ $errorMessage }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" wire:click="$set('errorMessage', '')">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Main Card -->
    <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom p-0">
            <!-- Sleek Horizontal Navigation Tabs -->
            <ul class="nav nav-tabs nav-tabs-custom nav-justified mb-0" role="tablist" style="border-bottom: none;">
                <li class="nav-item">
                    <a class="nav-link py-3 {{ $currentTab === 'account' ? 'active text-success' : 'text-muted' }}" href="javascript:void(0);" wire:click="setTab('account')" style="font-weight: 600; border-top: 3px solid transparent; {{ $currentTab === 'account' ? 'border-top-color: #25D366; background-color: #f8f9fa;' : '' }}">
                        <i class="fas fa-cog mr-2"></i> Account settings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 {{ $currentTab === 'templates' ? 'active text-success' : 'text-muted' }}" href="javascript:void(0);" wire:click="setTab('templates')" style="font-weight: 600; border-top: 3px solid transparent; {{ $currentTab === 'templates' ? 'border-top-color: #25D366; background-color: #f8f9fa;' : '' }}">
                        <i class="fas fa-file-code mr-2"></i> Templates
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 {{ $currentTab === 'mappings' ? 'active text-success' : 'text-muted' }}" href="javascript:void(0);" wire:click="setTab('mappings')" style="font-weight: 600; border-top: 3px solid transparent; {{ $currentTab === 'mappings' ? 'border-top-color: #25D366; background-color: #f8f9fa;' : '' }}">
                        <i class="fas fa-link mr-2"></i> Event Mapping
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 {{ $currentTab === 'audiences' ? 'active text-success' : 'text-muted' }}" href="javascript:void(0);" wire:click="setTab('audiences')" style="font-weight: 600; border-top: 3px solid transparent; {{ $currentTab === 'audiences' ? 'border-top-color: #25D366; background-color: #f8f9fa;' : '' }}">
                        <i class="fas fa-users mr-2"></i> Target Audiences
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 {{ $currentTab === 'campaigns' ? 'active text-success' : 'text-muted' }}" href="javascript:void(0);" wire:click="setTab('campaigns')" style="font-weight: 600; border-top: 3px solid transparent; {{ $currentTab === 'campaigns' ? 'border-top-color: #25D366; background-color: #f8f9fa;' : '' }}">
                        <i class="fas fa-bullhorn mr-2"></i> Campaigns
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 {{ $currentTab === 'messages' ? 'active text-success' : 'text-muted' }}" href="javascript:void(0);" wire:click="setTab('messages')" style="font-weight: 600; border-top: 3px solid transparent; {{ $currentTab === 'messages' ? 'border-top-color: #25D366; background-color: #f8f9fa;' : '' }}">
                        <i class="fas fa-paper-plane mr-2"></i> Outbound Messages
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 {{ $currentTab === 'chats' ? 'active text-success' : 'text-muted' }}" href="javascript:void(0);" wire:click="setTab('chats')" style="font-weight: 600; border-top: 3px solid transparent; {{ $currentTab === 'chats' ? 'border-top-color: #25D366; background-color: #f8f9fa;' : '' }}">
                        <i class="fas fa-comments mr-2"></i> Two-Way Chats
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 {{ $currentTab === 'logs' ? 'active text-success' : 'text-muted' }}" href="javascript:void(0);" wire:click="setTab('logs')" style="font-weight: 600; border-top: 3px solid transparent; {{ $currentTab === 'logs' ? 'border-top-color: #25D366; background-color: #f8f9fa;' : '' }}">
                        <i class="fas fa-history mr-2"></i> Webhook & Activity Logs
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body p-4 bg-light">
            <!-- TAB 1: Account Settings -->
            @if($currentTab === 'account')
                <div class="row">
                    <div class="col-md-7">
                        <div class="card border-0 shadow-sm p-4" style="border-radius: 8px;">
                            <h5 class="mb-4 font-weight-bold text-dark"><i class="fas fa-key text-success mr-2"></i> Meta Developer API Credentials</h5>
                            <form wire:submit.prevent="saveAccount">
                                <div class="form-group mb-3">
                                    <label class="font-weight-semibold text-muted mb-1">Sender WhatsApp Phone Number</label>
                                    <input type="text" class="form-control" wire:model.defer="sender_phone_number" placeholder="e.g. +254700000000" style="border-radius: 6px;">
                                    @error('sender_phone_number') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-semibold text-muted mb-1">WhatsApp Phone Number ID</label>
                                    <input type="text" class="form-control" wire:model.defer="phone_number_id" placeholder="e.g. 1048293928129" style="border-radius: 6px;">
                                    @error('phone_number_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                    <small class="text-muted">Obtained from App Dashboard > WhatsApp > API Setup</small>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-semibold text-muted mb-1">WhatsApp Business Account ID (WABA ID)</label>
                                    <input type="text" class="form-control" wire:model.defer="waba_id" placeholder="e.g. 1092837482918" style="border-radius: 6px;">
                                    @error('waba_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-semibold text-muted mb-1">Permanent System User Access Token</label>
                                    <textarea class="form-control" wire:model.defer="access_token" rows="3" placeholder="EAABw..." style="border-radius: 6px; font-family: monospace; font-size: 13px;"></textarea>
                                    @error('access_token') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label class="font-weight-semibold text-muted mb-1">Webhook Verification Token (Verify Token)</label>
                                    <input type="text" class="form-control bg-light" wire:model="webhook_verify_token" readonly style="border-radius: 6px; font-family: monospace;">
                                    @error('webhook_verify_token') <span class="text-danger small">{{ $message }}</span> @enderror
                                    <small class="text-muted">Use this token when registering your Webhook URL in Meta App settings.</small>
                                </div>

                                <button type="submit" class="btn btn-success px-4 py-2" style="border-radius: 6px; background-color: #25D366; border-color: #25D366; font-weight: 600;">
                                    <i class="fas fa-save mr-1"></i> Save Configuration
                                </button>

                                @if($isAccountConfigured)
                                    <button type="button" class="btn btn-outline-success px-4 py-2 ml-2" wire:click="validateConnection" style="border-radius: 6px; font-weight: 600;" wire:loading.attr="disabled">
                                        <i class="fas fa-check-double mr-1" wire:loading.remove wire:target="validateConnection"></i>
                                        <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true" wire:loading wire:target="validateConnection"></span>
                                        Validate Connection
                                    </button>
                                @endif
                            </form>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card border-0 shadow-sm p-4 text-white" style="border-radius: 8px; background: linear-gradient(135deg, #128C7E, #075E54);">
                            <h5 class="font-weight-bold mb-3"><i class="mdi mdi-information-outline"></i> Meta Webhook Setup Guide</h5>
                            <ol class="pl-3 mb-4" style="line-height: 1.8;">
                                <li>Create a Meta App of type <strong>Business</strong>.</li>
                                <li>Navigate to <strong>WhatsApp > Setup</strong>.</li>
                                <li>Configure Webhooks: set Callback URL to <br><code class="text-warning font-weight-bold" style="word-break: break-all;">{{ route('api.whatsapp.webhook') }}</code></li>
                                <li>Paste the <strong>Webhook Verification Token</strong> shown on the left into the Meta Verify Token field.</li>
                                <li>Subscribe to <strong>messages</strong> and <strong>message_template_status_update</strong> webhook events.</li>
                            </ol>
                            <div class="border-top pt-3 border-light">
                                <span class="badge badge-light py-2 px-3 text-dark font-weight-bold">Status: {{ $isAccountConfigured ? 'CONNECTED' : 'DISCONNECTED' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- TAB 2: Templates -->
            @if($currentTab === 'templates')
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm p-4" style="border-radius: 8px;">
                            <h5 class="mb-4 font-weight-bold text-dark"><i class="fas fa-plus-circle text-success mr-2"></i> Register New Template</h5>
                            <form wire:submit.prevent="submitTemplate">
                                <div class="form-group mb-3">
                                    <label class="font-weight-semibold text-muted mb-1">Template Name (Lowercase & underscore only)</label>
                                    <input type="text" class="form-control" wire:model.defer="tpl_name" placeholder="e.g. lab_result_ready" style="border-radius: 6px;">
                                    @error('tpl_name') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-semibold text-muted mb-1">Category</label>
                                    <select class="form-control" wire:model.defer="tpl_category" style="border-radius: 6px;">
                                        <option value="UTILITY">Utility / Transactional</option>
                                        <option value="MARKETING">Marketing / Broadcast</option>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-semibold text-muted mb-1">Language Code</label>
                                    <input type="text" class="form-control" wire:model.defer="tpl_language" placeholder="en" style="border-radius: 6px;">
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-semibold text-muted mb-1">Header Type</label>
                                    <select class="form-control" wire:model="tpl_header_type" style="border-radius: 6px;">
                                        <option value="NONE">None</option>
                                        <option value="TEXT">Text</option>
                                        <option value="IMAGE">Image</option>
                                        <option value="VIDEO">Video</option>
                                        <option value="DOCUMENT">Document (e.g. PDF Report)</option>
                                    </select>
                                </div>

                                @if($tpl_header_type === 'TEXT')
                                    <div class="form-group mb-3">
                                        <label class="font-weight-semibold text-muted mb-1">Header Content</label>
                                        <input type="text" class="form-control" wire:model.defer="tpl_header_content" placeholder="e.g. Laboratory Report Notification" style="border-radius: 6px;">
                                    </div>
                                @endif

                                <div class="form-group mb-3">
                                    <label class="font-weight-semibold text-muted mb-1">Body Content (Use \{\{1\}\}, \{\{2\}\} for parameters)</label>
                                    <textarea class="form-control" wire:model.defer="tpl_body_content" rows="4" placeholder="Dear {{1}}, your lab result for reference {{2}} is ready." style="border-radius: 6px;"></textarea>
                                    @error('tpl_body_content') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-semibold text-muted mb-1">Footer Content</label>
                                    <input type="text" class="form-control" wire:model.defer="tpl_footer_content" placeholder="e.g. Thank you for choosing Imara LIMS" style="border-radius: 6px;">
                                </div>

                                <!-- Dynamic Buttons Wrapper -->
                                <div class="mb-4">
                                    <label class="font-weight-semibold text-muted mb-1 d-flex justify-content-between align-items-center">
                                        Quick Reply Buttons
                                        <button type="button" class="btn btn-sm btn-outline-success py-1" wire:click="addButton" style="border-radius: 4px;"><i class="fas fa-plus"></i> Add Button</button>
                                    </label>
                                    @foreach($tpl_buttons as $index => $btn)
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control form-control-sm" wire:model.defer="tpl_buttons.{{ $index }}.text" placeholder="Button Text" style="border-radius: 6px 0 0 6px;">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-sm btn-danger" wire:click="removeButton({{ $index }})" style="border-radius: 0 6px 6px 0;"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <button type="submit" class="btn btn-success btn-block py-2" style="border-radius: 6px;">
                                    <i class="fas fa-paper-plane mr-1"></i> Register & Submit to Meta
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm p-4" style="border-radius: 8px;">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-list-ul text-success mr-2"></i> Registered Message Templates</h5>
                                <button type="button" class="btn btn-sm btn-outline-primary px-3 py-2" wire:click="syncTemplates" style="border-radius: 6px;">
                                    <i class="fas fa-sync mr-1"></i> Sync Status with Meta
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Template Name</th>
                                            <th>Category</th>
                                            <th>Language</th>
                                            <th>Variables</th>
                                            <th>Status</th>
                                            <th>Meta ID</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($templates as $tmpl)
                                            <tr>
                                                <td class="font-weight-semibold text-dark">{{ $tmpl->name }}</td>
                                                <td><span class="badge badge-soft-primary px-2 py-1">{{ $tmpl->category }}</span></td>
                                                <td><code>{{ $tmpl->language }}</code></td>
                                                <td>
                                                    @if(!empty($tmpl->variables_json))
                                                        @foreach($tmpl->variables_json as $var)
                                                            <span class="badge badge-soft-secondary mb-1">\{\{ {{ $var['index'] }} \}\}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted small">None</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($tmpl->status === 'APPROVED')
                                                        <span class="badge badge-success px-2 py-1"><i class="mdi mdi-checkbox-marked-circle-outline"></i> APPROVED</span>
                                                    @elseif($tmpl->status === 'PENDING')
                                                        <span class="badge badge-warning px-2 py-1 text-dark"><i class="mdi mdi-timer-sand"></i> PENDING</span>
                                                    @else
                                                        <span class="badge badge-danger px-2 py-1"><i class="mdi mdi-alert-outline"></i> {{ $tmpl->status }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-muted small" style="font-family: monospace;">{{ $tmpl->meta_template_id ?? 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">No templates registered yet. Use the sync button or register form.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- TAB 3: Event Mapping -->
            @if($currentTab === 'mappings')
                <div class="row">
                    <div class="col-md-5">
                        <div class="card border-0 shadow-sm p-4" style="border-radius: 8px;">
                            <h5 class="mb-4 font-weight-bold text-dark"><i class="fas fa-link text-success mr-2"></i> Map LIMS Event to Template</h5>
                            <form wire:submit.prevent="saveEventMapping">
                                <div class="form-group mb-3">
                                    <label class="font-weight-semibold text-muted mb-1">Select System Event</label>
                                    <select class="form-control" wire:model.defer="map_event_code" style="border-radius: 6px;">
                                        <option value="">-- Choose Event --</option>
                                        @foreach($availableEvents as $code => $label)
                                            <option value="{{ $code }}">{{ $label }} ({{ $code }})</option>
                                        @endforeach
                                    </select>
                                    @error('map_event_code') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-semibold text-muted mb-1">Mapped Template (Only Approved templates)</label>
                                    <select class="form-control" wire:model.defer="map_template_id" style="border-radius: 6px;">
                                        <option value="">-- Choose Template --</option>
                                        @foreach($templates as $tmpl)
                                            @if($tmpl->status === 'APPROVED')
                                                <option value="{{ $tmpl->id }}">{{ $tmpl->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('map_template_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="mapActiveSwitch" wire:model.defer="map_active">
                                        <label class="custom-control-label font-weight-semibold text-muted" for="mapActiveSwitch">Activate Mapping Immediately</label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success px-4 py-2" style="border-radius: 6px;">
                                    <i class="fas fa-check mr-1"></i> Save Event Mapping
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="card border-0 shadow-sm p-4" style="border-radius: 8px;">
                            <h5 class="mb-4 font-weight-bold text-dark"><i class="fas fa-exchange-alt text-success mr-2"></i> Active Event Mappings</h5>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Event Code</th>
                                            <th>Mapped Template</th>
                                            <th>Target Variables</th>
                                            <th>Active</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($mappings as $map)
                                            <tr>
                                                <td class="font-weight-bold text-dark">{{ $map->event_code }}</td>
                                                <td class="text-primary">{{ $map->template->name }}</td>
                                                <td>
                                                    @if(!empty($map->template->variables_json))
                                                        @foreach($map->template->variables_json as $var)
                                                            <span class="badge badge-soft-dark mb-1">\{\{ {{ $var['index'] }} \}\}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted small">None</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-pill {{ $map->active ? 'badge-soft-success' : 'badge-soft-danger' }} px-2 py-1">
                                                        {{ $map->active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-secondary" wire:click="toggleMapping({{ $map->id }})" style="border-radius: 4px;">
                                                        Toggle
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">No mappings defined yet. Define mappings to enable auto notifications.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- TAB 4: Target Audiences -->
            @if($currentTab === 'audiences')
                <div class="row">
                    <div class="col-md-5">
                        <div class="card border-0 shadow-sm p-4" style="border-radius: 8px;">
                            <h5 class="mb-4 font-weight-bold text-dark"><i class="fas fa-users-cog text-success mr-2"></i> Create Customer Audience</h5>
                            <form wire:submit.prevent="saveAudience">
                                <div class="form-group mb-3">
                                    <label class="font-weight-semibold text-muted mb-1">Audience Name</label>
                                    <input type="text" class="form-control" wire:model.defer="aud_name" placeholder="e.g. Domestic Corporate Clients" style="border-radius: 6px;">
                                    @error('aud_name') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-semibold text-muted mb-1">Filter by Customer Country</label>
                                    <select class="form-control" wire:model.defer="aud_country_id" style="border-radius: 6px;">
                                        <option value="">-- All Countries --</option>
                                        @foreach($countries as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mb-4">
                                    <label class="font-weight-semibold text-muted mb-1">Customer Group Type</label>
                                    <select class="form-control" wire:model.defer="aud_is_internal" style="border-radius: 6px;">
                                        <option value="">All Groups</option>
                                        <option value="0">External Customers / Clients</option>
                                        <option value="1">Internal / Laboratory Users</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-success px-4 py-2" style="border-radius: 6px;">
                                    <i class="fas fa-filter mr-1"></i> Compile Audience
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="card border-0 shadow-sm p-4" style="border-radius: 8px;">
                            <h5 class="mb-4 font-weight-bold text-dark"><i class="fas fa-user-friends text-success mr-2"></i> Registered Audiences</h5>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Audience Name</th>
                                            <th>Filter Criteria</th>
                                            <th>Created At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($audiences as $aud)
                                            <tr>
                                                <td class="font-weight-bold text-dark">{{ $aud->name }}</td>
                                                <td>
                                                    @if(!empty($aud->rules_json))
                                                        @foreach($aud->rules_json as $k => $v)
                                                            <span class="badge badge-soft-secondary">{{ $k }}: {{ is_bool($v) ? ($v ? 'YES' : 'NO') : $v }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="badge badge-soft-success">All Active Contacts</span>
                                                    @endif
                                                </td>
                                                <td class="text-muted small">{{ $aud->created_at->toDateTimeString() }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted">No audience definitions configured yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- TAB 5: Campaigns -->
            @if($currentTab === 'campaigns')
                <div class="row">
                    <div class="col-md-5">
                        <div class="card border-0 shadow-sm p-4" style="border-radius: 8px;">
                            <h5 class="mb-4 font-weight-bold text-dark"><i class="fas fa-bullhorn text-success mr-2"></i> Create Notification Campaign</h5>
                            <form wire:submit.prevent="saveCampaign">
                                <div class="form-group mb-3">
                                    <label class="font-weight-semibold text-muted mb-1">Campaign Name</label>
                                    <input type="text" class="form-control" wire:model.defer="camp_name" placeholder="e.g. Laboratory Anniversary Broadcast" style="border-radius: 6px;">
                                    @error('camp_name') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label class="font-weight-semibold text-muted mb-1">Target Audience</label>
                                    <select class="form-control" wire:model.defer="camp_audience_id" style="border-radius: 6px;">
                                        <option value="">-- Select Audience --</option>
                                        @foreach($audiences as $aud)
                                            <option value="{{ $aud->id }}">{{ $aud->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('camp_audience_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label class="font-weight-semibold text-muted mb-1">Message Template</label>
                                    <select class="form-control" wire:model.defer="camp_template_id" style="border-radius: 6px;">
                                        <option value="">-- Choose Template --</option>
                                        @foreach($templates as $tmpl)
                                            @if($tmpl->status === 'APPROVED')
                                                <option value="{{ $tmpl->id }}">{{ $tmpl->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('camp_template_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <button type="submit" class="btn btn-success px-4 py-2" style="border-radius: 6px;">
                                    <i class="fas fa-plus mr-1"></i> Initialize Campaign
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="card border-0 shadow-sm p-4" style="border-radius: 8px;">
                            <h5 class="mb-4 font-weight-bold text-dark"><i class="fas fa-bullhorn text-success mr-2"></i> Campaigns Registry</h5>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Campaign Name</th>
                                            <th>Audience</th>
                                            <th>Template</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($campaigns as $camp)
                                            <tr>
                                                <td class="font-weight-bold text-dark">{{ $camp->name }}</td>
                                                <td><span class="text-primary">{{ $camp->audience->name }}</span></td>
                                                <td><code>{{ $camp->template->name }}</code></td>
                                                <td>
                                                    @if($camp->status === 'COMPLETED')
                                                        <span class="badge badge-success px-2 py-1">COMPLETED</span>
                                                    @elseif($camp->status === 'RUNNING')
                                                        <span class="badge badge-warning px-2 py-1 text-dark"><i class="fas fa-spinner fa-spin mr-1"></i> RUNNING</span>
                                                    @elseif($camp->status === 'QUEUED')
                                                        <span class="badge badge-secondary px-2 py-1">QUEUED</span>
                                                    @else
                                                        <span class="badge badge-light px-2 py-1">{{ $camp->status }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($camp->status === 'DRAFT')
                                                        <button class="btn btn-sm btn-success py-1 px-3" wire:click="triggerCampaign({{ $camp->id }})" style="border-radius: 4px;">
                                                            Launch
                                                        </button>
                                                    @elseif($camp->status === 'FAILED')
                                                        <button class="btn btn-sm btn-danger py-1 px-3" wire:click="retryCampaign({{ $camp->id }})" style="border-radius: 4px;">
                                                            Retry
                                                        </button>
                                                    @else
                                                        <span class="text-muted small">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">No campaigns defined yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- TAB: Outbound Messages -->
            @if($currentTab === 'messages')
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm p-4" style="border-radius: 8px;">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-paper-plane text-success mr-2"></i> Outbound Messages Log</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Recipient</th>
                                            <th>Event</th>
                                            <th>Template ID</th>
                                            <th>Status</th>
                                            <th>Attempts</th>
                                            <th>Error</th>
                                            <th>Sent At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($outboundMessages as $msg)
                                            <tr>
                                                <td class="font-weight-bold text-dark">{{ $msg->recipient }}</td>
                                                <td><span class="badge badge-soft-info">{{ $msg->event_code }}</span></td>
                                                <td><code>{{ $msg->provider_template_id }}</code></td>
                                                <td>
                                                    @if($msg->status === 'SENT' || $msg->status === 'DELIVERED' || $msg->status === 'READ')
                                                        <span class="badge badge-success px-2 py-1"><i class="mdi mdi-checkbox-marked-circle-outline"></i> {{ $msg->status }}</span>
                                                    @elseif($msg->status === 'queued')
                                                        <span class="badge badge-warning px-2 py-1 text-dark"><i class="mdi mdi-timer-sand"></i> queued</span>
                                                    @else
                                                        <span class="badge badge-danger px-2 py-1"><i class="mdi mdi-alert-circle-outline"></i> {{ $msg->status }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $msg->attempts }}</td>
                                                <td class="text-danger small">{{ $msg->error ?: '-' }}</td>
                                                <td class="text-muted small">{{ $msg->created_at->toDateTimeString() }}</td>
                                                <td>
                                                    @if($msg->status === 'FAILED')
                                                        <button class="btn btn-sm btn-outline-success" wire:click="retryMessage({{ $msg->id }})" style="border-radius: 4px;">
                                                            <i class="fas fa-redo mr-1"></i> Retry
                                                        </button>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-4 text-muted">No outbound WhatsApp messages found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $outboundMessages->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- TAB 6: Logs -->
            @if($currentTab === 'logs')
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card border-0 shadow-sm p-4" style="border-radius: 8px;">
                            <h5 class="mb-4 font-weight-bold text-dark"><i class="fas fa-exchange-alt text-success mr-2"></i> WhatsApp Activity & Transactions Log</h5>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Type</th>
                                            <th>Details / Raw Payload</th>
                                            <th>Logged At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($activityLogs as $log)
                                            <tr>
                                                <td>
                                                    @if($log->activity_type === 'request')
                                                        <span class="badge badge-soft-info px-2 py-1">REQUEST</span>
                                                    @elseif($log->activity_type === 'response')
                                                        <span class="badge badge-soft-success px-2 py-1">RESPONSE</span>
                                                    @else
                                                        <span class="badge badge-soft-danger px-2 py-1">FAILURE</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <pre class="bg-light p-2 rounded small text-dark mb-0" style="max-height: 120px; overflow-y: auto; font-family: monospace;">{{ json_encode($log->payload, JSON_PRETTY_PRINT) }}</pre>
                                                </td>
                                                <td class="text-muted small">{{ $log->created_at->toDateTimeString() }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted">No activity logs recorded yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $activityLogs->links() }}
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm p-4" style="border-radius: 8px;">
                            <h5 class="mb-4 font-weight-bold text-dark"><i class="fas fa-satellite-dish text-success mr-2"></i> Incoming Webhook Events Queue</h5>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Event Field</th>
                                            <th>Status</th>
                                            <th>Payload Preview</th>
                                            <th>Received At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($webhookEvents as $evt)
                                            <tr>
                                                <td class="font-weight-semibold text-primary"><code>{{ $evt->event_type ?? 'generic' }}</code></td>
                                                <td>
                                                    @if($evt->processed)
                                                        <span class="badge badge-soft-success px-2 py-1"><i class="mdi mdi-checkbox-marked-circle-outline"></i> Processed</span>
                                                    @else
                                                        <span class="badge badge-soft-warning px-2 py-1 text-dark"><i class="mdi mdi-timer-sand"></i> Queued</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <pre class="bg-light p-2 rounded small text-dark mb-0" style="max-height: 120px; overflow-y: auto; font-family: monospace;">{{ json_encode($evt->payload_json, JSON_PRETTY_PRINT) }}</pre>
                                                </td>
                                                <td class="text-muted small">{{ $evt->created_at->toDateTimeString() }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">No incoming webhook events received yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $webhookEvents->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- TAB 7: Two-Way Chats -->
            @if($currentTab === 'chats')
                <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 8px; background-color: #fff; min-height: 550px;">
                    <div class="row no-gutters" style="height: 550px;">
                        <!-- Conversations Sidebar -->
                        <div class="col-md-4 border-right d-flex flex-column" style="background-color: #fcfdfc; height: 100%;">
                            <div class="p-3 bg-light border-bottom">
                                <h6 class="font-weight-bold mb-0 text-dark"><i class="fas fa-list text-success mr-2"></i> Active Chats</h6>
                            </div>
                            <div class="flex-grow-1 overflow-auto" style="height: 480px;">
                                <div class="list-group list-group-flush">
                                    @forelse($conversations as $conv)
                                        <a href="javascript:void(0);" wire:click="selectConversation({{ $conv->id }})" 
                                           class="list-group-item list-group-item-action border-0 py-3 {{ $activeConversationId === $conv->id ? 'bg-soft-success border-left border-success' : '' }}"
                                           style="border-left: 3px solid transparent; transition: all 0.2s ease;">
                                            <div class="d-flex w-100 justify-content-between align-items-center">
                                                <h6 class="mb-1 font-weight-bold text-dark" style="font-size: 14px;">{{ $conv->customer_name ?? $conv->phone_number }}</h6>
                                                <small class="text-muted" style="font-size: 11px;">{{ $conv->last_message_at ? $conv->last_message_at->diffForHumans() : '' }}</small>
                                            </div>
                                            <p class="mb-1 small text-muted text-truncate" style="font-size: 12px;">
                                                {{ $conv->phone_number }}
                                            </p>
                                        </a>
                                    @empty
                                        <div class="text-center py-5 text-muted">
                                            <i class="far fa-comments fa-2x mb-3 text-muted"></i>
                                            <p class="small mb-0">No conversations registered yet.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Messages Transcript Area -->
                        <div class="col-md-8 d-flex flex-column" style="height: 100%;">
                            @if($activeConversationId)
                                @php
                                    $selectedConv = $conversations->firstWhere('id', $activeConversationId);
                                @endphp
                                <!-- Chat Header -->
                                <div class="p-3 bg-light border-bottom d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="font-weight-bold mb-0 text-dark">{{ $selectedConv->customer_name ?? $selectedConv->phone_number }}</h6>
                                        <small class="text-muted"><i class="fab fa-whatsapp text-success mr-1"></i> {{ $selectedConv->phone_number }}</small>
                                    </div>
                                    <span class="badge badge-soft-success py-1 px-2" style="font-size: 11px;">24h Customer Service Window Active</span>
                                </div>

                                <!-- Chat Transcript Bubble List -->
                                <div class="flex-grow-1 p-3 overflow-auto d-flex flex-column" style="background-color: #f7f9fa; height: 360px;">
                                    @forelse($activeMessages as $msg)
                                        @if($msg->direction === 'inbound')
                                            <!-- Inbound Message bubble (Client) -->
                                            <div class="d-flex mb-3 align-items-start">
                                                <div class="bg-white text-dark p-3 rounded shadow-sm border" style="max-width: 70%; border-radius: 0 12px 12px 12px !important;">
                                                    <p class="mb-1" style="font-size: 13px; line-height: 1.5; white-space: pre-line;">{{ $msg->message_body }}</p>
                                                    <div class="text-right">
                                                        <small class="text-muted" style="font-size: 10px;">{{ $msg->timestamp->format('H:i') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <!-- Outbound Message bubble (LIMS Agent / System) -->
                                            <div class="d-flex mb-3 justify-content-end align-items-start">
                                                <div class="text-white p-3 rounded shadow-sm" style="background-color: #056162; max-width: 70%; border-radius: 12px 0 12px 12px !important;">
                                                    @if($msg->message_type === 'template')
                                                        <span class="badge badge-warning text-dark font-weight-bold py-1 px-2 mb-1" style="font-size: 9px; vertical-align: middle;"><i class="fas fa-robot mr-1"></i> TEMPLATE</span>
                                                    @endif
                                                    <p class="mb-1" style="font-size: 13px; line-height: 1.5; white-space: pre-line;">{{ $msg->message_body }}</p>
                                                    <div class="text-right">
                                                        <small class="text-white-50" style="font-size: 10px;">{{ $msg->timestamp->format('H:i') }} <i class="fas fa-check-double text-info ml-1"></i></small>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @empty
                                        <div class="text-center my-auto py-5 text-muted">
                                            <p class="small mb-0">No messages in this thread yet.</p>
                                        </div>
                                    @endforelse
                                </div>

                                <!-- Message Reply Input Area -->
                                <div class="p-3 bg-white border-top">
                                    <form wire:submit.prevent="sendChatMessage">
                                        <div class="input-group">
                                            <textarea class="form-control" wire:model.defer="replyBody" rows="2" placeholder="Type a response to send..." style="border-radius: 6px; resize: none; font-size: 13px;"></textarea>
                                            <div class="input-group-append pl-2 d-flex align-items-center">
                                                <button type="submit" class="btn btn-success px-4 h-100" style="border-radius: 6px; background-color: #25D366; border-color: #25D366; font-weight: 600;">
                                                    <i class="fas fa-paper-plane mr-1"></i> Send
                                                </button>
                                            </div>
                                        </div>
                                        @error('replyBody') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <small class="text-muted"><i class="fas fa-info-circle mr-1"></i> Customer replies are free-form and subject to Meta's standard pricing policy.</small>
                                            <small class="text-muted">Max 1,000 characters</small>
                                        </div>
                                    </form>
                                </div>
                            @else
                                <div class="my-auto text-center py-5 text-muted">
                                    <i class="far fa-comments fa-4x mb-3 text-muted" style="opacity: 0.3;"></i>
                                    <h5>Two-Way Customer Support</h5>
                                    <p class="text-muted">Select an active conversation thread from the sidebar to view chat history and write replies.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

