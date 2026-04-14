<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class UserRoleView extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table ="user_roles_view";
}
