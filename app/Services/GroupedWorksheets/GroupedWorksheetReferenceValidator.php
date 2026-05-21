<?php

namespace App\Services\GroupedWorksheets;

use App\Enums\GroupedWorksheetItemType;
use App\Enums\HybridWorksheetBlockType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GroupedWorksheetReferenceValidator
{
    /**
     * @return array<string, mixed>
     */
    public function groupedItemRules(): array
    {
        return [
            'label' => 'required|string|max:255',
            'description' => 'nullable|string',
            'item_type' => ['required', Rule::in(array_column(GroupedWorksheetItemType::cases(), 'value'))],
            'reference_id' => 'required|uuid',
            'is_required' => 'boolean',
        ];
    }

    public function validateGroupedItem(string $itemType, string $referenceId): void
    {
        $type = GroupedWorksheetItemType::from($itemType);
        $table = $type->referenceTable();

        Validator::make(
            ['reference_id' => $referenceId],
            ['reference_id' => "required|uuid|exists:{$table},id"]
        )->validate();
    }

    /**
     * @return array<string, mixed>
     */
    public function hybridBlockRules(): array
    {
        return [
            'label' => 'nullable|string|max:255',
            'block_type' => ['required', Rule::in(array_column(HybridWorksheetBlockType::cases(), 'value'))],
            'reference_id' => 'nullable|uuid',
        ];
    }

    public function validateHybridBlock(string $blockType, ?string $referenceId): void
    {
        $type = HybridWorksheetBlockType::from($blockType);

        if ($type->isReference()) {
            Validator::make(
                ['reference_id' => $referenceId],
                ['reference_id' => 'required|uuid|exists:'.$type->referenceTable().',id']
            )->validate();
        }
    }
}
