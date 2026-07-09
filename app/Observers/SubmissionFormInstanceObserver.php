<?php

namespace App\Observers;

use App\Models\SubmissionFormInstance;
use App\Services\Portal\CustomerStatusChangeNotificationService;

class SubmissionFormInstanceObserver
{
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
