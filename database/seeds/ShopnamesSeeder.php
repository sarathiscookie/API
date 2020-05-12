<?php


use App\Shopname;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ShopnamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Shopname::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Rakuten',
            ]
        );

        Shopname::updateOrCreate(
            ['id' => 2],
            [
                'name' => 'Amazone',
            ]
        );

        Shopname::updateOrCreate(
            ['id' => 3],
            [
                'name' => 'Ebay',
            ]
        );
    }
}
