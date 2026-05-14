<?php

namespace App\Repositories\Monitoring;

use App\Models\Monitoring\MonitoringTemplate;
use Illuminate\Support\Collection;

class MonitoringTemplateRepository
{
    public function activeByCategoryForLab(string $category, ?string $labId): Collection
    {
        return MonitoringTemplate::query()
            ->where('monitoring_category', $category)
            ->where('is_active', true)
            ->where(function ($query) use ($labId): void {
                $query->whereNull('lab_id');

                if ($labId !== null && $labId !== '') {
                    $query->orWhere('lab_id', $labId);
                }
            })
            ->with(['fields', 'formulaRules'])
            ->orderBy('name')
            ->get();
    }

    public function findTemplate(string $templateId): ?MonitoringTemplate
    {
        return MonitoringTemplate::query()
            ->with(['fields', 'formulaRules'])
            ->find($templateId);
    }
}
