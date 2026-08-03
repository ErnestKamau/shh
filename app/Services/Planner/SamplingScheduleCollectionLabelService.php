<?php

namespace App\Services\Planner;

use App\AnalysisElements;
use App\AnalysisType;
use App\Analyte;
use App\Company;
use App\Models\SamplingSchedule;
use App\Models\System\SystemConfiguration;
use App\SampleType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Builds Sample Collection Label view data from a System Planner sampling schedule.
 */
final class SamplingScheduleCollectionLabelService
{
    /**
     * @return array<string, mixed>
     */
    public function viewData(SamplingSchedule $schedule): array
    {
        $schedule->loadMissing(['client', 'samplePoint', 'sample_type', 'analysis_type']);

        $logoPath = $this->resolveLogoPath();
        $logoSrc = $this->resolveLogoDataUri($logoPath);

        $clientName = trim((string) ($schedule->client?->name ?? ''));
        $siteLocation = $schedule->locationDisplayName();
        $collectedBy = $schedule->personnelNames();
        $sampleTypeNames = $this->resolveSampleTypeNames($schedule);
        $parameterNames = $this->resolveParameterNames($schedule);
        $analysisTypeNames = $this->resolveAnalysisTypeNames($schedule);

        $sampleName = trim((string) ($schedule->title ?? ''));
        if ($sampleName === '') {
            $sampleName = $sampleTypeNames !== [] ? implode(', ', $sampleTypeNames) : 'N/A';
        }

        $sampleDescription = trim(strip_tags((string) ($schedule->description ?? '')));
        if ($sampleDescription === '') {
            $sampleDescription = $sampleName;
        }

        $collectionForParts = array_values(array_unique(array_filter(array_merge(
            $analysisTypeNames,
            $sampleTypeNames
        ))));
        $sampleCollectionFor = $collectionForParts !== [] ? implode(', ', $collectionForParts) : 'N/A';

        $dateTimeOfCollection = $schedule->sampling_datetime
            ? $schedule->sampling_datetime->format('Y-m-d H:i')
            : now()->format('Y-m-d H:i');

        $testRequirement = $parameterNames !== [] ? implode(', ', $parameterNames) : 'N/A';

        // Lightweight stand-in so the shared label blade can resolve barcode safely.
        $instance = (object) [
            'id' => (string) $schedule->id,
            'form_number' => '',
        ];

        return [
            'instance' => $instance,
            'logoPath' => $logoPath,
            'logoSrc' => $logoSrc,
            'labelType' => 'collection',
            'jobNumber' => '',
            'customerName' => $clientName !== '' ? $clientName : 'N/A',
            'customerAddress' => 'N/A',
            'customerPhone' => 'N/A',
            'sampleType' => $sampleTypeNames !== [] ? implode(', ', $sampleTypeNames) : 'N/A',
            'sampleDescription' => $sampleDescription !== '' ? $sampleDescription : 'N/A',
            'samplingDate' => $schedule->sampling_datetime?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'samplingPoint' => $siteLocation,
            'sampleRows' => [],
            'formData' => [],
            'sampleName' => $sampleName,
            'batchNumber' => 'N/A',
            'clientName' => $clientName !== '' ? $clientName : 'N/A',
            'siteLocation' => $siteLocation !== '' ? $siteLocation : 'N/A',
            'dateTimeOfCollection' => $dateTimeOfCollection,
            'sampleTemperature' => 'N/A',
            'collectedBy' => $collectedBy !== '' ? $collectedBy : 'N/A',
            'preservationApplied' => 'No',
            'containerType' => 'N/A',
            'sampleCollectionFor' => $sampleCollectionFor,
            'sampleId' => 'N/A',
            'testRequirement' => $testRequirement,
            'barcodeValue' => '',
        ];
    }

    /**
     * @return list<string>
     */
    private function resolveSampleTypeNames(SamplingSchedule $schedule): array
    {
        $ids = $schedule->assignedSampleTypeIds();
        if ($ids === []) {
            $name = trim((string) ($schedule->sample_type?->name ?? ''));

            return $name !== '' ? [$name] : [];
        }

        $namesById = SampleType::query()
            ->whereIn('id', $ids)
            ->pluck('name', 'id');

        $names = [];
        foreach ($ids as $id) {
            $name = trim((string) ($namesById[$id] ?? ''));
            if ($name !== '') {
                $names[] = $name;
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * @return list<string>
     */
    private function resolveAnalysisTypeNames(SamplingSchedule $schedule): array
    {
        $ids = [];
        if (! empty($schedule->analysis_type_id)) {
            $ids[] = (string) $schedule->analysis_type_id;
        }

        foreach ((array) ($schedule->sample_details ?? []) as $entry) {
            $id = trim((string) ($entry['analysis_type_id'] ?? ''));
            if ($id !== '') {
                $ids[] = $id;
            }
        }

        $ids = array_values(array_unique($ids));
        if ($ids === []) {
            $name = trim((string) ($schedule->analysis_type?->name ?? ''));

            return $name !== '' ? [$name] : [];
        }

        $namesById = AnalysisType::query()
            ->whereIn('id', $ids)
            ->pluck('name', 'id');

        $names = [];
        foreach ($ids as $id) {
            $name = trim((string) ($namesById[$id] ?? ''));
            if ($name !== '') {
                $names[] = $name;
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * @return list<string>
     */
    private function resolveParameterNames(SamplingSchedule $schedule): array
    {
        $parameterIds = [];

        foreach ((array) ($schedule->parameters ?? []) as $id) {
            $id = trim((string) $id);
            if ($id !== '') {
                $parameterIds[] = $id;
            }
        }

        foreach ((array) ($schedule->sample_details ?? []) as $entry) {
            foreach ((array) ($entry['parameters'] ?? []) as $id) {
                $id = trim((string) $id);
                if ($id !== '') {
                    $parameterIds[] = $id;
                }
            }
        }

        $parameterIds = array_values(array_unique($parameterIds));
        if ($parameterIds === []) {
            return [];
        }

        $byAnalyte = Analyte::query()
            ->whereIn('id', $parameterIds)
            ->pluck('name', 'id');

        $names = [];
        $missing = [];
        foreach ($parameterIds as $id) {
            $name = trim((string) ($byAnalyte[$id] ?? ''));
            if ($name !== '' && ! Str::isUuid($name)) {
                $names[] = $name;
            } else {
                $missing[] = $id;
            }
        }

        if ($missing !== []) {
            $fromElements = AnalysisElements::query()
                ->with('analyte')
                ->whereIn('id', $missing)
                ->get();

            foreach ($fromElements as $element) {
                $name = trim((string) ($element->analyte?->name ?? ''));
                if ($name !== '' && ! Str::isUuid($name)) {
                    $names[] = $name;
                }
            }
        }

        return array_values(array_unique(array_filter($names, static fn (string $name): bool => $name !== '')));
    }

    private function resolveLogoPath(): ?string
    {
        $activeCompany = Company::query()
            ->where('active', 1)
            ->orderByDesc('updated_at')
            ->first();

        $logoPath = null;
        if ($activeCompany) {
            $logoPath = $activeCompany->report_logo ?: $activeCompany->logo;
        }

        if (empty($logoPath)) {
            $companyLogoConfig = SystemConfiguration::query()
                ->where('key', 'company_logo')
                ->first();
            $logoPath = $companyLogoConfig?->value;
        }

        return is_string($logoPath) ? $logoPath : null;
    }

    private function resolveLogoDataUri(?string $logoPath): string
    {
        $absolutePath = $this->resolveLogoAbsolutePath($logoPath);
        if ($absolutePath === '') {
            return '';
        }

        $contents = @file_get_contents($absolutePath);
        if ($contents === false) {
            return '';
        }

        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    private function resolveLogoAbsolutePath(?string $logoPath): string
    {
        $candidates = [];
        $normalized = trim((string) $logoPath);

        if ($normalized !== '') {
            if (Str::startsWith($normalized, ['http://', 'https://'])) {
                $normalized = (string) (parse_url($normalized, PHP_URL_PATH) ?? $normalized);
            }

            $normalized = ltrim($normalized, '/');
            $filename = basename($normalized);

            if ($filename !== '') {
                $relative = preg_replace('#^storage/#', '', $normalized);
                if (is_string($relative) && $relative !== $normalized) {
                    $candidates[] = Storage::disk('public')->path($relative);
                }

                $candidates[] = storage_path('app/companies/'.$filename);
                $candidates[] = public_path($normalized);
                $candidates[] = public_path('storage/companies/'.$filename);
            }
        }

        $candidates[] = public_path('images/company_logo.png');
        $candidates[] = public_path('images/logo.png');

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '' && is_readable($candidate)) {
                return $candidate;
            }
        }

        return '';
    }
}
