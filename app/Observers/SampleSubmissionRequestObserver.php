<?php

namespace App\Observers;

use App\Models\SampleSubmissionRequest;
use App\Services\Portal\CustomerStatusChangeNotificationService;

class SampleSubmissionRequestObserver
{
    public function updated(SampleSubmissionRequest $request): void
    {
        if (! $request->wasChanged('status')) {
            return;
        }

        $fromStatus = trim((string) $request->getOriginal('status'));
        $toStatus = trim((string) ($request->status ?? ''));

        if ($toStatus === '' || $fromStatus === $toStatus) {
            return;
        }

        app(CustomerStatusChangeNotificationService::class)
            ->recordSubmissionRequestStatusChange($request, $fromStatus, $toStatus);
    }
}
