<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    //protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function authenticated()
    {
        if(auth()->user()->user_type == 'admin')
        {
            return redirect('/admin/dashboard');
        } 
        elseif(auth()->user()->user_type == 'employee')
        {
            return redirect('/employee/dashboard');
        } 
        elseif(auth()->user()->user_type == 'manager')
        {
            return redirect('/manager/dashboard');
        } 
        else {
            return redirect('/')->with('error', 'You have not permission to access.');
        }
    }


    /*public function redirectTo() {
        $user = Auth::user()->user_type;
        switch(true) {
            case 'admin':
            return '/admin/dashboard';
            case 'manager':
            return '/manager/dashboard';
            case 'employee':
            return '/employee/dashboard';
            default:
            return '/';
        }
    }*/
}
