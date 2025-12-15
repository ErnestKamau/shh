<?php

namespace Modules\TemplateEngine\Services;

use Modules\TemplateEngine\Models\FormTemplate;
use Modules\TemplateEngine\Models\TemplateSection;
use Illuminate\Support\Facades\DB;
use App\User;

class TemplateCreationService
{
    public function createTemplate(array $data, ?User $user = null): FormTemplate
    {
        return DB::transaction(function () use ($data, $user) {
            $template = FormTemplate::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? 'General',
                'status' => 'draft',
                'created_by' => $user->id ?? null,
                'header_content' => $data['header_content'] ?? null,
                'footer_content' => $data['footer_content'] ?? null,
                'process_id' => $data['process_id'] ?? null,
                'process_type' => $data['process_type'] ?? null,
            ]);

            // Create default sections
            $this->addSection($template, 'Header', 'header', 0);
            $this->addSection($template, 'Body', 'body', 1);
            $this->addSection($template, 'Footer', 'footer', 2);

            return $template;
        });
    }

    public function updateTemplate(FormTemplate $template, array $data): FormTemplate
    {
        $template->update($data);
        return $template;
    }

    public function addSection(FormTemplate $template, string $title, string $type = 'body', int $order = 0): TemplateSection
    {
        return $template->sections()->create([
            'title' => $title,
            'type' => $type,
            'order_index' => $order
        ]);
    }

    public function deleteSection(TemplateSection $section): bool
    {
        // Don't delete fields, maybe move them? specific logic can be added later.
        // For now, cascade delete handles fields.
        return $section->delete();
    }
    
    public function publishTemplate(FormTemplate $template): FormTemplate
    {
        $template->update(['status' => 'published']);
        return $template;
    }
}
