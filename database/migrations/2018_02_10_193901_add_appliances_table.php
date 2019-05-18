<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAppliancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appliances', function($t)
        {
           $t->increments('id');
           $t->timestamps();
           $t->string('name');
           $t->double('price');
           $t->integer('count_as')->default(1);
           $t->integer('group_id')->default(0);
           $t->boolean('active')->default(1);

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
        Schema::drop('appliances');
    }
}
