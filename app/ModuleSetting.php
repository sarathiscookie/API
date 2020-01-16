<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModuleSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
    * Scope for shop status and company status.
    *
    * @param  string  $query
    * @return string
    */
    public function scopeJoinActive($query)
    {
        return $query->where('modules.active', 'yes');
    }
}
