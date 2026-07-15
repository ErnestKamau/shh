<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Models\Personnel\PersonelCertification;
use App\Models\Personnel\RoleCertification;

use App\InventoryDepartment;
use App\Http\Controllers\MailController as Mailers;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailer;
use Illuminate\Http\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Excel;
use App\Imports\StandardsImport;
use App\SampleAnalysisStage;
use App\UserLabRelation;

class PersonnelController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
	public function __construct()
  {
    $this->middleware('auth');
  }

  public function index()
  {
		$stages = SampleAnalysisStage::where('active', 1)->get();

		return view('livewire.layout.personnel-app', [
			'componentType' => 'personnel-dashboard',
			'pageTitle' => 'Personnel Dashboard',
			'stages' => $stages,
		]);
	}

	public function personnel_list()
	{
		$stages = SampleAnalysisStage::where('active', 1)->get();

		return view('livewire.layout.personnel-app', [
			'componentType' => 'personnel-list',
			'pageTitle' => 'Personnel List',
			'stages' => $stages,
		]);
	}

  public function show_personnel($id)
  {
		return view('livewire.layout.personnel-app', [
			'componentType' => 'personnel-detail',
			'pageTitle' => 'Personnel Profile',
			'userId' => $id,
		]);

	}

	public function departments()
	{
		return view('livewire.layout.personnel-app', [
			'componentType' => 'organizational-departments',
			'pageTitle' => 'Organizational Departments',
		]);
	}

	public function reset_personnel_password(Request $request, $id){
		$user = getUserById($id);
		if (!$user) {
			return redirect()->back()->with('error', 'No user with specified ID');
		}

		if ($request->has('password','con_password')){
			if ($request->password != $request->con_password){
				return redirect()->back()->with('error' , 'Password did not match.');
			}else{
				$user->password = bcrypt($request->password);
				$user->password_changed_at = null; // force change on next login
				$user->failed_login_attempts = 0;
				$user->login_locked_by_admin_reset = false;
				$user->save();
				$body = 'Hi '.$user->first_name.',<br><br>
						Your password has been reset successfully.Your new password is:
						<br><br>'.$request->password ;
				$mailData = array(
					'body'=>$body,
					'subject'=> '[Imara-Lims Password Reset - '.$user->first_name.']',
					'contacts'=>[$user->email],
				);
				$mailer = new  Mailers;
				$sendmail = $mailer->html_email($mailData,'default');

				return redirect()->back()->with('success','Password reset successfully');
			}
		}else{
			return redirect()->back()->with('error','Password inputs not provided');
		}
	}
	public function deactivate_personnel(Request $request,$id){
		$personel = getUserById($id);
		if (isset($personel->active)){
			if ($request->state == "active"){
				$personel->active = 1;

			}else{
				$personel->active = 0;
			}
			$personel->save();

			return redirect()->back()->with('success','Personel state updated successfully');
		}else{
			return redirect()->back()->with('error','No personel with specified ID');
		}
	}

	public function add(Request $request, $id)
	{
		if($request->has('main_password')){
			if($request->main_password != $request->confirm_password){
				return \redirect()->back()->with('error', 'Passwords did not match.');
			}
		}

		$request->validate([
			'zone_id' => 'nullable|string|exists:zones,id',
			'directorate_id' => 'nullable|string|exists:directorates,id',
			'lab_id' => 'nullable|string|exists:labs,id',
			'zone_ids' => 'nullable|array',
			'zone_ids.*' => 'nullable|string|exists:zones,id',
			'directorate_ids' => 'nullable|array',
			'directorate_ids.*' => 'nullable|string|exists:directorates,id',
			'lab_ids' => 'nullable|array',
			'lab_ids.*' => 'nullable|string|exists:labs,id',
			'analyst_is_gazzetted' => 'nullable|boolean',
			'date_of_gazzette' => 'nullable|date',
			'gazzette_no' => 'nullable|string|max:255',
			'start_of_career' => 'nullable|date',
		]);

		$zoneIds = collect($request->input('zone_ids', []))
			->filter(fn ($id) => !is_null($id) && (string) $id !== '')
			->map(fn ($id): string => (string) $id)
			->unique()
			->values();

		if ($zoneIds->isEmpty() && $request->filled('zone_id')) {
			$zoneIds = collect([(string) $request->zone_id]);
		}

		$directorateIds = collect($request->input('directorate_ids', []))
			->filter(fn ($id) => !is_null($id) && (string) $id !== '')
			->map(fn ($id): string => (string) $id)
			->unique()
			->values();

		if ($directorateIds->isEmpty() && $request->filled('directorate_id')) {
			$directorateIds = collect([(string) $request->directorate_id]);
		}

		$labIds = collect($request->input('lab_ids', []))
			->filter(fn ($id) => !is_null($id) && (string) $id !== '')
			->map(fn ($id): string => (string) $id)
			->unique()
			->values();

		if ($labIds->isEmpty() && $request->filled('lab_id')) {
			$labIds = collect([(string) $request->lab_id]);
		}
		
		$personnel = User::find($id) ?? new User();
		$check_user = User::where('email',$request->email)->get();

		if(!isset($personnel->email) && $check_user->count() > 0){
			return redirect()->back()->with('error','There is a user with the specified email!');
		}

		if(isset($personnel->department_id) && $personnel->department_id != $request->department){
			$personnelWorkHistoryChanged = true;
		}

		if(isset($personnel->position) && $personnel->position != $request->position){
			$personnelWorkHistoryChanged = true;
		}

		if(!isset($personnel->id)){
			$personnelWorkHistoryChanged = true;
		}

		$personnel->first_name = $request->first_name;
		$personnel->middle_name = $request->middle_name;
		$personnel->last_name = $request->last_name;
		$personnel->name = $request->first_name." ".$request->middle_name." ".$request->last_name;
		$personnel->email = $request->email;
		$personnel->phone = $request->phone;
		$personnel->company_id = getUserCompany();
		$personnel->location_id = getCurrentUserLocation()->id;
		$personnel->nssf = $request->nssf;
		$personnel->nhif = $request->nhif;
		$personnel->kra_pin = $request->kra_pin;
		$personnel->active = $request->active ?? 0;
		$personnel->lab_section_id = implode(',',$request->lab_section_id ?? []) ?? '';
		$personnel->analyst_is_gazzetted = (bool) $request->boolean('analyst_is_gazzetted');
		$personnel->date_of_gazzette = $personnel->analyst_is_gazzetted && $request->filled('date_of_gazzette')
			? $request->date_of_gazzette
			: null;
		$personnel->gazzette_no = $personnel->analyst_is_gazzetted && $request->filled('gazzette_no')
			? trim((string) $request->gazzette_no)
			: null;
		$personnel->start_of_career = $request->filled('start_of_career')
			? $request->start_of_career
			: null;

		if (Schema::hasColumn('users', 'zone_id')) {
			$personnel->zone_id = $zoneIds->first() ?: null;
		}

		if(!isset($personnel->id)){

			if(isset($request->has_credentials) && $request->has_credentials == '1'){

				if($request->has('main_password') && trim($request->main_password) != ""){
					$personnel->password = bcrypt($request->main_password);
					$personnel->password_changed_at = null; // force change on first login
				}
			}else{

				$gnrt_pass = $request->first_name.env('APP_NAME').date('Y');
				$personnel->password = bcrypt($gnrt_pass);
				$personnel->password_changed_at = null; // force change on first login
				// return response()->json($gnrt_pass,200);
			}
		}


	if ($request->hasFile('image')){
      $path = $request->image->path();
      $file = Storage::putFile('personnel', new File($path));
      $file = explode('/', $file);

      $fName = '/storage/personnel/'.urlencode(end($file));

      $personnel->photo = (String) $fName;
    }

    if ($request->hasFile('signature')){
      $path = $request->signature->path();
      $file = Storage::putFile('personnel-signature', new File($path));
      $file = explode('/', $file);

      $fName = '/storage/personnel-signature/'.urlencode(end($file));

		$personnel->electronic_sig = (String) $fName;
	//   return response()->json($personnel,200);
		}

		if ($request->filled('signature_data') && str_starts_with($request->signature_data, 'data:image/')) {
			if (preg_match('/^data:image\/(\w+);base64,/', $request->signature_data, $matches)) {
				$extension = strtolower($matches[1]);
				if ($extension === 'jpeg') {
					$extension = 'jpg';
				}

				if (in_array($extension, ['png', 'jpg', 'gif', 'webp'], true)) {
					$imageData = substr($request->signature_data, strpos($request->signature_data, ',') + 1);
					$decoded = base64_decode($imageData, true);
					if ($decoded !== false) {
						$filename = Str::uuid()->toString() . '_' . time() . '.' . $extension;
						$storagePath = 'personnel-signature/' . $filename;
						Storage::put($storagePath, $decoded);
						$personnel->electronic_sig = '/storage/personnel-signature/' . rawurlencode($filename);
					}
				}
			}
		}

		$personnel->save();

		// Labs assignment only (zones/directorates removed from Labs organization)
		if (Schema::hasTable('user_lab_relation')) {
			DB::table('user_lab_relation')->where('user_id', $personnel->id)->delete();
			foreach ($labIds as $labId) {
				UserLabRelation::query()->create([
					'user_id' => $personnel->id,
					'lab_id' => $labId,
				]);
			}
		}

		if (Schema::hasTable('user_zone_relation')) {
			DB::table('user_zone_relation')->where('user_id', $personnel->id)->delete();
		}

		if (Schema::hasTable('user_directorate_relation')) {
			DB::table('user_directorate_relation')->where('user_id', $personnel->id)->delete();
		}
		// return response()->json($personnel);
		if(isset($personnelWorkHistoryChanged)){
			$PWH = new PersonnelWorkHistoryController;
			$createPWH = $PWH->updateWorkHistory($personnel->id, $personnel->department_id, $personnel->position);
		}

    try{
      if(!isset($personnel->id)){
        if($request->has('main_password') && trim($request->main_password) != ""){
          $password = $request->main_password;
        }else{
          $password = $gnrt_pass;
        }
        $ip_address_link = request()->root();
        $companyDetails = getCompanyDetails();
        $message = 'We would like to welcome you to '.$companyDetails['name'].'. Please find below your Login Credentials and Link to Imara Lims:';
        $body = 'Hi '.$personnel->name.', <br><br><br>'
            .$message.'<br><br>
            App Link: <a href='.$ip_address_link.'>'.$ip_address_link.'</a> ,<br><br>
            Email: '.$personnel->email.' ,<br><br>
            Password: '.$password.' , <br>

            Regards, <br><br> '.env('APP_NAME').' ';
        $subject = '['.env('APP_NAME').'] User Credentials';

        notify_user($body,$personnel->email,$subject);
      }


      $personnel->save();
      // return response()->json($personnel,200);

      return redirect()->back()->with('success', 'User details saved.');
    }
    catch(Exception $e){
      return redirect()->back()->with('error', $e->getMessage());
    }

  }

	public function importUser(Request $request){
		$batch = BulkImportBatch::create([
			'company_id' => getUserCompany(),
			'user_id' => auth()->id(),
			'module' => 'personnel',
			'form_type' => 'user',
			'status' => 'started',
		]);

		$zoneId = $request->input('zone_id');
		Excel::import(new UserImporter($batch, $zoneId), $request->file);
		
		$batch->markAsCompleted();

		return redirect()->back()->with('success', "Import completed. {$batch->imported_rows} users processed.");
	}

	public function user_profile()
	{
		return view('layouts.personnel.users.user_profile');
	}

	public function get_personnel_via_ajax($id=false){
		if($id==false){
			$id = getUserCompany();
		}

		$personnel = User::selectRaw('id, name as text')->where('company_id', $id)->get();

		return json_encode($personnel);
	}

	/**
	 * Display locked accounts management page
	 */
	public function lockedAccounts()
	{
		$this->authorize('personnel.personnel.edit');

		return view('livewire.layout.personnel-app', [
			'componentType' => 'locked-accounts-manager',
			'pageTitle' => 'Locked Accounts Management',
		]);
	}

	/**
	 * Unlock a locked user account
	 */
	public function unlockAccount(Request $request, $id)
	{
		$this->authorize('personnel.personnel.edit');

		try {
			$user = User::findOrFail($id);

			$user->login_locked_by_admin_reset = false;
			$user->failed_login_attempts = 0;
			$user->save();

			return response()->json([
				'success' => true,
				'message' => "Account unlocked for {$user->name}",
			]);
		} catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'message' => 'Error unlocking account: ' . $e->getMessage(),
			], 500);
		}
	}

}
