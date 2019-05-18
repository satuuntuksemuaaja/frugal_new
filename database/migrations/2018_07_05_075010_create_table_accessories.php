<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAccessories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('accessories')) { return; }
          Schema::create('accessories', function (Blueprint $table) {
              $table->increments('id');
              $table->string('sku', 255)->nullable();
              $table->text('description')->nullable();
              $table->string('name', 255)->nullable();
              $table->double('price', 255)->nullable();
              $table->integer('vendor_id')->nullable()->default(0);
              $table->integer('on_site')->nullable()->default(0);
              $table->integer('active')->nullable()->default(1);
              $table->string('image', 255)->nullable();
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
        Schema::dropIfExists('accessories');
    }
}
