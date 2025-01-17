<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableExtras extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('extras')) { return; }
        Schema::create('extras', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->double('price', 2)->default('0.00');
            $table->integer('active')->default(1);
            $table->integer('user_id');
            $table->integer('group_id');
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
        Schema::dropIfExists('extras');
    }
}
