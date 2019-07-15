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
        $data = [
        	['name' => 'Rakuten', 'created_at' => Carbon::now()],
        	['name' => 'Amazone', 'created_at' => Carbon::now()],
        	['name' => 'Ebay', 'created_at' => Carbon::now()]
        ];

        Shopname::insert($data);
    }
}
