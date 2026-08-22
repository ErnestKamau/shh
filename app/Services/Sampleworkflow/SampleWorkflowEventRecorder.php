<?php

namespace App\Services\Sampleworkflow;

use App\Models\Sampleworkflow\SampleWorkflowEvent;
use App\User;

final class SampleWorkflowEventRecorder
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        string $subjectType,
        string $subjectId,
        string $eventType,
        string $what,
        ?string $how = null,
        ?string $why = null,
        ?string $where = null,
        ?string $instanceId = null,
        ?string $batchId = null,
        ?string $workflowStage = null,
        array $metadata = [],
        ?User $user = null,
    ): SampleWorkflowEvent {
        $user ??= auth()->user();

        return SampleWorkflowEvent::query()->create([
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'submission_form_instance_id' => $instanceId,
            'sample_header_id' => $batchId,
            'workflow_stage' => $workflowStage,
            'event_type' => $eventType,
            'who_name' => $user instanceof User ? (string) $user->name : 'System',
            'who_user_id' => $user instanceof User ? (string) $user->id : null,
            'what' => $what,
            'how' => $how,
            'why' => $why,
            'where' => $where,
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }
}
