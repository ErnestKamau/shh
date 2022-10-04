<?php

namespace App\Http\Controllers;

use App\BatchComment;
use Illuminate\Http\Request;

class BatchCommentController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
	}

	public function add(Request $request){

		$comment = new BatchComment;
		$comment->created_by = \Auth::user()->id;
		$comment->reminder_for = $request->user_id;
		$comment->personnel_to_cc = $request->has('followers') ? implode(",", $request->followers) : 0;
		$comment->comments = $request->message;
		$comment->comment_type = $request->type;
		$comment->sample_header_id = $request->batch_id;

		$comment->save();
		$companyDetails = getCompanyDetails();
		$batch = getSampleHeaderByID($request->batch_id);
		if($comment->personnel_to_cc !=0){
			$contacts = explode(',',$comment->personnel_to_cc);
			array_push($contacts,$comment->reminder_for);
			foreach($contacts as $contact){
				$user = getUserById((int)$contact);
				$message = 'There is a new note for batch '.$batch->batch_code.'. Kindly review the notes.';
				$body = 'Hi '.$user->name.',<br><br>'
						.$message.'<br>
						Regards, <br><br>'
						.$companyDetails['name'].' ';
				$subject = '['.$companyDetails['name'].'] Batch Notes Notification';
				$notify = notify_user($body,$user->email,$subject);

			}
		}else{
			$user = getUserById($comment->reminder_for);
			$message = 'There is a new note for batch '.$batch->batch_code.'. Kindly review the notes.';
			$body = 'Hi '.$user->name.',<br><br>'
					.$message.'<br>
					Regards, <br><br>'
					.$companyDetails['name'].' ';
			$subject = '['.$companyDetails['name'].'] Batch Notes Notification';
			$notify = notify_user($body,$user->email,$subject);
		}

		//TODO: Send emails

    return redirect()->back()->with('success', 'Batch Comment Added.');
	}

	public function edit(Request $request, $id){
		$comment = BatchComment::find($id);
		$comment->created_by = \Auth::user()->id;
		$comment->reminder_for = $request->user_id;
		$comment->personnel_to_cc =  $request->has('followers') ? implode(",", $request->followers) : 0;
		$comment->comments = $request->message;
		$comment->comment_type = $request->type;
		$comment->sample_header_id = $request->batch_id;

		if($request->has('complete')){
			$comment->completed_at = date('Y-m-d H:i:s');
		}

		$comment->save();

		//TODO: Send emails

    return redirect()->back()->with('success', 'Batch Comment Edited.');
	}
}
