<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class StockTaking extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;

	public function counters(){
		return User::join('stock_taking_counters as stc', 'stc.counter_id', 'users.id')
		->selectRaw('users.*')->orderBy('name', 'asc')->where('stock_taking_id', $this->id)->get();
	}

}
