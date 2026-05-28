<?php

namespace App\Repositories\Monitoring;

use App\Models\Monitoring\MonitoringTemplate;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MonitoringTemplateRepository
{
    public function activeByCategoryForLab(string $category, ?string $labId): Collection
    {
        return MonitoringTemplate::query()
            ->where('monitoring_category', $category)
            ->where('is_active', true)
            ->with(['fields.formulaRule', 'formulaRules'])
            ->orderBy('name')
            ->get()
            ->filter(function (MonitoringTemplate $template) use ($labId): bool {
                if ($labId === null || $labId === '') {
                    return true;
                }

                return $template->appliesToLab($labId);
            })
            ->values();
    }

    public function findTemplate(string $templateId): ?MonitoringTemplate
    {
        return MonitoringTemplate::query()
            ->with(['fields.formulaRule', 'formulaRules'])
            ->find($templateId);
    }

    /**
     * Active environmental templates linked to a lab section via metadata.
     *
     * @return Collection<int, MonitoringTemplate>
     */
    public function activeForSection(string $sectionId, ?string $labId): Collection
    {
        return $this->activeByCategoryForLab('environmental', $labId)
            ->filter(function (MonitoringTemplate $template) use ($sectionId): bool {
                $metaField = $template->fields->firstWhere('field_key', '__meta_scope_items');

                if ($metaField === null) {
                    return false;
                }

                $sections = array_map(
                    fn ($id) => (string) $id,
                    (array) Arr::get($metaField->field_config ?? [], 'sections', []),
                );

                return in_array((string) $sectionId, $sections, true);
            })
            ->values();
    }
}
