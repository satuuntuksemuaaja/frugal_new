<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableJobSchedules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('job_schedules')) { return; }
        Schema::create('job_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->datetime('start')->nullable();
            $table->datetime('end')->nullable();
            $table->integer('group_id')->nullable()->default(0);
            $table->integer('user_id');
            $table->integer('job_id');
            $table->integer('complete')->nullable()->default(0);
            $table->integer('sent')->nullable()->default(0);
            $table->text('notes')->nullable();
            $table->integer('aux')->nullable()->default(0);
            $table->text('customer_notes')->nullable();
            $table->integer('default_email')->nullable()->default(0);
            $table->integer('locked')->nullable()->default(0);
            $table->text('contractor_notes')->nullable();
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
        Schema::dropIfExists('job_schedules');
    }
}
