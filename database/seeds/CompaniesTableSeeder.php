<?php

use App\Company;
use Illuminate\Database\Seeder;

class CompaniesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Company::updateOrCreate(
            ['id' => 1],
            [
                'company' => 'O.s.t. Ocean Sun Templin Ug',
                'country_id' => 83,
                'active' => 'yes',
            ]
        );

        Company::updateOrCreate(
            ['id' => 2],
            [
                'company' => 'O.s.t. Multishop1',
                'country_id' => 83,
                'active' => 'yes',
            ]
        );

        Company::updateOrCreate(
            ['id' => 3],
            [
                'company' => 'Benjamin Shop',
                'country_id' => 83,
                'active' => 'yes',
            ]
        );
    }
}