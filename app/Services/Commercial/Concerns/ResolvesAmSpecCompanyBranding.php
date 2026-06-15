<?php

namespace App\Services\Commercial\Concerns;

use App\Company;
use Illuminate\Support\Facades\File;

trait ResolvesAmSpecCompanyBranding
{
    /**
     * @return array{primary: string|null, secondary: string|null}
     */
    protected function resolveAmSpecLogoPaths(?Company $company): array
    {
        $primary = $this->resolveNamedAmSpecLogoPath($company, 'quotation_primary')
            ?? $this->resolveAmSpecFilePath($company?->report_logo)
            ?? $this->resolveAmSpecFilePath($company?->logo);

        $secondary = $this->resolveNamedAmSpecLogoPath($company, 'quotation_secondary');

        return [
            'primary' => $primary,
            'secondary' => $secondary,
        ];
    }

    private function resolveNamedAmSpecLogoPath(?Company $company, string $name): ?string
    {
        if ($company === null) {
            return null;
        }

        $path = $company->getReportLogoPath($name);
        if ($path === null || $path === $company->report_logo) {
            return null;
        }

        return $this->resolveAmSpecFilePath($path);
    }

    private function resolveAmSpecFilePath(mixed $candidate): ?string
    {
        if (! is_string($candidate) || $candidate === '') {
            return null;
        }

        if (File::exists($candidate)) {
            return $candidate;
        }

        if (str_starts_with($candidate, '/')) {
            $absolute = public_path(ltrim($candidate, '/'));
            if (File::exists($absolute)) {
                return $absolute;
            }
        }

        $storagePath = storage_path('app/'.ltrim($candidate, '/'));
        if (File::exists($storagePath)) {
            return $storagePath;
        }

        return null;
    }
}
