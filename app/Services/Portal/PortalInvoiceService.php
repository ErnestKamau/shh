<?php

namespace App\Services\Portal;

use App\DTOs\Portal\Crm\InvoiceDetailDTO;
use App\Repositories\PortalCrmRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PortalInvoiceService
{
    public function __construct(
        private readonly PortalCrmRepository $repository,
    ) {}

    /**
     * @param  array{status?: string, date_from?: string, date_to?: string}  $filters
     */
    public function list(
        string $customerId,
        int $page,
        int $perPage,
        array $filters = [],
        ?string $portalAccountId = null,
    ): LengthAwarePaginator {
        $cacheKey = sprintf(
            'portal:invoices:%s:page:%d:per:%d:status:%s:from:%s:to:%s',
            $customerId,
            $page,
            $perPage,
            $filters['status'] ?? '',
            $filters['date_from'] ?? '',
            $filters['date_to'] ?? '',
        );

        Log::channel('daily')->info('portal.invoices.list', [
            'customer_id' => $customerId,
            'portal_account_id' => $portalAccountId,
            'page' => $page,
        ]);

        return Cache::store($this->cacheStore())->remember(
            $cacheKey,
            config('portal_crm.cache.invoices_ttl', 120),
            fn () => $this->repository->paginateInvoices($customerId, $perPage, $filters),
        );
    }

    public function show(string $customerId, string $invoiceId, ?string $portalAccountId = null): ?InvoiceDetailDTO
    {
        Log::channel('daily')->info('portal.invoices.show', [
            'customer_id' => $customerId,
            'invoice_id' => $invoiceId,
            'portal_account_id' => $portalAccountId,
        ]);

        return $this->repository->findInvoiceForCustomer($customerId, $invoiceId);
    }

    private function cacheStore(): string
    {
        return (string) config('portal_crm.cache.store', config('cache.default', 'file'));
    }
}
