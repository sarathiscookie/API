<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModuleSetting extends Model
{
    // Defining module id.
    CONST MODULE_ID = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
    * Scope for module setting status.
    *
    * @param  object  $query
    * @return string
    */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Get the crons that owns the module settings.
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function crons()
    {
        return $this->hasMany('App\Cron', 'module_setting_id');
    }

    /**
     * Get the supplier record associated with the module setting.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supplier()
    {
        return $this->belongsTo('App\User', 'user_supplier_id');
    }

    /**
     * Get the products record associated with the mosule setting.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id');
    }

    /**
     * Condition to fetch module settings matching with product id (from api) and module is 1.
     *
     * @param  integer  $productId
     * @return string
     */
    public static function byProductId(int $productId): ? ModuleSetting
    {
        return self::where("product_id", $productId)
                    ->where('module_id', self::MODULE_ID)
                    ->first();
    }
    
}
