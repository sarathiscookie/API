<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeyCountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('key_counts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('shop_id');
            $table->integer('total_keys');
            $table->integer('available_keys');
            $table->integer('used_keys');
            $table->integer('used_ok');
            $table->integer('used_nok');
            $table->integer('used_support');
            $table->timestamps();

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
        Schema::dropIfExists('key_counts');
    }
}
