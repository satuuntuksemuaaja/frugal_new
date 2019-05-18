<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFfts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('ffts')) { return; }
        Schema::create('ffts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('job_id');
            $table->integer('warranty')->nullable()->default(0);
            $table->text('notes')->nullable();
            $table->integer('closed')->nullable()->default(0);
            $table->datetime('schedule_start')->nullable();
            $table->datetime('schedule_end')->nullable();
            $table->datetime('pre_schedule_start')->nullable();
            $table->datetime('pre_schedule_end')->nullable();
            $table->integer('pre_assigned')->nullable()->default(0);
            $table->datetime('signed')->nullable();
            $table->text('signature')->nullable();
            $table->string('hours', 255)->nullable();
            $table->integer('customer_id');
            $table->integer('payment')->nullable()->default(0);
            $table->integer('ordered_email')->nullable()->default(0);
            $table->text('signoff')->nullable();
            $table->timestamp('signoff_stamp')->nullable();
            $table->text('warranty_notes')->nullable();
            $table->integer('paid')->nullable()->default(0);
            $table->text('paid_reason')->nullable();
            $table->integer('punch_reminder_emailed')->nullable()->default(0);
            $table->integer('service')->nullable()->default(0);
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
        Schema::dropIfExists('ffts');
    }
}
