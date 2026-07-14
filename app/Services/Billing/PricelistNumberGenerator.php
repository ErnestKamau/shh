<?php

namespace App\Services\Billing;

use App\NamingConvensionConsensus;
use Illuminate\Support\Facades\DB;

final class PricelistNumberGenerator
{
    /**
     * @return array{code: string, document_no: string, sequence: int}
     */
    public function next(?string $companyId = null, ?int $year = null): array
    {
        $year ??= (int) now()->format('Y');
        $prefix = 'PL-'.$year.'-';

        return DB::transaction(function () use ($companyId, $prefix, $year): array {
            $query = NamingConvensionConsensus::query()
                ->where('model', 'Pricelist')
                ->where('string_part', $prefix);

            if ($companyId === null) {
                $query->whereNull('company_id');
            } else {
                $query->where('company_id', $companyId);
            }

            $consensus = $query->lockForUpdate()->first();
            $sequence = $consensus === null
                ? 1
                : ((int) $consensus->getAttribute('integer_part') + 1);

            if ($consensus === null) {
                $consensus = new NamingConvensionConsensus();
                $consensus->setAttribute('string_part', $prefix);
                $consensus->setAttribute('model', 'Pricelist');
                $consensus->setAttribute('company_id', $companyId);
            }

            $consensus->setAttribute(
                'integer_part',
                str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
            );
            $consensus->save();

            $suffix = str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

            return [
                'code' => 'PL-'.$year.'-'.$suffix,
                'document_no' => 'DOC-'.$year.'-'.$suffix,
                'sequence' => $sequence,
            ];
        });
    }
}
