<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableQuoteCabinets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('quote_cabinets')) { return; }
        Schema::create('quote_cabinets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('quote_id');
            $table->text('data');
            $table->text('override');
            $table->string('location', 255);
            $table->integer('measure');
            $table->string('color', 255);
            $table->integer('cabinet_id');
            $table->string('name', 255);
            $table->string('inches', 255);
            $table->double('price', 10, 2)->default(0);
            $table->string('delivery', 255);
            $table->text('wood_xml', 255);
            $table->string('description', 255);
            $table->integer('are_we_removing_cabinets')->default(0);
            $table->softDeletes();
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
        Schema::dropIfExists('quote_cabinets');
    }
}
