<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableShopCabinets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('shop_cabinets')) { return; }
        Schema::create('shop_cabinets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('quote_cabinet_id')->nullable();
            $table->integer('shop_id');
            $table->text('notes')->nullable();
            $table->timestamp('approved')->nullable();
            $table->timestamp('started')->nullable();
            $table->timestamp('completed')->nullable();
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
        Schema::dropIfExists('shop_cabinets');
    }
}
