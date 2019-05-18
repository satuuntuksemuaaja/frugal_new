<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePromotions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('promotions')) { return; }
      Schema::create('promotions', function (Blueprint $table) {
          $table->increments('id');
          $table->string('name', 255);
          $table->integer('active')->default(1);
          $table->string('modifier', 255);
          $table->string('condition', 255);
          $table->double('qualifier', 10, 2)->default(0);
          $table->double('discount_amount', 10, 2)->default(0);
          $table->string('verbiage', 1024);
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
        Schema::dropIfExists('promotions');
    }
}
