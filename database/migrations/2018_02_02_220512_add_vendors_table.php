<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendors', function($t)
        {
           $t->increments('id');
           $t->timestamps();
           $t->string('name');
           $t->integer('shipping_days')->default(0);
           $t->double('multiplier')->default(0.00);
           $t->double('freight')->default(0.00);
           $t->double('build_up')->default(0.00);
           $t->text('colors', 2048)->nullable();
           $t->boolean('active');
           $t->boolean('wood_products')->default(0);
           $t->integer('confirmation_days')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('vendors');
    }
}
