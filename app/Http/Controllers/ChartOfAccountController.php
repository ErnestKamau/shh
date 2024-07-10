<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function synchronize()
    {
        $zoho = new ZohoController();

        $accounts = $zoho->sync_zoho_things("chartofaccounts");
        $arr = [];

        $accounts = json_decode($accounts);

        foreach($accounts as $acc){
            $arr[] = [
                "name" => $acc->account_name,
                "account_id" => $acc->account_id,
                "type" => $acc->account_type
            ];
        }     
        ChartOfAccount::insert($arr);
        return response()->json($accounts);
    }
}
