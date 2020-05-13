<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cron extends Model
{
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $guarded = ['id'];

    /**
     * Get the module settings record associated with the cron.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function moduleSetting()
    {
        return $this->belongsTo('App\ModuleSetting', 'module_setting_id');
    }
}
