<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCabinets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cabinets', function ($t) {
            $t->increments('id');
            $t->timestamps();
            $t->string('frugal_name');
            $t->string('name');
            $t->double('price');
            $t->integer('vendor_id');
            $t->boolean('active')->default(0);
            $t->text('description');
            $t->string('image')->nullable();

            $t->index('vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cabinets');
    }
}
