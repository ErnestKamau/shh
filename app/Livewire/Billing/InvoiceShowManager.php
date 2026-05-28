<?php

namespace App\Livewire\Billing;

use App\EntityAttachment;
use App\EntityNote;
use App\Invoice;
use App\InvoicePaymentDetail;
use App\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\File;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class InvoiceShowManager extends Component
{
    use WithFileUploads;

    public string $invoiceId;

    public string $activeTab = 'payments';

    public string $message = '';

    public string $messageType = 'success';

    public string $noteType = 'General';

    public string $noteDescription = '';

    public string $attachmentTitle = '';

    public string $attachmentDescription = '';

    public $attachmentFile;

    public bool $showRaiseControlModal = false;

    public string $controlAmount = '';

    public function mount(string $invoiceId): void
    {
        $this->invoiceId = $invoiceId;
    }

    public function getInvoiceProperty(): ?Invoice
    {
        return Invoice::query()
            ->with([
                'crmCustomer',
                'currencyinfo',
                'pricelist.currency',
                'details.invoicableItem',
                'details.analysisType.analysis_elements' => function ($query): void {
                    $query->where('active', 1)->orderBy('level')->with('analyte');
                },
                'batches',
            ])
            ->select('customer_invoice.*')
            ->selectRaw('(SELECT COALESCE(SUM(CAST(invoice_details.total AS NUMERIC)), 0) FROM invoice_details WHERE invoice_details.invoice_id = customer_invoice.id) as line_items_total')
            ->selectRaw('(SELECT COALESCE(SUM(CAST(invoice_details.tax_amount AS NUMERIC)), 0) FROM invoice_details WHERE invoice_details.invoice_id = customer_invoice.id) as line_items_tax')
            ->find($this->invoiceId);
    }

    public function getSummaryProperty(): array
    {
        $invoice = $this->invoice;

        if (! $invoice) {
            return [
                'total' => 0,
                'tax' => 0,
                'subtotal' => 0,
                'batch_count' => 0,
                'line_count' => 0,
            ];
        }

        $total = (float) ($invoice->line_items_total ?? $invoice->invoicetotal);
        $tax = (float) ($invoice->line_items_tax ?? $invoice->total_tax);

        return [
            'total' => $total,
            'tax' => $tax,
            'subtotal' => $total - $tax,
            'batch_count' => $invoice->batches->count(),
            'line_count' => $invoice->details->count(),
        ];
    }

    public function getPaymentsProperty(): Collection
    {
        $invoice = $this->invoice;

        if (! $invoice) {
            return collect();
        }

        return $invoice->payments()->get();
    }

    public function getRemainingInvoiceAmountProperty(): float
    {
        $invoice = $this->invoice;

        if (! $invoice) {
            return 0;
        }

        $paid = $this->payments->sum(fn ($payment) => (float) ($payment->amount ?? 0));

        return max(0, round($this->summary['total'] - $paid, 2));
    }

    public function getCanSendToCustomerProperty(): bool
    {
        return filled($this->invoice?->upload_url);
    }

    public function getNotesProperty(): Collection
    {
        return EntityNote::query()
            ->where('model', 'Invoice')
            ->where('model_id', $this->invoiceId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (EntityNote $note) {
                $note->creator_name = User::find($note->created_by)?->name ?? 'Unknown';

                return $note;
            });
    }

    public function getAttachmentsProperty(): Collection
    {
        $items = EntityAttachment::query()
            ->where('model', 'Invoice')
            ->where('model_id', $this->invoiceId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (EntityAttachment $attachment) {
                $attachment->uploader_name = User::find($attachment->created_by)?->name ?? 'Unknown';

                return $attachment;
            });

        $invoice = $this->invoice;

        if ($invoice?->upload_url) {
            $items->prepend((object) [
                'id' => 'upload-url',
                'title' => 'Invoice PDF',
                'description' => 'Uploaded invoice document',
                'file' => $invoice->upload_url,
                'type' => 'Invoice PDF',
                'uploader_name' => 'System',
                'created_at' => $invoice->updated_at,
                'is_upload_url' => true,
            ]);
        }

        return $items;
    }

    public function setActiveTab(string $tab): void
    {
        if (in_array($tab, ['payments', 'attachments', 'notes'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function openRaiseControlModal(): void
    {
        $this->controlAmount = $this->remainingInvoiceAmount > 0
            ? (string) $this->remainingInvoiceAmount
            : '';
        $this->resetErrorBag();
        $this->showRaiseControlModal = true;
    }

    public function closeRaiseControlModal(): void
    {
        $this->showRaiseControlModal = false;
        $this->controlAmount = '';
        $this->resetErrorBag();
    }

    public function submitRaiseControl(): void
    {
        $maxAmount = $this->remainingInvoiceAmount;

        $this->validate([
            'controlAmount' => ['required', 'numeric', 'min:0.01', 'max:'.$maxAmount],
        ], [
            'controlAmount.max' => 'Amount cannot exceed the remaining invoice balance ('.number_format($maxAmount, 2).').',
        ]);

        $payment = new InvoicePaymentDetail;
        $payment->id = (string) Str::uuid();
        $payment->invoice_id = $this->invoiceId;
        $payment->received_by = Auth::id();
        $payment->amount = (string) $this->controlAmount;
        $payment->payment_method = 'GEPG';
        $payment->ref_no = 'PENDING-GEPG';
        $payment->transaction_no = null;
        $payment->balance = (string) max(0, round($maxAmount - (float) $this->controlAmount, 2));
        $payment->save();

        $this->closeRaiseControlModal();
        $this->showMessage(
            'Payment record created. Control number generation is awaiting GEPG integration.',
            'success'
        );
    }

    public function sendInvoiceToCustomer(): void
    {
        $invoice = $this->invoice;

        if (! $invoice) {
            return;
        }

        if (! $invoice->upload_url) {
            $this->showMessage('Upload the invoice PDF before sending it to the customer.', 'danger');

            return;
        }

        $customer = $invoice->crmCustomer;

        if (! $customer) {
            $this->showMessage('Customer not found for this invoice.', 'danger');

            return;
        }

        $contacts = getCrmCustomerContacts($customer->id);

        if ($contacts->isEmpty()) {
            $this->showMessage('No customer contacts configured to receive invoices were found.', 'warning');

            return;
        }

        $batch = $invoice->batches->first();
        $batchCode = $batch?->batch_code ?? $invoice->invoice_number;
        $active = getActiveCompany();
        $message = 'Invoice for batch '.$batchCode.' has been processed. Please find the invoice attached below.';
        $body = 'Hi '.$customer->name.',<br><br>'
            .$message.'<br><br>
            Regards, <br>
            '.($active->name ?? 'Laboratory').' ';
        $subject = '['.($active->name ?? 'LIMS').'] Invoice '.$invoice->invoice_number;
        $file = storage_path().$invoice->upload_url;

        foreach ($contacts as $contact) {
            if (! empty($contact->email)) {
                notify_user($body, $contact->email, $subject, $file);
            }
        }

        $this->showMessage('Invoice sent to customer contacts successfully.');
    }

    public function addNote(): void
    {
        $this->validate([
            'noteType' => 'required|string|max:255',
            'noteDescription' => 'required|string|max:5000',
        ]);

        $note = new EntityNote;
        $note->id = (string) Str::uuid();
        $note->type = $this->noteType;
        $note->description = $this->noteDescription;
        $note->model = 'Invoice';
        $note->model_id = $this->invoiceId;
        $note->created_by = Auth::id();
        $note->save();

        $this->noteType = 'General';
        $this->noteDescription = '';
        $this->showMessage('Note added successfully.');
    }

    public function deleteNote(string $noteId): void
    {
        $note = EntityNote::query()
            ->where('model', 'Invoice')
            ->where('model_id', $this->invoiceId)
            ->where('id', $noteId)
            ->first();

        if ($note) {
            $note->delete();
            $this->showMessage('Note removed.');
        }
    }

    public function addAttachment(): void
    {
        $this->validate([
            'attachmentTitle' => 'required|string|max:255',
            'attachmentDescription' => 'nullable|string|max:2000',
            'attachmentFile' => 'required|file|max:10240',
        ]);

        $path = $this->attachmentFile->getRealPath();
        $stored = Storage::putFile('invoice-attachments', new File($path));
        $segments = explode('/', $stored);
        $filePath = '/storage/invoice-attachments/'.urlencode((string) end($segments));

        $attachment = new EntityAttachment;
        $attachment->id = (string) Str::uuid();
        $attachment->title = $this->attachmentTitle;
        $attachment->type = 'Invoice Attachment';
        $attachment->description = $this->attachmentDescription ?: $this->attachmentTitle;
        $attachment->model = 'Invoice';
        $attachment->model_id = $this->invoiceId;
        $attachment->file = $filePath;
        $attachment->mime = $this->attachmentFile->getMimeType();
        $attachment->size = (string) $this->attachmentFile->getSize();
        $attachment->created_by = Auth::id();
        $attachment->save();

        $this->attachmentTitle = '';
        $this->attachmentDescription = '';
        $this->attachmentFile = null;
        $this->showMessage('Attachment uploaded successfully.');
    }

    public function deleteAttachment(string $attachmentId): void
    {
        $attachment = EntityAttachment::query()
            ->where('model', 'Invoice')
            ->where('model_id', $this->invoiceId)
            ->where('id', $attachmentId)
            ->first();

        if (! $attachment) {
            return;
        }

        if (filled($attachment->file) && str_starts_with($attachment->file, '/storage/')) {
            $relative = Str::after($attachment->file, '/storage/');
            Storage::disk('public')->delete(urldecode($relative));
        }

        $attachment->delete();
        $this->showMessage('Attachment removed.');
    }

    public function clearAttachmentFile(): void
    {
        $this->attachmentFile = null;
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    public function showMessage(string $message, string $type = 'success'): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }

    public function render(): View
    {
        return view('livewire.billing.invoice-show-manager', [
            'invoice' => $this->invoice,
            'summary' => $this->summary,
            'payments' => $this->payments,
            'notes' => $this->notes,
            'attachments' => $this->attachments,
            'remainingInvoiceAmount' => $this->remainingInvoiceAmount,
            'canSendToCustomer' => $this->canSendToCustomer,
        ]);
    }
}
