<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuoteCountertops extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quote_countertops', function ($t) {
            $t->increments('id');
            $t->timestamps();
            $t->integer('quote_id');
            $t->string('description')->nullable();
            $t->integer('countertop_id');
            $t->string('granite_override')->nullable();
            $t->double('pp_sqft');
            $t->string('removal_type')->nullable();
            $t->string('measurements');
            $t->string('counter_edge');
            $t->double('counter_edge_ft');
            $t->double('backsplash_height')->default(0);
            $t->double('raised_bar_length')->default(0);
            $t->double('raised_bar_depth')->default(0);
            $t->double('island_width')->default(0);
            $t->double('island_length')->default(0);
            $t->string('countertop_jo')->nullable();

            $t->index('quote_id');
            $t->index('countertop_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quote_countertops');
    }
}
