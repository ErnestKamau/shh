<?php

namespace App\Observers;

use App\Http\Controllers\ZohoController;
use App\Supplier;

class SupplierObserver
{
    /**
     * Handle the supplier "created" event.
     *
     * @param  \App\Supplier  $supplier
     * @return void
     */
    public function created(Supplier $supplier)
    {
        $zoho = new ZohoController();
        if(empty($supplier->zoho_supplier_id)){
            $zoho->createZohoSupplier($supplier);
        }
    }

    /**
     * Handle the supplier "updated" event.
     *
     * @param  \App\Supplier  $supplier
     * @return void
     */
    public function updated(Supplier $supplier)
    {
        //
    }

    /**
     * Handle the supplier "deleted" event.
     *
     * @param  \App\Supplier  $supplier
     * @return void
     */
    public function deleted(Supplier $supplier)
    {
        //
    }

    /**
     * Handle the supplier "restored" event.
     *
     * @param  \App\Supplier  $supplier
     * @return void
     */
    public function restored(Supplier $supplier)
    {
        //
    }

    /**
     * Handle the supplier "force deleted" event.
     *
     * @param  \App\Supplier  $supplier
     * @return void
     */
    public function forceDeleted(Supplier $supplier)
    {
        //
    }
}
