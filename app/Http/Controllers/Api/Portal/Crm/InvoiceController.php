<?php

namespace App\Http\Controllers\Api\Portal\Crm;

use App\Http\Controllers\Api\V1\Concerns\RespondsWithApiEnvelope;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Portal\ListPortalInvoicesRequest;
use App\Http\Requests\Api\Portal\ShowPortalInvoiceRequest;
use App\Services\Portal\PortalInvoiceService;
use App\Services\SubmissionForm\PortalSubmissionFormAccess;
use App\Transformers\PortalCrmTransformer;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __construct(
        private readonly PortalInvoiceService $invoiceService,
        private readonly PortalSubmissionFormAccess $portalAccess,
        private readonly PortalCrmTransformer $transformer,
    ) {}

    public function index(ListPortalInvoicesRequest $request, string $customerId): JsonResponse
    {
        $paginator = $this->invoiceService->list(
            $customerId,
            $request->page(),
            $request->perPage(),
            $request->filters(),
            $this->portalAccess->portalAccountIdFromRequest($request),
        );

        return $this->successResponse(
            'Invoices loaded successfully',
            $this->transformer->transformPaginated($paginator, 'invoices'),
        );
    }

    public function show(ShowPortalInvoiceRequest $request, string $customerId, string $invoiceId): JsonResponse
    {
        $invoice = $this->invoiceService->show(
            $customerId,
            $invoiceId,
            $this->portalAccess->portalAccountIdFromRequest($request),
        );

        if (! $invoice) {
            return $this->errorResponse('Invoice not found.', [], 404);
        }

        return $this->successResponse(
            'Invoice loaded successfully',
            $invoice->toArray(),
        );
    }
}
