<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTabeJobItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('job_items')) { return; }
        Schema::create('job_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('job_id');
            $table->string('instanceof', 255)->nullable();
            $table->text('reference')->nullable();
            $table->date('ordered')->nullable();
            $table->date('confirmed')->nullable();
            $table->date('received')->nullable();
            $table->date('verified')->nullable();
            $table->integer('orderable')->nullable()->default(1);
            $table->text('meta')->nullable();
            $table->string('hours', 255)->nullable();
            $table->integer('replacement')->nullable()->default(0);
            $table->text('notes')->nullable();
            $table->text('contractor_notes')->nullable();
            $table->integer('contractor_complete')->nullable()->default(0);
            $table->string('image1', 255)->nullable();
            $table->string('image2', 255)->nullable();
            $table->string('image3', 255)->nullable();
            $table->integer('po_item_id')->nullable()->default(0);
            $table->integer('group_id')->nullable()->default(0);
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
        Schema::dropIfExists('job_items');
    }
}
