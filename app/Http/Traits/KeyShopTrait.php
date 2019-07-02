<?php

namespace App\Http\Traits;

use App\KeyShop;

trait KeyShopTrait {
    /**
     * Find shops of parent company
     * @param  int $keyContainerId
     * @param  int $keyShopId
     * @return \Illuminate\Http\Response
     */
    public function getKeyShop($keyContainerId, $keyShopId)
    {
        try {
            $keyShop = KeyShop::select('id', 'shop_id')
            ->where('key_container_id', $keyContainerId)
            ->where('shop_id', $keyShopId)
            ->first();

            return $keyShop;
        }
        catch(\Exception $e) {
            abort(404);
        } 
    }

}

?>