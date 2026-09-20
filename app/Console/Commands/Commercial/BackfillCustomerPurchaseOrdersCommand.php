<?php

namespace App\Console\Commands\Commercial;

use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstanceAttachment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackfillCustomerPurchaseOrdersCommand extends Command
{
    protected $signature = 'commercial:backfill-customer-purchase-orders
                            {--dry-run : Show what would be created without writing}';

    protected $description = 'Create customer_purchase_orders rows for enquiries that already have a client PO number or skipped flag';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $enquiries = SampleSubmissionRequest::query()
            ->with(['submissionFormInstance'])
            ->where(function ($q): void {
                $q->whereNotNull('client_po_number')
                    ->where('client_po_number', '!=', '')
                    ->orWhere('po_skipped', true);
            })
            ->whereDoesntHave('customerPurchaseOrder')
            ->orderBy('created_at')
            ->get();

        if ($enquiries->isEmpty()) {
            $this->info('No enquiries need backfill.');

            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[dry-run] ' : '').'Backfilling '.$enquiries->count().' enquiry/enquiries…');

        $created = 0;

        foreach ($enquiries as $enquiry) {
            $poNumber = trim((string) ($enquiry->client_po_number ?? ''));
            $poNumber = $poNumber !== '' ? $poNumber : null;
            $poSkipped = (bool) ($enquiry->po_skipped ?? false) || $poNumber === null;

            if ($poNumber !== null) {
                $poSkipped = false;
            }

            $attachment = $this->findPurchaseOrderAttachment($enquiry);

            $attributes = [
                'po_number' => $poNumber,
                'po_skipped' => $poSkipped,
                'enquiry_id' => (string) $enquiry->id,
                'quotation_header_id' => $enquiry->accepted_quotation_header_id
                    ?? $enquiry->current_quotation_header_id,
                'customer_id' => $enquiry->crm_customer_id,
                'uploaded_by' => $attachment?->uploaded_by,
                'recorded_at' => $enquiry->quotation_accepted_at ?? $enquiry->updated_at ?? now(),
                'file_path' => null,
                'file_name' => null,
                'mime' => null,
                'size' => null,
            ];

            if ($attachment !== null && filled($attachment->file_path)) {
                $copied = $this->copyAttachmentFile($enquiry, $attachment, $dryRun);
                if ($copied !== null) {
                    $attributes = array_merge($attributes, $copied);
                } else {
                    $attributes['file_path'] = $attachment->file_path;
                    $attributes['file_name'] = $attachment->original_name;
                }
            }

            if ($dryRun) {
                $this->line('Would create PO for enquiry '.$enquiry->id.' ('.($poNumber ?? 'skipped').')');
                $created++;

                continue;
            }

            DB::transaction(function () use ($attributes): void {
                CustomerPurchaseOrder::query()->create($attributes);
            });

            $created++;
        }

        $this->info(($dryRun ? '[dry-run] Would create ' : 'Created ').$created.' customer purchase order(s).');

        return self::SUCCESS;
    }

    private function findPurchaseOrderAttachment(SampleSubmissionRequest $enquiry): ?SubmissionFormInstanceAttachment
    {
        $instanceId = trim((string) ($enquiry->submission_form_instance_id ?? ''));
        if ($instanceId === '') {
            return null;
        }

        try {
            return SubmissionFormInstanceAttachment::query()
                ->where('submission_form_instance_id', $instanceId)
                ->where(function ($q): void {
                    $q->whereRaw('LOWER(COALESCE(attachment_type, \'\')) LIKE ?', ['%purchase order%'])
                        ->orWhereRaw('LOWER(COALESCE(attachment_heading, \'\')) LIKE ?', ['%purchase order%']);
                })
                ->orderByDesc('created_at')
                ->first();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{file_path: string, file_name: ?string, mime: ?string, size: ?int}|null
     */
    private function copyAttachmentFile(
        SampleSubmissionRequest $enquiry,
        SubmissionFormInstanceAttachment $attachment,
        bool $dryRun,
    ): ?array {
        $source = (string) $attachment->file_path;
        if ($source === '' || ! Storage::disk('public')->exists($source)) {
            return null;
        }

        $basename = basename($source);
        $target = 'customer-purchase-orders/'.$enquiry->id.'/'.$basename;

        if (! $dryRun && $target !== $source) {
            Storage::disk('public')->copy($source, $target);
        }

        return [
            'file_path' => $dryRun ? $source : $target,
            'file_name' => $attachment->original_name ?: $basename,
            'mime' => Storage::disk('public')->mimeType($source) ?: null,
            'size' => Storage::disk('public')->size($source) ?: null,
        ];
    }
}
