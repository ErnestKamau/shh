<?php

namespace App\Livewire\Crm;

use App\Models\CRM\CrmAlertRule;
use App\Models\CRM\CrmScoreConfig;
use App\Models\CRM\CrmDashboardWidget;
use App\Livewire\Crm\BaseCrmComponent;
use App\Jobs\Crm\RecalculateInteractionScoresJob;

class CrmDashboardSettings extends BaseCrmComponent
{
    // Settings Sections
    public $activeTab = 'widgets'; // 'widgets' or 'alerts'

    // -- WIDGETS MANAGER --
    public $widgetsList = [];
    public $isEditingInsight = false;
    public $insightForm = [
        'id' => null,
        'title' => '',
        'data_source' => '',
        'type' => 'bar_stacked',
        'grid_width' => 6,
        'color' => 'primary'
    ];

    // -- SCORE CONFIG MANAGER --
    public $scoreConfig = [
        'base_score' => 50,
        'sample_weight' => 10,
        'feedback_weight' => 5,
        'complaint_weight' => -5,
    ];

    // -- ALERT RULES (SLA) MANAGER --
    public $alertRulesList = [];
    public $isEditingAlertRule = false;
    public $alertRuleForm = [
        'id' => null,
        'rule_name' => '',
        'condition_type' => '',
        'threshold_value' => '',
        'action' => 'system_alert_feed',
        'is_active' => true
    ];

    public function mount($tab = null)
    {
        $this->initialize();
        $this->checkPermission('CRM.permission');
        
        if ($tab && in_array($tab, ['widgets', 'alerts', 'scoreFormula'])) {
            $this->activeTab = $tab;
        }

        $this->loadSettings();
    }

    public function changeTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function loadSettings()
    {
        $this->widgetsList = CrmDashboardWidget::orderBy('position_order')->get()->map(function($w) {
            return [
                'id' => $w->id,
                'title' => $w->title,
                'data_source' => $w->data_source,
                'type' => $w->type,
                'is_active' => $w->is_active,
                'grid_width' => $w->ui_config['grid_width'] ?? 6,
                'color' => $w->ui_config['color'] ?? 'primary',
            ];
        })->toArray();

        $this->alertRulesList = CrmAlertRule::orderBy('id', 'desc')->get()->toArray();

        $config = CrmScoreConfig::first();
        if ($config) {
            $this->scoreConfig = [
                'base_score' => $config->base_score,
                'sample_weight' => $config->sample_weight,
                'feedback_weight' => $config->feedback_weight,
                'complaint_weight' => $config->complaint_weight,
            ];
        }
    }

    // ── WIDGETS LOGIC ──────────────────────────────────────────────────

    public function toggleInsightForm($id = null)
    {
        if ($id) {
            $widget = CrmDashboardWidget::find($id);
            if ($widget) {
                $this->insightForm = [
                    'id' => $widget->id,
                    'title' => $widget->title,
                    'data_source' => $widget->data_source,
                    'type' => $widget->type,
                    'grid_width' => $widget->ui_config['grid_width'] ?? 6,
                    'color' => $widget->ui_config['color'] ?? 'primary'
                ];
                $this->isEditingInsight = true;
            }
        } else {
            $this->insightForm = [
                'id' => null,
                'title' => '',
                'data_source' => '',
                'type' => 'bar_stacked',
                'grid_width' => 6,
                'color' => 'primary'
            ];
            $this->isEditingInsight = true;
        }
    }

    public function cancelInsightForm()
    {
        $this->isEditingInsight = false;
    }

    public function saveInsight()
    {
        $uiConfig = [];
        if ($this->insightForm['type'] === 'kpi_card') {
            $uiConfig['color'] = $this->insightForm['color'];
            $uiConfig['icon'] = 'mdi-chart-bell-curve-cumulative';
        } else {
            $uiConfig['grid_width'] = (int) $this->insightForm['grid_width'];
            $uiConfig['color'] = $this->insightForm['color']; // For consistency
        }

        if ($this->insightForm['id']) {
            // Edit existing
            $widget = CrmDashboardWidget::find($this->insightForm['id']);
            if ($widget) {
                // merge with existing ui_config to preserve icon if it exists
                $existingUi = $widget->ui_config ?? [];
                if (isset($existingUi['icon']) && $this->insightForm['type'] === 'kpi_card') {
                    $uiConfig['icon'] = $existingUi['icon'];
                }
                $widget->update([
                    'title' => $this->insightForm['title'],
                    'type' => $this->insightForm['type'],
                    'data_source' => $this->insightForm['data_source'],
                    'ui_config' => $uiConfig
                ]);
            }
        } else {
            // Create new
            $maxOrder = CrmDashboardWidget::max('position_order') ?? 0;
            CrmDashboardWidget::create([
                'title' => $this->insightForm['title'],
                'type' => $this->insightForm['type'],
                'data_source' => $this->insightForm['data_source'],
                'position_order' => $maxOrder + 1,
                'is_active' => true,
                'ui_config' => $uiConfig
            ]);
        }

        $this->isEditingInsight = false;
        $this->loadSettings();
        session()->flash('widget_success', 'Insight configuration saved successfully.');
    }

    public function toggleWidgetActive($index)
    {
        if (isset($this->widgetsList[$index])) {
            $id = $this->widgetsList[$index]['id'];
            $newStatus = $this->widgetsList[$index]['is_active'];
            CrmDashboardWidget::where('id', $id)->update(['is_active' => $newStatus]);
        }
    }

    // ── ALERT RULES LOGIC ─────────────────────────────────────────────

    public function toggleAlertRuleForm($id = null)
    {
        if ($id) {
            $rule = CrmAlertRule::find($id);
            if ($rule) {
                $this->alertRuleForm = [
                    'id' => $rule->id,
                    'rule_name' => $rule->rule_name,
                    'condition_type' => $rule->condition_type,
                    'threshold_value' => $rule->threshold_value,
                    'action' => $rule->action,
                    'is_active' => $rule->is_active
                ];
                $this->isEditingAlertRule = true;
            }
        } else {
            $this->alertRuleForm = [
                'id' => null,
                'rule_name' => '',
                'condition_type' => '',
                'threshold_value' => '',
                'action' => 'system_alert_feed',
                'is_active' => true
            ];
            $this->isEditingAlertRule = true;
        }
    }

    public function cancelAlertRuleForm()
    {
        $this->isEditingAlertRule = false;
    }

    public function saveAlertRule()
    {
        $this->validate([
            'alertRuleForm.rule_name' => 'required|string',
            'alertRuleForm.condition_type' => 'required|string',
            'alertRuleForm.threshold_value' => 'required|string',
        ]);

        if ($this->alertRuleForm['id']) {
            // Edit existing
            $rule = CrmAlertRule::find($this->alertRuleForm['id']);
            if ($rule) {
                $rule->update([
                    'rule_name' => $this->alertRuleForm['rule_name'],
                    'condition_type' => $this->alertRuleForm['condition_type'],
                    'threshold_value' => $this->alertRuleForm['threshold_value'],
                    'action' => $this->alertRuleForm['action'],
                ]);
            }
        } else {
            // Create new
            CrmAlertRule::create([
                'rule_name' => $this->alertRuleForm['rule_name'],
                'condition_type' => $this->alertRuleForm['condition_type'],
                'threshold_value' => $this->alertRuleForm['threshold_value'],
                'action' => $this->alertRuleForm['action'],
                'is_active' => true,
            ]);
        }

        $this->isEditingAlertRule = false;
        $this->loadSettings();
        session()->flash('alert_success', 'Alert rule configuration saved successfully.');
    }

    public function toggleAlertRuleActive($index)
    {
        if (isset($this->alertRulesList[$index])) {
            $id = $this->alertRulesList[$index]['id'];
            $newStatus = $this->alertRulesList[$index]['is_active'];
            CrmAlertRule::where('id', $id)->update(['is_active' => $newStatus]);
        }
    }

    // ── SCORE FORMULA LOGIC ───────────────────────────────────────────

    public function saveCustomFormula()
    {
        $this->validate([
            'scoreConfig.base_score' => 'required|numeric',
            'scoreConfig.sample_weight' => 'required|numeric',
            'scoreConfig.feedback_weight' => 'required|numeric',
            'scoreConfig.complaint_weight' => 'required|numeric',
        ]);

        $config = CrmScoreConfig::first() ?? new CrmScoreConfig();
        $config->base_score = $this->scoreConfig['base_score'];
        $config->sample_weight = $this->scoreConfig['sample_weight'];
        $config->feedback_weight = $this->scoreConfig['feedback_weight'];
        $config->complaint_weight = $this->scoreConfig['complaint_weight'];
        $config->save();

        RecalculateInteractionScoresJob::dispatch();

        session()->flash('score_success', 'Formula updated and background recalculation job dispatched!');
    }

    public function render()
    {
        return view('livewire.crm.crm-dashboard-settings')
            ->extends('layouts.crm.layout.app', ['dataTable' => false, 'select2' => false])
            ->section('content2');
    }
}
