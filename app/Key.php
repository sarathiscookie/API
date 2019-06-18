<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Key extends Model
{
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $guarded = ['id'];

    /**
     * Set the key category.
     *
     * @param  string  $value
     * @return void
     */
    public function setCategoryAttribute($value)
    {
        $this->attributes['category'] = strtolower($value);
    }

    /**
    * Get the key category.
    *
    * @param  string  $value
    * @return string
    */
    public function getCategoryAttribute($value)
    {
        return ucwords($value);
    }
}
