<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableChangeOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('change_orders')) { return; }
        Schema::create('change_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('job_id')->nullable();
            $table->integer('user_id');
            $table->text('signature')->nullable();
            $table->datetime('signed_on')->nullable();
            $table->integer('signed')->default(0);
            $table->integer('billed')->default(0);
            $table->integer('closed')->default(0);
            $table->integer('sent')->nullable()->default(0);
            $table->datetime('sent_on')->nullable();
            $table->integer('declined')->default(0);
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
        Schema::dropIfExists('change_orders');
    }
}
