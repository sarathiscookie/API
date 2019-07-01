<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KeyContainer extends Model
{
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $guarded = ['id'];

    /**
     * Set the key name.
     *
     * @param  string  $value
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }

    /**
    * Get the key name.
    *
    * @param  string  $value
    * @return string
    */
    public function getNameAttribute($value)
    {
        return ucwords($value);
    }

    /**
     * Get the keys for the key container.
     */
    public function keys()
    {
        return $this->hasMany('App\Key');
    }

    /**
    * Get the matching company
    */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
