<?php

namespace App\Services\Billing;

use App\AnalysisElements;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistCustomer;
use App\Models\Billing\PricelistEmailLog;
use App\Models\Billing\PricelistItem;
use App\Models\Billing\PricelistItemElement;
use App\Models\CRM\CRMCustomer;
use App\Models\Currency;
use App\SampleType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class PricelistSeedService
{
    /** @var array<string, string> */
    private const CUSTOMER_NAMES = [
        'adnoc' => 'ADNOC Group',
        'enoc' => 'Emirates National Oil Company (ENOC)',
        'dp_world' => 'DP World UAE',
        'gulftainer' => 'Gulftainer Company',
    ];

    public function __construct(
        private readonly PricelistNumberGenerator $numberGenerator,
    ) {}

    /**
     * @return array{
     *     pricelists: int,
     *     items: int,
     *     packages: int,
     *     assignments: int,
     *     master_elements: int,
     *     package_analysis_types: list<string>
     * }
     */
    public function rebuild(string $currencyCode = 'AED'): array
    {
        $this->assertPackagePricingSchema();

        $customers = $this->resolveCustomers();
        $companyId = $this->resolveCommonCompanyId($customers);
        $currency = $this->resolveCurrency($currencyCode);
        $sampleTypes = $this->resolveSampleTypes($companyId);
        $elements = $this->resolveMasterElements($sampleTypes);

        if ($elements->isEmpty()) {
            throw new \RuntimeException('No active analysis elements were found for the Water and Food sample types.');
        }

        return DB::transaction(function () use ($companyId, $currency, $sampleTypes, $elements, $customers): array {
            $oldPricelistIds = Pricelist::query()->pluck('id')->all();

            $master = $this->createPricelist(
                'Master Water & Food Per-Parameter Pricelist',
                (string) $currency->id,
                true,
                $companyId,
            );
            $customer = $this->createPricelist(
                'Customer Contract Per-Parameter Pricelist',
                (string) $currency->id,
                false,
                $companyId,
            );
            $package = $this->createPricelist(
                'Water & Food Analysis Package Pricelist',
                (string) $currency->id,
                false,
                $companyId,
            );

            $itemCount = $this->createPerParameterItems($master, $customer, $elements);
            $packageAnalysisTypes = $this->createPackageItems($package, $elements, $sampleTypes);

            $this->replaceOldPricelists($oldPricelistIds, (string) $master->id);

            $assignmentCount = $this->assignPricelist($customer, $customers['adnoc'])
                + $this->assignPricelist($package, $customers['adnoc'])
                + $this->assignPricelist($customer, $customers['enoc'])
                + $this->assignPricelist($customer, $customers['dp_world'])
                + $this->assignPricelist($master, $customers['gulftainer']);

            return [
                'pricelists' => 3,
                'items' => $itemCount + count($packageAnalysisTypes),
                'packages' => count($packageAnalysisTypes),
                'assignments' => $assignmentCount,
                'master_elements' => $elements->count(),
                'package_analysis_types' => $packageAnalysisTypes,
            ];
        });
    }

    private function assertPackagePricingSchema(): void
    {
        $hasRequiredSchema = Schema::hasColumn('pricelist_items', 'is_package')
            && Schema::hasTable('pricelist_item_elements')
            && Schema::hasColumn('quotation_details', 'is_package');

        if (! $hasRequiredSchema) {
            throw new \RuntimeException(
                'Package pricing schema is missing. Run "php artisan migrate" before rebuilding pricelists.',
            );
        }
    }

    /**
     * @return array<string, CRMCustomer>
     */
    private function resolveCustomers(): array
    {
        $customers = [];

        foreach (self::CUSTOMER_NAMES as $key => $name) {
            $matches = CRMCustomer::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->get();

            if ($matches->count() !== 1) {
                throw new \RuntimeException(sprintf(
                    'Expected exactly one CRM customer named "%s"; found %d.',
                    $name,
                    $matches->count(),
                ));
            }

            $customers[$key] = $matches->first();
        }

        return $customers;
    }

    /**
     * @param  array<string, CRMCustomer>  $customers
     */
    private function resolveCommonCompanyId(array $customers): ?string
    {
        $companyIds = collect($customers)
            ->pluck('company_id')
            ->filter()
            ->map(fn ($id): string => (string) $id)
            ->unique()
            ->values();

        if ($companyIds->count() > 1) {
            throw new \RuntimeException('The four target customers belong to different companies.');
        }

        return $companyIds->first();
    }

    private function resolveCurrency(string $currencyCode): Currency
    {
        /** @var Currency|null $currency */
        $currency = Currency::query()
            ->whereRaw('UPPER(code) = ?', [mb_strtoupper(trim($currencyCode))])
            ->first();

        if ($currency === null) {
            throw new \RuntimeException("Currency {$currencyCode} was not found.");
        }

        return $currency;
    }

    /**
     * @return Collection<string, SampleType>
     */
    private function resolveSampleTypes(?string $companyId): Collection
    {
        $sampleTypes = collect();

        foreach (['Water', 'Food'] as $name) {
            $query = SampleType::query()
                ->where(function ($query) use ($name): void {
                    $query->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                        ->orWhereRaw('LOWER(code) = ?', [mb_strtolower($name)]);
                })
                ->where(function ($query): void {
                    $query->where('active', true)->orWhereNull('active');
                });

            if ($companyId !== null && Schema::hasColumn('sample_types', 'company_id')) {
                $query->where('company_id', $companyId);
            }

            $sampleType = $query->orderBy('name')->first();

            if ($sampleType === null) {
                throw new \RuntimeException("Active {$name} sample type was not found.");
            }

            $sampleTypes->put(mb_strtolower($name), $sampleType);
        }

        return $sampleTypes;
    }

    /**
     * @param  Collection<string, SampleType>  $sampleTypes
     * @return Collection<int, AnalysisElements>
     */
    private function resolveMasterElements(Collection $sampleTypes): Collection
    {
        return AnalysisElements::query()
            ->with(['analysis_type', 'analyte'])
            ->whereHas('analysis_type', function ($query) use ($sampleTypes): void {
                $query->whereIn('sample_type_id', $sampleTypes->pluck('id'))
                    ->where(function ($query): void {
                        $query->where('active', true)->orWhereNull('active');
                    });
            })
            ->where(function ($query): void {
                $query->where('active', true)->orWhereNull('active');
            })
            ->get()
            ->sortBy(fn (AnalysisElements $element): string => implode('|', [
                (string) ($element->analysis_type?->sample_type_id ?? ''),
                str_pad((string) ($element->analysis_type?->level ?? 0), 8, '0', STR_PAD_LEFT),
                (string) ($element->analysis_type?->name ?? ''),
                str_pad((string) ($element->level ?? 0), 8, '0', STR_PAD_LEFT),
                (string) ($element->analyte?->name ?? ''),
            ]))
            ->values();
    }

    private function createPricelist(
        string $description,
        string $currencyId,
        bool $isMaster,
        ?string $companyId,
    ): Pricelist {
        $numbers = $this->numberGenerator->next($companyId);

        return Pricelist::query()->create([
            'id' => (string) Str::uuid(),
            'code' => $numbers['code'],
            'description' => $description,
            'currency_id' => $currencyId,
            'is_master' => $isMaster,
            'active' => true,
            'document_no' => $numbers['document_no'],
            'revision_number' => '1',
            'status' => 'no-changes',
            'valid_till' => now()->addYear()->toDateString(),
        ]);
    }

    /**
     * @param  Collection<int, AnalysisElements>  $elements
     */
    private function createPerParameterItems(
        Pricelist $master,
        Pricelist $customer,
        Collection $elements,
    ): int {
        $level = 1;

        foreach ($elements as $element) {
            $analysisType = $element->analysis_type;

            if ($analysisType === null) {
                continue;
            }

            $costPrice = 35 + (($level - 1) % 10) * 5;
            $masterSellingPrice = round($costPrice * 1.60, 2);
            $customerSellingPrice = round($masterSellingPrice * 0.90, 2);

            $this->createParameterItem($master, $element, $costPrice, $masterSellingPrice, $level);
            $this->createParameterItem($customer, $element, $costPrice, $customerSellingPrice, $level);

            $level++;
        }

        return ($level - 1) * 2;
    }

    private function createParameterItem(
        Pricelist $pricelist,
        AnalysisElements $element,
        float $costPrice,
        float $sellingPrice,
        int $level,
    ): void {
        PricelistItem::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $pricelist->id,
            'sample_type_id' => $element->analysis_type->sample_type_id,
            'analysis_id' => $element->analysis_type_id,
            'analysis_element_id' => $element->id,
            'cost_price' => $costPrice,
            'selling_price' => $sellingPrice,
            'changed_price' => $sellingPrice,
            'vat' => false,
            'internal_use' => false,
            'external_view' => true,
            'active' => true,
            'is_package' => false,
            'level' => $level,
        ]);
    }

    /**
     * @param  Collection<int, AnalysisElements>  $elements
     * @param  Collection<string, SampleType>  $sampleTypes
     * @return list<string>
     */
    private function createPackageItems(
        Pricelist $packagePricelist,
        Collection $elements,
        Collection $sampleTypes,
    ): array {
        $analysisNames = [];
        $level = 1;

        foreach ($sampleTypes as $sampleType) {
            $analysisGroup = $elements
                ->filter(fn (AnalysisElements $element): bool => (
                    (string) $element->analysis_type?->sample_type_id === (string) $sampleType->id
                ))
                ->groupBy('analysis_type_id')
                ->first();

            if (! $analysisGroup instanceof Collection || $analysisGroup->isEmpty()) {
                throw new \RuntimeException("No package-capable analysis type found for {$sampleType->name}.");
            }

            /** @var AnalysisElements $firstElement */
            $firstElement = $analysisGroup->first();
            $analysisType = $firstElement->analysis_type;

            if ($analysisType === null) {
                throw new \RuntimeException("Package analysis type could not be loaded for {$sampleType->name}.");
            }

            $parameterTotal = 0.0;
            $costTotal = 0.0;

            foreach ($analysisGroup as $element) {
                $position = $elements->search(fn (AnalysisElements $candidate): bool => $candidate->id === $element->id);
                $elementLevel = $position === false ? 1 : $position + 1;
                $cost = 35 + (($elementLevel - 1) % 10) * 5;
                $costTotal += $cost;
                $parameterTotal += round(($cost * 1.60) * 0.90, 2);
            }

            $packageItem = PricelistItem::query()->create([
                'id' => (string) Str::uuid(),
                'pricelist_id' => $packagePricelist->id,
                'sample_type_id' => $sampleType->id,
                'analysis_id' => $analysisType->id,
                'analysis_element_id' => null,
                'cost_price' => round($costTotal, 2),
                'selling_price' => round($parameterTotal * 0.85, 2),
                'changed_price' => round($parameterTotal * 0.85, 2),
                'vat' => false,
                'internal_use' => false,
                'external_view' => true,
                'active' => true,
                'is_package' => true,
                'level' => $level++,
            ]);

            foreach ($analysisGroup as $element) {
                PricelistItemElement::query()->create([
                    'id' => (string) Str::uuid(),
                    'pricelist_item_id' => $packageItem->id,
                    'analysis_element_id' => $element->id,
                ]);
            }

            $analysisNames[] = (string) $analysisType->name;
        }

        return $analysisNames;
    }

    /**
     * @param  list<string>  $oldPricelistIds
     */
    private function replaceOldPricelists(array $oldPricelistIds, string $newMasterPricelistId): void
    {
        PricelistCustomer::query()->delete();
        PricelistEmailLog::query()->delete();

        if ($oldPricelistIds === []) {
            return;
        }

        if (Schema::hasTable('quotation_headers') && Schema::hasColumn('quotation_headers', 'pricelist_id')) {
            DB::table('quotation_headers')
                ->whereIn('pricelist_id', $oldPricelistIds)
                ->update(['pricelist_id' => $newMasterPricelistId]);
        }

        if (Schema::hasTable('customer_invoice') && Schema::hasColumn('customer_invoice', 'pricelist_id')) {
            DB::table('customer_invoice')
                ->whereIn('pricelist_id', $oldPricelistIds)
                ->update(['pricelist_id' => $newMasterPricelistId]);
        }

        if (Schema::hasTable('analysis_acceptance_forms')
            && Schema::hasColumn('analysis_acceptance_forms', 'pricelist_id')) {
            DB::table('analysis_acceptance_forms')
                ->whereIn('pricelist_id', $oldPricelistIds)
                ->update(['pricelist_id' => $newMasterPricelistId]);
        }

        Pricelist::query()->whereIn('id', $oldPricelistIds)->delete();
    }

    private function assignPricelist(Pricelist $pricelist, CRMCustomer $customer): int
    {
        PricelistCustomer::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $pricelist->id,
            'customer_id' => $customer->id,
        ]);

        return 1;
    }
}
