<?php

namespace App\Http\Traits;

use App\Shopname;

trait ShopnameTrait {

    /**
     * Get all shop names
     * @return \Illuminate\Http\Response
     */
	public function shopNames()
	{
		try {
            $shopNames = Shopname::get();
            return $shopNames;
        }
        catch(\Exception $e) {
            abort(404);
        } 
	}

    /**
     * Get all shop name
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function shopName($id)
    {
        try {
            $shopName = Shopname::select('name')->find($id);
            return $shopName;
        }
        catch(\Exception $e) {
            abort(404);
        } 
    }
}

?>