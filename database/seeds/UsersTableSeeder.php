<?php

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
        	'name' => 'Admin',
        	'email' => 'admin@gmail.com',
        	'password' => Hash::make('11111111'),
        	'created_at' => Carbon::now(),
        	'user_type' => 'admin',
        	'active' => 'yes',
        ];

        User::insert($data);
    }
}
