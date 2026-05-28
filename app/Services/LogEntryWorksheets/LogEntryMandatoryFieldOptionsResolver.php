<?php

namespace App\Services\LogEntryWorksheets;

use App\Analyte;
use App\AnalysisMethod;
use App\Models\Equipments\Equipment;
use App\Models\LogEntryWorksheets\LogEntryWorksheetMandatoryField;
use App\SampleType;
use App\Services\LogEntryWorksheets\LogEntryDatasetResolverService;
use App\User;
use Illuminate\Support\Collection;

class LogEntryMandatoryFieldOptionsResolver
{
    public function mandatoryFieldUsesSelectList(LogEntryWorksheetMandatoryField $field): bool
    {
        return $field->field_type === 'dataset_related'
            || LogEntryWorksheetMandatoryField::isPresetLookupType($field->field_type);
    }

    public function optionsForField(LogEntryWorksheetMandatoryField $field, int $limit = 100): Collection
    {
        if ($field->dataset_config) {
            return app(LogEntryDatasetResolverService::class)
                ->dropdownOptions($field->dataset_config)
                ->take($limit);
        }

        $modelTiedTo = LogEntryWorksheetMandatoryField::isPresetLookupType($field->field_type)
            ? $field->field_type
            : ($field->model_tied_to ?? '');

        return $this->optionsForModel($modelTiedTo, $limit);
    }

    public function optionsForModel(string $modelTiedTo, int $limit = 100): Collection
    {
        return match ($modelTiedTo) {
            'users' => User::query()->where('active', 1)->orderBy('name')->limit($limit)->get()
                ->map(fn ($u) => (object) ['id' => (string) $u->id, 'label' => $u->name ?: $u->email]),
            'equipments' => Equipment::query()->orderBy('name')->limit($limit)->get()
                ->map(fn ($e) => (object) ['id' => (string) $e->id, 'label' => $e->name]),
            'methods' => AnalysisMethod::query()->orderBy('name')->limit($limit)->get()
                ->map(fn ($m) => (object) ['id' => (string) $m->id, 'label' => $m->name]),
            'sample_types' => SampleType::query()->where('active', 1)->orderBy('name')->limit($limit)->get()
                ->map(fn ($t) => (object) ['id' => (string) $t->id, 'label' => $t->name]),
            'analytes' => Analyte::query()->where('active', 1)->orderBy('name')->limit($limit)->get()
                ->map(fn ($a) => (object) [
                    'id' => (string) $a->id,
                    'label' => trim($a->code ? "{$a->code} — {$a->name}" : (string) $a->name),
                ]),
            'sample_details', 'captured_results' => collect(),
            default => collect(),
        };
    }
}
