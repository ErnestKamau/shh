<?php

namespace App\Support\Registry;

use App\Models\Registry\RegistryRequest;
use App\Models\Registry\WorkflowStep;

class RegistryStatusManager
{
    public function statusForStage(?WorkflowStep $step): string
    {
        if ($step === null) {
            return RegistryRequest::STATUS_OPEN;
        }

        if ($step->is_final) {
            return RegistryRequest::STATUS_CLOSED;
        }

        return RegistryRequest::STATUS_PENDING_APPROVAL;
    }
}
