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
            $shop = Shop::select('shopname_id')
            ->active()
            ->find($id);

            return $shop;
        }
        catch(\Exception $e) {
            abort(404);
        } 
    }

    /**
     * Find shops of parent company
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function getShops($id)
    {
        try {
            $shop = Shop::join('shopnames', 'shops.shopname_id', '=', 'shopnames.id')
            ->select('shops.id', 'shopnames.name AS shop')
            ->where('shops.company_id', $id)
            ->joinactive()
            ->get();

            return $shop;
        }
        catch(\Exception $e) {
            abort(404);
        } 
    }

    /**
     * Find shop name for container
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function getShopsName($id)
    {
        try {
            $shops = Shop::join('key_shops', 'shops.id', '=', 'key_shops.shop_id')
            ->select('shops.shopname_id')
            ->where('key_shops.key_container_id', $id)
            ->joinactive()
            ->get();

            return $shops;
        }
        catch(\Exception $e) {
            abort(404);
        } 
    }

}

?>