<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableQuoteGranites extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('quote_granites')) { return; }
        Schema::create('quote_granites', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('quote_id');
            $table->string('description', 255)->nullable();
            $table->integer('granite_id');
            $table->string('granite_override', 255)->nullable();
            $table->double('pp_sqft')->nullable();
            $table->string('removal_type', 255)->nullable();
            $table->string('measurements', 255)->nullable();
            $table->string('counter_edge', 255)->nullable();
            $table->double('counter_edge_ft')->nullable();
            $table->double('backsplash_height')->nullable();
            $table->double('raised_bar_length')->nullable();
            $table->double('raised_bar_depth')->nullable();
            $table->double('island_width')->nullable();
            $table->double('island_length')->nullable();
            $table->string('granite_jo', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quote_granites');
    }
}
