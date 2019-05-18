<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RebuildCustomerTableWithNullables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('customers');

        Schema::create('customers', function($t)
        {

            $t->increments('id');
            $t->timestamps();
            $t->string('name');
            $t->string('address');
            $t->string('city');
            $t->string('state');
            $t->string('zip');
            $t->string('email');
            $t->boolean('active')->default(1);
            $t->string('job_address')->nullable();
            $t->string('job_city')->nullable();
            $t->string('job_state')->nullable();
            $t->string('job_zip')->nullable();
            $t->bigInteger('mobile')->nullable();
            $t->bigInteger('home')->nullable();
            $t->bigInteger('alternate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
