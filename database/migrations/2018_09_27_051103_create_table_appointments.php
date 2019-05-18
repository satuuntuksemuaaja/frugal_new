<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAppointments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('appointments')) { return; }
          Schema::create('appointments', function (Blueprint $table) {
              $table->increments('id');
              $table->timestamps();
              $table->integer('lead_id');
              $table->integer('user_id')->nullable();
              $table->datetime('scheduled')->nullable();
              $table->string('location')->nullable();
              $table->string('type');
          });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}
