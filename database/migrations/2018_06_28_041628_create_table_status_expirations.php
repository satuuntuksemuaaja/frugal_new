<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableStatusExpirations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (Schema::hasTable('status_expirations')) { return; }
      Schema::create('status_expirations', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('status_id');
          $table->string('name', 255);
          $table->integer('expires')->default(0);
          $table->integer('active')->default(1);
          $table->string('warning', '1')->nullable();
          $table->string('type', 255)->nullable()->default(0);
          $table->integer('expires_before')->default(0);
          $table->integer('expires_after')->default(0);

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
        Schema::dropIfExists('status_expirations');
    }
}
