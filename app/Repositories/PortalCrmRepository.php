<?php

namespace App\Repositories;

use App\DTOs\Portal\Crm\ComplaintDTO;
use App\DTOs\Portal\Crm\ComplaintTypeDTO;
use App\DTOs\Portal\Crm\FeedbackListItemDTO;
use App\DTOs\Portal\Crm\FeedbackMetricDTO;
use App\DTOs\Portal\Crm\InvoiceDetailDTO;
use App\DTOs\Portal\Crm\InvoiceLineItemDTO;
use App\DTOs\Portal\Crm\InvoiceListItemDTO;
use App\DTOs\Portal\Crm\InvoicePaymentDTO;
use App\Invoice;
use App\InvoiceDetails;
use App\InvoicePaymentDetail;
use App\Models\CRM\Complaint;
use App\Models\CRM\Complaint_Type;
use App\Models\CRM\CustomerFeedback;
use App\Models\CRM\EvaluationMetric;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PortalCrmRepository
{
    public function paginateComplaints(string $customerId, int $perPage): LengthAwarePaginator
    {
        return Complaint::query()
            ->where('client_id', $customerId)
            ->orderByDesc('date')
            ->paginate($perPage)
            ->through(fn (Complaint $complaint) => $this->mapComplaint($complaint));
    }

    public function paginateFeedback(string $customerId, int $perPage): LengthAwarePaginator
    {
        return CustomerFeedback::query()
            ->where('customer_id', $customerId)
            ->where('is_submitted', true)
            ->orderByDesc('submitted_at')
            ->paginate($perPage)
            ->through(fn (CustomerFeedback $feedback) => $this->mapFeedbackListItem($feedback));
    }

    /**
     * @return list<ComplaintTypeDTO>
     */
    public function activeComplaintTypes(): array
    {
        return Complaint_Type::query()
            ->where(function ($query): void {
                $query->where('status', 1)->orWhere('status', 'active');
            })
            ->orderBy('name')
            ->get()
            ->map(fn (Complaint_Type $type) => new ComplaintTypeDTO(
                id: (string) $type->id,
                name: (string) $type->name,
                description: $type->description,
            ))
            ->all();
    }

    /**
     * @return list<FeedbackMetricDTO>
     */
    public function activeFeedbackMetrics(): array
    {
        return EvaluationMetric::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->map(fn (EvaluationMetric $metric) => new FeedbackMetricDTO(
                id: (string) $metric->id,
                name: (string) $metric->name,
                maxRating: (int) $metric->max_rating,
                sortOrder: (int) $metric->display_order,
                promptText: $metric->prompt_text,
            ))
            ->all();
    }

    /**
     * @param  array{status?: string, date_from?: string, date_to?: string}  $filters
     */
    public function paginateInvoices(string $customerId, int $perPage, array $filters = []): LengthAwarePaginator
    {
        $query = Invoice::query()
            ->with('currencyinfo')
            ->where('customer_id', $customerId)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at');

        if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
            $query->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
        }

        if (! empty($filters['status'])) {
            return $this->paginateInvoicesWithStatusFilter($query, $perPage, (string) $filters['status']);
        }

        $paginator = $query->paginate($perPage);
        $invoiceIds = collect($paginator->items())->pluck('id')->all();
        $paymentsByInvoice = $this->paymentsByInvoiceIds($invoiceIds);

        $paginator->setCollection(
            collect($paginator->items())->map(function (Invoice $invoice) use ($paymentsByInvoice) {
                $paid = round((float) ($paymentsByInvoice[$invoice->id] ?? 0), 2);

                return $this->mapInvoiceListItem($invoice, $paid);
            })
        );

        return $paginator;
    }

    private function paginateInvoicesWithStatusFilter(EloquentBuilder $query, int $perPage, string $status): LengthAwarePaginator
    {
        $invoices = $query->get();
        $paymentsByInvoice = $this->paymentsByInvoiceIds($invoices->pluck('id')->all());

        $items = $invoices
            ->map(function (Invoice $invoice) use ($paymentsByInvoice) {
                $paid = round((float) ($paymentsByInvoice[$invoice->id] ?? 0), 2);

                return $this->mapInvoiceListItem($invoice, $paid);
            })
            ->filter(fn (InvoiceListItemDTO $dto) => $dto->paymentStatus === $status)
            ->values();

        $page = max(1, (int) request()->input('page', 1));
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $slice,
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function findInvoiceForCustomer(string $customerId, string $invoiceId): ?InvoiceDetailDTO
    {
        $invoice = Invoice::query()
            ->with(['currencyinfo', 'details'])
            ->where('id', $invoiceId)
            ->where('customer_id', $customerId)
            ->whereNull('deleted_at')
            ->first();

        if (! $invoice) {
            return null;
        }

        $paid = round((float) ($this->paymentsByInvoiceIds([$invoice->id])[$invoice->id] ?? 0), 2);
        $listItem = $this->mapInvoiceListItem($invoice, $paid);

        $payments = InvoicePaymentDetail::query()
            ->where('invoice_id', $invoice->id)
            ->where('is_delete', false)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (InvoicePaymentDetail $payment) => new InvoicePaymentDTO(
                id: (string) $payment->id,
                amount: $this->parseAmount($payment->amount),
                paymentMethod: $payment->payment_method,
                refNo: $payment->ref_no,
                transactionNo: $payment->transaction_no,
                createdAt: $payment->created_at?->toIso8601String(),
            ))
            ->all();

        $lineItems = $invoice->details
            ->map(fn (InvoiceDetails $line) => new InvoiceLineItemDTO(
                id: (string) $line->id,
                description: $line->analysis_type_name ?? $line->analysis_title ?? $line->zoho_item_name,
                quantity: $line->quantity !== null ? (float) $line->quantity : null,
                unitPrice: $line->final_unit_price !== null ? (float) $line->final_unit_price : null,
                total: $line->total !== null ? (float) $line->total : null,
            ))
            ->all();

        return new InvoiceDetailDTO(
            id: $listItem->id,
            invoiceNumber: $listItem->invoiceNumber,
            referenceNumber: $listItem->referenceNumber,
            currencyCode: $listItem->currencyCode,
            invoiceDate: $listItem->invoiceDate,
            dueDate: $listItem->dueDate,
            totalAmount: $listItem->totalAmount,
            taxAmount: $listItem->taxAmount,
            paidAmount: $listItem->paidAmount,
            outstandingAmount: $listItem->outstandingAmount,
            paymentStatus: $listItem->paymentStatus,
            lineItems: $lineItems,
            payments: $payments,
        );
    }

    /**
     * @param  list<string>  $invoiceIds
     * @return Collection<string, float|string>
     */
    private function paymentsByInvoiceIds(array $invoiceIds): Collection
    {
        if ($invoiceIds === []) {
            return collect();
        }

        return DB::table('invoice_payment_details')
            ->select('invoice_id', DB::raw('SUM(CAST(NULLIF(amount, \'\') AS DECIMAL(18,2))) as paid'))
            ->whereIn('invoice_id', $invoiceIds)
            ->where('is_delete', false)
            ->groupBy('invoice_id')
            ->pluck('paid', 'invoice_id');
    }

    private function mapInvoiceListItem(Invoice $invoice, float $paid): InvoiceListItemDTO
    {
        $total = round((float) $invoice->total + (float) ($invoice->total_tax ?? 0), 2);
        $outstanding = round(max($total - $paid, 0), 2);

        return new InvoiceListItemDTO(
            id: (string) $invoice->id,
            invoiceNumber: (string) $invoice->invoice_number,
            referenceNumber: $invoice->reference_number,
            currencyCode: $invoice->currencyinfo?->code ?? $invoice->currencyinfo?->name,
            invoiceDate: $invoice->created_at?->toDateString(),
            dueDate: $invoice->due_date,
            totalAmount: round((float) $invoice->total, 2),
            taxAmount: round((float) ($invoice->total_tax ?? 0), 2),
            paidAmount: $paid,
            outstandingAmount: $outstanding,
            paymentStatus: $this->paymentStatus($total, $paid),
        );
    }

    private function paymentStatus(float $total, float $paid): string
    {
        if ($total <= 0 || $paid >= $total) {
            return 'paid';
        }

        if ($paid <= 0) {
            return 'unpaid';
        }

        return 'partial';
    }

    private function parseAmount(mixed $amount): ?float
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        return round((float) $amount, 2);
    }

    private function mapComplaint(Complaint $complaint): ComplaintDTO
    {
        return new ComplaintDTO(
            id: (string) $complaint->id,
            complaintNumber: $complaint->complaint_id,
            title: $complaint->description,
            complaintType: $complaint->type,
            status: $this->publicStatusLabel((string) ($complaint->complaint_workflow ?? 'open')),
            createdAt: $complaint->date?->toIso8601String(),
            resolutionStatus: $complaint->is_closed ? 'resolved' : 'open',
        );
    }

    private function mapFeedbackListItem(CustomerFeedback $feedback): FeedbackListItemDTO
    {
        return new FeedbackListItemDTO(
            id: (string) $feedback->id,
            code: $feedback->code,
            ratingOverall: $feedback->rating_overall !== null ? (float) $feedback->rating_overall : null,
            specificFeedback: $feedback->specific_feedback,
            serviceType: $feedback->service_type,
            serviceReferenceNo: $feedback->service_reference_no,
            submittedAt: $feedback->submitted_at?->toIso8601String(),
        );
    }

    private function publicStatusLabel(string $status): string
    {
        return str($status)->replace('_', ' ')->title()->toString();
    }
}
