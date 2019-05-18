<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function ($t) {
            $t->increments('id');
            $t->timestamps();
            $t->string('name');
            $t->string('address');
            $t->string('city');
            $t->string('state');
            $t->string('zip');
            $t->boolean('archived');
            $t->timestamp('deleted_at')->nullable();
            $t->string('job_address');
            $t->string('job_city');
            $t->string('job_state');
            $t->string('job_zip');
            $t->string('email');
            $t->bigInteger('mobile');
            $t->bigInteger('home');
            $t->bigInteger('alternate');
            $t->bigInteger('primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
