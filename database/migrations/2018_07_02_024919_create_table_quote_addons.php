<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableQuoteAddons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('quote_addons')) { return; }
        Schema::create('quote_addons', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('quote_id');
            $table->integer('addon_id');
            $table->double('price');
            $table->double('qty');
            $table->text('description')->nullable();
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
        Schema::dropIfExists('quote_addons');
    }
}
