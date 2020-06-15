<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the products record associated with the mosule setting.
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function moduleSettings()
    {
        return $this->hasMany('App\ModuleSetting', 'product_id');
    }

    /**
     * Scope a query to only include api product id associated with product.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  integer  $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfWhereApiProductId($query, $id)
    {
       return $query->where('api_product_id', $id);
    }
}