<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeyShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('key_shops', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('key_container_id');
            $table->unsignedBigInteger('shop_id');
            $table->timestamps();

            $table->foreign('key_container_id')
            ->references('id')->on('key_containers');

            $table->foreign('shop_id')
            ->references('id')->on('shops');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('key_shops');
    }
}
