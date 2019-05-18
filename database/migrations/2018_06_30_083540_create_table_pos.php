<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('pos')) { return; }
        Schema::create('pos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number', 255)->nullable();
            $table->integer('customer_id');
            $table->string('title', 255);
            $table->integer('user_id');
            $table->string('status', 255)->nullable();
            $table->datetime('submitted')->nullable();
            $table->datetime('committed')->nullable();
            $table->integer('archived')->nullable()->default(0);
            $table->integer('vendor_id');
            $table->string('type', 255)->nullable();
            $table->integer('job_id');
            $table->string('company_invoice', 255)->nullable();
            $table->string('projected_ship', 255)->nullable();
            $table->integer('object_id')->nullable();
            $table->integer('emailed')->nullable()->default(0);
            $table->integer('parent_id')->nullable()->default(0);
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
        Schema::dropIfExists('pos');
    }
}
