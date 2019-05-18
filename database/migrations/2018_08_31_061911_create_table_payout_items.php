<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePayoutItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payout_items')) { return; }
          Schema::create('payout_items', function (Blueprint $table) {
              $table->increments('id');
              $table->timestamps();
              $table->integer('payout_id');
              $table->string('item');
              $table->double('amount');
          });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payout_items');
    }
}
