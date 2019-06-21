<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('key_container_id');
            $table->string('key');
            $table->tinyInteger('available'); // 1 => not available, 0 => available
            $table->tinyInteger('used')->nullable(); // How many keys are already sold. // 1 => sold, 0 => not sold
            $table->tinyInteger('used_support_check')->nullable(); // If user complaint that key is not working, then support will test and if the key is working then used status will be ok otherwise not_ok. // 1 => checked, 0 => not checked
            $table->tinyInteger('used_status')->nullable(); // 1 => key is working, 0 => key is not working
            $table->timestamps();

            $table->foreign('key_container_id')
            ->references('id')->on('key_containers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('key_instructions');
    }
}
