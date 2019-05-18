<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tasks')) { return; }
          Schema::create('tasks', function (Blueprint $table) {
              $table->increments('id');
              $table->integer('user_id');
              $table->integer('assigned_id');
              $table->string('subject', 255);
              $table->text('body');
              $table->integer('job_id');
              $table->integer('customer_id');
              $table->integer('closed')->nullable()->default(0);
              $table->datetime('due')->nullable();
              $table->integer('urgent')->nullable()->default(0);
              $table->integer('satisfied')->nullable()->default(0);
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
        Schema::dropIfExists('tasks');
    }
}
