<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePoItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('po_items')) { return; }
        Schema::create('po_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('po_id');
            $table->integer('job_item_id');
            $table->string('item', 255)->nullable();
            $table->datetime('received')->nullable();
            $table->integer('received_by')->nullable()->default(0);
            $table->integer('user_id');
            $table->text('notes')->nullable();
            $table->integer('qty')->nullable()->default(0);
            $table->integer('punch')->nullable()->default(0);
            $table->integer('fft_id')->nullable()->default(0);
            $table->integer('service_id')->nullable()->default(0);
            $table->integer('warranty_id')->nullable()->default(0);
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
        Schema::dropIfExists('po_items');
    }
}
