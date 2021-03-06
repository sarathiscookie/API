<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CountriesTableSeeder::class);
    	$this->call(UsersTableSeeder::class);
        $this->call(ShopnamesSeeder::class);
        $this->call(ModulesTableSeeder::class);
        $this->call(CompaniesTableSeeder::class);
        $this->call(ShopsTableSeeder::class);
    }
}
