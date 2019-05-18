<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableChangeOrderDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('change_order_details')) { return; }
        Schema::create('change_order_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('change_order_id')->nullable();
            $table->text('description')->nullable();
            $table->double('price', 10, 2)->nullable();
            $table->integer('user_id');
            $table->integer('orderable')->nullable()->default(0);
            $table->datetime('ordered_on')->nullable();
            $table->integer('ordered_by')->nullable()->default(0);
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
        Schema::dropIfExists('change_order_details');
    }
}
