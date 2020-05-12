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
        User::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'admin',
                'email' => 'admin@gmail.com',
                'username' => 'admin',
                'password' => Hash::make('11111111'),
                'role' => 'admin',
                'active' => 'yes',
            ]
        );

        User::updateOrCreate(
            ['id' => 2],
            [
                'name' => 'marko',
                'email' => 'marko@herm.de',
                'username' => 'marko',
                'password' => Hash::make('www.herm.de'),
                'role' => 'admin',
                'active' => 'yes',
            ]
        );
    }
}
