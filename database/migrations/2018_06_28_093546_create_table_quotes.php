<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableQuotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('quotes')) { return; }
      Schema::create('quotes', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('accepted')->default(0);
          $table->integer('final')->default(0);
          $table->text('meta');
          $table->integer('quote_type_id');
          $table->integer('lead_id');
          $table->integer('closed')->default(0);
          $table->integer('suspended')->default(0);
          $table->double('price', 10, 2)->default(0);
          $table->string('title', 255);
          $table->integer('paperwork')->default(0);
          $table->double('finance_total', 10, 2)->default(0);
          $table->double('for_designer', 10, 2)->default(0);
          $table->double('markup', 10, 2)->default(0);
          $table->string('picking_slab', 255);
          $table->integer('picked_slab')->default(0);
          $table->integer('promotion_id')->default(0);
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
        Schema::dropIfExists('quotes');
    }
}
