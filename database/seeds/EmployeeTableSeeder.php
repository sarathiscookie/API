<?php

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $data = [
            'name' => 'Employee',
            'email' => 'employee@gmail.com',
            'password' => Hash::make('11111111'),
            'created_at' => Carbon::now(),
            'role' => 'employee',
            'active' => 'yes',
        ];

        User::insert($data);
    }
}
