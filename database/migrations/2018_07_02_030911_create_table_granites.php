<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableGranites extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('granites')) { return; }
        Schema::create('granites', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->double('price', 10, 2);
            $table->double('removal_price', 10, 2);
            $table->integer('active');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('granites');
    }
}
