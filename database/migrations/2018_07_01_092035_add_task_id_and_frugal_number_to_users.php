<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaskIdAndFrugalNumberToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('users')) {
          Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'task_id')) {
                  $table->integer('task_id')->nullable()->default(0);
            }
            if (!Schema::hasColumn('users', 'frugal_number')) {
                  $table->bigInteger('frugal_number')->nullable()->default(0);
            }
          });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
