<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableQuoteAppliances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('quote_appliances')) { return; }
        Schema::create('quote_appliances', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('quote_id');
            $table->integer('appliance_id');
            $table->string('brand', 255);
            $table->string('model', 255);
            $table->string('size', 255);
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
        Schema::dropIfExists('quote_appliances');
    }
}
