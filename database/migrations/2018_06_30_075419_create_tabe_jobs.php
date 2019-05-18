<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTabeJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('jobs')) { return; }
        Schema::create('jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('quote_id');
            $table->date('contract_date')->nullable();
            $table->date('start_date')->nullable();
            $table->integer('closed')->default(0);
            $table->datetime('closed_on')->nullable();
            $table->text('meta');
            $table->integer('paid')->default(0);
            $table->integer('locked')->default(0);
            $table->integer('has_money')->default(0);
            $table->integer('construction')->default(0);
            $table->integer('schedules_sent')->default(0);
            $table->integer('schedules_confirmed')->default(0);
            $table->integer('built')->default(0);
            $table->integer('loaded')->default(0);
            $table->datetime('schedule_sent_on')->nullable();
            $table->integer('truck_left')->default(0);
            $table->integer('reviewed')->default(0);
            $table->string('payout_additionals', 255)->nullable();
            $table->integer('sent_cabinet_arrival')->nullable();
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
        Schema::dropIfExists('jobs');
    }
}
