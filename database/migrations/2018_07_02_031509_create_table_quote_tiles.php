<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableQuoteTiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('quote_tiles')) { return; }
        Schema::create('quote_tiles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('quote_id');
            $table->string('description', 255);
            $table->double('linear_feet_counter');
            $table->double('backsplash_height');
            $table->string('pattern', 255);
            $table->string('sealed', 255);
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
        Schema::dropIfExists('quote_tiles');
    }
}
