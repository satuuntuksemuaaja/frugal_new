<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAddons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addons', function ($t) {
            $t->increments('id');
            $t->timestamps();
            $t->string('item');
            $t->double('price');
            $t->boolean('active');
            $t->boolean('automatic');
            $t->string('contract');
            $t->integer('group_id');

            $t->index('group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addons');
    }
}
