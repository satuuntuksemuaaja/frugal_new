<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function($t)
        {
           $t->increments('id');
           $t->timestamps();
           $t->string('setting');
           $t->string('name');
           $t->text('value')->nullable();
           $t->string('description');
           $t->string('plugin');
           $t->string('meta')->nullable();
           $t->string('type')->default('text');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('settings');
    }
}
