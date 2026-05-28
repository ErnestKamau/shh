<?php

namespace App\Http\Controllers\RiskManagement;

use App\Http\Controllers\Controller;
use App\Models\RiskManagement\RiskCategory;
use App\Models\RiskManagement\RiskSource;
use App\Models\RiskManagement\RiskStatus;
use App\Models\RiskManagement\TreatmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiskConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function riskCategories()
    {
        return view('layouts.risk.config.risk-categories');
    }

    public function riskSources()
    {
        return view('layouts.risk.config.risk-sources');
    }

    public function riskStatuses()
    {
        return view('layouts.risk.config.risk-statuses');
    }

    public function treatmentTypes()
    {
        return view('layouts.risk.config.treatment-types');
    }

    public function workflowApprovers()
    {
        return view('layouts.risk.config.approval-config');
    }

    // CRUD operations for Risk Categories
    public function storeRiskCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:risk_categories,code,NULL,id,deleted_at,NULL',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        RiskCategory::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active') ? 1 : 1,
            'company_id' => riskCompanyId(),
        ]);

        return redirect()->route('risk.config.risk-categories')
            ->with('success', 'Risk category created successfully.');
    }

    public function updateRiskCategory(Request $request, $id)
    {
        $category = RiskCategory::forCompany()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:risk_categories,code,' . $id . ',id,deleted_at,NULL',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $category->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('risk.config.risk-categories')
            ->with('success', 'Risk category updated successfully.');
    }

    public function deleteRiskCategory($id)
    {
        $category = RiskCategory::forCompany()->findOrFail($id);

        $category->delete();

        return redirect()->route('risk.config.risk-categories')
            ->with('success', 'Risk category deleted successfully.');
    }

    // CRUD operations for Risk Sources
    public function storeRiskSource(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:risk_sources,code,NULL,id,deleted_at,NULL',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        RiskSource::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active') ? 1 : 1,
            'company_id' => riskCompanyId(),
        ]);

        return redirect()->route('risk.config.risk-sources')
            ->with('success', 'Risk source created successfully.');
    }

    public function updateRiskSource(Request $request, $id)
    {
        $source = RiskSource::forCompany()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:risk_sources,code,' . $id . ',id,deleted_at,NULL',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $source->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('risk.config.risk-sources')
            ->with('success', 'Risk source updated successfully.');
    }

    public function deleteRiskSource($id)
    {
        $source = RiskSource::forCompany()->findOrFail($id);

        $source->delete();

        return redirect()->route('risk.config.risk-sources')
            ->with('success', 'Risk source deleted successfully.');
    }

    // CRUD operations for Risk Statuses
    public function storeRiskStatus(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:risk_statuses,code,NULL,id,deleted_at,NULL',
            'description' => 'nullable|string',
            'color_code' => 'nullable|string|max:7',
            'order_index' => 'nullable|integer',
            'workflow_step' => 'nullable|integer|min:1|max:7',
            'is_active' => 'nullable|boolean',
        ]);

        RiskStatus::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'color_code' => $validated['color_code'] ?? '#17a2b8',
            'order_index' => $validated['order_index'] ?? 0,
            'workflow_step' => $validated['workflow_step'] ?? null,
            'is_active' => $request->has('is_active') ? 1 : 1,
            'company_id' => riskCompanyId(),
        ]);

        return redirect()->route('risk.config.risk-statuses')
            ->with('success', 'Risk status created successfully.');
    }

    public function updateRiskStatus(Request $request, $id)
    {
        $status = RiskStatus::forCompany()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:risk_statuses,code,' . $id . ',id,deleted_at,NULL',
            'description' => 'nullable|string',
            'color_code' => 'nullable|string|max:7',
            'order_index' => 'nullable|integer',
            'workflow_step' => 'nullable|integer|min:1|max:7',
            'is_active' => 'nullable|boolean',
        ]);

        $status->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'color_code' => $validated['color_code'] ?? null,
            'order_index' => $validated['order_index'] ?? 0,
            'workflow_step' => $validated['workflow_step'] ?? null,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('risk.config.risk-statuses')
            ->with('success', 'Risk status updated successfully.');
    }

    public function deleteRiskStatus($id)
    {
        $status = RiskStatus::forCompany()->findOrFail($id);

        $status->delete();

        return redirect()->route('risk.config.risk-statuses')
            ->with('success', 'Risk status deleted successfully.');
    }

    // CRUD operations for Treatment Types
    public function storeTreatmentType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:treatment_types,code,NULL,id,deleted_at,NULL',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        TreatmentType::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active') ? 1 : 1,
            'company_id' => riskCompanyId(),
        ]);

        return redirect()->route('risk.config.treatment-types')
            ->with('success', 'Treatment type created successfully.');
    }

    public function updateTreatmentType(Request $request, $id)
    {
        $type = TreatmentType::forCompany()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:treatment_types,code,' . $id . ',id,deleted_at,NULL',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $type->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('risk.config.treatment-types')
            ->with('success', 'Treatment type updated successfully.');
    }

    public function deleteTreatmentType($id)
    {
        $type = TreatmentType::forCompany()->findOrFail($id);

        $type->delete();

        return redirect()->route('risk.config.treatment-types')
            ->with('success', 'Treatment type deleted successfully.');
    }

    // AJAX endpoints for getting single records
    public function getRiskCategory($id)
    {
        $category = RiskCategory::forCompany()->findOrFail($id);

        return response()->json($category);
    }

    public function getRiskSource($id)
    {
        $source = RiskSource::forCompany()->findOrFail($id);

        return response()->json($source);
    }

    public function getRiskStatus($id)
    {
        $status = RiskStatus::forCompany()->findOrFail($id);

        return response()->json($status);
    }

    public function getTreatmentType($id)
    {
        $type = TreatmentType::forCompany()->findOrFail($id);

        return response()->json($type);
    }
}


