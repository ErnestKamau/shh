<?php

namespace App\Support\Registry;

use App\Models\Registry\RegistryRequest;
use App\Models\Registry\RegistryRequestCategory;

class RegistryReferenceGenerator
{
    public function generate(?RegistryRequestCategory $category = null): string
    {
        $prefix = 'REG';
        if ($category !== null) {
            $prefix = strtoupper(substr($category->code, 0, 3));
        }

        $year = now()->format('Y');
        $pattern = $prefix . '-' . $year . '-%';

        $last = RegistryRequest::query()
            ->where('reference_no', 'like', $pattern)
            ->orderByDesc('reference_no')
            ->value('reference_no');

        $sequence = 1;
        if ($last !== null && preg_match('/-(\d+)$/', $last, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('%s-%s-%05d', $prefix, $year, $sequence);
    }
}
