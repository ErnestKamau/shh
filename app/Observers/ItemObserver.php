<?php

namespace App\Observers;

use App\Http\Controllers\ZohoController;
use App\InventorySubCategories;

class ItemObserver
{
    /**
     * Handle the inventory item "created" event.
     *
     * @param  \App\InventoryItem  $inventoryItem
     * @return void
     */
    public function created(InventorySubCategories $inventoryItem)
    {
        $zoho = new ZohoController();
        if(empty($inventoryItem->zoho_item_code)){
            // $zoho->createZohoItem($inventoryItem);
        }
    }

    /**
     * Handle the inventory item "updated" event.
     *
     * @param  \App\InventoryItem  $inventoryItem
     * @return void
     */
    public function updated(InventorySubCategories $inventoryItem)
    {
        //
    }

    /**
     * Handle the inventory item "deleted" event.
     *
     * @param  \App\InventoryItem  $inventoryItem
     * @return void
     */
    public function deleted(InventorySubCategories $inventoryItem)
    {
        //
    }

    /**
     * Handle the inventory item "restored" event.
     *
     * @param  \App\InventoryItem  $inventoryItem
     * @return void
     */
    public function restored(InventorySubCategories $inventoryItem)
    {
        //
    }

    /**
     * Handle the inventory item "force deleted" event.
     *
     * @param  \App\InventoryItem  $inventoryItem
     * @return void
     */
    public function forceDeleted(InventorySubCategories $inventoryItem)
    {
        //
    }
}
