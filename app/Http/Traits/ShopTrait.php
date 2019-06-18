<?php

namespace App\Http\Traits;

use App\Shop;

trait ShopTrait {
    /**
     * Get shop matching with id
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function fetchShop($id)
    {
        try {
            $shop = Shop::select('shop')
            ->active()
            ->find($id);

            return $shop;
        }
        catch(\Exception $e) {
            abort(404);
        } 
    }
}

?>