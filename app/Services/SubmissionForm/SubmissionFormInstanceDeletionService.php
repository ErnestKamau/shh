<?php

namespace App\Services\SubmissionForm;

use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use Illuminate\Support\Facades\DB;

class SubmissionFormInstanceDeletionService
{
    /**
     * Delete an instance, its values, audit logs, linked batches, and portal attachment instances.
     */
    public function deleteInstance(SubmissionFormInstance $instance): void
    {
        DB::transaction(function () use ($instance): void {
            $instance->attachmentInstances()->each(function (SubmissionFormInstance $attachment): void {
                $this->deleteInstanceTree($attachment);
            });

            $this->deleteInstanceTree($instance);
        });
    }

    private function deleteInstanceTree(SubmissionFormInstance $instance): void
    {
        SubmissionFormInstance::withoutAuditing(function () use ($instance): void {
            $instance->batches()->get()->each(function (\App\SampleHeader $batch): void {
                if ($batch->samples()->exists()) {
                    $batch->samples()->delete();
                }

                if (method_exists($batch, 'stagingDetails') && $batch->stagingDetails()->exists()) {
                    $batch->stagingDetails()->delete();
                }

                $batch->delete();
            });

            SubmissionFormInstanceValue::withoutAuditing(function () use ($instance): void {
                $instance->values()->delete();
            });

            $instance->auditLogs()->delete();
            $instance->delete();
        });
    }
}
