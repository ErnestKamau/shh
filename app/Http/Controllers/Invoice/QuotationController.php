<?php

namespace App\Http\Controllers\Invoice;

use App\CapturedResult;
use App\Lab;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\Models\Currency;
use App\Models\System\SystemConfiguration;
use App\QuotationHeader;
use App\QuotationDetails;
use App\Result;
use App\SampleAnalysisStage;
use App\SampleAnalysisTypeRelation;
use App\SampleDetails;
use App\SampleHeader;
use App\TaxRegime;
use App\AnalysisType;
use App\AnalysisElements;
use App\Analyte;
use App\InvoicableItem;
use App\ZohoCustomers;
use Illuminate\Http\File;
use App\Http\Controllers\Controller;
use App\Models\CRM\SamplePoint;
use App\QuotationDetailAnalysisSplit;
use App\QuotationHeaderView;
use App\SampleType;
use App\Services\Billing\QuotationPricingResolver;
use App\Services\Billing\QuotationReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function __construct(
        private readonly QuotationReportService $quotationReportService,
        private readonly QuotationPricingResolver $quotationPricingResolver
    ) {
        $this->middleware('auth');
    }

    public function index($stage = false)
    {
        if ($stage != false) {
            $quotations = QuotationHeaderView::where('is_draft', 0)->where('status', $stage)->orderBy('id', 'desc')->get();
            $drafts = QuotationHeaderView::where('is_draft', 1)->where('status', $stage)->orderBy('id', 'desc')->get();
        } else {
            $quotations = QuotationHeaderView::where('is_draft', 0)->orderBy('id', 'desc')->get();
            $drafts = QuotationHeaderView::where('is_draft', 1)->orderBy('id', 'desc')->get();
            $stage = 'All Quotations';
        }

        // return response()->json($stage,200);
        $customers = CRMCustomer::where('active', 1)->orderBy('name')->get();
        // foreach ($quotations as $quotation) {
        //     $customer = getCrmCustomerByID($quotation->crm_customer_id);
        //     $contact = getCrmCustomerContactById($quotation->crm_customer_contact_id);
        //     $user = getUserById($quotation->prepared_by_id);
        //     $pricelist = getPricelistByID($quotation->pricelist_id);
        //     $quotation['customer'] = $customer->name;
        //     $quotation['contact'] = $contact->first_name . ' ' . $contact->middle_name . ' ' . $contact->last_name;
        //     $quotation['pricelist'] = $pricelist->code ?? '-';
        //     $quotation['prepared_by_name'] = $user->name;
        // }
        $sample_types = SampleType::where('active', 1)->get();

        return view('layouts.lab.invoice.quotation-index', compact('customers', 'quotations', 'drafts', 'stage', 'sample_types'));
    }
    public function filterQuotations(Request $request)
    {


        $drafts = QuotationHeaderView::where('is_draft', 1)->orderBy('id', 'desc')->get();
        if ($request->quote_type == 'Analysis') {
            $quotations_d = QuotationDetails::query();
            $quotations_d = $request->sample_type_id != '' ? $quotations_d->where('sample_type', $request->sample_type_id) : $quotations_d;
            $analysis_detail_ids = $request->analysis_type_id != '' ? QuotationDetailAnalysisSplit::where('id', $request->analysis_type_id)->pluck('quotation_detail_id')->toArray() : [];
            $quotations_d = sizeof($analysis_detail_ids) > 0 ? $quotations_d->whereIn('id', $analysis_detail_ids) : $quotations_d;
            $quotations_d_ids = $quotations_d->pluck('quotation_header_id')->toArray();
            $quotations = QuotationHeaderView::where('is_draft', 0)->whereIn('id', $quotations_d_ids);

            $quotations = $request->end_date != '' ? $quotations->where('created_at', '>=', $request->end_date) : $quotations;
            $quotations = $quotations->where('is_draft', 0)->orderBy('id', 'desc')->get();
        }
        if ($request->quote_type == 'General') {
            $quotations_d = QuotationDetails::where('description', 'LIKE', '%' . $request->item_description . '%')->pluck('quotation_header_id')->toArray();
            $quotations = QuotationHeaderView::where('is_draft', 0)->whereIn('id', $quotations_d);

            $quotations = $request->end_date != '' ? $quotations->where('created_at', '>=', $request->end_date) : $quotations;
            $quotations = $quotations->where('is_draft', 0)->orderBy('id', 'desc')->get();

        }


        $stage = 'All Quotations';
        $customers = CRMCustomer::where('active', 1)->orderBy('name')->get();
        $sample_types = SampleType::where('active', 1)->get();

        return view('layouts.lab.invoice.quotation-index', compact('customers', 'quotations', 'drafts', 'stage', 'sample_types'));
    }
    public function populateQuotationDetailSplit()
    {
        $headers = QuotationHeader::where('quotation_type', 'Analysis')->pluck('id')->toArray();
        $details = QuotationDetails::whereIn('quotation_header_id', $headers)->get();
        foreach ($details as $detail) {
            $idsTypes = explode(',', $detail->part_no);
            $insertArr = [];
            foreach ($idsTypes as $id) {
                array_push($insertArr, ['quotation_detail_id' => $detail->id, 'analysis_type_id' => $id]);
            }
            QuotationDetailAnalysisSplit::insert($insertArr);
        }
        return response()->json('done');
    }
    public function add_quotation_header(Request $request)
    {
        $request->validate([
            'currency_id' => ['nullable', 'uuid', 'exists:currencies,id'],
        ]);

        if (isset($request->quote_id)) {
            $header = QuotationHeader::find($request->quote_id);
        } else {

            $header = new QuotationHeader();
        }

        $customer = CRMCustomer::where('id', $request->client)->first();
        $header->crm_customer_id = $customer->id;
        if (!isset($request->client_contact)) {
            return redirect()->back()->with('error', 'Kindly add contacts to ' . $customer->name . ' customer.');
        }
        $header->crm_customer_contact_id = $request->client_contact;
        $header->quote_date = $request->quotation_date;
        $header->expiring_date = $request->expire_date;
        $header->prepared_by_id = auth()->user()->id;

        // Handle Dynamics customer linking
        if ($request->filled('zoho_customer_id')) {
            // Update CRM customer with zoho_customer_id
            $customer->zoho_customer_id = $request->zoho_customer_id;
            $customer->save();
        }

        // Handle currency
        if ($request->filled('currency_id')) {
            $header->currency_id = $request->currency_id;
        }

        if (!isset($request->quote_id)) {
            $header->status = 'Quote In Reception';
        }
        $header->quotation_type = $request->quotation_type;
        $header->subject = $request->input('subject');
        $header->sample_point_id = $request->input('sample_point_id');
        $header->sampling_location = $request->input('sampling_location');
        $header->laboratory_ref = $request->input('laboratory_ref');
        $header->terms_override = $request->input('terms_override');

        $header->status = 'Quote In Preparation';
        // return response()->json($header,200);
        $header->save();
        if (!isset($request->quote_id)) {

            $idstr = strval($header->id);
            if (strlen($idstr) < 4) {
                $count = 4 - strlen($idstr);
                $zeros = str_repeat('0', $count);
                $number = 'QUOTE-' . $zeros . $idstr;
            } else {
                $number = 'QUOTE-' . $idstr;
            }
            $header->quote_number = $number;
            $header->laboratory_ref = $this->quotationReportService->generateLaboratoryRef($header);
        }
        $header->is_draft = 1;
        $header->save();
        $this->quotationReportService->ensureHeaderMetadata($header);
        // return response()->json($header,200);

        return redirect()->route('add-qoute-details-view', ['id' => $header->id, 'stage' => $header->status]);
    }

    public function getCurrencyByCode(Request $request)
    {
        $currencyCode = $request->input('currency_code');

        if (!$currencyCode) {
            return response()->json(['error' => 'Currency code is required'], 400);
        }

        $currency = Currency::where('code', $currencyCode)->first();

        if (!$currency) {
            return response()->json(['error' => 'Currency not found'], 404);
        }

        return response()->json([
            'id' => $currency->id,
            'code' => $currency->code,
            'description' => $currency->description
        ]);
    }
    public function view_quote_header_detail($id, $stage = false)
    {
        // return response()->json('test');
        $customers = CRMCustomer::all();
        $header = QuotationHeader::find($id);
        $sample_types = SampleType::all();
        if ($stage == false) {
            $header->is_draft = 0;
        } else {

            $header->status = $stage;
            if ($stage == 'Quote Complete') {
                $header->is_complete = 1;
                $header->save();
            }
        }
        $header->save();
        // return response()->json($header,200);


        $user = getUserById($header->prepared_by_id);

        $header['prepared_by_name'] = $user->name;

        // Pricelist no longer used - using invoicable items instead
        $pricelist = null;
        $pricelist_items = [];
        $details = QuotationDetails::where('quotation_header_id', $id)->get();
        $count = 1;

        foreach ($details as $detail) {
            if ($header->quotation_type == 'Analysis') {

                $detail_sub_analytes = explode(',', $detail->subcontracted_analytes);
                $detail_accredited_analytes = explode(',', $detail->accredited_analytes);
                $detail_part_no = explode(',', $detail->part_no);

                $analytes_ac = [];
                $analyte_sa = [];
                $analyte_sub_acc = [];
                $default_a = [];
                $part_no = [];
                foreach ($detail_accredited_analytes as $ac) {
                    $analyte = AnalysisElements::find($ac);
                    if (isset($analyte->id)) {
                        $an = getAnalyteByID($analyte->analyte_id);
                        array_push($analytes_ac, $an->name);
                    }
                }
                foreach ($detail_sub_analytes as $sa) {
                    $analyte = AnalysisElements::find($sa);
                    if (isset($analyte->id)) {
                        $an = getAnalyteByID($analyte->analyte_id);
                        array_push($analyte_sa, $an->name);
                    }
                }
                foreach (explode(',', $detail->sub_acc_analytes) as $sc) {
                    $analyte = AnalysisElements::find($sc);
                    if (isset($analyte->id)) {
                        $an = getAnalyteByID($analyte->analyte_id);
                        array_push($analyte_sub_acc, $an->name);
                    }
                }
                foreach (explode(',', $detail->default_analytes) as $sc) {
                    $analyte = AnalysisElements::find($sc);
                    if (isset($analyte->id)) {
                        $an = getAnalyteByID($analyte->analyte_id);
                        array_push($default_a, $an->name);
                    }
                }
                foreach ($detail_part_no as $pn) {
                    $analysis = AnalysisType::find($pn);
                    if (isset($analysis->id)) {
                        array_push($part_no, $analysis->name);
                    }
                }

                $detail['sub_analytes'] = $analyte_sa;
                $detail['acc_analytes'] = $analytes_ac;
                $detail['sub_acc'] = $analyte_sub_acc;
                $detail['default'] = $default_a;
                $detail['part_no_final'] = implode(',', $part_no);
                $detail['sample_type_name'] = getSampleTypeByID($detail->sample_type)->name;
            }

            $detail['count'] = $count;
            ++$count;
            // return response()->json($detail,200);
        }
        if ($header->status == 'Quote Complete') {

            return redirect()->route('view_quotation_final', ['id' => $header->id, 'stage' => $header->stage]);
        }



        // return view('layouts.lab.invoice.quotation-show', compact('header', 'pricelist', 'pricelist_items', 'customers', 'details','sample_types '));
        $terms_config = getConfigTypeByName('Terms of Sale');
        $banks_config = getConfigTypeByName('Bank Details');
        $terms = getconfigByID($terms_config->id);
        $terms_array = array();
        foreach ($terms as $term) {
            $terms_array[$term->key] = $term->value;
        }
        $users = getAllUsers();
        // return response()->json($sample_types);

        $samplePoints = SamplePoint::query()
            ->where('active', 1)
            ->where('crm_customer_id', $header->crm_customer_id)
            ->orderBy('id')
            ->get();

        $currencies = Currency::query()->orderBy('code')->get();

        return view('layouts.lab.invoice.quotation-show', compact('header', 'pricelist', 'pricelist_items', 'customers', 'details', 'sample_types', 'terms_array', 'users', 'samplePoints', 'currencies'));
        // return response()->json($pricelist_items,200);
    }
    public function change_quotation_workflow($id, $stage)
    {

        $header = QuotationHeader::find($id);

        if (isset($header->id)) {
            if (in_array($header->status, ['Quote Complete', 'Quote In Approval']) && in_array($stage, ['Quote In Preparation', 'Quote In Approval'])) {
                $header->is_complete = 0;
                $header->is_draft = 0;
                $header->is_print = 0;

                $header->is_approved = 0;
            }
            if (in_array($header->status, ['Quote In Preparation', 'Quote In Approval']) && in_array($stage, ['Quote Complete'])) {
                return redirect()->back()->with('error', 'Quote ' . $header->quote_number . ' has not being approved');
            }

            $previous = $header->status;
            $header->status = $stage;
            if ($stage == 'Quote Complete') {
                $header->approved_by = auth()->user()->id;
                $header->is_complete = 1;
                $header->is_draft = 0;
            } else {
                $header->is_complete = 0;
                $header->is_draft = 0;
                $header->is_print = 0;
                $header->is_approved = 0;
            }




            $header->save();
        }
        // return response()->json($header,200);
        // return response()->json($previous);
        return redirect()->route('quotation-index', $previous)->with('success', 'Quotation Moved to ' . $stage . ' successfully.');
    }
    public function approve_workflow(Request $request)
    {
        $header = QuotationHeader::find($request->header_id);

        $res_approve = getUserById($request->user_id);
        $company = getActiveCompany();
        $prepared = getUserById($header->prepared_by_id);
        if ($header->status == 'Quote In Preparation') {
            $subject = '[' . $company->name . '] Quote ' . $header->quote_number . ' Approval Notification.';
            $message = 'Hi ' . $res_approve->name . ', Please approve for me Quote ' . $header->quote_number . '. From ' . $prepared->name;
            if (isset($request->notification)) {

                $notify = notify_user($message, $res_approve->email, $subject);
            }
            if (isset($request->send_message)) {
                $send = sendTextMessage($res_approve->phone, $message);
                if ($send == 'error') {
                    return redirect()->back()->with('error', 'Kindly add the sms configurations in the configurations section!');
                }
            }
            if (!isset($res_approve->id)) {
                return redirect()->back()->with('error', 'Kindly Provide the approve before sending for approval!');
            }
            $header->approved_by = $res_approve->id;
        }
        if ($header->status == 'Quote In Approval') {
            $subject = '[' . $company->name . '] Quote ' . $header->quote_number . ' Approval Notification.';
            $message = 'Hi ' . $prepared->name . ', Quote ' . $header->quote_number . ' has been approved successfully.';
            if (isset($request->notification)) {

                $notify = notify_user($message, $prepared->email, $subject);
            }
            if (isset($request->send_message)) {
                $send = sendTextMessage($prepared->phone, $message);
                if ($send == 'error') {
                    return redirect()->back()->with('error', 'Kindly add the sms configurations in the configurations section!');
                }
            }
            $header->is_complete = 1;
            $header->is_approved = 1;
        }
        $previous = $header->status;
        $header->status = $request->stage;
        $header->save();
        return redirect()->route('quotation-index', $previous)->with('success', 'Quotation Moved to ' . $request->stage . ' successfully.');
    }
    public function add_quotation_detail(Request $request, $id)
    {
        $request->validate([
            'currency_id' => ['required', 'uuid', 'exists:currencies,id'],
            'service_delivery' => ['required', 'string'],
            'payments' => ['required', 'string'],
            'quote_specification' => ['required', 'string'],
            'additional_info' => ['required', 'string'],
            'payment_info' => ['required', 'string'],
        ]);

        $header = QuotationHeader::find($id);

        $termsOverride = $request->input('terms_override', $header->terms_override);
        if ($termsOverride === 'null' || $termsOverride === '') {
            $termsOverride = null;
        }

        $header->service_delivery = $request->service_delivery;
        $header->payments = $request->payments;
        $header->quote_specification = $request->quote_specification;
        $header->additional_info = $request->additional_info;
        $header->payment_info = $request->payment_info;
        $header->currency_id = $request->currency_id;
        $header->subject = $request->input('subject', $header->subject);
        $header->sample_point_id = $request->input('sample_point_id', $header->sample_point_id);
        $header->sampling_location = $request->input('sampling_location', $header->sampling_location);
        $header->laboratory_ref = $request->input('laboratory_ref', $header->laboratory_ref);
        $header->terms_override = $termsOverride;
        $header->show_loq_column = $request->has('show_loq_column');
        $header->show_mu_column = $request->has('show_mu_column');
        $header->save();
        $this->quotationReportService->ensureHeaderMetadata($header);



        if ($header->quotation_type == 'General') {
            $count = 0;
            if ($request->part_no == '') {
                return redirect()->back();
            }
            foreach ($request->part_no as $id) {
                $detail = new QuotationDetails();
                $detail->item_name = $request->item[$count];
                $detail->unit_price = $request->unit_price[$count];
                // return response()->json($request->unit_price);
                $detail->tax = $request->tax[$count];
                $detail->part_no = $request->part_no[$count];
                $detail->quantity = $request->quantity[$count];
                $detail->description = $request->description[$count];
                if (isset($request->photo[$count]) && $request->photo[$count] != '') {
                    $path = $request->photo[$count]->path();
                    $file = Storage::putFile('quote_items', new File($path));
                    $file = explode('/', $file);
                    $fname = '/storage/quote_items/' . urlencode(end($file));
                    $detail->photo_url = (string) $fname;
                }
                // return response()->json($request->analysis_id,200);
                $detail->quotation_header_id = $header->id;

                $detail->save();
                ++$count;
            }
        } else {
            // return response()->json($request->all(),200);

            $count = 0;
            if ($request->sample_type == '') {
                return redirect()->back();
            }
            foreach ($request->sample_type as $id) {
                $detail = new QuotationDetails();
                $detail->sample_type = $request->sample_type[$count];
                $unitPrice = (float) $request->unit_price[$count];
                if ($unitPrice <= 0) {
                    $elementIds = array_filter(explode(',', implode(',', [
                        $request->default_analytes[$count] ?? '',
                        $request->accreditted_analytes[$count] ?? '',
                        $request->sub_analytes[$count] ?? '',
                        $request->sub_acc[$count] ?? '',
                    ])));
                    $unitPrice = $this->quotationPricingResolver->suggestLineUnitPrice(
                        $header,
                        $request->sample_type[$count],
                        $request->part_number_final[$count],
                        array_values(array_unique($elementIds))
                    );
                }
                $detail->unit_price = $unitPrice;
                $detail->tax = $request->tax[$count];
                $detail->part_no = $request->part_number_final[$count];
                $detail->quantity = $request->quantity[$count];
                $detail->accredited_analytes = $request->accreditted_analytes[$count];
                $detail->subcontracted_analytes = $request->sub_analytes[$count];
                $detail->sub_acc_analytes = $request->sub_acc[$count];
                $detail->default_analytes = $request->default_analytes[$count];
                $detail->quotation_header_id = $header->id;

                $analysisTypeIds = array_filter(explode(',', (string) $request->part_number_final[$count]));
                $this->quotationPricingResolver->persistInvoicableItemOnDetail(
                    $detail,
                    $analysisTypeIds[0] ?? null
                );
                $detail->save();
                $analysis_types_ids = explode(',', $request->part_number_final[$count]);
                $insertArr = [];
                QuotationDetailAnalysisSplit::where('quotation_detail_id', $detail->id)->delete();

                foreach ($analysis_types_ids as $a_id) {
                    $data = ["analysis_type_id" => $a_id, 'quotation_detail_id' => $detail->id];
                    array_push($insertArr, $data);
                }
                QuotationDetailAnalysisSplit::insert($insertArr);
                ++$count;
            }

        }
        $details = QuotationDetails::where('quotation_header_id', $header->id)->get();
        $sub_total = [];
        $taxes = [];
        foreach ($details as $detail) {
            $detail['extended_price'] = (int) $detail->quantity * (float) $detail->unit_price;

            array_push($sub_total, (float) $detail->extended_price);
            if ($detail->tax != 0) {
                $tax = (int) $detail->tax / 100 * (float) $detail->unit_price * (int) $detail->quantity;
                // return response()->json($tax);
                array_push($taxes, $tax);
            }
        }
        $sub_total_num = array_sum($sub_total);
        $tax_num = array_sum($taxes);
        // return response()->json($header[0]['tax']);
        $total_price_num = array_sum($sub_total) + array_sum($taxes);
        $header->sub_total = $sub_total_num;
        $header->tax = $tax_num;
        $header->total_amount = $total_price_num;
        $header->save();
        return redirect()->back()->with('success', 'Analysis added successfully!');
    }
    public function edit_quotation_header(Request $request, $id)
    {
        $header = QuotationHeader::find($id);
        $customer = CRMCustomer::where('name', $request->client)->first();

        $header->crm_customer_id = $customer->id;
        $header->crm_customer_contact_id = $request->client_contact;
        $header->quote_date = $request->quotation_date;
        $header->quotation_type = $request->quotation_type;
        $header->expiring_date = $request->expire_date;
        $header->subject = $request->input('subject', $header->subject);
        $header->sample_point_id = $request->input('sample_point_id', $header->sample_point_id);
        $header->sampling_location = $request->input('sampling_location', $header->sampling_location);
        $header->laboratory_ref = $request->input('laboratory_ref', $header->laboratory_ref);

        // Pricelist no longer used - using invoicable items instead
        $header->pricelist_id = null;
        $header->save();
        $this->quotationReportService->ensureHeaderMetadata($header);

        return redirect()->back()->with('success', 'Quotation updated successfully');
    }
    public function view_quotation_final($id, $stage = false)
    {
        $hd = QuotationHeader::findOrFail($id);
        if ($stage != false) {
            $hd->status = $stage;
        } else {
            $hd->is_draft = 0;
        }
        $hd->save();

        $this->recalculateQuotationTotals($hd);
        $hd->refresh();

        $currency = $hd->currency_id ? Currency::find($hd->currency_id) : null;
        $header = QuotationHeader::join('crm_customers', 'crm_customers.id', '=', 'quotation_headers.crm_customer_id')
            ->join('crm_customer_contacts', 'crm_customer_contacts.id', '=', 'quotation_headers.crm_customer_contact_id')
            ->join('users', DB::raw('users.id::text'), '=', DB::raw('quotation_headers.prepared_by_id::text'))
            ->join('module_pre_configs as tc', 'tc.id', '=', 'users.position')
            ->where('quotation_headers.id', $id)
            ->selectRaw('quotation_headers.service_delivery,quotation_headers.payments,quotation_headers.payment_info,quotation_headers.quotation_type,quotation_headers.additional_info,quotation_headers.quote_specification,quotation_headers.quote_number,quotation_headers.upload_url,quotation_headers.is_print,quotation_headers.id,quotation_headers.quote_date,quotation_headers.expiring_date,quotation_headers.email_to_customer,quotation_headers.is_draft,quotation_headers.is_complete,quotation_headers.approved_by,quotation_headers.email_to_customer,quotation_headers.total_amount,quotation_headers.sub_total,quotation_headers.tax,crm_customers.name,crm_customers.postal_address,crm_customers.physical_address,crm_customer_contacts.first_name,crm_customer_contacts.middle_name,crm_customer_contacts.last_name,crm_customer_contacts.email,crm_customer_contacts.mobile,
                                users.name as prepared_by,tc.name as position,users.email as prepared_by_email,users.phone,quotation_headers.is_batch_generate')
            ->get();

        $header[0]['total_price'] = $hd->total_amount;
        $header[0]['sub_total'] = $hd->sub_total;
        $header[0]['tax'] = $hd->tax;

        $labs = Lab::where('active', 1)->get();
        $reportViewData = $this->quotationReportService->buildViewData($hd);

        return view('layouts.lab.invoice.quotation-doc', array_merge(
            compact('header', 'currency', 'labs'),
            $reportViewData
        ));
    }

    public function previewQuotation(string $id)
    {
        $header = QuotationHeader::findOrFail($id);
        $this->recalculateQuotationTotals($header);
        $data = $this->quotationReportService->buildViewData($header->fresh());

        return view('billing.quotations.amspec.preview', $data);
    }

    public function publicReportView(string $id, string $token)
    {
        $header = QuotationHeader::findOrFail($id);

        if (! hash_equals($this->quotationReportService->publicReportToken($header), $token)) {
            abort(403, 'Invalid quotation report link.');
        }

        $this->recalculateQuotationTotals($header);
        $data = $this->quotationReportService->buildViewData($header->fresh());

        return view('billing.quotations.amspec.public-report', $data);
    }

    public function streamQuotationPdf(string $id)
    {
        $header = QuotationHeader::findOrFail($id);

        return $this->quotationReportService->streamPdf($header);
    }

    public function edit_quotation_detail(Request $request)
    {
        // return response()->json($request->all(),200);
        $detail = QuotationDetails::find($request->detail_id);
        if (!isset($detail->id)) {
            return redirect()->back()->with('error', 'No Quotation analysis with the specified id!');
        }
        $header = QuotationHeader::find($detail->quotation_header_id);
        if ($header->quotation_type == 'General') {
            $detail->item_name = $request->item;
            $detail->part_no = $request->part_no;
            $detail->description = $request->description;
            if ($request->hasFile('photo')) {
                $path = $request->photo->path();
                $file = Storage::putFile('quote_items', new File($path));
                $file = explode('/', $file);
                $fname = '/storage/quote_items/' . urlencode(end($file));
                $detail->photo_url = (string) $fname;
            }
        } else {

            $detail->part_no = implode(',', $request->part_no);
            $detail->accredited_analytes = isset($request->accreditted_analytes) ? $request->accreditted_analytes : $detail->accredited_analytes;
            $detail->subcontracted_analytes = isset($request->sub_analytes) ? $request->sub_analytes : $detail->subcontracted_analytes;
            $detail->sub_acc_analytes = isset($request->sub_acc) ? $request->sub_acc : $detail->sub_acc_analytes;
            $detail->default_analytes = isset($request->default_analytes) ? $request->default_analytes : $detail->default_analytes;
            $analysis_types_ids = explode(',', $request->part_no);
            $insertArr = [];
            QuotationDetailAnalysisSplit::where('quotation_detail_id', $detail->id)->delete();

            foreach ($analysis_types_ids as $a_id) {
                $data = ["analysis_type_id" => $a_id, 'quotation_detail_id' => $detail->id];
                array_push($insertArr, $data);
            }
            QuotationDetailAnalysisSplit::insert($insertArr);
        }
        $unitPrice = (float) $request->unit_price;
        if ($unitPrice <= 0 && $header->quotation_type !== 'General') {
            $elementIds = $this->quotationPricingResolver->collectElementIdsFromDetail($detail);
            $unitPrice = $this->quotationPricingResolver->suggestLineUnitPrice(
                $header,
                (string) $detail->sample_type,
                (string) $detail->part_no,
                $elementIds
            );
        }
        $detail->unit_price = $unitPrice;
        $detail->tax = $request->tax;
        $detail->quantity = $request->quantity;
        $detail->save();

        $this->recalculateQuotationTotals($header);

        return redirect()->back()->with('success', 'Quotation detail edited successfully!');
    }
    public function delete_quotation_detail($id)
    {
        $detail = QuotationDetails::find($id);
        if (!isset($detail->id)) {
            return redirect()->back()->with('error', 'No Quotation analysis with the specified id!');
        }
        $detail->delete();
        return redirect()->back()->with('success', 'Quotation analysis deleted successfully!');
    }
    public function delete_quotation(Request $request, $id)
    {
        if ($request->header_id != $id) {
            return redirect()->back()->with('error', 'Quotation Id does not match the passed Quotation ID!');
        }
        $header = QuotationHeader::find($id);
        if (!isset($header->id)) {
            return redirect()->back()->with('error', 'There is no Quotation with the specified ID');
        }
        $header->delete();
        return redirect()->route('quotation-index')->with('success', 'Quotation deleted successfully!');
    }
    public function save_draft(Request $request, $id)
    {
        $header = QuotationHeader::find($id);
        $header->is_draft = 1;
        $header->save();
        return redirect()->route('quotation-index')->with('success', 'Quotation ' . $header->quote_number . ' saved as draft successfully');
    }
    public function save_quotation_final(Request $request, $id)
    {
        $header = QuotationHeader::find($id);
        $header->status = 'Quote Complete';
        // return response()->json((int) $id,200);
        $header->tax = (int) $request->tax;
        $header->sub_total = (int) $request->sub_total;
        $header->total_amount = (int) $request->total;
        $header->is_draft = 0;
        $header->is_complete = 1;
        $header->save();
        return redirect()->back()->with('success', 'Quotation ' . $header->quote_number . ' saved successfully');
    }
    public function clone_quotation($id)
    {
        $header = QuotationHeader::find($id);
        $header_clone = new QuotationHeader();
        $header_clone->crm_customer_id = $header->crm_customer_id;

        $header_clone->crm_customer_contact_id = $header->crm_customer_contact_id;
        $header_clone->quote_date = $header->quote_date;
        $header_clone->status = $header->status;
        $header_clone->expiring_date = $header->expiring_date;
        $header_clone->prepared_by_id = auth()->user()->id;

        $header_clone->pricelist_id = null; // No longer using pricelists
        $header_clone->save();

        $idstr = strval($header_clone->id);
        if (strlen($idstr) < 4) {
            $count = 4 - strlen($idstr);
            $zeros = str_repeat('0', $count);
            $number = 'QUOTE-' . $zeros . $idstr;
        } else {
            $number = 'QUOTE-' . $idstr;
        }

        $header_clone->quote_number = $number;
        $header_clone->is_draft = 1;
        $header_clone->save();

        $details = QuotationDetails::where('quotation_header_id', $header->id)->get();
        foreach ($details as $detail) {
            $new = new QuotationDetails();
            $new->sample_type = $detail->sample_type;
            $new->unit_price = $detail->unit_price;
            // return response()->json($request->unit_price);
            $new->tax = $detail->tax;
            $new->part_no = $detail->part_no;
            $new->quantity = $detail->quantity;
            $new->analyte_id = $detail->analyte_id;
            // return response()->json($request->analysis_id,200);
            $new->quotation_header_id = $header_clone->id;

            $new->save();
        }
        return redirect()->route('add-qoute-details-view', ['id' => $header_clone->id]);
    }
    public function redirect_from_docs($id, $stage)
    {
        $header = QuotationHeader::find($id);
        // return response()->json($stage);
        $header->status = $stage;
        $header->is_complete = 0;
        $header->save();
        return redirect()->route('add-qoute-details-view', ['id' => $header->id, 'stage' => $header->status]);
    }
    public function print_quotation($id)
    {
        ini_set('max_execution_time', 300);
        $header = QuotationHeader::findOrFail($id);
        $this->recalculateQuotationTotals($header);
        $stored = $this->quotationReportService->storePdf($header->fresh());

        return $stored;
    }
    public function upload_quotation(Request $request, $id)
    {
        $header = QuotationHeader::find($id);
        // return response()->json($request->all(),200);

        $contact = getCrmCustomerContactById($header->crm_customer_contact_id);
        $name = $contact->first_name . ' ' . $contact->middle_name . ' ' . $contact->last_name;
        $company = getActiveCompany();
        $message = 'Quotation ' . $header->quote_number . ' has been to you from ' . $company->name . '. <br>Kindly find it attached below. Contact us if you need any clarification. ';
        $body = 'Hi ' . $name . ', <br>' . $message . '<br>Regards, <br>' . $company->name . '.';
        $subject = '[' . $company->name . '] Quotation ' . $header->quote_number . '.';
        $file = \storage_path() . '/app' . $header->upload_url;
        $notify = notify_user($body, $contact->email, $subject, $file);
        $header->email_to_customer = getTodayDate();
        $header->save();

        return redirect()->back()->with('success', 'Quotation uploaded and sent to client successfully');
    }
    public function get_quotation_detail($id)
    {
        $detail = QuotationDetails::find($id);
        if (isset($detail->id)) {
            $sample_types = SampleType::find($detail->sample_type);
            $analysis_data = AnalysisType::where('sample_type_id', $detail->sample_type)->get();
            $detail_sub_analytes = explode(',', $detail->subcontracted_analytes);
            $detail_accredited_analytes = explode(',', $detail->accredited_analytes);
            $detail_part_no = explode(',', $detail->part_no);
            $analytes_ac = [];
            $analyte_sa = [];
            $analyte_sub_acc = [];
            $default_a = [];
            $part_no = [];
            foreach ($detail_accredited_analytes as $ac) {
                $analyte = AnalysisElements::find($ac);
                if (isset($analyte->id)) {
                    $an = getAnalyteByID($analyte->analyte_id);
                    array_push($analytes_ac, $an->name);
                }
            }
            foreach ($detail_sub_analytes as $sa) {
                $analyte = AnalysisElements::find($sa);
                if (isset($analyte->id)) {
                    $an = getAnalyteByID($analyte->analyte_id);
                    array_push($analyte_sa, $an->name);
                }
            }
            foreach (explode(',', $detail->sub_acc_analytes) as $sc) {
                $analyte = AnalysisElements::find($sc);
                if (isset($analyte->id)) {
                    $an = getAnalyteByID($analyte->analyte_id);
                    array_push($analyte_sub_acc, $an->name);
                }
            }
            foreach (explode(',', $detail->default_analytes) as $sc) {
                $analyte = AnalysisElements::find($sc);
                if (isset($analyte->id)) {
                    $an = getAnalyteByID($analyte->analyte_id);
                    array_push($default_a, $an->name);
                }
            }
            foreach ($detail_part_no as $pn) {
                $analysis = AnalysisType::find($pn);
                if (isset($analysis->id)) {
                    array_push($part_no, $analysis->name);
                }
            }

            $detail['sub_analytes'] = $analyte_sa;
            $detail['acc_analytes'] = $analytes_ac;
            $detail['sub_acc'] = $analyte_sub_acc;
            $detail['default'] = $default_a;
            $detail['part_no_final'] = implode(',', $part_no);
            $detail['part_no_value'] = $detail_part_no;
            $detail['sample_type_name'] = getSampleTypeByID($detail->sample_type)->name;
        }
        $final = [];
        $final['analysis_data'] = $analysis_data;
        $final['detail'] = $detail;
        return $final;
    }
    public function convertQuoteToBatch(Request $request)
    {
        $quote = QuotationHeader::with(['details', 'contact', 'customer'])->find($request->quote_id);
        $sample_type_arr = array_unique(QuotationDetails::where('quotation_header_id', $quote->id)->pluck('sample_type')->toArray());
        $sampletypes = SampleType::whereIn('id', $sample_type_arr)->get();
        $batch_config = SystemConfiguration::where('key', 'batch_code_config')->first();
        if (!isset($batch_config->id)) {
            return redirect()->back()->with('error', 'Kindly add batch_code_config configuration');
        }
        $unit = CRMCompanyUnit::where('crm_customer_id', $quote->crm_customer_id)->where('name', $quote->contact->unit_name)->first();

        $account = SystemConfiguration::find($quote->customer->account_status);
        if (isset($account->id)) {
            $account_status = $account->key;
            if ($account->key == 'Suspended') {
                return redirect()->back()->with('error', 'The customer is currently suspended!');
            }
        } else {
            $account_status = 'N/a';
        }
        foreach ($sampletypes as $sample_type) {
            $cust_code = str_split($quote->customer->code);
            $code = [];
            $loop = 0;
            $cont = [];
            foreach ($cust_code as $cc) {
                if ((int) $cc > 0) {
                    array_push($cont, $loop);
                } elseif (is_string($cc) && $cc != '0') {
                    array_push($code, $cc);
                }
                ++$loop;
            }
            $tt = sizeof($cust_code) - 1;

            $ranges = range($cont[0], $tt);
            $values = [];
            if (sizeof($cont) < 2) {
                array_push($values, '0');
                array_push($values, $cust_code[$cont[0]]);
            } else {
                foreach ($ranges as $r) {
                    array_push($values, $cust_code[$r]);
                }
            }

            $cP = 'BA' . $batch_config->value . implode('', $values) . $sample_type->code;
            $config_batch_no = SystemConfiguration::where('key', 'batch_start_no')->first();
            if (!isset($config_batch_no->id)) {
                return redirect()->back()->with('error', 'Kindly set the start batch no');
            }
            $last_id = isset(SampleHeader::latest('id')->first()->id) ? SampleHeader::latest('id')->first()->id : 0;
            $batch_no_s = $config_batch_no->value + $last_id + 1;
            $final_no = '';
            if (strlen(strval($batch_no_s)) < 4) {
                $zerosss = str_repeat('0', 4 - strlen(strval($batch_no_s)));
                $final_no = $zerosss . '' . strval($batch_no_s);
            } else {
                $final_no = strval($batch_no_s);
            }
            $batch_code = $cP . '' . $final_no;



            $insert_batch = [
                'receipt_date' => date('Y-m-d'),
                'date_collected' => date('Y-m-d'),
                'crm_customer_id' => $quote->crm_customer_id,
                'crm_unit_name' => $quote->contact->unit_name,
                'sample_type_id' => $sample_type->id,
                'reference_number' => $quote->quote_number,
                'quote_no' => $quote->quote_number,
                'batch_code' => $batch_code,
                'crm_contact_id' => $quote->contact->id,
                'status' => 'Samples Reception',
                'crm_unit_id' => $unit->id,
                'lab_capable' => 1,
                'client_instruction_clear' => 1,
                'current_account_status' => $account_status,
                'receiving_officer' => auth()->user()->id,
                'quote_id' => $quote->id,

            ];
            $batch = SampleHeader::create($insert_batch);
            $analysis_max_report_time = 0;
            $analytes_max_report_time = 0;

            foreach ($quote->details as $detail) {
                if ($detail->sample_type == $sample_type->id) {
                    $analysis_types_arr = QuotationDetailAnalysisSplit::where('quotation_detail_id', $detail->id)->pluck('analysis_type_id')->toArray();
                    foreach (range(1, $detail->quantity) as $no_samples) {
                        $lab = Lab::find($request->lab_id);
                        $code = SampleDetails::orderBy('id', 'DESC')->first();
                        $last_sample = isset($code->id) ? $code->sample_no : $lab->start_sample_no;

                        // $last_sample = isset(SampleDetails::latest('id')->first()->id) ? substr(SampleDetails::latest('id')->first()->sample_code,9,strlen(SampleDetails::latest('id')->first()->sample_code) -1) : $config_start_no->value;

                        // return response()->json($request->sample_details['lab_id'][$k]);
                        $sample_number = intval($last_sample) + 1;
                        // $sample_number = str_pad($sample_number, 4, '0', STR_PAD_LEFT);

                        $sample_code = 'S' . date('Y') . $lab->code . $sample_type->code . sprintf('%0' . '4' . 'd', $sample_number);
                        $sample_no = sprintf('%0' . '4' . 'd', $sample_number);
                        $report_number = 'LR/' . $sample_type->code . '/' . date('Y') . '/' . $lab->code . '/' . sprintf('%0' . '4' . 'd', $sample_number);
                        $insert_sample = [
                            'sample_code' => $sample_code,
                            'sample_no' => $sample_no,
                            'report_number' => $report_number,
                            'analysis_type_id' => implode(',', $analysis_types_arr),
                            'lab_id' => $lab->id,
                            'sample_header_id' => $batch->id,
                            'disposal_date' => \Carbon\Carbon::parse($batch->receipt_date)->addDays(14)->format('Y-m-d'),
                        ];
                        $sample = SampleDetails::create($insert_sample);
                        $this->createDetailAnalysisRelation($batch->id, $sample->id, explode(',', $sample->analysis_type_id));

                        $analysis_elements = AnalysisElements::with(['analyte'])->whereIn('id', explode(',', $detail->default_analytes))->get();
                        foreach ($analysis_elements as $element) {
                            $insert_captured = [
                                "sample_detail_code" => $sample->sample_code,
                                "sample_detail_id" => $sample->id,
                                "sample_header_id" => $batch->id,
                                "analyte_id" => $element->analyte_id,
                                "analysis_type_id" => $element->analysis_type_id,
                                "analyte_code" => $element->analyte->code,
                                "equipment_id" => $element->equipment_id,
                                "method_id" => $element->method_id,
                                "reporting_unit_id" => $element->reporting_unit,
                                "user_id" => Auth()->user()->id,
                                "operator_id" => $element->operator_id,
                                "ltm_method_id" => $element->ltm_method_id,
                                "analyte_accredited" => $element->non_accredited,
                                "analyte_status_contracted" => $lab->is_external ?? 0,
                                "lab_section_id" => $element->lab_section_id,
                                "parameters_order" => $element->level,
                                "remark_is_manual" => $element->remark_is_manual
                            ];
                            $captured = CapturedResult::create($insert_captured);
                            $insert_result = [
                                'captured_result_id' => $captured->id,
                                "sample_detail_id" => $sample->id,
                                'sample_detail_code' => $sample->sample_code,
                                "sample_header_id" => $batch->id,
                                "analyte_id" => $element->analyte_id,
                                "analysis_type_id" => $element->analysis_type_id,
                                "analyte_code" => $element->analyte->code,
                                "unit_code" => $element->reporting_unit,
                                "reporting_symbol" => $element->reporting_symbol,
                                "recheck" => 0,
                                "analyte_status_contracted" => $lab->is_external ?? 0,
                                "lab_section_id" => $element->lab_section_id,
                                "parameters_order" => $element->level,
                                "remark_is_manual" => $element->remark_is_manual

                            ];
                            $result = Result::create($insert_result);
                        }

                        $analysis_max_report_time = AnalysisType::whereIn('id', $analysis_types_arr)->max('reporting_time');
                        $analytes_max_report_time = AnalysisElements::whereIn('analysis_type_id', $analysis_types_arr)->max('reporting_time');
                        $maxReportingTime = $analysis_max_report_time > $analytes_max_report_time ? $analysis_max_report_time : $analytes_max_report_time;
                    }
                }
            }
            $targetDateStr = 'Target Date';
            $targetDate = \App\SampleDate::where('sample_header_id', $batch->id)->where('name', $targetDateStr)->first() ?? new \App\SampleDate();
            $targetDate->name = $targetDateStr;
            $targetDate->sample_header_id = $batch->id;
            $targetDate->date = \Carbon\Carbon::parse($batch->receipt_date)->addDays($maxReportingTime);
            $targetDate->save();

            $strStage = 'Sample Labeling';
            $samWk = 'Samples Reception';

            $stage = SampleAnalysisStage::where('name', $strStage)->where('sample_workflow', $samWk)->first();
            $batch->days_of_analysis = $maxReportingTime;
            $batch->sample_tracking_stage = $stage->id;
            $batch->save();

        }

        QuotationHeader::find($request->quote_id)->update(['is_batch_generate' => 1]);

        return redirect()->back()->with('success', 'Batch created successfully');


    }
    public function createDetailAnalysisRelation($batch_id, $sample_id, $analysis_type)
    {
        $data = [];
        SampleAnalysisTypeRelation::where('batch_id', $batch_id)->where('sample_detail_id', $sample_id)->whereNotIn('analysis_type_id', $analysis_type)->delete();
        $existing = SampleAnalysisTypeRelation::where('batch_id', $batch_id)->where('sample_detail_id', $sample_id)->pluck('analysis_type_id')->toArray();
        foreach ($analysis_type as $at) {
            if (!in_array($at, $existing)) {
                $data[] = [
                    'analysis_type_id' => $at,
                    'batch_id' => $batch_id,
                    'sample_detail_id' => $sample_id,
                ];
            }
        }
        sizeof($data) > 0 ? SampleAnalysisTypeRelation::insert($data) : '';

        return 'success';
    }

    private function recalculateQuotationTotals(QuotationHeader $header): void
    {
        $details = QuotationDetails::where('quotation_header_id', $header->id)->get();
        $subTotal = 0.0;
        $taxes = 0.0;

        foreach ($details as $detail) {
            $extended = (int) $detail->quantity * (float) $detail->unit_price;
            $subTotal += $extended;

            if ($detail->tax != 0) {
                $taxes += (int) $detail->tax / 100 * (float) $detail->unit_price * (int) $detail->quantity;
            }
        }

        $header->sub_total = $subTotal;
        $header->tax = $taxes;
        $header->total_amount = $subTotal + $taxes;
        $header->save();
    }
}
