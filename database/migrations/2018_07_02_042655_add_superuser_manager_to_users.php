<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSuperuserManagerToUsers extends Migration
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
            if (!Schema::hasColumn('users', 'superuser')) {
                  $table->integer('superuser')->nullable()->default(0);
            }
            if (!Schema::hasColumn('users', 'manager')) {
                  $table->integer('manager')->nullable()->default(0);
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
