<?php

namespace App\Services\Billing;

use App\QuotationDetailAnalysisSplit;
use App\QuotationDetails;
use App\QuotationHeader;
use App\Services\Commercial\AmSpecQuotationNumberGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class QuotationRevisionService
{
    /**
     * @param  array<string, mixed>  $overrides
     */
    public function createRevision(QuotationHeader $priorHeader, array $overrides = []): QuotationHeader
    {
        return DB::transaction(function () use ($priorHeader, $overrides): QuotationHeader {
            $priorHeader->loadMissing('details');

            $preparedById = Auth::id();
            if ($preparedById === null) {
                throw new RuntimeException('You must be signed in to create a quotation revision.');
            }

            $header = new QuotationHeader();
            $header->crm_customer_id = $priorHeader->crm_customer_id;
            $header->crm_customer_contact_id = $priorHeader->crm_customer_contact_id;
            $header->quote_date = now()->toDateString();
            $header->expiring_date = now()->addDays(30)->toDateString();
            $header->prepared_by_id = (string) $preparedById;
            $header->quotation_type = $priorHeader->quotation_type ?? 'Analysis';
            $header->status = 'Quote In Preparation';
            $header->pricelist_id = $priorHeader->pricelist_id;
            $header->currency_id = $priorHeader->currency_id;
            $header->revision_of_quotation_header_id = $priorHeader->id;
            $header->revision_number = ($priorHeader->revision_number ?? 1) + 1;
            $header->is_draft = 0;
            $header->is_complete = 0;
            $header->is_approved = 0;
            $header->is_print = 0;
            $header->sub_total = $priorHeader->sub_total;
            $header->tax = $priorHeader->tax;
            $header->total_amount = $priorHeader->total_amount;
            $header->service_delivery = $priorHeader->service_delivery;
            $header->payments = $priorHeader->payments;
            $header->quote_specification = $priorHeader->quote_specification;
            $header->additional_info = $priorHeader->additional_info;
            $header->payment_info = $priorHeader->payment_info;
            $header->subject = $priorHeader->subject;
            $header->sample_point_id = $priorHeader->sample_point_id;
            $header->sampling_location = $priorHeader->sampling_location;
            $header->laboratory_ref = $priorHeader->laboratory_ref;
            $header->terms_override = $priorHeader->terms_override;
            $header->structured_terms = $priorHeader->structured_terms;
            $header->show_loq_column = $priorHeader->show_loq_column ?? true;
            $header->show_mu_column = $priorHeader->show_mu_column ?? true;
            $header->show_unit_price_column = $priorHeader->show_unit_price_column ?? true;
            $header->from_enquiry = (bool) ($priorHeader->from_enquiry ?? false);
            $header->sample_submission_request_id = $priorHeader->sample_submission_request_id;

            foreach ($overrides as $key => $value) {
                $header->{$key} = $value;
            }

            $header->save();

            AmSpecQuotationNumberGenerator::assignIfMissing($header);

            foreach ($priorHeader->details as $detail) {
                $cloned = QuotationDetails::query()->create([
                    'quotation_header_id' => $header->id,
                    'sample_type' => $detail->sample_type,
                    'quantity' => $detail->quantity,
                    'unit_price' => $detail->unit_price,
                    'tax' => $detail->tax,
                    'part_no' => $detail->part_no,
                    'accredited_analytes' => $detail->accredited_analytes,
                    'subcontracted_analytes' => $detail->subcontracted_analytes,
                    'default_analytes' => $detail->default_analytes,
                    'sub_acc_analytes' => $detail->sub_acc_analytes,
                    'description' => $detail->description,
                    'item_name' => $detail->item_name,
                    'photo_url' => $detail->photo_url,
                ]);

                $splits = QuotationDetailAnalysisSplit::query()
                    ->where('quotation_detail_id', $detail->id)
                    ->get();

                foreach ($splits as $split) {
                    QuotationDetailAnalysisSplit::query()->create([
                        'quotation_detail_id' => $cloned->id,
                        'analysis_type_id' => $split->analysis_type_id,
                    ]);
                }
            }

            return $header->fresh(['details', 'revisionOf']);
        });
    }

    /**
     * @return Collection<int, QuotationHeader>
     */
    public function collectRevisionFamily(QuotationHeader $header): Collection
    {
        $root = $this->findRoot($header);
        $family = collect([$root]);
        $toScan = collect([$root->id]);

        while ($toScan->isNotEmpty()) {
            $children = QuotationHeader::query()
                ->whereIn('revision_of_quotation_header_id', $toScan->all())
                ->orderBy('revision_number')
                ->get();

            $family = $family->merge($children);
            $toScan = $children->pluck('id');
        }

        return $family
            ->unique('id')
            ->sortBy('revision_number')
            ->values();
    }

    public function findRoot(QuotationHeader $header): QuotationHeader
    {
        $current = $header;

        while ($current->revision_of_quotation_header_id) {
            $parent = QuotationHeader::find($current->revision_of_quotation_header_id);
            if ($parent === null) {
                break;
            }

            $current = $parent;
        }

        return $current;
    }
}
