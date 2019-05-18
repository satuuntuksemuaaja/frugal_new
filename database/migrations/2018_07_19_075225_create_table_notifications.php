<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('notifications')) { return; }
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('isFor', 255);
            $table->integer('reference')->nullable()->default(0);
            $table->integer('status_id')->nullable()->default(0);
            $table->integer('expiration_id')->nullable()->default(0);
            $table->datetime('set')->nullable();
            $table->datetime('expires')->nullable();
            $table->integer('followup_id')->nullable()->default(0);
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
        Schema::dropIfExists('notifications');
    }
}
