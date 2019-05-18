<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads', function($t)
        {
           $t->increments('id');
           $t->timestamps();
           $t->integer('customer_id')->default(0);
           $t->integer('source_id')->default(0);
           $t->integer('user_id')->default(0);
           $t->integer('status_id')->default(0);
           $t->string('title')->nullable();
           $t->boolean('closed')->default(0);
           $t->boolean('archived')->default(0);
           $t->boolean('provided')->defualt(0);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('leads');
    }
}
