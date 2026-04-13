<div>
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex align-items-center">
                <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                    style="width:28px;height:28px;background:#fef3c7;">
                    <i class="mdi mdi-clipboard-text-outline" style="font-size:1rem;color:#d97706;"></i>
                </span>
                <div>
                    <small class="font-weight-bold text-dark" style="font-size:0.82rem;">Incident Details</small>
                    <small class="text-muted d-block" style="font-size:0.67rem;">Complaint metadata, priority
                        classification &amp; registration info</small>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <x-crm.data-table>
                <x-slot:header>
                    <tr>
                        <th>Detail</th>
                        <th>Value</th>
                    </tr>
                </x-slot:header>
                <tr>
                    <th scope="row">Complaint ID:</th>
                    <td class="font-weight-bold">{{ $complaint->complaint_id }}</td>
                </tr>
                <tr>
                    <th scope="row">Date Logged:</th>
                    <td>{{ $complaint->date ? $complaint->date->format('d-M-Y') : $complaint->created_at->format('d-M-Y') }}</td>
                </tr>
                <tr>
                    <th style="width:35%;background:#f8fafc;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.04em;color:#64748b;">Priority:</th>
                    <td>
                        @php
                            $pLower = strtolower($complaint->priority ?? '');
                            if ($pLower === 'high' || $pLower === 'critical') {
                                $pColor = 'crm-badge-danger';
                            } elseif ($pLower === 'medium') {
                                $pColor = 'crm-badge-warning';
                            } else {
                                $pColor = 'crm-badge-secondary';
                            }
                        @endphp
                        <span class="crm-badge {{ $pColor }}">{{ $complaint->priority ?? 'N/A' }}</span>
                    </td>
                </tr>
                <tr>
                    <th style="width:35%;background:#f8fafc;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.04em;color:#64748b;">Lab Related?</th>
                    <td>
                        @if($complaint->is_lab_related)
                            <span class="crm-badge crm-badge-danger"><i class="mdi mdi-flask mr-1"></i>Yes</span>
                        @else
                            <span class="crm-badge crm-badge-secondary">No</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Complaint Type:</th>
                    <td>{{ $complaint->type }}</td>
                </tr>
                <tr>
                    <th>Organization Name:</th>
                    <td class="font-weight-bold">
                        {{ $complaint->organization_name ?? $complaint->received_from }}
                        @if($complaint->client)
                            <span class="badge badge-outline-primary ml-2" style="font-size: 0.7rem; font-weight: 500;">Linked to Customer</span>
                        @endif
                        <span class="text-muted ml-2" style="font-size: 0.8rem;">({{ $complaint->received_from_type ?? 'Other Party' }})</span>
                    </td>
                </tr>
                @if($complaint->contact_name)
                <tr>
                    <th>Contact Name:</th>
                    <td>{{ $complaint->contact_name }}</td>
                </tr>
                @endif
                @if($complaint->title_position)
                <tr>
                    <th>Title / Position:</th>
                    <td>{{ $complaint->title_position }}</td>
                </tr>
                @endif
                <tr>
                    <th>Mode of Delivery:</th>
                    <td>{{ $complaint->mode_of_delivery ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Registered By:</th>
                    <td>{{ $complaint->registered_by }}</td>
                </tr>
                <tr>
                    <th style="width:35%;background:#f8fafc;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.04em;color:#64748b;">Status:</th>
                    <td><span class="crm-badge crm-badge-primary">
                        {{ $complaint->complaint_workflow == 1 ? 'Stage 1: Intake & Triage' : 'Stage ' . $complaint->complaint_workflow . ': ' . $workflowStage }}
                    </span></td>
                </tr>
                @if($complaint->intakeApprovedBy)
                <tr>
                    <th style="width:35%;background:#f8fafc;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.04em;color:#64748b;">Intake Approved By:</th>
                    <td><span class="font-weight-bold text-success"><i class="mdi mdi-check-decagram mr-1"></i>{{ $complaint->intakeApprovedBy->name }}</span></td>
                </tr>
                @endif
                @if($complaint->nature_of_complaint)
                <tr>
                    <th>Nature of Complaint:</th>
                    <td>{{ $complaint->nature_of_complaint }}</td>
                </tr>
                @endif
                @php
                    $testItem = $complaint->test_item ?? $complaint->test_item_report_serial_no;
                @endphp
                @if($testItem)
                <tr>
                    <th>Test Item:</th>
                    <td>{{ $testItem }}</td>
                </tr>
                @endif
                @if($complaint->report_serial_no)
                <tr>
                    <th>Report Serial No:</th>
                    <td>{{ $complaint->report_serial_no }}</td>
                </tr>
                @endif
                @if($workflowStage == 'Closed Complaints' || $complaint->closure_recipient_emails || $complaint->closure_sent_at)
                    <tr>
                        <th colspan="2"
                            style="background:#f8fbf9; color:#1a6b3a; padding:12px 15px; border-top:2px solid #28a745; border-bottom:1px solid #e9ecef;">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-checkbox-marked-circle-outline mr-2 text-success"
                                    style="font-size: 1.2rem;"></i>
                                <span class="font-weight-bold"
                                    style="letter-spacing: 0.5px; text-transform: uppercase; font-size: 0.85rem;">Closure
                                    Information</span>
                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th style="width: 30%; vertical-align: middle; background-color: #fcfdfe;">Sent To:</th>
                        <td style="padding: 10px 15px;">
                            @php
                                $emails = is_array($complaint->closure_recipient_emails)
                                    ? $complaint->closure_recipient_emails
                                    : (is_string($complaint->closure_recipient_emails) ? json_decode($complaint->closure_recipient_emails, true) : []);
                                $emails = $emails ?? [];
                            @endphp
                            @if(count($emails))
                                <div class="d-flex flex-wrap" style="gap: 5px;">
                                    @foreach($emails as $email)
                                        <div
                                            style="background:#e6f4ea; color:#1e7e34; border:1px solid #c3e6cb; border-radius:20px; padding:3px 12px; font-size:0.8rem; font-weight:500; display:flex; align-items:center;">
                                            <i class="mdi mdi-email-outline mr-1"></i> {{ $email }}
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted font-italic">No recipient emails recorded</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th style="width: 30%; background-color: #fcfdfe;">Sent & Closed At:</th>
                        <td style="padding: 10px 15px;">
                            @if($complaint->closure_sent_at)
                                <span class="font-weight-bold text-dark">
                                    <i class="mdi mdi-calendar-check mr-1 text-success"></i>
                                    {{ $complaint->closure_sent_at->format('M d, Y') }}
                                </span>
                                <span class="text-secondary ml-2" style="font-size: 0.9rem;">
                                    <i class="mdi mdi-clock-outline mr-1"></i>
                                    {{ $complaint->closure_sent_at->format('H:i') }}
                                </span>
                            @else
                                <span class="crm-badge crm-badge-warning">Pending Final Closure</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th style="width: 30%; background-color: #fcfdfe;">Closed By:</th>
                        <td style="padding: 10px 15px;">
                            @if($closureOfficer ?? null)
                                <span class="font-weight-bold text-dark">
                                    <i class="mdi mdi-account-check-outline mr-1 text-success"></i>
                                    {{ $closureOfficer }}
                                </span>
                            @else
                                <span class="text-muted font-italic">Not recorded</span>
                            @endif
                        </td>
                    </tr>
                @endif
            </x-crm.data-table>
        </div>
        <div class="col-md-6">
            <div class="d-flex align-items-center mb-2 mt-1">
                <i class="mdi mdi-text-box-outline text-muted mr-2" style="font-size:1rem;"></i>
                <small class="font-weight-bold text-uppercase text-muted"
                    style="font-size:0.72rem;letter-spacing:0.05em;">Complaint Description</small>
            </div>
            <div class="my-small-text" style="line-height:1.6;color:#374151;">{!! $complaint->description !!}</div>

        </div>
    </div>
</div>