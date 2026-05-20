<?php

namespace App\Http\Controllers\LivewireControllers;

use App\Http\Controllers\Controller;
use App\Models\Equipments\Equipment;
use App\Models\Equipments\EquipmentDisposal;

/**
 * EquipmentAppController
 * 
 * Handles all Livewire-based equipment management views including:
 * - Equipment Management
 * - Equipment Details
 */
class EquipmentAppController extends Controller
{
    /**
     * Display the equipment management page.
     */
    public function equipmentManager()
    {
        return view('livewire.layout.equipment-app', [
            'componentType' => 'equipment-manager',
            'pageTitle' => 'Equipment Management'
        ]);
    }

    /**
     * Display the equipment dashboard page.
     */
    public function equipmentDashboard()
    {
        return view('livewire.layout.equipment-app', [
            'componentType' => 'equipment-dashboard',
            'pageTitle' => 'Equipment Dashboard'
        ]);
    }

    /**
     * Display the equipment detail page.
     */
    public function equipmentDetail($equipmentId)
    {
        $equipment = Equipment::findOrFail($equipmentId);
        
        return view('livewire.layout.equipment-app', [
            'componentType' => 'equipment-detail',
            'pageTitle' => 'Equipment Details - ' . $equipment->name,
            'equipment' => $equipment,
            'equipmentId' => $equipmentId,
            'fromEquipmentChecks' => request()->query('from') === 'equipment-checks',
            'fromEquipmentMaintenance' => request()->query('from') === 'equipment-maintenance',
        ]);
    }

    /**
     * Display the equipment disposal management page.
     */
    public function disposalManager()
    {
        return view('livewire.layout.equipment-app', [
            'componentType' => 'disposal-manager',
            'pageTitle' => 'Equipment Disposal Management'
        ]);
    }

    /**
     * Display the equipment disposal detail page.
     */
    public function disposalDetail($disposalId)
    {
        $disposal = EquipmentDisposal::with(['equipment'])->findOrFail($disposalId);
        
        return view('livewire.layout.equipment-app', [
            'componentType' => 'disposal-detail',
            'pageTitle' => 'Disposal Request - ' . $disposal->equipment->name,
            'disposal' => $disposal,
            'disposalId' => $disposalId
        ]);
    }
    /**
     * Display the workflow management page.
     */
    public function workflowManager()
    {
        return view('livewire.layout.equipment-app', [
            'componentType' => 'workflow-manager',
            'pageTitle' => 'Disposal Workflow Management'
        ]);
    }

    /**
     * Display the workflow form page.
     */
    public function workflowForm($id = null)
    {
        return view('livewire.layout.equipment-app', [
            'componentType' => 'workflow-form',
            'pageTitle' => $id ? 'Edit Workflow' : 'Create Workflow',
            'workflowId' => $id
        ]);
    }
    /**
     * Display the asset type management page.
     */
    public function assetTypeManager()
    {
        return view('livewire.layout.equipment-app', [
            'componentType' => 'asset-type-manager',
            'pageTitle' => 'Asset Type Management'
        ]);
    }

    /**
     * Display the asset location management page.
     */
    public function assetLocationManager()
    {
        return view('livewire.layout.equipment-app', [
            'componentType' => 'asset-location-manager',
            'pageTitle' => 'Asset Location Management'
        ]);
    }

    /**
     * Display the equipment checks page.
     */
    public function checksIndex()
    {
        return view('livewire.layout.equipment-app', [
            'componentType' => 'equipment-checks',
            'pageTitle' => 'Equipment Checks',
        ]);
    }

    /**
     * Display the equipment daily log page.
     */
    public function dailyLogIndex()
    {
        return view('livewire.layout.equipment-app', [
            'componentType' => 'equipment-daily-log',
            'pageTitle' => 'Equipment Daily Log',
        ]);
    }

    /**
     * Display the Equipment Monitoring module dashboard.
     */
    public function monitoring()
    {
        return view('livewire.layout.equipment-app', [
            'componentType' => 'monitoring',
            'pageTitle' => 'Equipment Monitoring',
        ]);
    }

    /**
     * Display the Equipment Maintenance page.
     */
    public function equipmentMaintenance()
    {
        return view('livewire.layout.equipment-app', [
            'componentType' => 'equipment-maintenance',
            'pageTitle' => 'Equipment Maintenance',
        ]);
    }

    /**
     * Display the create equipment monitoring template page.
     */
    public function createMonitoringTemplate()
    {
        return view('livewire.layout.equipment-app', [
            'componentType' => 'template-create',
            'pageTitle' => 'Create Equipment Monitoring Template',
            'breadcrumbItems' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Equipment Monitoring', 'url' => route('equipment.monitoring')],
                ['label' => 'Create Template', 'current' => true],
            ],
        ]);
    }

    /**
     * Display the edit equipment monitoring template page.
     */
    public function editMonitoringTemplate(\App\Models\Monitoring\MonitoringTemplate $template)
    {
        return view('livewire.layout.equipment-app', [
            'componentType' => 'template-edit',
            'pageTitle' => 'Edit Equipment Monitoring Template',
            'template' => $template,
            'breadcrumbItems' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Equipment Monitoring', 'url' => route('equipment.monitoring')],
                ['label' => 'Edit Template', 'current' => true],
            ],
        ]);
    }

}

