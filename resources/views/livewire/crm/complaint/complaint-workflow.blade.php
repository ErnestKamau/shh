<div>
    <div class="tab-pane fade show p-3" id="complaint-workflow" role="tabpanel">
        <h5 class="card-title">
            <i class="mdi mdi-workflow"></i> Workflow - {{ $currentWorkflowName }}
        </h5>
        
        <div class="p-3">
            @if($complaint->complaint_workflow == 1)
                <button class="btn btn-outline-secondary btn-sm" wire:click="approveNext">
                    <i class="mdi mdi-comment-check"></i> Request Approval
                </button>
            @endif
            
            @if($complaint->complaint_workflow == 2)
                <button class="btn btn-outline-success btn-sm" wire:click="approveNext">
                    <i class="mdi mdi-comment-check"></i> Approve Complaint
                </button>
                <button class="btn btn-outline-warning btn-sm" wire:click="reverseApproval">
                    <i class="mdi mdi-comment-arrow-left"></i> Return Complaint
                </button>
                <button class="btn btn-outline-danger btn-sm" wire:click="reject">
                    <i class="mdi mdi-comment-remove"></i> Reject Complaint
                </button>
            @endif
            
            @if($complaint->complaint_workflow == 3)
                <button class="btn btn-outline-secondary btn-sm" wire:click="requestResolutionApprove">
                    <i class="mdi mdi-comment-check"></i> Request Resolution Approval
                </button>
            @endif
            
            @if($complaint->complaint_workflow == 4)
                <button class="btn btn-outline-success btn-sm" wire:click="approveResolution">
                    <i class="mdi mdi-comment-check"></i> Approve Resolution
                </button>
                <button class="btn btn-outline-warning btn-sm" wire:click="reverseResolution">
                    <i class="mdi mdi-comment-arrow-left"></i> Return Resolution
                </button>
                <button class="btn btn-outline-danger btn-sm" wire:click="rejectResolution">
                    <i class="mdi mdi-comment-remove"></i> Reject Resolution
                </button>
            @endif
            
            <div class="form-group mt-3">
                <label>Comment:</label>
                <textarea class="form-control" wire:model="comment" rows="2"></textarea>
            </div>
        </div>

        <h6 class="mt-4">Chain of Custody</h6>
        <x-crm.data-table>
            <x-slot:header>
                <tr>
                    <th>Action</th>
                    <th>Action Taker</th>
                    <th>Workflow Stage</th>
                    <th>Comments</th>
                    <th>Date</th>
                </tr>
            </x-slot:header>
            @foreach($chainOfCustody as $custody)
                <tr>
                    <td>{{ $custody->action }}</td>
                    <td>{{ $custody->actionTaker->name ?? '-' }}</td>
                    <td>{{ $custody->workflow_stage }}</td>
                    <td>{{ $custody->comments ?? '-' }}</td>
                    <td>{{ $custody->move_out_date ?? $custody->created_at }}</td>
                </tr>
            @endforeach
        </x-crm.data-table>
    </div>
</div>

