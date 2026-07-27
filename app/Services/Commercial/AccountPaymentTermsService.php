<?php

namespace App\Services\Commercial;

use App\Invoice;
use App\Models\CRM\CRMCustomer;
use App\Models\System\SystemConfiguration;
use App\Models\TestRequestReportDelivery;
use App\SampleHeader;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

final class AccountPaymentTermsService
{
    public const ANCHOR_INVOICE_CREATED = 'invoice_created';

    public const ANCHOR_REPORT_DELIVERY = 'test_report_delivery';

    public const ANCHOR_IMMEDIATE = 'immediate';

    /**
     * Selectable payment methods for "Other – editable" account settings.
     *
     * @return array<string, string>
     */
    public static function paymentMethodOptions(): array
    {
        return [
            'bank_slip' => 'Bank slip',
            'bank_deposit' => 'Bank deposit',
            'bank_slip_or_deposit' => 'Bank slip / bank deposit',
            'cash' => 'Cash',
            'cheque' => 'Cheque',
            'wire_transfer' => 'Wire transfer',
            'other' => 'Other',
        ];
    }

    public function paymentMethodLabel(?string $method): string
    {
        if (! filled($method)) {
            return '';
        }

        return self::paymentMethodOptions()[$method] ?? (string) $method;
    }

    /**
     * @return array{
     *     config_id: string|null,
     *     key: string|null,
     *     label: string|null,
     *     billing_type: string,
     *     days: int|null,
     *     anchor: string,
     *     payment_method: string|null,
     *     allows_custom_days: bool,
     *     allows_custom_payment_method: bool
     * }
     */
    public function resolveFromCustomer(?CRMCustomer $customer): array
    {
        if ($customer === null || empty($customer->account_status)) {
            return $this->defaultTerms();
        }

        return $this->resolveFromConfigId((string) $customer->account_status, $customer);
    }

    /**
     * @return array{
     *     config_id: string|null,
     *     key: string|null,
     *     label: string|null,
     *     billing_type: string,
     *     days: int|null,
     *     anchor: string,
     *     payment_method: string|null,
     *     allows_custom_days: bool,
     *     allows_custom_payment_method: bool
     * }
     */
    public function resolveFromConfigId(?string $configId, ?CRMCustomer $customer = null): array
    {
        if (! filled($configId)) {
            return $this->defaultTerms();
        }

        $config = SystemConfiguration::query()->find($configId);
        if ($config === null) {
            return $this->defaultTerms();
        }

        $meta = is_array($config->meta) ? $config->meta : [];
        $label = $meta['label'] ?? ($config->value ?: $config->key);
        $billingType = $meta['billing_type'] ?? $this->inferBillingType((string) $config->key, (string) $config->value);
        $allowsCustom = (bool) ($meta['allows_custom_days'] ?? false);
        $allowsCustomPaymentMethod = $billingType === 'other'
            || (bool) ($meta['allows_custom_payment_method'] ?? false);
        $days = array_key_exists('days', $meta) ? $meta['days'] : null;
        $paymentMethod = $meta['payment_method'] ?? null;

        if ($allowsCustom && $customer !== null && is_numeric($customer->credit_days)) {
            $days = (int) $customer->credit_days;
        } elseif ($days === null && is_numeric($customer?->credit_days)) {
            $days = (int) $customer->credit_days;
        }

        if ($allowsCustomPaymentMethod && $customer !== null && filled($customer->payment_method)) {
            $paymentMethod = (string) $customer->payment_method;
        }

        return [
            'config_id' => (string) $config->id,
            'key' => (string) $config->key,
            'label' => (string) $label,
            'billing_type' => (string) $billingType,
            'days' => $days !== null ? (int) $days : null,
            'anchor' => (string) ($meta['anchor'] ?? self::ANCHOR_INVOICE_CREATED),
            'payment_method' => $paymentMethod,
            'allows_custom_days' => $allowsCustom,
            'allows_custom_payment_method' => $allowsCustomPaymentMethod,
        ];
    }

    /**
     * Human-readable label for account setting dropdowns / badges.
     *
     * @param  SystemConfiguration|array|object|null  $account
     */
    public function displayLabel(mixed $account): string
    {
        if ($account === null) {
            return '';
        }

        $meta = data_get($account, 'meta');
        if (is_string($meta)) {
            $meta = json_decode($meta, true) ?: [];
        }
        if (! is_array($meta)) {
            $meta = [];
        }

        $label = $meta['label'] ?? null;
        if (filled($label)) {
            return (string) $label;
        }

        $value = data_get($account, 'value');
        if (filled($value)) {
            return (string) $value;
        }

        return (string) data_get($account, 'key', '');
    }

    /**
     * Apply fixed-term days onto the customer when a non-custom account setting is chosen.
     */
    public function syncCustomerFromAccountSetting(
        CRMCustomer $customer,
        ?string $accountStatusId,
        ?int $customCreditDays = null,
        ?string $paymentTermsNote = null,
        ?string $paymentMethod = null
    ): void {
        $terms = $this->resolveFromConfigId($accountStatusId);

        if ($terms['allows_custom_days']) {
            if ($customCreditDays !== null) {
                $customer->credit_days = max(0, $customCreditDays);
            }

            if ($terms['billing_type'] === 'other') {
                $customer->payment_method = filled($paymentMethod) ? (string) $paymentMethod : null;
                $customer->payment_terms_note = filled($paymentTermsNote) ? trim((string) $paymentTermsNote) : null;
            } else {
                $customer->payment_method = null;
                $customer->payment_terms_note = null;
            }

            return;
        }

        $customer->credit_days = max(0, (int) ($terms['days'] ?? 0));
        $customer->payment_method = $terms['payment_method'] ?? null;
        $customer->payment_terms_note = null;
    }

    public function calculateDueDate(
        ?CRMCustomer $customer,
        Carbon|string|null $invoiceCreatedAt = null,
        Carbon|string|null $reportDeliveredAt = null
    ): string {
        $terms = $this->resolveFromCustomer($customer);
        $createdAt = $invoiceCreatedAt
            ? Carbon::parse($invoiceCreatedAt)
            : now();

        if (
            $terms['anchor'] === self::ANCHOR_IMMEDIATE
            || ($terms['billing_type'] === EnquiryAccountSettingsService::TYPE_ADVANCE && (int) ($terms['days'] ?? 0) === 0)
        ) {
            return $createdAt->toDateString();
        }

        $days = (int) ($terms['days'] ?? 0);
        if ($days <= 0) {
            // Legacy fallback: blank credit days previously defaulted to +30.
            $days = 30;
        }

        if ($terms['anchor'] === self::ANCHOR_REPORT_DELIVERY && $reportDeliveredAt) {
            return Carbon::parse($reportDeliveredAt)->addDays($days)->toDateString();
        }

        // Provisional due date until report delivery (or for invoice_created anchor).
        return $createdAt->copy()->addDays($days)->toDateString();
    }

    public function applyDueDateToInvoice(Invoice $invoice, ?CRMCustomer $customer = null, ?string $batchId = null): void
    {
        $customer ??= $invoice->crmCustomer;
        $reportDeliveredAt = null;

        $terms = $this->resolveFromCustomer($customer);
        if ($terms['anchor'] === self::ANCHOR_REPORT_DELIVERY) {
            $reportDeliveredAt = $this->firstSuccessfulDeliveryAt($batchId)
                ?? $this->firstSuccessfulDeliveryAtForInvoice($invoice);
        }

        $invoice->due_date = $this->calculateDueDate(
            $customer,
            $invoice->created_at ?? now(),
            $reportDeliveredAt
        );
    }

    public function recalculateInvoiceDueDatesForBatch(string $batchId): void
    {
        $header = SampleHeader::query()->find($batchId);
        if (! $header || empty($header->invoice_id)) {
            return;
        }

        $invoice = Invoice::query()
            ->whereNull('deleted_at')
            ->find($header->invoice_id);

        if (! $invoice) {
            return;
        }

        $customer = CRMCustomer::query()->find($invoice->customer_id);
        $terms = $this->resolveFromCustomer($customer);

        if ($terms['anchor'] !== self::ANCHOR_REPORT_DELIVERY) {
            return;
        }

        $deliveredAt = $this->firstSuccessfulDeliveryAt($batchId);
        if (! $deliveredAt) {
            return;
        }

        $invoice->due_date = $this->calculateDueDate($customer, $invoice->created_at, $deliveredAt);
        $invoice->save();
    }

    public function clearAccountSettingsCache(): void
    {
        Cache::forget('api_account_settings_list');
    }

    /**
     * @return array{
     *     config_id: null,
     *     key: null,
     *     label: null,
     *     billing_type: string,
     *     days: int,
     *     anchor: string,
     *     payment_method: null,
     *     allows_custom_days: bool
     * }
     */
    private function defaultTerms(): array
    {
        return [
            'config_id' => null,
            'key' => null,
            'label' => null,
            'billing_type' => EnquiryAccountSettingsService::TYPE_WALK_IN,
            'days' => 30,
            'anchor' => self::ANCHOR_INVOICE_CREATED,
            'payment_method' => null,
            'allows_custom_days' => true,
            'allows_custom_payment_method' => false,
        ];
    }

    private function inferBillingType(string $key, string $value): string
    {
        $haystack = strtolower($key.' '.$value);

        if (str_contains($haystack, 'pay upfront') || str_contains($haystack, 'advance')) {
            return EnquiryAccountSettingsService::TYPE_ADVANCE;
        }

        if (str_contains($haystack, 'other')) {
            return 'other';
        }

        if (str_contains($haystack, 'account holder') && ! str_contains($haystack, 'overdue')) {
            return EnquiryAccountSettingsService::TYPE_CREDIT;
        }

        if (str_contains($haystack, 'days after') || str_contains($haystack, 'test report')) {
            return EnquiryAccountSettingsService::TYPE_CREDIT;
        }

        return EnquiryAccountSettingsService::TYPE_WALK_IN;
    }

    private function firstSuccessfulDeliveryAt(?string $batchId): ?Carbon
    {
        if (! filled($batchId)) {
            return null;
        }

        $delivery = TestRequestReportDelivery::query()
            ->where('batch_id', $batchId)
            ->where('status', 'sent')
            ->orderBy('created_at')
            ->first();

        return $delivery?->created_at ? Carbon::parse($delivery->created_at) : null;
    }

    private function firstSuccessfulDeliveryAtForInvoice(Invoice $invoice): ?Carbon
    {
        $batchIds = SampleHeader::query()
            ->where('invoice_id', $invoice->id)
            ->pluck('id');

        if ($batchIds->isEmpty()) {
            return null;
        }

        $delivery = TestRequestReportDelivery::query()
            ->whereIn('batch_id', $batchIds)
            ->where('status', 'sent')
            ->orderBy('created_at')
            ->first();

        return $delivery?->created_at ? Carbon::parse($delivery->created_at) : null;
    }
}
