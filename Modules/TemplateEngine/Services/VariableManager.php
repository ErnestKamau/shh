<?php

namespace Modules\TemplateEngine\Services;

use Modules\TemplateEngine\Models\FormTemplate;
use Modules\TemplateEngine\Models\FormTemplateVariable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class VariableManager
{
    /**
     * Create a new variable for a template.
     */
    public function createVariable(FormTemplate $template, array $data): FormTemplateVariable
    {
        $this->validateVariable($data, $template->id);

        return $template->variables()->create([
            'name' => $data['name'],
            'type' => $data['type'],
            'data_type' => $data['data_type'],
            'config' => $data['config'] ?? [],
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Update an existing variable.
     */
    public function updateVariable(FormTemplateVariable $variable, array $data): FormTemplateVariable
    {
        if (isset($data['name']) && $data['name'] !== $variable->name) {
            $this->validateVariable($data, $variable->form_template_id, $variable->id);
        }

        $variable->update([
            'name' => $data['name'] ?? $variable->name,
            'type' => $data['type'] ?? $variable->type,
            'data_type' => $data['data_type'] ?? $variable->data_type,
            'config' => $data['config'] ?? $variable->config,
        ]);

        return $variable;
    }

    /**
     * Delete a variable.
     */
    public function deleteVariable(FormTemplateVariable $variable): void
    {
        // TODO: Check for usage in fields before deleting?
        $variable->delete();
    }

    /**
     * Validate variable data.
     */
    protected function validateVariable(array $data, int $templateId, ?int $ignoreId = null): void
    {
        $exists = FormTemplateVariable::where('form_template_id', $templateId)
            ->where('name', $data['name'])
            ->when($ignoreId, function ($query) use ($ignoreId) {
                return $query->where('id', '!=', $ignoreId);
            })
            ->exists();

        if ($exists) {
            throw new Exception("Variable with name '{$data['name']}' already exists in this template.");
        }
        
        // Basic validation
        if (!in_array($data['type'], ['static', 'database', 'system'])) {
             throw new Exception("Invalid variable type: {$data['type']}");
        }
    }
}
