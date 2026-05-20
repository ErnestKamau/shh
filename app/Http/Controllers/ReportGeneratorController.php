<?php

namespace App\Http\Controllers;

use App\Exports\ReportExporter;
use App\Jobs\PDFGenerator;
use Illuminate\Http\Request;
use App\ReportGenerator\ReportGenerator;
use Illuminate\Support\Facades\DB;

use App\RequestEntity;
use App\SavedReportConfiguration;
use Maatwebsite\Excel\Facades\Excel;

use App\InventoryCategories;


class ReportGeneratorController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth', ['except'=>'generate_report']);
  }

  public function index($params){
    // $report_params = array();
    $sub_report = '';
    $report_params = array(
      'VAR_BATCH_ID' => '',
      'LOGO_DIR' => base_path('lib/JasperReports/reports/logos/'),
      'SUBREPORT_DIR' => base_path('lib/JasperReports/reports/report_headers/')
    );

    $report_input_dir = base_path('lib/JasperReports/reports/aqualytic/');

    if (isset($params['report_type']) && $params['report_type'] == 'aqualytic_report_one') {
      $report_params['VAR_BATCH_ID'] = $params['VAR_BATCH_ID'];
      $sub_report = 'aqualytic_report_header';
    }

    $report_file_name = $params['report_name'] . '.jrxml';
    $rg = new ReportGenerator($this, $params['report_type'], $params['client_name']."-".$params['batch_code']."-".$params['report_date'], true, $params['client_name'], $report_input_dir);

    $rg->addMainReport($report_file_name);
    // $rg->addSubReports();
    $rg->setReportParams($report_params);
    $rg->generateReport();

    // return view('layouts.configuration.index');
	}

	public function consumption(Request $request){
		$year = date('Y');
		$month = date('F');
		$group = 'Cost Center';

		$category = false;
		$topColumns = [];
		$title = 'Consumption';
		$consumption = [];

		$columns = [
			"main"=>["Date", "Week", "Requisition Number"]
		];

		$theData = [];
		$theWeekData = [];

		$sub_cats = ['All Items...'=>''];

		$categories = InventoryCategories::orderBy('name')->get();

		if($request->has('category')){
			if($request->has('filter_item') && trim($request->filter_item) !=""){
				$group = 'Department';
			}

			if($request->has('year')){
				$year = $request->year;
			}

			if($request->has('month')){
				$month = $request->month;
			}

			$consumption = DB::table('view_consumption_by_category_reports')
				->where('Year', $year)->where('Month', $month)->where('Category', $request->category);

			$zumption = DB::table('view_consumption_by_category_reports')
				->where('Year', $year)->where('Month', $month)->where('Category', $request->category)->get();

			if($group == 'Department'){
				$consumption = $consumption->where('Item', $request->filter_item);
			}

			$consumption = $consumption->get();

			foreach($zumption as $z){
				$sub_cats[$z->Item] = $z->Item;
			}

			foreach($consumption as $c){
				$cA = (array) $c;
				$keyStr = implode(" - ", [$c->Date,$c->Week]);

				if(!isset($theData[$keyStr])){
					$theData[$keyStr] = [
						"Date" => $c->Date,
						"Week" => $c->Week,
						"Requisition Number" => '',
					];
				}

				if(!isset($theWeekData[$c->Week])){
					$theWeekData[$c->Week] = [];
				}

				$theData[$keyStr]["Requisition Number"] = explode('/', $theData[$keyStr]["Requisition Number"]);

				$theData[$keyStr]["Requisition Number"][] = $cA['Requisition Number'];

				$theData[$keyStr]["Requisition Number"] = trim(implode('/', array_unique($theData[$keyStr]["Requisition Number"])), '/');

				$topLevel= $group=="Department" ? $cA['Cost Center'] : trim($c->Item);
				$lowerLevel= $group=="Department" ? $cA['Department'] : $cA['Cost Center'];

				if(!isset($theData[$keyStr][$topLevel])){
					$theData[$keyStr][$topLevel] = [];
				}

				if(!isset($theWeekData[$c->Week][$topLevel])){
					$theWeekData[$c->Week][$topLevel] = [];
				}

				if(!isset($theData[$keyStr][$topLevel][$lowerLevel])){
					$theData[$keyStr][$topLevel][$lowerLevel] = ["quantity"=>0, "price"=>0];
				}

				if(!isset($theWeekData[$c->Week][$topLevel][$lowerLevel])){
					$theWeekData[$c->Week][$topLevel][$lowerLevel] = ["quantity"=>0, "price"=>0];
				}

				$theData[$keyStr][$topLevel][$lowerLevel]["quantity"] += floatval($cA['Quantity']);
				$theData[$keyStr][$topLevel][$lowerLevel]["price"] += floatval($cA['TOTAL PRICE']);

				$theWeekData[$c->Week][$topLevel][$lowerLevel]["quantity"] += floatval($cA['Quantity']);
				$theWeekData[$c->Week][$topLevel][$lowerLevel]["price"] += floatval($cA['TOTAL PRICE']);

				if(!isset($topColumns[$topLevel])){
					$topColumns[$topLevel] = [];
				}

				$topColumns[$topLevel][] = trim($group=="Department" ? $c->Department : $cA['Cost Center']);
				$topColumns[$topLevel] = array_unique($topColumns[$topLevel]);
			}

			$title = $request->category.' Consumption';
		}

		// return response()->json($topColumns, 200);

    	return view('layouts.inventory.reports.consumption', compact('sub_cats', 'theWeekData', 'theData', 'month', 'year', 'consumption', 'categories', 'title', 'columns', 'topColumns'));
	}


	public function check_pdf_processing_progress($id){
		$entity = RequestEntity::find($id);
		if($entity->downloadable_link == "pending"){
			return json_encode([
				"status"=>false
			]);
		}
		else{
			return json_encode([
				"status"=> trim($entity->downloadable_link == "") && trim($entity->downloadable_link == "pending") ? false : url($entity->downloadable_link)
			]);
		}
	}

	public function generate_supplier_pdf($spl, $id){
		$entity = \App\RequestEntity::find($id);
		$requisition = \App\RequestEntity::find($entity->parent_material_requisition);
		$supplier = \App\Supplier::find($spl);

		$itemIDs = $supplier->itemIDs();

		$items = \App\RequestEntityItem::join('inventory_sub_categories as isc', 'isc.id', 'request_entity_items.inventory_sub_category_id')
			->whereIn('isc.id', $itemIDs)->where('request_entity_items.request_id', $id)
			->selectRaw('isc.id, isc.name, isc.unit_type, request_entity_items.quantity')->get();

		$preparedBy = $entity->creator();

		$supplierQuoteDate = \App\SupplierQuote::where('supplier_id', $spl)->where('request_id', $id)->first();

		$rfq_approval_id = getConfigByName('rfq_approval_id');
		$rfq_approval_id = count($rfq_approval_id) > 0 ? $rfq_approval_id[0]->value : 0;

		$finalApproval = \App\EntityApproval::join('users as u', 'u.id', 'entity_approvals.user_id')
			->where('entity_approvals.model_id', $requisition->id)
			->where('entity_approvals.approval_id', $rfq_approval_id)->first();

		// return json_encode($finalApproval);

		return view('layouts.inventory.templates.supplier-rfq', compact('supplier', 'entity', 'items', 'requisition', 'preparedBy', 'finalApproval', 'supplierQuoteDate'));
	}

	public function generate_report_pdf($id, $isHTML=false){
		$job = PDFGenerator::dispatch($id, $isHTML);
		$entity = RequestEntity::find($id);
		$entity->downloadable_link ="pending";
		$entity->save();

		return redirect()->back()->with('success', 'Preparing PDF...');
	}

	public function generate_report($id, $is_supply=false, $isHTML=false){
		$currentE = RequestEntity::find($id);

		// if(in_array($currentE->request_type, ['Lend', 'Loan'])){
		// 	return redirect()->back()->with('error', 'No templates available');
		// }

		$swapper = false;
		if($currentE->request_type == "Request for Quotation"){
			$id = RequestEntity::find($id)->parent_material_requisition;
			$swapper = true;
		}

		$entity = RequestEntity::find($id);

		$arrays = getDocumentTemplates();

		$isInternal = true;

		return view($is_supply ? $arrays[$is_supply] : $arrays[$swapper ? $currentE->request_type : $entity->request_type], compact('entity', 'isHTML', 'isInternal'));
	}

	public function inventory_reports(){
		$str = "Inventory_Items_View,analysis_elements,analysis_guides,analysis_method_elements,analysis_methods,analysis_types,analytes,approvals,asset_locations,asset_types,audits,batch_comments,batch_notifications,captured_results,chain_of_custodies,chain_of_custody_complaints,chat_message,companies,company_products,complaint_type,complaintattachments,complaintnotes,complaints,complaintsresolutions,conversation,countries,crm_company_units,crm_customer_contacts,crm_customers,currency_conversions,customer_invoice,customerfeedbacks,customerqualifications,disposal_reasons,entity_approvals,entity_attachments,entity_notes,equipment,equipment_operators,equipment_usage,failed_jobs,inventory_categories,inventory_departments,inventory_item_notes,inventory_items,inventory_location_users,inventory_locations,inventory_order_item_to_inventory_items,inventory_order_items,inventory_orders,inventory_store_contacts,inventory_store_slot_contents,inventory_store_slots,inventory_stores,inventory_sub_categories,inventory_supplier_ratings,invoice_details,item_brands,item_states,job_designation_responsibility,lab_category_items,labs,maintainance_calibration_logs,method_reagents,migrations,module_pre_configs,naming_convension_consensuses,o_t_p_s,parts_repaireds,password_resets,personel_certifications,personnel_work_histories,phone_contacts,pricelist_customers,pricelist_items,pricelists,qualifications,quotation_details,quotation_headers,report_header_details,report_table_configurations,reporting_units,request_entities,request_entity_items,request_types,results,role_certifications,roles,sample_analysis_stages,sample_conditions,sample_dates,sample_details,sample_headers,sample_points,sample_results,sample_to_sample_analysis_stages,sample_type_qualifications,sample_types,samples_by_category,standard_values,standards,stock_taking_counters,stock_taking_sheets,stock_takings,stock_transfer_items,stock_transfers,supplier_categories,supplier_contract_items,supplier_contracts,supplier_quote_attachments,supplier_quote_notes,supplier_quotes,supplier_r_f_q_s,suppliers,system_configuration_types,system_configurations,tax_regime,unit_of_measure_conversions,uom_conversions,user_alerts,user_roles,users,verification_logs";
		$tables_to_load = explode(",", $str);

		$configurations = SavedReportConfiguration::all();

		return view('layouts.inventory.reports.index', compact('tables_to_load', 'configurations'));
	}

	public function fields($table)
	{
		return DB::getSchemaBuilder()->getColumnListing($table);
	}

	public function fetch(Request $request)
	{
		$raw = $request->raw;
		$filters = $request->filters ?? [];

		if(count($filters) > 0){

			$filterParts = [];

			foreach($filters as $f){
				$filterParts[] = $f['field']." ".$f['operator']." '".$f['value']."'";
			}

			$raw = strtolower($raw);
			$parts = explode("order by", $raw);

			$filterStr = implode(" and ", $filterParts);

			if(count($parts) == 1){
				$rawStr = [$parts[0], "HAVING", $filterStr];
			}
			else{
				$rawStr = [$parts[0], "HAVING", $filterStr, "ORDER BY", $parts[1]];
			}

			$raw = implode(' ', $rawStr);
		}

		// return $raw;

		$data = DB::select($raw);
		return json_encode(["data"=>$data, "sql"=>$raw]);
	}

	public function save_report(Request $request, $id)
	{
		$config = SavedReportConfiguration::find($id);
		$config->configuration = $request->graph_query;
		$config->raw_sql = $request->report_query;
		$config->save();

		return redirect()->back()->with('success', 'Configurations Saved');
	}

	public function delete(Request $request)
	{
		$data = SavedReportConfiguration::find($request->id)->delete();
		return json_encode(array("status"=>true));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$data = $request->all();
		// return "<pre>".json_encode($data, JSON_PRETTY_PRINT);
		unset($data['_token']);

		try{
			$savedConfiguration = new SavedReportConfiguration($data);
			$savedConfiguration->save();
			$msg = array("status"=>true);
		}
		catch(\Exception $e){
			$msg = array("error"=> $e->getMessage());
		}

		return json_encode($msg);
	}

	public function print(Request $request){
		$data = DB::select($request->sql);

		if(count($data) == 0){
			return redirect()->back()->with('error', 'No data found for the report');
		}

		$title = $request->title;
		$columns = array_keys((array) $data[0]);
		// return response()->json($columns, 200);
		return view('layouts.inventory.reports.print', compact('data', 'title', 'columns'));
	}

	public function download_items_xlsx(Request $request, $id, $isPDF=false){
		if($isPDF){
			$ids = explode(',', $id);

			$reqItem = RequestEntity::whereIn('id', $ids)->first();

			$reqItemType = $reqItem->request_type;

			$items = \App\RequestEntityItem::join('request_entities as re', 're.id', 'request_entity_items.request_id')
				->join('inventory_sub_categories as isc', 'isc.id', 'request_entity_items.inventory_sub_category_id')
				->leftJoin('inventory_stores as ins', 'ins.id', 'request_entity_items.store_id')
				->leftJoin('inventory_store_slots as iss', 'iss.inventory_store_id', 'ins.id');

			if($reqItemType == "Material Issuance"){
				$items = $items->join('request_entities as re2', 're2.id', 're.parent_request_id')
					->leftJoin('entity_approvals as ea', function($join) {
						$join->on(DB::raw('cast(ea.model_id as text)'), '=', DB::raw('cast(re2.id as text)'));
					})
				->selectRaw('request_entity_items.created_at as issued_at, re2.request_type, re2.cost_center, re2.request_code as `Request Code`, isc.sap_code, ea.approved_at, re2.created_at, isc.name as Item, isc.code as `Code`, request_entity_items.comments as Comments, request_entity_items.uom as `Unit Type`, request_entity_items.quantity as Quantity, ins.name as Store, iss.name as Slot')
				->whereIn('request_entity_items.request_id', $ids)->where('request_entity_items.action', 'issued_received')
				->groupBy('request_entity_items.request_id')->orderBy('isc.name', 'asc')->get()->toArray();
			}
			else{
				$items = 	$items->leftJoin('entity_approvals as ea', function($join) {
						$join->on(DB::raw('cast(ea.model_id as text)'), '=', DB::raw('cast(re.id as text)'));
					})
				->selectRaw('re.request_type, re.cost_center, re.request_code as `Request Code`, isc.sap_code, ea.approved_at, re.created_at, isc.name as Item, isc.code as `Code`, request_entity_items.comments as Comments, request_entity_items.uom as `Unit Type`, request_entity_items.quantity as Quantity, ins.name as Store, iss.name as Slot')
				->whereIn('request_entity_items.request_id', $ids)->groupBy('request_entity_items.id')->orderBy('isc.name', 'asc')->get()->toArray();
			}

			// return json_encode($items);

			$entities = [];

			$fileName = 'No_Data.pdf';

			foreach($items as $item){
				$entity =  [
					"request_type"=>$item['request_type'],
					"request_code" =>$item['Request Code'],
					"created_at" => $item['created_at'],
					"approved_at" => $item['approved_at'],
					"cost_center" => $item['cost_center'],
					"items" => []
				];

				if(isset($item['issued_at'])){
					$entity['issued_at'] = $item['issued_at'];
				}

				$entity = (Object) $entity;

				if(!isset($entities[$entity->request_code])){
					$entities[$entity->request_code] = $entity;

					$fileName =$entity->request_type." - ".$entity->request_code.".pdf";
				}

				$entities[$entity->request_code]->items[] = $item;
			}

			// return response()->json($entities);

			$pdf = \App::make('dompdf.wrapper')->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
	  
			$pdfFile = $pdf->loadView('layouts.inventory.requisition.item-pdf', compact('entities'));
			$pdfFile->setPaper('letter', 'landscape');

			// return $fileName;

			return $pdf->download($fileName);
		}
		else{
			$items = \App\RequestEntityItem::join('request_entities as re', 're.id', 'request_entity_items.request_id')
				->join('inventory_sub_categories as isc', 'isc.id', 'request_entity_items.inventory_sub_category_id')
				->leftJoin('inventory_stores as is', 'is.id', 'request_entity_items.store_id')
				->leftJoin('inventory_store_slots as iss', 'iss.inventory_store_id', 'is.id')
				->selectRaw('re.request_code as `Request Code`, isc.name as Item, isc.code as `Code`, request_entity_items.comments as Comments, request_entity_items.uom as `Unit Type`, request_entity_items.quantity as Quantity, is.name as Store, iss.name as Slot')
				->where('request_entity_items.request_id', $id)->groupBy('request_entity_items.request_id')->orderBy('isc.name', 'asc')->get()->toArray();
			$columns = array_keys((array) $items[0]);
			$title = "Request-Items.xlsx";
			return Excel::download(new ReportExporter($items, $columns), $title);
		}

	}

	public function exportCsv(Request $request)
	{
		$data = DB::select($request->sql);

		if(count($data) == 0){
			return redirect()->back()->with('error', 'No data found for the report');
		}

		$title = urlencode(space_underscore($request->title)).'.'.$request->file_type;

		$newData = [];

		foreach($data as $i=>$s){
			$s = (array) $s;
			$s = array_merge(['No' => $i+1] + $s);
			$s['Date Generated'] = date('Y-m-d');
			$newData[] = $s;
		}

		$columns = array_keys((array) $newData[0]);
		// return json_encode($newData);

		return Excel::download(new ReportExporter($newData, $columns), $title);
	}
}
