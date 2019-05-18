<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveGranites extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('granites');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('granites', function($t)
        {
           $t->increments('id');
           $t->timestamps();
           $t->string('name');
           $t->double('price');
           $t->double('removal_price')->default(0);
           $t->boolean('active')->default(1);
        });
    }
}
