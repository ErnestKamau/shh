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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Excel;
use App\Imports\StandardsImport;
use App\SampleAnalysisStage;

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
		$license_count = $this->users_by_license();
		$stages = SampleAnalysisStage::where('active', 1)->get();

		return view('livewire.layout.personnel-app', [
			'componentType' => 'personnel-dashboard',
			'pageTitle' => 'Personnel Dashboard',
			'license_count' => $license_count,
			'stages' => $stages,
		]);
	}

	public function personnel_list()
	{
		$license_count = $this->users_by_license();
		$stages = SampleAnalysisStage::where('active', 1)->get();

		return view('livewire.layout.personnel-app', [
			'componentType' => 'personnel-list',
			'pageTitle' => 'Personnel List',
			'license_count' => $license_count,
			'stages' => $stages,
		]);
	}

	public function users_by_license(){
		$users = User::where('company_id', getUserCompany())->get();

		$license_count = array();

		foreach($users as $u){
			if(!isset($license_count[$u->license_type])){
				$license_count[$u->license_type] = 0;
			}

			$license_count[$u->license_type]++;
		}

		return $license_count;
	}

  public function show_personnel($id)
  {
		return view('livewire.layout.personnel-app', [
			'componentType' => 'personnel-detail',
			'pageTitle' => 'Personnel Profile',
			'userId' => (int) $id,
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
		if ($request->has('password','con_password')){
			if ($request->password != $request->con_password){
				return redirect()->back()->with('error' , 'Password did not match.');

			}else{
				$user->password = bcrypt($request->password);
				$user->save();

				$body = 'Hi '.$user->first_name.',<br><br>
						Your password has been reset successfully.Your new password is:
						<br><br>'.$request->password ;
				$mailData = array(
					'contacts'=> array($user->email),
					'body'=>$body,
					'subject'=> '[Imara-Lims Password Reset - '.$user->first_name.']',

				);
				$mailer = new  Mailers;
				$sendmail = $mailer->html_email($mailData,'default');

				return redirect()->back()->with('sucess','Password reset successfully');
			}
		}else{
			return redirect()->back()->with('error','No user with specified ID');
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

			return redirect()->back()->with('sucess','Personel state updated successfully');
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
			'zone_id' => 'nullable|integer|exists:zones,id',
		]);
		
		$personnel = User::find($id) ?? new User();
		$check_user = User::where('email',$request->email)->get();

		if(!isset($personnel->email) && $check_user->count() > 0){
			return redirect()->back()->with('error','There is a user with the specified email!');
		}

		$license_count = $this->users_by_license();

		if(isset($personnel->department_id) && $personnel->department_id != $request->department){
			$personnelWorkHistoryChanged = true;
		}

		if(isset($personnel->position) && $personnel->position != $request->position){
			$personnelWorkHistoryChanged = true;
		}

		if(!isset($personnel->id)){
			$personnelWorkHistoryChanged = true;
		}

		// if(!isset($personnel->id)){
		// 	$personnelWorkHistoryChanged = true;
		// 	if($license_count[$request->user_license] >= mamboSawa($request->user_license.'s')){
		// 		return redirect()->back()->with('success',
		// 			'No '.($request->user_license == 'shared_user' ? 'Shared' : 'Named').' User licenses available.');
		// 	}
		// }
		// else{
		// 	if($personnel->license_type != $request->user_license){
		// 		if($license_count[$request->user_license] >= mamboSawa($request->user_license.'s')){
		// 			return redirect()->back()->with('success',
		// 				'No '.($request->user_license == 'shared_user' ? 'Shared' : 'Named').' User licenses available.');
		// 		}
		// 	}
		// }


		$personnel->first_name = $request->first_name;
		$personnel->middle_name = $request->middle_name;
		$personnel->last_name = $request->last_name;
		$personnel->name = $request->first_name." ".$request->middle_name." ".$request->last_name;
		$personnel->email = $request->email;
		$personnel->phone = $request->phone;
		$personnel->company_id = getUserCompany();
		$personnel->location_id = getCurrentUserLocation()->id;
		$personnel->department_id = $request->department;
		$personnel->designation = $request->designation;
		$personnel->position = $request->position;
		$personnel->education_level = $request->educational_level;
		$personnel->employment_date = $request->employment_date;
		$personnel->date_of_birth = $request->date_of_birth;
		$personnel->id_number = $request->id_number;
		$personnel->nssf = $request->nssf;
		$personnel->nhif = $request->nhif;
		$personnel->kra_pin = $request->kra_pin;
		$personnel->active = $request->active ?? 0;
		$personnel->lab_section_id = implode(',',$request->lab_section_id ?? []) ?? '';

		$personnel->license_type = $request->user_license;
		if (Schema::hasColumn('users', 'zone_id')) {
			$personnel->zone_id = $request->zone_id ? (int) $request->zone_id : null;
		}

		if(!isset($personnel->id)){

			if(isset($request->has_credentials) && $request->has_credentials == '1'){

				if($request->has('main_password') && trim($request->main_password) != ""){
					$personnel->password = bcrypt($request->main_password);
				}
			}else{

				$gnrt_pass = $request->first_name.env('APP_NAME').date('Y');
				$personnel->password = bcrypt($gnrt_pass);
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

		$personnel->save();
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
		Excel::import( new StandardsImport,$request->file);
		return redirect()->back()->with('success','import successfully');
	}

	public function user_profile(){
		$user = Auth::user();
		$license_count = $this->users_by_license();
		$stages = SampleAnalysisStage::where('active',1)->get();
		return view('layouts.personnel.users.user_profile', compact('user','license_count','stages'));
	}

	public function get_personnel_via_ajax($id=false){
		if($id==false){
			$id = getUserCompany();
		}

		$personnel = User::selectRaw('id, name as text')->where('company_id', $id)->get();

		return json_encode($personnel);
	}

}
