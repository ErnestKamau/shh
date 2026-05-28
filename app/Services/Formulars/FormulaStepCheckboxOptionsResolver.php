<?php

namespace App\Services\Formulars;

use App\Analyte;
use App\AnalysisMethod;
use App\Models\Equipments\Equipment;
use App\Models\Formulars\FormulaStep;
use App\Services\LogEntryWorksheets\LogEntryDatasetResolverService;
use App\User;
use Illuminate\Support\Collection;

class FormulaStepCheckboxOptionsResolver
{
    /**
     * @return Collection<int, object{id: string, label: string}>
     */
    public function optionsForStep(FormulaStep $step, int $limit = 200): Collection
    {
        $config = is_array($step->step_config) ? $step->step_config : [];
        $mode = (string) ($config['options_mode'] ?? 'static');

        if ($mode === 'preset' && ! empty($config['preset_model'])) {
            return $this->optionsForPresetModel((string) $config['preset_model'], $limit);
        }

        if ($mode === 'dataset') {
            $datasetConfig = is_array($config['dataset_config'] ?? null) ? $config['dataset_config'] : [];
            if ($datasetConfig !== []) {
                return app(LogEntryDatasetResolverService::class)
                    ->dropdownOptions($datasetConfig)
                    ->take($limit);
            }
        }

        return collect($config['static_options'] ?? [])
            ->map(fn ($option) => (object) [
                'id' => (string) $option,
                'label' => (string) $option,
            ]);
    }

    /**
     * @return Collection<int, object{id: string, label: string}>
     */
    public function optionsForPresetModel(string $modelTiedTo, int $limit = 200): Collection
    {
        return match ($modelTiedTo) {
            'users' => User::query()->where('active', 1)->orderBy('name')->limit($limit)->get()
                ->map(fn ($u) => (object) ['id' => (string) $u->id, 'label' => $u->name ?: $u->email]),
            'equipments' => Equipment::query()->orderBy('name')->limit($limit)->get()
                ->map(fn ($e) => (object) ['id' => (string) $e->id, 'label' => $e->name]),
            'methods' => AnalysisMethod::query()->orderBy('name')->limit($limit)->get()
                ->map(fn ($m) => (object) ['id' => (string) $m->id, 'label' => $m->name]),
            'analytes' => Analyte::query()->where('active', 1)->orderBy('name')->limit($limit)->get()
                ->map(fn ($a) => (object) [
                    'id' => (string) $a->id,
                    'label' => trim($a->code ? "{$a->code} — {$a->name}" : (string) $a->name),
                ]),
            default => collect(),
        };
    }
}
