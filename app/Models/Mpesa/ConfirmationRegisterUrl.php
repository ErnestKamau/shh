<?php

namespace App\Models\Mpesa;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class ConfirmationRegisterUrl extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = "mpesa_confirmation_data";
}
