<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHardwaresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hardwares', function ($t) {
            $t->increments('id');
            $t->timestamps();
            $t->string('sku');
            $t->text('description');
            $t->integer('vendor_id');
            $t->double('price');
            $t->boolean('active')->default(0);
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
        Schema::dropIfExists('hardwares');
    }
}
