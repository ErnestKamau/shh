<?php

namespace App\Services\SubmissionForm;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormSection;
use Illuminate\Support\Facades\Storage;

class FormSchemaBuilder
{
    /** @var list<string> */
    private const AJAX_OPTION_TYPES = [
        'client_select',
        'sample_type_select',
        'client_unit_select',
        'client_contact_select',
        'client_submission_officers_select',
        'analysis_type_select',
        'analysis_elements_select',
        'store_select',
        'store_slot_select',
        'sample_condition_select',
        'standard_select',
        'sample_point_select',
        'user_select',
    ];

    /** @var list<string> */
    private const MULTIPLE_SELECT_TYPES = [
        'sample_point_select',
        'analysis_type_select',
        'analysis_elements_select',
    ];

    public function buildFormMeta(SubmissionForm $form): array
    {
        return [
            'id' => $form->id,
            'name' => $form->name,
            'description' => $form->description,
            'document_code' => $form->document_code,
            'version' => $form->version,
            'issue_date' => $form->issue_date?->format('Y-m-d'),
            'form_type' => $form->form_type,
            'is_customer_portal_form' => (bool) $form->is_customer_portal_form,
            'is_customer_request_form' => (bool) $form->is_customer_request_form,
            'display_mode' => $form->display_mode,
            'naming_convention_prefix' => $form->naming_convention_prefix,
            'naming_convention_format' => $form->naming_convention_format,
            'lims_destination_pages' => $form->lims_destination_pages ?? [],
            'updated_at' => $form->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Full schema for a template form including linked attachment form schemas.
     *
     * @return array<string, mixed>
     */
    public function buildTemplateWithAttachments(SubmissionForm $form): array
    {
        $form->loadMissing([
            'sections.elementHolders.elements' => function ($query): void {
                $query->orderBy('sort_order');
            },
            'attachmentForms.sections.elementHolders.elements' => function ($query): void {
                $query->orderBy('sort_order');
            },
        ]);

        $payload = [
            'form' => $this->buildFormMeta($form),
            'sections' => $this->buildSections($form),
            'submission_payload' => $this->submissionPayloadDocumentation($form),
        ];

        if ($form->isTemplate()) {
            $payload['attachment_forms'] = $form->attachmentForms
                ->map(fn (SubmissionForm $attachment): array => $this->buildAttachmentFormPackage($attachment, $form->id))
                ->values()
                ->all();
        }

        if ($form->isAttachment()) {
            $payload['linked_template_form_ids'] = $form->templateForms()->pluck('submission_forms.id')->all();
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildAttachmentFormPackage(SubmissionForm $attachment, ?string $templateFormId = null): array
    {
        $attachment->loadMissing([
            'sections.elementHolders.elements' => function ($query): void {
                $query->orderBy('sort_order');
            },
        ]);

        return [
            'form' => $this->buildFormMeta($attachment),
            'template_form_id' => $templateFormId,
            'sections' => $this->buildSections($attachment),
            'submission_payload' => $this->submissionPayloadDocumentation($attachment),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function buildSections(SubmissionForm $form): array
    {
        return $form->sections
            ->sortBy('sort_order')
            ->values()
            ->map(fn (SubmissionFormSection $section): array => $this->buildSection($section))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSection(SubmissionFormSection $section): array
    {
        return [
            'id' => $section->id,
            'title' => $section->title,
            'description' => $section->description,
            'type' => $section->section_type === 'rows_section' ? 'rows_section' : 'regular',
            'alignment' => $section->section_alignment ?? 'left',
            'sort_order' => $section->sort_order,
            'logos' => collect($section->getSectionLogos())
                ->map(fn (array $logo): array => [
                    'url' => $this->publicUrl($logo['path']),
                    'position' => $logo['position'] ?? 'left',
                ])
                ->all(),
            'holders' => $section->elementHolders
                ->sortBy('sort_order')
                ->values()
                ->map(fn (SubmissionFormElementHolder $holder): array => $this->buildHolder($holder))
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildHolder(SubmissionFormElementHolder $holder): array
    {
        $elements = $holder->elements->sortBy('sort_order')->values();

        return [
            'id' => $holder->id,
            'type' => $holder->holder_type,
            'max_elements' => $holder->max_elements,
            'sort_order' => $holder->sort_order,
            'column_count' => $holder->holder_type === 'field' ? max(1, $elements->count()) : 0,
            'elements' => $elements
                ->map(fn (SubmissionFormElement $element): array => $this->buildElement($element))
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildElement(SubmissionFormElement $element): array
    {
        $dependsOn = null;
        if ($element->element_type === 'user_signature' || $element->element_type === 'contact_signature') {
            $dependsOn = is_array($element->options) ? ($element->options['depends'] ?? null) : null;
        } elseif ($element->element_type === 'depended_field') {
            $dependsOn = $element->depends_on_field;
        } elseif (in_array($element->element_type, ['store_slot_select', 'client_unit_select', 'client_contact_select', 'sample_point_select', 'analysis_type_select', 'analysis_elements_select'], true)) {
            $dependsOn = match ($element->element_type) {
                'store_slot_select' => 'store_select',
                'client_unit_select', 'client_contact_select' => 'client_select',
                'sample_point_select' => 'client_unit_select',
                'analysis_type_select' => 'sample_type_select',
                'analysis_elements_select' => 'analysis_type_select',
                default => null,
            };
        }

        $isMultiple = in_array($element->element_type, self::MULTIPLE_SELECT_TYPES, true)
            || (is_array($element->options) && filter_var($element->options['multiple'] ?? false, FILTER_VALIDATE_BOOLEAN));

        $payload = [
            'id' => $element->id,
            'type' => $element->element_type,
            'name' => $element->name,
            'label' => $element->label,
            'placeholder' => $element->placeholder,
            'help_text' => $element->help_text,
            'required' => (bool) $element->is_required,
            'readonly' => (bool) $element->is_readonly,
            'default_value' => $element->default_value,
            'sort_order' => $element->sort_order,
            'options' => $this->resolveStaticOptions($element),
            'options_source' => $this->resolveOptionsSource($element),
            'properties' => [
                'depends_on' => $dependsOn,
                'depends_on_type' => $element->depends_on_type,
                'multiple' => $isMultiple,
                'mapping' => $element->is_mapped ? [
                    'table' => $element->mapping_table,
                    'field' => $element->mapping_field,
                ] : null,
                'source_table' => $element->source_table,
                'source_field' => $element->source_field,
            ],
            'validation' => [
                'rules' => $element->getLaravelValidationRules(),
            ],
        ];

        if ($element->element_type === 'depended_field') {
            $payload['properties']['depends_on_field'] = $element->depends_on_field;
        }

        return $payload;
    }

    /**
     * @return list<array{value: mixed, label: string}>|null
     */
    private function resolveStaticOptions(SubmissionFormElement $element): ?array
    {
        if (in_array($element->element_type, self::AJAX_OPTION_TYPES, true)) {
            return null;
        }

        if ($element->element_type === 'zone_select') {
            return $element->getDynamicOptions();
        }

        if ($element->hasOptions()) {
            return collect($element->options ?? [])
                ->map(function ($option): array {
                    if (is_array($option) && isset($option['value'], $option['label'])) {
                        return [
                            'value' => $option['value'],
                            'label' => $option['label'],
                        ];
                    }

                    return [
                        'value' => $option,
                        'label' => is_string($option) ? $option : (string) ($option['label'] ?? $option['value'] ?? ''),
                    ];
                })
                ->values()
                ->all();
        }

        return null;
    }

    private function resolveOptionsSource(SubmissionFormElement $element): string
    {
        if (in_array($element->element_type, self::AJAX_OPTION_TYPES, true)) {
            return 'ajax';
        }

        if ($element->element_type === 'zone_select') {
            return 'embedded';
        }

        if ($element->hasOptions()) {
            return 'static';
        }

        return 'none';
    }

    /**
     * Documents how the gateway / portal should POST field values.
     *
     * @return array<string, mixed>
     */
    public function submissionPayloadDocumentation(SubmissionForm $form): array
    {
        $fieldNames = $this->collectFieldNames($form);

        return [
            'description' => 'Submit field values using element.name keys. Prefer a top-level fields object in JSON; multipart file uploads use the element name as the form field name.',
            'create_instance' => [
                'title' => 'string|nullable',
                'portal_account_id' => 'uuid|nullable',
                'crm_customer_id' => 'uuid|nullable',
                'portal_request_id' => 'uuid|nullable — set when creating an attachment instance tied to a template instance',
            ],
            'submit_instance' => [
                'action' => 'submit (required)',
                'title' => 'string|nullable',
                'fields' => array_fill_keys($fieldNames, 'scalar|string|array — see field definitions'),
            ],
            'field_key_rules' => [
                'use_element_name' => true,
                'rows_section_fields' => 'fields.{name} is an array indexed 0..n-1',
                'multiple_select_fields' => 'fields.{name} is an array of selected values',
                'file_fields' => 'multipart field name equals element.name (not nested under fields)',
            ],
            'headers' => [
                'Authorization' => 'Bearer {PORTAL_GATEWAY_API_KEY}',
                'X-CRM-Customer-Id' => 'required for customer scoping',
                'X-Portal-Account-Id' => 'optional portal account uuid',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public function collectFieldNames(SubmissionForm $form): array
    {
        return $form->sections
            ->flatMap(fn (SubmissionFormSection $section) => $section->elementHolders)
            ->flatMap(fn (SubmissionFormElementHolder $holder) => $holder->elements)
            ->pluck('name')
            ->unique()
            ->values()
            ->all();
    }

    private function publicUrl(string $path): string
    {
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
