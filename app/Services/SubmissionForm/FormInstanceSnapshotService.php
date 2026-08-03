<?php

namespace App\Services\SubmissionForm;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;

class FormInstanceSnapshotService
{
    public function __construct(
        public FormSchemaBuilder $schemaBuilder
    ) {}

    public function capture(
        SubmissionFormInstance $instance,
        SubmissionForm $form
    ): void {
        $form->loadMissing([
            'sections.elementHolders.elements' => function ($query): void {
                $query->orderBy('sort_order');
            },
        ]);

        $instance->forceFill([
            'template_version' => (string) $form->version,
            'structure_snapshot' => [
                'form' => $this->schemaBuilder->buildFormMeta($form),
                'sections' => $this->schemaBuilder->buildSections($form),
            ],
            'structure_snapshot_at' => now(),
        ])->saveQuietly();
    }
}
