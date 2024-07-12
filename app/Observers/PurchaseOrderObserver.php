<?php

namespace App\Observers;

use App\Http\Controllers\ZohoController;
use App\RequestEntity;

class PurchaseOrderObserver
{
    /**
     * Handle the request entity "created" event.
     *
     * @param  \App\RequestEntity  $requestEntity
     * @return void
     */
    public function created(RequestEntity $requestEntity)
    {
        //
    }

    /**
     * Handle the request entity "updated" event.
     *
     * @param  \App\RequestEntity  $requestEntity
     * @return void
     */
    public function updated(RequestEntity $requestEntity)
    {
        if($requestEntity->request_type == "Purchase Orders"){
            if(trim($requestEntity->zoho_id) == ""){
                $zoho = new ZohoController();
                $zoho->createPurchaseOrder($requestEntity);
            }
        }
    }

    /**
     * Handle the request entity "deleted" event.
     *
     * @param  \App\RequestEntity  $requestEntity
     * @return void
     */
    public function deleted(RequestEntity $requestEntity)
    {
        //
    }

    /**
     * Handle the request entity "restored" event.
     *
     * @param  \App\RequestEntity  $requestEntity
     * @return void
     */
    public function restored(RequestEntity $requestEntity)
    {
        //
    }

    /**
     * Handle the request entity "force deleted" event.
     *
     * @param  \App\RequestEntity  $requestEntity
     * @return void
     */
    public function forceDeleted(RequestEntity $requestEntity)
    {
        //
    }
}
