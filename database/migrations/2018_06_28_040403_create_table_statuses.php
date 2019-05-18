<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('statuses')) { return; }
        Schema::create('statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('stage_id');
            $table->string('name', 255);
            $table->integer('active')->default(1);
            $table->integer('followup_status')->nullable()->default(0);
            $table->integer('followup_expiration')->nullable()->default(0);
            $table->integer('followup_lock')->nullable()->default(0);

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
        Schema::dropIfExists('statuses');
    }
}
