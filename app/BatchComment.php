<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchComment extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;

	public $with = ['creator', 'reminderRecipient'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
	}

    /**
     * User this note is addressed TO.
     */
    public function reminderRecipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reminder_for');
	}

    /**
     * Backwards-compatible accessor for legacy code calling reminder_for().
     */
    public function reminder_for()
    {
        return $this->reminderRecipient;
	}

  public function people_to_cc(){
		$people = explode(",",$this->personnel_to_cc);
		$users = User::whereIn('id', $people)->get();

		$response = array("ids"=>array(), "names"=>array());

		foreach($users as $u){
			$response['ids'][] = $u->id;
			$response['names'][] = $u->name;
		}

		return $response;
	}
}
