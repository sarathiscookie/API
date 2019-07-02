<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KeyShop extends Model
{
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $guarded = ['id'];

    /**
    * Get the matching shop
    */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
