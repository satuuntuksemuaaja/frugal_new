<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePayouts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('payouts')) { return; }
          Schema::create('payouts', function (Blueprint $table) {
              $table->increments('id');
              $table->timestamps();
              $table->integer('user_id');
              $table->integer('job_id');
              $table->boolean('paid')->default(0);
              $table->boolean('archived')->default(0);
              $table->boolean('approved')->default(0);
              $table->timestamp('paid_on')->nullable();
              $table->text('notes')->nullable();
              $table->string('check')->nullable();
              $table->string('invoice')->nullable();
              $table->double('total');
              $table->integer('group_id');
          });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payouts');
    }
}
