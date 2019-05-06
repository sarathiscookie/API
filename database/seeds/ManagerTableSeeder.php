<?php

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ManagerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            'name' => 'Manager',
            'email' => 'manager@gmail.com',
            'password' => Hash::make('11111111'),
            'created_at' => Carbon::now(),
            'role' => 'manager',
            'active' => 'yes',
        ];

        User::insert($data);
    }
}
