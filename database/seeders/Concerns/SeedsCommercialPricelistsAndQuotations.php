<?php

namespace Database\Seeders\Concerns;

use App\AnalysisElements;
use App\Company;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistCustomer;
use App\Models\Billing\PricelistItem;
use App\Models\Billing\PricelistItemElement;
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
    use ClearsAmSpecFoodPricelistData;
    use ClearsSeedCommercialDemoData;

    /** @var list<string> */
    private const PER_PARAMETER_SAMPLE_TYPE_CODES = ['FOOD', 'WATER', 'FOOD FEED'];

    /** @var list<string> */
    private const PACKAGE_SAMPLE_TYPE_CODES = ['FOOD', 'WATER'];

    /**
     * @return list<array{customer_code: string, use_package_quote: bool}>
     */
    protected function commercialClientDefinitions(): array
    {
        return [
            [
                'customer_code' => 'INT-ENRG-001',
                'use_package_quote' => true,
            ],
            [
                'customer_code' => 'EXT-ENRG-001',
                'use_package_quote' => false,
            ],
            [
                'customer_code' => 'EXT-ENRG-002',
                'use_package_quote' => false,
            ],
        ];
    }

    /**
     * Seeds exactly three pricelists:
     * - master per-parameter
     * - customer per-parameter
     * - one package pricelist
     *
     * @return array{pricelists: int, items: int, assignments: int, quotations: int, lines: int}
     */
    protected function seedCommercialPricelistsAndQuotations(Company $company, User $preparedBy): array
    {
        $this->clearSeedCommercialDemoData();
        $this->clearAmSpecFoodPricelistDataIfPresent();

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

        $elements = $this->resolveUniqueActiveElements($company, self::PER_PARAMETER_SAMPLE_TYPE_CODES);

        if ($elements->isEmpty()) {
            $this->command?->warn('No analysis elements found for seed sample types. Run taxonomy/parameter seeders first.');

            return $stats;
        }

        $master = $this->createSeedPricelist(
            $currency,
            self::SEED_PRICELIST_CODE_MASTER,
            'Seed Master Per-Parameter Pricelist',
            isMaster: true,
        );
        $customer = $this->createSeedPricelist(
            $currency,
            self::SEED_PRICELIST_CODE_CUSTOMER,
            'Seed Customer Contract Per-Parameter Pricelist',
            isMaster: false,
        );
        $package = $this->createSeedPricelist(
            $currency,
            self::SEED_PRICELIST_CODE_PACKAGE,
            'Seed Water & Food Package Pricelist',
            isMaster: false,
        );
        $stats['pricelists'] = 3;

        $stats['items'] += $this->seedPerParameterItems($master, $elements, priceMultiplier: 1.0);
        $stats['items'] += $this->seedPerParameterItems($customer, $elements, priceMultiplier: 0.90);
        $stats['items'] += $this->seedPackageItems(
            $package,
            $this->resolveUniqueActiveElements($company, self::PACKAGE_SAMPLE_TYPE_CODES),
        );

        $customersByCode = [];
        foreach ($this->commercialClientDefinitions() as $definition) {
            $crmCustomer = CRMCustomer::query()
                ->where('company_id', $company->id)
                ->where('code', $definition['customer_code'])
                ->first();

            if ($crmCustomer === null) {
                $this->command?->warn("Skipping assignment for missing customer {$definition['customer_code']}.");

                continue;
            }

            $customersByCode[$definition['customer_code']] = [
                'customer' => $crmCustomer,
                'use_package_quote' => $definition['use_package_quote'],
            ];
        }

        foreach ($customersByCode as $entry) {
            /** @var CRMCustomer $crmCustomer */
            $crmCustomer = $entry['customer'];

            PricelistCustomer::query()->create([
                'id' => (string) Str::uuid(),
                'pricelist_id' => $customer->id,
                'customer_id' => $crmCustomer->id,
            ]);
            $stats['assignments']++;

            if ($entry['use_package_quote']) {
                PricelistCustomer::query()->create([
                    'id' => (string) Str::uuid(),
                    'pricelist_id' => $package->id,
                    'customer_id' => $crmCustomer->id,
                ]);
                $stats['assignments']++;
            }
        }

        // Keep master assigned to one client for demo discovery.
        if (isset($customersByCode['EXT-ENRG-002'])) {
            PricelistCustomer::query()->create([
                'id' => (string) Str::uuid(),
                'pricelist_id' => $master->id,
                'customer_id' => $customersByCode['EXT-ENRG-002']['customer']->id,
            ]);
            $stats['assignments']++;
        }

        $reportService = app(QuotationReportService::class);
        $quoteIndex = 1;
        $customerItems = PricelistItem::query()
            ->with(['analysisType', 'analysisElement.analyte', 'sampleType', 'packageElements'])
            ->where('pricelist_id', $customer->id)
            ->where('is_package', false)
            ->orderBy('level')
            ->get();
        $packageItems = PricelistItem::query()
            ->with(['analysisType', 'sampleType', 'packageElements'])
            ->where('pricelist_id', $package->id)
            ->where('is_package', true)
            ->orderBy('level')
            ->get();

        foreach ($customersByCode as $entry) {
            /** @var CRMCustomer $crmCustomer */
            $crmCustomer = $entry['customer'];

            $contact = CustomerContact::query()
                ->where('crm_customer_id', $crmCustomer->id)
                ->orderBy('created_at')
                ->first();

            if ($contact === null) {
                $this->command?->warn("No contact for {$crmCustomer->name}; skipping quotation.");

                continue;
            }

            $usePackage = (bool) $entry['use_package_quote'];
            $quotePricelist = $usePackage ? $package : $customer;
            $quoteItems = $usePackage ? $packageItems : $customerItems;

            if ($quoteItems->isEmpty()) {
                continue;
            }

            $quoteStats = $this->seedCompleteQuotation(
                $crmCustomer,
                $contact,
                $quotePricelist,
                $quoteItems,
                $preparedBy,
                $currency,
                $reportService,
                $quoteIndex,
                $usePackage,
            );

            $stats['quotations'] += $quoteStats['quotations'];
            $stats['lines'] += $quoteStats['lines'];
            $quoteIndex++;

            $this->command?->info(sprintf(
                'Commercial demo for %s: %s pricelist (%d items available), quotation %s.',
                $crmCustomer->name,
                $usePackage ? 'package' : 'per-parameter',
                $quoteItems->count(),
                $quoteStats['laboratory_ref'],
            ));
        }

        $this->command?->info(sprintf(
            'Seeded 3 pricelists: %s (master/per-parameter), %s (customer/per-parameter), %s (package).',
            self::SEED_PRICELIST_CODE_MASTER,
            self::SEED_PRICELIST_CODE_CUSTOMER,
            self::SEED_PRICELIST_CODE_PACKAGE,
        ));

        return $stats;
    }

    private function clearAmSpecFoodPricelistDataIfPresent(): void
    {
        if (self::FOOD_PRICELIST_CODE === self::SEED_PRICELIST_CODE_PACKAGE) {
            return;
        }

        $this->clearAmSpecFoodPricelistData();
    }

    private function createSeedPricelist(
        Currency $currency,
        string $code,
        string $description,
        bool $isMaster,
    ): Pricelist {
        return Pricelist::query()->create([
            'id' => (string) Str::uuid(),
            'code' => $code,
            'description' => $description,
            'currency_id' => $currency->id,
            'is_master' => $isMaster,
            'active' => true,
            'document_no' => strtoupper(str_replace('PL-SEED-', 'DOC-', $code)),
            'revision_number' => '1',
            'status' => 'no-changes',
            'valid_till' => now()->addYear()->toDateString(),
        ]);
    }

    /**
     * @param  list<string>  $sampleTypeCodes
     * @return Collection<int, AnalysisElements>
     */
    private function resolveUniqueActiveElements(Company $company, array $sampleTypeCodes): Collection
    {
        $sampleTypeIds = $this->resolveSampleTypeIdsExactly($company, $sampleTypeCodes);

        if ($sampleTypeIds->isEmpty()) {
            return collect();
        }

        return AnalysisElements::query()
            ->with(['analysis_type.sample_type', 'analyte'])
            ->whereHas('analysis_type', function ($query) use ($company, $sampleTypeIds): void {
                $query->where('company_id', $company->id)
                    ->whereIn('sample_type_id', $sampleTypeIds);
            })
            ->where('active', true)
            ->orderBy('analysis_type_id')
            ->orderBy('id')
            ->get()
            ->unique('id')
            ->values();
    }

    /**
     * One row per unique analysis element; never repeats the same parameter on a pricelist.
     *
     * @param  Collection<int, AnalysisElements>  $elements
     */
    private function seedPerParameterItems(
        Pricelist $pricelist,
        Collection $elements,
        float $priceMultiplier,
    ): int {
        $level = 1;
        $created = 0;
        $seenElementIds = [];

        foreach ($elements as $element) {
            $elementId = (string) $element->id;
            if (isset($seenElementIds[$elementId])) {
                continue;
            }

            $analysisType = $element->analysis_type;
            $sampleType = $analysisType?->sample_type;

            if ($analysisType === null || $sampleType === null) {
                continue;
            }

            $seenElementIds[$elementId] = true;

            $cost = 35 + ((($level - 1) % 10) * 5);
            $sell = round(($cost * 1.60) * $priceMultiplier, 2);

            PricelistItem::query()->create([
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
                'is_package' => false,
                'level' => $level,
            ]);

            $level++;
            $created++;
        }

        return $created;
    }

    /**
     * One package row per analysis type (never repeated) on the single package pricelist.
     *
     * @param  Collection<int, AnalysisElements>  $elements
     */
    private function seedPackageItems(Pricelist $pricelist, Collection $elements): int
    {
        $groups = $elements->groupBy('analysis_type_id');
        $level = 1;
        $created = 0;
        $seenAnalysisTypeIds = [];

        foreach ($groups as $analysisTypeId => $analysisElements) {
            $analysisTypeKey = (string) $analysisTypeId;
            if (isset($seenAnalysisTypeIds[$analysisTypeKey])) {
                continue;
            }

            $first = $analysisElements->first();
            $analysisType = $first?->analysis_type;
            $sampleType = $analysisType?->sample_type;

            if ($analysisType === null || $sampleType === null) {
                continue;
            }

            $seenAnalysisTypeIds[$analysisTypeKey] = true;
            $uniqueElements = $analysisElements->unique('id')->values();
            $parameterCount = $uniqueElements->count();

            if ($parameterCount === 0) {
                continue;
            }

            $cost = 25 * $parameterCount;
            $sell = round($cost * 1.70, 2);

            $item = PricelistItem::query()->create([
                'id' => (string) Str::uuid(),
                'pricelist_id' => $pricelist->id,
                'sample_type_id' => $sampleType->id,
                'analysis_id' => $analysisType->id,
                'analysis_element_id' => null,
                'cost_price' => $cost,
                'selling_price' => $sell,
                'changed_price' => $sell,
                'vat' => false,
                'internal_use' => false,
                'external_view' => true,
                'active' => true,
                'is_package' => true,
                'level' => $level,
            ]);

            foreach ($uniqueElements as $element) {
                PricelistItemElement::query()->create([
                    'id' => (string) Str::uuid(),
                    'pricelist_item_id' => $item->id,
                    'analysis_element_id' => $element->id,
                ]);
            }

            $level++;
            $created++;
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
        bool $asPackage,
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
            $item->loadMissing(['analysisType', 'analysisElement.analyte', 'sampleType', 'packageElements']);
            $unitPrice = (float) ($item->selling_price ?? $item->changed_price ?? 50);
            $quantity = 1;

            if ($asPackage || (bool) $item->is_package) {
                $packageElementIds = $item->coveredElementIds();
                $elementIdCsv = implode(',', $packageElementIds);
                $analysisName = $item->analysisType?->name ?? 'Laboratory analysis';
                $parameterCount = count($packageElementIds);

                QuotationDetails::query()->create([
                    'id' => (string) Str::uuid(),
                    'quotation_header_id' => $header->id,
                    'analyte_id' => null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax' => 0,
                    'sample_type' => $item->sample_type_id,
                    'part_no' => $item->analysis_id,
                    'item_name' => $parameterCount > 0
                        ? "{$analysisName} package ({$parameterCount} parameters)"
                        : $analysisName,
                    'description' => $analysisName,
                    'default_analytes' => $elementIdCsv,
                    'accredited_analytes' => $elementIdCsv,
                    'is_package' => true,
                ]);
            } else {
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
                    'is_package' => false,
                ]);
            }

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
    private function resolveSampleTypeIdsExactly(Company $company, array $sampleTypeCodes): Collection
    {
        return SampleType::query()
            ->where('company_id', $company->id)
            ->whereIn('code', $sampleTypeCodes)
            ->pluck('id')
            ->unique()
            ->values();
    }
}
