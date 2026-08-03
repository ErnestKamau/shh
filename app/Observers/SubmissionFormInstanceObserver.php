<?php

namespace App\Observers;

use App\Models\SubmissionFormInstance;
use App\Services\Portal\CustomerStatusChangeNotificationService;
use App\Services\SubmissionForm\FormInstanceSnapshotService;

class SubmissionFormInstanceObserver
{
    public function created(SubmissionFormInstance $instance): void
    {
        $form = $instance->submissionForm;
        if ($form === null) {
            return;
        }

        app(FormInstanceSnapshotService::class)->capture($instance, $form);
    }

    public function updated(SubmissionFormInstance $instance): void
    {
        if (! $instance->wasChanged('status')) {
            return;
        }

        $fromStatus = trim((string) $instance->getOriginal('status'));
        $toStatus = trim((string) ($instance->status ?? ''));

        if ($toStatus === '' || $fromStatus === $toStatus) {
            return;
        }

        app(CustomerStatusChangeNotificationService::class)
            ->recordFormInstanceStatusChange($instance, $fromStatus, $toStatus);
    }
}
