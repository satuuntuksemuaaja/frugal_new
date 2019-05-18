<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAuthorizationLists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('authorization_lists')) { return; }
          Schema::create('authorization_lists', function (Blueprint $table) {
              $table->increments('id');
              $table->text('item');
              $table->integer('active')->nullable()->default(1);
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
        Schema::dropIfExists('authorization_lists');
    }
}
