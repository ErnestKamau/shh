<?php

namespace App\Services\Procedures;

use App\Models\Procedures\ProcedureConfigField;
use App\SampleDetails;
use App\SampleHeader;
use Carbon\Carbon;
class ProcedureConfigFieldSampleResolver
{
    public function resolve(ProcedureConfigField $field, ?SampleDetails $sample, ?SampleHeader $header = null): string
    {
        if (! $sample) {
            return '';
        }

        $header ??= $sample->sample_header_id
            ? SampleHeader::find($sample->sample_header_id)
            : null;

        return match ($field->field_type) {
            'customer_select' => $this->resolveCustomer($header),
            'lab_select' => $this->resolveLab($sample),
            'sample_select' => $this->resolveSampleColumn($sample, (string) ($field->model_tied_to ?? '')),
            default => '',
        };
    }

    public function resolveCustomer(?SampleHeader $header): string
    {
        if (! $header) {
            return '';
        }

        $customer = $header->customer ?? $header->client ?? null;

        if (! $customer) {
            return '';
        }

        return (string) ($customer->name ?? $customer->company_name ?? $customer->code ?? '');
    }

    public function resolveLab(SampleDetails $sample): string
    {
        $lab = $sample->lab;

        if ($lab) {
            $code = $lab->code ?? null;

            return trim(($lab->name ?? '') . ($code ? " ({$code})" : ''));
        }

        if ($sample->lab_id) {
            return (string) $sample->lab_id;
        }

        return '';
    }

    public function resolveSampleColumn(SampleDetails $sample, string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        $segments = explode('.', $path);
        $column = array_shift($segments);

        if ($column === '') {
            return '';
        }

        $raw = $sample->getAttribute($column);

        if ($segments === []) {
            return $this->formatScalar($raw);
        }

        $relationColumn = implode('.', $segments);
        $fkId = $raw;

        if ($fkId === null || $fkId === '') {
            return '';
        }

        $resolver = ProcedureConfigFieldSampleCatalog::relationResolver($column);
        $related = $resolver ? $resolver($fkId) : null;

        if (! $related) {
            return (string) $fkId;
        }

        if (str_contains($relationColumn, '.')) {
            $parts = explode('.', $relationColumn);
            $value = $related;
            foreach ($parts as $part) {
                if (is_object($value)) {
                    $value = $value->{$part} ?? null;
                } else {
                    $value = null;
                    break;
                }
            }

            return $this->formatScalar($value);
        }

        return $this->formatScalar($related->{$relationColumn} ?? $related->getAttribute($relationColumn) ?? '');
    }

    protected function formatScalar(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if ($value instanceof Carbon) {
            return $value->format('Y-m-d H:i');
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return implode(', ', array_map('strval', $value));
        }

        return (string) $value;
    }
}
