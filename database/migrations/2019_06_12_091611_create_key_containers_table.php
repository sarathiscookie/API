<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeyContainersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('key_containers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->string('container', 50);
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('company_id');
            $table->string('type', 15);
            $table->integer('activation_number'); // Eg 20. Eack key can activate 20 times.
            $table->integer('count'); // Count of keys: 5
            $table->integer('total_activation'); //Total is activation_numner (20) * count (5) = 100
            $table->integer('total_available'); // How many keys are available.
            $table->integer('total_used')->nullable(); // How many keys are already sold.
            $table->integer('total_used_support_check')->nullable(); //If user complaint that key is not working, then support will test and if the key is working then move to user_ok otherwise used_not_ok.
            $table->integer('total_used_ok')->nullable();
            $table->integer('total_used_not_ok')->nullable();
            $table->enum('active', ['yes', 'no'])->default('no');
            $table->timestamps();

            $table->foreign('shop_id')
            ->references('id')->on('shops');

            $table->foreign('company_id')
            ->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keys');
    }
}
