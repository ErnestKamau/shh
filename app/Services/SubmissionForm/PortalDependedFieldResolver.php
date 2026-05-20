<?php

namespace App\Services\SubmissionForm;

use App\Exceptions\Api\Portal\PortalApiException;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PortalDependedFieldResolver
{
    public function resolve(
        SubmissionFormInstance $instance,
        string $elementName,
        mixed $dependsOnValue = null,
    ): mixed {
        $element = SubmissionFormElement::query()
            ->whereHas('holder.section', function ($query) use ($instance): void {
                $query->where('submission_form_id', $instance->submission_form_id);
            })
            ->where('name', $elementName)
            ->where('element_type', 'depended_field')
            ->first();

        if (! $element) {
            throw ValidationException::withMessages([
                'element_name' => ['No depended_field element with this name exists on the form.'],
            ]);
        }

        $sourceId = $this->resolveSourceId($instance, $element, $dependsOnValue);

        if ($sourceId === null || $sourceId === '') {
            return null;
        }

        $sourceTable = (string) ($element->source_table ?? '');
        $sourceField = (string) ($element->source_field ?? '');

        if ($sourceTable === '' || $sourceField === '') {
            throw PortalApiException::withMessage(
                'depended_field_misconfigured',
                'Depended field source is not configured.',
                422,
            );
        }

        if (! Schema::hasTable($sourceTable)
            || ! Schema::hasColumn($sourceTable, 'id')
            || ! Schema::hasColumn($sourceTable, $sourceField)) {
            throw PortalApiException::withMessage(
                'depended_field_unavailable',
                'Depended field source is unavailable.',
                422,
            );
        }

        if (is_string($sourceId) && str_contains($sourceId, ',')) {
            $sourceId = trim(explode(',', $sourceId)[0]);
        }

        $record = DB::table($sourceTable)
            ->where('id', $sourceId)
            ->first([$sourceField]);

        return $record ? data_get($record, $sourceField) : null;
    }

    private function resolveSourceId(
        SubmissionFormInstance $instance,
        SubmissionFormElement $element,
        mixed $dependsOnValue,
    ): mixed {
        if ($dependsOnValue !== null && $dependsOnValue !== '') {
            return $dependsOnValue;
        }

        $parentField = (string) ($element->depends_on_field ?? '');

        if ($parentField === '') {
            return null;
        }

        $parentElement = SubmissionFormElement::query()
            ->whereHas('holder.section', function ($query) use ($instance): void {
                $query->where('submission_form_id', $instance->submission_form_id);
            })
            ->where('name', $parentField)
            ->first();

        if (! $parentElement) {
            return null;
        }

        $value = $instance->values()
            ->where('submission_form_element_id', $parentElement->id)
            ->orderByDesc('updated_at')
            ->value('value');

        return $value;
    }
}
