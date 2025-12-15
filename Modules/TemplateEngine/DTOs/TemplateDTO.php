<?php

namespace Modules\TemplateEngine\DTOs;

class TemplateDTO
{
    public string $name;
    public ?string $description;
    public ?string $category;
    public ?array $header_content;
    public ?array $footer_content;
    public array $sections = [];

    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $this->description = $data['description'] ?? null;
        $this->category = $data['category'] ?? null;
        $this->header_content = $data['header_content'] ?? null;
        $this->footer_content = $data['footer_content'] ?? null;
    }
    
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'header_content' => $this->header_content,
            'footer_content' => $this->footer_content,
        ];
    }
}
