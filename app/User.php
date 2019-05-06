<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Setting user type is admin
     * @return string
     */
    public function isAdmin() 
    {
        return $this->role = 'admin';
    }

    /**
     * Setting user type is manager
     * @return string
     */
    public function isManager()
    {
        return $this->role = 'manager';
    }

    /**
     * Setting user type is employee
     * @return string
     */
    public function isEmployee()
    {
        return $this->role = 'employee';
    }
}
