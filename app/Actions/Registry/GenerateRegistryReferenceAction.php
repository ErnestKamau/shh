<?php

namespace App\Actions\Registry;

use App\Models\Registry\RegistryRequestCategory;
use App\Support\Registry\RegistryReferenceGenerator;

class GenerateRegistryReferenceAction
{
    public function __construct(
        protected RegistryReferenceGenerator $referenceGenerator,
    ) {
    }

    public function execute(?string $categoryId = null): string
    {
        $category = $categoryId !== null
            ? RegistryRequestCategory::query()->find($categoryId)
            : null;

        return $this->referenceGenerator->generate($category);
    }
}
