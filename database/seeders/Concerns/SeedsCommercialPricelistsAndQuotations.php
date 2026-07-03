<?php

namespace Database\Seeders\Concerns;

use App\AnalysisElements;
use App\Company;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistCustomer;
use App\Models\Billing\PricelistItem;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\Models\Currency;
use App\QuotationDetails;
use App\QuotationHeader;
use App\SampleType;
use App\Services\Billing\QuotationReportService;
use App\Services\Commercial\AmSpecQuotationNumberGenerator;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait SeedsCommercialPricelistsAndQuotations
{
    use ClearsSeedCommercialDemoData;

    /**
     * @return list<array{customer_code: string, pricelist_code: string, pricelist_name: string, sample_type_codes: list<string>}>
     */
    protected function commercialClientDefinitions(): array
    {
        return [
            [
                'customer_code' => 'INT-ENRG-001',
                'pricelist_code' => self::SEED_PRICELIST_CODE_PREFIX.'ADNOC',
                'pricelist_name' => 'Seed ADNOC Multi-Matrix Pricelist',
                'sample_type_codes' => ['FOOD FEED', 'WATER'],
            ],
            [
                'customer_code' => 'EXT-ENRG-001',
                'pricelist_code' => self::SEED_PRICELIST_CODE_PREFIX.'ENOC',
                'pricelist_name' => 'Seed ENOC Water & Fuels Pricelist',
                'sample_type_codes' => ['WATER'],
            ],
            [
                'customer_code' => 'EXT-ENRG-002',
                'pricelist_code' => self::SEED_PRICELIST_CODE_PREFIX.'SHELL',
                'pricelist_name' => 'Seed Shell Trading Pricelist',
                'sample_type_codes' => ['WATER', 'SMP WWTR'],
            ],
        ];
    }

    /**
     * @return array{pricelists: int, items: int, assignments: int, quotations: int, lines: int}
     */
    protected function seedCommercialPricelistsAndQuotations(Company $company, User $preparedBy): array
    {
        $this->clearSeedCommercialDemoData();

        $stats = [
            'pricelists' => 0,
            'items' => 0,
            'assignments' => 0,
            'quotations' => 0,
            'lines' => 0,
        ];

        $currency = Currency::query()->where('code', 'AED')->first()
            ?? Currency::query()->orderBy('code')->first();

        if ($currency === null) {
            $this->command?->error('No currency found for commercial demo seeding.');

            return $stats;
        }

        $reportService = app(QuotationReportService::class);
        $quoteIndex = 1;

        foreach ($this->commercialClientDefinitions() as $definition) {
            $customer = CRMCustomer::query()
                ->where('company_id', $company->id)
                ->where('code', $definition['customer_code'])
                ->first();

            if ($customer === null) {
                $this->command?->warn("Skipping commercial demo for missing customer {$definition['customer_code']}.");

                continue;
            }

            $pricelist = Pricelist::query()->create([
                'id' => (string) Str::uuid(),
                'code' => $definition['pricelist_code'],
                'description' => $definition['pricelist_name'],
                'currency_id' => $currency->id,
                'is_master' => false,
                'active' => true,
                'document_no' => strtoupper(str_replace('PL-SEED-', 'DOC-', $definition['pricelist_code'])),
                'revision_number' => '1',
                'status' => 'no-changes',
                'valid_till' => now()->addYear()->toDateString(),
            ]);
            $stats['pricelists']++;

            PricelistCustomer::query()->create([
                'id' => (string) Str::uuid(),
                'pricelist_id' => $pricelist->id,
                'customer_id' => $customer->id,
            ]);
            $stats['assignments']++;

            $items = $this->seedPricelistItemsForCustomer(
                $company,
                $pricelist,
                $definition['sample_type_codes'],
            );
            $stats['items'] += $items->count();

            $contact = CustomerContact::query()
                ->where('crm_customer_id', $customer->id)
                ->orderBy('created_at')
                ->first();

            if ($contact === null) {
                $this->command?->warn("No contact for {$customer->name}; skipping quotation.");

                continue;
            }

            $quoteStats = $this->seedCompleteQuotation(
                $customer,
                $contact,
                $pricelist,
                $items,
                $preparedBy,
                $currency,
                $reportService,
                $quoteIndex,
            );

            $stats['quotations'] += $quoteStats['quotations'];
            $stats['lines'] += $quoteStats['lines'];
            $quoteIndex++;

            $this->command?->info(sprintf(
                'Commercial demo for %s: pricelist %s (%d items), quotation %s.',
                $customer->name,
                $pricelist->code,
                $items->count(),
                $quoteStats['laboratory_ref'],
            ));
        }

        return $stats;
    }

    /**
     * @param  list<string>  $sampleTypeCodes
     * @return Collection<int, PricelistItem>
     */
    private function seedPricelistItemsForCustomer(
        Company $company,
        Pricelist $pricelist,
        array $sampleTypeCodes,
    ): Collection {
        $sampleTypeIds = $this->resolveSampleTypeIds($company, $sampleTypeCodes);

        if ($sampleTypeIds->isEmpty()) {
            return collect();
        }

        $elements = AnalysisElements::query()
            ->with(['analysis_type.sample_type', 'analyte'])
            ->whereHas('analysis_type', function ($query) use ($company, $sampleTypeIds): void {
                $query->where('company_id', $company->id)
                    ->whereIn('sample_type_id', $sampleTypeIds);
            })
            ->where('active', true)
            ->orderBy('analysis_type_id')
            ->orderBy('id')
            ->limit(12)
            ->get();

        $level = 1;
        $created = collect();

        foreach ($elements as $element) {
            $analysisType = $element->analysis_type;
            $sampleType = $analysisType?->sample_type;

            if ($analysisType === null || $sampleType === null) {
                continue;
            }

            $sell = 45 + ($level * 5);
            $cost = (int) round($sell * 0.55);

            $item = PricelistItem::query()->create([
                'id' => (string) Str::uuid(),
                'pricelist_id' => $pricelist->id,
                'sample_type_id' => $sampleType->id,
                'analysis_id' => $analysisType->id,
                'analysis_element_id' => $element->id,
                'cost_price' => $cost,
                'selling_price' => $sell,
                'changed_price' => $sell,
                'vat' => false,
                'internal_use' => false,
                'external_view' => true,
                'active' => true,
                'level' => $level,
            ]);

            $created->push($item);
            $level++;
        }

        return $created;
    }

    /**
     * @param  Collection<int, PricelistItem>  $items
     * @return array{quotations: int, lines: int, laboratory_ref: string}
     */
    private function seedCompleteQuotation(
        CRMCustomer $customer,
        CustomerContact $contact,
        Pricelist $pricelist,
        Collection $items,
        User $preparedBy,
        Currency $currency,
        QuotationReportService $reportService,
        int $quoteIndex,
    ): array {
        $laboratoryRef = self::SEED_QUOTATION_LAB_REF_PREFIX.str_pad((string) $quoteIndex, 3, '0', STR_PAD_LEFT);
        $quoteDate = now()->subDays(14 - $quoteIndex)->toDateString();
        $expireDate = now()->addDays(30)->toDateString();

        $header = QuotationHeader::query()->create([
            'id' => (string) Str::uuid(),
            'crm_customer_id' => $customer->id,
            'crm_customer_contact_id' => $contact->id,
            'pricelist_id' => $pricelist->id,
            'currency_id' => $currency->id,
            'prepared_by_id' => (string) $preparedBy->id,
            'approved_by' => (string) $preparedBy->id,
            'quote_date' => $quoteDate,
            'expiring_date' => $expireDate,
            'quotation_type' => 'Analysis',
            'status' => 'Quote Complete',
            'subject' => "Seed quotation for {$customer->name}",
            'laboratory_ref' => $laboratoryRef,
            'is_draft' => false,
            'is_complete' => true,
            'is_approved' => true,
            'is_print' => false,
            'show_loq_column' => true,
            'show_mu_column' => true,
            'show_unit_price_column' => true,
            'sent_to_customer_at' => now()->subDays(10 - $quoteIndex),
        ]);

        AmSpecQuotationNumberGenerator::assignIfMissing($header);
        $reportService->ensureHeaderMetadata($header->fresh());
        $reportService->seedDefaultTermsOfSale($header->fresh());
        $reportService->seedDefaultStructuredTerms($header->fresh());

        $subTotal = 0.0;
        $lineCount = 0;

        foreach ($items->take(8) as $item) {
            $item->loadMissing(['analysisType', 'analysisElement.analyte', 'sampleType']);
            $unitPrice = (float) ($item->selling_price ?? $item->changed_price ?? 50);
            $quantity = 1;

            QuotationDetails::query()->create([
                'id' => (string) Str::uuid(),
                'quotation_header_id' => $header->id,
                'analyte_id' => $item->analysisElement?->analyte_id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'tax' => 0,
                'sample_type' => $item->sample_type_id,
                'item_name' => $item->analysisElement?->analyte?->name ?? $item->analysisType?->name ?? 'Analysis parameter',
                'description' => $item->analysisType?->name ?? 'Laboratory analysis',
            ]);

            $subTotal += $unitPrice * $quantity;
            $lineCount++;
        }

        $header->update([
            'sub_total' => $subTotal,
            'tax' => 0,
            'total_amount' => $subTotal,
        ]);

        return [
            'quotations' => 1,
            'lines' => $lineCount,
            'laboratory_ref' => $laboratoryRef,
        ];
    }

    /**
     * @param  list<string>  $sampleTypeCodes
     * @return Collection<int, string>
     */
    private function resolveSampleTypeIds(Company $company, array $sampleTypeCodes): Collection
    {
        $ids = collect();

        foreach ($sampleTypeCodes as $code) {
            $matches = SampleType::query()
                ->where('company_id', $company->id)
                ->where(function ($query) use ($code): void {
                    $query->where('code', $code)
                        ->orWhere('code', 'ilike', '%'.$code.'%')
                        ->orWhere('name', 'ilike', '%'.$code.'%');
                })
                ->pluck('id');

            $ids = $ids->merge($matches);
        }

        return $ids->unique()->values();
    }
}
