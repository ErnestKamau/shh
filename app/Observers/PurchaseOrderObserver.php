<?php

namespace App\Observers;

use App\Http\Controllers\ZohoController;
use App\RequestEntity;
use App\RequestType;
use Exception;

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
        $rfq = RequestEntity::find($requestEntity->parent_request_id);
        
    }

    /**
     * Handle the request entity "updated" event.
     *
     * @param  \App\RequestEntity  $requestEntity
     * @return void
     */
    public function updated(RequestEntity $requestEntity)
    {
        // Zoho Books integration disabled — not in use.
        /*
        if ($requestEntity->request_type == "Purchase Orders" && trim($requestEntity->status) != "In Preparation") {
            if (
                ! \Illuminate\Support\Facades\Schema::hasColumn('request_entities', 'zoho_id')
                || trim((string) ($requestEntity->zoho_id ?? '')) !== ''
            ) {
                return;
            }

            $errors = [];
            try {
                $zoho = new ZohoController();
                $msg = $zoho->createPurchaseOrder($requestEntity);
                if (isset($msg['error'])) {
                    $errors[] = $msg['error'];
                }
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }

            if (count($errors) > 0) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('request_entities', 'errors')) {
                    $requestEntity->errors = json_encode($errors);
                    $requestEntity->save();
                }
            } else {
                $zoho->attachQuote($requestEntity);
                if (\Illuminate\Support\Facades\Schema::hasColumn('request_entities', 'errors')) {
                    $requestEntity->errors = null;
                    $requestEntity->save();
                }
            }
        }
        */
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
