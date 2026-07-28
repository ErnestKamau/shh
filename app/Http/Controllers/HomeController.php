<?php

namespace App\Http\Controllers;

use App\InventoryCategories;
use App\InventorySubCategories;
use App\InventoryDepartment;
use App\InventoryItem;
use App\Supplier;
use App\SampleDetails;

use Session;
use App\EntityAttachment;
use App\Models\System\SystemConfiguration;
use App\SampleHeader;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File as PILE;
use Illuminate\Support\Facades\Storage;
use App\Services\AI\AiInferenceService;
use App\Support\CaseInsensitiveSearch;

class HomeController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    $this->middleware(['auth','twofactor']);
  }

	/**
	 * Refresh cached permission payload for the session.
	 */
  public function loadUserPermissions($user)
  {
		Session::forget('permissions');
  }

  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
   */
  public function index()
  {
	$user = auth()->user();
	$user->is_client = 0;
	$user->save();
	$user->assignRole('admin');
	if ($user->active == 0){
		\Auth::logout();
		return redirect()->route('login');
	}elseif($user->is_client == 1){
		
		return redirect()->route('client-dashboard-home');
	}elseif($user->supplier_id !== null){
		$user->is_online = 1;
		$user->save();
		return redirect()->route('supplier-dashboard-home');
	}elseif($user->is_tablet == 1){
		$user->is_online = 1;
		$user->save();
		return redirect()->route('sample-submissions');
	}
	else{
		$user->is_online = 1;
		$user->save();
		$user_personel_access = $user->can('personnel.permission')
			|| $user->can('Personnel.permission')
			|| $user->hasRole('Access Personnel');

		$this->loadUserPermissions($user);

		return view('home',compact('user_personel_access'));

	}


  }

  public function default_company(Request $request){
    Session::put('company_id', $request->company_id);
    return redirect()->back();
	}

  public function remove_attachment(Request $request, $docID){
		$attachment = EntityAttachment::find($docID);
		$attachment->delete();
		if(is_file($attachment->file)){
			Storage::delete($attachment->file);


		}
		else{
			return redirect()->back()->with('active', 'No matching file was found!');
		}

    return redirect()->back()->with('success', 'Attachment removed!');
	}

  public function add_attachment(Request $request){
		$attachment = new EntityAttachment;
		$attachment->title = $request->title;
		$attachment->type = "Page Attachment";
		$attachment->model = $request->input('url');
		$attachment->model_id = "0";
		if ($request->hasFile('attachment')){
      $path = $request->attachment->path();
      $file = Storage::putFile('page-attachents', new File($path));
      $attachment->mime = Storage::mimeType($file);
      $attachment->size = Storage::size($file);
      $file = explode('/', $file);

      $fName = '/storage/page-attachents/'.urlencode(end($file));

      $attachment->file = (String) $fName;
		}
		else{
			return redirect()->back()->with('error', 'No attachment was found!');
		}
		$attachment->created_by = \Auth::user()->id;
		$attachment->description = $request->description;
		$attachment->save();

    return redirect()->back()->with('success', 'Attachment added!');
	}

	public function searchsample(Request $request){
		$query = SampleDetails::query();
		CaseInsensitiveSearch::equals($query, 'sample_code', (string) $request->sample);
		$sample = $query->get();
		// return response()->json($sample[0]->id,200);
		if(isset($sample[0]->id)){
			$batch = SampleHeader::find($sample[0]->sample_header_id);
			return redirect()->route('view-batch-details',['batch'=>$batch->id]);
		}else{
			return redirect()->back()->with('error','No sample with the specified code!');
		}
	}

	public function my_approvals(){
		$approvals = pendingApprovals();

		// return response()->json($approvals, 200);

		return view('layouts.inventory.approvals.index', compact('approvals'));
	}

  public function inventory(){
		$location = getCurrentUserLocation();
		if (! $location?->id) {
			viewableLocations();
			$location = getCurrentUserLocation();
		}

		$departments = InventoryDepartment::all()->count();

		if (! $location?->id) {
			return view('layouts.inventory.index', [
				'activity' => collect(),
				'categoriesNo' => 0,
				'departments' => $departments,
				'suppliers' => 0,
			]);
		}

		$locationId = $location->id;

		$categoriesNo = InventoryCategories::where('inventory_location_id', $locationId)
			->get()->count();

		$suppliers = Supplier::where('inventory_location_id', $locationId)->get()->count();

		$activity = InventoryItem::join('inventory_categories as ic', 'ic.id', '=', 'inventory_items.inventory_category_id')
			->join('inventory_sub_categories as isc', 'isc.inventory_category_id', '=', 'ic.id')
			->selectRaw('isc.name as category, SUM(inventory_items.stock_in) as stock_in, SUM(inventory_items.stock_out) as stock_out')
			->where('ic.inventory_location_id', $locationId)
			->where("ic.category_type", "!=", "is_lab_samples")
			->where('inventory_items.created_at', '>', (new \Carbon\Carbon)->submonths(1))
			->groupBy('isc.name')->get();

		// return json_encode($activity);

    return view('layouts.inventory.index', compact('activity', 'categoriesNo', 'departments', 'suppliers'));
  }

	public function get_file($link, $public=false)
	{

		if($public == false){
			$link = explode('/storage', $link);
			$link = end($link);
		}

		// Add folder path here instead of storing in the database.
		$path = $public ? public_path($link) : storage_path('app'.$link);
		if (!PILE::exists($path)) {
			// Livewire uploads store under the public disk (storage/app/public/...).
			$publicDiskPath = $public
				? storage_path('app/public/' . ltrim((string) $link, '/'))
				: storage_path('app/public' . $link);
			if (PILE::exists($publicDiskPath)) {
				$path = $publicDiskPath;
			} else {
				$path = \public_path('images/no-logo.png');
			}
		}

		// return $path;

		$file = PILE::get($path);
		$type = PILE::mimeType($path);

		return 'data:'.$type.';base64,'.base64_encode($file);


		return $file->path();
	}
	public function aiIndex($id = null){
		$company = getActiveCompany();
		return view('layouts.ImaraAi.index', compact('company'));
	}

    /**
     * Handle AI chat requests via the internal inference proxy.
     */
    public function aiChat(Request $request, AiInferenceService $aiService)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $response = $aiService->chat($request->message);

        return response()->json($response);
    }
}
